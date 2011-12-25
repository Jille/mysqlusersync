mysqlusersync - The goal of this tool is to simplify mysql user/privilege management.

There is a script `genconfig` which will connect to an existing MySQL-server and process the privilege tables into a mysqlusersync configfile.

Another script `sync` reads the config-file and updates the MySQL-server with those privileges. Currently it just prints the queries instead of executing them (issue #3).

The idea for this tool was conceived at [Hexon BV](http://www.hexon.cx/).
