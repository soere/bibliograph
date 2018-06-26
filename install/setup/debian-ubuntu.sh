#!/usr/bin/env bash
# General packages
apt-get update && apt-get install -y \
  wget curl zip \
  build-essential

# Apache / PHP
apt-get install -y \
  apache2 mysql-server \
  php7.0 php7.0-dev php-pear \
  php7.0-cli php7.0-ldap php7.0-curl php7.0-gd \
  php7.0-intl php7.0-json php7.0-mbstring \
  php7.0-mcrypt php7.0-mysql php7.0-opcache \
  php7.0-readline php7.0-xml php7.0-xsl php7.0-zip

# YAZ
sudo apt-get install -y yaz libyaz4-dev bibutils
pear channel-update pear.php.net && \
  yes $'\n' | pecl install yaz && \
  pear install Structures_LinkedList-0.2.2 &&
  \pear install File_MARC

sudo service apache2 restart
if php -i | grep yaz --quiet && echo '<?php exit(function_exists("yaz_connect")?0:1);' | php ; then echo "YAZ is installed"; else echo "YAZ installation failed"; exit 1; fi;

# MySQL
service mysql start
mysql -e 'CREATE DATABASE IF NOT EXISTS bibliograph;'
mysql -e "DROP USER IF EXISTS 'bibliograph'@'localhost';"
mysql -e "CREATE USER 'bibliograph'@'localhost' IDENTIFIED BY 'bibliograph';"
mysql -e "GRANT ALL PRIVILEGES ON bibliograph.* TO 'bibliograph'@'localhost';"