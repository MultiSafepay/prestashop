<form action="{$action}" id="msp-in3-form" method="POST" class="additional-information">
    <link href="{$multisafepay_module_dir|escape:'htmlall':'UTF-8'}views/css/multisafepay.css" rel="stylesheet" type="text/css">
 
  <div class="form-group row">
    <div class="col-md-8">
    
        <div class='multisafepay-required'>
            <label class='multisafepay'>{$label_birthday}</label>
            <input class='multisafepay' type="text" required maxlength="10" size="10" id="msp-in3-birthday" name="birthday" placeholder='dd-mm-yyyy' value="{$birthday}"/>
        </div>
        
        <div class='multisafepay-required'>
            <label class='multisafepay'>{$label_phone}</label>
            <input class='multisafepay' type="text" required maxlength="15" size="15" id="msp-in3-phone" name="phone" value="{$phone}"/>
        </div>
        
        <div>
            <label class='multisafepay' id="msp-label-gender">{$label_gender}</label>
            <span id="msp-input-gender">
                <input type="radio" id="msp-in3-gender-male" name="gender" value="Mr"  {if $gender == 1 } checked="checked" {/if}>    {l s='male'   mod='multisafepay'}
                <input type="radio" id="msp-in3-gender-female" name="gender" value="Mrs" {if $gender == 2 } checked="checked" {/if}>    {l s='female' mod='multisafepay'}
            </span>

        </div>
        <br>
    </div>
  </div>  
  <input type="hidden" name="gateway" value="in3"/>
</form>
