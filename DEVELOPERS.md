# Developers
useful tipps for development and its environment.

## Useful commands

##### symfony-cmd
`php bin/console server:run` to start the symfony server  
`doctrine:migrations:diff` to generate the migration class  
`doctrine:migrations:migrate` to execute all migrations  
`doctrine:fixtures:load` to load fixtures

##### cmd
`composer install` to install backend dependencies  
`yarn install && yarn encore dev` to install & build frontend dependencies  
`phpunit` to execute the unit tests  
`vendor/bin/php-cs-fixer fix` to fix code style issues  
`dep deploy` to deploy  

##### develop
login with `info@mangel.io`, `asdf`  
`yarn encode dev-server` starts the frontend dev server  
test error templates inside TwigBundle/views by accessing `/_error/404` and `/_error/500`

##### deploy
server must fulfil requirements of `composer.json`    
if you deploy the first time, while `deploy:composer` is running, set the `.env` file in `/shared/.env`  
do not forget to setup cronjobs as specified in the `CronJobController`  
 
##### ssh
`ssh-copy-id -i ~/.ssh/id_rsa.pub username@domain` to add ssh key  
`cat ~/.ssh/id_rsa.pub` to query the active ssh key  
`ssh-keygen -t rsa -b 4096 -C "username@domain" && eval $(ssh-agent -s) && ssh-add ~/.ssh/id_rsa` generate a new key & add it to ssh  

## git hooks
##### pre-commit
```
#!/bin/sh
./vendor/bin/php-cs-fixer fix --dry-run -v > /dev/null 2>&1
status=$?

if [ "$status" = 0 ] ; then
    exit 0
else
    ./vendor/bin/php-cs-fixer fix > /dev/null 2>&1
    git add *
    echo 1>&2 "Found not properly formatted files. php-cs-fixer
was run."
    exit 0
fi

```
