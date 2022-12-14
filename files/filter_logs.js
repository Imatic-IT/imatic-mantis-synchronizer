$(() => {

    // imaticSynchronizerLogs
    function getLogsData() {
        const el = document.querySelector('#imaticSynchronizerLogs');
        if (el == null) {
            return;
        }

        return JSON.parse(el.dataset.data);
    }


    let logs_data = getLogsData();
    let append_logs = $('#logs')
    let issue_id_filter = $('#issue_id_synchronizer_filter')
    let synchronizer_filter_log_filer = $('#synchronizer_filter_log_filer')
    let filters = $('.synch_log_filter')


    imaticAppenLogs(logs_data)
    clearFilter()
    // imaticChangePage()  // uncomment for pagination


    addMultipleEventListener(filters)

    // -------------------------------------------------------------------------------------------------------------------
    // --------------------------------PAGINATIOM ---------------------------------------------------------------------


    // -------------------------------------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------------------------------------

    function clearFilter() {
        let clear_log_filter = $('#clear_log_filter')

        clear_log_filter.on('click', function () {

            append_logs.empty()
            imaticAppenLogs(logs_data, false)

            // remove filters values

        })


    }


    // function filterToggle(el) {
    //
    //     let filter
    //     if (!el.val()) {
    //         filter = 'off'
    //     } else {
    //         filter = 'on'
    //     }
    //
    //     if (el == null) {
    //         return;
    //     }
    //     el.data('filter', filter)
    // }


    function addMultipleEventListener(element) {

        let filters_val = {}

        element.each(function (e, i) {
            let _this = $(this)

            _this.on('input change ', function () {
                let _name = _this.attr('id').replace('_filter', '');
                filters_val[_name] = (element[e].value).trim()
                console.log(filters_val[_name]);
                let new_data = logs_data.filter(function (item) {
                    for (var key in filters_val) {
                        if (item[key] === undefined || item[key] != filters_val[key]) return false;
                    }
                    return true;
                });

                append_logs.empty()

                // If empty filter append all logs
                if (!filters_val[_name]) {
                    new_data = null
                }

                imaticAppenLogs(new_data, false)
            });
        });

    }


// -------------------------------------------------------------------------------------------------------------------


//-----------------Append logs--------------
    function imaticAppenLogs(logs_data = null, create_pagination = true) {

        if (logs_data == null) {
            logs_data = getLogsData()
        }
        if (!logs_data) {
            return;
        }


        let log_per_page = 10
        let counter_page = 1

        logs_data.forEach((log, i) => {
            if (i >= log_per_page) {
                log_per_page = log_per_page + 10
                counter_page++

            }
            let format_date = new Date(log.date_submitted * 1000)
            format_date = format_date.toLocaleDateString('cs-CZ');
            format_date = format_date.replace(/ +(?=)/g, '')

            let hidden = ''
            if (counter_page > 1) {
                // hidden = 'display: none'  // Uncomment for paginate

            }

            console.log(log)
            append_logs.append(`
         <tr style="${hidden}" data-page="${counter_page}" class="${hidden} log_page_${counter_page}">
            <td>${i+1}</td>
            <td>${log.issue_id}</td>
            <td>${log.bugnote_id}</td>
            <td>${log.log_level}</td>
            <td>${log.webhook_event}</td>
            <td>${log.sended}</td>
            <td>${format_date}</td>
            <td>${log.webhook_id}</td>
            <td>${log.webhook_name}</td>
         </tr>`)


        })

        let logs_pagination = $('#logs_pagination')

        if (create_pagination) {
            // https://flaviusmatis.github.io/simplePagination.js/

            // Uncomment for paginate

            // logs_pagination.pagination({
            //     items: counter_page,
            //     itemsOnPage: 10,
            //     cssStyle: 'light-theme'
            // });
        }
    }

    function imaticChangePage() {

        let logs_pagination = $('#logs_pagination')


        let current_page = logs_pagination.pagination('getCurrentPage')

        logs_pagination.on('click', function (e) {
            e.preventDefault()


            //?????????????????????
            let new_page = $(this).pagination('getCurrentPage');
            let prev_page = logs_pagination.pagination('prevPage').children().find('.active').children().text()
            if (prev_page && new_page) {
                logs_pagination.pagination('drawPage', new_page);

                $('.log_page_' + prev_page).hide()
                $('.log_page_' + new_page).show()
            }
        })
    }


//-----------------End append logs--------------


// DATE RANGE
//     let log_filter_daterange = $('input[name="log_filter_daterange"]')


// log_filter_daterange.daterangepicker({
//     "locale": {
//         "format": "D.M.YYYY",
//         "separator": " - ",
//         "applyLabel": "Potvrdit",
//         "cancelLabel": "Zrušit",
//         "fromLabel": "Od",
//         "toLabel": "Do",
//         "customRangeLabel": "Vlastní",
//         "weekLabel": "T",
//         "daysOfWeek": [
//             "Ne",
//             "Po",
//             "Út",
//             "St",
//             "Čt",
//             "Pá",
//             "So"
//         ],
//         "monthNames": [
//             "Leden",
//             "Únor",
//             "Březen",
//             "Duben",
//             "Květen",
//             "Červen",
//             "Červenec",
//             "Srpen",
//             "Září",
//             "Říjen",
//             "Listopad",
//             "Prosinec"
//         ],
//         "firstDay": 1
//     },
//     "ranges": {
//         'Dnes': [moment(), moment()],
//         'Včera': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
//         'Posledních 7 dní': [moment().subtract(6, 'days'), moment()],
//         'Posledních 30 dní': [moment().subtract(29, 'days'), moment()],
//         'Tento měsíc': [moment().startOf('month'), moment().endOf('month')],
//         'Předchozí měsíc': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
//     }
// }, function (start, end, label) {

    // let start_date = start.format('YYYY-MM-DD')
    // let end_date = end.format('YYYY-MM-DD')
    //
    // console.log(start_date)
    // console.log(end_date)

// });


// function filter_logs(type = null, start_date = null, end_date = null,) {
//
//
//     all_logs.each(function () {
//         let _this = $(this)
//
//         _this.hide()
//
//         if (type) {
//             if (type == '[ALL]') {
//                 all_logs.show()
//             }
//
//             if (_this.text().includes(type)) {
//                 _this.show()
//             }
//         }
//
//         if (start_date && end_date) {
//
//             let log_text = _this.text()
//             let extracted_date = log_text.split('[')[0].trim()
//
//             if (new Date(extracted_date) <= new Date(end_date) && new Date(extracted_date) >= new Date(start_date)) {
//                 _this.show()
//             }
//         }
//     })
// }
//
})


