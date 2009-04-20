   <div class="remark {$commenttype}">
    <form action="comment.php?id={$commentid}" method="post">
    <h4>New remark:</h4>
     <textarea name="newRemark" id="newRemark" style="width: 50%; height: 100px; vertical-align: middle"></textarea>
     {$checkBoxHtml}
     <input type="hidden" name="page" value="{$page}"/>
     <input type="submit" value="Add New Remark" style="vertical-align: middle"/>
    </form>
   </div>

