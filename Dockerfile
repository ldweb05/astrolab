FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libxml2-dev \
    libffi-dev \
    zip \
    unzip \
    git \
    wget \
    build-essential \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        dom \
    && docker-php-source extract \
    && docker-php-ext-install ffi \
    && docker-php-source delete \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN git clone --branch v2.10.03 --depth 1 \
    https://github.com/aloistr/swisseph.git /opt/swisseph/src && \
    cd /opt/swisseph/src && \
    gcc -g -Wall -fPIC -c sweph.c swephlib.c swejpl.c swemmoon.c swemplan.c swehouse.c swecl.c swedate.c swehel.c && \
    ar rcs libswe.a sweph.o swephlib.o swejpl.o swemmoon.o swemplan.o swehouse.o swecl.o swedate.o swehel.o && \
    gcc -O2 -Wall -o swetest swetest.c -L. -lswe -lm && \
    mkdir -p /usr/local/include/swisseph && \
    cp *.h /usr/local/include/swisseph/ && \
    cp libswe.a /usr/local/lib/ && \
    gcc -shared -o libswe.so sweph.o swephlib.o swejpl.o swemmoon.o swemplan.o swehouse.o swecl.o swedate.o swehel.o -lm && \
    cp libswe.so /usr/local/lib/ && \
    cp swetest /usr/local/bin/ && \
    ldconfig

RUN mkdir -p /opt/swisseph/ephe && \
    wget -q https://github.com/aloistr/swisseph/raw/master/ephe/seas_18.se1 -O /opt/swisseph/ephe/seas_18.se1 && \
    wget -q https://github.com/aloistr/swisseph/raw/master/ephe/semo_18.se1 -O /opt/swisseph/ephe/semo_18.se1 && \
    wget -q https://github.com/aloistr/swisseph/raw/master/ephe/sepl_18.se1 -O /opt/swisseph/ephe/sepl_18.se1

RUN echo 'ffi.enable=true' > /usr/local/etc/php/conf.d/ffi.ini

RUN a2enmod rewrite

ENV SWISSEPH_PATH=/opt/swisseph/ephe
