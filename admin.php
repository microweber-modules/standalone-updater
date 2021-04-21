<?php must_have_access(); ?>

<?php
$currentVersion = MW_VERSION;
$currentVersion = 1.3;
$latestVersionDetails = latest_version();

$isUpToDate = false;
if ($latestVersionDetails['version'] == $currentVersion) {
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
            <img src="<?php echo $module_info['icon']; ?>" class="module-icon-svg-fill"/> <strong><?php _e($module_info['name']); ?></strong>
        </h5>
    </div>

    <div class="card-body pt-3">

        <div class="card <?php if ($isUpToDate):  ?>card-success<?php else: ?> card-danger <?php endif; ?> text-center">
            <div class="card-body">
                <h4>Easy update your microweber website</h4>
                <h6>with Standalone Updater</h6>

                Your current version: <b><?php echo $currentVersion; ?></b> <br />
                Latest available version: <b><?php echo $latestVersionDetails['version']; ?></b> <br />
                Release date: <b><?php echo $latestVersionDetails['build_date']; ?></b>
                <br />
                <br />

                <?php if ($isUpToDate) {  ?>
                    <h2><i class="mdi mdi-check"></i> You are up to date!</h2>
                    <?php
                } else {
                ?>
                    Your version is old!<br />
                    <a href="<?php echo route('module.standalone-updater.update'); ?>"class="btn btn-success"><i class="mdi mdi-update"></i> Update now!</a>
                <?php
                }
                ?>

            </div>
        </div>

    </div>
</div>
