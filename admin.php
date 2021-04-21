<?php must_have_access(); ?>

<?php
$latestVersionDetails = latest_version();
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

        <div class="card card-success text-center">
            <div class="card-body">
                <h4>Easy update your microweber website</h4>
                <h6>with Standalone Updater</h6>

                Your current version: <b><?php echo MW_VERSION; ?></b> <br />
                Latest available version: <b><?php echo $latestVersionDetails['version']; ?></b>
                Release date: <b><?php echo $latestVersionDetails['build_date']; ?></b>
                <br />

            </div>
        </div>

    </div>
</div>
