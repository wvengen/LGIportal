{extends "home_base.tpl"}
{block "title"}Available resources{/block}
{block "content"}
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
{/block}
