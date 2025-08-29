
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

After doing this, add the path ~/.composer/vendor/bin (~/.config/composer/vendor/bin for Ubuntu 24.04) to PATH:

```sh
export PATH="$HOME/.config/composer/vendor/bin:$PATH"
echo 'export PATH="$HOME/.config/composer/vendor/bin:$PATH"' >> ~/.bashrc
```


You can add this to your .bashrc or .zshrc file to make it permanent:

```sh
source ~/.bashrc
```

test it 

```sh
phpcs -i

# Example output:
# The installed coding standards are MySource, PEAR, PSR1, PSR2, PSR12, Squiz, Zend, LaminasCodingStandard, SlevomatCodingStandard, coding-standard and WebimpressCodingStandard
```

```sh
cd ~/.config/sublime-text/Packages/User
vim laminas-cs.sublime-build
```

Change "$USER" variable with your username and copy&paste below the code in your laminas-cs.sublime-build file.

```json
{
  "cmd": ["/home/$USER/.config/composer/vendor/bin/phpcs", "--standard=LaminasCodingStandard", "$file"],
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


Open a php file with sublime text. Click "Ctrl + B" shortcuts. Choose "laminas-cs" if the prompt open in a window.

