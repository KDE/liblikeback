Hi!

A developer responded on your LikeBack feedback message. This is the message you sent us:

{$comment->comment|wrapQuote}

A developer, {$developer->login},{if $newResolution} set the status to {$newStatus} ({$newResolution|message:'resolution'}){elseif strToLower( $newStatus ) == "triaged"} triaged the bug over to Trac #{$tracbug} (at <{$tracurl}>){elseif $newStatus} set the status to {$newStatus}{/if}{if $newStatus and $remark}, and{/if}{if $remark} wrote this message to you:

{$remark|wrapQuote}

{else}.{/if}
To reply to this message, you can simply use the Reply function of your e-mail client. Alternatively, you can use the original LikeBack form within the application.

Thank you for using LikeBack!
The {$project} developers
