  <a name="newRemark"></a>

   <div class="remark multiRemark">
    <h2>Add a new remark to marked comments:</h2>

      <label>
        <input type="radio" name="mutation" value="none" checked="checked"/>
        Don't modify status of the comments
      </label><br/>

{if in_array( "Closed", $statusFilter ) and count( $statusFilter ) == 1 }
{include file='html/newremark_multi_closedmutations.tpl'}
{elseif not in_array( "Closed", $statusFilter ) }
{include file='html/newremark_multi_openmutations.tpl'}
{else}
  <div class="error">You can't modify the status of both open and closed comments.</div>
{/if}

     <textarea name="newRemark" id="newRemark"></textarea><br/>

     <label class="mailRemark" name="mailUserBox">
        <input class="mailRemark" type="checkbox" name="mailUser" checked="checked" value="checked" />
        Also send this remark to non-anonymous authors
     </label><br/>
     <input type="hidden" name="page" value="{$page}"/>
     <input type="submit" value="Add New Remark" />
   </div>

