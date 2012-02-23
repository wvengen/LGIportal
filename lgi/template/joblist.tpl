{extends "home_base.tpl"}
{block "addhead"}
<script type="text/javascript"><!--
function collapse_container(id, collapse, animation) {
	var container = $('#container-'+id);
        if (typeof(collapse)=='undefined') collapse = container.hasClass('uncollapsed');
	if (typeof(animation)=='undefined') animation = 'fold';
	// do so
        if (collapse) {
                $('.contained-by-'+id).hide(animation, function() {
                        container.removeClass('child-top');
                });
                container.removeClass('uncollapsed').addClass('collapsed');
		$('.collapse img', container)[0].src = '{$webroot}/css/menu-collapsed.png';
		// TODO fix table colouring
        } else {
		$('.collapse img', container)[0].src = '{$webroot}/css/menu-expanded.png';
                container.removeClass('collapsed').addClass('uncollapsed child-top');
                $('.contained-by-'+id).show(animation);
		// TODO fix table colouring
        }
	return container;
}

// collapse by default
$(function() {
	// gather parent ids
	var ids = [];
	$('.contained').each(function(i,e) {
		var m = e.className.match('contained-by-(.*)');
		if (m!==null && jQuery.inArray(m[1], ids)<0)
			ids.push(m[1]);
	});
	// collapse them
	for (var i in ids) {
		collapse_container(ids[i], true, null);
	}
});
--></script>
{/block}
{block "title"}Your jobs{/block}
{block "content"}
{load_templates "functions.tpl"}
			<table class="list joblist interactive">
				<tr>
					<th class="collapse"></th>
					<th class="state"></th>
					<th class="id">ID</th>
					<th class="app">Application</th>
					<th class="name">Name</th>
					<th class="owners">Owners</th>
					<th class="target">Target resources</th>
					<th class="action"></th>
				</tr>
				{loop $jobs}{*
				*}{if $isparent}{*
					*}{assign cat("javascript:collapse_container(",$job_specifics.parent,")") action}{*
				*}{else}{*
					*}{assign cat($__.approot,'/job/',$job_id) action}{*
				*}{/if}{*
				*}<tr class="{cycle values=array('odd','even')}{*
						*}{if $child_bottom} child-bottom{/if}{*
						*}{if $isparent} child-top container uncollapsed{*
						*}{elseif $job_specifics.parent} contained contained-by-{$job_specifics.parent}{/if}{*
						*}"{if $isparent} id="container-{$job_specifics.parent}"{/if}>
					<td class="collapse">{if $isparent}{*
						*}<a href="{$action}"><img src="{$__.webroot}/css/menu-expanded.png" width="10" height="10" alt="o" /></a>{*
					*}{/if}</td>
					<td class="state">{stateimg $state}</td>
					<td class="id"><a href="{$action}">{if !$isparent}{$job_id}{else}({$job_specifics.nchildren}){/if}</a></td>
					<td class="app"><a href="{$action}">{$application}</a></td>
					<td class="name"><a href="{$action}">{tif $job_specifics.title ? $job_specifics.title : '(untitled)'}</a></td>
					<td class="owners">{$owners}</td>
					<td class="target">{$target_resources}</td>
					<td class="action spacer">{abdelbutton $job_id $state $__.nonce}</td>
				</tr>{*
				*}{/loop}
			</table>
{/block}
