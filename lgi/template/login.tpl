{extends "home_base.tpl"}
{block "menu"}{/block}
{block "content"}
{if $simplesamlphp_exists && $authsource!='local'}
			<p>Login using
				<a href="{$approot}/login/feide-openidp">Feide OpenIdp</a>, {*
				<-- alternate way using the idpdiscovery service -->
				<a href="{$approot}/login/idpdisco?idp=https://openidp.feide.no">Feide OpenIdP</a>, *}
				<a href="{$approot}/login/twitter">Twitter</a>,
				<a href="{$approot}/login/google">Google</a>,
				<a href="{$approot}/login/idpdisco?idp=https://idp.nikhef.nl">Nikhef</a>,
				<a href="{$approot}/login/idpdisco">another institution</a>.
			</p>
			<div>Or with a local account:</div>
{/if}
			<form action="{$approot}/login" method="post" class="cmxform">
				<fieldset><ol>
					<li><label for="name">Name:</label> <input type="text" name="name" id="name" value="{$name}" /></li>
					<li><label for="password">Password:</label> <input type="password" name="password" id="password" /></li>
					<li><input type="submit" value="Login" /></li>
				</ol></fieldset>
			</form>
{/block}
