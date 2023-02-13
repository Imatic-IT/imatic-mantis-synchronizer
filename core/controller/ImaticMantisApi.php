<?php

namespace Imatic\Mantis\Synchronizer;
class ImaticMantisApi
{
    private $issue_model;
    private $event_issue_type;
    private $webhook_result;
    private $issue_data;

    public function __construct()
    {
        $this->issue_model = new ImaticMantisIssueModel;
        $this->db_looger = new ImaticMantisDbLogger;
    }

    public function createIssue($issue_data)
    {
        $this->issue_data = $issue_data;
        $this->event_issue_type = EVENT_REPORT_BUG;
        //-----------------------
        $wh = new ImaticWebhook();
        $webhooks = $wh->getWebhooks();
        foreach ($webhooks as $key => $webhook) {

            if ($webhook['status'] == 'on') {

                if (in_array($issue_data->issue->project_id, $webhook['projects'])) {

                    $this->webhook_result = $wh->sendWebhook($issue_data, $webhook['url']);

                    $this->imaticCallDbLog($key, $webhook['name']);
                }
            }
        }
    }


    public function updateIssue($issue_data, $note = null)
    {

        $this->issue_data = $issue_data;
        // If event is update or update->created bugnote
        if ($this->event_issue_type = isset($this->issue_data->notes) && !empty($this->issue_data->notes) ? EVENT_BUGNOTE_ADD : EVENT_UPDATE_BUG_DATA) ;
        $wh = new ImaticWebhook();
        $webhooks = $wh->getWebhooks();
        foreach ($webhooks as $key => $webhook) {
            if (in_array($issue_data->issue->project_id, $webhook['projects'])) {

                $this->webhook_result = $wh->sendWebhook($issue_data, $webhook['url']);

                $this->imaticCallDbLog($key, $webhook['name']);
            }
        }
    }


    private function imaticCallDbLog($webhook_id, $webhook_name)
    {

        $issue_json = json_encode($this->issue_data);

        $logger = new ImaticMantisDbLogger();
        if ($this->webhook_result['status'] >= 400 || $this->webhook_result['status'] == 0) {
            $this->type_results = 'error';
            $logger->setResended('false');
        } else {
            $this->type_results = 'success';
        }
        if (isset($this->issue_data->notes[0]['id']) && !empty($this->issue_data->notes[0]['id'])) {
            $logger->setBugnoteId($this->issue_data->notes[0]['id']);
        }
        $logger->setIssueId($this->issue_data->issue->issue_id);
        $logger->setLogLevel($this->type_results);
        $logger->setWebhookEvent($this->event_issue_type);
        $logger->setProjectId(bug_get_row($this->issue_data->issue->issue_id)['project_id']); //Optimize  ?
        $logger->setWebhookId($webhook_id);
        $logger->setWebhookName($webhook_name);
        $logger->setStatusCode($this->webhook_result['status'] );
        $logger->setIssueJson($issue_json);
        $logger->log();
    }
}