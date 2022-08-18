[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FJacquesDurand%2Fprojet8-TodoList%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/JacquesDurand/projet8-TodoList/main)

[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=JacquesDurand_projet8-TodoList&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=JacquesDurand_projet8-TodoList)
[![Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=JacquesDurand_projet8-TodoList&metric=vulnerabilities)](https://sonarcloud.io/summary/new_code?id=JacquesDurand_projet8-TodoList)
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=JacquesDurand_projet8-TodoList&metric=bugs)](https://sonarcloud.io/summary/new_code?id=JacquesDurand_projet8-TodoList)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=JacquesDurand_projet8-TodoList&metric=coverage)](https://sonarcloud.io/summary/new_code?id=JacquesDurand_projet8-TodoList)


# Parcours OpenClassrooms: Développeur d'application PHP/Symfony

## Projet 8: Améliorer un projet existant

-----------------------------------------------

## Description

The goal of this project was to upgrade the existing MVP of a company named **ToDo&Co**.  
The company has been able to present a shortly-made project of a ToDoList application to
a group of investors, and now that the concept has been validated, we had to upgrade it to 
a full-grown modern application.

As a newly hired developer for this company, I had to work on the quality of the app, by
namely:
- Implementing new features
- Fix a few bugs
- Set up automated tests, which in turn enabled me to
  - Upgrade the version of the framework used
  - Upgrade the version of the language used


**NOTA BENE :**  
The back end has been realised in **PHP 8.1**, **Symfony 6.1** and **Twig**

## Table of contents

- [Installation](#Installation)
    - [Prerequisites](#Prerequisites)
        - [Git](#Git)
        - [Docker](#Docker)
        - [Docker-compose](#Docker-Compose)
    - [Clone](#clone)

- [Configuration](#configuration)
- [Getting started](#getting-started)
- [Docs](#docs)

## Installation

### Prerequisites

#### Git

To be able to locally use this project, you will need to install [Git](https://git-scm.com/) on your machine.  
Follow the installation instructions [here](https://git-scm.com/downloads) depending on your Operating system.

#### Docker

This project runs 3 separate applications each in their own containers:

1. The PostgreSql DataBase
2. The Nginx Server
3. The PHP/Symfony application itself

Each is based upon its own Docker Image.  
To have them run locally, you will need to install [Docker](https://www.docker.com/) on your machine.  
Follow the installation instructions [here](https://docs.docker.com/get-docker/) for most OS
or [here](https://wiki.archlinux.org/title/Docker) for Archlinux.

#### Docker Compose

As defined on the documentation:
> Compose is a tool for defining and running multi-container Docker applications.

Since it is our case in this project, we also chose to use compose to build the complete project.  
You can see how to install it [here](https://docs.docker.com/compose/install/)

### Clone

Move to the parent directory where you wish to clone the project.

```shell
git clone https://github.com/JacquesDurand/projet8-TodoList.git
```

Then move into the newly cloned directory

```shell
cd projet8-TodoList
```

## Configuration

This project relies on the use of environment variables, which act as *secrets*. These are for instance the database
connection information.  
To override the examples given in `.env` or `.env.dist`, create your local file:

```shell
cp .env.dist .env.local
```

Then open your newly created **.env.local** with [your favorite text editor](https://neovim.io/) and replace the different *"
CHANGEME"* values by your own.


## Getting Started

### Launching the project

Now that everything has been configured, let us get into it !  
Still at the root of **p7_oc_bilemo**, run :

```shell
make install
```
This will:
- Check that **Docker** and **Docker-Compose** are installed on your machine.
- Check that the necessary ports ( 80 and 443 for the web server, and 49154 for the database) are not already used.
- Build the Docker image.
- Start the containers.
- Wait for the containers to accept TCP connection.
- Create the database and fill it with fixtures.

If everything went fine, you should be able to navigate to [localhost](http://localhost:80) and start interacting with the API.

If not, please do not hesitate to [submit an issue](https://github.com/JacquesDurand/p7_oc_bilemo/issues/new) and I'll get
back to you *ASAP*.


| *Note*: |
|---------|
If somehow the database ran into an issue (sometimes the `make install` command is too fast for
the db container to be up already,
```shell
make db-reset
```
might help.

## Contributing

To contribute to the project, please read [the guide](CONTRIBUTING.md).

## Docs

[Visit the docs !](https://jacquesdurand.github.io/projet8-TodoList/)