
{if $skipped > 0}
    <h2 class="error">{if $skipped == 1}1 comment{else}{$skipped} comments{/if} were already up to date.</h2>
{/if}

<h2>Updated comments:</h2>

{include file='html/commenttable.tpl'}

<div class="content">
  <a href="view.php">Go back to the comments list</a>
</div>
