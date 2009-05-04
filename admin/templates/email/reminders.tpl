{* $comments may be cut down to i.e. the oldest 50 only, and should be sorted oldest-first!
 * also, there should be an array statusCounts with i.e. (New => 5, Confirmed => 20);
 * numbers are the total number of reports with this status) *}
Hello {$developer->login},

This is your weekly LikeBack comment reminder!{* You can turn this e-mail off in
your E-mail options. *}

A total of {$totalCount} comments is currently waiting for your attention:
{foreach from=$statusCounts key=k item=v}
 * {$v} comments {$k}
{/foreach}

{if $totalCount > $comments|@count}
Here is a list of the oldest {$comments|@count} comments waiting for your
attention (oldest first):
{else}
Here is a list of all comments waiting for your attention (oldest first):
{/if}
{section name=i loop=$comments}
{assign var=c value="`$comments[i]`"}
#{$c->id} at {$c->date}, for version {$c->fullVersion} ({$c->status} {$c->type}):
Link: <{$url}{$c->id}>
{$c->comment|wrapQuote:80:'  > '}

{/section}

Thank you,
{$project}'s developing servant, LikeBack
