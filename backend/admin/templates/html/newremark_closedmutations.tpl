      <label><input type="radio" name="mutation" value="reopen"/>
      <strong>Reopen comment with status:</strong></label>
        <select name="reopenStatus" onclick="setMutationTo('reopen')">
{section name=i loop=$statuses}
  {if strToLower($statuses[i]) != "closed" && strToLower($statuses[i]) != "triaged" && strToLower($statuses[i]) != strToLower($comment->status)}
          <option value="{$statuses[i]}">{$statuses[i]|message:'status'}</option>
  {/if}
{sectionelse}
          <option>Error: No known statuses</option>
{/section}
        </select><br/>


      <label><input type="radio" name="mutation" value="reclose"/>
      <strong>Change resolution to:</strong></label>
        <select name="recloseResolution" onclick="setMutationTo('reclose');">
{section name=j loop=$resolutions}
  {if strToLower($comment->status) != "closed" || $comment->resolution != $resolutions[j]}
          <option value="{$resolutions[j]}">{$resolutions[j]|message:'resolution'}</option>
  {/if}
{sectionelse}
          <option>Error: No known resolutions</option>
{/section}
        </select><br/>
