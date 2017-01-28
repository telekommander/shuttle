# SHUTTLE | Slim 3 Kickstart

This is a simple kickstart project fork from 
akrabat/slim3-skeleton
based on
mrcoco/slim3-eloquent-skeleton
that includes 
scaffold tool, migrations, auth, Twig, Flash messages, eloquent ORM and Monolog.

## Features

* Description comming soon

### Create your project:

    $ composer create-project -n -s dev telekommander/shuttle yourapp

### ... or clone and run:

    $ composer install

### (Re)generate composer autoloader

    $ composer dump-autoload -o

#### Run it:

1. `$ cd yourapp`
2. Change database settings `config/config.json`
3. `$ php -S 0.0.0.0:8888 -t web web/index.php`
4. Browse to `http://localhost:8888` 
or
6. Browse to `http://localhost/yourapp/` without step 4 above

#### Key directories

* `app`: Application code
* `app/src`: All class files within the `App` namespace
* `storage/cache/twig`: Twig's autocreated cache files
* `storage/log`: Log files
* `web`: Webserver root
* `vendor`: Composer dependencies
* `web/views`: Twig template files
* `web/assets`: Frontend files

#### Key files

* `web/index.php`: Entry point to application
* `app/config.json`: Configuration
* `app/core/dependencies.php`: Services for Pimple
* `app/core/middleware.php`: Application middleware
* `app/core/routes.php`: All application routes are here
* `app/src/Action/HomeController.php`: Action class for the home page
* `app/src/Action/LoginControllerAction.php`: Action class for the login/logout page
* `web/views/home.twig`: Twig template file for the home page

##### Demo User:

1. `admin` username: `admin@slim.dev` password: `password` 
2. `moderator` username: `moderator@slim.dev` password: `password` 
3. `user` username: `user@slim.dev` password: `password` 
