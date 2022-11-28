<?php

namespace Imatic\Mantis\Synchronizer;


class ImaticMantisBugnotesModel
{

    public function imaticGetAllBugnotesByIssueId($bug_id)
    {

        # get attachments data
        $t_fields = config_get('bug_view_page_fields');
        $t_fields = columns_filter_disabled($t_fields);

        $t_show_attachments = in_array('attachments', $t_fields);


        $t_result = bug_activity_get_all($bug_id, /* include_attachments */ $t_show_attachments);
        $t_activities = $t_result['activities'];
        $t_bugnotes = $t_result['bugnotes'];

        return $t_bugnotes;
    }

}