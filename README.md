Introduction
======

project build with symfony. Dependecy managers:
 - composer for php
 - bower for css/ js
 - npm for build tools
 
please ensure you have:
 - php installation (7.0 recommended)
 - configured your IDE with the PSE coding standard
 - composer & npm installed
 
this release includes:
 - gulp: build & minify frontend like css & js. Separate build process for admin (sonata) and frontend
 - doctrine: DAL
 - fosuserbundle: Login / Register
 - doctrine migrations: keep database consistent with multiple developers
 - doctrine fixtures: create sample data
 - sonata admin: CRUD for your database in no time!
 - 404templates: preconfigured error templates
 - deployer: deploy you application with 0 downtime!

create project
 - go to `https://gitlab.com/JKwebGmbH/symfony-sceleton/tags`, and download the newest version
 - extract the zip and put it where you have your repositories
 - replace all instances of `my_page` with your page name in the `app/Resources` folder
 - rename `my_project` to your project in `bower.json`, `composer.json` and `package.json`
 - go to `app/Resources/views/base.html.twig` and adapt to your needs
 - push a first time and check everything runs
 
after first pull, execute from project root:
 - `npm install` #installs npm dependencies
 - `npm install gulp -g` #installs gulp globally 
 - `composer install` #installs php dependencies
 - `gulp` #builds css / js files
 - `php bin/console assets:install` #builds sonata admin files
 - `php bin/console doctrine:migrations:migrate` #migrate db to newest version
 - `php bin/console doctrine:fixtures:load` #load sample data & user
 
if you're developing in the backend:
 - `php bin/console server:run` #starts the symfony server
 
if you're developing the frontend (css/js), execute afterwards:
 - `gulp watch` #serves as a proxy between the symfony server & the webpage displayed in the browser
 - go to the webpage display in the console from gulp, propably http://localhost:3000/
 - edit files in web/assets/sass or web/assets/js, save them to see the change instantly in the browser
 - test error templates inside TwigBundle/views by accessing `/app_dev.php/_error/404` and `/app_dev.php/_error/500`
 
if you want to login as an admin you can use the user `info@jkweb.ch` with pass `asdf123` or create an admin for yourself:
 - go to /register to create a new account if you have none already
 - call /logout to logout
 - execute `php bin/console fos:user:promote famoser ROLE_ADMIN`, replacing famoser with your own username
 - go to /login and authenticate
 
if you want to create Entities from an exiting db
 - `php bin/console doctrine:mapping:import --force AppBundle annotation`, replacing AppBundle with your own bundle name
 - `php bin/console doctrine:generate:entities AppBundle`, to generate boostrap code for getters & setters

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
    - `ssh-keygen -t rsa -b 4096 -C "info@jkweb.ch"` generate a new key
    - `eval $(ssh-agent -s)` start the ssh agent
    - `ssh-add ~/.ssh/id_rsa` add the new key
 - go to https://gitlab.com/jkweb/hamiltonPCN/deploy_keys and add your ssh key
 - point the web directory to `~/myurl.ch/ENV/current/web`
 - deploy!
 - you may want to check with `php bin/symfony_requirements` if your server does support symfony
 
setup by @famoser, happy to answer questions / take suggestions