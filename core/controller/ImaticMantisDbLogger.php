<?php


namespace Imatic\Mantis\Synchronizer;


class ImaticMantisDbLogger
{
    public $issue_id;
    public $log_level;
    public $bugnote_id = null;
    public $webhook_event;
    public $message;
    public $project_id;
    public $db_log_moddel;
    public $all_logs;
    public $webhook_id;
    public $webhook_name;
    public $status_code;
    public $resended;
    public $issue_json;


    public function __construct()
    {
        $this->db_log_moddel = new ImaticMantisDbloggerModel();
    }

    /**
     * @param mixed $issue_id
     */
    public function setIssueId($issue_id): void
    {
        $this->issue_id = $issue_id;
    }

    /**
     * @param mixed $log_level
     */
    public function setLogLevel($log_level): void
    {
        $this->log_level = $log_level;
    }

    /**
     * @param null $bugnote_id
     */
    public function setBugnoteId($bugnote_id): void
    {
        $this->bugnote_id = $bugnote_id;
    }

    /**
     * @param mixed $webhook_event
     */
    public function setWebhookEvent($webhook_event): void
    {
        $this->webhook_event = $webhook_event;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @param mixed $project_id
     */
    public function setProjectId($project_id): void
    {
        $this->project_id = $project_id;
    }

    public function log()
    {
        $this->db_log_moddel->imaticLog($this);
    }

    /**
     * @return mixed
     */
    public function getAllLogs()
    {
        return $this->db_log_moddel->imaticGetLogs();
    }

    /**
     * @return mixed
     */
    public function getWebhookId()
    {
        return $this->webhook_id;
    }

    /**
     * @param mixed $webhook_id
     */
    public function setWebhookId($webhook_id)
    {
        $this->webhook_id = $webhook_id;
    }

    /**
     * @return mixed
     */
    public function getWebhookName()
    {
        return $this->webhook_name;
    }

    /**
     * @param mixed $webhook_name
     */
    public function setWebhookName($webhook_name)
    {
        $this->webhook_name = $webhook_name;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @param mixed $status_code
     */
    public function setStatusCode($status_code)
    {
        $this->status_code = $status_code;
    }

    /**
     * @param mixed $resended
     */
    public function setResended($resended)
    {
        $this->resended = $resended;
    }

    /**
     * @param mixed $issue_json
     */
    public function setIssueJson($issue_json)
    {
        $this->issue_json = $issue_json;
    }

}