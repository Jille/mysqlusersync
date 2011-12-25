<?php
	require('privileges.php');
	require('user.php');

	foreach(UserPrivileges::ls() as $priv) {
		define('PRIV_'. strtoupper($priv), $priv);
	}

	function mus_connect($host, $user, $pass) {
		$connection = new MySQLi($host, $user, $pass);
		if($connection->connect_error) {
			throw new MUSException('Connection to '. $host .' failed: '. $connection->connect_error);
		}
		return $connection;
	}

	function mus_parsePrivilegeRow($row) {
		$privileges = array();
		foreach(mus_getPrivileges() as $priv) {
			$privileges[$priv] = ($row[$priv .'_priv'] == 'Y');
		}
		return $privileges;
	}

	function mus_getPrivilegesFromMySQL($connection) {
		$users = array();
		$res = $connection->query("SELECT * FROM mysql.user");
		while($row = $res->fetch_assoc()) {
			$users[$row['User']][$row['Host']] = User::createFromMySQLRecord($row);
		}

		$res = $connection->query("SELECT * FROM mysql.db");
		while($row = $res->fetch_assoc()) {
			$users[$row['User']][$row['Host']]->addDBPrivilegesFromMySQLRecord($row);
		}

		// XXX Add table en column specific privileges

		return $users;
	}

	class MUSException extends Exception {
	}
?>
