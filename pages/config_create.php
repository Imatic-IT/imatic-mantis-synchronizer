<?php


auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));


layout_page_header();
layout_page_begin('manage_overview_page.php');
print_manage_menu('manage_plugin_page.php');

include 'links.php';

$t_current = plugin_get_current();
$t_path = config_get_global('plugin_path') . $t_current . '/core/config/';
$file = $t_path . 'config.php';

if (file_exists($file)) {
    $file_exists = 'Replace';
    $file_exists_msg = 'File exists do you want replace them ?';
} else {
    $file_exists = 'Create';
    $file_exists_msg = '';
}

?>

    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="form-container">
            <form action="<?php echo plugin_page('config_create_file') ?>" method="post">
                <?php echo form_security_field('imatic_synchronizer_config_create') ?>
                <div class="widget-box widget-color-blue2">
                    <div class="widget-header widget-header-small">
                        <h4 class="widget-title lighter">
                            <i class="ace-icon fa fa-text-width"></i>
                            Create config
                        </h4>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main no-padding">
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed table-striped">
                                    <tr>
                                        <th class="category width-40">
                                            <?php echo 'USER API TOKEN' ?><br>
                                        </th>
                                        <td>
                                            <input value="" type="text" name="user_api_token">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="category width-40">
                                            <?php echo 'API URL' ?><br>
                                        </th>
                                        <td>
                                            <input value="" type="text" name="api_url">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="widget-toolbox padding-8 clearfix">

                            <input type="submit" class="btn btn-primary btn-white btn-round"
                                   value="<?php echo $file_exists ?>"/>
                            <label for=""><?php echo $file_exists_msg ?></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


<?php

layout_page_end();