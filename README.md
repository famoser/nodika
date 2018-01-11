Introduction
======
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)
[![Travis Build Status](https://travis-ci.org/famoser/nodika.svg?branch=master)](https://travis-ci.org/famoser/nodika)
[![Code Climate](https://codeclimate.com/github/famoser/nodika/badges/gpa.svg)](https://codeclimate.com/github/famoser/nodika)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/0049282fe1b3437ba8321ec244a3ea93)](https://www.codacy.com/app/famoser/nodika)
[![Scrutinizer](https://scrutinizer-ci.com/g/famoser/nodika/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/famoser/nodika)

dependencies (you need this on your machine):
 - `xampp` https://www.apachefriends.org/de/index.html
 - `composer` https://getcomposer.org/download/
 - `npm` https://nodejs.org/en/download/
 - `yarn` https://yarnpkg.com/lang/en/docs/install/
 
ubuntu installation:
 - `sudo add-apt-repository ppa:ondrej/php`
 - `sudo apt-get update`
 - `sudo apt-get install php php-xml php-zip php-mbstring npm yard`

backend with symfony4, with the additional bundles:
 - `server` for a better symfony server
 - `doctrine` the database wrapper 
 - `migrations` to migrate between different versions of the database
 - `orm-fixtures` to generate sample data
 - `admin` for the admin
 - `annotation` to configure routes in the controller
 - `form` to easely manage html forms
 - `logger` who doesn't need logging?
 - `profiler` to measure performance
 - `mailer` to send mails via smtp
 - `apache-pack` for the .htaccess file
 - `phpunit-bridge` to run tests
 - `expression-language` for fancy expressions & annotations

using the following libraries:
 - `erusev/parsedown` to convert markup to html
 - `friendsofphp/php-cs-fixer` to fix code styling issues
  
frontend building tools:
 - `@symfony/webpack-encore` for the encore provided by symfony
 - `jquery` to simplify DOM access
 - `bootstrap-sass` bootstrap for basic css styling
 - `font-awesome` font with icons
 - `sass-loader node-sass` to enable the sass precompiler
 
after first pull, execute from project root:
 - `yarn install` installs npm dependencies 
 - `composer install` installs php dependencies
 - `yarn encore dev` builds css / js files
 - `php bin/console doctrine:fixtures:load` loads sample data & user
 
if you're developing in the backend:
 - `php bin/console server:run` #starts the symfony server
 
if you're developing the frontend (css/js), execute afterwards:
 - `gulp watch` #serves as a proxy between the symfony server & the webpage displayed in the browser
 - go to the webpage display in the console from gulp, propably http://localhost:3000/
 - edit files in web/assets/sass or web/assets/js, save them to see the change instantly in the browser
 - test error templates inside TwigBundle/views by accessing `/_error/404` and `/_error/500`
 
if you want to login as an admin
 - go to /login
 - use the user `info@nodika.ch` with pass `asdf1234`
 
if you've changed the Entities and need to adapt the database
 - `php bin/console doctrine:migrations:diff` to generate the migration class
 - `php bin/console doctrine:migrations:migrate` to migrate db to the newest version
 
if you want to deploy
 - rename `servers_template.yml` to `servers.yml`, correct entries
 - execute `php deployer.phar deploy ENVIRONMENT`, replacing `ENVIRONMENT` by ether `dev`, `testing` or `production` (defaults to `dev`) 
 - if you are on the dev branch fixtures are autoamtically applied
 - if you deploy the fist time to production may want to login with ssh and prepare the database data with `php bin/console doctrine:fixtures:load --fixtures=src/DataFixtures/Production -q`
 
if you're setting up deployment on a new server
 - `cat ~/.ssh/id_rsa.pub` to ensure you already have created an ssh key for yourself, if none:
    - `ssh-keygen -t rsa -b 4096 -C "info@famoser.ch"` generate a new key
    - `eval $(ssh-agent -s)` start the ssh agent
    - `ssh-add ~/.ssh/id_rsa` add the new key
 - add own ssh key to the server with `ssh-copy-id -i ~/.ssh/id_rsa.pub username@server.domain` 
 - connect to server with `ssh username@server.domain`
 - `cat ~/.ssh/id_rsa.pub` to display the sever ssh key, if none see above on how to create one
 - go to https://github.com/famoser/nodika/deploy_keys and add the server ssh key
 - point the web directory to `~/myurl.ch/ENV/current/web`
 - deploy!
 - you may want to check with `php bin/symfony_requirements` if your server does support symfony
