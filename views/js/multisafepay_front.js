$(document).ready(function () {
    
    $("#msp-einvoice-form").submit(function(event){
        var required_fields_einvoice = ["msp-einvoice-birthday", "msp-einvoice-bankaccount", "msp-einvoice-phone"];
        for (i = 0; i < required_fields_einvoice.length; i++) {
            if(document.getElementById(required_fields_einvoice[i]).checkValidity() === false) {
                event.preventDefault();
                $("button[type=submit]", $("#msp-einvoice-form")).click();
            }
        }
    });
    
    $("#msp-payafter-form").submit(function(event){
        var required_fields_pad = ["msp-payafter-birthday", "msp-payafter-bankaccount", "msp-payafter-phone"];
        for (i = 0; i < required_fields_pad.length; i++) {
            if(document.getElementById(required_fields_pad[i]).checkValidity() === false) {
                event.preventDefault();
                $("button[type=submit]", $("#msp-payafter-form")).click();
            }
        }
    });   

    $("#msp-ideal-form").submit(function(event){
        if(document.getElementById("msp-ideal-issuer").checkValidity() === false) {
            event.preventDefault();
            $("button[type=submit]", $("#msp-ideal-form")).click();
        }

    });
});

