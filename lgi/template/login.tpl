{extends "home_base.tpl"}
{block "menu"}{/block}
{block "content"}
			<form action="{$approot}/login" method="post" class="cmxform">
				<fieldset><ol>
					<li><label for="name">Name:</label> <input type="text" name="name" id="name" value="{$name}" /></li>
					<li><label for="password">Password:</label> <input type="password" name="password" id="password" /></li>
					<li><input type="submit" value="Login" /></li>
				</ol></fieldset>
			</form>
{/block}