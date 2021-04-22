<?php

/*Route::prefix(ADMIN_PREFIX)->middleware(['admin'])->group(function () {

    Route::get('standalone-update-now', function() {

    })->name('module.standalone-updater.update');
});*/

api_expose_admin('standalone-update-now', function () {

    $updateCacheFolderName = 'standalone-update/' . rand(222,444) . time(). '/' ;
    $updateCacheDir = base_path() . DS . $updateCacheFolderName;

    delete_recursive($updateCacheDir);
    mkdir_recursive($updateCacheDir);

    $randomFolderUpdateName = rand(222,444) . time(). '-mw-update.php';

    $redirectLink = site_url() . $updateCacheFolderName;

    // copy(  dirname(__DIR__) . '/mw-black-logo.png', $updateCacheDir . DS . 'mw-black-logo.png');
   //  copy(  dirname(__DIR__) . '/Microweber-logo-reveal.mp4', $updateCacheDir . DS . 'Microweber-logo-reveal.mp4');

    $sourceUpdater = file_get_contents(dirname(__DIR__) .'/standalone-installation-setup/actions.source');
    $saveActions = file_put_contents($updateCacheDir . DS . 'actions.php', $sourceUpdater);

    $sourceUpdater = file_get_contents(dirname(__DIR__) .'/standalone-installation-setup/index.source');
    $saveIndex = file_put_contents($updateCacheDir . DS . 'index.php', $sourceUpdater);

    if ($saveActions && $saveIndex) {
        return ['success'=>true,'redirect_to'=>$redirectLink];
    }

    return ['success'=>false,'message'=>'Cant create update file.'];
});
