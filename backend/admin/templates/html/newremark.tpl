  <a name="newRemark"></a>
  <h2>Add a new remark:</h2>
   <div class="remark {$comment->type}">
    <form action="comment.php?id={$comment->id}" method="post">
      <strong>Mark comment as:</strong>
        <select name="newStatus">
          <optgroup label="Open">
{section name=i loop=$statuses}
  {if strToLower($statuses[i]) != "closed"}
    {if $statuses[i] == $comment->status}{assign var=selected value='selected=""'}{else}{assign var=selected value=''}{/if}
            <option value="{$statuses[i]}" {$selected}>{$statuses[i]|message:'status'}</option>
  {/if}
{sectionelse}
            <option>Error: No known statuses</option>
{/section}
          </optgroup>
          <optgroup label="Closed">
{section name=j loop=$resolutions}
  {if strToLower($comment->status) == "closed" && $comment->resolution == $resolutions[j]}
    {assign var=selected value='selected=""'}{else}{assign var=selected value=''}
  {/if}
            <option value="closed_{$resolutions[j]}" {$selected}>{$resolutions[j]|message:'resolution'}</option>
{sectionelse}
            <option>Error: No known resolutions</option>
{/section}
          </optgroup>
        </select><br/>
     <textarea name="newRemark" id="newRemark" style="width: 50%; height: 100px; vertical-align: middle"></textarea><br/>
{if $comment->email}
     <label class="mailRemark" name="mailUserBox">
        <input class="mailRemark" type="checkbox" name="mailUser" checked="checked" value="checked" />
{else}
     <label disabled="disabled" class="mailRemarkOff" name="mailUserBox">
        <input disabled="disabled" class="mailRemarkOff" type="checkbox" name="mailUser" value="checked" />
{/if}
        Also send this comment to the author</label><br/>
     <input type="hidden" name="page" value="{$page}"/>
     <input type="submit" value="Add New Remark" style="vertical-align: middle"/>
    </form>
   </div>

