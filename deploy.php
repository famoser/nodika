<?php
namespace Deployer;
require 'vendor/deployer/deployer/recipe/symfony3.php';

// Configuration
set('repository', 'git@gitlab.com:JKwebGmbH/massgekocht.git');
set('shared_files', ['app/config/parameters.yml', "app/data/data.db3"]);
set('http_user', 'massgekocht');
// import servers
serverList('servers.yml');

//stages: dev, testing, production
set('default_stage', 'dev');
set('writable_use_sudo', false);