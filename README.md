# Test Management App

## Overview

The Test Management App is a web application built with Laravel that allows users to manage tests. 
The goal of this project is to create a REST API for creating, editing, deleting, and viewing tests.

## Features
- 2 User roles: admin and manager
- User Authentication (Laravel Breeze)
- Create, Edit, Delete, and View actions for User, Test entities
- Authorization using policies with roles and permissions. I.e. managers can only update their tests
- Services and repositories as additional layers to fetch data from storage
- TDD (PHPUnit tests covered API and services)

## Installation and Setup

1. Requirements
   <br><br>
   Before starting work on the project, ensure that you have the following components installed:
    - PHP
    - Composer
    - nginx or apache (or use built-in Laravel server)
    - MySQL
    - Laravel
2. Clone the repository to your local machine:
   ```shell
   git clone https://github.com/aghorianducalis/task-management-app.git
   cd task-management-app
   ```
3. Set up environment:
   <br><br>
   Copy the `.env.example` to `.env` and set the environment values.

   Configure database access and other necessary parameters. Set up your database in the `.env` file:
   ```shell
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
   ```

4. Install composer dependencies:
    ```shell
    composer install
    ```
5. Run Laravel setup commands via artisan. I.e. generate an application key:
    ```shell
    php artisan key:generate
    php artisan storage:link
    ```
   
6. Run database migrations:
    ```shell
    php artisan migrate
    ```

7. Run the seeders:
   ```shell
   php artisan migrate:refresh --seed
   ```
   Run the seeder for roles and permissions:
   ```shell
   php artisan roles-permissions:sync
   ```

8. Start the development server:
   ```shell
   php artisan serve
   ```

Now, you can open a web browser and navigate to http://localhost:8000 to view your project.


## Usage

Once you have started the Artisan development server, your application will be accessible in your web browser at http://localhost:8000.

After installing and configuring the project, you can:

Register, log in, and log out.

## Authentication

The Test Management System includes user registration and authentication. Users can sign up, log in, and log out. Only authenticated users can create, edit, delete, and view their tests.

## Testing

The application includes PHPUnit tests to ensure functionality and authorization are working as expected. Run the tests with the following command:
   ```shell
   php artisan test
   ```

## Structure

### Roles and permissions

#### Package
We are utilizing a spatie/laravel-permission package that provides role and permission management.
Documantation: https://spatie.be/docs/laravel-permission/v5/basic-usage/basic-usage

#### Usage

A role has a set of permissions associated with it.

Every action is authorized through a permission (via role).

Roles and permissions are seeded into the database using seeder and/or a specific command. Their values are hardcoded.

When the values are updated, a command is executed.

There are no routes available for creating, editing, or deleting roles and permissions.

When creating a user, a role is assigned to them. In the UserService::create() method, the passed role is determined by the $data parameter, defaulting to 'admin'.

There are two roles: admin and manager.

#### Admin
Can perform all actions on User and Test entities.
Has access to routes related to users. For example, the admin can see a list of all users.
Has access to create, edit, or delete tests.

#### Manager
Utilizes the system and can access routes for viewing tests.
Has access only to their own test. For instance, the list of tests for a manager is limited to their own tests using appropriate filtering.
Can only edit the rate of their own tests.

### Authorization
Authorization is built on the use of roles and permissions. Authorization of actions through API routes is contained in the policy. Obtaining an authorized user and additional logic related to the user's role is contained in the controller. Then the controller delegates the execution of the logic to the corresponding service, passing to its method data specific to a particular user.

### Services and repositories
- Repositories only perform actions on corresponding entity. That means not related entities. I.e. UserRepository::delete() removes only User. Related Test are removed in their own repositories. Appropriate calls are done within service.
  <br>
- Services do not contain the auth logic. They act on User like abstract User. Authenticated User is retrieved on higher level (i. e. in controller or command). For example, controller gets the auth User and pass it as a parameter into the service.

## Security Vulnerabilities

If you discover a security vulnerability within application, please send an e-mail to developer via [aghorianducalis@gmail.com](mailto:aghorianducalis@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
