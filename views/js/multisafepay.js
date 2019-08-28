$(document).ready(function () {

    $('.tabs nav .tab-title').click(function () {
        var elem = $(this);
        var target = $(elem.data('target'));
        elem.addClass('active').siblings().removeClass('active');
        target.show().siblings().hide();
    })

    if ($('.tabs nav .tab-title.active').length == 0) {
        $('.tabs nav .tab-title:first').trigger("click");
    }


    $('#multisafepay_gateways_form input[type=radio]').change(function () {

        var gateway = $(this).attr("class");
        var value = $(this).attr("value");
        var methodclass = '.' + gateway + '_settings';

        if (value == 1) {
            $(methodclass).show(500);
        } else {
            $(methodclass).hide(500);
        }
    })

    $('#multisafepay_giftcards_form input[type=radio]').change(function () {

        var gateway = $(this).attr("class");
        var value = $(this).attr("value");
        var methodclass = '.' + gateway + '_settings';

        if (value == 1) {
            $(methodclass).show(500);
        } else {
            $(methodclass).hide(500);
        }
    })

});





