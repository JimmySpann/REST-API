<?php

//header("Access-Control-Allow-Origin: *");
//header('Content-type: application/xml');
require_once("DB.php");
require_once("utilities.php");

$db = new DB("");  
$server_resquest = $_SERVER['REQUEST_METHOD'];
$url = $_GET['url'];

if ($server_resquest == "GET") {
} else if ($server_resquest == "POST") {
        if (strpos($url, 'todo-list/') !== true) {
                require_once("todo-list/index.php");
        } else {
                require_once("general.php");
        }

} else if ($server_resquest == "DELETE") {
} else {
        echo "Invalid Request.";
        http_response_code(401);
}

?>
