<?php
namespace FileUpload\Repository;

use FileUpload\FileEntity;

interface RepositoryInterface {
    public function save(FileEntity $entity);
    public function find($id);
}