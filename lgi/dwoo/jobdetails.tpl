{extends "home_base.tpl"}
{block "title"}
{if $jobOwner == $user}Your job {$jobId}{*
*}{else}{capitalize $jobOwner}'s job {$jobId}{/if}
{/block}
{block "content"}
{load_templates "functions.tpl"}
			<div style="float:right">{abdelbutton $jobId $jobStatus $nonce 32}</div>

			<table class="jobdetails desc">
				<tr class="id">
					<th>Job:</th>
					<td>{$jobId} {if $jobName && $jobName!='(untitled)'}: <i>{$jobName}</i>{/if}</td>
				</tr>
				<tr class="app">
					<th>Application:</th>
					<td>{$application} <span class="nonimp">at {$target}</span></td>
				</tr>
				<tr class="state">
					<th>State:</th>
					<td>{stateimg $jobStatus}{$jobStatus} <span class="nonimp">since {date_format 0 '%c'} <em>TODO</em></span></td>
				</tr>
				<tr class="owners">
					<th>Owner:</th>
					<td>{$jobOwner}</td>
				</tr>
				<tr class="access">
					<th>Access:</th>
					<td><i>read:</i> {$readAccess}; <i>write:</i> {$writeAccess}</td>
				</tr>
				<tr class="repo">
					<th>Repository:</th>
					<td><em>TODO</em></td>
				</tr>
			</table>

			<p><em>TODO: input, output and job specifics</em></p>
{/block}
