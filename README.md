

## MB Support Bot
Бот предназнчен в помощь операторам тех поддержки когда не совсем удобно заходить в админ панель


### Возможности:
 - поиск абонента по логин/договор/uid
 - просмотр базовой информации по абоненту
 - просмотр истории платежей
 - просмотр истории сессий
 - просмотр услуг
 - вход в ЛК 
 
![png image](https://github.com/kagatan/mb-support-bot/blob/master/resources/img/image.png?raw=true)

### 1. Установка

Устанвливаем пакеты и зависимости
```shell script
cd /var/www/
git clone https://github.com/kagatan/mb-support-bot.git
cd mb-support-bot

composer install

# даем права
sudo chown -R www-data:www-data /var/www/mb-support-bot
sudo chmod -R 775 /var/www/mb-support-bot/storage/

```

### 2. Nginx 

создаем конфиг на публичную диреторию
/var/www/mb-support-bot/public

в идеале вынести на отдельный поддомен, и указать его в конфиге APP_URL
для вебхука телеграма обязателен валидный сертификат
  
p.s. необходима если будет использовать вебхук

```shell script
...

   location ~ /\.git {
  	    deny all;
   }

   location / {
        root   /var/www/mb-support-bot/public;
        index  index.php;
        try_files $uri $uri/ /index.php?$args;
   }

   location ~ \.php$ {
      include /etc/nginx/fastcgi_params;
      fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
      fastcgi_index index.php;
      fastcgi_param SCRIPT_FILENAME /var/www/mb-support-bot/public$fastcgi_script_name;
   }

...

```

### 2.1 Apache

создаем конфиг на публичную диреторию
/var/www/mb-support-bot/public


пример .htaccess
```shell script

<IfModule mod_rewrite.c>
<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

RewriteEngine On

RewriteCond %{REQUEST_FILENAME} -d [OR]
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ ^$1 [N]

RewriteCond %{REQUEST_URI} (\.\w+$) [NC]
RewriteRule ^(.*)$ public/$1

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule (.*) index.php
DirectoryIndex /public/index.php
</IfModule>

```
### 3. Настраиваем .env

Конфиг находится в корне диреткории ,файл .env

Необходимые к заполнению:

```shell script
APP_URL=https://my-domen.ru
TELEGRAM_BOT_TOKEN="11111:xxxxxxxxxxxx"
TELEGRAM_BOT_NAME="name_bot"
TELEGRAM_BOT_ALLOWED_ID="[1234345, 4789456]"

MIKBILL_CABINET_HOST="https://stat.my-domen.ru"
MIKBILL_HOST="https://admin.my-domen.ru"
MIKBILL_LOGIN=admin
MIKBILL_PASSWORD=admin

```

### 4. Webhook

Установить webhook
```php
php artisan telebot:webhook --setup
```

Удалить webhook
```php
php artisan telebot:webhook --remove
```

### 5. Long pooling

Запустить в режиме пулинга без вебхука.

Чтоб запустить необходимо сначала выполнить команду 
"удалить вебхук" если он установлен
```php
php artisan telebot:polling --all
```
