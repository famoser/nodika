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

require 'vendor/deployer/deployer/recipe/symfony4.php';

set('bin_dir', 'bin');
set('var_dir', 'var');

// Configuration
set('repository', 'git@github.com:famoser/nodika.git');
set('shared_files', ['.env.local', 'var/data.sqlite']);
set('shared_dirs', array_merge(get('shared_dirs'), ['public/upload']));
set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts');

// import servers
inventory('servers.yml');

//stages: dev, testing, production
set('default_stage', 'dev');
//only keep two releases
set('keep_releases', 2);

//use php 7.2
set(
    'bin/php',
    '/usr/local/php72/bin/php'
);

//build yarn stuff & upload
task('frontend:build', function () {
    runLocally('yarn install');
    runLocally('yarn upgrade');
    runLocally('yarn run encore production');
    runLocally('rsync -azP public/dist {{user}}@{{hostname}}:{{release_path}}/public');
})->desc('Build frontend assets');

// kill php processes to ensure symlinks are refreshed
task('deploy:refresh_symlink', function () {
    run('killall -9 php-cgi'); //kill all php processes so symlink is refreshed
})->desc('Refreshing symlink');

//automatic till vendors comand
desc('Deploy project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
]);

//add the other tasks
after('deploy:vendors', 'frontend:build');
after('frontend:build', 'database:migrate');
after('database:migrate', 'deploy:cache:clear');
after('deploy:cache:clear', 'deploy:cache:warmup');
after('deploy:cache:warmup', 'deploy:symlink');
after('deploy:symlink', 'deploy:refresh_symlink');
after('deploy:refresh_symlink', 'deploy:unlock');
after('deploy:unlock', 'cleanup');
