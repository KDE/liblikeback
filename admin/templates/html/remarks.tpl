
   <h2><img src="icons/remarks.png" width="16" height="16" alt="remarks"> {$remarks|@count} remarks</h2>

{section name=i loop=$remarks}
   <div class="remark {$comment->type}">
    <h3>On <strong>{$remarks[i]->dateTime|escape:'html':'utf-8'}</strong>, by <strong>{$remarks[i]->login|escape:'html':'utf-8'}</strong></h3>
    <p>{$remarks[i]->remark|escape:'html':'utf-8'|nl2br}</p>
   </div>
{/section}

{include file='html/newremark.tpl'}
