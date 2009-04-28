{* todo remove this later *}
{if $contents && !$subBarContents}{assign var=subBarContents value="$contents"}{/if}
{if $commentType && !$subBarType }{assign var=subBarType     value="$commentType}{/if}
  <div class="subBar {$subBarType}">
    {$subBarContents}
  </div>

