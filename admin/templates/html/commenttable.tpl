   <table id="data">
    <thead>
     <tr>
      <th>Id</th>
      <th>Type</th>
      <th>Status</th>
      <th>Comment</th>
      <th>Locale</th>
      <th>Date</th>
      <th>Version</th>
      <th>Window</th>
      <th>Context</th>
      {* <th>&nbsp;</th> *}
     </tr>
    </thead>
    <tbody>
{section name=i loop=$comments}
{assign var='comment' value="$comments[i]"}
{assign var='commentLink' value="comment.php?id=`$comment->id`&amp;page=$page"}
{assign var='id' value=`$comment->id`}
     <tr class="{$comment->type} {$comment->status}" id="comment_{$id}">
      <td><a href="{$commentLink}">#{$comment->id}</a></td>
      <td>{iconForType type=`$comment->type`}<a href="comment_{$id}"></a></td>
      <td><nobr>
        <a href="#" onclick="return showStatusMenu(event)">{iconForStatus id="$id" status=`$comment->status`}</a>
        <a title="Remark count" href="{$commentLink}">{$comment->remarkCount}<img src="icons/remarks.png" width="16" height="16" alt="remarks"/>
{if ! empty( $comment->email ) }
          <img src="icons/email.png" width="16" height="16" title="E-mail address available" alt="E-mail address available"/>
{/if}
        </a>
      </nobr></td>
      {* todo: replace found parts from textFilter with <span class="found">, see rev 4651 of view.php *}
      <td class="listed-comment"><a href="{$commentLink}">{$comment->comment|escape:'html':'utf-8'|nl2br}</a></td>
      <td>{$comment->locale|escape:'html':'utf-8'}</td>
      <td><div title="{$comment->date|date_format:"%d-%m-%Y"}, at {$comment->date|date_format:"%T"}"><nobr>{$comment->date|date_format:"%d %b %Y"}</nobr></div></td>
      <td class="listed-minor">{$comment->version|escape:'html':'utf-8'}</td>
      <td class="listed-minor">
        <div title="{$comment->window|escape:'html':'utf-8'}"><nobr>
          {$comment->window|truncate:20:'...':TRUE|escape:'html':'utf-8'}
        </nobr></div>
      </td>
      <td class="listed-minor">{$line->context|escape:'html':'utf-8'}</td>
      {* <td style="text-align: center">{$emailCell}</td> *}
     </tr>
{/section}
    </tbody>
   </table>
