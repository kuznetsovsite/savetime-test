<?php
namespace FileUpload;


class FileEntity
{
    protected $id;

    protected $title;

    /** @var string $fullPath - полный путь */
    protected $fullPath;

    protected $path;

    protected $createdAt;

    protected $fileSize;

    protected $urlId;

    protected $ext;

    protected $mimeType;

    public function __set($name, $value)
    {

        if(property_exists($this, $name)) {
            // здесь проверки на соответствие типов. Но надо вынести их в отдельный класс.
            // Например как это сделанно в symfony Entity на основе нотации или yml описаний свойств
            if($name == 'createdAt' && !($value instanceof \DateTime)) {
                throw new \Exception('Propery '.$name.' must have DateTime value');
            }
            $this->$name = $value;
        } else {
            throw new \Exception('Propery '.$name.' not exists');
        }
    }

    public function __get($name)
    {
        if(property_exists($this, $name)) {
            return $this->$name;
        } else {
            throw new \Exception('Propery '.$name.' not exists');
        }
    }




    public function toArray()
    {
        return array(
            'id' => $this->id,
            'title' => $this->title,
            'fullPath' => $this->fullPath,
            'path' => $this->path,
            'createdAt' => $this->createdAt,
            'fileSize' => $this->fileSize,
            'urlId' => $this->urlId,
            'ext' => $this->ext,
            'mimeType' => $this->mimeType,
        );
    }

    public function importArray(array $arr)
    {
        if(isset($arr['id'])) {
            $this->id = $arr['id'];
        }
        if(isset($arr['title'])) {
            $this->title = $arr['title'];
        }

        if(isset($arr['path'])) {
            $this->path = $arr['path'];
        }
        if(isset($arr['createdAt'])) {
            if($arr['createdAt'] instanceof \DateTime) {
                $this->createdAt = $arr['createdAt'];
            } else {
                $dt = new \DateTime();
                $dt->setTimestamp($arr['createdAt']);
                $this->createdAt = $dt;
            }
        }
        if(isset($arr['fileSize'])) {
            $this->fileSize = $arr['fileSize'];
        }
        if(isset($arr['urlId'])) {
            $this->urlId = $arr['urlId'];
        }
        if(isset($arr['ext'])) {
            $this->ext = $arr['ext'];
        }
        if(isset($arr['mimeType'])) {
            $this->mimeType = $arr['mimeType'];
        }
    }
}