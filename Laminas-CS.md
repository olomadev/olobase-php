
# Php CS-Fixer & Laminas Coding Stardart Installation for SublimeText 4 Editor 

Remove old packages

```
sudo apt remove php-codesniffer
```

Install new packages with composer

```php
composer global require squizlabs/php_codesniffer
composer global require laminas/laminas-coding-standard
```

test it 

```sh
user@user:~$ /home/user/.config/composer/vendor/bin/phpcs -i

The installed coding standards are MySource, PEAR, PSR1, PSR2, PSR12, Squiz, Zend, LaminasCodingStandard, SlevomatCodingStandard, coding-standard and WebimpressCodingStandard
```

```sh
cd /home/user/.config/sublime-text/Packages/User
vim laminas-cs.sublime-build
```

Change "user" variable with your user name and copy&paste below the code in your laminas-cs.sublime-build file.

```json
{
  "cmd": ["/home/user/.config/composer/vendor/bin/phpcs", "--standard=LaminasCodingStandard", "$file"],
  "selector": "source.php",
  "working_dir": "${file_path}"
}
```

You can do this using sublime text ui 

```
Tools > Build System > New Build System
```

or

```sh
cd /home/user/.config/sublime-text/Packages/User
touch laminas-cs.sublime-build
```

via command line.
