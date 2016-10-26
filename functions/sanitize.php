<?php
	function sanitize($string)
	{
		htmlentities($string,ENT_QUOTES,'UTF-8');
	}
?>