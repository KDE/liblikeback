  <a name="newRemark"></a>
{if strToLower( disable ) == "closed" }
  <h2>Add a new remark:</h2>
{/if}
   <div class="remark {$comment->type}">
    <form name="newRemarkForm" action="comment.php?id={$comment->id}" method="post">
      <label>
        <input type="radio" name="mutation" value="none" checked="checked"/>
        Don't modify status of this comment
      </label><br/>

{if strToLower( $comment->status ) == "closed" }
{include file='html/newremark_closedmutations.tpl'}
{else}
{include file='html/newremark_openmutations.tpl'}
{/if}

     <textarea name="newRemark" id="newRemark"></textarea><br/>
{if $comment->email}
     <label class="mailRemark" name="mailUserBox">
        <input class="mailRemark" type="checkbox" name="mailUser" checked="checked" value="checked" />
{else}
     <label disabled="disabled" class="mailRemarkOff" name="mailUserBox">
        <input disabled="disabled" class="mailRemarkOff" type="checkbox" name="mailUser" value="checked" />
{/if}
        Also send this comment to the author</label><br/>
     <input type="hidden" name="page" value="{$page}"/>
     <input type="submit" value="Add New Remark" />
    </form>
   </div>

