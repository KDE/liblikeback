  <div class="content">
   <table class="summary">
    <tr><th>Version:</th> <td>{$comment->fullVersion|escape:'html':'utf-8'}</td></tr>
    <tr><th>Locale:</th>  <td>{$comment->locale|escape:'html':'utf-8'}</td></tr>
    <tr><th>Window:</th>  <td>{$comment->window|escape:'html':'utf-8'}</td></tr>
    {* <tr><th>Context:</th> <td>{$comment->context|escape:'html':'utf-8'}</td></tr> *}
    <tr><th>Status:</th>  <td><a href="#newRemark">{$comment->status|message:'status':'both'}</a></td></tr>
{if $comment->email}
    <tr><th>E-mail:</th>  <td>{$comment->email} (<em>Please reply by using the form below</em>)</td></tr>
{else}
    <tr><th>E-mail:</th>  <td>Anonymous</td></tr>
{/if}
   </table>
   <div class="comment">
   {$comment->comment|escape:'html':'utf-8'|nl2br}
   </div>
