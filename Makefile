install:
	composer install

phpunit:
	./vendor/bin/phpunit

phpcs:
	./vendor/bin/phpcs

phpstan:
	./vendor/bin/phpstan

test:
	make phpunit
	make phpcs
	make phpstan
