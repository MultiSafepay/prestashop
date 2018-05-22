$(document).ready(function () {
    
    $("#msp-einvoice-form").submit(function(event){
        $("#payment-confirmation button").attr("disabled", true);
        var required_fields_einvoice = ["msp-einvoice-birthday", "msp-einvoice-bankaccount", "msp-einvoice-phone"];
        for (i = 0; i < required_fields_einvoice.length; i++) {
            if(document.getElementById(required_fields_einvoice[i]).checkValidity() === false) {
                $("#payment-confirmation button").attr("disabled", false);
                event.preventDefault();
                $("button[type=submit]", $("#msp-einvoice-form")).click();
            }
        }
    });
    
    $("#msp-payafter-form").submit(function(event){
        $("#payment-confirmation button").attr("disabled", true);
        var required_fields_pad = ["msp-payafter-birthday", "msp-payafter-bankaccount", "msp-payafter-phone"];
        for (i = 0; i < required_fields_pad.length; i++) {
            if(document.getElementById(required_fields_pad[i]).checkValidity() === false) {
                $("#payment-confirmation button").attr("disabled", false);
                event.preventDefault();
                $("button[type=submit]", $("#msp-payafter-form")).click();
            }
        }
    });   

    $("#msp-ideal-form").submit(function(event){
        $("#payment-confirmation button").attr("disabled", true);
        if(document.getElementById("msp-ideal-issuer").checkValidity() === false) {
            $("#payment-confirmation button").attr("disabled", false);
            event.preventDefault();
            $("button[type=submit]", $("#msp-ideal-form")).click();
        }
    });
    
    $('[id^=msp-gateway-]').submit(function(event){
        $("#payment-confirmation button").attr("disabled", true);
    });     
});

