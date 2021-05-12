<?php must_have_access(); ?>

<?php
$currentVersion = MW_VERSION;
$latestVersionDetails = latest_version();

$isUpToDate = false;

if (version_compare($currentVersion, $latestVersionDetails['version']) >= 0) {
    $isUpToDate = true;
}
?>

<?php if (isset($params['backend'])): ?>
    <module type="admin/modules/info"/>
<?php endif; ?>

<style>
    .mw-standalone-icons {
        font-size: 50px;
    }
</style>

<div class="card style-1 m-3">
    <div class="card-header">
        <?php $module_info = module_info($params['module']); ?>
        <h5>
            <img src="<?php echo $module_info['icon']; ?>" class="module-icon-svg-fill"/>
            <strong><?php _e($module_info['name']); ?></strong>
        </h5>
    </div>

    <div class="card-body border-0">
        <div class="text-center <?php if ($isUpToDate): ?>card-success<?php else: ?> card-danger <?php endif; ?>">
            <div class="card-body p-5">
                <h4>Easy update your website builder and CMS</h4>
                <small>with Standalone Updater</small>
                <br><br>
                <div class="d-flex justify-content-center ">
                    <p>Your current version is <span class="font-weight-bold"><?php echo $currentVersion; ?></span></p>&nbsp;
                    <p class="mb-0">and the latest version is <span class="font-weight-bold"><?php echo $latestVersionDetails['version']; ?></span></p>
                    &nbsp;released on <?php echo $latestVersionDetails['build_date']; ?>
                <br><br>

                </div>
                <?php if ($isUpToDate) { ?>
                <br> <br>
                <h1 class="text-success"><i class="mw-standalone-icons mdi mdi-check-circle-outline"></i><h4><h5 class="text-success font-weight-bold">  You are up to date!</h5></h4>
                    <?php
                } else { ?>
                <br> <br>
               <h1 class="text-danger"><i class="mw-standalone-icons mdi mdi-close-circle-outline"></i></h1> <h5 class="text-danger font-weight-bold"> You're not up to date!</h5><br/>
                    <?php } ?>
                <br><br>

                <form method="post" action="<?php echo site_url('api/standalone-update-now'); ?>">
                    <div class="d-flex justify-content-center">
                        <div class="form-group mb-0 mr-4">
                            <div class="input-group align-items-center">
                                <label> Version:</label>&nbsp;
                                <select name="version" class="form-control">
                                    <option value="latest">Latest stable</option>
                                    <option value="dev">Latest Developer (unstable)</option>
                                </select>
                            </div>
                        </div>

                        <?php if ($isUpToDate) { ?>
                            <button method="submit" class="btn btn-success js-standalone-updater-update-button"> Reinstall</button>
                            <?php
                        } else {
                            ?>
                            <button method="submit" class="btn btn-success js-standalone-updater-update-button"> Update now!</button>
                            <?php
                        }
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
