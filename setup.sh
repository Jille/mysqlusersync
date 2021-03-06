#!/bin/sh

if [ "$1" = "-f" ]; then
	shift
else
	if [ -f config.php ]; then
		echo "$0: config.php already exists. Bailing out." >&2
		exit 1
	fi
fi

if [ -z "$1" ]; then
	read -p "Server: " SERVER
else
	SERVER="$1"
fi
if [ -z "$2" ]; then
	read -p "Username: " DBUSER
else
	DBUSER=$2
fi
# As it will appear in plain text anyway
read -p "Password (plain text): " PASSWORD

echo '<?php' > config.php
echo '	$servers = array(' >> config.php
echo "		'$SERVER' => array(" >> config.php
echo "			'user' => '$DBUSER'," >> config.php
echo "			'password' => '$PASSWORD'," >> config.php
echo '		),' >> config.php
echo '	);' >> config.php
echo '' >> config.php
php genconfig.php >> config.php-current
OK=$?
sed 's/^/	/g' < config.php-current >> config.php
rm config.php-current
echo '?>' >> config.php

if [ "$OK" = "0" ]; then
	echo "Configuration seems to be generated in config.php"
else
	echo "There seems to be an error."
	cat config.php
	exit "$OK"
fi
