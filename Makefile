.PHONY: install test phpunit phpcs phpcbf phpstan coverage help

PHP_BIN := vendor/bin

install:
	composer install

test: phpunit phpcs phpstan

phpunit:
	$(PHP_BIN)/phpunit

phpcs:
	$(PHP_BIN)/phpcs

phpcbf:
	$(PHP_BIN)/phpcbf

phpstan:
	$(PHP_BIN)/phpstan

coverage:
	$(PHP_BIN)/phpunit --coverage-html coverage

help:
	@echo "Targets: install, test, phpunit, phpcs, phpcbf, phpstan, coverage"
