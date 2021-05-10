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

<div class="card style-1 mb-3">

    <div class="card-header">
        <?php $module_info = module_info($params['module']); ?>
        <h5>
            <img src="<?php echo $module_info['icon']; ?>" class="module-icon-svg-fill"/>
            <strong><?php _e($module_info['name']); ?></strong>
        </h5>
    </div>

    <div class="card-body pt-3">
        <div class="card <?php if ($isUpToDate): ?>card-success<?php else: ?> card-danger <?php endif; ?> text-center">
            <div class="card-body">
                <h4>Easy update your microweber website</h4>
                <h6>with Standalone Updater</h6>

                Your current version: <b><?php echo $currentVersion; ?></b> <br/>
                Latest available version: <b><?php echo $latestVersionDetails['version']; ?></b> <br/>
                Release date: <b><?php echo $latestVersionDetails['build_date']; ?></b>
                <br/>
                <br/>
                <?php if ($isUpToDate) { ?>
                    <h2><i class="mdi mdi-check"></i> You are up to date!</h2>
                    <?php
                } else {
                ?>
                Your version is old!<br/>
                <?php
                }
                ?>
                <form method="post" action="<?php echo site_url('api/standalone-update-now'); ?>">
                    <div class="d-flex justify-content-center">
                        <div class="form-group mb-0 mr-4">
                            <div class="input-group">
                                <label> Version:</label>
                                <select name="version" class="form-control">
                                    <option value="latest">Latest stable</option>
                                    <option value="dev">Latest Developer (unstable)</option>
                                </select>
                            </div>
                        </div>

                        <?php if ($isUpToDate) { ?>
                            <button method="submit" class="btn btn-success js-standalone-updater-update-button"><i class="mdi mdi-cogs"></i> Reinstall</button>
                            <?php
                        } else {
                            ?>
                            <button method="submit" class="btn btn-success js-standalone-updater-update-button"><i class="mdi mdi-update"></i> Update now!</button>
                            <?php
                        }
                        ?>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
