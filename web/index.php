<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once("../conf/config.php");

require_once("../lib/errorHandler.php");
require_once("../lib/log.php");

require_once ("../lib/db_connection.php");
require_once ("../lib/db_user.php");


require_once ("WebRequestHandler.php");
require_once ("WebRequest.php");
require_once("LoginRequest.php");


date_default_timezone_set("UTC");


if (!isset($_SERVER['REQUEST_METHOD'])){
    die("missing REQUEST_METHOD");
}


#$url = parse_url($_SERVER['REQUEST_URI'])["path"];
#$script_name = parse_url($_SERVER['SCRIPT_NAME'])["path"];
$requestMethod = $_SERVER["REQUEST_METHOD"];

// make sure, we have a proper connection to database
global $conf;
$db_connection = new DB_Connection($conf);
if (!$db_connection->connect()){
    die();
}

// create request handler
$requestHandler = new WebRequestHandler();
$requestHandler->addRequest( new LoginRequest($db_connection) );


switch($requestMethod){
    case 'GET': {
        header('HTTP/1.1 200 OK');
        echo json_encode(["result" => "OK", "type"=> "GET"]);
        exit();
        }

    case 'POST': {

        // execute request - returns array()
        $result_array = $requestHandler->execute();

        // convert to json
        echo json_encode($result_array);

        exit();
    }

}



