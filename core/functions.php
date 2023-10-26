<?php


 function imatic_mci_enum_get_array_by_id( $p_enum_id, $p_enum_type, $p_lang ) {
    $t_result = array();
    $t_result['id'] = (int)$p_enum_id;

    $t_enum_name = $p_enum_type . '_enum_string';

    $t_enum_string_value = lang_get( $t_enum_name, $p_lang );

    $t_result['name'] = $t_enum_string_value;

    if( $p_enum_type == 'status' ) {
        // Pre status enum môžete pridať získanie farby, ak to potrebujete
        $t_status_colors = config_get( 'status_colors' );

        if( !array_key_exists( $t_result['name'], $t_status_colors ) ) {
            $t_result['color'] = 'currentcolor';
        } else {
            $t_result['color'] = $t_status_colors[$t_result['name']];
        }
    }

    return $t_result;
}