<?php


auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));


layout_page_header();
layout_page_begin('manage_overview_page.php');
print_manage_menu('manage_plugin_page.php');

include 'links.php';

layout_page_end();