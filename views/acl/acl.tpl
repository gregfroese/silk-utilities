{if $save == 1}
	<h4>Settings saved</h4>
{/if}

{form}
<table class="acl">
	{foreach from=$methods key=visibility item=components}
		{if $visibility == "public"}
			{foreach from=$components key=name item=functions}
				<tr class="acl_header">
					<td><h2>{$name}</h2></td>
					{foreach from=$groups key=group_id item=group}
						<td>
							<b>{$group}</b><br />
							{acl_select acl_aro=$acl_aro controller=$name function="CONTROLLER" group_id=$group_id}
						</td>
					{/foreach}
				</tr>
				{foreach from=$functions key=key item=function}
					<tr>
						<td>{$function}</td>
						{foreach from=$groups key=group_id item=group}
							<td>
								{acl_select acl_aro=$acl_aro controller=$name function=$function group_id=$group_id}
							</td>
						{/foreach}
					</tr>
				{/foreach}
				<tr><td>{submit value="Save"}</td></tr>
			{/foreach}
		{/if}
	{/foreach}
	<tr><td>{submit value="Save"}</td></tr>
</table>
{/form}