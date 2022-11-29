<?php

auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));


$t_current = plugin_get_current();
$t_path = config_get_global('plugin_path') . $t_current . '/core/config/';
$file = $t_path . 'config.php';


if (isset($_POST) && !empty($_POST)) {

    form_security_validate('imatic_synchronizer_config_create');

    $t_current = plugin_get_current();
    $t_path = config_get_global('plugin_path') . $t_current . '/core/config/';
    $file = $t_path . 'config.php';

    $token = var_export($_POST['user_api_token'], true);
    $url = var_export($_POST['api_url'], true);

    $vars = "<?php
\n\$user_api_token = $token;
\$api_url = $url;";

    file_put_contents($file, $vars);

    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
