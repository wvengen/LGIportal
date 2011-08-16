{extends "home_base.tpl"}
{block "content"}
			<table class="resourcelist">
				<tr>
					<th>Resource Name</th>
					<th>Capabilities</th>
					<th>Last Call Time</th>                          
				</tr>
				{loop $resources}<tr>
					<td>{$name}</td>    						
					<td>{$capabilities}</td>
					<td>{$lastcalltime}</td>    						
				</tr>{/loop}
			</table>
{/block}
