<?php

namespace Imatic\Mantis\Synchronizer;

class ImaticMantisApi
{
    private $url;
    private $token;
    private $issue_model;
    private $event_issue_type;
    private $callapi_results;
    private $issue_data;
    private $bugnote = null;

    public function __construct()
    {
        require __DIR__ . '../../config/config.php';

        $this->url = $api_url;
        $this->token = $user_api_token;

        $this->issue_model = new ImaticMantisIssueModel;
        $this->db_looger = new ImaticMantisDbLogger;
    }

    public function createIssue($issue_data)
    {
        $this->issue_data = $issue_data;
        $this->event_issue_type = EVENT_REPORT_BUG;

        $this->callapi_results = $this->callApi($issue_data, 'POST');

        //If error insert data to queue table, for later synchronization
        if (isset($this->callapi_results->error) && !empty($this->callapi_results->error)) {

            $queue_issue_data = $this->issue_model->imaticQueueIdAndMethodExists($this->issue_data->issue->issue_id, $this->event_issue_type);

            if (!$queue_issue_data && empty($queue_issue_data)) {
                $this->issue_model->imaticInsertNotSuccessIssueData($this->issue_data, $this->issue_data->issue->issue_id, $this->event_issue_type);
            }
        }

        $this->imaticCallDbLog();

        return $this->type_results;
    }


    public function updateIssue($issue_data, $note = null)
    {
        // for log just bugnote not issue update
        $this->bugnote = $note;

        $this->issue_data = $issue_data;

        // If event is update or update->created bugnote
        if ($this->event_issue_type = isset($this->issue_data->notes) && !empty($this->issue_data->notes) ? EVENT_BUGNOTE_ADD : EVENT_UPDATE_BUG_DATA) ;

        //call api
        $this->callapi_results = $this->callApi($issue_data, 'PATCH');

        //If error insert data to queue table, for later synchronization
        if (isset($this->callapi_results->error) && !empty($this->callapi_results->error)) { // STOPKA TU TREBA ROZDELIT UPDATE issue od update note v logery dať preč processs result dat len logger a tu do queue

            $this->issue_model->imaticInsertNotSuccessIssueData($this->issue_data, $this->issue_data->issue->issue_id, $this->event_issue_type);

        }

        $this->imaticCallDbLog();


        return $this->type_results;
    }


    private function imaticCallDbLog()
    {

        $logger = new ImaticMantisDbLogger();

        $this->type_results = isset($this->callapi_results->error) && !empty($this->callapi_results->error) ? 'error' : 'info';

        switch ($this->event_issue_type) {
            case EVENT_REPORT_BUG:
                if ($this->type_results == 'error') {
                    $logger->setSended(false);
                    $logger->setMessage('Issue with ID: ' . $this->issue_data->issue->issue_id . ' was not successfuly sended. [Webhook event] ' . $this->event_issue_type);
                } else {
                    $logger->setMessage('Issue with ID: ' . $this->issue_data->issue->issue_id . ' was succesfully sended. [Webhook event] ' . $this->event_issue_type);
                }
                break;
            case EVENT_UPDATE_BUG_DATA:
                if (!$this->bugnote) {
                    if ($this->type_results == 'error') {
                        $logger->setSended(false);
                        $logger->setMessage('Issue with ID: ' . $this->issue_data->issue->issue_id . ' was not successfuly updated. [Webhook event] ' . $this->event_issue_type);
                    } else {
                        $logger->setMessage('Issue with ID: ' . $this->issue_data->issue->issue_id . ' was succesfully updated. [Webhook event] ' . $this->event_issue_type);
                    }
                }
                break;
            case EVENT_BUGNOTE_ADD:
                if ($this->bugnote) {
                    $logger->setBugnoteId($this->issue_data->notes[0]['id']);
                    if ($this->type_results == 'error') {
                        $logger->setSended(false);
                        $logger->setMessage('Issue bugnote with issue with ID: ' . $this->issue_data->issue->issue_id . ' was not successfuly updated. [Webhook event] ' . $this->event_issue_type);
                    } else {
                        $logger->setMessage('Issue bugnote with issue with ID: ' . $this->issue_data->issue->issue_id . ' was succesfully updated. [Webhook event] ' . $this->event_issue_type);
                    }
                }
                break;
        }

        $logger->setIssueId($this->issue_data->issue->issue_id);
        $logger->setLogLevel($this->type_results);
        $logger->setWebhookEvent($this->event_issue_type);
        $logger->setProjectId(bug_get_row($this->issue_data->issue->issue_id)['project_id']); //Optimize  ?
        $logger->log();

    }


    // Call curl and send
    private function callApi($issue_data, $method): ImaticMantisCurl //: MantisResponse
    {
        $curl = new ImaticMantisCurl();
        $curl->url($this->url);
        $curl->data(json_encode($issue_data));
        $curl
            ->handlerToken($this->token)
            ->method($method)
            ->send();

        $curl->close();

        return $curl;
    }

}