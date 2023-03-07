<?php


namespace Imatic\Mantis\Synchronizer;

use stdClass;

class ImaticMantisIssue
{

    private $issue_data;
    private ImaticMantisIssueModel $issue_model;


    public function __construct($issue_data = null)
    {
        $this->issue_data = $issue_data;
        $this->issue_model = new ImaticMantisIssueModel();
    }

    public function imaticParseData(): stdClass
    {

        return $this->issue_model->imaticParseData($this->issue_data);

    }

    public function imaticInsertSyncIssueId($issue_id){

        $this->issue_model->imaticModelInsertSynchIssueId($issue_id);
    }

    public function imaticSyncGetIssue($issue_id){

        $this->issue_model->imaticModelInsertSynchIssueId($issue_id);

    }
}