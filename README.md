# GoodFood 2.0 - Auth Api


[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=goodfood-cesi_api-auth&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=goodfood-cesi_api-auth)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=goodfood-cesi_api-auth&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=goodfood-cesi_api-auth)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=goodfood-cesi_api-auth&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=goodfood-cesi_api-auth)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=goodfood-cesi_api-auth&metric=coverage)](https://sonarcloud.io/summary/new_code?id=goodfood-cesi_api-auth)
[![Duplicated Lines (%)](https://sonarcloud.io/api/project_badges/measure?project=goodfood-cesi_api-auth&metric=duplicated_lines_density)](https://sonarcloud.io/summary/new_code?id=goodfood-cesi_api-auth)
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=goodfood-cesi_api-auth&metric=bugs)](https://sonarcloud.io/summary/new_code?id=goodfood-cesi_api-auth)

API to manage authentication

## Technologies
![PHP](https://img.shields.io/badge/-PHP-05122A?style=flat&logo=php&logoColor=777BB4)
![JWT](https://img.shields.io/badge/JWT-05122A?style=flat&logo=JSON%20web%20tokens)
![MySQL](https://img.shields.io/badge/-MySQL-05122A?style=flat&logo=mysql&logoColor=4479A1)
![Docker](https://img.shields.io/badge/docker-05122A.svg?style=flat&logo=docker&logoColor=white)
![Kubernetes](https://img.shields.io/badge/kubernetes-05122A.svg?style=flat&logo=kubernetes&logoColor=white)
![Lumen](https://img.shields.io/badge/-Lumen-05122A?style=flat&logo=lumen&logoColor=FF2D20)

## Requierements:
MySQL Database

## Installation
Install dependencies:
```
composer install
```
Copy and complete .env file from .env.example:
```
cp .env .env.example
```

Generate JWT secret key:
```
php artisan jwt:secret
```
Copy and complete .env :
```
JWT_SECRET=<generated_key>
```
Run project:
```
php -S 0.0.0.0:8001 -t public
```

## Tests
Run tests:
```
./vendor/bin/phpunit
```
