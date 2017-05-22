<form id="multisafepay_giftcards_form" class="defaultForm form-horizontal" action="" method="post">
  <div class="panel">
    {foreach from=$giftcards key=sort item=giftcard}
      <div class="form-group">
        <div class="col-lg-2 logo-wrapper">
          <img src="{$path|escape:'htmlall':'UTF-8'}views/images/giftcards/{$locale}/{$giftcard.code|escape:'htmlall':'UTF-8'}.png" alt="{$giftcard.code|escape:'htmlall':'UTF-8'}" class="paymentlogo">
        </div>
        <div class="col-lg-3">
          <div class="col-lg-4 control-label switch-label">{$giftcard.name}</div>
          <div class="col-lg-6 switch prestashop-switch fixed-width-lg">
            <input type="radio" class="{$giftcard.code}" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}" id="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_on" value="1" {if ($giftcard.active == 1)}checked="checked"{/if}>
            <label for="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_on">{$enable|escape:'htmlall':'UTF-8'}</label>
            <input type="radio" class="{$giftcard.code}" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}" id="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_off" value="0" {if empty($giftcard.active)}checked="checked"{/if}>
            <label for="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_off">{$disable|escape:'htmlall':'UTF-8'}</label>
            <a class="slide-button btn"></a>
          </div>
        </div>
        <div style="clear: both"></div>
        <div class="col-lg-12 {if $giftcard.active == 1} show_method {else} hide_method {/if} {$giftcard.code}_settings settings_panel">
          <div class="panel">
            <div class="panel-heading">
              <i class="icon-cog"></i> {$giftcard.name} {$configuration}
            </div>
            <!--Title Configuration-->
            <div  class="spacer">
              <div class="col-lg-9">
                <label for="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_TITLE">{$title}</label>
              </div>
              <div class="col-lg-3">
                <input type="text" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_TITLE" id="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_TITLE" value="{$giftcard.title}" >
              </div>
              <div style="clear: both"></div>
            </div>

            <!-- Sort Order configuration-->
            <div  class="spacer">
              <div class="col-lg-9">
                <label for="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_SORT">{$sort_order}</label>
              </div>
              <div class="col-lg-3">
                <input type="text" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_SORT" id="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_SORT" value="{$giftcard.sort}" >
              </div>
              <div style="clear: both"></div>
            </div>

            <!-- Min amount-->
            <div  class="spacer">
              <div class="col-lg-9">
                <label for="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_MIN_AMOUNT">{$min_order_amount}</label>
              </div>
              <div class="col-lg-3">
                <input type="text" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_MIN_AMOUNT" id="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_MIN_AMOUNT" value="{$giftcard.min_amount}" >
              </div>
              <div style="clear: both"></div>
            </div>

            <!-- Max amount-->
            <div  class="spacer">
              <div class="col-lg-9">
                <label for="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_MAX_AMOUNT">{$max_order_amount}</label>
              </div>
              <div class="col-lg-3">
                <input type="text" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_MAX_AMOUNT" id="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_MAX_AMOUNT" value="{$giftcard.max_amount}" >
              </div>
              <div style="clear: both"></div>
            </div>
            <!-- Description -->
            <div  class="spacer">
              <div class="col-lg-9">
                <label for="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_DESC">{$description}</label>
              </div>
              <div class="col-lg-3">
                <textarea class="form-control" rows="3" name="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_DESC" id="MULTISAFEPAY_GIFTCARD_{$giftcard.code|escape:'htmlall':'UTF-8'}_DESC"  >{$giftcard.desc}</textarea>
              </div>
              <div style="clear: both"></div>
            </div>

          </div>
        </div>
      </div>
      <div style="clear: both"></div>
    {/foreach}
    <div class="panel-footer">
      <button type="submit" value="1" name="btnGiftcardsSubmit" class="btn btn-default pull-right">
        <i class="process-icon-save"></i> {$save|escape:'htmlall':'UTF-8'}
      </button>
    </div>

  </div>
  <input type="hidden" name="multisafepay_tab" value="giftcard_configuration"/>
</form>
