<?php

namespace Imatic\Mantis\Synchronizer;

use stdClass;

class ImaticMantisIssueModel
{

    private $issue_data;
    private  $parsed_issue_data;
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


    public function imaticInsertNotSuccessIssueData($issue, $issue_id, $method_type)
    {
        $db = db_get_table('imatic_synchronizer_bug_queue');

        $issue = json_encode($issue);

        $db_now = db_now();
        db_param_push();
        $t_query = 'INSERT INTO ' . $db . '
                        ( issue_id, method_type, issue, last_updated)
                      VALUES
                        ( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';

        db_query($t_query, array((int)$issue_id, $method_type, $issue, $db_now));

        return db_affected_rows($db);
    }

    public function imaticUpdatdeSynchronizedStatusInQueue($self_issue_id, $method_type, $status = false)
    {
        $db = db_get_table('imatic_synchronizer_bug_queue');

        $db_now = db_now();
        db_param_push();

        $sql = "UPDATE " . $db . " SET synchronized=$status WHERE issue_id=$self_issue_id AND method_type = '$method_type'";

        db_query($sql);


        $rows_updated = db_affected_rows();
        return $rows_updated;
    }


    public function imaticQueueIdAndMethodExists($self_issue_id, $method_type)
    {

        $db = db_get_table('imatic_synchronizer_bug_queue');

        $t_query = "SELECT * From $db WHERE issue_id= $self_issue_id AND method_type = '$method_type'";


        $t_result = db_query($t_query);

        $t_res = [];
        while ($row = db_fetch_array($t_result)) {
            $t_res[] = $row;
        }

        return $t_res;
    }

    public function imaticInsertInternIssue($issue_id)
    {

        $db = db_get_table('imatic_synchronizer_bug');

        db_param_push();
        $t_query = 'INSERT INTO ' . $db . "
                        ( issue_id, intern_issue)
                      VALUES
                        ( " . db_param() . ', ' . db_param() . ')';

        db_query($t_query, array((int)$issue_id, 1,));

        return db_affected_rows($db);
    }


    public function imaticCheckIfIssueIsIntern($issue_id)
    {

        $db = db_get_table('imatic_synchronizer_bug');

        $t_query = 'SELECT intern_issue
                              FROM      ' . $db . '   WHERE issue_id= ' . $issue_id;

        $t_result = db_query($t_query);
        $t_row = db_fetch_array($t_result);

        if (isset($t_row['intern_issue']) && !empty($t_row['intern_issue'])) {
            return $t_row['intern_issue'];
        }
        return;
    }

}