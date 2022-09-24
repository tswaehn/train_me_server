<?php



class LoginRequest extends WebRequest
{

    function getRequestType(){
        return "login";
    }

    // \TODO: create actual JWT
    function createJWT(){
        return ["jwt" => ""];
    }

    // override:
    function executeRequest($session_token, $data): bool
    {
        $jwt = $this->createJWT();
        return $this->returnWithSuccess($jwt + ["user"=>$this->db_user->getUserInfo()]);
    }



}