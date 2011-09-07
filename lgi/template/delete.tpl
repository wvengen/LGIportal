{*****************************************************************************************
 *	The template for deleting a job. It extends the homs_base.tpl. 
 * 	The heading, menus, footer are same as base template. Only the content is changed. 
 *****************************************************************************************}
{extends "home_base.tpl"}
{block "title"}Delete job{/block}
{block "content"}
			<form action="delete.php" method="post" class="cmxform">				
				<fieldset>
				<ol>
					<li>					
       						<label for="job_id">Job ID:</label> <input type="text" name="job_id" id="job_id" /> <br/>
       				 	</li>
       				 	<li>
       				 		<input type="submit" value="Delete Job" />
       				 		<input type="hidden" name="nonce" value="{$nonce}" />
       			 	</li>
       				</ol>
				</fieldset>
			</form>
			
			
{/block}

