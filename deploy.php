<?php

namespace Deployer;
require 'vendor/deployer/deployer/recipe/symfony3.php';

// Configuration
set('repository', 'git@github.com:famoser/nodika.git');
set('shared_files', array_merge(get('shared_files'), ["app/data/data.db3"]));
set('shared_dirs', array_merge(get('shared_dirs'), ["web/public"]));
set('writable_dirs', array_merge(get('writable_dirs'), ["web/public"]));

// import servers
inventory('servers.yml');

//stages: dev, testing, production
set('default_stage', 'dev');
/*
set('writable_use_sudo', false);
*/

/*
//I need this config for my hoster
set(
    'composer_options',
    '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --ignore-platform-reqs'
);
*/

set(
    'bin/php',
    '/usr/local/php71/bin/php'
);

/**
 * Rebuild test data in dev
 */
task('database:fixtures', function () {
    if (get('branch') == "develop") {
        //ensure dev
        $before = get("symfony_env");
        set('symfony_env', 'dev');
        run('{{bin/php}} {{bin/console}} doctrine:fixtures:load {{console_options}}');
        set('symfony_env', $before);
    }
})->desc('Initializing example data');

// kill php processes to ensure symlinks are refreshed
task('deploy:refresh_symlink', function () {
    run("killall -9 php-cgi"); //kill all php processes so symlink is refreshed
})->desc('Refreshing symlink');

// migrations
after('deploy:writable', 'database:migrate');
// fixtures
after('database:migrate', 'database:fixtures');
// refresh symlink
after('deploy:symlink', 'deploy:refresh_symlink');
