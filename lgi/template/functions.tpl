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
		*}<input type='hidden' name='job_id' value='{$jobid}'/>{*
		*}<input type='image' name='submit' value='{$title}' title='{$title}' width='{$width}' height='{$height}' src='{$image}'/>{*
	*}</form>{*
*}{/template}

{*

  deletebutton / abortbutton

  Image button form that deletes or aborts a job

*}
{template deletebutton jobid nonce size=16}{*
	*}{if $size <= 20}{actionbutton cat($__.approot,'/delete') $jobid $nonce 'delete' cat($__.webroot,'/icons/action-delete_16.png') $size $size}{*
	*}{else}{actionbutton cat($__.approot,'/delete') $jobid $nonce 'delete' cat($__.webroot,'/icons/action-delete_32.png') $size $size}{/if}{*
*}{/template}

{template abortbutton jobid nonce size=16}{*
	*}{if $size <= 20}{actionbutton cat($__.approot,'/delete') $jobid $nonce 'abort' cat($__.webroot,'/icons/action-abort_16.png') $size $size}{*
	*}{else}{actionbutton cat($__.approot,'/delete') $jobid $nonce 'abort' cat($__.webroot,'/icons/action-abort_32.png') $size $size}{/if}{*
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
	*}<img src="{$__.webroot}/icons/state-{$jobstatus}_16.{tif $jobstatus=='running' ? 'gif' : 'png'}" width="{$size}" height="{$size}" alt="{$jobstatus}" title="{$jobstatus}"/>{*
*}{/template}


{*

  select

  An html select element, built from an array.
*}
{template select id values currentvalue}{*
	*}<select name='{$id}' id='{$id}'>{*
		*}{foreach $values a}{*
			*}<option value='{$a}'{if $a==$currentvalue} selected='selected'{/if}>{$a}</option>{*
		*}{/foreach}{*
	*}</select>{*
*}{/template}


{*

  inputselect

  Either an html select or a text input element, depending on if values are given or not.
*}
{template inputselect id values currentvalue}{*
	*}{if $values}{*
		*}{select $id $values $currentvalue}{*
	*}{else}{*
		*}<input type='text' name='{$id}' id='{$id}' value='{$currentvalue}' />{*
	*}{/if}{*
*}{/template}

