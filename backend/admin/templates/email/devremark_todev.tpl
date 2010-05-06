Hi!

A developer responded on LikeBack issue #{$comment->id} (a {$comment->status} {$comment->type}):

{$comment->comment|wrapQuote}

Developer {$developer->login} {if $newResolution}set the status to {$newStatus} ({$newResolution|message:'resolution'}){elseif strToLower( $newStatus ) == "triaged"}triaged the bug over to Trac #{$tracbug} (at <{$tracurl}>){elseif $newStatus}set the status to {$newStatus}{/if}{if $newStatus and $remark}, and {/if}{if $remark}wrote this message{if $userNotified} to the user{/if}:

{$remark|wrapQuote}

{else}.{/if}

To respond or read more information about this comment, please use this URL:
  {$url}

Thank you,
{$project}'s developing servant, LikeBack
