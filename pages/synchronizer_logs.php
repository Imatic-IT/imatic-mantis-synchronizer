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
$logs =  $logger->getAllLogs();

if (!$logs) return;
?>
    <input id="imaticSynchronizerLogs" <?php echo 'data-data="' . htmlspecialchars(json_encode($logs)) . '"' ?>
           type="hidden">

    <div id="test_pagine"></div>

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
                <tbody id="">
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
        <div id="synchronizer_logs" class="">

            <div class="widget-main no-padding">
                <div class="table-responsive checkbox-range-selection">
                    <div id="logs-pagination"></div>
                    <table id="buglist" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="buglist-headers">
                            <td></td>
                            <?php
                            // Create table fields names from db logs
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
    </div>


<?php

layout_page_end();