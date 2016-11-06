<?php
class Utilities {
	function generateExhibitId(){
		$contributorIdToHex = getkey();
		$query = "SELECT exhibit_id FROM exhibit WHERE exhibit_id LIKE '$contributorIdToHex%' ORDER BY exhibit_id DESC LIMIT 1";
		$db = Db::getInstance()->select($query, array());
		if($db->error()){
			return false;
		}
		else{
			if($db->count() > 0){
				$lastCount = (int)(substr($db->results()[0]->exhibit_id, 3));
				$newCount = $lastCount + 1;
				if(strlen($newCount) == 1){
					$newCount = "00".$newCount;
				}
				elseif(strlen($newCount) == 2){
					$newCount = "0".$newCount;
				}
				$id = $contributorIdToHex.$newCount;
			}
			else{
				$id = $contributorIdToHex."001";
			}
		}
		return (string)$id;
	}

	function getKey(){
		$key = dechex($_SESSION['user']['id']);
		if(strlen($key) == 1){
			$key = "00".$key;
		}
		elseif (strlen($key) == 2) {
			$key = "0".$key;
		}
		return $key;
	}
}
?>