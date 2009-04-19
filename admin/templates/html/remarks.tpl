
   <h2><img src="icons/remarks.png" width="16" height="16" alt=""> {$remarks|@count} remarks</h2>

{section name=i loop=$remarks}
   <div class="remark {$commenttype}">
    <h3>On <strong>{$remarks[i]->dateTime}</strong>, by <strong>{$remarks[i]->login|escape:'html':'utf-8'}</strong></h3>
    <p>{$remarks[i]->remark}</p>
   </div>
{/section}

{include file='html/newremark.tpl'}
