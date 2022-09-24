<?php


class WebRequest
{
    var $jsonRequest = array();
    var $result_str = "";
    var $result = array();
    var $db_connection = NULL;

    var $session_id = "";

    var DB_Connection|DB_User $db_user;

    function __construct($db_connection){
        $this->db_connection = $db_connection;
        $this->db_user = new DB_User($db_connection);
    }

    function getRequestType(){
        return "request";
    }

    /*
     * read from request and check for correct version of API
     */
    function readFromPOST(){
        $this->input_data = (array) json_decode(file_get_contents('php://input'), TRUE);

        $JSON_API_VERSION = $this->getInput("API_VERSION");
        if ($JSON_API_VERSION != Configuration::API_VERSION){
            // clear data if version does not fit
            $this->input_data = array();
            $this->setRequestReturnStr("invalid API version");
            return false;
        }

        $r = $this->getRequestType();
        $data_string = "";

        // check if we received gzip coded content
        if (isset($this->input_data[$r]["gzip"])){
            $base64 = $this->input_data[$r]["gzip"];
            $gzip_data = base64_decode($base64, true);
            if ($gzip_data == false){
                $this->setRequestReturnStr("invalid base64data");
                return false;
            }
            $data_string = gzdecode($gzip_data);
            if ($data_string == false){
                $this->setRequestReturnStr("invalid gzdata");
                return false;
            }

            // string to array
            $array_data = json_decode($data_string, true);

            // replace with decoded
            $this->input_data[$r]["data"] = $array_data;

        } else {
            // data is already array ... so do nothing
        }



        return true;
    }

    /*
     * connect to db and validate token
     *  - create db object
     *  - connect
     */
    function checkAuth(){

        $session = $this->getInput("session");
        $user_name = $this->getInput("auth,user_name" );
        $user_pass = $this->getInput("auth,user_pass" );

        // check input
        $use_session_token = true;
        // no session token, we need to login, so need user and pass
        if (($user_name != NULL) && ($user_name != "")){
            $use_session_token = false;
        }
        if (($user_pass != NULL) && ($user_pass != "")){
            $use_session_token = false;
        }

        if ($use_session_token){
            // validate token
            if (!$this->db_user->loginWithSession($session)){
                // cannot auth
                $this->setRequestReturnStr("invalid login");
                return false;
            }
        } else {
            // validate login
            if (!$this->db_user->loginWithPass($user_name, $user_pass)){
                // cannot auth
                $this->setRequestReturnStr("invalid login");
                return false;
            }
        }

        // finally, set session id
        $this->session_id = $this->db_user->getSessionId();
        return true;
    }

    /*
     * get a value/obj from input json
     *
     * ex:
     *  JSON = { "test": { "hello": "key"} }
     *
     *  would be addressed like: $input_str = "test,hello"
     *
     * \return: addressed object
     */
    function getInput($input_str){
        $input_array = explode(",", $input_str);

        $p = $this->jsonRequest;

        // step down the array recursive and check if key exists
        foreach ($input_array as $ref){
            if (isset($p[$ref])){
                $p = &$p[$ref];
            } else {
                return NULL;
            }
        }
        // here we reached the full depth
        return $p;
    }

    /*
     * entry for handler
     *  $action = [string]
     *  $requestData = [array]
     */
    function execute($action, $requestData): bool
    {
        $this->jsonRequest = $requestData;

        if (!$this->checkAuth()){
            return $this->returnWithError([],"auth failed");
        }

        // call virtual
        return $this->executeRequest($action, $requestData);
    }

    function getResult(){
        return $this->result;
    }
    /*
     * virtual function, need to override
     */
    function executeRequest($session_token, $data): bool
    {
        return $this->returnWithError([], "unknown request");
    }


    function setRequestReturnStr($text){
        $this->result_str = $text;
    }
    function returnWithError($data=array(), $text=NULL){
        if ($text == NULL){
            $text = $this->result_str;
        }
        $this->result= array("response_code" => "failed", "response_str" => $text) + $data;
        return false;
    }

    function returnWithSuccess($data=array(), $text=NULL){
        if ($text == NULL){
            $text = $this->result_str;
        }
        $this->result= array("session" => $this->session_id, "response_code" => "success", "response_str" => $text) + $data;
        return true;
    }

}

