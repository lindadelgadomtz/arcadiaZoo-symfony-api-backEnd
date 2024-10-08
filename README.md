# Arcadia Zoo API
** Welcome to the Arcadia Zoo API! This project is an API for managing various entities of a zoo, built with Symfony. The API allows users to create, read, update, and delete records for animals, habitats, races, images, veterinary reports, roles, and services. **

## Table of Contents

- Installation
- Usage
- API Endpoints
- Entity Details
- License

### Installation

- Prerequisites:
    - PHP 8.0 or higher
    - Composer
    - Symfony CLI (optional but recommended)
    - MySQL or any other database supported by Doctrine ORM

- Steps

    - Clone the repository:
```bash
$ git clone https://github.com/lindadelgadomtz/arcadiaZoo-symfony-api-backEnd.git 
```
    - Install dependencies:
```bash
$ composer install
```
    - Set up the database:
    Create a .env.local file and configure your database connection:
```bash

$ DATABASE_URL="mysql://user:password@tj5iv8piornf713y.cbetxkdyhwsb.us-east-1.rds.amazonaws.com:3306/r1kou6d4xdy6q9er"

```
     - Create the database and run migrations:
```bash
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate
$ php bin/console doctrine:fixtures:load


```
     - Run the Symfony server:
```bash
$ symfony server:start
```
     - Or using the built-in PHP server:
```bash
$ php -S localhost:8000 -t public
```

### Usage
Use tools like Postman or Curl to interact with the API. Below are the available endpoints.

To see all available routes, services... :

```bash
$ bin/console debug:router
$ bin/console debug:container
$ bin/console debug:...
```

### Entity Details

### Charte Graphique 
https://drive.google.com/file/d/1pPi4ywtTDsGDimJgsyypTToWovPrzXjR/view?usp=drive_link 

### Gestion de Projet
https://drive.google.com/file/d/11YmszxKrjBtHI65CgrmEwnlglsJjv3Mz/view?usp=drive_link

### License 
This project is licensed under the MIT License. See the LICENSE file for more details. 


This README file provides a comprehensive guide for users and developers to understand, set up, and use the Arcadia Zoo API project. Adjust the structure and details according to the actual implementation of your API.
