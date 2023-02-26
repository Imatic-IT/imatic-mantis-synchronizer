<?php

require 'core/require.php';

//CONTROLLERS
use Imatic\Mantis\Synchronizer\ImaticWebhook;
use Imatic\Mantis\Synchronizer\ImaticMantisDbLogger;

//MODELS
use Imatic\Mantis\Synchronizer\ImaticMantisDbloggerModel;
use Imatic\Mantis\Synchronizer\ImaticMantisIssueModel;
use Imatic\Mantis\Synchronizer\ImaticMantisEventListener;

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
            'synch_threshold' => [
                'send_issue_threshold' => 50,
                'send_bugnote_threshold' => 50,
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
        
        return [0 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_bug'), "
				issue_id                        								I,
				intern_issue                    								I
			"]], 1 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_bug_queue'), "
				issue_id                      I,
                method_type	        		  C(32)             DEFAULT \" ' ' \",
				resended					  L                 DEFAULT \" '0' \" ,
				issue						  JSON,
                last_updated			      I		   NOTNULL  DEFAULT '" . db_now() . "'
			"]], 2 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
				issue_id                      I,
				bugnote_id                    I                  DEFAULT \" '0' \" ,
                log_level   	        	  C(32)	         	 DEFAULT \" ' ' \",
                webhook_event	        	  C(32)	         	 DEFAULT \" ' ' \",
                message	        			  C(200)		     DEFAULT \" ' ' \",
				sended						  L                  DEFAULT \" '0' \" ,               
			"]], 3 => ['AddColumnSQL', [db_get_table('imatic_synchronizer_bug_queue'), "
				webhook_id						I,
				webhook_name					C(32)	         	 DEFAULT \" ' ' \"
			"]], 4 => ['AddColumnSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
				webhook_id						I,
				webhook_name					C(32)	         	 DEFAULT \" ' ' \"
			"]], 5 => ['AddColumnSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
                 date_submitted			      I	        NOTNULL  DEFAULT '" . db_now() . "'
			"]], 6 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_webhooks'), "
				id							I		       PRIMARY NOTNULL AUTOINCREMENT,
                name	        		  C(120)           NOTNULL  ,
                url	        		      C(200)           NOTNULL  DEFAULT \" ' ' \",
				status					   L                 DEFAULT \" '0' \" ,
				projects				   JSON,           
                date_submitted			    I	           NOTNULL  DEFAULT '" . db_now() . "'
			"]], 7 => ['DropColumnSQL', [db_get_table('imatic_synchronizer_webhooks'), "
                 status
			"]], 8 => ['AddColumnSQL', [db_get_table('imatic_synchronizer_webhooks'), "
                 status					   C(10)
			"]], 9 => ['AddColumnSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
                 status_code			      c(32)	        
			"]], 10 => ['AddColumnSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
				id							I		       PRIMARY NOTNULL AUTOINCREMENT
			"]], 11 => ['AddColumnSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
				resended					  c(32),                 
				issue						  JSON
			"]], 12 => ['DropColumnSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
                 sended
			"]],];
    }
    
    public function hooks(): array
    {
        return [
            'EVENT_REPORT_BUG_FORM' => 'prevention_catch_event_from_api',
            'EVENT_REPORT_BUG' => 'event_bug_hooks',
            'EVENT_UPDATE_BUG_DATA' => 'event_bug_hooks',
            'EVENT_BUGNOTE_ADD' => 'bugnote_add_hook',
            'EVENT_LAYOUT_BODY_END' => 'layout_body_end_hook',
            'EVENT_UPDATE_BUG_FORM' => 'prevention_catch_event_from_api',
            'EVENT_UPDATE_BUG_STATUS_FORM' => 'prevention_catch_event_from_api',
            'EVENT_BUGNOTE_ADD_FORM' => 'prevention_catch_event_from_api',];
    }
    
    /*
     * On bugnote create
     */
    public function bugnote_add_hook($p_event)
    {
        $issue_id = $_POST['bug_id'];
        $p_bug = bug_get($issue_id);
        
        $db_loger = new ImaticMantisDbloggerModel();
        $log = $db_loger->imaticGetLogById($issue_id);
        
        if (!$log){
            return $p_bug;
        }
        
        if ($p_event == 'EVENT_BUGNOTE_ADD') {
            // If threshold is bigger than 50(is ist private view state) send private bugnote also
            if (plugin_config_get('synch_threshold')['send_bugnote_threshold'] <= 50) {
                // If issue is private do not synchronize issue
                if (isset($_POST['private'])) {
                    if ($_POST['private'] == 'on' || $p_bug->view_state == 50) {
                        return $p_bug;
                    }
                }
            }
        }

        $this->event_bug_hooks($p_event, $p_bug, $issue_id);
    
        return;
    }
    
    
    public function event_bug_hooks($p_event, BugData $p_bug, $issue_id)
    {

        // Prevention before creating an issue from the API
        if (!$_POST['synchronize_issue']) {
            return $p_bug;
        }
        $issue_model = new ImaticMantisIssueModel();
        
        if ($p_event == 'EVENT_REPORT_BUG') {
            // If threshold is bigger than 50(is ist private view state) send private issue also
            if (plugin_config_get('synch_threshold')['send_issue_threshold'] <= 50) {
                // If issue is private do not synchronize issue
                if ($_POST['view_state'] == 50 || $p_bug->view_state == 50) {
                    return $p_bug;
                }
            }
        }
        
        // Check if project is in config
        if (!$this->ImaticCheckProjectForSyhnchronize()) {
            return $p_bug;
        }
        
        // Check if issue was synchronized before. For Example issue is changed from private to public, issue must be created first
        // If is non synch issue event will be changed to EVENT_REPORT_BUG like created issue
        $logger = new ImaticMantisDbloggerModel();
        $log = $logger->imaticGetLogById($p_bug->id);
        
        if (empty($log)) {
            $p_event = 'EVENT_REPORT_BUG';
        }
        // End check
        
        $eventListener = new ImaticMantisEventListener;
        
        // constant name of  webhook like : mantis:issue_created -> defined in core/constant.php // Jira use same
        $p_bug->webhookEvent = constant($p_event);
        switch ($p_event) {
            case 'EVENT_UPDATE_BUG_DATA':
                $eventListener->onUpdateIssue($p_bug);
                return $p_bug;
            case
            'EVENT_REPORT_BUG':
                $eventListener->onNewIssue($p_bug);
                return $p_bug;
            case 'EVENT_BUGNOTE_ADD':
                $eventListener->onNewBugnote($issue_id, $p_bug);
                break;
        }
        return $p_bug;
    }
    
    public function prevention_catch_event_from_api()
    {
        // If not project id in arr, checkbox will not be created
        if (!$this->ImaticCheckProjectForSyhnchronize()) {
            return;
        }
        
        // For Prevent catching event from API (API POST request does not have this $_POST field and method event_bug_hooks will not send this issue from API like self created issue )
        require __DIR__ . '/inc/synchronize_issue_checkbox.php';
    }
    
    
    private function ImaticCheckProjectForSyhnchronize()
    {
        
        
        $wh = new ImaticWebhook();
        
        $webhooks_projects = $wh->getWebhooksProjects();
        
        // If plugin ImaticProjectSelection not installed this imaticProject not exists in url
        if (!empty($_GET['imaticProject'])) {
            $project_id = gpc_get_int('imaticProject');
        } else {
            $project_id = helper_get_current_project();
        }
        
        if (!in_array($project_id, $webhooks_projects)) {
            return;
        }
        return true;
        
    }
    
    public function layout_body_end_hook($p_event)
    {
        
        echo '<link rel="stylesheet" type="text/css" href="' . plugin_file('css/style.css') . '" />';
        
        echo '<script  src="' . plugin_file('js/datepickerinput.js') . '&v=' . $this->version . '"></script>';
        echo '<script  src="' . plugin_file('js/app.js') . '&v=' . $this->version . '"></script>';
        
        //SELECT 2
        echo '<link rel="stylesheet" type="text/css" href="' . plugin_file('css/select2.min.css') . '" />';
        echo '<script  src="' . plugin_file('js/select2.full.min.js') . '"></script>';
        echo '<script  src="' . plugin_file('js/webhook.js') . '&v=' . $this->version . '"></script>';
        
        // Date range picker from https://daterangepicker.com/
        echo '<link rel="stylesheet" type="text/css" href="' . plugin_file('css/daterangepicker.css') . '" />';
        echo '<script  src="' . plugin_file('js/daterangepicker.min.js') . '"></script>';
        echo '<script  src="' . plugin_file('js/moment.min.js') . '"></script>';
    }
}
