<?php

/*Route::prefix(ADMIN_PREFIX)->middleware(['admin'])->group(function () {

    Route::get('standalone-update-now', function() {

    })->name('module.standalone-updater.update');
});*/

api_expose_admin('standalone-update-now', function () {

    $updateCacheFolderName = 'standalone-update';
    $updateCacheDir = base_path() . DS . $updateCacheFolderName;
    delete_recursive($updateCacheDir);
    mkdir_recursive($updateCacheDir);

    $randomFileUpdateName = rand(222,444) . time(). '-mw-update.php';

    $redirectLink = site_url() . $updateCacheFolderName .'/'. $randomFileUpdateName;

    // copy(  dirname(__DIR__) . '/mw-black-logo.png', $updateCacheDir . DS . 'mw-black-logo.png');

   //  copy(  dirname(__DIR__) . '/Microweber-logo-reveal.mp4', $updateCacheDir . DS . 'Microweber-logo-reveal.mp4');

    $sourceUpdater = file_get_contents(dirname(__DIR__) .'/StandaloneUpdateExecutor.source');

    $save = file_put_contents($updateCacheDir . DS . $randomFileUpdateName, $sourceUpdater);
    if ($save) {
        return ['success'=>true,'redirect_to'=>$redirectLink];
    }

    return ['success'=>false,'message'=>'Cant create update file.'];
});
