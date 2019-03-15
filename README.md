# playlist-api
rest api to manage an ordered playlist

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
