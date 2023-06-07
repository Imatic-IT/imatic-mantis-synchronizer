<?php

namespace Imatic\Mantis\Synchronizer;
class ImaticMantisWebhookModel
{

    public function saveWebhook($webhook)
    {
        $db = db_get_table('imatic_synchronizer_webhooks');
        $db_now = db_now();
        db_param_push();
        $t_query = 'INSERT INTO ' . $db . '
                        ( name, url, status, projects, events)
                      VALUES
                        ( ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ')';
        db_query($t_query, array($webhook['name'], $webhook['url'], $webhook['status'], $webhook['projects'], $webhook['events']));
        return db_affected_rows($db);
    }


    public function imaticGetWebhooks()
    {

        $db = db_get_table('imatic_synchronizer_webhooks');
        $t_query = "SELECT * From $db ";
        $t_result = db_query($t_query);
        $t_res = [];
        while ($row = db_fetch_array($t_result)) {
            $row['projects'] = json_decode($row['projects']);
            $t_res[] = $row;
        }
        return $t_res;
    }
    public function imaticGetEnadbledWebhooks()
    {

        $db = db_get_table('imatic_synchronizer_webhooks');
        $t_query = "SELECT * From $db WHERE status='on' ";
        $t_result = db_query($t_query);
        $t_res = [];
        while ($row = db_fetch_array($t_result)) {
            $row['projects'] = json_decode($row['projects']);
            $t_res[] = $row;
        }
        return $t_res;
    }


    public function imaticGetWebhook($id)
    {
        $db = db_get_table('imatic_synchronizer_webhooks');
        $t_query = "SELECT * From $db " . "WHERE id=" . $id;
        $t_result = db_query($t_query);
        $t_res = [];
        while ($row = db_fetch_array($t_result)) {
            $row['projects'] = json_decode($row['projects']);
            $t_res[] = $row;
        }
        return $t_res[0];
    }

    public function imaticDeleteWebhook($id)
    {
        $db = db_get_table('imatic_synchronizer_webhooks');
        $t_query = "DELETE From $db " . "WHERE id=" . $id;
        $t_result = db_query($t_query);
        return db_affected_rows($db);
    }


    public function imaticUpdateWebhook($webhook)
    {

        $webhook['projects'] = json_encode($webhook['projects']);
        $webhook['events'] = json_encode($webhook['events']);
        $id = $webhook['webhook_id'];
        $name = $webhook['name'];
        $url = $webhook['url'];
        $status = $webhook['status'];
        $projects = $webhook['projects'];
        $events = $webhook['events'];
        $db = db_get_table('imatic_synchronizer_webhooks');
        $sql = "UPDATE " . $db . " SET name='" . $name . "', url='" . $url . "', status='" . $status . "', projects='" . $projects ."', events='" . $events . "' WHERE id=" . $id;
        db_query($sql);
        return db_affected_rows();


    }


}