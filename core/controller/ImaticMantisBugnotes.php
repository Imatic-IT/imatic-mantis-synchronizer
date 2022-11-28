<?php

namespace Imatic\Mantis\Synchronizer;


class ImaticMantisBugnotes
{
    private $bugnotes_model;
    private $bugnotes;
    private $last_bugnote;
    private $parse_last_bugnote = [];
    private $last_bugnote_key;

    public function __construct()
    {
        $this->bugnotes_model = new ImaticMantisBugnotesModel;
    }


    public function imaticGetAllBugnotes($bug_id)
    {
        $this->bugnotes = $this->bugnotes_model->imaticGetAllBugnotesByIssueId($bug_id);

        $this->last_bugnote = $this->imaticGetLastBugnote();

        return $this->bugnotes;
    }

    public function imaticGetLastBugnote()
    {
        $this->last_bugnote_key = count($this->bugnotes) - 1;
        return $this->bugnotes[$this->last_bugnote_key];
    }

    public function imaticParseBugnotesData()
    {

        $p_lang = lang_get_current();

        $text_view_state = mci_enum_get_array_by_id($this->last_bugnote->view_state, 'view_state', $p_lang);
        $text_view_state['label'] = $text_view_state['name'];


        $this->parse_last_bugnote = [
            [
                'id' => $this->last_bugnote->id, // just case update bugnote
                'reporter' => ['name' => user_get_name($this->last_bugnote->reporter_id),],
                'text' => $this->last_bugnote->note, 'view_state' => $text_view_state,
                "type" => "note" // check types, for now I set just note
            ]
        ];

        return $this->parse_last_bugnote;
    }
}