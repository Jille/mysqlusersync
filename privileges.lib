<?php
	abstract class Privileges {
		protected $rights = array();

		public function __construct($initialGrant = array()) {
			foreach(static::$privileges as $priv) {
				$this->rights[$priv] = false;
			}
			if(!is_array($initialGrant)) {
				$initialGrant = func_get_args();
			}
			$this->grantList($initialGrant);
		}

		public function grant($priv) {
			$this->rights[$priv] = true;
		}

		public function revoke($priv) {
			$this->rights[$priv] = false;
		}

		public function grantList(array $privs) {
			foreach($privs as $priv) {
				$this->grant($priv);
			}
		}

		public function revokeList(array $privs) {
			foreach($privs as $priv) {
				$this->revoke($priv);
			}
		}

		public function getAssoc() {
			return $this->rights;
		}

		public function getGranted() {
			return array_keys(array_filter($this->rights));
		}

		static public function ls() {
			return static::$privileges;
		}

		static public function parseFromMySQLRecord($row) {
			$privileges = array();
			foreach(static::$privileges as $priv) {
				if($row[$priv .'_priv'] == 'Y') {
					$privileges[] = $priv;
				}
			}
			return new static($privileges);
		}
	}

	class DBPrivileges extends Privileges {
		static $privileges = array('Select', 'Insert', 'Update', 'Delete', 'Create', 'Drop', 'Grant', 'References', 'Index', 'Alter', 'Create_tmp_table', 'Lock_tables', 'Execute', 'Create_view', 'Show_view', 'Create_routine', 'Alter_routine', 'Event', 'Trigger');
	}

	class UserPrivileges extends Privileges {
		static $privileges = array('Select', 'Insert', 'Update', 'Delete', 'Create', 'Drop', 'Reload', 'Shutdown', 'Process', 'File', 'Grant', 'References', 'Index', 'Alter', 'Show_db', 'Super', 'Create_tmp_table', 'Lock_tables', 'Execute', 'Repl_slave', 'Repl_client', 'Create_view', 'Show_view', 'Create_routine', 'Alter_routine', 'Create_user', 'Event', 'Trigger');
	}
?>
