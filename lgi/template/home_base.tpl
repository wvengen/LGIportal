{*
	This file is the base template for a page that user sees after logging in.
	All 'normal' pages will use this, and replace the block "content".
	Additional header items can be added using the block "addhead".

*}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>LGI Portal - {block "title"}{/block}</title>
	<link rel="stylesheet" href="{$webroot}/css/layout.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="{$webroot}/css/collapse.css" type="text/css" media="screen" />
	<script src="{$webroot}/js/jquery.js" type="text/javascript"></script>
	<script src="{$webroot}/js/jquery.collapsible.js" type="text/javascript"></script>
	<script type="text/javascript">{literal}<!--
	$(function() {
		$('fieldset.collapsed').collapse({closed: true});
		$('fieldset.uncollapsed').collapse({closed: false});
	});
	//-->{/literal}</script>
{block "addhead"}
{/block}
</head>
<body><div id="container">
	<div id="header">
		<h1>{block "title"}LGI Portal{/block}</h1>
	</div>
	<div id="navigation">
{block "menu"}
		<ul>
			<li><a href="{$approot}/jobs">Jobs</a> </li>
			<li><a href="{$approot}/submit">New job</a></li>
		</ul>
		<ul class="user">
			<li><a href="{$approot}/resources">Resources</a> </li>
			<li><a href="{$approot}/user">Settings</a> </li>
			<li id="nav_user_groups"><a href="{$approot}/user#groups"><img src="{$webroot}/icons/system-users_16.png" width="16" height="16" alt="Group:" /> {$lgi.group}</a>
				<script type="text/javascript"><!--
				var approot = "{$approot}";
				// enhance group switch button with drop-down menu to dynamically switch group
				document.write('<ul class="dropdown" style="display:none"><li class="header">switch group<\/li>');
				{foreach $lgi.groups g}
				document.write('<li><a href="javascript:switch_group(\'{$g}\')"><img src="{$webroot}/icons/system-users_16.png" width="16" height="16" alt="Group" /> {$g}<\/a><\/li>');
				{/foreach}{*
				*}{literal}
				document.write('<\/ul>');
				// open menu on hover
				$(document).ready(function() {
					$('#nav_user_groups').hover(function() {
						$('#nav_user_groups ul').fadeIn();
					}, function() {
						$('#nav_user_groups ul').fadeOut();
					});
					// TODO handle focusin and focusout as well
				});
				// handle menu events
				function switch_group($g) {
					// update group using ajax
					// TODO implement json handling in LGIportal, now just returns html page :o
					data = {'json':true, 'submit_dfl':true, 'group': $g};
					$.post(approot+'/user', data, function(data) {
						// then reload page
						window.location.reload();
					});
				}
				//-->{/literal}</script></li>
			<li><a href="{$approot}/logout">Logout {$user}</a></li>
		</ul>
{/block}
	</div>
	<div id="content-container">
		<div id="content">
		{if $errormessage}<div id="error">{$errormessage}</div>{/if}
		{if $infomessage}<div id="info">{$infomessage}</div>{/if}
{block "content"}
			<h2>Welcome {$user}! </h2>
			<p>You are now logged into the LGI portal.</p>
{/block}		
		</div>
		<div id="aside">{block "aside"}{/block}</div>
	</div>
	<div id="footer">&#169;2011 <a href="http://www.biggrid.nl/">BiG Grid</a></div>
</div></body>
</html> 
