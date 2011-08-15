{extends "home_base.tpl"}
{block "content"}
			<table border="1">
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
