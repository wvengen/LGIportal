{*

 == Templates ==

 These can be used as commands after they have been loaded by load_templates.

*}

{*

 actionbutton 

 Form that POSTs an action rendered as an image.
 Useful for giving access to modifying operations as 'delete'.

*}
{template actionbutton action jobid nonce title image width=16 height=16}{*
*}<form action='{$action}' method='POST'>{*
		*}<input type='hidden' name='nonce' value='{$nonce}'/>{*
		*}<input type='hidden' name='jobid' value='{$jobid}'/>{*
		*}<input type='image' name='submit' value='{$title}' title='{$title}' width='{$width}' height='{$height}' src='{$image}'/>{*
	*}</form>{*
*}{/template}

{*

  deletebutton / abortbutton

  Image button form that deletes or aborts a job

*}
{template deletebutton jobid nonce size=16}{*
	*}{if $size <= 20}{actionbutton 'delete.php' $jobid $nonce 'delete' 'icons/action-delete_16.png' $size $size}{*
	*}{else}{actionbutton 'delete.php' $jobid $nonce 'delete' 'icons/action-delete_32.png' $size $size}{/if}{*
*}{/template}

{template abortbutton jobid nonce size=16}{*
	*}{if $size <= 20}{actionbutton 'abort.php' $jobid $nonce 'abort' 'icons/action-abort_16.png' $size $size}{*
	*}{else}{actionbutton 'delete.php' $jobid $nonce 'abort' 'icons/action-abort_32.png' $size $size}{/if}{*
*}{/template}

{*

  abdelbutton

  Image button that deletes or aborts a job, depending on job state.

*}
{template abdelbutton jobid jobstatus nonce size=16}{*
	*}{if $jobstatus!='finished' && $jobstatus!='queued' && $jobstatus!='aborted'}{*
		*}{abortbutton $jobid $nonce $size}{*
	*}{else}{*
		*}{deletebutton $jobid $nonce $size}{*
	*}{/if}{*
*}{/template}

{*

  stateimg

  Job state image
*}
{template stateimg jobstatus size=16}{*
	*}<img src="icons/state-{$jobstatus}_16.{tif $jobstatus=='running' ? 'gif' : 'png'}" width="{$size}" height="{$size}" alt="{$jobstatus}" title="{$jobstatus}"/>{*
*}{/template}

