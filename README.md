# Playlist-api

This PHP api manage an ordered playlist.

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
# How to use the API

if you are on MacOS / Windows : DOCKER_HOST is not set to localhost, so you will have to use your ip-machine instead of "localhost".
Here is how to get your ip-machine :
```
docker-machine ip default
```

The pattern of the API url is the following : localhost/action/action_name/id
* action_name parameter refers to the action needed
* id is an optionnal integer parameter and refers to a specific playlist

List of the action :

## get Data
### get all the playlists
```
curl -X GET http://192.168.99.100/action/getplaylist/
```
### get a specific playlist
```
curl -X GET http://192.168.99.100/action/getplaylist/1
```
### get all the videos
```
curl -X GET http://192.168.99.100/action/getvideos/
```
### get all the videos from a playlist
```
curl -X GET http://192.168.99.100/action/getplaylistvideos/1
```

## create / modify / delete data

### create a new playlist
```
curl -X POST --header "Content-Type: application/json" --data '{"name":"bestof_2015"}' http://192.168.99.100/action/createplaylist/
```
### delete a playlist
```
curl -X POST --header "Content-Type: application/json" --data '{"id_playlist":5}' http://192.168.99.100/action/deleteplaylist/
```
### update a playlist
```
curl -X POST --header "Content-Type: application/json" --data '{"id_playlist":1,"name":"bestof2018_volume2"}' http://192.168.99.100/action/updateplaylist/
```
### add a video to a playlist with position
```
curl -X POST --header "Content-Type: application/json" --data '{"id_video":5,"id_playlist":2,"placement":2}' http://192.168.99.100/action/addtoplaylist/
```
### move a video to new position inside a playlist
```
curl -X POST --header "Content-Type: application/json" --data '{"id_video":5,"id_playlist":2,"placement":3}' http://192.168.99.100/action/moveinplaylist/
```
### remove a video from a playlist
```
curl -X POST --header "Content-Type: application/json" --data '{"id_video":5,"id_playlist":2}' http://192.168.99.100/action/removefromplaylist/
```

# How it works

This Docker-application starts 3 services in containers : mysql, nginx and php7-fpm.

The file docker-compose.yml defines thoses services, networks and their volumes :

* nginx
  * the logs (access and error) are stored in ./etc/nginx/log/
  * the conf is located in ./etc/nginx/conf.d/default.conf
* php:7-fpm
  * the php files are located in ./web/
* mysql
  * the data is stored in ./data/db/mysql
  * the SQL files to execute on application start in ./data/sql-scripts/
  
At the application start, 3 SQL scripts are executed in order to :
  * create tables in a mysql "playlist" database ("playlist", "video" and "playlist_video" table to link a video to a playlist)
  * feed each table with rows
  * update user access and privileges
  
The PHP API is located in web/ folder :
```
├─── web
├────── index.php - core dispatcher of the api : dispatch tasks given the action in URL
├────── config
├──────────── database.php - class to init a new mysqli connection
├──────────── request.php - class to validate request parameters and return messages to user with HTTP status
├────── model
├──────────── playlist.php - contains properties and methods for "playlist" database queries
├──────────── video.php  contains properties and methods for "video" database queries
├────── controller
├──────────── playlist.php - controller to verify missing params and call methods of playlist model

When the user request the api : 
1. the core dispatcher (index.php) parse the url parameters (action and id), verify the POST parameters with the Request class (is each parameter known and allowed, is it empty, does it have the good pattern ?)
2. if the action required is known, and the parameters alright, the controller playlist is called with a specific method
3. each method of the playlist controller checks that all the required parameters are present (e.g. if we want to move a video, then 'id_video', 'id_playlist' and 'placement' parameters are required) and call a method in the model playlist
4. each method of the playlist model get, create, modify or delete informations about a playlist.

```
# troubleshooting

Try remove all unused containers, networks, images, and optionally, volumes :
```
docker system prune
```
You can also manually stop the containers with the following command :
```
docker stop nginx-container && docker stop playlistapi_php_1 && docker stop mysql-container
```
