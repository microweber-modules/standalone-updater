
<script>
    function standaloneUpdaterDashboardNoticeDissmiss()
    {
        $('.js-standalone-updater-dashboard-notice').slideUp();
        $.ajax({
            url: "<?php echo route('api.standalone-updater.remove-dashboard-notice'); ?>",
            method: "GET",
            success: function (response) {
            },
            error: function () {
            }
        });
    }
</script>

<div class="js-standalone-updater-dashboard-notice card border-primary mb-3 p-3">
    <div class="card-body">
        <h2 class="card-title font-weight-bold"><?php _e('New version is available'); ?>!</h2>
        <p class="card-text"><?php _e('Current version'); ?> (<span class="font-weight-bold"><?php echo MW_VERSION; ?></span>)</p>
        <p class="card-text"><?php _e('There is a new version'); ?> (<span class="font-weight-bold"><?php echo $params['new-version']; ?></span>) <?php _e('available, do you want to update now?') ?></p>

        <br />

        <a href="#" onclick="standaloneUpdaterDashboardNoticeDissmiss()" class="btn btn-link justify-content-center mw-admin-action-links mw-adm-liveedit-tabs me-3"><?php _e('Later') ?></a>
        <a href="<?php echo module_admin_url('standalone-updater'); ?>" class="btn btn-link justify-content-center mw-admin-action-links mw-adm-liveedit-tabs"><?php _e('Update now') ?></a>
    </div>
</div>
