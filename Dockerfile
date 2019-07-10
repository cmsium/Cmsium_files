FROM php:7.2-fpm

RUN apt-get update

RUN apt-get install nano -y && \
    apt-get install openssl -y && \
    apt-get install libssl-dev -y && \
    apt-get install wget -y && \
    apt-get install libxml2-dev -y && \
    docker-php-ext-install -j$(nproc) opcache mbstring mysqli pdo_mysql xml

RUN cd /tmp && wget https://pecl.php.net/get/swoole-4.3.3.tgz && \
    tar zxvf swoole-4.3.3.tgz && \
    cd swoole-4.3.3  && \
    phpize  && \
    ./configure  --enable-openssl && \
    make && make install

RUN touch /usr/local/etc/php/conf.d/swoole.ini && \
    echo 'extension=swoole.so' > /usr/local/etc/php/conf.d/swoole.ini

RUN mkdir -p /application/config

WORKDIR /application

COPY . /application

VOLUME ["/application/config"]

EXPOSE 9501
CMD ["/usr/local/bin/php", "/application/server.php"]