{if isset($gateway_restrictions_saved)}
    <div class="alert alert-success">{$gateway_restrictions_saved}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
{/if}

<form id="module_form" class="defaultForm form-horizontal" action="" method="post">
    <!--Start gateways-->
    <span class="restriction_title">{$gateways_restriction}</span>
    <div class="panel">
        <div class="form-group">
            <div class="col-lg-12">
                <i class="icon-money"><span>{$currency_restriction}</span></i>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="left">{$currency}</th>
                            {foreach from=$gateways key=sort item=gateway}	
                                {if $gateway.active == 1}
                                    <th>{$gateway.name}</th>
                                {/if}
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$currencies key=sort item=currency}		      
                            <tr>
                                <td class="left">{$currency.name}</td>
                                {foreach from=$gateways key=sort item=gateway}	
                                    {if $gateway.active == 1}
                                        {if isset ($gateway.currency[$currency.id_currency])}
                                            <td><input type="checkbox" name="MULTISAFEPAY_GATEWAY_{$gateway.code}_CURRENCY_{$currency.id_currency}"   checked/></td>
                                        {else}
                                            <td><input type="checkbox" name="MULTISAFEPAY_GATEWAY_{$gateway.code}_CURRENCY_{$currency.id_currency}" /></td>
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
            <button type="submit" value="1" name="btnSubmitGatewayConfig" class="btn btn-default pull-right">
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
                            {foreach from=$gateways key=sort item=gateway}	
                                {if $gateway.active == 1}
                                    <th>{$gateway.name}</th>
                                {/if}
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$groups key=sort item=group}			      
                            <tr>
                                <td class="left">{$group.name}</td>
                                {foreach from=$gateways key=sort item=gateway}	
                                    {if $gateway.active == 1}
                                        {if isset ($gateway.group[$group.id_group])}
                                            <td><input type="checkbox" name="MULTISAFEPAY_GATEWAY_{$gateway.code}_GROUP_{$group.id_group}"  checked/></td>
                                        {else}
                                            <td><input type="checkbox" name="MULTISAFEPAY_GATEWAY_{$gateway.code}_GROUP_{$group.id_group}" /></td>
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
            <button type="submit" value="1" name="btnSubmitGatewayConfig" class="btn btn-default pull-right">
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
                            {foreach from=$gateways key=sort item=gateway}	
                                {if $gateway.active == 1}
                                    <th>{$gateway.name}</th>
                                {/if}
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$carriers key=sort item=carrier}			      
                            <tr>
                                <td class="left">{$carrier.name}</td>
                                {foreach from=$gateways key=sort item=gateway}	
                                    {if $gateway.active == 1}
                                        {if isset ($gateway.carrier[$carrier.id_carrier])}
                                            <td><input type="checkbox" name="MULTISAFEPAY_GATEWAY_{$gateway.code}_CARRIER_{$carrier.id_carrier}" checked/></td>
                                        {else}
                                            <td><input type="checkbox" name="MULTISAFEPAY_GATEWAY_{$gateway.code}_CARRIER_{$carrier.id_carrier}" /></td>
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
            <button type="submit" value="1" name="btnSubmitGatewayConfig" class="btn btn-default pull-right">
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
                                {foreach from=$gateways key=sort item=gateway}	
                                    {if $gateway.active == 1}
                                        <th>{$gateway.name}</th>
                                    {/if}
                                {/foreach}
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$countries key=sort item=country}			      
                                <tr>
                                    <td class="left">{$country.name}</td>
                                    {foreach from=$gateways key=sort item=gateway}	
                                        {if $gateway.active == 1}
                                            {if isset ($gateway.country[$country.id_country])}
                                                <td><input type="checkbox" name="MULTISAFEPAY_GATEWAY_{$gateway.code}_COUNTRY_{$country.id_country}" checked/></td>
                                            {else}
                                                <td><input type="checkbox" name="MULTISAFEPAY_GATEWAY_{$gateway.code}_COUNTRY_{$country.id_country}" /></td>
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
            <button type="submit" value="1" name="btnSubmitGatewayConfig" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {$save|escape:'htmlall':'UTF-8'}
            </button>
        </div>
    </div>
    <input type="hidden" name="multisafepay_tab" value="gateway_restrictions_configuration"/>
</form>