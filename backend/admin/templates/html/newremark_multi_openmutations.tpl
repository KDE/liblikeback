      <label><input type="radio" name="mutation" value="close"/>
      Close comment with status:</label>
        <select name="closeResolution" onclick="setMutationTo( 'close' );">
{section name=j loop=$resolutions}
  {if strToLower($comment->status) != "closed" || $comment->resolution != $resolutions[j]}
          <option value="{$resolutions[j]}">{$resolutions[j]|message:'resolution'}</option>
  {/if}
{sectionelse}
          <option>Error: No known resolutions</option>
{/section}
        </select><br/>


      <label><input type="radio" name="mutation" value="restatus"/>
      Change status to:</label>
        <select name="restatusStatus" onclick="setMutationTo( 'restatus' );">
{section name=i loop=$statuses}
  {if strToLower($statuses[i]) != "closed" && strToLower($statuses[i]) != "triaged" && strToLower($statuses[i]) != strToLower($comment->status)}
          <option value="{$statuses[i]}">{$statuses[i]|message:'status'}</option>
  {/if}
{sectionelse}
          <option>Error: No known statuses</option>
{/section}
        </select><br/>


{if strToLower($comment->status) != "triaged" && isset($tracurl)}
      <label><input type="radio" name="mutation" value="triage"/>
      Triage comment to Trac bug:</label>
        <input type="text" name="tracbug" onclick="setMutationTo( 'triage' );"/><br/>
{/if}
