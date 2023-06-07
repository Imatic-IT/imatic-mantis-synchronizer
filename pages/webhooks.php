<?php

use Imatic\Mantis\Synchronizer\ImaticWebhook;

auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));


layout_page_header();
layout_page_begin('manage_overview_page.php');
print_manage_menu('manage_plugin_page.php');


$projects = project_get_all_rows();

$imatic_webhook = new ImaticWebhook();
$webhooks = $imatic_webhook->getWebhooks();

include 'links.php';
?>

    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>

        <div id="webhooks" style="background-color: #ccc;" class="col-md-3 col-xs-3">
            <div class="space-10"></div>


            <?php
            if ($webhooks) {

                foreach ($webhooks as $key => $webhook) {
                    echo '<p class="webhooks"><a data-webhook_id="' . $webhook['id'] . '" href="' . plugin_page('webhook_get') . '">' . $webhook['name'] . '</a></p>';
                }
            } else {
                echo '<p>No webhooks</p>';
            }

            ?>
        </div>

        <!-- CREATE WEBHOOK FORM -->
        <div id="create-new-webhook-form" style="display: none;" class="col-md-4 col-xs-4 ">
            <div class="form-container">
                <form action="<?php echo plugin_page('webhook_save') ?>" method="POST">
                    <input id="webhook_method" type="hidden" name="webhook_method" value="">
                    <input id="webhook_id" type="hidden" name="webhook_id" value="">
                    <label for="name">Name</label><br>
                    <input style="width: 60%;" id="webhook_name" class="" type="text" name="name">
                    <br>


                    <label for="url">URL</label><br>
                    <input style="width: 60%;" id="webhook_url" class="" type="text" name="url">
                    <br>
                    <hr>

                    <label for="status"><strong>Status</strong></label><br>
                    <label for="status">Enabled</label>
                    <input id="webhook_status" type="checkbox" name="status">
                    <br>
                    <hr>

                    <label for="events"><strong>Events</strong></label><br>
                    <label for="event_issue_created">Issue created</label>
                    <input type="checkbox" name="events[]" value="1">
                    <br>
                    <label for="event_issue_updated">Issue updated</label>
                    <input type="checkbox" name="events[]" value="2">
                    <br>
                    <label for="event_comment_created">Comment created</label>
                    <input type="checkbox" name="events[]" value="3">
                    <br>
                    <hr>

                    <div class="">
                        <label for="projects"><strong>Choose projects</strong></label><br>
                        <select id="webhook_projects" style="width: 60%;" class="project_select_two" name="projects[]"
                                multiple="multiple">
                            <?php
                            foreach ($projects as $key => $project) {
                                echo $project['name'];
                                echo '<option value="' . $project['id'] . '">' . $project['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <br>


                    <input id="submit-form" class="btn btn-primary btn-sm" type="submit" value="Create">
                    <input id="delete-webhook" formaction="<?php echo plugin_page('webhook_delete') ?>"
                           style="display: none" class="btn btn-danger btn-sm" type="submit" value="Delete">

                </form>
            </div>
        </div>


        <div class="col-md-2 col-xs-2">
            <button id="create-new-webhook" class="btn btn-secondary "><img height="20px" width="20px"
                                                                            src="<?php echo plugin_file('icons/icons8-webhook-48.png') ?>"
                                                                            alt="">Create a webhook
            </button>
        </div>
    </div>


<?php


layout_page_end();
