FROM ubuntu:18.04
ENV TZ=Asia/Manila
ENV DEBIAN_FRONTEND=noninteractive
RUN 	ln -fs /usr/share/zoneinfo/Asia/Manila /etc/localtime
RUN 	apt-get update -y && \
	apt-get upgrade -y && \
	apt-get dist-upgrade -y && \
	apt-get install software-properties-common nginx php7.2 php7.2-fpm php7.2-curl php7.2-ldap php7.2-mysql php7.2-gd \
	php7.2-xml php7.2-mbstring php7.2-zip php7.2-bcmath mysql-client composer npm nodejs curl wget redis-server -y
RUN 	apt-get purge apache2 apache* -y
RUN 	npm cache clean -f
RUN 	npm install -g npm
RUN 	npm install -g n
RUN 	n stable
COPY . /home/
COPY Docker/config.nginx /etc/nginx/sites-enabled/config.nginx
COPY Docker/config.env /home/server/.env
RUN mkdir /home/server/storage/app/temp
RUN chmod +x /home/Docker/start
RUN cp -f /home/Docker/redis.conf /etc/redis/
RUN composer install -d /home/server
RUN npm install --prefix /home/client
RUN php /home/server/artisan key:generate
RUN php /home/server/artisan jwt:secret
RUN npm run build --prefix /home/client
RUN cp -rf /home/client/build/* /home/server/public/
RUN mv /home/server/public/index.html /home/server/resources/views/index.blade.php
RUN chmod 777 -R /home/
CMD /home/start
