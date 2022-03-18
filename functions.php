<?php

use Composer\Semver\Comparator;

autoload_add_namespace(__DIR__ . '/src/', 'MicroweberPackages\\StandaloneUpdater\\');

function mw_standalone_updater_get_latest_version() {

    $updateApi = 'https://update.microweberapi.com/?api_function=get_download_link&get_last_version=1';
    $version = app()->url_manager->download($updateApi);

    $version = json_decode($version, true);
    return $version;
}

function mw_standalone_updater_delete_recursive($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    try {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            @$todo($fileinfo->getRealPath());
        }
    } catch (\Exception $e) {
        // Cant remove files from this path
    }

    @rmdir($dir);
}


// Show new update on dashboard
$dashboardDismiss = cache_get('dashboard-notice-dismiss','standalone-updater');
if (!$dashboardDismiss) {

    $latestVersionDetails = mw_standalone_updater_get_latest_version();
    $newVersionNumber = $latestVersionDetails['version'];
    $mustUpdate = false;
    if (Comparator::greaterThan($newVersionNumber, MW_VERSION)) {
        $mustUpdate = true;
    }
    if ($mustUpdate) {
        event_bind('mw.admin.dashboard.content', function ($item) use ($newVersionNumber) {
           echo '<div type="standalone-updater/dashboard_notice" new-version="'.$newVersionNumber.'" class="mw-lazy-load-module"></div>';
        });
    }
}
