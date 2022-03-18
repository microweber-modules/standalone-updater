<?php
namespace MicroweberPackages\Modules\StandaloneUpdater\Http\Controllers;

use GrahamCampbell\Markdown\Facades\Markdown;
use MicroweberPackages\App\Http\Controllers\AdminController;

class StandaloneUpdaterController extends AdminController
{
    public function aboutNewVersion()
    {
        $readmeFile = MW_ROOTPATH . 'about.md';
        if (is_file($readmeFile)) {
            $readmeFile = file_get_contents($readmeFile);
            $html =  Markdown::convertToHtml($readmeFile);
        } else {
            return redirect(admin_url());
        }

        return $this->view('standalone-updater::about', ['about'=>$html]);
    }

    public function updateNow()
    {
        $installVersion = 'latest';
        if (isset($_POST['version']) && $_POST['version'] == 'dev') {
            $installVersion = 'dev';
        }

        setcookie('max_receive_speed_download', get_option('max_receive_speed_download', 'standalone_updater'), time() + (1800 * 5), "/");
        setcookie('admin_url', admin_url('view:modules/load_module:standalone-updater?delete_temp=1'), time() + (1800 * 5), "/");
        setcookie('site_url', site_url(), time() + (1800 * 5), "/");
        setcookie('install_session_id', false, time() - (1800 * 5), "/");

        $updateCacheFolderName = 'standalone-update' . DS . rand(222, 444) . time() . DS;
        $updateCacheDir = userfiles_path() . $updateCacheFolderName;

        mw_standalone_updater_delete_recursive(userfiles_path() . 'standalone-update');
        mkdir_recursive($updateCacheDir);

        $bootstrap_cached_folder = normalize_path(base_path('bootstrap/cache/'), true);
        mw_standalone_updater_delete_recursive($bootstrap_cached_folder);

        $redirectLink = site_url() . 'userfiles/' . $updateCacheFolderName . 'index.php?installVersion=' . $installVersion;

        // copy(  dirname(__DIR__) . '/mw-black-logo.png', $updateCacheDir . DS . 'mw-black-logo.png');
        //  copy(  dirname(__DIR__) . '/Microweber-logo-reveal.mp4', $updateCacheDir . DS . 'Microweber-logo-reveal.mp4');

        $sourceActions = file_get_contents(dirname(__DIR__) . '/standalone-installation-setup/actions.source');
        $saveActions = file_put_contents($updateCacheDir . DS . 'actions.php', $sourceActions);

        $sourceUpdater = file_get_contents(dirname(__DIR__) . '/standalone-installation-setup/index.source');
        $saveIndex = file_put_contents($updateCacheDir . DS . 'index.php', $sourceUpdater);

        $sourceUnzip = file_get_contents(dirname(__DIR__) . '/standalone-installation-setup/Unzip.source');
        $saveUnzip = file_put_contents($updateCacheDir . DS . 'Unzip.php', $sourceUnzip);

        if ($saveActions && $saveIndex && $saveUnzip) {
            return redirect($redirectLink);
        }

        return redirect(admin_url('view:modules/load_module:standalone-updater?message=Cant create update file.'));
    }

    public function deleteTemp()
    {
        $path = userfiles_path() . 'standalone-update';
        if (!is_dir($path)) {
            return false;
        }
        try {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                @$todo($fileinfo->getRealPath());
            }
            @rmdir($path);
        } catch (\Exception $e) {
            //
        }
    }
}
