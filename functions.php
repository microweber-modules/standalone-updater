<?php

use Composer\Semver\Comparator;

autoload_add_namespace(__DIR__ . '/src/', 'MicroweberPackages\\Modules\\StandaloneUpdater\\');

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

event_bind('mw.admin', function ($params = false) {

    // Show new update on dashboard
    $lastUpdateCheckTime = get_option('last_update_check_time','standalone-updater');
    if (!$lastUpdateCheckTime) {
        $lastUpdateCheckTime = \Carbon\Carbon::now();
    }

    $showDashboardNotice =\Carbon\Carbon::now()->greaterThan(\Carbon\Carbon::parse($lastUpdateCheckTime));
    if ($showDashboardNotice) {

        $latestVersionDetails = mw_standalone_updater_get_latest_version();
        $newVersionNumber = $latestVersionDetails['version'];

        if (Comparator::equalTo($newVersionNumber, MW_VERSION)) {
            save_option( 'last_update_check_time',\Carbon\Carbon::parse('+24 hours'),'standalone-updater');
            return;
        }

        $mustUpdate = false;
        if (Comparator::greaterThan($newVersionNumber, MW_VERSION)) {
            $mustUpdate = true;
        }

        if ($mustUpdate) {
            event_bind('mw.admin.dashboard.start', function ($item) use ($newVersionNumber) {
               echo '<div type="standalone-updater/dashboard_notice" new-version="'.$newVersionNumber.'" class="mw-lazy-load-module"></div>';
            });
        }
    }
});
