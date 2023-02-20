"use strict";
selectMultipleCheckboxex();
filterLogsBuildQuery();
function selectMultipleCheckboxex() {
    let endCheckbox;
    let checkboxes;
    checkboxes = document.querySelectorAll("td input[type='checkbox']");
    let startCheckbox = null;
    let lastChecked = null;
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].addEventListener("click", function (event) {
            if (event.shiftKey) {
                let start = Array.from(checkboxes).indexOf(this);
                let end = Array.from(checkboxes).indexOf(startCheckbox);
                if (start > end) {
                    [start, end] = [end, start];
                }
                for (let j = start; j <= end; j++) {
                    checkboxes[j].checked = lastChecked.checked;
                }
            }
            else {
                startCheckbox = this;
            }
            lastChecked = this;
        });
    }
}
function filterLogsBuildQuery() {
    const logFilterForm = document.querySelector('#log_filter_form');
    logFilterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(logFilterForm);
        const dateRange = formData.get('log_filter_daterange');
        const issueId = formData.get('issue_id');
        const bugnoteId = formData.get('bugnote_id');
        const logLevel = formData.get('log_level');
        const webhookEvent = formData.get('webhook_event');
        const resended = formData.get('resended');
        const [startDate, endDate] = getDateRange(dateRange);
        const params = {
            'issue_id': issueId,
            'bugnote_id': bugnoteId,
            'log_level': logLevel,
            'webhook_event': webhookEvent,
            'resended': resended,
            'start_date': startDate,
            'end_date': endDate
        };
        const queryString = http_build_query(params);
        submitFormWithQueryString(logFilterForm, queryString);
        function getDateRange(dateRange) {
            const [startDateStr, endDateStr] = dateRange.split(" - ");
            const startDateParts = startDateStr.split(".");
            const endDateParts = endDateStr.split(".");
            const startDate = new Date(Date.UTC(parseInt(startDateParts[2]), parseInt(startDateParts[1]) - 1, parseInt(startDateParts[0]), 0, 0, 0));
            const endDate = new Date(Date.UTC(parseInt(endDateParts[2]), parseInt(endDateParts[1]) - 1, parseInt(endDateParts[0]), 0, 0, 0));
            return [startDate.getTime() / 1000, endDate.getTime() / 1000];
        }
        function http_build_query(params) {
            const entries = Object.entries(params).filter(([key, value]) => value !== null && value !== undefined && value !== '');
            const query = new URLSearchParams(entries).toString();
            return query;
        }
        function submitFormWithQueryString(form, queryString) {
            form.action += `&${queryString}`;
            form.submit();
        }
    });
}
