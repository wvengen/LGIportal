{extends "home_base.tpl"}
{block "content"}
{load_templates "functions.tpl"}
			<table border="1">
				<tr>
					<th class="state"></th>
					<th class="id">ID</th>
					<th class="app">Application</th>
					<th class="name">Name</th>
					<th class="owners">Owners</th>
					<th class="target">Target resources</th>
					<th class="action"></th>
				</tr>
				{loop $jobs}<tr>
					<td class="state">
						<img src="icons/state-{$jobStatus}.{tif $jobState=='running' ? 'gif' : 'png'}" width="16" height="16" alt="{$jobStatus}" title="{$jobStatus}"/>
					</td>
					<td class="id">{$jobId}</td>    						
					<td class="app">{$application}</td>
					<td class="name">{$jobName}</td>
					<td class="owners">{$jobOwner}</td>
					<td class="target">{$target}</td>
					<td class="action spacer">
						{if $jobStatus!='finished' && $jobStatus!='queued' && $jobStatus!='aborted'}{*
							*}{actionbutton 'delete.php' $jobId 'abort' 'icons/action-abort.png'}{*
						*}{else}{*
							*}{actionbutton 'delete.php' $jobId 'delete' 'icons/action-delete.png'}{*
						*}{/if}
					</td>
				</tr>{/loop}
			</table>
{/block}
