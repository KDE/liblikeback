
{if $skipped > 0}
    <h2 class="error">The comment was already up to date.</h2>
{/if}

{assign var=newRemarkFocus value='class="invisibleLink" href="javascript:document.getElementById(\'newRemark\').focus();"'}
  <div class="content">
   <table class="summary">
    <tr><th>Version:</th> <td>{$comment->fullVersion|escape:'html':'utf-8'}</td></tr>
    <tr><th>Locale:</th>  <td>{$comment->locale|escape:'html':'utf-8'}</td></tr>
    <tr><th>Window:</th>  <td>{$comment->window|escape:'html':'utf-8'}</td></tr>
    {* <tr><th>Context:</th> <td>{$comment->context|escape:'html':'utf-8'}</td></tr> *}
    <tr><th>Status:</th>  <td><a {$newRemarkFocus}>
{if strToLower($comment->status) == "closed"}
      {$comment->resolution|message:'resolution':'both'}
{elseif strToLower($comment->status) == "triaged" && isset( $tracurl ) && $comment->tracbug }
      {$comment->status|message:'status':'both'} - Trac bug <a href="{$tracurl|htmlentities}/ticket/{$comment->tracbug}">#{$comment->tracbug}</a>
{else}
      {$comment->status|message:'status':'both'}
{/if}
    </a></td></tr>
{if $comment->email}
    <tr><th>E-mail:</th>  <td><a {$newRemarkFocus}>{$comment->email}</a> (<em>Please reply by using the form below</em>)</td></tr>
{else}
    <tr><th>E-mail:</th>  <td><a {$newRemarkFocus}>Anonymous</a></td></tr>
{/if}
   </table>
   <div class="comment">
   {$comment->comment|escape:'html':'utf-8'|nl2br}
   </div>
