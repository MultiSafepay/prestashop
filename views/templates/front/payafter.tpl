<form action="{$action}" id="msp-payafter-form" method="POST" class="additional-information">
    <link href="{$multisafepay_module_dir|escape:'htmlall':'UTF-8'}views/css/multisafepay.css" rel="stylesheet" type="text/css">
 
  <div class="form-group row">
    <div class="col-md-8">
    
        <div class='multisafepay-required'>
            <label class='multisafepay'>{$label_birthday}</label>
            <input class='multisafepay' type="text" required maxlength="10" size="10" id="msp-payafter-birthday" name="birthday" placeholder='dd-mm-yyyy' value="{$birthday}"/>
        </div>
        
        <div class='multisafepay-required'>
            <label class='multisafepay'>{$label_phone}</label>
            <input class='multisafepay' type="text" required maxlength="15" size="15" id="msp-payafter-phone" name="phone" value="{$phone}"/>
        </div>
        
        <div class='multisafepay-required'>
            <label class='multisafepay'>{$label_bankaccount}</label>
            <input class='multisafepay' type="text" required maxlength="34" size="20" id="msp-payafter-bankaccount" name="bankaccount" placeholder='NLXX XXXX 0000 0000 00' value="{$bankaccount}"/>
        </div>
        <br>
        {$terms nofilter}
        
    </div> 
  </div>  
  <input type="hidden" name="gateway" value="payafter"/>
</form>
