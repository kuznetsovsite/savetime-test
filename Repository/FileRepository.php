<?php
/**
 * Created by PhpStorm.
 * User: sk
 * Date: 1/9/18
 * Time: 3:24 PM
 */

namespace FileUpload\Repository;


use FileUpload\EntityInterface;
use FileUpload\FileEntity;

/**
 * По хорошему, надо создать базовый класс для работы с БД, и вспомогательные классы для работы и обработки свойств(prepared statement), мэппинга
 * В базовом классе реализовать основные методы (поиск по id, persist, transaction....) Некое подобие Doctrine
 * Class FileRepository
 * @package FileUpload\FileRepository
 */
class FileRepository implements RepositoryInterface
{
    private $tableName;
    private $dbConn;

    public function __construct(MysqlConnect $dbConn)
    {
        $this->dbConn = $dbConn;
        $this->tableName = 'sk_file_table';
    }

    public function save(FileEntity $file)
    {
        if(empty($file->title)) {
            $title = $this->dbConn->getConnect()->real_escape_string($file->title);
        } else {
            $title = '';
        }

        $fullPath = '';//$this->dbConn->getConnect()->real_escape_string($file->fullPath);
        $path = $this->dbConn->getConnect()->real_escape_string($file->path);

        if(empty($file->urlId)) {
            $urlId = $this->dbConn->getConnect()->real_escape_string($file->urlId);
        } else {
            $urlId = '';
        }

        $ext = $this->dbConn->getConnect()->real_escape_string($file->ext);
        $mimeType = $this->dbConn->getConnect()->real_escape_string($file->mimeType);
        $createdAt = $file->createdAt;
        if(empty($createdAt)) {
            $createdAt = new \DateTime();
        }
        $createdAt = $createdAt->format('Y-m-d H:i:s');
        $fileSize = $file->fileSize;
        $query = "INSERT INTO `".$this->dbConn->getDB()."`.`".$this->tableName."` (`id`, `title`, `full_path`, `path`, `created_at`, `file_size`, `url_id`, `ext`, `mimeType`) VALUES (NULL, '".$title."', '".$fullPath."', '".$path."', '".$createdAt."', '".$fileSize."', '".$urlId."', '".$ext."', '".$mimeType."')";

        $this->dbConn->getConnect()->query($query);
        if($this->dbConn->getConnect()->error) {
            throw new \Exception($this->dbConn->getConnect()->error);
        }

        return $this->dbConn->getConnect()->insert_id;
    }

    public function find($id)
    {
        $query = "SELECT * FROM `".$this->dbConn->getDB()."`.`".$this->tableName."` WHERE `".$this->tableName."`.`id`=".intval($id);
//        echo $query;

        $result = $this->dbConn->getConnect()->query($query);
        if ($result) {
            $fileArray = $result->fetch_assoc();
            $result->close();

            $fileEntity = new FileEntity();
            $fileEntity->importArray($fileArray);

            return $fileEntity;
        }

        return null;
    }
}