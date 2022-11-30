<?php


auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));


if ($_POST['project_for_synchronize']) {

    $project_id = $_POST['project_for_synchronize'];

    plugin_config_set('project_for_synchronize', [$project_id]);


    // Set project to custom field which show link to extern app
    $custom_field = plugin_config_get('custom_field');

    if ($custom_field['id']) {

        // Unlink projects
        $t_project_ids = custom_field_get_project_ids($custom_field['id']);
        foreach ($t_project_ids as $id) {
            custom_field_unlink($custom_field['id'], $id);
        }

        //Link project
        if (custom_field_exists($custom_field['id'])) {
            if (!custom_field_is_linked($custom_field['id'], $project_id)) {
                custom_field_link($custom_field['id'], $project_id);
            }
        }
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
