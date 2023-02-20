<?php

namespace Imatic\Mantis\Synchronizer;
class ImaticMantisDbloggerModel
{

    public function imaticLog($issue_data)
    {
        
        $db = db_get_table('imatic_synchronizer_bug_logger');
        
        date_default_timezone_set('Europe/Prague');
        $db_now = db_now();
        db_param_push();
        $t_query = 'INSERT INTO ' . $db . '
                        ( issue_id, bugnote_id, webhook_event, resended, log_level, date_submitted, webhook_id, webhook_name, status_code, issue)
                      VALUES
                        ( ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
        db_query($t_query, array((int)$issue_data->issue_id, $issue_data->bugnote_id, $issue_data->webhook_event, $issue_data->resended, $issue_data->log_level, $db_now, $issue_data->webhook_id, $issue_data->webhook_name, $issue_data->status_code, $issue_data->issue_json));
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


    public function imaticDeleteLogsByLevel($log_level)
    {
        $db = db_get_table('imatic_synchronizer_bug_logger');
        $t_query = "DELETE From $db " . "WHERE log_level='" . $log_level . "'";
        $t_result = db_query($t_query);
        return db_affected_rows($db);
    }

    public function imaticDeleteAllLogs()
    {
        $db = db_get_table('imatic_synchronizer_bug_logger');
        $t_query = "DELETE From $db ";
        $t_result = db_query($t_query);
        return db_affected_rows($db);
    }

    public function imaticDeleteSelectedLog($id)
    {
        $db = db_get_table('imatic_synchronizer_bug_logger');
        $t_query = "DELETE From $db " . "WHERE id='" . $id . "'";
        $t_result = db_query($t_query);
        return db_affected_rows($db);
    }

    public function imaticGetAllErrorLogs()
    {
        $db = db_get_table('imatic_synchronizer_bug_logger');
        $t_query = "SELECT * From $db " . "WHERE log_level='error'";
        $t_query .= "ORDER BY date_submitted ASC";
        $t_result = db_query($t_query);
        $t_res = [];
        while ($row = db_fetch_array($t_result)) {
            $date = date("d.m.Y", $row['date_submitted']);
            $time = date("H:i:s", $row['date_submitted']);
            $row['parsed_date'] = $date;
            $row['parsed_time'] = $time;
            $t_res[] = $row;
        }
        return $t_res;

    }

    public function imaticUpdateWhenResended($resend_status, $log_level, $status, $id)
    {

        $db = db_get_table('imatic_synchronizer_bug_logger');
        $sql = "UPDATE " . $db . " SET resended='" . $resend_status . "',status_code='" . $status . "', log_level='" . $log_level. "' WHERE id=". $id;
             db_query($sql);
        return  db_affected_rows();
    }

    public  function imaticGetLogById($id){

        $db = db_get_table('imatic_synchronizer_bug_logger');
        $t_query = "SELECT * From $db " . "WHERE issue_id='$id'";

        $t_result = db_query($t_query);
        $t_res = [];
        while ($row = db_fetch_array($t_result)) {
            $t_res[] = $row;
        }

        return $t_res;

    }
}