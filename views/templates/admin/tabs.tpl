<script type='text/javascript' src="{$multisafepay_js|escape:'htmlall':'UTF-8'}"></script>
<link href="{$multisafepay_css|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">

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
