{*****************************************************************************************
 *	The template for submiting a job. It extends the home_base.tpl. 
 * 	The heading, menus, footer are same as base template. Only the content is changed. 
 *****************************************************************************************}
{extends "home_base.tpl"}
{block "title"}Submit new job{/block}
{block "addhead"}
	<script src="{$webroot}/js/jquery.MultiFile.js" type="text/javascript"></script>
{/block}
{block "content"}
{load_templates "functions.tpl"}
				<form id='newjob' action='submit.php' method='post' enctype='multipart/form-data'>
					<input type='hidden' name='nonce' value='{$nonce}' />

					<div><label for='input'>Input</label></div>
					<textarea name='input' id='input' wrap='off' cols='80' rows='15'>{$input}</textarea>
					<p></p>

					<fieldset class='collapsible uncollapsed'><legend>Attach files</legend>
						<input name='uploaded_file' id='uploaded_file' type='file' class='multi' />
					</fieldset>

					<fieldset class='collapsible uncollapsed'><legend>Advanced</legend>
						<div>
							<label for='application'>Application</label>
							{inputselect 'application' $applications $application}
						</div>
						<div>Additional
							<label for='read_access'>read access</label> for
							<input type='text' name='read_access' id='read_access' value='{$read_access}' />
							and <label for='write_access'>write access</label> for
							<input type='text' name='write_access' id='write_access' value='{$write_access}' />.
						</div>
					</fieldset>

					<p style="text-align: right"><input type='submit' name='submit' id='submit' value='Submit job' />
				</form>
{/block}

