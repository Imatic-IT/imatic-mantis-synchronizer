<?php

use Imatic\Mantis\Synchronizer\ImaticMantisDbloggerModel;

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
    header('Location: ' . $_SERVER['HTTP_REFERER']);


}