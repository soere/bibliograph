Bibliograph Docker Image
========================

This is a preconfigured image of the web-based bibliographic data manager [Bibliograph](http://www.bibliograph.org) 
running in an Ubuntu container. It's an easy way to try the application out and see whether it 
provides what you need. The docker setup is simple and must NOT be used in production. 

The container uses the latest [release at GitHub](https://github.com/cboulanger/bibliograph/releases)

Building and running of the Image
---------------------------------

On Mac and Windows, use [Kitematic](https://kitematic.com/) to run the image.

On Linux, or if you like the command line, download and build the container with

```
sudo docker pull cboulanger/bibliograph
sudo docker build -t cboulanger/bibliograph .
```

If you just want to test the software, run

```
sudo docker run --rm -p 80:80 cboulanger/bibliograph
```

This will remove the container and its data when you shut down the process.

For a daemonized process, run

```
sudo docker run -d -p 80:80 cboulanger/bibliograph
```

Data persistence
----------------

If the data should be stored in an existing mysql server on the host, you can set the following environment variables:

```
docker run .... \
 -e "BIB_USE_HOST_MYSQL=yes" -e "BIB_MYSQL_USER=superuser" -e "BIB_MYSQL_PASSWORD=secret" \
 ...
```
Replace "superuser" and "secret" with the real username and password of a MySql user that
is allowed to log in from everywhere and has all privileges. You might have to create such a
user first in the database and you can safely delete this newly-created user afterwards. The
user account is only needed to create the database tables and a "bibliograph" user which
is allowed to log in only from the docker container's IP address.

Configuration and use
---------------------
You can log with the following credentials (username/password):

- user/user
- manager/manager
- admin/admin
