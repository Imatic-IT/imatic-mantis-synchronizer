<?php

namespace Imatic\Mantis\Synchronizer;

use stdClass;

class ImaticMantisIssueModel
{

    private $issue_data;
    private $parsed_issue_data;
    private stdClass $parsed_issue_data_object;
    private ImaticMantisBugnotes $bugnote;

    /**
     * @param $issue_data
     * @return stdClass
     */
    public function imaticParseData($issue_data): stdClass
    {

        $this->issue_data = $issue_data;
        $this->parsed_issue_data = [];
        $this->parsed_issue_data_object = new stdClass;
        $this->parsed_issue_data_object->issue = new stdClass;
        $this->bugnote = new ImaticMantisBugnotes;
        $p = $this->parsed_issue_data_object;

        $this->p_lang = lang_get_current();

        $p->webhookEvent = $this->issue_data->webhookEvent;
        $p->url = $_SERVER['HTTP_HOST'];
        $p->issue_event_type_name = substr($this->issue_data->webhookEvent, strpos($this->issue_data->webhookEvent, ":") + 1);

        $p->issue->issue_id = $this->issue_data->id;
        $p->issue->project_id = $this->issue_data->project_id;
        $p->issue->summary = $this->issue_data->summary;
        $p->issue->description = $this->issue_data->description;
        $p->issue->additional_information = $this->issue_data->additional_information;
        $p->issue->category = (object)[
            'id' => $this->issue_data->category_id,
            'name' => category_get_name($this->issue_data->category_id),
        ];
        $p->issue->handler = (object)[
            'name' => user_get_name($this->issue_data->handler_id),
        ];
        $p->issue->view_state = (object)mci_enum_get_array_by_id($this->issue_data->view_state, 'view_state', $this->p_lang);
        $p->issue->status = (object)mci_enum_get_array_by_id($this->issue_data->status, 'status', $this->p_lang);
        $p->issue->priority = (object)mci_enum_get_array_by_id($this->issue_data->priority, 'priority', $this->p_lang);
        $p->issue->severity = (object)mci_enum_get_array_by_id($this->issue_data->severity, 'severity', $this->p_lang);
        $p->issue->reproducibility = (object)mci_enum_get_array_by_id($this->issue_data->reproducibility, 'reproducibility', $this->p_lang);
        $p->issue->sticky = (object)((bool)$this->issue_data->sticky ? 'true' : 'false');

        return $p;
    }



    public function imaticModelInsertSyncIssueId($issue_id)
    {
        if (!$this->imaticModelSyncGetIssue($issue_id)) {

            $db = db_get_table('imatic_synchronizer_bug');

            db_param_push();
            $t_query = 'INSERT INTO ' . $db . "
            ( sync_issue_id)
            VALUES
            ( " . db_param() . ')';

            db_query($t_query, array((int)$issue_id));

            return db_affected_rows($db);
        }
        return ;
    }


    public function imaticModelSyncGetIssue($issue_id)
    {

        $db = db_get_table('imatic_synchronizer_bug');

        $t_query = 'SELECT sync_issue_id
                              FROM      ' . $db . '   WHERE sync_issue_id= ' . $issue_id;

        $t_result = db_query($t_query);
        $t_row = db_fetch_array($t_result);

        if (isset($t_row['sync_issue_id']) && !empty($t_row['sync_issue_id'])) {
            return $t_row['sync_issue_id'];
        }
        return;
    }
}
