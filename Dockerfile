FROM ubuntu:18.04
ENV TZ=Asia/Manila
ENV DEBIAN_FRONTEND=noninteractive
RUN 	ln -fs /usr/share/zoneinfo/Asia/Manila /etc/localtime
RUN 	apt-get update -y && \
	apt-get upgrade -y && \
	apt-get dist-upgrade -y && \
	apt-get install software-properties-common nginx php7.2 gnumeric gnupg2 pass php7.2-fpm php7.2-curl php7.2-ldap php7.2-mysql php7.2-gd \
	php7.2-xml php7.2-mbstring php7.2-zip php7.2-bcmath mysql-client composer npm nodejs curl wget redis-server nano htop -y
RUN 	apt-get purge apache2 apache* -y
COPY . /home/
COPY Docker/config.nginx /etc/nginx/sites-enabled/config.nginx
COPY Docker/config.env /home/.env
RUN mkdir /home/storage/app/temp
RUN chmod +x /home/Docker/start
RUN chmod +x /home/Docker/nohup
RUN cp -f /home/Docker/redis.conf /etc/redis/
RUN composer install -d /home/
RUN php /home/artisan key:generate
RUN php /home/artisan jwt:secret
RUN chmod 777 -R /home/
CMD /home/Docker/start