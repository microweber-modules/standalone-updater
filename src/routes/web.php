<?php

/*Route::prefix(ADMIN_PREFIX)->middleware(['admin'])->group(function () {

    Route::get('standalone-update-now', function() {

    })->name('module.standalone-updater.update');
});*/

api_expose_admin('standalone-update-now', function () {

    if (isset($_POST['version']) && $_POST['version'] == 'dev') {
        setcookie('install_session_version', 'dev', time() - (1800 * 5), "/");
    } else {
        setcookie('install_session_version', 'latest', time() - (1800 * 5), "/");
    }

    setcookie('install_session_id', false, time() - (1800 * 5), "/");

    $updateCacheFolderName = 'standalone-update'. DS. rand(222,444) . time(). DS ;
    $updateCacheDir = userfiles_path() . $updateCacheFolderName;

    delete_recursive(userfiles_path() . 'standalone-update');
    mkdir_recursive($updateCacheDir);

    $redirectLink = site_url() . 'userfiles/' . $updateCacheFolderName;

    // copy(  dirname(__DIR__) . '/mw-black-logo.png', $updateCacheDir . DS . 'mw-black-logo.png');
   //  copy(  dirname(__DIR__) . '/Microweber-logo-reveal.mp4', $updateCacheDir . DS . 'Microweber-logo-reveal.mp4');

    $sourceActions = file_get_contents(dirname(__DIR__) .'/standalone-installation-setup/actions.source');
    $saveActions = file_put_contents($updateCacheDir . DS . 'actions.php', $sourceActions);

    $sourceUpdater = file_get_contents(dirname(__DIR__) .'/standalone-installation-setup/index.source');
    $saveIndex = file_put_contents($updateCacheDir . DS . 'index.php', $sourceUpdater);

    $sourceUnzip = file_get_contents(dirname(__DIR__) .'/standalone-installation-setup/Unzip.source');
    $saveUnzip = file_put_contents($updateCacheDir . DS . 'Unzip.php', $sourceUnzip);

    if ($saveActions && $saveIndex && $saveUnzip) {
        return redirect($redirectLink);
    }

    return redirect(admin_url('view:modules/load_module:standalone-updater?message=Cant create update file.'));
});
