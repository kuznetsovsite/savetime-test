<?php
namespace FileUpload;


//////////// Вот это всё через autoload
require_once 'Exception/FileUploadException.php';
require_once 'Repository/RepositoryInterface.php';
require_once 'Repository/FileRepository.php';
require_once 'Repository/MysqlConnect.php';
require_once 'FileEntity.php';
require_once 'FileUpload.php';


//Interface 'FileUpload\Repository\RepositoryInterface' not found in /home/sk/CE/FileUpload/Repository/FileRepository.php on line 21

// From configuration
use FileUpload\Repository\FileRepository;
use FileUpload\Repository\MysqlConnect;

$host = 'localhost';
$user = 'root';
$psw = 'PassWord';
$db = 'visotki';


$fileUploadService = new FileUpload(__DIR__, '/upload', array('jpg', 'png'));
$maxb = $fileUploadService->getMaxBytes();

if(empty($_FILES)) {
    echo '
        <form enctype="multipart/form-data" method="post" action="">
            <input type="hidden" name="MAX_FILE_SIZE" value="'.$maxb.'" />
            <input type="file" name="file"> 
            <input type="submit" value="Send to upload">
        </form>
    ';
    echo '
        <form enctype="multipart/form-data" method="post" action="">
            <input type="hidden" name="MAX_FILE_SIZE" value="'.$maxb.'" />
            <input type="file" name="file_new"> 
            <input type="submit" value="Send to upload_new">
        </form>
    ';
} else {
    try {

        $connectEntity = new MysqlConnect($host, $user, $psw, $db);
        $connectEntity->connect();


        $fileSaveDbService = new FileRepository($connectEntity);
        $fileUploadService->setSaveDbService($fileSaveDbService);

        if(isset($_FILES['file_new'])) {
            $fileUploadService->setUploadDir('/upload_new');
            $id = $fileUploadService->upload($_FILES['file_new']);
        } elseif(isset($_FILES['file'])) {
            $id = $fileUploadService->upload($_FILES['file']);
        }
//        var_dump($id);
//        die(__FILE__.__LINE__);
        if(!empty($id)) {

            echo 'Id загруженного файла:'.$id;
            $info = $fileUploadService->info($id);
            echo '<pre>';
            var_dump($info);
            echo '</pre>';
        } else {
            echo 'Не получилось сохранить в БД';
        }

        $connectEntity->close();
    } catch (\Exception $e) {
        // log full error $e->getMessage().....
        // template error
        echo '<pre>';
        echo $e->getMessage();
        print_r($e->getTraceAsString());
        die('Error: Что-то пошло не так');
    }
}

