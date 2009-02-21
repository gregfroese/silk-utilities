<ul>
<li><a href="{$homeLink}">{lang name="_LANGUAGE_HOME" text="Home" section="Language"}</li>
<li><a href="{$updateLink}">{lang name="_UPDATE_LANGUAGE" section="links" text="Update all language files"}</a></li>

{foreach from=$translateLanguageLinks item=link key=key}
	<li><a href="{$link}">Translate {$key}</a></li>
{/foreach}
</ul>