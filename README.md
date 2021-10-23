# CMR Staging API

## Instructions

To start the solution you can, being located in the project directory, run:

```shell
./run.sh
```

That's all! this command will start the solution using docker-compose and will run the tests!

## The services

Besides the staging API, the repository contains additional services to complement the solution. Here is a list of the
services and an explanation of them:

service|mapped port|description -|-|- staging|8000|The main service. This is the staging API. core_mock|-|This is the
mock of the core API used for testing and development integration_test|-|This is a small application used only for
running the integration tests and is stopped by the run.s script after that swagger-ui|8001|a swagger service with the
definition of the staging API

## About the development

The Staging API was developed using Slim Framework and following onion architecture. Here is an explanation of the
directories:

### public

It's the directory served by apache and only contains a very simple `index.php`.

### config

This directory contains basic files used to start and configure the app. Here you can find the routing, dependencies
injection, middlewares registering and a class which provide some test tokens.

### infrastructure

After config, this is the most external layer, here you can find request handling middlewares and some adapter for
interfaces defined inside the application.

#### Middlewares

See [slim documentation](https://www.slimframework.com/docs/v4/concepts/middleware.html) for more details.

#### Adapters

Adapters that allow communicating with external services (supposing external even the configured tokens).

### app

Here are the files that belong to the application itself. It contains the core files, and also the controllers and
consumers.

#### Controllers

The application controllers

#### Consumers

The classes used to consume external resources

#### Security

Here is where the token validation occurs

### Core

Contains the business logic and domain entities.

#### Business

Business logic files.

##### UseCases

Business logic executed by the controllers

##### Repositories

Interface of the repositories used to consume external resources

#### Entities

Domain entities.

### tests

Unit tests, they replicate the files structure of the rest of the application.