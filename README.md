# vending-machine

## To run the app

```
docker build .
docker exec -it  vending-machine_php_1 sh /usr/src/app/start.sh
```

## To run the tests
```
docker exec -it  vending-machine_php_1  /usr/src/app/vendor/bin/phpunit -v tests/
```
