<?php
namespace Deployer;
require 'vendor/deployer/deployer/recipe/symfony3.php';

// Configuration
set('repository', 'git@gitlab.com:famoser/notfalldienst.git');
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