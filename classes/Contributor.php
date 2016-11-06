<?php

	class Contributor{

		public static function readExhibit($exhibitId = null){
			if(strcmp($exhibitId, "") == 0){
				return null;
			}
			else{
				$query = "SELECT * FROM exhibit WHERE exhibit_id = '$exhibitId'";
				$db = Db::getInstance()->select($query, array());
				if($db->error() || $db->count() == 0){
					return null;
				}
				elseif($db->count() > 0){
					return $db->results()[0];
				}
			}
		}

		public static function readExhibitList($key){
			$query = "SELECT exhibit_id, exhibit_name, audio_filename FROM exhibit WHERE exhibit_id LIKE '$key%'";
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

		public static function addExhibit($input = array()){
			$id = Utilities::generateExhibitId();
			$flag = false;
			if(isset($_FILES[':audio'])){
				if(strcmp($_FILES[':audio']['name'], "") == 0){
					$query = "INSERT INTO exhibit VALUES('$id', :exhibit, :description, default)";
					$flag = true;
				}
				else{
					if($audioFileName = Contributor::uploadAudio($_FILES[':audio'])){
						$flag = true;
						$query = "INSERT INTO exhibit VALUES ('$id', :exhibit, :description, '$audioFileName')";
					}else{
						var_dump(Contributor::uploadAudio($_FILES[':audio']));
					}
				}
				if($flag){
					$db = Db::getInstance()->insert($query, $input);
					if($db->error()){
						if(isset($audioFileDestination)){
							unlink($audioFileDestination);
						}
						return false;
					}
					else{
						return true;
					}
				}
			}else{
				var_dump($input);
			}
		}

		public static function updateExhibit($input = array()){
			$audioFileName = md5($_POST[':exhibit'].$_SESSION['user']['id']);
			if(strcmp($_FILES[':audio']['name'], "") != 0){
				if(strcmp($input[':exhibit'], $input[':prevName']) != 0){
					if(file_exists("audio/".$input[':audioFileName'].".mp3")){
						unlink("audio/".$input[':audioFileName'].".mp3");
					}	
				}
				Contributor::uploadAudio($_FILES[':audio']);
				$query = "UPDATE exhibit SET exhibit_name = :exhibit, description = :description, audio_filename = '$audioFileName' 
				WHERE exhibit_id = :id";
			}
			else{
				$query = "UPDATE exhibit SET exhibit_name = :exhibit, description = :description WHERE exhibit_id = :id";
			}
			unset($input[':prevName']);
			unset($input[':audioFileName']);
			$db = Db::getInstance()->update($query, $input);
			if($db->error()){
				return false;
			}
			else{
				return true;
			}
		}

		public static function deleteExhibit($exhibitId = null){
			$exhibit = Contributor::readExhibit($exhibitId);
			if(!is_null($exhibit)){
				$audioFileName = "audio/".$exhibit->audio_filename.".mp3";
				if(!is_null($exhibit->audio_filename)){
					if(!unlink($audioFileName)){
						return false;
					}
				}
				$query = "DELETE FROM exhibit WHERE exhibit_id = '$exhibitId'";
				$db = Db::getInstance()->delete($query, array());
				if($db->error()){
					return false;
				}
				else{
					return true;
				}
			}
		}

		public static function uploadAudio($audio = null){
			$audioFileName = md5($_POST[':exhibit'].$_SESSION['user']['id']);
			$audioFileDestination = "audio/".$audioFileName.".mp3";
			if($audio['error'] == 0 && move_uploaded_file($audio['tmp_name'], $audioFileDestination)){
				return $audioFileName;
			}
			else{
				return false;
			}
		}
	}
?>