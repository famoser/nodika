<?php

namespace Deployer;
require 'vendor/deployer/deployer/recipe/symfony3.php';

// Configuration
set('repository', 'git@github.com:famoser/nodika.git');
set('shared_files', ['app/config/parameters.yml', "app/data/data.db3"]);
// import servers
serverList('servers.yml');

//stages: dev, testing, production
set('default_stage', 'dev');
set('writable_use_sudo', false);
set('http_user', 'floria74');

//I need this config for my hoster
set(
    'composer_options',
    '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --ignore-platform-reqs'
);
set(
    'bin/php',
    "bin/php71"
);

/**
 * Execute migrations
 */
task('deploy:doctrine:migrations', function () {
    $commands = [
        "doctrine:migrations:migrate -q"
    ];
    foreach ($commands as $command) {
        run('{{bin/php}} {{release_path}}/' . trim(get('bin_dir'), '/') . '/console ' . $command);
    }
})->desc('Migrating data');

/**
 * Rebuild test data in dev
 */
task('deploy:doctrine:fixtures', function () {
    if (env('branch') == "develop") {
        $commands = [
            "doctrine:fixtures:load -q"
        ];
        foreach ($commands as $command) {
            run('{{bin/php}} {{release_path}}/' . trim(get('bin_dir'), '/') . '/console ' . $command);
        }
    }
})->desc('Initializing example data');

//
/**
 * Create symlink to last release.
 */
task('deploy:symlink', function () {
    run("cd {{deploy_path}} && ln -sfn {{release_path}} current"); // Atomic override symlink.
    run("cd {{deploy_path}} && rm release"); // Remove release link.
    run("killall -9 php-cgi"); //kill all php processes so symlink is refreshed
})->desc('Creating symlink to release');

/**
 * Main task
 */
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:create_cache_dir',
    'deploy:shared',
    'deploy:assets',
    'deploy:vendors',
    'deploy:assetic:dump',
    'deploy:cache:warmup',
    'deploy:writable',
    'deploy:doctrine:migrations',
    'deploy:doctrine:fixtures',
    'deploy:symlink',
    'cleanup',
])->desc('Deploy your project');