Hi!

The automatic LikeBack Trac integration service closed LikeBack feedback message #{$comment->id} (a {$comment->status} {$comment->type}). This is the original comment:

{$comment->comment|wrapQuote}

This comment was linked to Trac bug #{$ticketid}, found at <{$tracurl}>. The integration service closed the comment and set the resolution to {$newResolution|message:'resolution'}, saying:

{$remark|wrapQuote}

To respond or read more information about this comment, please use this URL:
  {$url}

Thank you,
{$project}'s developing servant, LikeBack
