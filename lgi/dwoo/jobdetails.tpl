{extends "home_base.tpl"}
{block "title"}
{if $job.owners == $user}Your job {$job_id}{*
*}{else}Job {$job_id}{/if}
{/block}
{block "content"}
{load_templates "functions.tpl"}
			<div style="float:right">{abdelbutton $job.job_id $job.state $nonce 32}</div>

			<table class="jobdetails desc">
				<tr class="id">
					<th>Job:</th>
					<td>{$job.job_id} {if $job.job_name && $job.job_name!='(untitled)'}: <i>{$job_name}</i>{/if}</td>
				</tr>
				<tr class="app">
					<th>Application:</th>
					<td>{$job.application} <span class="nonimp">at {$job.target_resources}</span></td>
				</tr>
				<tr class="state">
					<th>State:</th>
					<td>{stateimg $job.state}{$job.state} <span class="nonimp">since {date_format $job.state_time_stamp '%c'}</span></td>
				</tr>
				<tr class="owners">
					<th>Owner:</th>
					<td>{$job.owners}</td>
				</tr>
				<tr class="access">
					<th>Access:</th>
					<td><i>read:</i> {$job.read_access}; <i>write:</i> {$job.write_access}</td>
				</tr>
				<tr class="repo">
					<th>Repository:</th>
					<td><em>TODO</em></td>
				</tr>
			</table>

			<p><em>TODO: input, output and job specifics</em></p>
{/block}
