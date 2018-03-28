BookRater
=========

A Symfony project created on November 2, 2017, 2:25 pm.

Project Setup
=============

Parameters
----------
copy parameters.yml.dist to config folder as parameters.yml

enter anything you like into jwt_key_pass_phrase in parameters.yml
e.g. <code>jwt_key_pass_phrase: reallysecretkey</code>

optionally you can also enter a <code>database_password</code>

Composer
--------

run '<code>composer install</code>' from command prompt in base project directory


Database
--------
from command prompt in base project directory, run following commands:

<code>mkdir var/db</code>

<code>bin/console doctrine:database:create</code>

<code>bin/console doctrine:schema:create</code>

<code>bin/console doctrine:fixtures:load</code>

Json Web Token Security
-----------------------
from command prompt in base project directory, run following commands:

<code>mkdir var/jwt</code>

<code>openssl genrsa -out var/jwt/private.pem -aes256 4096</code>

<strong>\* use value entered for jwt_key_pass_phrase when it asks for a key</strong>

<code>openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem</code>

Start App
---------
You're good to go, finally just run: 

<code>bin/console server:run</code>

and go to http://localhost:8000