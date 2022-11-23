<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define("INI_SYSTEM_CHECK_DISABLED", ini_get('disable_functions'));

if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'ini_set')) {
    ini_set("memory_limit", "512M");
    ini_set("set_time_limit", 0);
}

if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'date_default_timezone_set')) {
    date_default_timezone_set('America/Los_Angeles');
}

include(__DIR__ . '/Unzip.php');

class StandaloneUpdateExecutor
{
    public function getLogfile()
    {
        return $this->getInstallSessionId() . 'log.txt';
    }

    public function log($log)
    {
        @file_put_contents($this->getLogfile(), $log . PHP_EOL);
    }

    public function getInstallSessionId()
    {
        return $_COOKIE['install_session_id'];
    }

    public function startSession()
    {
        if (!isset($_COOKIE['install_session_id'])) {
            $this->setCookie('install_session_id', rand(2222, 4444));
            $this->log('Starting the installation session..');
            return true;
        }

        return false;
    }

    public function setCookie($key, $value)
    {
        return setcookie($key, $value, time() + (1800), "/");
    }

    public function setInstallVersion($version = 'latest')
    {
        setcookie('install_session_version', $version, time() + (1800), "/");
    }

    public function isStarted()
    {
        if (!isset($_COOKIE['install_session_id'])) {
            return false;
        }
        return true;
    }

    public function latestVersion()
    {

        $latestMasterVersionZip = 'http://updater.microweberapi.com/microweber-master.zip';

        return ['url' => $latestMasterVersionZip];
    }

    public function latestDevVersion()
    {

        $latestDevVersionZip = 'http://updater.microweberapi.com/microweber-dev.zip';

        return ['url' => $latestDevVersionZip];
    }

    public function startUpdating()
    {
        $version = 'latest';

        if (isset($_COOKIE['install_session_version']) && $_COOKIE['install_session_version'] == 'dev') {
            $version = 'developer';
            $installVersion = $this->latestDevVersion();
        } else {
            $installVersion = $this->latestVersion();
            if (!$installVersion['url']) {
                $this->log("We can't download latest version right now.");
                return ['status' => 'failed', 'downloaded' => false];
            }
        }

        $this->log('Downloading ' . $version . ' version of system.');

        $zipFile = time() . 'mw-app.zip';

        $downloadStatus = $this->downloadFile($installVersion['url'], $zipFile);
        if ($downloadStatus) {
            $this->log('The ' . $version . ' version of the system has been downloaded successfully!');
        }

        $this->setCookie('install_version_source', $version);
        $this->setCookie('install_version_zip_file', $zipFile);

        return ['status' => 'success', 'downloaded' => true];
    }

    public function unzippAppGetNumberOfStepsNeeded()
    {
        $version = $_COOKIE['install_version_source'];
        $zipFile = $_COOKIE['install_version_zip_file'];

        $version = $this->sanitizeString($version);
        $zipFile =  $this->sanitizeString($zipFile);



        $this->log('Unzipping ' . $version . ' version files...');
        $filesInZip = [];

        $zip = new \ZipArchive;
        $res = $zip->open(__DIR__ . DIRECTORY_SEPARATOR . $zipFile);
        if ($res === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename_only = $zip->getNameIndex($i);
                $filesInZip[] = $filename_only;
            }
        }

        if (is_object($res)) {
            try {
                $zip->close();
            } catch (Exception $e) {
                //   $this->log('Error: ' . $e->getMessage());
            }

        }


        if ($filesInZip) {
            $chunks = array_chunk($filesInZip, 1000);

            $steps_file = __DIR__ . DIRECTORY_SEPARATOR . 'unzip_steps.json';

            $json = json_encode($chunks);
            file_put_contents($steps_file, $json);

            return ['status' => 'success', 'unzip_steps_needed' => count($chunks)];

        }


    }
    public function sanitizeString($string)
    {
        $string = strtolower(trim(preg_replace('/[^A-Za-z0-9-_.]+/', '-', $string)));
        $string = str_replace('..', '', $string);
        $string = str_replace('\\', '', $string);
        $string = str_replace('/', '', $string);

        return $string;
    }
    public function unzipAppExecStep($step)
    {
        $step = intval($step);
        $version = $_COOKIE['install_version_source'];
        $zipFile = $_COOKIE['install_version_zip_file'];
        $version = $this->sanitizeString($version);
        $zipFile =  $this->sanitizeString($zipFile);
        $extractToFolder = __DIR__ . DIRECTORY_SEPARATOR . 'mw-app-unziped';
        $this->log('Extracting files from archive step ' . $step . ' of ' . $version . ' version files...');

        $zip = new \ZipArchive;
        $res = $zip->open(__DIR__ . DIRECTORY_SEPARATOR . $zipFile);
        if ($res === TRUE) {
            $steps_file = __DIR__ . DIRECTORY_SEPARATOR . 'unzip_steps.json';
            $steps = json_decode(file_get_contents($steps_file), true);
            if (isset($steps[$step])) {
                $files = $steps[$step];
                $logOnce = false;
                foreach ($files as $file) {
                    $file = normalize_path($file, false);
                    if (!$logOnce) {
                     //   $this->log('Unzipping ' . basename($file). ' ...');
                        $logOnce = true;
                    }
                    $dn = dirname($extractToFolder . DIRECTORY_SEPARATOR . $file);
                    if (!is_dir($dn)) {
                        mkdir_recursive($dn);
                    }
                    $extractThefile = $zip->extractTo($extractToFolder, $file);
                 }

            }
        }
        if (is_object($zip)) {
            $zip->close();
        }
        return ['status' => 'success', 'unzip_step_executed' => $step];

    }

    public function unzippApp()
    {
        $version = $_COOKIE['install_version_source'];
        $zipFile = $_COOKIE['install_version_zip_file'];
        $version = $this->sanitizeString($version);
        $zipFile =  $this->sanitizeString($zipFile);

        $this->log('Unzipping ' . $version . ' version files...');

        $zip = new \ZipArchive;
        $res = $zip->open(__DIR__ . DIRECTORY_SEPARATOR . $zipFile);
        if ($res === TRUE) {
            $zip->extractTo(__DIR__ . DIRECTORY_SEPARATOR . 'mw-app-unziped');
            $zip->close();
            $this->log('Unzipping ' . $version . ' version files done!');
            return ['status' => 'success', 'unzipped' => true];
        } else {
            $this->log('Error unzipping the ' . $version . ' version of the system!');
            return ['status' => 'failed', 'unzipped' => false];
        }
    }

    public function replaceFilesPrepareStepsNeeded()
    {
        $this->log('Preparing replace steps...');

        $replace = new StandaloneUpdateReplacer();
        $steps = $replace->prepareSteps();

        return ['status' => 'success', 'steps_needed' => $steps];

    }


    public function replaceFilesExecStep($step)
    {

        $replace = new StandaloneUpdateReplacer();
        $replace->logger = $this;
        $step = $replace->replaceFilesExecStep($step);

        return ['status' => 'success', 'step_executed' => $step];

    }

    public function replaceFilesExecCleanupStep()
    {

        $replace = new StandaloneUpdateReplacer();
        $replace->logger = $this;
        $step = $replace->replaceFilesExecCleanupStep();


        return ['status' => 'success', 'step_executed' => $step];

    }

    public function replaceFiles()
    {
        $this->log('Replacing with the new files...');

        $replace = new StandaloneUpdateReplacer();
        $replace->start();

        $message = 'You are up to date!';

        /*
        if (!empty($_COOKIE['site_url'])) {
            $siteUrl = $_COOKIE['site_url'];
            $message .= '<br /><a href="'.$siteUrl.'">Visit your website</a>';
        }*/

        if (!empty($_COOKIE['admin_url'])) {
            $adminUrl = $_COOKIE['admin_url'];
            $message .= '<br /><a href="' . $adminUrl . '" class="btn btn-link" style="color:#fff">Back to admin</a>';
        }

        $this->log(json_encode(['success' => true, 'message' => $message]));

        return ['status' => 'success', 'replaced' => true];
    }

    public function downloadFile($url, $dest)
    {
        set_time_limit(0);
        $logFile = $this->getLogfile();
        $options = array(
            CURLOPT_FILE => is_resource($dest) ? $dest : fopen($dest, 'w'),
            CURLOPT_TIMEOUT => 600,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_VERBOSE => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_URL => $url,
            CURLOPT_FAILONERROR => true, // HTTP code > 400 will throw curl error
        );


        if (isset($_COOKIE['max_receive_speed_download']) && $_COOKIE['max_receive_speed_download'] > 0) {
            $speedLimit = (intval($_COOKIE['max_receive_speed_download']) * 1024 * 1024);
            $options[CURLOPT_MAX_RECV_SPEED_LARGE] = $speedLimit;
        }

        if ($logFile) {
            $options[CURLOPT_STDERR] = fopen($logFile, 'a+');
            $options[CURLOPT_WRITEHEADER] = fopen($logFile, 'a+');
        }


        $options[CURLOPT_PROGRESSFUNCTION] = array($this, 'downloadFileProgress');
        $options[CURLOPT_BUFFERSIZE] = 1000000;
        $options[CURLOPT_NOPROGRESS] = false;


        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $return = curl_exec($ch);

        if ($return === false) {
            return curl_error($ch);
        } else {
            return true;
        }
    }

    public function downloadFileProgress($resource, $downloadSize, $downloaded, $uploadSize, $uploaded)
    {
        if ($downloadSize > 0 and $downloaded > 0) {
            $percent = round(($downloaded / $downloadSize) * 100, 2);
            $this->log('Downloaded ' . $percent . '%');
        }
        // $this->log('Downloaded ' . $downloaded . ' of ' . $downloadSize . ' bytes.');


//         [
//            'resource' => $resource,
//            'download_size' => $downloadSize,
//            'downloaded' => $downloaded,
//            'upload_size' => $uploadSize,
//            'uploaded' => $uploaded
//        ]

    }
}

if (isset($_REQUEST['format']) && $_REQUEST['format'] == "json") {

    $json = [];
    header('Content-Type: application/json');

    if (isset($_GET['startSession']) && $_GET['startSession'] == 1) {
        $update = new StandaloneUpdateExecutor();
        $update->setInstallVersion($_GET['installVersion']);
        $json['start'] = $update->startSession();
    }

    if (isset($_GET['startUpdating']) && $_GET['startUpdating'] == 1) {
        $update = new StandaloneUpdateExecutor();
        $json['updating'] = $update->startUpdating();
    }

    if (isset($_GET['unzippApp']) && $_GET['unzippApp']) {
        $update = new StandaloneUpdateExecutor();
        $json['unzipping'] = $update->unzippApp();
    }


    if (isset($_GET['unzipAppExecStep'])) {
        $update = new StandaloneUpdateExecutor();
        $json['unzipping'] = $update->unzipAppExecStep(intval($_GET['unzipAppExecStep']));
    }


    if (isset($_GET['unzippAppGetNumberOfStepsNeeded']) && $_GET['unzippAppGetNumberOfStepsNeeded'] == 1) {
        $update = new StandaloneUpdateExecutor();
        $json['unzipping'] = $update->unzippAppGetNumberOfStepsNeeded();
    }

    if (isset($_GET['replaceFiles']) && $_GET['replaceFiles'] == 1) {
        $update = new StandaloneUpdateExecutor();
        $json['replacing'] = $update->replaceFiles();
    }
    if (isset($_GET['replaceFilesPrepareStepsNeeded']) && $_GET['replaceFilesPrepareStepsNeeded'] == 1) {
        $update = new StandaloneUpdateExecutor();
        $json['replace_steps'] = $update->replaceFilesPrepareStepsNeeded();
    }
    if (isset($_GET['replaceFilesExecStep'])) {
        $update = new StandaloneUpdateExecutor();
        $json['replace_step_result'] = $update->replaceFilesExecStep(intval($_GET['replaceFilesExecStep']));
    }
    if (isset($_GET['replaceFilesExecCleanupStep'])) {
        $update = new StandaloneUpdateExecutor();
        $json['clean_step_result'] = $update->replaceFilesExecCleanupStep();
    }

    if (isset($_GET['isStarted']) && $_GET['isStarted'] == 1) {
        $update = new StandaloneUpdateExecutor();
        $json['started'] = $update->isStarted();
    }

    if (isset($_GET['getLogfile']) && $_GET['getLogfile'] == 1) {
        $update = new StandaloneUpdateExecutor();
        $json['logfile'] = $update->getLogfile();
    }

    print(json_encode($json));
    exit;
}

class StandaloneUpdateReplacer
{
    public $microweberPath;
    public $newMicroweberPath;
    public $logger = null;

    public function __construct()
    {
        $this->microweberPath = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR;
        $this->newMicroweberPath = __DIR__ . DIRECTORY_SEPARATOR . 'mw-app-unziped';
    }

    public function log($mgs)
    {
        if ($this->logger) {
            $this->logger->log($mgs);
        }
    }

    public function replaceFilesExecCleanupStep()
    {
        $steps_file = $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'replace_steps.json';
        return $this->deleteDirectoryRecursive($this->newMicroweberPath);
    }


    public function replaceFilesExecStep($step)
    {
        $step = intval($step);
        if ($step == 0) {
            $this->deleteOldDirectories();
        }

        $steps_file = $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'replace_steps.json';
        $step_data = json_decode(file_get_contents($steps_file), true);

        $total = count(array_keys($step_data));

        if (isset($step_data[$step]) and is_array($step_data[$step]) and !empty($step_data[$step])) {
            $newFilesForCopy = $step_data[$step];
            $this->performFilesCopy($newFilesForCopy);

        }

        $this->log('Completed replace step ' . $step);

        if(intval($step) > 1 and ($total == $step)){
            $this->log('Update is completed');
         }

        return $step;
    }

    public function prepareSteps()
    {
        if(!is_dir($this->newMicroweberPath)){
            mkdir_recursive($this->newMicroweberPath);
        }
        $steps_file = $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'replace_steps.json';

        $files = $this->getFilesToCopy();
        $chunks = array_chunk($files, 1000);

        $json = json_encode($chunks);
        file_put_contents($steps_file, $json);

        return count($chunks);
    }

    public function getFilesToCopy()
    {
        $newFilesForCopy = [];
        $newFilesForCopy = array_merge($newFilesForCopy, $this->getFilesFromPath($this->newMicroweberPath . DIRECTORY_SEPARATOR . 'userfiles' . DIRECTORY_SEPARATOR . 'templates'));
        $newFilesForCopy = array_merge($newFilesForCopy, $this->getFilesFromPath($this->newMicroweberPath . DIRECTORY_SEPARATOR . 'userfiles' . DIRECTORY_SEPARATOR . 'modules'));
        $newFilesForCopy = array_merge($newFilesForCopy, $this->getFilesFromPath($this->newMicroweberPath . DIRECTORY_SEPARATOR . 'userfiles' . DIRECTORY_SEPARATOR . 'elements'));
        $newFilesForCopy = array_merge($newFilesForCopy, $this->getFilesFromPath($this->newMicroweberPath . DIRECTORY_SEPARATOR . 'src'));
        $newFilesForCopy = array_merge($newFilesForCopy, $this->getFilesFromPath($this->newMicroweberPath . DIRECTORY_SEPARATOR . 'vendor'));

        $newFilesForCopy[] = ['realPath' => $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'composer.lock', 'targetPath' => 'composer.lock'];
        $newFilesForCopy[] = ['realPath' => $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'composer.json', 'targetPath' => 'composer.json'];

        $newFilesForCopy[] = ['realPath' => $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'version.txt', 'targetPath' => 'version.txt'];
        $newFilesForCopy[] = ['realPath' => $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'ABOUT.md', 'targetPath' => 'ABOUT.md'];
        $newFilesForCopy[] = ['realPath' => $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'README.md', 'targetPath' => 'README.md'];
        $newFilesForCopy[] = ['realPath' => $this->newMicroweberPath . DIRECTORY_SEPARATOR . 'CHANGELOG.md', 'targetPath' => 'CHANGELOG.md'];

        return $newFilesForCopy;
    }

    public function deleteOldDirectories()
    {
        $this->deleteDirectoryRecursive($this->microweberPath . 'vendor');
        $this->deleteDirectoryRecursive($this->microweberPath . 'bootstrap ' . DIRECTORY_SEPARATOR . 'cache');
        $this->deleteDirectoryRecursive($this->microweberPath . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache');
        $this->deleteDirectoryRecursive($this->microweberPath . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views');
        $this->deleteDirectoryRecursive($this->microweberPath . 'userfiles' . DIRECTORY_SEPARATOR . 'cache');
        $this->deleteDirectoryRecursive($this->microweberPath . 'userfiles' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'admin-css');

    }

    public function performFilesCopy($newFilesForCopy)
    {
        $countCopy = count($newFilesForCopy);
        $copyIterator = 0;
        foreach ($newFilesForCopy as $newFile) {
            $copyIterator++;
            $newFileFolder = dirname($this->microweberPath . $newFile['targetPath']);
            if (!is_dir($newFileFolder)) {
                mkdir_recursive($newFileFolder);
            }

            if (is_file($this->microweberPath . $newFile['targetPath'])) {
                if ($this->filesAreEqual($newFile['realPath'], $this->microweberPath . $newFile['targetPath'])) {
                    continue;
                }
            }

            if (is_file($this->microweberPath . $newFile['targetPath'])) {
                @unlink($this->microweberPath . $newFile['targetPath']);
            }

            copy($newFile['realPath'], $this->microweberPath . $newFile['targetPath']);

        }
    }

    public function start()
    {
        $this->deleteOldDirectories();
        $newFilesForCopy = $this->getFilesToCopy();

        $countCopy = count($newFilesForCopy);

        $this->performFilesCopy($newFilesForCopy);

        $this->log(json_encode(['success' => true, 'message' => $countCopy . ' files copied']));

    }


    public function filesAreEqual($a, $b)
    {
        $a = file_get_contents($a);
        $b = file_get_contents($b);

        $a = preg_replace('/\s+/', '', $a);
        $b = preg_replace('/\s+/', '', $b);

        $a = trim($a);
        $b = trim($b);

        if ($a == $b) {
            return true;
        }

        return false;
    }

    public function deleteDirectoryRecursive($path)
    {
        if (!is_dir($path)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            @$todo($fileinfo->getRealPath());
        }

        return @rmdir($path);
    }

    public function getFilesFromPath($path)
    {
        $filesMap = [];
        if(!is_dir($path)){
            return $filesMap;
        }
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $fileinfo) {
            if (!$fileinfo->isDir()) {

                $targetPath = $fileinfo->getRealPath();
                $targetPath = str_replace($this->newMicroweberPath, '', $targetPath);

                $filesMap[] = ['realPath' => $fileinfo->getRealPath(), 'targetPath' => $targetPath];
            }
        }
        return $filesMap;
    }
}
