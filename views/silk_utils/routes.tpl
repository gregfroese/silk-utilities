<h2>Routes</h2>
{$auto_menu}
 	
{foreach from=$routes key=key item=route}
	<div>
		{foreach from=$route key=routeKey item=one_route}
			{if $routeKey == "route_string"}
				<b>Route String: {$one_route}</b><br />
			{elseif $routeKey == "callback"}
				---- Callback: {$one_route}<br />
			{else}
				{foreach from=$one_route key=oneKey item=default_param}
					---- {$oneKey}: {$default_param}<br />
				{/foreach}
			{/if}
		{/foreach}
	</div><br />
{/foreach}