<?php
	class Admin{
		
		public static function addUser($input = array()){
            if(count($input) <= 0){
                return false;
            }
            else{
                $input[':password'] = password_hash($input[':password'], PASSWORD_DEFAULT);
                $query = "INSERT INTO users VALUES(default, :firstName, :lastName, :middleName, :contact, :email, :password, :type, default)";
                $db = Db::getInstance()->insert($query, $input);
                if($db->error()){
                    return false;
                }
                else{
                    return true;
                }
            }
        }

        public static function readContributorList(){
        	$query = "SELECT user_id, firstName, lastName, middleName, contact, email, deactivated FROM users WHERE type = 'contributor'";
        	$db = Db::getInstance()->select($query, array());
        	if($db->error()){
        		return null;
        	}
        	else{
        		if($db->count() > 0){
        			return $db->results();
        		}
        		return null;
        	}
        }

        public static function deactivateContributor($id){
        	$query = "UPDATE users SET deactivated = 1 WHERE user_id = :id";
            $values = array(
                ':id' => $id
                );
        	$db = Db::getInstance()->update($query, $values);
        	if($db->error()){
        		return false;
        	}
        	else{
        		return true;
        	}
        }

        public static function activateContributor($id){
            $query = "UPDATE users SET deactivated = 0 WHERE user_id = :id";
            $values = array(
                ':id' => $id
                );
            $db = Db::getInstance()->update($query, $values);
            if($db->error()){
                return false;
            }
            else{
                return true;
            }
        }
	}

?>