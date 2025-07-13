
# olobase-php

Olobase 3.x Php Backend


## ModuleName

Installing a module

```sh
php bin/module.php --install="ModuleName" --env=local
```

Removing a module

```sh
php bin/module.php --remove="ModuleName" --env=local
```

## Migrations

Migration docs for doctrine migration.

### Migrate

Runs the Doctrine migration files in the src/ModuleName/Migrations/ folder.

```sh
php bin/module.php --migrate="ModuleName" --env=local
```

### Status

Shows the current, migrated and pending status of migrations for a specific module.

```sh
php bin/module.php --status=ModuleName --env=local
```

### Rollback

Rolls back the last executed migration file (Doctrine's rollback command also supports advanced arguments such as --allow-no-migration if you want).

## One Step Rollback

```sh
php bin/module.php --rollback="ModuleName" --env=local
```

## To

→ This command rolls back migrations up to the specified version.
→ However, the version specified with `to` remains active (`down()` is not executed).
→ If you want to roll back this version as well, you must use `--strict` parameter.

## Rollback Multiple Steps

```sh
php bin/module.php --rollback="ModuleName" --steps=3 --env=local
```

## Migrate To Specific Version

```sh
php bin/module.php --migrate="ModuleName" --to=Version20250707150000
```

## Rollback To Specific Version

```sh
php bin/module.php --rollback="ModuleName" --to=Version20250707150000
```

## Strict Rollback

If you want to strictly revert to version "20250707151000", you should use the `--strict` parameter. Using the strict parameter also runs the down method of the current version.

```sh
php bin/module.php --rollback=ModuleName --to=20250707151000 --strict --env=local
```

### Migration All

If there is a Migrations/ folder in all module folders under src/, migrations for all of them are run in order.

```sh
php bin/module.php --migrate-all --env=local
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
