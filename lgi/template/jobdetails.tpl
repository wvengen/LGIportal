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
					<td>{$job.job_id}{if $job.job_specifics.title} <i>{$job.job_specifics.title}</i>{/if}</td>
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
					<td><tt><a href="{$approot}/repository?url={escape $job.job_specifics.repository_url url}">{regex_replace $job.job_specifics.repository_url '/^.*\//' ''}</a></tt></td>
				</tr>
			</table>

			<fieldset class="collapsible uncollapsed jobdetails"><legend>Input</legend>
			<pre>{$job.input}</pre>
			</fieldset>

			<fieldset class="collapsible uncollapsed jobdetails"><legend>Output</legend>
			<pre>{$job.output}</pre>
			</fieldset>

			<fieldset class="collapsible collapsed jobdetails"><legend>Job specifics</legend>
			<table class="list job_specifics">
				{loop $job.job_specifics}<tr class="job_specifics-{regex_replace $_key '/[^-a-zA-Z0-9_]/' ''}">
					<th>{$_key}</th>
					<td>{$}</td>
				</tr>{/loop}
			</table>
			</fieldset>
{/block}
