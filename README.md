
# olobase-php

Olobase 3.x Php Backend


## ModuleName

Installing a module

```sh
php bin/console.php module:install --name="ModuleName" --env=local
```

Installing Specific Version

```sh
php bin/console.php module:install --name="ModuleName" --env=local --v=1.0.1
```

Removing a module

```sh
php bin/console.php module:remove --name="ModuleName" --env=local
```


## Open API - Swagger

Swagger shortcut commands to build and debug swagger schemas.

```sh
composer swagger
```

Debug all files for syntax errors

```sh
composer swagger-debug
```


