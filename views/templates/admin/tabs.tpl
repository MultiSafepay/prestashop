<script type='text/javascript' src="{$multisafepay_js|escape:'htmlall':'UTF-8'}"></script>
<link href="{$multisafepay_css|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">

{if isset($errors) && $errors}
    <div class="alert alert-danger">
        <p>{if $errors|@count > 1}{l s='There are %d errors' sprintf=$errors|@count mod='multisafepay'}{else}{l s='There is %d error' sprintf=$errors|@count mod='multisafepay'}{/if}</p>
        <ol>
            {foreach from=$errors key=k item=error}
                <li>{$error}</li>
            {/foreach}
        </ol>
    </div>
{/if}

{if isset($warnings) && $warnings}
    <div class="alert alert-warning">
        <p>{if $warnings|@count > 1}{l s='There are %d warnings' sprintf=$warnings|@count mod='multisafepay'}{else}{l s='There is %d error' sprintf=$warnings|@count mod='multisafepay'}{/if}</p>
        <ol>
            {foreach from=$warnings key=k item=warning}
                <li>{$warning}</li>
            {/foreach}
        </ol>
    </div>
{/if}

<div class="tabs">
  {if $tabs}
    <nav>
      {foreach $tabs as $tab}
        <a class="tab-title {if isset($active_tab) && $tab.id==$active_tab}active{/if}" href="#" id="{$tab.id|escape:'htmlall':'UTF-8'}" data-target="#tabs-{$tab.id|escape:'htmlall':'UTF-8'}">{$tab.title|escape:'htmlall':'UTF-8'}</a>
      {/foreach}
    </nav>
    <div class="content">
      {foreach $tabs as $tab}
        <div class="tab-content" id="tabs-{$tab.id|escape:'htmlall':'UTF-8'}" style="display:{if isset($active_tab) && $tab.id==$active_tab}block{else}none{/if}">
          {html_entity_decode($tab.content|escape:'htmlall':'UTF-8')}
        </div>
      {/foreach}
    </div>
  {/if}
</div>
