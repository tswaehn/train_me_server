<?php

class DB_User {

    // db object
    var $connection;

    // props
    private $uid = -1;
    private $login_ok = false;

    // cache
    private $user_name = "";
    private $first_name = "";
    private $last_name = "";
    private $email = "";

    private string $session = "";
    private $db_connection;

    function __construct($db_connection){
        $this->db_connection = $db_connection;
        $this->login_ok = false;
    }

    function loginWithPass($userName, $userPass){
        $values = ["name"=>$userName, "password"=> $userPass];

        $stmt= $this->db_connection->q("SELECT * from ". t("users"). " WHERE name=:name AND password=:password LIMIT 1", $values );

        if ($stmt->rowCount() == 0){
            do_log(0, "error invalid user");
            return false;
        }

        $user = $stmt->fetch();
        $this->uid = $user["uid"];
        $this->login_ok = true;
        $this->user_name = $user["name"];
        $this->first_name = $user["first_name"];
        $this->last_name = $user["last_name"];
        $this->email = $user["email"];

        if (!$this->updateSession()){
            return false;
        }

        return true;
    }

    function loginWithSession($session){
        $values = ["sid"=>$session];

        // lookup session to retrieve the user id
        $stmt= $this->db_connection->q("SELECT * from ". t("sessions"). " WHERE sid=:sid LIMIT 1", $values );
        if ($stmt->rowCount() == 0){
            do_log(0, "error invalid user");
            return false;
        }
        $db_session = $stmt->fetch();
        $uid = $db_session["uid"];

        // with the user id we lookup the user
        $values = ["uid"=>$uid];
        $stmt= $this->db_connection->q("SELECT * from ". t("users"). " WHERE uid=:uid LIMIT 1", $values );

        if ($stmt->rowCount() == 0){
            do_log(0, "error invalid user");
            return false;
        }

        // set session id
        $this->session = $db_session["sid"];

        $user = $stmt->fetch();
        $this->uid = $user["uid"];
        $this->login_ok = true;
        $this->user_name = $user["name"];
        $this->first_name = $user["first_name"];
        $this->last_name = $user["last_name"];
        $this->email = $user["email"];

        return true;

    }

    function loginWithToken($userName, $userToken){
        $values = ["name"=>$userName, "token"=> $userToken];

        $stmt= $this->db_connection->q("SELECT * from ". t("users"). " WHERE name=:name AND token=:token LIMIT 1", $values );

        if ($stmt->rowCount() == 0){
            do_log(0, "error invalid user");
            return false;
        }
        $user = $stmt->fetch();
        $this->uid = $user["uid"];
        $this->login_ok = true;
        $this->user_name = $user["name"];
        $this->first_name = $user["first_name"];
        $this->last_name = $user["last_name"];
        $this->email = $user["email"];

        return true;
    }

    public function getUserInfo(){
        return [
            "uid"=>$this->uid,
            "name"=>$this->user_name,
            "firstName"=>$this->first_name,
            "lastName"=>$this->last_name,
            "email"=>$this->email,
        ];
    }

    public function getSessionId(){
        return $this->session;
    }

    private function updateSession(){
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        $uuid4 = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

        $values= ["uid"=> $this->uid, "sid"=>$uuid4];
        // insert or update
        $stmt= $this->db_connection->q("REPLACE INTO ". t("sessions"). " (uid,sid) VALUES(:uid,:sid)", $values );
        if ($stmt == NULL){
            return false;
        } else {
            // update session id
            $this->session = $uuid4;
            return true;
        }
    }

}