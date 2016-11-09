<?php

    class Users{ 
        public static function login($input = array()){
            if(count($input) < 2){
                //Lacking inputs
                return false;
            }
            else{
                $query = "SELECT user_id, password,type, email FROM users WHERE email = :email";
                $db = Db::getInstance()->select($query, array(':email' => $input['email']));
                if($db->count() === 0){
                    //Email does not exists
                    return false;
                }
                else{
                    $result = $db->results()[0];
                    if(!password_verify($input['password'], $result->password)){
                        //Incorrect password
                        return false;
                    }
                    else{
                        $_SESSION['user'] = array(
                            'id' => $result->user_id,
                            'type' => $result->type,
                            'email' => $result->email,
                        );
                        return true;
                    }
                }
            }
        }

        public static function logout(){
            session_unset($_SESSION['user']);
        }
    }
?>