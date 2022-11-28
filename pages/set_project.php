<?php


auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));


if ($_POST['project_for_synchronize']) {

    plugin_config_set('project_for_synchronize', [$_POST['project_for_synchronize']]);
    print_header_redirect(plugin_page('select_projects'));

}