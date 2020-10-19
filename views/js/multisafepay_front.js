$(document).ready(function () {
    $("#msp-afterpay-form, #msp-payafter-form, #msp-einvoice-form, #msp-ideal-form").keypress(
        function (event) {
            if (event.which === 13) { // ENTER
                event.preventDefault();
            }
        }
    );

    $("#msp-afterpay-form").submit(function (event) {
        $("#payment-confirmation button").attr("disabled", true);
        var required_fields_afterpay = ["msp-afterpay-birthday", "msp-afterpay-phone"];
        for (i = 0; i < required_fields_afterpay.length; i++) {
            if (document.getElementById(required_fields_afterpay[i]).checkValidity() === false) {
                $("#payment-confirmation button").attr("disabled", false);
                event.preventDefault();
                $("button[type=submit]", $("#msp-afterpay-form")).click();
            }
        }
    });

    $("#msp-einvoice-form").submit(function (event) {
        $("#payment-confirmation button").attr("disabled", true);
        var required_fields_einvoice = ["msp-einvoice-birthday", "msp-einvoice-bankaccount", "msp-einvoice-phone"];
        for (i = 0; i < required_fields_einvoice.length; i++) {
            if (document.getElementById(required_fields_einvoice[i]).checkValidity() === false) {
                $("#payment-confirmation button").attr("disabled", false);
                event.preventDefault();
                $("button[type=submit]", $("#msp-einvoice-form")).click();
            }
        }
    });

    $("#msp-in3-form").submit(function (event) {
        $("#payment-confirmation button").attr("disabled", true);
        var required_fields_in3 = ["msp-in3-birthday", "msp-in3-gender-male", "msp-in3-gender-female", "msp-in3-phone"];
        for (i = 0; i < required_fields_in3.length; i++) {
            if (document.getElementById(required_fields_in3[i]).checkValidity() === false) {
                $("#payment-confirmation button").attr("disabled", false);
                event.preventDefault();
                $("button[type=submit]", $("#msp-in3-form")).click();
            }
        }
    });

    $("#msp-payafter-form").submit(function (event) {
        $("#payment-confirmation button").attr("disabled", true);
        var required_fields_pad = ["msp-payafter-birthday", "msp-payafter-bankaccount", "msp-payafter-phone"];
        for (i = 0; i < required_fields_pad.length; i++) {
            if (document.getElementById(required_fields_pad[i]).checkValidity() === false) {
                $("#payment-confirmation button").attr("disabled", false);
                event.preventDefault();
                $("button[type=submit]", $("#msp-payafter-form")).click();
            }
        }
    });

    $("#msp-ideal-form").submit(function (event) {
        $("#payment-confirmation button").attr("disabled", true);
        if (document.getElementById("msp-ideal-issuer").checkValidity() === false) {
            $("#payment-confirmation button").attr("disabled", false);
            event.preventDefault();
            $("button[type=submit]", $("#msp-ideal-form")).click();
        }
    });

    $('[id^=msp-gateway-]').submit(function (event) {
        $("#payment-confirmation button").attr("disabled", true);
    });

    $(".msp-creditcard-input").hide();
    $('.delete-button').hide();
    $(".msp-creditcard-checkbox").on('click', function () {
        if ($(this).is(":checked")) {
            $(".msp-creditcard-input").show();
        } else {
            $(".msp-creditcard-input").hide();
        }
    });

    $('.msp-saved-creditcards').on('change', function () {
        if (this.value == '') {
            $('.msp-save-new').show();
            $('.delete-button').hide();
        } else {
            $('.delete-button').show();
            $('.msp-save-new').hide();
            $('.msp-creditcard-input').hide().val('');
            $('.msp-creditcard-checkbox').attr('checked', false);
        }
    });

    $(function () {
        $("select").each(function () {
            if ($(this).find("option").val() === "" && $(this).find("option").length <= 1) {
                $(this).hide();
            }
        });
    });

    $("input[name=payment-option]:radio").change(function () {
        $(".msp-saved-creditcards").val("");
        $(".msp-save-new").show();
        $('.delete-button').hide();

        if ($('.msp-creditcard-input').is(':visible')) {
            $('.msp-creditcard-checkbox').prop('checked', true);
        } else {
            $('.msp-creditcard-checkbox').prop('checked', false);
        }
    })


    $(".delete_selected_recurring").click(function () {
        var creditCardType = '#cc_dropdown-' + $(this)[0].id;
        var cc_hash = $(creditCardType).val();
        var cc_name = $(creditCardType + " option[value='" + cc_hash + "']").text();

        var delete_msg  =   confirm_token_deletion;

        var confirmRemoval = confirm(delete_msg + "'" + cc_name + "'" + "?");

        if (confirmRemoval) {
            $.ajax({
                type: "POST",
                url: 'index.php?fc=module&module=multisafepay&controller=tokenization',
                async: false,
                cache: false,
                dataType: 'json',
                data: {
                    'method': 'delete',
                    'hash': cc_hash
                },
                success: function () {
                    $(creditCardType + " option[value='" + cc_hash + "']").remove();
                    $('.delete-button').hide();
                    $('.msp-save-new').show();

                    $("select").each(function () {
                        if ($(this).find("option").val() === "" && $(this).find("option").length <= 1) {
                            $(this).hide();
                            $('.delete-button').hide();
                            $('.msp-save-new').show();
                        }
                    });
                }
            });
        }
    });


});
