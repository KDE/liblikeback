Hi!

A developer responded on LikeBack issue #{$comment->id} (a {$comment->status} {$comment->type}):

{$comment->comment|wrapQuote}

{$developer->login} {if $newResolution}set the status to {$newStatus} ({$newResolution|message:'resolution'}) and {elseif $newStatus}set the status to {$newStatus} and {/if}wrote this message{if $userNotified} to the user{/if}:

{$remark|wrapQuote}

To respond or read more information about this comment, please use this URL:
  {$url}

Thank you,
{$project}'s developing servant, LikeBack
