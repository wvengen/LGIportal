{extends "home_base.tpl"}
{block "title"}Your jobs{/block}
{block "content"}
{load_templates "functions.tpl"}
			<table class="list joblist interactive">
				<tr>
					<th class="state"></th>
					<th class="id">ID</th>
					<th class="app">Application</th>
					<th class="name">Name</th>
					<th class="owners">Owners</th>
					<th class="target">Target resources</th>
					<th class="action"></th>
				</tr>
				{loop $jobs}<tr class="{cycle values=array('odd','even')}">
					<td class="state">{stateimg $state}</td>
					<td class="id"><a href="{$__.approot}/job/{$job_id}">{$job_id}</a></td>
					<td class="app"><a href="{$__.approot}/job/{$job_id}">{$application}</a></td>
					<td class="name"><a href="{$__.approot}/job/{$job_id}">{tif $job_specifics.title ? $job_specifics.title : '(untitled)'}</a></td>
					<td class="owners">{$owners}</td>
					<td class="target">{$target_resources}</td>
					<td class="action spacer">{abdelbutton $job_id $state $__.nonce}</td>
				</tr>{/loop}
			</table>
{/block}
