{*****************************************************************************************
 *	The template for deleting a job. It extends the homs_base.tpl. 
 * 	The heading, menus, footer are same as base template. Only the content is changed. 
 *****************************************************************************************}
{extends "home_base.tpl"}
{block "content"}
			<h2>Welcome to the LGI portal!</h2>
			
				<form action="delete.php" method="post" class="cmxform">				
				<fieldset>
				<ol>
					
					<li>					
        					<label for="jobid">Job ID:</label> <input type="text" name="jobid" id="jobid" /> <br/>
        			 	</li>
        			 	
        			 	<li>
        			 		<input type="submit" value="Delete Job" />
        			 		<input type="hidden" name="submitrequest" value="request"/>
        			 		<input type="hidden" name="nonce" value={$nonce}/>
        			 	</li>
        			 </ol>
				</fieldset>
				</form>
			
			
{/block}

