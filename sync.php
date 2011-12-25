<?php
	require('functions.php');
	require('config.php');

	foreach($servers as $server => $info) {
		$out[$server] = array('global' => array());
		$db = mus_connect($server, $info['user'], $info['password']);
		$dbusers = mus_getPrivilegesFromMySQL($db);
		$cfgusers = config_getUsers($server);

		$hostusers = array();
		foreach($cfgusers as $user) {
			foreach($user->hosts as $host) {
				$hostusers[$user->user][$host] = $user->cloneForHosts(array($host));
			}
		}

		config_handleHostExceptions($server, $hostusers);

		// sync
		$queries = array();

		foreach($hostusers as $userhosts) {
			foreach($userhosts as $host => $user) {
				if(!isset($dbusers[$user->user][$host])) {
					$mysqlprivs = array();
					if($user->password == '') {
						$passpart = '';
					} elseif($user->password[0] == '*') {
						$passpart = " IDENTIFIED BY PASSWORD '". addslashes($user->password) ."'";
					} else {
						$passpart = " IDENTIFIED BY '". addslashes($user->password) ."'";
					}
					$queries[] = "CREATE USER '". addslashes($user->user) ."'@'". addslashes($host) ."'". $passpart .";";
				} else {
					$mysqlprivs = $dbusers[$user->user][$host]->privileges;
					if($dbusers[$user->user][$host]->password != $user->password) {
						if($user->password == '') {
							$passpart = "''";
						} elseif($user->password[0] == '*') {
							$passpart = "'". addslashes($user->password) ."'";
						} else {
							$passpart = "PASSWORD('". addslashes($user->password) ."')";
						}
						$queries[] = "SET PASSWORD FOR '". addslashes($user->user) ."'@'". addslashes($host) ."' = ". $passpart .";";
					}
				}

				foreach($user->privileges as $db => $cfgprivs) {
					if(isset($mysqlprivs[$db])) {
						$diff = array_diff($cfgprivs->getGranted(), $mysqlprivs[$db]->getGranted());
					} else {
						$diff = $cfgprivs->getGranted();
					}
					if($diff) {
						$grantpart = '';
						if(in_array('Grant', $diff)) {
							$diff = array_diff($diff, array('Grant'));
							$grantpart = ' WITH GRANT OPTION';
						}
						$privs = implode(', ', $diff);
						$queries[] = "GRANT ". $privs ." ON ". addslashes($db) ." TO '". addslashes($user->user) ."'@'". addslashes($host) ."'". $grantpart .";";
					}
				}

				foreach($mysqlprivs as $db => $dbprivs) {
					if(isset($user->privileges[$db])) {
						$diff = array_diff($dbprivs->getGranted(), $user->privileges[$db]->getGranted());
					} else {
						$diff = $dbprivs->getGranted();
					}
					if($diff) {
						if(in_array('Grant', $diff)) {
							$diff = array_diff($diff, array('Grant'));
							$diff[] = 'GRANT OPTION';
						}
						$privs = implode(', ', $diff);
						$queries[] = "REVOKE ". $privs ." ON ". addslashes($db) ." FROM '". addslashes($user->user) ."'@'". addslashes($host) ."';";
					}
				}
			}
		}

		foreach($dbusers as $username => $userhosts) {
			foreach($userhosts as $host => $user) {
				if(!isset($hostusers[$user->user][$host])) {
					# $queries[] = "REVOKE ALL PRIVILEGES, GRANT OPTION FROM '". addslashes($user->user) ."'@'". addslashes($host) ."';";
					$queries[] = "DROP USER '". addslashes($user->user) ."'@'". addslashes($host) ."';";
				}
			}
		}

		if($queries) {
			$queries[] = 'FLUSH PRIVILEGES;';
		}

		print('-- '. $server ."\n");
		print(implode("\n", $queries) ."\n");
	}
?>
