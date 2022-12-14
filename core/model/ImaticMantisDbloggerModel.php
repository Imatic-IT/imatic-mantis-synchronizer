<?php

namespace Imatic\Mantis\Synchronizer;

class ImaticMantisDbloggerModel
{

    public function imaticLog($issue_data)
    {
        $db = db_get_table('imatic_synchronizer_bug_logger');

        $db_now = db_now();

        db_param_push();
        $t_query = 'INSERT INTO ' . $db . '
                        ( issue_id, bugnote_id, webhook_event, sended, log_level, date_submitted, webhook_id, webhook_name)
                      VALUES
                        ( ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';

        db_query($t_query, array((int)$issue_data->issue_id, $issue_data->bugnote_id, $issue_data->webhook_event, $issue_data->sended, $issue_data->log_level, $db_now, $issue_data->webhook_id, $issue_data->webhook_name));

        return db_affected_rows($db);
    }


    public function imaticGetLogs()
    {


        $db = db_get_table('imatic_synchronizer_bug_logger');

        $t_query = "SELECT * From $db ";

        $t_result = db_query($t_query);

        $t_res = [];
        while ($row = db_fetch_array($t_result)) {
            $t_res[] = $row;
        }

        return $t_res;
    }


}