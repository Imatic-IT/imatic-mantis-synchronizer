<?php

require 'core/require.php';

//CONTROLLERS
use Imatic\Mantis\Synchronizer\ImaticWebhook;
use Imatic\Mantis\Synchronizer\ImaticMantisIssue;
use Imatic\Mantis\Synchronizer\ImaticMantisBugnotes;

//MODELS
use Imatic\Mantis\Synchronizer\ImaticMantisDbLogger;
use Imatic\Mantis\Synchronizer\ImaticMantisIssueModel;
use Imatic\Mantis\Synchronizer\ImaticMantisDbloggerModel;
use Imatic\Mantis\Synchronizer\ImaticMantisEventListener;

//constant
require 'core/constant.php';

require_api('install_helper_functions_api.php');
require_api('bug_activity_api.php');


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
            ],
            'message_api_event' => "This is issue created from API",
            'allowed_pages' => array(
                'bug_update.php',
                'bug_report.php',
                'bugnote_add.php')
        ];
    }

    public function schema(): array
    {

        return [
            0 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_bug'), "
                id							I		       PRIMARY NOTNULL AUTOINCREMENT,
				sync_issue_id               I
			"]],
            1 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_bug_logger'), "
				issue_id                      I,
				bugnote_id                    I                  DEFAULT \" '0' \" ,
                log_level   	        	  C(32)	         	 DEFAULT \" ' ' \",
                webhook_event	        	  C(32)	         	 DEFAULT \" ' ' \",
				webhook_id					  I,
				webhook_name				  C(32)	         	 DEFAULT \" ' ' \",
                date_submitted			      I	        NOTNULL  DEFAULT '" . db_now() . "',
                status_code			          c(32),
                resended					  c(32)              DEFAULT \" ' ' \",
                issue						  JSON,
                id							  I		       PRIMARY NOTNULL AUTOINCREMENT
			"]], 2 => ['CreateTableSQL', [db_get_table('imatic_synchronizer_webhooks'), "
				id							I		       PRIMARY NOTNULL AUTOINCREMENT,
                name	        		    C(120)           NOTNULL  ,
                events	        		    JSON,
                url	        		        C(200)           NOTNULL  DEFAULT \" ' ' \",
				status					    C(10)                 DEFAULT \" '0' \" ,
				projects				    JSON,
                date_submitted			    I	           NOTNULL  DEFAULT '" . db_now() . "'
			"]]
        ];
    }

    public function hooks(): array
    {
        return [
            'EVENT_REPORT_BUG' => 'event_bug_hooks',
            'EVENT_UPDATE_BUG_DATA' => 'event_bug_hooks',
            'EVENT_BUGNOTE_ADD' => 'bugnote_add_hook',
            'EVENT_LAYOUT_BODY_END' => 'layout_body_end_hook',
        ];
    }

    /*
     * On bugnote create
     */
    public function bugnote_add_hook($p_event)
    {
        if (isset($_POST['bug_id'])) {
            $issue_id = $_POST['bug_id'];
            $p_bug = bug_get($issue_id);

            if ($p_event == 'EVENT_BUGNOTE_ADD') {
                // If threshold is bigger than 50(its private view state) send private bugnote also
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
        }
    }


    public function event_bug_hooks($p_event, BugData $p_bug, $issue_id)
    {

        // Check if the current PHP script is in the allowed pages
        if (!$this->checkAllowedPage()) {
            // The event was not triggered from an allowed page
            $this->ImaticLogApiIssue($p_event, $p_bug);
            return $p_bug;
        }

        if ($p_event == 'EVENT_REPORT_BUG') {
            // If threshold is bigger than 50(is ist private view state) send private issue also
            if (plugin_config_get('synch_threshold')['send_issue_threshold'] <= 50) {
                // If issue is private do not synchronize issue
                if ($_POST['view_state'] == 50 || $p_bug->view_state == 50) {
                    return $p_bug;
                }
            }
        }

        // Check if project is webhooks
        if (!$this->ImaticCheckProjectForSyhnchronize()) {
            return $p_bug;
        }

        // Check if issue was synchronized before. For Example issue is changed from private to public, issue must be created first
        // If is non synch issue event will be changed to EVENT_REPORT_BUG like created issue
        $issue = new ImaticMantisIssue();
        $synch_issue_id = $issue->imaticSyncGetIssue($p_bug->id);

        if (empty($synch_issue_id)) {
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

    public function checkAllowedPage()
    {
        // Define the list of pages where you want to allow the event
        $allowedPages = plugin_config_get('allowed_pages');
        $scriptName = basename($_SERVER['SCRIPT_FILENAME']);

        return in_array($scriptName, (array)$allowedPages);
    }

    /*
     * This method need for synchronization, issue before synchronization  is checked if is logged.
     */
    public function ImaticLogApiIssue($p_event, $p_bug)
    {
        //Log issue id into bug table, for check if issue is synchronized
        $issue = new ImaticMantisIssue();
        $issue->imaticInsertSyncIssueId($p_bug->id);
        //--

        $logger = new ImaticMantisDbLogger();
        $message = plugin_config_get('message_api_event');

        // Get last bugnote id
        if (isset($_POST['bugnote_text'])) {
            $bugnote_controller = new ImaticMantisBugnotes();
            $bugnotes = $bugnote_controller->imaticGetAllBugnotes($p_bug->id);
            $last_bugnote = $bugnote_controller->imaticGetLastBugnote();
            $logger->setBugnoteId($last_bugnote['id']);
        }

        $logger->setIssueId($p_bug->id);
        $logger->setLogLevel('api');
        $logger->setWebhookEvent(constant($p_event));
        $logger->setProjectId($p_bug->project_id);
        $logger->setStatusCode(200);
        $logger->setIssueJson(json_encode(['message' => $message]));
        $logger->log();

        return $p_bug;
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
        echo '<script  src="' . plugin_file('js/min/webhook.min.js') . '&v=' . $this->version . '"></script>';

        // Date range picker from https://daterangepicker.com/
        echo '<link rel="stylesheet" type="text/css" href="' . plugin_file('css/daterangepicker.css') . '" />';
        echo '<script  src="' . plugin_file('js/daterangepicker.min.js') . '"></script>';
        echo '<script  src="' . plugin_file('js/moment.min.js') . '"></script>';
    }
}
