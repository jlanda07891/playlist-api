# playlist-api
rest api to manage an ordered playlist

# How it works

This Docker-application starts 3 services in containers : mysql, nginx and php7-fpm.

The file docker-compose.yml defining thoses services, networks and their volumes :

* nginx
  * the logs (access and error) are stored in ./etc/nginx/log/
  * the conf is located in ./etc/nginx/conf.d/default.conf
* php:7-fpm
  * the php files are located in ./web/
* mysql
  * the data is stored in ./data/db/mysql
  * the SQL files to execute on application start in ./data/sql-scripts/
  
At the application start, 3 SQL scripts are executed in order to :
  * create tables
  * feed with rows
  * update user access and privileges
  
The PHP API is located in web/ folder :
```
├─── web
├────── index.php - core controller of the api : dispatch task given the method used (GET, POST, PUT and DELETE)
├────── config
├──────────── database.php - class to init a new mysql conll danection
├──────────── request.php - class to parse request parameters / return messages to user with HTTP status
├────── model
├──────────── playlist.php - contains properties and methods for "playlist" database queries
├──────────── video.php  contains properties and methods for "video" database queries
├────── controller - controller are called from index.php
├──────────── playlist.php - class to prepare data of playlist objects + needed methods (read, create, update and delete)
├──────────── video.php - same for video
```

# Run the application

First git clone the project and navigate to the project's root folder.
```
$ git clone https://github.com/jlanda07891/playlist-api.git playlist-api
$ cd ./playlist-api 
```

Then build the "php:7-fpm" image from the Dockerfile (this will install php extensions such as mysqli )
```
$ docker build -t php:7-fpm .
```

Now you can run the Docker application
```
$ docker-compose up -d
```

Check that the 3 needed containers are running (nginx-container, playlistapi_php_1 and mysql-container) :
```
$ docker ps
CONTAINER ID        IMAGE               COMMAND                  CREATED              STATUS              PORTS                               NAMES
a37f7ed5ef42        nginx               "nginx -g 'daemon of…"   About a minute ago   Up 4 seconds        0.0.0.0:80->80/tcp                  nginx-container
5b67899b006d        php:7-fpm           "docker-php-entrypoi…"   About a minute ago   Up 7 seconds        9000/tcp                            playlistapi_php_1
3aca8e447565        mysql               "docker-entrypoint.s…"   About a minute ago   Up 10 seconds       0.0.0.0:3306->3306/tcp, 33060/tcp   mysql-container
```
