<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

require 'vendor/deployer/deployer/recipe/symfony-flex.php';

// Configuration
set('repository', 'git@github.com:famoser/nodika.git');
set('shared_files', array_merge(get('shared_files'), ['var/data.db3']));
set('symfony_env_file', '.env');
set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts');

// import servers
inventory('servers.yml');

//stages: dev, testing, production
set('default_stage', 'dev');
//only keep two releases
set('keep_releases', 2);

//use php 7.1
set(
    'bin/php',
    '/usr/local/php71/bin/php'
);
//build yarn stuff & upload
task('frontend:build', function () {
    runLocally('yarn install');
    runLocally('yarn run encore production');
    runLocally('rsync -azP public/dist {{user}}@{{hostname}}:{{release_path}}/public');
})->desc('Build frontend assets');


// Symfony console bin
set('bin/console', function () {
    $env = get("env_file_path");
    return sprintf('--version && cd {{release_path}} && set -a && source ' . $env . ' && set +a && {{bin/php}} {{release_path}}/%s/console', trim(get('bin_dir'), '/'));
});

//load fixtures for dev
task('database:fixtures', function () {
    if ('dev' === get('stage')) {
        //ensure dev
        $before = get('symfony_env');
        set('symfony_env', 'dev');
        run('{{bin/php}} {{bin/console}} doctrine:fixtures:load {{console_options}}');
        set('symfony_env', $before);
    }
})->desc('Initializing example data if on dev stage');

// kill php processes to ensure symlinks are refreshed
task('deploy:refresh_symlink', function () {
    run('killall -9 php-cgi'); //kill all php processes so symlink is refreshed
})->desc('Refreshing symlink');
//frontend stuff
after('deploy:vendors', 'frontend:build');
// migrations
after('deploy:writable', 'database:migrate');
// fixtures
after('database:migrate', 'database:fixtures');
// refresh symlink
after('deploy:symlink', 'deploy:refresh_symlink');
