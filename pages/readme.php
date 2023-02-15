<?php
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));
layout_page_header();
layout_page_begin('manage_overview_page.php');
print_manage_menu('manage_plugin_page.php');
include 'links.php';
?>


<div class="col-md-12 col-xs-12">

    <h1> Imatic Mantis synchronizer</h1>

    <h2> Mantis webhook</h2>

    <a href="https://www.imatic.cz/"><img src="<?php echo plugin_file('imatic-logo.png') ?>" alt="IMATIC"></a>


    <h2> Features</h2>

    <p>Create webhook event on:</p>

    <ul>
        <li>create issue</li>
        <li>update issue</li>
        <li>create bugnote</li>
    </ul>

    <h2> Requirements</h2>
    <ul>
        <li>
            <a href="https://www.mantisbt.org/">Mantis bugtracker</a>
        </li>
        <li>
            <a href="https://www.postgresql.org/">PostgreSQL</a>
        </li>
        <li>
            <a href="https://github.com/Imatic-IT/imatic-mantis-synchronizer">Imatic mantis synchronizer</a>
        </li>
    </ul>


    <h3> Installation</h3>

    <ul>
        <li>
            Copy all files from
            <a href="https://github.com/Imatic-IT/imatic-mantis-synchronizer">
                imatic mantis synchronizer
            </a>
            into plugins/ImaticSynchronizer.
        </li>
        <li>
            In mantis plugins, install
            <a href="https://github.com/Imatic-IT/imatic-mantis-synchronizer">
                imatic mantis synchronizer
            </a>
        </li>

    </ul>


    <h3> Settings</h3>

    <ul>
        <li>
            in ImaticSynchronizer plugin config page, create new webhook
        </li>
    </ul>


    <h3> Link to issue in another app</h3>

    <ul>
        <li>
            If issue is synchronized you can view his link to another app in
            <a href="https://support.mantishub.com/hc/en-us/articles/204274065-Adding-custom-fields">
                custom field (create)
            </a>
        </li>
        <li>
            <a href="https://documenter.getpostman.com/view/29959/mantis-bug-tracker-rest-api/7Lt6zkP">
                Mantis API
            </a>
            can update this custom field with issue link, for update you will need custom field id.
        </li>
        <li>
            Update (patch method) you can see
            <a href="https://documenter.getpostman.com/view/29959/mantis-bug-tracker-rest-api/7Lt6zkP#57dff6bc-64d5-7f05-23e2-2073ca85e87f">
                here

            </a>
        </li>
    </ul>


    <h3>Details</h3>

    <h5>
        <strong> POST</strong>
    </h5>
    <ul>
        <li>Post looks like
            <a href="https://documenter.getpostman.com/view/29959/mantis-bug-tracker-rest-api/7Lt6zkP#2d3878c7-4195-42f7-53b7-9cc11f7501f4">MANTIS
                API POST
            </a>
        </li>
        <li>
            You can specify your post details in
            <strong>
                ImaticSynchronizer/core/model/ImaticMantisIssueModel.php
            </strong>
        </li>
        <li>
            <a data-toggle="modal"
               data-target="#readmeIssue" href="">
                <i class="fa fa-eye"> See issue json
                </i>
            </a>

            <div class="modal fade" id="readmeIssue" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="modalLabel">Issue details </h4>
                        </div>
                        <div class="modal-body">

                            <p>Created issue</p>
                            <?php
                            $issue_string = '{"issue":{"issue_id":380,"project_id":3,"summary":"asd","description":"adw","additional_information":"","category":{"id":1,"name":"General"},"handler":{"name":"user0"},"view_state":{"id":10,"name":"public"},"status":{"id":10,"name":"new"},"priority":{"id":30,"name":"normal"},"severity":{"id":50,"name":"minor"},"reproducibility":{"id":70,"name":"have not tried"},"sticky":{"scalar":"false"}},"webhookEvent":"mantis:issue_created","url":"localhost:8888","issue_event_type_name":"issue_created"}';
                            $issue_object = json_decode($issue_string);
                            echo '<pre>';
                            print_r(json_encode($issue_object, JSON_PRETTY_PRINT));
                            echo '</pre>';
                            ?>

                            <p>Updated issue</p>
                            <?php
                            $issue_string = '{"issue":{"issue_id":380,"project_id":3,"summary":"asd","description":"adw","additional_information":"","category":{"id":1,"name":"General"},"handler":{"name":"administrator"},"view_state":{"id":10,"name":"public"},"status":{"id":30,"name":"acknowledged"},"priority":{"id":30,"name":"normal"},"severity":{"id":50,"name":"minor"},"reproducibility":{"id":70,"name":"have not tried"},"sticky":{"scalar":"false"}},"webhookEvent":"mantis:issue_updated","url":"localhost:8888","issue_event_type_name":"issue_updated"}';
                            $issue_object = json_decode($issue_string);
                            echo '<pre>';
                            print_r(json_encode($issue_object, JSON_PRETTY_PRINT));
                            echo '</pre>';
                            ?>

                            <p>Create bugnote</p>
                            <?php
                            $issue_string = '{"issue":{"issue_id":380,"project_id":3,"summary":"asd","description":"adw","additional_information":"","category":{"id":1,"name":"General"},"handler":{"name":"user0"},"view_state":{"id":10,"name":"public"},"status":{"id":10,"name":"new"},"priority":{"id":30,"name":"normal"},"severity":{"id":50,"name":"minor"},"reproducibility":{"id":70,"name":"have not tried"},"sticky":{"scalar":"false"}},"webhookEvent":"mantis:bugnote_created","url":"localhost:8888","issue_event_type_name":"bugnote_created","notes":[{"id":"51","reporter":{"name":"administrator"},"text":"daw","view_state":{"id":10,"name":"public","label":"public"},"type":"note"}]}';
                            $issue_object = json_decode($issue_string);
                            echo '<pre>';
                            print_r(json_encode($issue_object, JSON_PRETTY_PRINT));
                            echo '</pre>';
                            ?>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
        </li>
    </ul>


</div>


<?php layout_page_end(); ?>

