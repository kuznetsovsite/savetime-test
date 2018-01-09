<?php
namespace FileUpload\Exception;


class FileUploadException extends \Exception
{
    const ERROR_MOVE_FILE = 70;
    const ERROR_MODE = 71;
    const FILE_UPLOAD_MAX_SIZE = 72;
    const FILE_UPLOAD_INCORRECT_EXT = 73;
    const FILE_FORMAT_INCORRECT = 74;

    public function __construct($code, \Exception $previous = null)
    {
        $message = $this->codeToMessage($code);

        parent::__construct($message, $code);
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "Загружаемый файл был получен только частично";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "Файл не был загружен";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Отсутствует временная директория";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Не удалось записать файл на диск";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "Расширение остановило загрузку файла";
                break;
            case self::FILE_UPLOAD_MAX_SIZE:
                $message = 'Размер превысил максимально допустимый';
                break;
            case self::FILE_UPLOAD_INCORRECT_EXT:
                $message = 'Недопустимое расширение';
                break;
            case self::FILE_FORMAT_INCORRECT:
                $message = 'Расширение не соответсвует формату файла';
                break;
            case self::ERROR_MOVE_FILE:
                $message = 'При сохранении файла произошла ошибка. Пожалуйста обратитесь в службу поддержки';
                break;
            case self::ERROR_MODE:
                $message = 'Директория недоступна для записи';
                break;
            default:
                $message = 'При загрузке произошла ошибка. Пожалуйста обратитесь в службу поддержки';
                break;
        }
        return $message;
    }
}