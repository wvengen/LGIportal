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
					<td class="name">{$name}</td>
					<td class="cap">{foreach $capabilities n c implode=', '}{$n}{/foreach}</td>
					<td class="time">{date_format $lastcalltime '%c'}</td>
				</tr>{/loop}
			</table>
{/block}
