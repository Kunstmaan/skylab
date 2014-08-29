#!/bin/bash -x

sh -c 'if [ "$TRAVIS_PHP_VERSION" != "hhvm-nightly" ]; then echo "" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini; fi;'
sh -c 'if [ "$TRAVIS_PHP_VERSION" != "hhvm-nightly" ] && [ $(php -r "echo PHP_MINOR_VERSION;") -le 4 ]; then echo "extension = apc.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini; fi;'

sudo locale-gen nl_BE.UTF-8 && sudo update-locale

sudo mkdir -p /home/projects
echo "USE mysql;\nUPDATE user SET password=PASSWORD('password') WHERE user='root';\nFLUSH PRIVILEGES;\n" | mysql -u root

sudo apt-get install nginx
sudo apt-get install php5-fpm

cat << EOF | sudo tee /etc/nginx/nginx.conf
worker_processes  1;

events {
    worker_connections  1024;
}

http {
    include       mime.types;
    default_type  application/octet-stream;

    gzip  on;

    server {
        listen       8080;

        root           /;

        location ~ \.php($|/) {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_script_name;
            include        fastcgi_params;
        }
    }
}

EOF

sudo service nginx restart