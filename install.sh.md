

sudo apt install vim
sudo apt update -y && sudo apt upgrade -y
sudo apt-get install apache2
sudo systemctl start apache2 && sudo systemctl enable apache2
sudo a2enmod rewrite
sudo sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride[[:space:]]\+None/AllowOverride All/' /etc/apache2/apache2.conf
	

sudo apt install php8.3 -y
sudo apt install libapache2-mod-php php8.3-common php8.3-cli php8.3-mbstring php8.3-bcmath php8.3-fpm php8.3-mysql php8.3-zip php8.3-gd php8.3-curl php8.3-xml -y
sudo apt install git -y
ssh-keygen -t ed25519 -C "eguvenc@gmail.com"
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519
cat ~/.ssh/id_ed25519.pub

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"

sudo mv composer.phar /usr/local/bin/composer

sudo apt install redis-server
sudo systemctl enable redis-server
sudo apt install php-redis
sudo phpenmod redis


sudo apt update
sudo apt-get remove --purge mysql*
sudo apt-get autoremove
sudo apt-get autoclean
sudo apt install mysql-server
sudo systemctl enable mysql
sudo mysql_secure_installation

sudo awk '/\[mysqld\]/{print; print "character-set-server = utf8\ninit-connect='\''SET NAMES utf8'\''\ncollation-server = utf8_general_ci"; next}1' /etc/mysql/mysql.conf.d/mysqld.cnf | sudo tee /etc/mysql/mysql.conf.d/mysqld.cnf.tmp
sudo mv /etc/mysql/mysql.conf.d/mysqld.cnf.tmp /etc/mysql/mysql.conf.d/mysqld.cnf
sudo sed -i 's/^bind-address\s*=.*$/bind-address = 0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf
sudo systemctl restart mysql


mysql >

SET PERSIST sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

CREATE USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
flush privileges;

CREATE USER 'admin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Mbry8992@';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
flush privileges;


SELECT User, Host FROM mysql.user;


# laminas-cs

sudo apt remove -y php-codesniffer && \
composer global require squizlabs/php_codesniffer laminas/laminas-coding-standard && \
export PATH="$HOME/.config/composer/vendor/bin:$PATH" && \
echo 'export PATH="$HOME/.config/composer/vendor/bin:$PATH"' >> ~/.bashrc && \
source ~/.bashrc && \
mkdir -p ~/.config/sublime-text/Packages/User && \
cat > ~/.config/sublime-text/Packages/User/laminas-cs.sublime-build <<EOF
{
  "cmd": ["/home/$USER/.config/composer/vendor/bin/phpcs", "--standard=LaminasCodingStandard", "\$file"],
  "selector": "source.php",
  "working_dir": "\${file_path}"
}
EOF
