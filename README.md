# BookRater

A Symfony project created on November 2, 2017, 2:25 pm.

## Local Project Setup

##### Parameters
- copy parameters.yml.dist to config folder as parameters.yml

- enter anything you like into jwt_key_pass_phrase in parameters.yml

e.g. `jwt_key_pass_phrase: reallysecretkey`

- optionally you can also enter a `database_password`

##### Database
From command prompt in base project directory, run following commands:

- `mkdir var/db`

- `bin/console doctrine:database:create`

- `bin/console doctrine:schema:create`

- `bin/console doctrine:fixtures:load`

##### Json Web Token Security
From command prompt in base project directory, run following commands:

- `mkdir var/jwt`

- **After executing the following command, use the value you entered for jwt_key_pass_phrase when it asks for a key**

- `openssl genrsa -out var/jwt/private.pem -aes256 4096`

- `openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem`

##### Composer

- run '`composer install`' from command prompt in base project directory

##### Start App
You're good to go, finally just run: 

`bin/console server:run`

and go to http://localhost:8000

## Useful Stuff
##### Host site
[rickcooper.ddns.net](https://rickcooper.ddns.net)

##### Default admin details (for checking the API etc.)

username: admin, password: admin

##### API Authentication
Bearer token authentication is required for any non-GET requests. To authorize:
 
- Use Basic auth on the token endpoint (padlock symbol) with a registered username and password, and copy the content of the "token" attribute from the response (shown in bold).

e.g.

`{
    "token": "`**`eyJhbGciOiJSUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZXhwIjoxNTIyNzUyMDY3LCJpYXQiOjE1MjI3NDg0Njd9.wdBjYv4fK5xLfNQRPvaR6KIDki0doGv94ijFHGC76JSYQaXINsJxVy9enbuGMGwHfZPn6dpJqX-XeUA8EmYAy5sF7u9C2G2DTNG7tHvX0-PJGjlGuCn9eDMA1Sg0yf6nr_PLLzYIz2_Wjx6N-GohDKiXwQogEZIDy8Fn5ieqRxCjMIN6zYj8GGZOQRaJ6zzfKUbDNLweVsAGbi2EemuSA9fLTQjOkNP6g2taecGl58GJXUZTQzKsY1StXt__nd6kJR1l08X8EJ2AIkMAKE8ZTAiuNQRCsJp1e_iXWtgxnWKcEpWsC3Y42beOW6K8LR75En3eWTH9wvbvduHHP9jSmYHd0hbjjZcXkixWuQ4UF_EYXgLjXEWwstnufsS_cRjmHwjyVEe43OweRL-KdujePz1L6i2CEN3A2BkD2sXQge3R2OO_4fffj3e0rinbenU33ii2As-vW-KJG0kQpKa_nVWusMfUqx1-LxKI8k-FJJJnZHoJVOrltPlwLwj8LRV8FbwGw-CNv2GZSvQF1oCmOlo6GQXctgebhdgjoq7R593bVbzo_V60usn_EwjsQACNp3MaTAKPHKVjJaC6CyYuIIPXMLiWJXZL1830eicEDNkjvmZ0U7Kma6eyrlDIzMS5nQ9OndC2-yVeSiFjyioQlOAUBvsRBz_1MmXHEDK6_FI`**`"
}`

- Logout from Basic auth and use Bearer auth with the content 'Bearer', followed by a space, followed by the copied token.

e.g.

`Bearer eyJhbGciOiJSUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZXhwIjoxNTIyNzUyMDY3LCJpYXQiOjE1MjI3NDg0Njd9.wdBjYv4fK5xLfNQRPvaR6KIDki0doGv94ijFHGC76JSYQaXINsJxVy9enbuGMGwHfZPn6dpJqX-XeUA8EmYAy5sF7u9C2G2DTNG7tHvX0-PJGjlGuCn9eDMA1Sg0yf6nr_PLLzYIz2_Wjx6N-GohDKiXwQogEZIDy8Fn5ieqRxCjMIN6zYj8GGZOQRaJ6zzfKUbDNLweVsAGbi2EemuSA9fLTQjOkNP6g2taecGl58GJXUZTQzKsY1StXt__nd6kJR1l08X8EJ2AIkMAKE8ZTAiuNQRCsJp1e_iXWtgxnWKcEpWsC3Y42beOW6K8LR75En3eWTH9wvbvduHHP9jSmYHd0hbjjZcXkixWuQ4UF_EYXgLjXEWwstnufsS_cRjmHwjyVEe43OweRL-KdujePz1L6i2CEN3A2BkD2sXQge3R2OO_4fffj3e0rinbenU33ii2As-vW-KJG0kQpKa_nVWusMfUqx1-LxKI8k-FJJJnZHoJVOrltPlwLwj8LRV8FbwGw-CNv2GZSvQF1oCmOlo6GQXctgebhdgjoq7R593bVbzo_V60usn_EwjsQACNp3MaTAKPHKVjJaC6CyYuIIPXMLiWJXZL1830eicEDNkjvmZ0U7Kma6eyrlDIzMS5nQ9OndC2-yVeSiFjyioQlOAUBvsRBz_1MmXHEDK6_FI`
