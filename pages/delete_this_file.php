<?php
// Show logs
use Imatic\Mantis\Synchronizer\ImaticMantisDbLogger;

auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));
layout_page_header();
layout_page_begin('manage_overview_page.php');
print_manage_menu('manage_plugin_page.php');
include 'links.php';
$start_date = date("d.m.Y");
$end_date = date("d.m.Y", strtotime("+1 day"));
$daterange = $start_date . " - " . $end_date;
$logger = new ImaticMantisDblogger();
$logs = $logger->getAllLogs();


$url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$query_string = parse_url($url, PHP_URL_QUERY);
$params = explode("?", $query_string);

$logpage = "";
foreach ($params as $param) {
    $parts = explode("=", $param);
    if ($parts[0] == "logpage") {
        $logpage = $parts[1];
        break;
    }
}


pre_r($logpage);



if (!$logs) return;

?>
    <input id="imaticSynchronizerLogs" <?php echo 'data-data="' . htmlspecialchars(json_encode($logs)) . '"' ?>
           type="hidden">

    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="space-4"></div>

        <div class="form-group ">
            <table id="log_filters" class="table table-bordered table-condensed table-hover table-striped">
                <thead>
                <tr class="buglist-headers">
                    <td>
                        Date range
                    </td>
                    <td>Issue id</td>
                    <td>Bugnote id</td>
                    <td>
                        Log level
                    </td>
                    <td>
                        Webhook event
                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <input id="date-range-picker" type="text" name="log_filter_daterange"
                               <?php if ($daterange) { ?>value="<?php echo htmlspecialchars($daterange) ?>"<?php } ?>/>
                    </td>
                    <td><input class="log_filter" id="issue_id_filter" type="search" placeholder="Id"
                               data-filter="off">
                    </td>
                    <td><input class="log_filter" id="bugnote_id_filter" type="search" placeholder="Id"></td>
                    <td>
                        <select class="log_filter" name="log_filer" id="log_level_filter"
                                data-data="{filter:'off'}">
                            <option value="">All</option>
                            <option value="error">Error</option>
                            <option value="success">Success</option>
                        </select>
                    </td>
                    <td>
                        <input class="log_filter" id="webhook_event_filter" type="search" placeholder="mantis:"
                               value="mantis:">
                    </td>
                    <td>
                        <button id="start_filter" class="btn btn-secondary btn-sm">Filter</button>
                    </td>
                    <td>
                        <button id="clear_log_filter" class="btn btn-secondary btn-sm">Clear filter</button>
                    </td>
                </tr>
                </tbody>
            </table>

        </div>
        <div id="logs-pagination"></div>
        <form id="logs_actions_form" method="post" action="<?php echo plugin_page('log_actions') ?>">
            <div id="synchronizer_logs" class="">

                <div class="widget-main no-padding">
                    <div class="table-responsive checkbox-range-selection">
                        <table id="buglist" class="table table-bordered table-condensed table-hover table-striped">
                            <thead>
                            <tr class="buglist-headers">
                                <td></td>
                                <?php
                                // Create table fields names from db logs
                                unset($logs[0]['id']);
                                foreach ($logs[0] as $key => $log) {

                                    echo '<td>' . ucfirst(str_replace("_", " ", $key)) . '</td>';
                                }
                                ?>
                            </tr>
                            </thead>

                            <!--                        Logs are appends here-->
                            <tbody id="logs">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="container-fluid badge-grey padding-2">
                <select class="" name="logs_actions" id="log_actions_selectbox">
                    <option value="delete_all_logs">Delete all logs</option>
                    <option value="delete_success_logs">Delete success logs</option>
                    <option value="delete_error_logs">Delete error logs</option>
                    <option value="delete_selected_logs">Delete selected logs</option>
                </select>
                <button style="position: relative; top: -1px"  type="submit" class="btn btn-default btn-xs glyphicon glyphicon-ok"></button>

            </div>


        </form>
    </div>

    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#flipFlop">
        Click Me
    </button>

    <!-- The modal -->
    <div class="modal fade" id="flipFlop" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalLabel">Modal Title</h4>
                </div>
                <div class="modal-body">
                    Modal content...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php
layout_page_end();