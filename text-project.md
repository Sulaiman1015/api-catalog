project api-catalog:

symfony new api-catalog --version="7.1" --webapp
cd api-catalog
composer require symfony/http-client
symfony console make:controller ProductController
symfony server:start/stop


others:
php bin/console cache:clear
composer dump-autoload
