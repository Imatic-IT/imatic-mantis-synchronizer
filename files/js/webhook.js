$(document).ready(function () {
    //select 2
    let project_select2 = $(".project_select_two");
    project_select2.select2();

    //webhooks
    let create_webhook_btn = $("#create-new-webhook");
    let create_webhook_form = $("#create-new-webhook-form");
    let webhooks = $(".webhooks");
    let submit_form_button = $("#submit-form");

    let name_input = $("#webhook_name");
    let url_input = $("#webhook_url");
    let status_input = $("#webhook_status");
    let webhook_input_method = $("#webhook_method");
    let webhook_id_input = $("#webhook_id");
    let delete_webhook_btn = $('#delete-webhook')

    //Create webhook form // show form // clear inputs
    create_webhook_btn.on("click", function (e) {
        e.preventDefault();

        delete_webhook_btn.hide();

        submit_form_button.attr("value", "Create");
        webhook_input_method.attr("value", "create");

        name_input.val("");
        url_input.val("");
        status_input.prop("checked", false);
        webhook_id_input.val('')


        project_select2.val("").trigger("change");

        $('input[name="events[]"]').each(function () {
            $(this).prop('checked', false);

        });

        create_webhook_form.find("input").each(function (e, i) {
        });

        create_webhook_form.show();
    });

    // Edit webhook
    webhooks.each(function (e, i) {
        let _this = $(this);

        _this.on("click ", function (e) {
            e.preventDefault();


            // Un-check all
            $('input[name="events[]"]').each(function () {
                $(this).prop('checked', false);
            });

            let data = $(this).find("a").data();
            let actionUrl = $(this).find("a").attr("href");

            // Active link
            _this
                .addClass("webhook_edit_active")
                .siblings()
                .removeClass("webhook_edit_active");

            //method
            webhook_input_method.attr("value", "update");

            // Change button name
            submit_form_button.attr("value", "Edit");

            create_webhook_form.show();

            // Show delete webhook button // set webhook id to attr href
            delete_webhook_btn.show();

            // Get Webhook data
            $.ajax({
                type: "POST",
                data: data,
                url: actionUrl,
                success: function (response) {
                    response = $.parseJSON(response);
                    let status;

                    if (response) {

                        var events = JSON.parse(response.events);

                        if (events) {

                            $('input[name="events[]"]').each(function () {
                                // $(this).prop('checked', false);
                                var value = $(this).val();
                                // console.log(value)
                                if (events.includes(value)) {
                                    $(this).prop('checked', true);
                                }
                            });
                        }

                        name_input.val(response.name);
                        url_input.val(response.url);
                        webhook_id_input.val(data.webhook_id)
                        project_select2.val(response.projects).trigger("change");

                        if (status = response.status === "on") {
                            status_input.prop("checked", true);
                        } else {
                            status_input.prop("checked", false);

                        }
                    }
                },
            });
        });
    });
});
