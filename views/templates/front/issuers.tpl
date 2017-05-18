<form action="{$action}" id="payment-form" method="POST" class="additional-information">
  <div class="form-group row">
    <div class="col-md-6">
      <select class="form-control form-control-select" name="issuer" id="issuers" required="required">
        <option value="">{$select_bank}</option>
        {foreach from=$issuers key=code item=issuer}
          <option value="{$issuer->code}">{$issuer->description}</option>
        {/foreach}
      </select>
    </div> 
  </div>  
  <input type="hidden" name="gateway" value="ideal"/>
</form>