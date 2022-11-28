<?php


auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));

layout_page_header();
layout_page_begin('manage_overview_page.php');
print_manage_menu('manage_plugin_page.php');

include 'links.php';

$projects = project_get_all_rows();

$selected_project = plugin_config_get('project_for_synchronize');

?>
    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="form-container">
            <form action="<?php echo plugin_page('set_project') ?>" method="post">
                <?php echo form_security_field('project_for_synchronize+') ?>
                <div class="widget-box widget-color-blue2">
                    <div class="widget-header widget-header-small">
                        <h4 class="widget-title lighter">
                            <i class="ace-icon fa fa-text-width"></i>
                            Set project for synchronize
                        </h4>
                    </div>
                    <div class="widget-body">
                        <div class="widget-main no-padding">
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed table-striped">
                                    <tr>
                                        <th class="category width-40">
                                            <?php echo 'Chose projects' ?><br>
                                        </th>
                                        <td>
                                            <select name="project_for_synchronize" id="">
                                                <?php
                                                foreach ($projects as $project) {
                                                    if ($selected = in_array($project['id'], (array)$selected_project) ? 'selected' : '');
                                                    echo '<option '.$selected.'  type="checkbox"  value="' . $project['id'] . '">' . $project['name'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="widget-toolbox padding-8 clearfix">
                            <input type="submit" class="btn btn-primary btn-white btn-round" value="Select"/>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


<?php

layout_page_end();