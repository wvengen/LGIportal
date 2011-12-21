{extends "home_base.tpl"}
{block "title"}Your profile{/block}
{block "content"}
{load_templates "functions.tpl"}
		<form action='{$approot}/user' method='POST'>
			<input type='hidden' name='nonce' value='{$nonce}' />

			<fieldset class="collapsible uncollapsed"><legend>Password</legend>
				<ol class="cmxform">
					<li><label for='pwd_old'>Old password:</label> <input type='password' name='pwd_old' id='pwd_old' /></li>
					<li><label for='pwd1'>New password:</label> <input type='password' name='pwd1' id='pwd1' /></li>
					<li><label for='pwd2'>New password:</label> <input type='password' name='pwd2' id='pwd2' /> (repeat)</li>
				</ol>
				<div style='text-align:right'><input type='submit' name='submit' id='submit' value='Change password' /></div>
			</fieldset>
{*

			<fieldset class="collapsible uncollapsed"><legend>Defaults</legend>
				<ol>
					<li><label for='server'>Project server</label> {inputselect 'server' $servers $servers[0]}</li>
					<li><label for='server'>Project name</label> {inputselect 'project' $projects $projects[0]}</li>
					<li title='Jobs that you submit will be owned by at least the names that you specify here.'><label for='groups'>Groups</label> <input type='text' name='groups' id='groups' value='{$groups}' /></li>
				<ol>
			</fieldset>
*}

			<fieldset class="collapsible uncollapsed"><legend>LGI credentials</legend>
				<div>It is possible to do computations from your computer directly,
				without having to login to this portal. Download your credentials using
				the button below, and save it into your {*
				*}{if $ua_windows}personal profile directory ("Documents and Settings"){else}home directory{/if}.
				Software like <a href="http://biggrid.nl/wiki/index.php/LGI/Rlgi">Rlgi</a> uses
				this to connect to and authenticate with the LGI project server.</div>
				<div><em>Important:</em> this file contains your private key. Do not
				distribute this file and make sure it is not readable by others.
				</div>
				<div style='text-align:right'><input type='submit' name='dlcred' id='dlcred' value='Download credentials' /></div>
			</fieldset>

		</form>
{/block}
