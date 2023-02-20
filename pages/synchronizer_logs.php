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
$result_per_page = 10;
$log_page = 2;

$logs = $logger->getAllLogs();

if (!$logs) {
    ?>
    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="space-4"></div>
        <h1>No logs</h1>
    </div>
    <?php
    return;
}


$filtered_logs = [];

require 'filtered_logs.php';

$offset = ($log_page - 1) * $result_per_page;
$total_logs = count($filtered_logs);
$total_pages = ceil($total_logs / $result_per_page);
$paginated_logs = array_slice($filtered_logs, $offset, $result_per_page);

// Generate previous & next page url
if ($previous_page_number = $log_page == 1 ? $log_page : $log_page - 1) ;
if ($next_page_number = $log_page == $total_pages ? $total_pages : $log_page + 1) ;
$previous_page_url = plugin_page("synchronizer_logs") . '&result_per_page=' . $result_per_page . '&log_page=' . $previous_page_number . '&' . $query;
$next_page_url = plugin_page("synchronizer_logs") . '&result_per_page=' . $result_per_page . '&log_page=' . $next_page_number . '&' . $query;
// End Generate previous & next page url

?>

    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="space-4"></div>

        <div class="form-group ">
            <form id="log_filter_form"
                  action="<?php echo plugin_page('synchronizer_logs&result_per_page=' . $result_per_page . '&log_page=1') ?>"
                  method="post">
                <table id="log_filters" class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                    <tr class="buglist-headers">
                        <td class="<?php if (isset($daterange_start) && isset($daterange_end)) {
                            echo 'bg-active-log-filter';
                        } ?>">
                            Date range (
                            <?php
                            if (isset($daterange_start) && isset($daterange_end)) {
                                echo $daterange_start . ' - ' . $daterange_end;
                            }
                            ?>
                            )
                        </td>
                        <td class="<?php if (isset($params['issue_id'])) {
                            echo 'bg-active-log-filter';
                        } ?>">
                            Issue id
                        </td>
                        <td class="<?php if (isset($params['bugnote_id'])) {
                            echo 'bg-active-log-filter';
                        } ?>">
                            Bugnote id
                        </td>
                        <td class="<?php if (isset($params['log_level'])) {
                            echo 'bg-active-log-filter';
                        } ?>">
                            Log level
                        </td>
                        <td class="<?php if (isset($params['webhook_event'])) {
                            echo 'bg-active-log-filter';
                        } ?>">
                            Webhook event
                        </td>
                        <td class="<?php if (isset($params['resended'])) {
                            echo 'bg-active-log-filter';
                        } ?>">
                            Resended
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
                        <td>
                            <input class="log_filter" name="issue_id" type="search"
                                   value="<?php if (isset($params['issue_id'])) {
                                       echo $params['issue_id'];
                                   } ?>" placeholder="Bug ID">
                        </td>
                        <td>
                            <input class="log_filter" name="bugnote_id" value="<?php if (isset($params['bugnote_id'])) {
                                echo $params['bugnote_id'];
                            } ?>" type="search" placeholder="Bugnote ID"></td>
                        <td>
                            <select class="log_filter" name="log_level" id="log_level_filter">
                                <option value="">All</option>
                                <option value="error">Error</option>
                                <option value="success">Success</option>
                            </select>
                        </td>
                        <td>
                            <select class="log_filter" name="webhook_event" id="log_level_filter">
                                <option value="">All</option>
                                <option <?php if (isset($params['webhook_event']) && $params['webhook_event'] == 'mantis:issue_created') {
                                    echo 'selected';
                                } ?> value="mantis:issue_created">Mantis issue created
                                </option>
                                <option <?php if (isset($params['webhook_event']) && $params['webhook_event'] == 'mantis:issue_updated') {
                                    echo 'selected';
                                } ?> value="mantis:issue_updated">Mantis issue updated
                                </option>
                                <option <?php if (isset($params['webhook_event']) && $params['webhook_event'] == 'mantis:bugnote_created') {
                                    echo 'selected';
                                } ?> value="mantis:bugnote_created">Mantis bugnote created
                                </option>
                            </select>
                        </td>
                        <td>
                            <select class="log_filter" name="resended">
                                <option value="">All</option>
                                <option <?php if (isset($params['resended']) && $params['resended'] == 'not-tried') {
                                    echo 'selected';
                                } ?> value="not-tried">Not tried
                                </option>
                                <option <?php if (isset($params['resended']) && $params['resended'] == 'tried') {
                                    echo 'selected';
                                } ?> value="tried">Tried
                                </option>
                                <option <?php if (isset($params['resended']) && $params['resended'] == 'success') {
                                    echo 'selected';
                                } ?> value="success">Success
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="result_per_page" value="<?php echo $result_per_page ?>">
                            <input type="hidden" name="log_page" value="<?php echo $log_page ?>">
                            <button id="start_filter" class="btn btn-primary btn-sm">Filter</button>
                        </td>
                        <td>
                            <a class="btn btn-danger btn-sm"
                               href="<?php echo plugin_page('synchronizer_logs&result_per_page=50&log_page=1') ?>">Clear
                                filter</a>

                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
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
                        echo '<li class="page-item active disabled"><a class="page-link" href="' . plugin_page("synchronizer_logs") . '&result_per_page=' . $result_per_page . '&log_page=' . $i . '& ' . $query . '"> ' . $i . ' </a></li>';
                        
                    } else {
                        
                        echo '<li class="page-item"><a class="page-link" href="' . plugin_page("synchronizer_logs") . '&result_per_page=' . $result_per_page . '&log_page=' . $i . '& ' . $query . '" > ' . $i . ' </a></li>';
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

        <form id="logs_actions_form" method="POST" action="<?php echo plugin_page('log_actions') ?>">
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
                                <div class="modal fade" id="flipFlop-<?php echo $log['id'] ?>" tabindex="-1"
                                     role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <h4 class="modal-title" id="modalLabel">Issue details </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                echo "<p> ID - " . $log['id'] . "</p>";
                                                echo '<pre>';
                                                print_r(json_decode($log['issue'], true));
                                                echo '</pre>';
                                                ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                    Close
                                                </button>
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
                <button type="submit" class="btn btn-default btn-xs glyphicon glyphicon-ok"></button>
            </div>


        </form>
    </div>
<?php layout_page_end(); ?>

<?php
