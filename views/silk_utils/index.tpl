<h1>{lang name="silk utils" section="silk utils" text="Silk Utils"}</h1>
{$auto_menu}
<div class="info">
	Here is some info
</div>
<ol>
{foreach from=$menuItems key=key item=options}
	{foreach from=$options key=optionKey item=value}
		{if $optionKey == "function"}
			<li>{link controller="silk_utils" action=$value text=$key}</li>
		{/if}
		{if $optionKey == "controller"}
			<li>{link controller=$value text=$key}</li>
		{/if}
	{/foreach}
{/foreach}
</ol>