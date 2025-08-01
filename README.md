
# olobase-php

Olobase 3.x Php Backend


php console.php module:install --module=ModuleName --env=local
php console.php module:remove --module=ModuleName --env=local



## ModuleName

Installing a module

```sh
php bin/module.php install --module="ModuleName" --env=local
```

Removing a module

```sh
php bin/module.php remove --module="ModuleName" --env=local
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


