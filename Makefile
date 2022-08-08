SHELL:=/bin/bash

HTTP_PORT=80
HTTPS_PORT=443
POSTGRES_PORT=5432

MAKE = make --no-print-directory
# Executables (local)
DOCKER_COMP = docker-compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec app

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console

# Misc
.DEFAULT_GOAL = help

## —— 🎵 🐳 The Symfony-docker Makefile 🐳 🎵 ——————————————————————————————————
.PHONY: help
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
.PHONY: build
.SILENT: build
build: ## Builds the Docker images
	set -e; \
	echo "";
	echo -e "\e[94mStep 2: Building the images\e[0m"; \
	echo -e "\e[94m-------------------------------------\e[0m\n"; \
	$(DOCKER_COMP) build --pull --no-cache; \
	echo -e "\e[94m-------------------------------------\e[0m\n"; \
	echo -e "[x] \e[92mBuild ok !\e[0m"
.PHONY: up
.SILENT: up
up: ## Start the docker hub in detached mode (no logs)
	set -e; \
	echo "";
	echo -e "\e[94mStep 2: Running the images\e[0m"; \
	echo -e "\e[94m-------------------------------------\e[0m\n"; \
	$(DOCKER_COMP) up --detach; \
	echo -e "\e[94m-------------------------------------\e[0m\n"; \
	echo -e "[x] \e[92mUp ok !\e[0m"

.PHONY: start
start: build up ## Build and start the containers

.PHONY: down
down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

.PHONY: logs
logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

.PHONY: sh
sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

.PHONY: composer
composer: ## Install dependencies
	@$(COMPOSER) install
## —— Doctrine 🎵 ———————————————————————————————————————————————————————————————
.PHONY: db-reset
db-reset: ## Reset database
	@$(SYMFONY) doctrine:database:drop --force --if-exists -nq
	@$(SYMFONY) doctrine:database:create -nq
	@$(SYMFONY) doctrine:migrations:migrate -nq
	@$(SYMFONY) hautelook:fixtures:load --no-bundles --no-interaction
	@$(SYMFONY) app:migrate:anon

## —— PHP-CS-Fixer 🎵 ———————————————————————————————————————————————————————————————
.PHONY: php-cs-fixer
php-cs-fixer: ## Fix PHP code style
	@$(PHP_CONT) vendor/bin/php-cs-fixer fix

## —— PHPUnit 🎵 ———————————————————————————————————————————————————————————————
.PHONY: phpunit
phpunit: ## Run tests
	@$(PHP_CONT) bin/phpunit

## —— PHPUnit 🎵 ———————————————————————————————————————————————————————————————
.PHONY: phpunit-coverage
phpunit-coverage: ## Run tests with coverage
	docker-compose exec -T php bin/phpunit --coverage-clover cover.xml

## -- TcpChecks --------------------------------------------------------------
.PHONY:assert-port-number
.SILENT: assert-port-number
assert-port-number:
	if [ "${PORT_NUMBER}" = "" ]; then echo -e "\e[91mThe 'PORT_NUMBER' parameter is required.\e[0m"; exit -1; fi

define check_timeout
    timer=0; \
    $(1); do \
    timer=$$(expr $$timer + 1); \
    if [ "$$timer" = 60 ]; then \
        exit -1; \
    fi; \
    sleep 1; \
    done
endef

.PHONY: wait-service
.SILENT: wait-service
wait-service: assert-port-number
	set -e; \
	$(call check_timeout,  until $$(nc -vz 127.0.0.1 $$PORT_NUMBER) ); \
	exit 0;

.PHONY: wait-services
.SILENT: wait-services
wait-services:
	set -e; \
	echo -e "\e[38;5;74;4;1mAsserting ports are ready:\e[0m"; \
	$(MAKE) wait-service PORT_NUMBER=${HTTP_PORT}; \
	$(MAKE) wait-service PORT_NUMBER=${POSTGRES_PORT}; \

## -- System requirements ------------------------------------------------------------

.PHONY:check-docker
.SILENT: check-docker
check-docker:
	set -e; \
	echo -e "\e[38;5;74;4;1mDOCKER:\e[0m"; \
	echo -e "=> \e[2mChecking if Docker is installed ...\e[0m \n"; \
	docker -v > /dev/null; \
	if [ $$? -ne 0 ]; then \
	  echo -e "\e[91mDocker is not installed on your machine ...\e[0m" ; \
	  echo -e "\e[38;5;203m---> Follow the installation instructions at \e[34mhttps://docs.docker.com/get-docker\e[38;5;203m for most OS or at \e[34mhttps://wiki.archlinux.org/title/Docker\e[38;5;203m for Archlinux.\e[0m" ; \
	  exit -1; \
	fi;
	echo -e "[x] \e[92mDocker seems present on this machine\e[0m\n"

.PHONY: check-compose
.SILENT: check-compose
check-compose:
	set -e; \
	echo -e "\e[38;5;74;4;1mDOCKER-COMPOSE:\e[0m"; \
	echo -e "=> \e[2mChecking if Docker-Compose is installed ...\e[0m \n"; \
	docker-compose -v > /dev/null; \
	if [ $$? -ne 0 ]; then \
	  echo -e "\e[91mDocker Compose is not installed on your machine ...\e[0m" ; \
	  echo -e "\e[38;5;203m---> You can see how to install it at \e[34mhttps://docs.docker.com/compose/install\e[0m" ; \
	  exit -1; \
	fi;
	echo -e "[x] \e[92mDocker-compose seems present on this machine\e[0m\n"


.PHONY:check-port
.SILENT: check-port
check-port: assert-port-number
	echo -e "=> \e[2mChecking if port: ${PORT_NUMBER} is in use ...\e[0m\n"; \
	PORT_COUNT=$$(ss -tuln | grep -c $$PORT_NUMBER); \
	if [ $$PORT_COUNT -ne 0 ]; then \
	  echo -e "\e[91mPort number ${PORT_NUMBER} is already in use\e[0m" ; \
	  exit -1; \
	fi;
	echo -e "[x] \e[92mPort ${PORT_NUMBER} seems free\e[0m\n"

.PHONY:check-ports
.SILENT: check-ports
check-ports:
	set -e; \
	echo -e "\e[38;5;74;4;1mPORTS:\e[0m"; \
	$(MAKE) check-port PORT_NUMBER=${HTTP_PORT}; \
	$(MAKE) check-port PORT_NUMBER=${POSTGRES_PORT}; \

.PHONY: check-requirements
.SILENT: check-requirements
check-requirements:
	set -e; \
	echo "";
	echo -e "\e[94mStep 1: Checking requirements\e[0m"; \
	echo -e "\e[94m-------------------------------------\e[0m\n"; \
	$(MAKE) check-docker || exit -1; \
	$(MAKE) check-compose || exit -1; \
	$(MAKE) check-ports || exit -1; \
	echo -e "\e[94m-------------------------------------\e[0m\n"; \
	echo -e "[x] \e[92mRequirements are ok !\e[0m"

## -- First time install ---------------------------------------------------------------
.PHONY: install
install: check-requirements build up wait-services composer db-reset
