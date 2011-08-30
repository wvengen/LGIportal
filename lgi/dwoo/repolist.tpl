{extends "home_base.tpl"}
{block "title"}Repository {$repo_id}{/block}
{block "content"}
{load_templates "functions.tpl"}
			{if $files}<table class="list filelist interactive">
				<tr>
					<th class="filename">Filename</th>
					<th class="filesize">Size</th>
					<th class="filetime">Time</th>
				</tr>{/if}
				{loop $files}<tr class="{cycle values=array('odd','even')}">
					<td class="filename"><a href="repository.php?url={escape $_.url url}&amp;file={escape $name url}">{$name}</a></td>
					<td class="filesize">{$size}</td>
					<td class="time">{date_format $date '%c'}</td>
				</tr>
				{else}<p><i>(no files in this repository)</i></p>{/loop}
			{if $files}</table>{/if}
{/block}
