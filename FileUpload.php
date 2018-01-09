<?php
namespace FileUpload;


//Все данные файла (название, размер, время загрузки и тд) хранятся в БД.
//Файл уникально идентифицируется по некому id.
//Физически файлы хранятся в файловой системе на сервере.
//В классе должны быть реализованы методы upload — загрузка файлов,
//принимает аргументом элемент массива $_FILES, возвращает результатом ошибку или id загруженного файла,
//info -- принимает id файла и возвращает всю информацию по нему, включая полный путь на сервере.
//В классе настраивается upload_dir —  директория загрузки файлов, допустимые расширения и максимальный размер загружаемого файла.
//Просьба не использовать PDO и т.п.
use FileUpload\Exception\FileUploadException;
use FileUpload\Repository\FileRepository;
use FileUpload\Repository\RepositoryInterface;

/**
 * Created by PhpStorm.
 * User: sk
 * Date: 1/9/18
 * Time: 3:06 PM
 */
class FileUpload
{
    /** @var string $uploadDir директория загрузки файлов */
    protected $uploadDir;
    /** @var array $ext допустимые расширения png jpg */
    protected $ext;
    /** @var  int $maxSize максимальный размер загружаемого файла */
    protected $maxSize; // 0 - ограниченно настройками сервера
    /** @var  string $baseDir - устанавливается при инициализации или из config*/
    protected $baseDir;
    /** @var FileRepository $saveDbService */
    protected $saveDbService;

    public function __construct($baseDir, $uploadDir='/upload', array $ext=array(), $maxSize=null)
    {
        if(file_exists($baseDir)) {
            $this->baseDir = $baseDir;
        } else {
            throw new \RuntimeException('Incorrect base dir');
        }

        $this->ext = array();
        if(!empty($ext) && is_array($ext)) {
            foreach($ext as $extItem) {
                if(is_string($extItem)) {
                    $this->addExt($extItem);
                }
            }
        }

        $this->setUploadDir($uploadDir);

        if(is_null($maxSize)) {
            $maxb = $this->getMaxBytes();
            $this->maxSize = $maxb;
        }

        if($maxSize<0) {
            $maxSize = 0;
        }
    }

    public function upload(array $file)
    {
        if($file['error'] == UPLOAD_ERR_OK) { // Ошибок не возникло, файл был успешно загружен на сервер.
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if($this->maxSize !=0 && $file['size']> $this->maxSize) {
                throw new FileUploadException(FileUploadException::FILE_UPLOAD_MAX_SIZE);
            } elseif(!empty($this->ext)) {
                if(empty($ext)) {
                    throw new FileUploadException(FileUploadException::FILE_UPLOAD_INCORRECT_EXT);
                } elseif(!in_array($ext, $this->ext)) {

                    throw new FileUploadException(FileUploadException::FILE_UPLOAD_INCORRECT_EXT);
                }
            }

            // проверка расширения и типа файла
            // @TODO: не реализованно
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            // СОЗДАНИЕ ИМЁН И ПУТЕЙ ВЫНЕСТИ В ОТДЕЛЬНЫЙ КЛАСС
            // ЗДЕСЬ ПРОСТО ДЛЯ НАГЛЯДНОСТИ - Неправильно!
            //
            // если всё хорошо - перемещаем файл
            $uploadDirPath = $this->baseDir.$this->uploadDir;
            // здесь проверка на существование директории и на право доступа. Не дописал
            // если нет директории то создаём рекурсивно.
            $uploadFilename = uniqid('f_', true).rand(1,1000000).uniqid('_', true).'.'.$ext;
            $uploadPath = $uploadDirPath.'/'.$uploadFilename;

            if(!is_writable($uploadDirPath)) {
                throw new FileUploadException(FileUploadException::ERROR_MODE);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                chmod($uploadPath, 0755);
                if(empty($this->saveDbService)) {
                    throw new \RuntimeException('Not initialized repository');
                }
                $fileEntity = new FileEntity();
                $fileEntity->title = $file['name'];
                $fileEntity->path = $this->uploadDir.'/'.$uploadFilename;
                $fileEntity->createdAt = new \DateTime();
                $fileEntity->fileSize = $file['size'];
                $fileEntity->fileSize = $file['size'];
                $fileEntity->urlId = $uploadFilename;
                $fileEntity->ext = $ext;
                $fileEntity->mimeType = $mime;

                $id = $this->saveDbService->save($fileEntity);

                return $id;
            } else {
                throw new FileUploadException(FileUploadException::ERROR_MOVE_FILE);
            }
        } else {
            throw new FileUploadException($file['error']);
        }
    }

    protected function getFullPath(FileEntity $fileEntity)
    {
        return $this->baseDir.$fileEntity->path;
    }

    public function info($id)
    {
        if(empty($this->saveDbService)) {
            throw new \RuntimeException('Not initialized repository');
        }

        $return = $this->saveDbService->find($id);
        if(empty($return)) {
            return null;
        }
        $return->fullPath = $this->getFullPath($return);
        return $return;
    }

    public function setUploadDir($uploadDir)
    {
        if(file_exists($this->baseDir.$uploadDir)) {
            $this->uploadDir = $uploadDir;
        } else {
            throw new \RuntimeException('Incorrect upload dir');
        }
    }

    public function addExt($ext)
    {
        $ext = mb_strtolower($ext);
        if(!is_array($this->ext)){
            $this->ext = array();
        }

        if(!in_array($ext, $this->ext)) {
            $this->ext[] = $ext;
        }

        return $this;
    }

    public function clearExt()
    {
        $this->ext = array();
        return $this;
    }

    public function removeExt($ext)
    {
        $ext = mb_strtolower($ext);
        $key = array_search($ext, $this->ext);
        if($key) {
            unset($this->ext[$key]);
        }
        return $this;
    }

    public function getExt()
    {
        return $this->ext;
    }

    public function setMaxSize($maxSize)
    {

    }

    public function getMaxSize()
    {
        $this->maxSize;
    }

    public function getMaxBytes()
    {
        $max_upload = $this->returnBytes((int)(ini_get('upload_max_filesize')));
        $max_post = $this->returnBytes((int)(ini_get('post_max_size')));
        $memory_limit = $this->returnBytes((int)(ini_get('memory_limit')));
        $maxb = min($max_upload, $max_post, $memory_limit);
        return $maxb;
    }

    protected function returnBytes($val)
    {
        $val  = trim($val);

        $last = strtolower($val[strlen($val)-1]);
        $val  = substr($val, 0, -1); // necessary since PHP 7.1; otherwise optional

        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    public function setSaveDbService(RepositoryInterface $saveDbService)
    {
        $this->saveDbService = $saveDbService;
    }
}