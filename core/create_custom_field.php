<?php


$custom_field = plugin_config_get('custom_field');

$f_name = $custom_field['name'];
if (!$f_name) {
    return;
}
if (!custom_field_is_name_unique($f_name)) {
    return;
};

$t_field_id = custom_field_create($f_name);


$custom_field['id'] = $t_field_id;

plugin_config_set('custom_field', $custom_field);

$t_values['name'] = $f_name;
$t_values['type'] = 0;
$t_values['length_min'] = 0;
$t_values['length_max'] = 200;
$t_values['access_level_r']	=	10;
$t_values['access_level_rw']	= 10;
$t_values['display_report'] = true;
$t_values['display_update'] = true;
$t_values['display_resolved'] = true;
$t_values['display_closed'] = true;
$t_values['require_update'] = true;
$t_values['require_resolved'] = true;
$t_values['require_closed'] = true;
$t_values['filter_by'] = true;

custom_field_update($t_field_id, $t_values);
