{*****************************************************************************************
 *	The template for deleting a job. It extends the home_base.tpl. 
 * 	The heading, menus, footer are same as base template. Only the content is changed. 
 *****************************************************************************************}
{extends "home_base.tpl"}
{block "title"}Job details{/block}
{block "content"}
			<form action="{$approot}/view" method="post" class="cmxform">				
				<fieldset><ol>
					<li><label for="job_id">Job ID:</label> <input type="text" name="job_id" id="job_id" /></li>
       				 	<li><input type="submit" value="View Job Details" /> <input type="hidden" value="request" name="submitrequest"/></li>
				</ol></fieldset>
			</form>
{/block}

