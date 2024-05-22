include .env

.PHONY: help start status stop yarn cache-clear symfo mysql shell-web shell-php shell-mysql shell-solr shell-node site-update site-install docker-pull docker-remove docker-prune docker-rebuild docker-debug

default: help

help:  ## Display this help.
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

node_modules:
	@docker compose run --rm --no-deps node /node-install.sh

vendor:
	@docker compose run --rm --no-deps app /php-install.sh

start: ## Start containers.
	@echo "$(COLOR_LIGHT_GREEN)Starting up containers for $(PROJECT_NAME)...$(COLOR_NC)"
	@docker compose up -d
	@echo "$(COLOR_LIGHT_GREEN)Your application is now reachable at https://app.localtest.me$(COLOR_NC)"

install: ## Build images if needed and Install dependencies.
	@echo "$(COLOR_LIGHT_GREEN)Installing dependencies for $(PROJECT_NAME)...$(COLOR_NC)"
	@make node_modules vendor

status: ## Status of containers
	@docker compose ps -a

stop: ## Stop containers.
	@echo "$(COLOR_LIGHT_GREEN)Stopping containers for $(PROJECT_NAME)...$(COLOR_NC)"
	@docker compose stop

yarn: ## Execute yarn command on node container. e.g: make yarn encore dev
	@docker compose run --rm --no-deps -T node bash -c "yarn $(filter-out $@,$(MAKECMDGOALS))"

clean-cache: ## Clear Symfony cache
	@docker compose run --no-deps --rm app php bin/console cache:clear

clean: ## Clean Docker containers
	@docker compose run --no-deps --rm app php bin/console cache:clear
	@docker compose down
	rm -rf build || true

clean-all: ## Clean containers, volumes, local images and dependencies (node_modules, vendors...)
	@$(MAKE) clean-cache || true
	@docker compose down -v
	rm -rf node_modules vendor || true
	rm -rf public/assets || true
	rm -rf build || true
	source .env && docker image rm $${REPOSITORY}/$${IMAGE}:$(APP_VERSION) || true

symfo: ## Execute symfony console command on PHP container. e.g: make symfo "cache:clear"
	@docker compose run --rm -T app bash -c "bin/console $(filter-out $@,$(MAKECMDGOALS))"

mysql: ## Open a mysql-cli on MySQL container
	@docker compose exec -e LINES=$(tput lines) -e COLUMNS=$(tput cols) ${MYSQL_HOST} mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE}

shell-php: ## Open a command line in the PHP container.
	@docker compose exec -e LINES=$(tput lines) -e COLUMNS=$(tput cols) app bash

shell-mysql: ## Open a  command line in the MySQL container.
	@docker compose exec -e LINES=$(tput lines) -e COLUMNS=$(tput cols) ${MYSQL_HOST} bash

shell-node: ## Open a command line in the NodeJS container.
	@docker compose run -e LINES=$(tput lines) -e COLUMNS=$(tput cols) node bash

shell-traefik: ## Open a command line in the traefik container.
	@docker compose exec -e LINES=$(tput lines) -e COLUMNS=$(tput cols) traefik bin/sh

logs-php: ## Open a command line in the PHP container.
	@docker compose logs -f app

logs-mysql: ## Open a  command line in the MySQL container.
	@docker compose logs -f db

logs-node: ## Open a command line in the NodeJS container.
	@docker compose logs -f node

docker-pull: ## Update container images.
	@echo "$(COLOR_LIGHT_GREEN)Update containers images for $(PROJECT_NAME)...$(COLOR_NC)"
	@docker compose pull

docker-remove: ## Remove containers.
	@echo "$(COLOR_LIGHT_GREEN)Removing containers for $(PROJECT_NAME)...$(COLOR_NC)"
	@docker compose down

docker-prune: ## Remove containers, volumes and images.
	@echo "$(COLOR_LIGHT_GREEN)Removing containers, volumes and images for $(PROJECT_NAME)...$(COLOR_NC)"
	@docker compose down -v --rmi all

docker-rebuild: ## Rebuild all or the specified container and don't start it
	@make stop
	@docker compose up --no-start --no-deps --build $(filter-out $@,$(MAKECMDGOALS))

docker-debug: ## Rebuild and run in attached mode all containers or the specified one
	@make stop
	@docker compose up --no-deps $(filter-out $@,$(MAKECMDGOALS))

# https://stackoverflow.com/a/6273809/1826109
%:
	@:
