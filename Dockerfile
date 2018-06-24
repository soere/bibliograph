# Bibliograph - Online Bibliographic Data Manager
# Build the latest GitHub master

FROM ubuntu:18.04
MAINTAINER Christian Boulanger <info@bibliograph.org>

# Environment variables for the setup
ENV DEBIAN_FRONTEND noninteractive
ENV DOCKER_RESOURCES_DIR=build/env/docker
ENV BIB_DIST_DIR=dist
ENV BIB_VAR_DIR /var/lib/bibliograph
ENV BIB_DEPLOY_DIR /var/www/html
ENV BIB_CONF_DIR /var/www/html/bibliograph/server/config/
ENV BIB_USE_HOST_MYSQL no
ENV BIB_MYSQL_USER root
ENV BIB_MYSQL_PASSWORD ""

# Packages
RUN apt-get update && apt-get install -y \
  bibutils wget zip \
  supervisor \
  apache2 libapache2-mod-php \
  mysql-server \
  php-{cli,intl,xsl,mbstring,mcrypt,mysql,zip,dev,pear} \
  yaz libyaz4-dev

# Install php-yaz
RUN pecl install yaz && \
  pear install Structures_LinkedList-0.2.2 && \
  pear install File_MARC && \
  echo "extension=yaz.so" >> /etc/php7/apache2/php.ini && \
  echo "extension=yaz.so" >> /etc/php7/cli/php.ini

# copy dist directory, remove unneeded files
COPY $BIB_DIST_DIR/* $BIB_DEPLOY_DIR
RUN rm /*.zip

# add configuration files
COPY $DOCKER_RESOURCES_DIR/app.conf.toml $BIB_CONF_DIR/app.conf.toml
COPY $DOCKER_RESOURCES_DIR/server.conf.php $BIB_CONF_DIR/server.conf.php

# supervisor files
COPY supervisord-apache2.conf /etc/supervisor/conf.d/supervisord-apache2.conf
COPY supervisord-mysqld.conf /etc/supervisor/conf.d/supervisord-mysqld.conf

# add mysqld configuration
COPY my.cnf /etc/mysql/conf.d/my.cnf

# Start command
COPY run.sh /run.sh
COPY start-apache2.sh /start-apache2.sh
COPY start-mysqld.sh /start-mysqld.sh

# Expose ports
EXPOSE 80 443

# Run
RUN chmod 755 /*.sh
CMD ["/run.sh"]
