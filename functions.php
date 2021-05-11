<?php
autoload_add_namespace(__DIR__ . '/src/', 'MicroweberPackages\\StandaloneUpdater\\');

function latest_version() {

    $updateApi = 'https://update-dev.microweberapi.com/?api_function=get_download_link&get_last_version=1';
    $version = app()->url_manager->download($updateApi);

    $version = json_decode($version, true);
    return $version;
}

function delete_recursive($dir)
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
