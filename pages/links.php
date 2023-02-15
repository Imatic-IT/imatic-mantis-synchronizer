
<?php



if($info_page =  $_GET['page'] == 'ImaticSynchronizer/readme.php' ? 'btn btn-default' : 'btn btn-primary' )
if($webhook_page =  $_GET['page'] == 'ImaticSynchronizer/webhooks' ? 'btn btn-default' : 'btn btn-primary' )
if($logs_page =  $_GET['page'] == 'ImaticSynchronizer/synchronizer_logs' ? 'btn btn-default' : 'btn btn-primary' )

?>
<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="form-container">
        <a class="btn <?php echo $info_page?> btn-sm" href="<?php echo plugin_page('readme.php') ?>"> <i class="fa fa-info-circle"></i></a>
        <a class="btn <?php echo $webhook_page?> btn-sm" href="<?php echo plugin_page('webhooks') ?>"><img height="20px" width="20px" src="<?php echo plugin_file('icons/icons8-webhook-48.png') ?>" alt=""> Create webhooks</a>
        <a class="btn <?php echo $logs_page?> btn-sm" href="<?php echo plugin_page('synchronizer_logs&result_per_page=50&log_page=1') ?>">Show logs</a>
    </div>
</div>