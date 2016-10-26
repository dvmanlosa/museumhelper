<?php
	class Db
	{
		private static $_instance = null;
		private $_pdo,
				$_query,
				$_error=false,
				$_results,
				$_count=0;

		private function __construct()
		{
			try{
				$this->_pdo=new PDO('mysql:host='.Config::get('mysql/host').'; dbname='.Config::get('mysql/db'),Config::get('mysql/username'),Config::get('mysql/password'));
			}catch(PDOException $e){
				die($e->getMessage());
			}
		}

		public static function getInstance()
		{
			if(!isset(self::$_instance)){
				self::$_instance=new DB();
			}
			return self::$_instance;
		}

		public function query($sql,$binds = array())
		{
			$this->_error = false;
			if($this->_query=$this->_pdo->prepare($sql)){
				if(count($binds)>0 ? $this->_query->execute($binds): $this->_query->execute()){
					$this->_results=$this->_query->fetchAll(PDO::FETCH_OBJ);
					$this->_count=$this->_query->rowCount();
				}else{
					$this->_error=true;
				}
			}
			return $this;
		}

		public function select($sql,$binds)
		{
			return $this->query($sql,$binds);
		}

		public function insert($sql,$binds)
		{
		    return $this->query($sql,$binds);
		}

		public function delete($sql,$binds)
		{
			return $this->query($sql,$binds);
		}

		public function update($sql,$binds)
		{
			return $this->query($sql,$binds);
		}

		public function error()
		{
			return $this->_error;
		}

		public function count()
		{
			return $this->_count;
		}

		public function results()
		{
			return $this->_results;
		}
	}
?> 