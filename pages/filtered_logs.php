<?php

// Get log page & result per page
if (isset($_GET['log_page']) && is_numeric($_GET['log_page'])) {
    $log_page = (int)$_GET['log_page'];
}
if (isset($_GET['result_per_page']) && is_numeric($_GET['result_per_page'])) {
    $result_per_page = (int)$_GET['result_per_page'];
}

if ($_GET) {
    $filters = ['issue_id', 'bugnote_id', 'log_level', 'webhook_event', 'resended',];
    $params = [];
    
    foreach ($filters as $filter) {
        if (isset($_GET[$filter]) && !empty($_GET[$filter])) {
            $params[$filter] = $_GET[$filter];
        }
    };
    
    // Filter diff by parameters
    foreach ($logs as $log) {
        if (empty(array_diff_assoc($params, $log))) {
            $filtered_logs[] = $log;
        }
    }
    
    // Filter by daterange
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $filtered_logs_daterange = [];
        foreach ($filtered_logs as $log) {
            
            if (isset($log['date_submitted'])) {
            
                $log_date = $log['date_submitted'];
                $start_date = $_GET['start_date'];
                $end_date = $_GET['end_date'];

                // set dates to params
                $params['start_date'] = $start_date;
                $params['end_date'] = $end_date;
                if ($log_date >= $start_date && $log_date <= $end_date) {
                    $filtered_logs_daterange[] = $log;
                }
                
            } else {
                $filtered_logs[] = $log;
            }
        }
        $filtered_logs = $filtered_logs_daterange;
        
        // Formated info daterange for UI
        $daterange_start = date("d.m.Y", $_GET['start_date']);
        $daterange_end = date("d.m.Y", $_GET['end_date']);
        $daterange = $daterange_start . " - " . $daterange_end;
        
    }

    // If empty parameter show default all logs
    if (!$params) {
        $filtered_logs = $logs;
    }
    //Make query for pagination links
    $query = http_build_query($params);
}


