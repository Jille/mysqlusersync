<?php
	require('functions.lib');
	require('config.lib');

	$out = array();

	foreach($servers as $server => $info) {
		$out[$server] = array('users' => array(), 'exceptions' => array());

		$db = mus_connect($server, $info['user'], $info['password']);
		$users = mus_getPrivilegesFromMySQL($db);

		foreach($users as $username => $userhosts) {
			$privileges = array();
			$passwords = array();

			foreach($userhosts as $host => $user) {
				$passwords[] = $user->password;
				foreach($user->privileges as $db => $dbprivs) {
					if(!isset($privileges[$db])) {
						$privileges[$db] = new DBPrivileges();
					}
					$privileges[$db]->grantList($dbprivs->getGranted());
				}
			}
			$passwords = array_count_values($passwords);
			arsort($passwords);
			$passwords = array_keys($passwords);

			if(count($out[$server]['users']) > 0) {
				$out[$server]['users'][] = '';
			}

			$privlist = implode(', ', array_map('asphp', $privileges['*']->getGranted()));
			$out[$server]['users'][] = '			$users['. asphp($username) .'] = new User('. asphp($username) .', '. asphp(array_keys($userhosts)) .', '. asphp($passwords[0]) .', new UserPrivileges('. $privlist .'));';

			foreach($privileges as $db => $dbprivs) {
				if($db == '*') {
					continue;
				}
				$privlist = implode(', ', array_map('asphp', $dbprivs->getGranted()));
				$out[$server]['users'][] = '			$users['. asphp($username) .']->addPrivileges('. asphp($db) .', new DBPrivileges('. $privlist .'));';
			}

			foreach($userhosts as $host => $user) {
				if($user->password != $passwords[0]) {
					$out[$server]['exceptions'][] = '			$hostusers['. asphp($username) .']['. asphp($host) .']->password = '. asphp($user->password) .';';
				}
				foreach($privileges as $db => $dbprivs) {
					if(isset($user->privileges[$db])) {
						if($diff = array_diff($dbprivs->getGranted(), $user->privileges[$db]->getGranted())) {
							$out[$server]['exceptions'][] = '			$hostusers['. asphp($username) .']['. asphp($host) .']->privileges['. asphp($db) .']->revokeList('. asphp(array_values($diff)) .');';
						}
					} else {
						$out[$server]['exceptions'][] = '			$hostusers['. asphp($username) .']['. asphp($host) .']->removePrivileges('. asphp($db) .');';
					}
				}
			}
		}
	}

	$code = array();
	$code[] = 'function config_getUsers($server) {';
	$code[] = '	$users = array();';
	$code[] = '	switch($server) {';
	foreach($out as $server => $serverout) {
		$code[] = '		case '. asphp($server) .':';
		$code = array_merge($code, $serverout['users']);
		$code[] = '			break;';
	}
	$code[] = '	}';
	$code[] = '	return $users;';
	$code[] = '}';

	$code[] = '';

	$code[] = 'function config_handleHostExceptions($server, &$hostusers) {';
	$code[] = '	switch($server) {';
	foreach($out as $server => $serverout) {
		$code[] = '		case '. asphp($server) .':';
		$code = array_merge($code, $serverout['exceptions']);
		$code[] = '			break;';
	}
	$code[] = '	}';
	$code[] = '}';

	print(implode("\n", $code) ."\n");
?>
