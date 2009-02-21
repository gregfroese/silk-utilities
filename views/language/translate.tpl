<h2>{lang name="_TRANSLATE" text="Translate" section="Headers"}</h2>
{render_partial template="language_menu.tpl"}
{foreach from=$langText key=sectionName item=section}
	<h3>{lang name="_SECTION" text="Section" section="Headers"}: {$sectionName}</h3>
	<table>
	{foreach from=$section key=key item=text}
		<div>
			<div id="{$sectionName}{$key}">
				<tr>
				<td>{$key}</td>
				<td>
				{form remote=true}
					{hidden name="section" value=$sectionName}
					{hidden name="key" value=$key}
					{hidden name="formAction" value="update"}
					{hidden name="langIndicator" value=$langIndicator}
					{textarea name="text" value=$text cols=50 rows=3}
					</td>
					<td>{submit value="Update"}</td>
				{/form}
			</div>
			<td>
			<div id="{$sectionName}{$key}result">
				<font color="Green">Unchanged</font>
			</div>
			</td>
			</tr>
		</div>
	{/foreach}
	</table>
{/foreach}
{lang name="_THANKS" text="Thank you for using this" section="footers"}