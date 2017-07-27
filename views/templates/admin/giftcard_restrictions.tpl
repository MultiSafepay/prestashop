{if isset($giftcard_restrictions_saved)}
<div class="alert alert-success">{$giftcard_restrictions_saved}
<button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
{/if}
<form id="module_form" class="defaultForm form-horizontal" action="" method="post">
  <!--Start Giftcards-->
  <span class="restriction_title">{$giftcards_restriction}</span>
  <div class="panel">
    <div class="form-group">
      <div class="col-lg-12">
        <i class="icon-money"><span>{$currency_restriction}</span></i>
        <table class="table table-hover">
          <thead>
            <tr>
              <th class="left">{$currency}</th>
                {foreach from=$giftcards key=sort item=giftcard}	
                  {if $giftcard.active == 1}
                  <th>{$giftcard.name}</th>
                  {/if}
                {/foreach}
            </tr>
          </thead>
          <tbody>
            {foreach from=$currencies key=sort item=currency}		      
              <tr>
                <td class="left">{$currency.name}</td>
                {foreach from=$giftcards key=sort item=giftcard}	
                  {if $giftcard.active == 1}
                    {if $giftcard.currency[$currency.id_currency] == 'on'}
                      <td><input type="checkbox" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code}_CURRENCY_{$currency.id_currency}"  checked/></td>
                      {else}
                      <td><input type="checkbox" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code}_CURRENCY_{$currency.id_currency}" /></td>
                      {/if}
                    {/if}
                  {/foreach}
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
      <div style="clear: both"></div>
    </div>
    <div class="panel-footer">
      <button type="submit" value="1" name="btnSubmitGiftcardConfig" class="btn btn-default pull-right">
        <i class="process-icon-save"></i> {$save|escape:'htmlall':'UTF-8'}
      </button>
    </div>
  </div>

  <div class="panel">
    <div class="form-group">
      <div class="col-lg-12">
        <i class="icon-group"><span>{$group_restriction}</span></i>
        <table class="table table-hover">
          <thead>
            <tr>
              <th class="left">{$group}</th>
                {foreach from=$giftcards key=sort item=giftcard}	
                  {if $giftcard.active == 1}
                  <th>{$giftcard.name}</th>
                  {/if}
                {/foreach}
            </tr>
          </thead>
          <tbody>
            {foreach from=$groups key=sort item=group}	     
              <tr>
                <td class="left">{$group.name}</td>
                {foreach from=$giftcards key=sort item=giftcard}	
                  {if $giftcard.active == 1}
                    {if $giftcard.group[$group.id_group] == 'on'}
                      <td><input type="checkbox" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code}_GROUP_{$group.id_group}"  checked/></td>
                      {else}
                      <td><input type="checkbox" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code}_GROUP_{$group.id_group}" /></td>
                      {/if}
                    {/if}
                  {/foreach}
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
      <div style="clear: both"></div>
    </div>
    <div class="panel-footer">
      <button type="submit" value="1" name="btnSubmitGiftcardConfig" class="btn btn-default pull-right">
        <i class="process-icon-save"></i> {$save|escape:'htmlall':'UTF-8'}
      </button>
    </div>
  </div>


  <div class="panel">
    <div class="form-group">
      <div class="col-lg-12">
        <i class="icon-truck"><span>{$carrier_restriction}</span></i>
        <table class="table table-hover">
          <thead>
            <tr>
              <th class="left">{$carrier}</th>
                {foreach from=$giftcards key=sort item=giftcard}	
                  {if $giftcard.active == 1}
                  <th>{$giftcard.name}</th>
                  {/if}
                {/foreach}
            </tr>
          </thead>
          <tbody>
            {foreach from=$carriers key=sort item=carrier}			      
              <tr>
                <td class="left">{$carrier.name}</td>
                {foreach from=$giftcards key=sort item=giftcard}	
                  {if $giftcard.active == 1}
                    {if $giftcard.carrier[$carrier.id_carrier] == 'on'}
                      <td><input type="checkbox" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code}_CARRIER_{$carrier.id_carrier}"  checked/></td>
                      {else}
                      <td><input type="checkbox" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code}_CARRIER_{$carrier.id_carrier}" /></td>
                      {/if}
                    {/if}
                  {/foreach}
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
      <div style="clear: both"></div>
    </div>
    <div class="panel-footer">
      <button type="submit" value="1" name="btnSubmitGiftcardConfig" class="btn btn-default pull-right">
        <i class="process-icon-save"></i> {$save|escape:'htmlall':'UTF-8'}
      </button>
    </div>
  </div>

  <div class="panel">
    <div class="form-group">
      <div class="col-lg-12">
        <i class="icon-globe"><span>{$country_restriction}</span></i>
        <div class="bodycontainer scrollable">
          <table class="table table-hover table-scrollable">
            <thead>
              <tr>
                <th class="left">{$country}</th>
                  {foreach from=$giftcards key=sort item=giftcard}	
                    {if $giftcard.active == 1}
                    <th>{$giftcard.name}</th>
                    {/if}
                  {/foreach}
              </tr>
            </thead>
            <tbody>
              {foreach from=$countries key=sort item=country}			      
                <tr>
                  <td class="left">{$country.name}</td>
                  {foreach from=$giftcards key=sort item=giftcard}	
                    {if $giftcard.active == 1}
                      {if $giftcard.country[$country.id_country] == 'on'}
                        <td><input type="checkbox" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code}_COUNTRY_{$country.id_country}" checked/></td>
                        {else}
                        <td><input type="checkbox" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code}_COUNTRY_{$country.id_country}" /></td>
                        {/if}	
                      {/if}
                    {/foreach}
                </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
      </div>
      <div style="clear: both"></div>
    </div>
    <div class="panel-footer">
      <button type="submit" value="1" name="btnSubmitGiftcardConfig" class="btn btn-default pull-right">
        <i class="process-icon-save"></i> {$save|escape:'htmlall':'UTF-8'}
      </button>
    </div>
  </div>
  <input type="hidden" name="multisafepay_tab" value="giftcard_restrictions_configuration"/>
</form>
