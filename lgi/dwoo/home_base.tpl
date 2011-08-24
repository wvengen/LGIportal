{*
	This file is the base template for a page that user sees after logging in. This is template is used by home.php, submit.php, delete.php etc. Replace only the "content" to use this template in other pages.
	Add more options to this file in block "menu"

*}<html>
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
			<li><a href="home.php">Home</a></li>
			<li><a href="jobs.php">Jobs</a> </li>
			<li><a href="submit.php">New job</a></li>
		</ul>
		<ul class="user">
			<li><a href="resources.php">Resources</a> </li>
			<li><a href="logout.php">Logout {$user}</a></li>
		</ul>
{/block}
	</div>
	<div id="content-container">
		<div id="content">
		{if $errormessage}<div id="error">{$errormessage}</div>{/if}
{block "content"}
			<h2>Welcome {$user}! </h2>
			<p>You are now logged into the LGI portal.</p>
{/block}		
		</div>
		<div id="aside">{block "aside"}{/block}</div>
		<div id="footer">Copyright 2011 <a href="http://www.biggrid.nl/">BiG Grid</a></div>
	</div>
</div></body>
</html> 
