
   <h2><img src="icons/remarks.png" width="16" height="16" alt="remarks"> {$remarks|@count} remarks</h2>

{section name=i loop=$remarks}
   <div class="remark {$comment->type}">
    <h3>On <strong>{$remarks[i]->dateTime|escape:'html':'utf-8'}</strong>, by <strong>{$remarks[i]->login|escape:'html':'utf-8'}</strong></h3>
{if $remarks[i]->userNotified or $remarks[i]->statusChangedTo or $remarks[i]->resolutionChangedTo or $remarks[i]->tracbugChangedTo}
    <p class="remarkDetails">
{if $remarks[i]->userNotified}
      This remark was also sent to the user.<br/>
{/if}
{if $remarks[i]->statusChangedTo}
      Status -&gt; {$remarks[i]->statusChangedTo|message:'status':'both'}<br/>
{/if}
{if $remarks[i]->resolutionChangedTo}
      Resolution -&gt; {$remarks[i]->resolutionChangedTo}<br/>
{/if}
{if $remarks[i]->tracbugChangedTo}
      Trac bug -&gt; <a href="{$tracurl}/ticket/{$remarks[i]->tracbugChangedTo}">#{$remarks[i]->tracbugChangedTo}</a><br/>
{/if}
    </p>
{/if}

{if $remarks[i]->remark}
    <p>{$remarks[i]->remark|escape:'html':'utf-8'|nl2br}</p>
{/if}
   </div>
{/section}

{include file='html/newremark.tpl'}
