
# olobase-php

Olobase 3.x Php Backend


## Modules

Installing a module

```sh
php bin/module.php --install="Authentication" --env=local
```

Removing a module

```sh
php bin/module.php --remove="Authentication" --env=local
```

## Migrations


### Migrate

```sh
php bin/module.php --migrate="Authentication" --env=local
```

Runs the Doctrine migration files in the src/Authentication/Migrations/ folder.


### Rollback

```sh
php bin/module.php --rollback="Authentication" --env=local
```

Rolls back the last executed migration file (Doctrine's rollback command also supports advanced arguments such as --allow-no-migration if you want).


### Migration All

```sh
php bin/module.php --migrate-all --env=local
```

If there is a Migrations/ folder in all module folders under src/, migrations for all of them are run in order.


## Open API - Swagger

Build swagger schemas

```sh
composer swagger-debug
```

Debug all files for syntax errors

```sh
composer swagger-debug
```
