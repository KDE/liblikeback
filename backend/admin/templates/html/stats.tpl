  {* include file='html/header.tpl' *}
  {include file='html/lbheader.tpl'}
  {include file='html/lbsubbar.tpl'}

  <div class="content">
    <h1>This installation...</h1>
    <p>...is running <strong>LikeBack {$lbversion}</strong>. <strong>{$numDevelopers}</strong> developers are known, <strong>{$numDevelopersWithEmail}</strong> also have their e-mail address set.</p>
    <h2>Out of {$totalCount} comments on Likeback...</h2>
    <p><strong>Types:</strong></p>
{section name=i loop=$typeCounts}
    <strong>{$typeCounts[i]->type|message:'type':'both'}</strong>: {$typeCounts[i]->count} comments<br/><br/>
{sectionelse}
    <strong>No types could be found!</strong>
{/section}
    <p><strong>Statuses:</strong><p>
{section name=j loop=$statusCounts}
    <strong>{$statusCounts[j]->status|message:'status':'both'}</strong>: {$statusCounts[j]->count} comments<br/><br/>
{sectionelse}
    <strong>No statuses could be found!</strong>
{/section}

  </div>

  {include file='html/bottom.tpl'}
