# json
Json to object

# Code style, Static analyze, Tests
Need checkout tests branch

## Build
```
$ docker build -t test-json-image-php8.1 -f ./docker/Dockerfile-php8.1 ./docker
```

## Install
```
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 composer install
```

## Code style
```
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 php ./vendor/bin/phpcs --standard=PSR12 -p ./src ./tests
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 php ./vendor/bin/phpcbf --standard=PSR12 -p ./src ./tests
```

## Static analyze
```
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 php ./vendor/bin/phpstan analyze --level=5 ./src
```

## Tests
```
$ docker run -it --rm --name json-tests -v "$PWD":/usr/src/app -w /usr/src/app test-json-image-php8.1 php ./vendor/bin/phpunit --bootstrap ./vendor/autoload.php ./tests
```