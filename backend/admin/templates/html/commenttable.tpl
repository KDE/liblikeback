{if $showEditingOptions}
   <form name="newRemarkForm" action="comment.php" method="post">
{/if}

{if $pager}
   <div class="pager">{$pager}</div>
{/if}

   <table id="data">
    <thead>
{if $showEditingOptions}
     <tr>
      <th colspan="4" class="CommentsTableMarksLinks">
        In this page:<br />
        <a href="#" onclick="return mark(true);">Mark all</a>
        or
        <a href="#" onclick="return mark(false);">Mark none</a>
      </th>
      <th colspan="5" class="CommentsTableLinks">
        <a href="#newRemark">Actions on selected comments...</a>
      </th>
     </tr>
{/if}
     <tr>
{if $showEditingOptions}
      <th class="noexpand">Mark</th>
{/if}
      <th class="noexpand">Id</th>
      <th class="noexpand">Type</th>
      <th class="noexpand">Status</th>
      <th>Comment</th>
      <th class="noexpand">Locale</th>
      <th class="noexpand">Date</th>
      <th class="noexpand">Version</th>
      <th class="noexpand">Window</th>
      {* <th>Context</th> *}
      {* <th>&nbsp;</th> *}
     </tr>
    </thead>
    <tfoot>
     <tr>
{if $showEditingOptions}
      <td colspan="9" class="CommentsTableActions">
{if $pager}
        <div class="pager">{$pager}</div>
{/if}
        {include file='html/newremark_multi.tpl'}
      </td>
{else}
{if $pager}
      <td colspan="9" class="pager">
        {$pager}
      </td>
{else}
      <td colspan="9"></td>
{/if}
{/if}
     </tr>
    </tfoot>
    <tbody>
{section name=i loop=$comments}
{assign var='comment' value="$comments[i]"}
{assign var='commentLink' value="comment.php?id=`$comment->id`&amp;page=$page"}
{assign var='id' value=`$comment->id`}
{assign var='class' value="class=\"`$comment->status` `$comment->type`\""}
     <tr {$class} id="comment_{$id}">
{if $showEditingOptions}
      <td class="nobr"><input type="checkbox" name="check_comment_{$id}" /></a></td>
{/if}
      <td class="nobr">{$comment->aname}<a href="{$commentLink}" {$class}>#{$comment->id}</a></td>
      <td class="nobr">{$comment->type|message:'type':'icon'}&nbsp;</td>
      <td class="nobr">
        <a title="Comment status" href="{$commentLink}#newRemark">
{if strToLower($comment->status) == "closed" }
          {$comment->resolution|message:'resolution':'icon'}
{else}
          {$comment->status|message:'status':'icon'}
{/if}
        </a>
        <a title="Remark count" href="{$commentLink}">{$comment->remarkCount}<img src="icons/remarks.png" width="16" height="16" alt="remarks"/>
{if ! empty( $comment->email ) }
          <img src="icons/email.png" width="16" height="16" title="E-mail address available" alt="E-mail address available"/>
{/if}
        </a>
      </td>
      {* todo: replace found parts from textFilter with <span class="found">, see rev 4651 of view.php *}
      <td class="listed-comment"><a href="{$commentLink}" class="listed-comment {$comment->status} {$comment->type}">{$comment->comment|nl2br}</a></td>
      <td class="nobr">{$comment->locale|escape:'html':'utf-8'}</td>
      <td class="nobr">
        <div title="{$comment->date|date_format:"%d-%m-%Y"}, at {$comment->date|date_format:"%T"}">
         {$comment->date|date_format:"%d %b %Y"}
        </div>
      </td>
      <td class="listed-minor nobr">
        <div title="{$comment->fullVersion|escape:'html':'utf-8'}">
          {$comment->version|escape:'html':'utf-8'}
        </div>
      </td>
      <td class="listed-minor nobr">
        <div title="{$comment->window|escape:'html':'utf-8'}">
          {$comment->window|truncate:20:'...':TRUE|escape:'html':'utf-8'}
        </div>
      </td>
      {* <td class="listed-minor">{$line->context|escape:'html':'utf-8'}</td> *}
     </tr>
{sectionelse}
     <tr>
      <td class="listed-comment error" colspan="{if $showEditingOptions}9{else}8{/if}">
        No comments were found.
      </td>
     </tr>
{/section}
    </tbody>
   </table>

{if $showEditingOptions}
   </form>
{/if}
