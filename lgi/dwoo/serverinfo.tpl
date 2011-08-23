{extends "home_base.tpl"}
{block "title"}Resources and servers{/block}
{block "content"}
			<h2>Resources</h2>
			<table class="resourcelist list">
				<tr>
					<th class="name">Name</th>
					<th class="cap">Capabilities</th>
					<th class="time">Last seen</th>                          
				</tr>
				{loop $resources}<tr class="{cycle values=array('odd','even')}">
					<td class="name">{$resource_name}</td>
					<td class="cap">{foreach $resource_capabilities n c implode=', '}{$n}{/foreach}</td>
					<td class="time">{date_format $last_call_time '%c'}</td>
				</tr>{/loop}
			</table>

			<h2>Project servers</h2>
			<table class="serverlist list">
				<tr>
					<th>Server url</th>
					<th class="flag">Master</th>
					<th class="flag">Selected</th>
				</tr>
				<tr>
					<td>{$project_master_server}</td>
					<td class="flag">x</td>
					<td class="flag">{if $project_master_server==$this_project_server}x{/if}</td>
				</tr>
				{foreach $servers s}<tr class="{cycle values=array('odd','even')}">
					<td>{$s}</td>
					<td class="flag"></td>
					<td class="flag">{if $s==$this_project_server}x{/if}</td>
				</tr>{/foreach}
			</table>

{/block}
