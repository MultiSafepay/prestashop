<form action="{$action}" id="msp-afterpay-form" method="POST" class="additional-information">
    <link href="{$multisafepay_module_dir|escape:'htmlall':'UTF-8'}views/css/multisafepay.css" rel="stylesheet" type="text/css">
 
  <div class="form-group row">
    <div class="col-md-8">

        <div class='multisafepay-required'>
            <label class='multisafepay'>{$label_gender}</label>
        </div>
        <fieldset>
                <input class='multisafepay-gender' type="radio" id="msp-afterpay-gender1" name="gender" value="Mr" {if ({$gender} == 1 or {$gender} == null)}checked{/if}>
                <label class='multisafepay-gender' for="mr">{$label_mr}</label>

                <input class='multisafepay-gender' type="radio" id="msp-afterpay-gender2" name="gender" value="Mrs" {if ({$gender} == 2)}checked{/if}>
                <label class='multisafepay-gender' for="mrs">{$label_mrs}</label>

                <input class='multisafepay-gender' type="radio" id="msp-afterpay-gender3" name="gender" value="Miss">
                <label class='multisafepay-gender' for="miss">{$label_miss}</label>
            <br>
        </fieldset>

        <div class='multisafepay-required'>
            <label class='multisafepay'>{$label_birthday}</label>
            <input class='multisafepay' type="text" required maxlength="10" size="10" id="msp-afterpay-birthday" name="birthday" placeholder='dd-mm-yyyy' value="{$birthday}"/>
        </div>
        
        <div class='multisafepay-required'>
            <label class='multisafepay'>{$label_phone}</label>
            <input class='multisafepay' type="text" required maxlength="15" size="15" id="msp-afterpay-phone" name="phone" value="{$phone}"/>
        </div>
        <br>
        {$terms nofilter}
        
    </div> 
  </div>  
  <input type="hidden" name="gateway" value="afterpay"/>
</form>
