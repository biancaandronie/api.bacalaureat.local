<?php

require __DIR__ . '/../vendor/autoload.php';
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

session_start();
$video_host = "http://bacalaureat.local";

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$container['upload_directory'] = __DIR__ . '/uploads';
$app = new \Slim\App($settings);
$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
    "allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$cors = new \CorsSlim\CorsSlim($corsOptions);

$app->add($cors);
// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();

function getVideos($request,$response) {
    $sql = "select * FROM videos";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $emp = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $response->withJson($emp,200)->write();
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getVideo($request) {
    //$id = 0;;
    $id =  $request->getAttribute('id');
    if(empty($id)) {
        echo '{"error":{"text":"Id is empty"}}';
    }
    try {
        $db = getConnection();
        $sth = $db->prepare("SELECT * FROM videos WHERE id=$id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $todos = $sth->fetchObject();
        return json_encode($todos);
    }
    catch(PDOException $e) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
function addVideo($request,$response) {
    global  $video_host;
    $emp = json_decode($request->getBody());
    $video_link = $video_host . "/".$emp->name;
    $sql = "INSERT INTO videos (name, course, link, tag, date) VALUES (:name,:course,:link,:tag,:date)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $emp->name);
        $stmt->bindParam("course", $emp->course);
        $stmt->bindParam("link", $video_link);
        $stmt->bindParam("tag", implode(" ",$emp->tag));
        $stmt->bindParam("date", date("Y-m-d H:i:s"));
        $stmt->execute();
        $emp->id = $db->lastInsertId();
        $db = null;
        return $response->withJson($emp,200)->write("Video successfully added");
//        $directory = $this->get('upload_directory');
//
//        $uploadedFiles = $request->getUploadedFiles();
//
//        // handle single input with single file upload
//        $uploadedFile = $uploadedFiles[$emp->name];
//        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
//            $filename = moveUploadedFile($directory, $uploadedFile);
//            $response->write('uploaded ' . $filename . '<br/>');
//        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function updateVideo($request) {
    global  $video_host;
    $emp = json_decode($request->getBody());
    $video_link = $video_host . "/".$emp->name;
    $id = $request->getAttribute('id');
    $sql = "UPDATE videos SET name=:name, course=:course link=:link, tag=:tag, date=:date WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $emp->name);
        $stmt->bindParam("course", $emp->course);
        $stmt->bindParam("link",  $video_link);
        $stmt->bindParam("tag", $emp->tag);
        $stmt->bindParam("date", date("Y-m-d H:i:s"));
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo json_encode($emp);
    }
    catch(PDOException $e) {
       echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function deleteVideo($request) {
    $id = $request->getAttribute('id');
    $sql = "DELETE FROM videos WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo '{"error":{"text":"successfully! deleted Records"}}';
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
