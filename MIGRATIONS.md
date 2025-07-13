
# Migrations


# 🛠️ Doctrine Migrations Guide (v2)

This document explains how to run and manage **module-based Doctrine Migrations** using the custom `bin/module.php` CLI tool, fully compatible with **Doctrine Migrations 3.9+**.

---

## 🚀 Migrate

Runs migration files located at `src/ModuleName/src/Migrations/`.

```bash
php bin/module.php --module="ModuleName" migrations:migrate --env=local
````

💡 **Note:**
If this is the first migration run, Doctrine will automatically create the `migration_versions` table to track executed versions.

---

## 📊 Status

Shows current migration status for the specified module.

```bash
php bin/module.php --module="ModuleName" migrations:list --env=local
```

💡 **Note:**
Displays which migrations have been **executed** and which are **pending**.

---

## 🔙 Rollback (1 Step)

Rolls back the last executed migration (`down()` method is executed).

```bash
php bin/module.php --module="ModuleName" migrations:migrate --prev=true --env=local
```

💡 **Note:**
Only the most recent migration will be rolled back.
You can add `--no-interaction` to skip confirmation and `--ansi` for colored output.

---

## 🎯 Rollback to Specific Version

Rolls back to the specified version.
⚠️ **The specified version remains applied** (i.e., its `down()` is **not** called).

```bash
php bin/module.php --module="ModuleName" --to=Version20250707151000 --env=local
```

💡 **Note:**
This rolls back migrations **up to** but **not including** the given version.
This is useful for partial rollbacks or keeping that version active.

---

## 🔒 Strict Rollback (Version Included)

Rolls back **to the specified version and includes it** — i.e., the `down()` of the target version is also executed.

```bash
php bin/module.php --module="ModuleName" --to=Version20250707151000 --env=local --strict=true
```

💡 **Note:**
Use `--strict=true` to remove the target version as well.
Without this flag, the target version remains **applied**.

---

## 🧪 Examples

### Migrate a specific module

```bash
php bin/module.php --module="Users" migrations:migrate --env=local
```

### Rollback to Previous Version

```bash
php bin/module.php migrations:migrate --module="Users" --prev --env=local
```

### Rollback to Specific Version

```bash
php bin/module.php migrations:migrate  --module="Users" --to=Version20250707151000 --env=local
```

### Rollback to Specific Version "0" (Strict)

Rollback to head of the specified version (0).

```bash
php bin/module.php migrations:migrate --module="Users" --to=Version20250707151000 --env=local --strict
```

---

## ♻️ Migrate All Modules

Scans all `src/*` module folders and runs migrations for each, in order.

```bash
php bin/module.php --migrate-all --env=local
```

💡 **Note:**
Each module must have a `src/Migrations/` folder. If missing, that module will be skipped with a warning.


