<?php
	class User {
		public $user;
		public $hosts = array();
		public $password;

		public $privileges;

		// ssl_*, x509_*, max_*

		public function __construct($user, array $hosts, $password, UserPrivileges $privileges) {
			$this->user = $user;
			$this->hosts = $hosts;
			$this->password = $password;
			$this->privileges['*'] = $privileges;
		}

		public function cloneForHosts($hosts) {
			$user = new self($this->user, $hosts, $this->password, $this->privileges['*']);
			foreach($this->privileges as $db => $privs) {
				$user->addPrivileges($db, clone $privs);
			}
			return $user;
		}

		public static function createFromMySQLRecord($row) {
			$privs = UserPrivileges::parseFromMySQLRecord($row);
			return new User($row['User'], array($row['Host']), $row['Password'], $privs);
		}

		public function addDBPrivilegesFromMySQLRecord($row) {
			assert($row['User'] == $this->user);
			assert(in_array($row['Host'], $this->hosts));
			$this->addPrivileges($row['Db'] .'.*', DBPrivileges::parseFromMySQLRecord($row));
		}

		public function addPrivileges($db, Privileges $privs) {
			$this->privileges[$db] = $privs;
		}

		public function removePrivileges($db) {
			unset($this->privileges[$db]);
		}
	}
?>
