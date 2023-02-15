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
$per_page = 10;
$log_page = 2;

//$filter = 'error';

// Get log page & result per page
if (isset($_GET['log_page']) && is_numeric($_GET['log_page'])) {
    $log_page = (int)$_GET['log_page'];
}
if (isset($_GET['result_per_page']) && is_numeric($_GET['result_per_page'])) {
    $result_per_page = (int)$_GET['result_per_page'];
}
$offset = ($log_page - 1) * $result_per_page;
$logs = $logger->getAllLogs();
// Filtrovanie logs
$filtered_logs = array();
foreach ($logs as $log) {
    if ($log['log_level'] === 'error') {

    }
    if (empty($filter) || strpos($log['log_level'], $filter) !== false) {
        $filtered_logs[] = $log;
    }
}
$total_logs = count($filtered_logs);
$total_pages = ceil($total_logs / $result_per_page);
$paginated_logs = array_slice($filtered_logs, $offset, $result_per_page);

// Generate previous & next page url
if ($previous_page_number = $log_page == 1 ? $log_page : $log_page - 1) ;
if ($next_page_number = $log_page == $total_pages ? $total_pages : $log_page + 1) ;
$previous_page_url = plugin_page("synchronizer_logs") . '&result_per_page=' . $result_per_page . '&log_page=' . $previous_page_number;
$next_page_url = plugin_page("synchronizer_logs") . '&result_per_page=' . $result_per_page . '&log_page=' . $next_page_number;
// End Generate previous & next page url

if (!$logs){
    ?>
    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="space-4"></div>
    <h1>No logs</h1>
    </div>
<?php
return;

}
?>

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

        <!--  Pagination-->
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item">
                    <a class="page-link" href="<?php echo $previous_page_url ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
                <?php
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i === $log_page) {
                        echo '<li class="page-item active disabled"><a class="page-link" href="' . plugin_page("synchronizer_logs") . '&result_per_page=' . $result_per_page . '&log_page=' . $i . '" > ' . $i . ' </a></li>';

                    } else {

                        echo '<li class="page-item"><a class="page-link" href="' . plugin_page("synchronizer_logs") . '&result_per_page=' . $result_per_page . '&log_page=' . $i . '" > ' . $i . ' </a></li>';
                    }
                }
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo $next_page_url ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="sr-only">Next</span>
                    </a>
                </li>
            </ul>
        </nav>

        <form id="logs_actions_form" method="post" action="<?php echo plugin_page('log_actions') ?>">
            <div id="synchronizer_logs" class="">

                <div class="widget-main no-padding">
                    <div class="table-responsive checkbox-range-selection">
                        <table id="buglist" class="table table-bordered table-condensed table-hover table-striped">
                            <thead>
                            <tr class="buglist-headers">
                                <td>#</td>
                                <?php
                                unset($logs[0]['id']);
                                foreach ($logs[0] as $key => $log) {

                                    echo '<td>' . ucfirst(str_replace("_", " ", $key)) . '</td>';
                                }
                                ?>
                            </tr>
                            </thead>

                            <tbody id="logs">
                            <?php foreach ($paginated_logs as $log): ?>
                                <?php
                                $date = date("d.m.Y", $log['date_submitted']);
                                $time = date("H:i:s", $log['date_submitted']);
                                ?>
                                <tr>
                                    <td><input type="checkbox" name="logs_id_arr[]" value="<?php echo $log['id'] ?>"
                                    </td>
                                    <td><?php echo $log['issue_id'] ?></td>
                                    <td><?php echo $log['bugnote_id'] ?></td>
                                    <td class="<?php echo $log['log_level'] ?>"><?php echo $log['log_level'] ?></td>
                                    <td><?php echo $log['webhook_event'] ?></td>
                                    <td><?php echo $log['webhook_id'] ?></td>
                                    <td><?php echo $log['webhook_name'] ?></td>
                                    <td><?php echo $date . ' ' . $time ?></td>
                                    <td><?php echo $log['status_code'] ?></td>
                                    <td class="<?php echo $log['resended'] ?>"><?php echo $log['resended'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal"
                                                data-target="#flipFlop-<?php echo $log['id'] ?>">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- The modal with issue details-->
                                <div class="modal fade" id="flipFlop-<?php echo $log['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <h4 class="modal-title" id="modalLabel">Issue details </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                echo "<p> ID - ".$log['id']. "</p>";
                                                echo '<pre>';
                                                print_r(json_decode($log['issue'], true));
                                                echo '</pre>';
                                                ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End the modal -->

                                </div>

                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="container-fluid badge-grey padding-2">
                <select class="" name="logs_actions" id="log_actions_selectbox">
                    <option value="resend_all_logs">Resend all error logs</option>
                    <option value="delete_all_logs">Delete all logs</option>
                    <option value="delete_success_logs">Delete success logs</option>
                    <option value="delete_error_logs">Delete error logs</option>
                    <option value="delete_selected_logs">Delete selected logs</option>
                </select>
                <button  type="submit" class="btn btn-default btn-xs glyphicon glyphicon-ok"></button>
            </div>


        </form>
    </div>
<?php layout_page_end(); ?>

<?php
