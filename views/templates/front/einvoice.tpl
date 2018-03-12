<form action="{$action}" id="payment-form" method="POST" class="additional-information">
<script type='text/javascript' src="{$multisafepay_js|escape:'htmlall':'UTF-8'}"></script>
<link href="{$multisafepay_css|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">

  <div class="form-group row">
    <div class="col-md-6">
    
        <label class='multisafepay'>{$label_birthday}</label>
        <input class='multisafepay' type="text" required maxlength="10" size="10" id="birthday" name="birthday" placeholder='dd-mm-yyyy' value="{$birthday}"/>
        <br/>

        <label class='multisafepay'>{$label_phone}</label>
        <input class='multisafepay' type="text" required maxlength="15" size="15" id="phone" name="phone" value="{$phone}"/>
        <br/>

        <label class='multisafepay'>{$label_bankaccount}</label>
        <input class='multisafepay' type="text" required maxlength="34" size="20" id="bankaccount" name="bankaccount" placeholder='NLXX XXXX 0000 0000 00' value="{$bankaccount}"/>
        <br/>

        {$terms nofilter}
        
    </div> 
  </div>  
  <input type="hidden" name="gateway" value="einvoice"/>
</form>