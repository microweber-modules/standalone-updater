<?php
$config = array();
$config['name'] = "Standalone Updater";
$config['author'] = "Microweber";
$config['no_cache'] = false;
$config['ui'] = true;
$config['ui_admin'] = true;
$config['is_system'] = true;
$config['categories'] = "other";
$config['position'] = 1;
$config['version'] = 2.2;

$config['settings']['service_provider'] = [
    \MicroweberPackages\StandaloneUpdater\StandaloneUpdaterServiceProvider::class
];
