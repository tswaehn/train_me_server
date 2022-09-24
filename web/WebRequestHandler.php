<?php


class WebRequestHandler
{
    var $SUCCESS = "SUCCESS";
    var $FAILED = "FAILED";

    var $availableRequests = array();
    var $jsonRequest = array();
    var $action = "";
    var $requestData = array();

    function __construct(){
    }

    function prepareError($text=""){
        $ret_array = ["API_VERSION"=>Configuration::API_VERSION];
        header('HTTP/1.0 404 Not Found');
        $ret_array["result"] = $this->FAILED;
        $ret_array["info"] = $text;
        return $ret_array;
    }

    function prepareSuccess($text=""){
        $ret_array = ["API_VERSION"=>Configuration::API_VERSION];
        header('HTTP/1.1 200 OK');
        $ret_array["result"] = $this->SUCCESS;
        $ret_array["info"] = $text;
        return $ret_array;
    }

    /*
     * we expect the request
     *      {
     *          "action" : "some_action",
     *          "session_token" : "some_session_token",
     *          "some_action":  {
     *                  ... data that will forwarded to the request
     *              }
     *      }
     */
    function execute(){

        if (!$this->loadFromPOST()){
            return $this->prepareError("json decode failed");
        }

        // make sure the request is well formed
        $requestObj = $this->checkRequestType();
        if ($requestObj == NULL){
            return $this->prepareError("invalid request type");
        }


        // do, whatever needs to be done and store return data to the action
        if ($requestObj->execute($this->action, $this->requestData)){
            // request is accepted
            $ret_array = $this->prepareSuccess();
        } else {
            // request failed
            $ret_array = $this->prepareError();
        }

        $ret_array[$this->action] = $requestObj->getResult();

        return $ret_array;
    }

    function loadFromPOST(){
        // try to load request from POST section
        try {
            $this->jsonRequest = (array) json_decode(file_get_contents('php://input'), TRUE);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    function checkRequestType(){
        if (!isset($this->jsonRequest["action"])){
            return NULL;
        }

        // make sure we have a valid action
        $this->action = $this->jsonRequest["action"];
        if (!isset($this->availableRequests[$this->action])) {
            return NULL;
        }

        if (isset($this->jsonRequest[$this->action])){
            $this->requestData = $this->jsonRequest[$this->action];
        }

        return $this->availableRequests[$this->action];
    }

    function addRequest($requestObject){
        $name = $requestObject->getRequestType();
        $this->availableRequests[$name]= $requestObject;
    }
}