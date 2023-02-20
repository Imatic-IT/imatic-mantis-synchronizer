$(() => {

// DATE RANGE
    let log_filter_daterange = $('input[name="log_filter_daterange"]')


    log_filter_daterange.daterangepicker({
        "locale": {
            "timePicker": true,
            "timeZone": "Europe/Prague",
            "format": "D.M.YYYY",
            "separator": " - ",
            "applyLabel": "Potvrdit",
            "cancelLabel": "Zrušit",
            "fromLabel": "Od",
            "toLabel": "Do",
            "customRangeLabel": "Vlastní",
            "weekLabel": "T",
            "daysOfWeek": [
                "Ne",
                "Po",
                "Út",
                "St",
                "Čt",
                "Pá",
                "So"
            ],
            "monthNames": [
                "Leden",
                "Únor",
                "Březen",
                "Duben",
                "Květen",
                "Červen",
                "Červenec",
                "Srpen",
                "Září",
                "Říjen",
                "Listopad",
                "Prosinec"
            ],
            "firstDay": 1
        },
        "ranges": {
            'Dnes': [moment(), moment()],
            'Včera': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Posledních 7 dní': [moment().subtract(6, 'days'), moment()],
            'Posledních 30 dní': [moment().subtract(29, 'days'), moment()],
            'Tento měsíc': [moment().startOf('month'), moment().endOf('month')],
            'Předchozí měsíc': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, function (start, end, label) {

        let start_date = start.format('YYYY-MM-DD')
        let end_date = end.format('YYYY-MM-DD')

    });


})


