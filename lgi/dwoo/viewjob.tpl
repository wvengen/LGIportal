{*****************************************************************************************
 *	The template for deleting a job. It extends the home_base.tpl. 
 * 	The heading, menus, footer are same as base template. Only the content is changed. 
 *****************************************************************************************}

{extends "home_base.tpl"}
{block "content"}
			<h2>Welcome to the LGI portal!</h2>
			
			<form action="viewjob.php" method="post" class="cmxform">				
				<fieldset><ol>
					<li><label for="jobid">Job ID:</label> <input type="text" name="jobid" id="jobid" /></li>
       				 	<li><input type="submit" value="View Job Details" /> <input type="hidden" value="request" name="submitrequest"/></li>
				</ol></fieldset>
			</form>
{/block}

