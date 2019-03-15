GRANT ALL PRIVILEGES ON `playlist`.* TO `dev`@`%`;
ALTER USER 'dev'@'%' IDENTIFIED WITH mysql_native_password BY 'Technik2dev';
flush privileges;
