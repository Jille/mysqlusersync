#!/bin/sh

if [ -f config.php ]; then
	echo "$0: config.php already exists. Bailing out." >&2
	exit 1
fi

read -p "Server: " SERVER
read -p "Username: " DBUSER
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
sed 's/^/	/g' < config.php-current >> config.php
rm config.php-current
echo '?>' >> config.php
