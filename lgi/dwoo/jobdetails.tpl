{extends "home_base.tpl"}
{block "title"}
{if $jobOwner == $user}Your job {$jobId}{*
*}{else}{capitalize $jobOwner}'s job {$jobId}{/if}
{/block}
{block "content"}
			<table class="jobdetails">
				<tr class="id">
					<th>Job ID</th>
					<td>{$jobId}</td>
				</tr>
				<tr class="app">
					<th>Application</th>
					<td>{$application}</td>
				</tr>
				<tr class="state">
					<th>State</th>
					<td>{$jobStatus}</td>
				</tr>
				<tr class="owners">
					<th>Owners</th>
					<td>{$jobOwner}</td>
				</tr>
				<tr class="access">
					<th>Read Access</th>
					<td>{$readAccess}</td>
				</tr>
				<tr class="target">
					<th>Target Resources</th>
					<td>{$target}</td>
				</tr>
			</table>
{/block}
