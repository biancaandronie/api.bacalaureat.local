<?php

require __DIR__ . '/../vendor/autoload.php';
use Slim\Http\UploadedFile;

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register routes
require __DIR__ . '/../src/routes.php';

//setup middleware
require __DIR__ . '/../src/middleware.php';



function getVideos($request,$response) {
    $sql = "select * FROM videos";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $emp = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $response->withJson($emp,200);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getVideo($request,$response) {
    $emp = json_decode($request->getBody());
    $name = "$emp->name%";
    $sql = "SELECT *  FROM videos WHERE name LIKE :name";
    try {
        if (!empty($emp->name)) {
            $db = getConnection();
            $sth = $db->prepare($sql);
            $sth->bindParam("name", $name);
            $sth->execute();
            $todos = $sth->fetchAll(PDO::FETCH_OBJ);
            return $response->withJson($todos, 200);
        }
        else{
            return $response->withJson("The name parameter is empty",401)->write();
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getVideoLink($request,$response) {
    $emp = json_decode($request->getBody());
    $sql = "SELECT *  FROM videos WHERE id=:id";
    try {
        if (!empty($emp->id)) {
            $db = getConnection();
            $sth = $db->prepare($sql);
            $sth->bindParam("id", $emp->id);
            $sth->execute();
            $todos = $sth->fetchAll(PDO::FETCH_OBJ);
            return $response->withJson($todos, 200);
        }
        else{
            return $response->withJson("The id parameter is empty",401);
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}



function addVideo($request,$response) {
    $video_host = "http://bacalaureat.local";
    $emp = json_decode($request->getBody());
    $name = $request->getParsedBodyParam('name');
    $course = $request->getParsedBodyParam('course');
    $description = $request->getParsedBodyParam('description');
    $sql = "INSERT INTO videos (name, course, link, description, date) VALUES (:name,:course,:link,:description,:date)";
    try {
    	$directory = '../bacalaureat/public/videos';
        $uploadedFiles = $request->getUploadedFiles();
        if (empty($uploadedFiles['videofile'])) {
            throw new \RuntimeException('Expected a videofile');
        }
        $uploadedFile = $uploadedFiles['videofile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);
            $response->write('uploaded ' . $filename . '<br/>');
	    }
    	$video_link = $video_host . "/videos/".$filename;
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $name);
        $stmt->bindParam("course", $course);
        $stmt->bindParam("link", $video_link);
        $stmt->bindParam("description", $description);
        $stmt->bindParam("date", date("Y-m-d H:i:s"));
        $stmt->execute();
        $emp->id = $db->lastInsertId();
        $db = null;
        // handle single input with single file upload
        return $response->withJson($emp, 200)->write("Video successfully added");
       } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//function updateVideo($request) {
//    global  $video_host;
//    $emp = json_decode($request->getBody());
//    $video_link = $video_host . "/".$emp->name;
//    $id = $request->getAttribute('id');
//    $sql = "UPDATE videos SET name=:name, course=:course link=:link, tag=:tag, date=:date WHERE id=:id";
//    try {
//        $db = getConnection();
//        $stmt = $db->prepare($sql);
//        $stmt->bindParam("name", $emp->name);
//        $stmt->bindParam("course", $emp->course);
//        $stmt->bindParam("link",  $video_link);
//        $stmt->bindParam("tag", $emp->tag);
//        $stmt->bindParam("date", date("Y-m-d H:i:s"));
//        $stmt->bindParam("id", $id);
//        $stmt->execute();
//        $db = null;
//        echo json_encode($emp);
//    }
//    catch(PDOException $e) {
//       echo '{"error":{"text":'. $e->getMessage() .'}}';
//    }
//}

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

function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

// Run app
$app->run();

