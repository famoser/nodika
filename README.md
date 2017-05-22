Introduction
======

project build with symfony. Dependecy managers:
 - composer for php
 - bower for css/ js
 - npm for build tools (gulp)
 
please ensure you have:
 - php installation (7.1 recommended)
 - configured your IDE with the PSE coding standard
 - composer & npm installed
 
this release includes:
 - gulp: build & minify frontend like css & js. Separate build process for admin (sonata) and frontend
 - doctrine: DAL
 - doctrine migrations: keep database consistent with multiple developers
 - doctrine fixtures: create sample data
 - 404templates: preconfigured error templates
 - deployer: deploy you application with 0 downtime!
 - custom login to replace bad FOSUserBundle
 
after first pull, execute from project root:
 - `npm install` #installs npm dependencies
 - `npm install gulp -g` #installs gulp globally 
 - `composer install` #installs php dependencies
 - `gulp` #builds css / js files
 - `reinit_dev.cmd` which executes:
	- `php bin/console doctrine:migrations:migrate` #migrate db to newest version
	- `php bin/console doctrine:fixtures:load` #load sample data & user
 
if you're developing in the backend:
 - `php bin/console server:run` #starts the symfony server
 
if you're developing the frontend (css/js), execute afterwards:
 - `gulp watch` #serves as a proxy between the symfony server & the webpage displayed in the browser
 - go to the webpage display in the console from gulp, propably http://localhost:3000/
 - edit files in web/assets/sass or web/assets/js, save them to see the change instantly in the browser
 - test error templates inside TwigBundle/views by accessing `/app_dev.php/_error/404` and `/app_dev.php/_error/500`
 
if you want to login as an admin
 - go to /login
 - use the user `info@nodika.ch` with pass `asdf1234`
 
if you've changed the Entities and need to adapt the database
 - `php bin/console doctrine:migrations:diff` to generate the changescript sql
 - `php bin/console doctrine:migrations:migrate` to migrate db to the newest version
 - optionally execute `php bin/console doctrine:generate:entities AppBundle:MyClass` to generate getter & setters
 
if you want to deploy
 - rename `servers_template.yml` to `servers.yml`, correct entries
 - execute `php deployer.phar deploy ENVIRONMENT`, replacing `ENVIRONMENT` by ether `dev`, `testing` or `production` (defaults to `dev`) 
 - you may want to login with ssh and prepare the database data with `php bin/console doctrine:fixtures:load --fixtures=src/AppBundle/DataFixtures/ORM/Production -q` (execute from active release root)
 
if you're setting up deployment on a new server
 - connect with an ssh agent (for example putty)
 - `cat ~/.ssh/id_rsa.pub` to display you ssh key, if you have none, follow the next 3 steps
    - `ssh-keygen -t rsa -b 4096 -C "info@famoser.ch"` generate a new key
    - `eval $(ssh-agent -s)` start the ssh agent
    - `ssh-add ~/.ssh/id_rsa` add the new key
 - go to https://github.com/famoser/nodika/deploy_keys and add your ssh key
 - point the web directory to `~/myurl.ch/ENV/current/web`
 - deploy!
 - you may want to check with `php bin/symfony_requirements` if your server does support symfony