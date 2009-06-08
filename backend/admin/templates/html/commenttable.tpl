   <table id="data">
    <thead>
     <tr>
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
    <tbody>
{section name=i loop=$comments}
{assign var='comment' value="$comments[i]"}
{assign var='commentLink' value="comment.php?id=`$comment->id`&amp;page=$page"}
{assign var='id' value=`$comment->id`}
{assign var='class' value="class=\"`$comment->status` `$comment->type`\""}
     <tr {$class} id="comment_{$id}">
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
      <td class="listed-comment"><a href="{$commentLink}" class="listed-comment {$comment->status} {$comment->type}">{$comment->comment|escape:'html':'utf-8'|nl2br}</a></td>
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
      <td></td>
      <td></td>
      <td></td>
      <td class="listed-comment" style="text-align: center; font-weight: bold;"><br/>No comments were found.</td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
     </tr>
{/section}
    </tbody>
   </table>
