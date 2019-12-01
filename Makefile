UID = $$(id -u)

all:
	cat Makefile

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose down
	docker-compose up -d

update: up
	docker-compose exec -u $(UID) php-cli composer update

stan: phpstan phpcs
	@echo "Running static analysis tools"

shell: cmd.sh up
	./cmd.sh attach-to-shell $(s) $(u)

phpstan: up
	@echo "Running phpstan"
	docker-compose exec -u $(UID) php-cli composer run-script phpstan

phpcs: up
	@echo "Running code sniffer"
	docker-compose exec -u $(UID) php-cli composer run-script phpcs

phpcbf: up
	docker-compose exec -u $(UID) php-cli composer run-script phpcbf

fix-composer-permissions: up
	docker-compose exec php-cli chown -R $(UID):$(UID) /composer