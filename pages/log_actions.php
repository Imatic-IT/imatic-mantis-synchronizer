<?php

use Imatic\Mantis\Synchronizer\ImaticMantisDbloggerModel;
use Imatic\Mantis\Synchronizer\ImaticWebhook;

if (isset($_POST) && !empty($_POST)) {

    $log_model = new ImaticMantisDbloggerModel();
    if ($_POST['logs_actions'] == 'delete_all_logs') {
        $log_model->imaticDeleteAllLogs();
    }
    if ($_POST['logs_actions'] == 'delete_success_logs') {
        $log_model->imaticDeleteLogsByLevel('success');
    }
    if ($_POST['logs_actions'] == 'delete_error_logs') {
        $log_model->imaticDeleteLogsByLevel('error');
    }
    if ($_POST['logs_actions'] == 'delete_selected_logs') {

        foreach ($_POST['logs_id_arr'] as $id) {
            $log_model->imaticDeleteSelectedLog($id);
        }
    }

    if ($_POST['logs_actions'] == 'resend_all_logs') {

        $wh = new ImaticWebhook();
        $logs = $log_model->imaticGetAllErrorLogs();

        foreach ($logs as $log){

            $issue_data = json_decode($log['issue'], true);

            $webhook = $wh->getWebhook($log['webhook_id']);

            $webhook_result = $wh->sendWebhook($issue_data, $webhook['url']);



            $resend_status = 'tried';
            $log_level = 'error';

            if ($webhook_result['status'] >= 200 && $webhook_result['status'] <= 205){
                $resend_status = 'success';
            }
            if ($webhook_result['status'] >= 200 && $webhook_result['status'] <= 205){
                $log_level = 'success';
            }

            $log_model->imaticUpdateWhenResended($resend_status, $log_level,$webhook_result['status'], $log['id']);

        }

    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);

}