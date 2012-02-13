{extends "home_base.tpl"}
{block "title"}Your profile{/block}
{block "content"}
{load_templates "functions.tpl"}
		<form action='{$approot}/user' method='POST'>
			<input type='hidden' name='nonce' value='{$nonce}' />

{if !isset($authsource) || $authsource=='local'}
			<fieldset class="collapsible uncollapsed"><legend>Password</legend>
				<ol class="cmxform">
					<li><label for='pwd_old'>Old password:</label> <input type='password' name='pwd_old' id='pwd_old' /></li>
					<li><label for='pwd1'>New password:</label> <input type='password' name='pwd1' id='pwd1' /></li>
					<li><label for='pwd2'>New password:</label> <input type='password' name='pwd2' id='pwd2' /> (repeat)</li>
				</ol>
				<div style='text-align:right'><input type='submit' name='submit_pwd' id='submit_pwd' value='Change password' /></div>
			</fieldset>
{/if}

			<fieldset class="collapsible uncollapsed"><legend>Defaults</legend>
				<ol class="cmxform">
					<li><label for='project'>Project</label> {inputselect 'project' $lgi.projects $lgi.project}</li>
				{if $lgi.fixedgroups}{*	
				*}	<li title='Jobs that you submit are accessible to members of this group.'><label for='group'>Default group</label> {inputselect 'group' $lgi.groups $lgi.group}</li>{*
				*}{else}{*
				*}</ol>
				<div>You are free to use any group you like. In practice you'll work with a small number of groups.
				Please enter the groups you want to use here, seperated by commas. Then select a default below.</div>
				<ol class="cmxform">
					<li><label for='groups'>Groups</label> <input type='text' name='groups' id='groups' value='{foreach $lgi.groups g implode=", "}{$g}{/foreach}' class='long' /></li>
					<li><label for='group'>Default group</label> {select 'group' $lgi.groups $lgi.group}</li>
				</ol>{*
				*}{/if}
				<div style='text-align:right'><input type='submit' name='submit_dfl' id='submit_dfl' value='Save defaults' /></div>
{*
				<script type="text/javascript"><!--
				// TODO update list of default groups when updating groups
				$(document).ready(function() {
					// TODO
				});
				//--></script>
*}
			</fieldset>

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
