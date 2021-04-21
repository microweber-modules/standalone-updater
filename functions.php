<?php
autoload_add_namespace(__DIR__ . '/src/', 'MicroweberPackages\\StandaloneUpdater\\');

function latest_version() {
    $updateApi = 'https://update-dev.microweberapi.com/?api_function=get_download_link&get_last_version=1';
    $version = file_get_contents($updateApi);
    $version = json_decode($version, true);
    return $version;
}
