<form action="{$action}" class="msp-token-form additional-information form" method="POST">
    <link href="{$multisafepay_module_dir|escape:'htmlall':'UTF-8'}views/css/multisafepay.css" rel="stylesheet" type="text/css">
    <div class="row">
        {if !$saved_tokens && in_array($gateway, $saved_gateways)}
            <div class="col-md-8">
                <div class="input-group msp-select">
                    <select class="form-control form-control-select msp-saved-creditcards" name="saved_cc" id="cc_dropdown-{$gateway}">
                        <option value="">{$label_dropdown}</option>
                        {foreach $tokens as $token}
                            {if $gateway == strtolower($token['cc_type'])}
                                <option value="{$token['recurring_id']}">{$token['cc_name']}</option>
                            {/if}
                        {/foreach}
                    </select>
                    <div class="input-group-btn delete-button">
                        <button type="button" class="btn-link delete_selected_recurring" id="{$gateway}">
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                </div>
            </div>
        {/if}
        <div class="col-md-8">
            <div class="msp-save-new">
                <label class="left">
                    <input type="checkbox" name="creditcard" class="msp-creditcard-checkbox"> {$label_creditcard}
                </label>
            </div>
        </div>
        <div class="col-md-8">
            <label class="msp-creditcard-input">
                <p class="msp-description"> {$label_description} </p>
            </label>
            <input type="text" name="creditcard-input" class="msp-creditcard-input form-control" value="" max-length="30" placeholder="{$gateway|upper} ***1234">
        </div>
    </div>
    <input type="hidden" name="gateway" value="{$gateway}"/>
</form>