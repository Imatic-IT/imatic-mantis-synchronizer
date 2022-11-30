<?php

require 'core/require.php';

//CONTROLLERS
use Imatic\Mantis\Synchronizer\ImaticMantisEventListener;
use Imatic\Mantis\Synchronizer\ImaticMantisDbLogger;


//MODELS
use Imatic\Mantis\Synchronizer\ImaticMantisIssueModel;

//constant
require 'core/constant.php';


require_api('install_helper_functions_api.php');
require_api('bug_activity_api.php');

require_once(__DIR__ . '/../../api/soap/mc_account_api.php');
require_once(__DIR__ . '/../../api/soap/mc_api.php');
require_once(__DIR__ . '/../../api/soap/mc_enum_api.php');
require_once(__DIR__ . '/../../api/soap/mc_issue_api.php');
require_once(__DIR__ . '/../../api/soap/mc_project_api.php');


class ImaticSynchronizerPlugin extends MantisPlugin
{

    public function register(): void
    {
        $this->name = 'Imatic Mantis Synchronizer';
        $this->description = 'Synchronize Mantis';
        $this->version = '0.0.1';
        $this->page = 'config';
        $this->requires = array('MantisCore' => '2.0.0');
        $this->author = 'Imatic Software s.r.o.';
        $this->contact = 'info@imatic.cz';
        $this->url = 'http://www.imatic.cz';
    }

    public function config(): array
    {
        return [
            'write_log_to_db' => true,
            'project_for_synchronize' =>
            [
                3
            ],
            'custom_field' => [
                'create' => true,
                'name' => 'Jira issue link',
                'id' => ''
            ]
        ];
    }

    public function schema(): array
    {

        // backwards compatibility with plugin version that didn't define schema
        $t_config_option = 'plugin_' . $this->basename . '_schema';
        $t_schema = config_get($t_config_option, -1, ALL_USERS, ALL_PROJECTS);
        if ($t_schema === -1 && db_table_exists(db_get_table('imatic_synchronizer_bug'))) {
            config_set($t_config_option, -1, ALL_USERS, ALL_PROJECTS);
        }

        return [
            0 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_bug'), "
				issue_id                        								I,
				intern_issue                    								I
			"]],
            1 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_bug_queue'), "
				issue_id                      I,
                method_type	        		  C(32)             DEFAULT \" ' ' \",
				resended					  L                 DEFAULT \" '0' \" ,
				issue						  JSON,
                date_submitted			      I        NOTNULL  DEFAULT '" . db_now() . "',
                last_updated			      I		   NOTNULL  DEFAULT '" . db_now() . "'
			"]],
            2 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
				issue_id                      I,
				bugnote_id                    I                  DEFAULT \" '0' \" ,
                log_level   	        	  C(32)	         	 DEFAULT \" ' ' \",
                webhook_event	        	  C(32)	         	 DEFAULT \" ' ' \",
                message	        			  C(200)		     DEFAULT \" ' ' \",
				sended						  L                  DEFAULT \" '0' \" ,
                date_submitted			      I	        NOTNULL  DEFAULT '" . db_now() . "'
			"]],
        ];
    }

    public function hooks(): array
    {
        return [
            'EVENT_REPORT_BUG_FORM' => 'report_bug_form_hook',
            'EVENT_REPORT_BUG' => 'event_bug_hooks',
            'EVENT_UPDATE_BUG_DATA' => 'event_bug_hooks',
            'EVENT_BUGNOTE_ADD' => 'bugnote_add_hook',
            'EVENT_LAYOUT_BODY_END' => 'layout_body_end_hook',
            'EVENT_CORE_READY' => 'core_ready_hook',
        ];
    }

    function core_ready_hook()
    {
        $custom_field = plugin_config_get('custom_field');
        if (!$custom_field['id'] && $custom_field['create'] != false) {
            require 'core/create_custom_field.php';
        }
    }

    /*
     * On bugnote create
     */
    public function bugnote_add_hook($p_event)
    {

        $issue_id = $_POST['bug_id'];
        $p_bug = bug_get($issue_id);

        $this->event_bug_hooks($p_event, $p_bug, $issue_id);

        return;
    }


    public function event_bug_hooks($p_event, BugData $p_bug, $issue_id)
    {


        $issue_model = new ImaticMantisIssueModel();

        // Check if project is in config
        if (!$this->ImaticCheckProjectForSyhnchronize()) {
            return $p_bug;
        }

        // Check if issue is intern, if not, than can be synchronized
        if ($issue_model->imaticCheckIfIssueIsIntern($p_bug->id)) {
            return $p_bug;
        }

        $eventListener = new ImaticMantisEventListener;

        // constant name of  webhook like : mantis:issue_created -> defined in core/constant.php // Jira use same
        $p_bug->webhookEvent = constant($p_event);

        //Check if issue is intern and save isssue as intern to DB // case update issue check if issue is intern if is intern synchronize will be stopped
        if (isset($_POST['synchronize_to_jira']) && !empty($_POST['synchronize_to_jira']) && $_POST['synchronize_to_jira'] === 'intern_issue') {

            $issue_model->imaticInsertInternIssue($issue_id);
            return $p_bug;
        }


        switch ($p_event) {
            case 'EVENT_UPDATE_BUG_DATA':
                $eventListener->onUpdateIssue($p_bug);
                return $p_bug;
            case
            'EVENT_REPORT_BUG':
                $eventListener->onNewIssue($p_bug);
                break;
            case 'EVENT_BUGNOTE_ADD':
                $eventListener->onNewBugnote($issue_id, $p_bug);
                break;
        }
    }

    public function report_bug_form_hook()
    {
        // If not project id in arr, checkbox will not be created
        if (!$this->ImaticCheckProjectForSyhnchronize()) {
            return;
        }

        require __DIR__ . '/inc/send_to_jira_checkbox.php';
    }


    private function ImaticCheckProjectForSyhnchronize()
    {

        $projects_for_synch = plugin_config_get('project_for_synchronize');

        // If plugin ImaticProjectSelection not installed this imaticProject not exists in url
        if (!empty($_GET['imaticProject'])) {
            $poject_id = gpc_get_int('imaticProject');
        } else {
            $poject_id = helper_get_current_project();
        }

        if (!in_array($poject_id, (array)$projects_for_synch)) {
            return;
        }

        return true;
    }


    public function layout_body_end_hook($p_event)
    {

        $logger = new ImaticMantisDblogger();
        $all_logs = $logger->getAllLogs();

        // uncomment for pagination // Pagination is not finished correctly // also in gitignore pagination files
        //        echo '<script type="text/javascript" src="' . plugin_file('jquery.simplePagination.js') . '"></script>';
        //        echo '<link type="text/css" rel="stylesheet" href="' . plugin_file('simplePagination.css') . '"/>';
        echo '<script id="imaticSynchronizerLogs" data-data="' . htmlspecialchars(json_encode($all_logs)) . '"  src="' . plugin_file('filter_logs.js') . '"></script>';
    }
}
