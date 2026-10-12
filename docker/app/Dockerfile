# コンテナのベースとしてamazonlinux:2023を指定
FROM amazonlinux:2023

RUN dnf -y update

RUN dnf -y install unzip \
wget

# PHP 8.4 系とモジュール関連をインストール
RUN dnf -y install less \
php8.4 php8.4-intl \
php8.4-cli php8.4-common php8.4-devel php8.4-fpm \
php8.4-gd php8.4-mysqlnd php8.4-mbstring php8.4-pdo php8.4-xml

# unix socket
RUN mkdir /var/run/php-fpm
VOLUME [ "/var/run/php-fpm" ]

EXPOSE 9000

ENTRYPOINT /usr/sbin/php-fpm -F

# composerインストール
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN composer self-update

# Node.js 26 のインストール（GitHub Actions とバージョンを統一）
RUN curl -fsSL https://rpm.nodesource.com/setup_26.x | bash -
RUN dnf -y install nodejs

# コンテナのディレクトリ指定
WORKDIR /var/www/html
