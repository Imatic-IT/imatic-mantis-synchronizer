<?php

namespace Imatic\Mantis\Synchronizer;

class ImaticMantisEventListener
{

    private ImaticMantisAPi $mantisApi;

    public function __construct()
    {
        $this->mantisApi = new ImaticMantisApi();
    }

    public function onNewIssue($issue_data)
    {

        $issue = new ImaticMantisIssue($issue_data);
        $issue_array_data = $issue->imaticParseData();
        $this->mantisApi->createIssue($issue_array_data);

    }

    public function onUpdateIssue($issue_data)
    {

        $issue = new ImaticMantisIssue($issue_data);

        $issue_data = $issue->imaticParseData();

        $this->mantisApi->updateIssue($issue_data);
    }

    public function onNewBugnote($bug_id, $issue_data)
    {

        $issue = new ImaticMantisIssue($issue_data);
        $bugnote = new ImaticMantisBugnotes;


        $issue_data = $issue->imaticParseData();

        $bugnote->imaticGetAllBugnotes($bug_id);

        $issue_data->notes = $bugnote->imaticParseBugnotesData();

        $this->mantisApi->updateIssue($issue_data, true);


    }
}