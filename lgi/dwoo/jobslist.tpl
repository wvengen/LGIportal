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
					<td class="state">{stateimg $jobStatus}</td>
					<td class="id"><a href="viewjob.php?jobid={$jobId}">{$jobId}</a></td>
					<td class="app"><a href="viewjob.php?jobid={$jobId}">{$application}</a></td>
					<td class="name"><a href="viewjob.php?jobid={$jobId}">{tif $jobName ? $jobName : '(untitled)'}</a></td>
					<td class="owners">{$jobOwner}</td>
					<td class="target">{$target}</td>
					<td class="action spacer">{abdelbutton $jobId $jobStatus $_.nonce}</td>
				</tr>{/loop}
			</table>
{/block}
