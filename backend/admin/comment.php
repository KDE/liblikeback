<?php
/***************************************************************************
                          comment.php - Show and modify a Likeback comment
                             -------------------
    begin                : unknown
    imported into SVN    : Sat, 18 Apr 2009
    copyright            : (C) by BasKet Note Pads developers
                           (C) 2008 by the KMess team
    email                : likeback@kmess.org
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

// Pass an 'id' parameter in the GET query string to show a single comment.
// Pass via POST some fields named check_comment_<comment_id> to edit all those comments at once.

$title = "View Comment";
include("header.php");

if( empty( $_REQUEST['id'] ) && empty( $_POST ) )
{
  header( 'Location: view.php?useSessionFilter=true' );
  exit();
}

$commentIds = array();
if( ! empty( $_REQUEST['id'] ) )
{
  // Process a single comment
  $commentIds[] = $_REQUEST['id'];
}
else
{
  // Get the list of ids of the selected comments from the POST data
  $checkboxName = "check_comment_";
  $size = strlen( $checkboxName );
  foreach( $_POST as $key => $item )
  {
    // Filter the non-checkbox form fields
    if( strpos( $key, $checkboxName ) === false )
    {
      continue;
    }

    $id = (int)substr( $key, $size );
    if( ! isset( $commentIds[ $id ] ) )
      $commentIds[] = $id;
  }
}

if( empty( $commentIds ) )
{
  header( 'Location: view.php?useSessionFilter=true' );
  exit();
}

// True if there are more than one selected comments
$flag_MultipleComments = ( count( $commentIds ) > 1 );
// True if there is any change to record
$flag_HaveChanges      = ( ! empty( $_POST['mutation'] ) );


$error = '';
$query = '';
$placeholders = array();

$newStatus = '';
$newResolution = '';
$newTracBug = 0;
$newRemark = maybeStrip( $_POST['newRemark'] );


if( $flag_HaveChanges )
{
  $mutation = maybeStrip( $_POST['mutation'] );
  switch( $mutation )
  {
    case "reclose":
      // set status to Closed, resolution to recloseResolution
      $newResolution = maybeStrip( $_POST['recloseResolution'] );
      // Go on to "close"


    case "close":
      // set status to Closed, resolution to closeResolution
      $newStatus     = "closed";
      if( $mutation == "close" )
        $newResolution = maybeStrip( $_POST['closeResolution'] );

      if( empty( $newResolution ) )
      {
        $error = 'No resolution was selected.';
      }
      else
      if( ! in_array( $newResolution, validResolutions() )
      &&  ! in_array( messageForResolution( $newResolution ), validResolutions() ) )
      {
        $error = 'The resolution you chose, &quot;' . $newResolution . '&quot;, is not valid.';
      }
      break;


    case "restatus":
      // set status to restatusStatus
      $newStatus = maybeStrip( $_POST['restatusStatus'] );
      // Go on to "reopen"


    case "reopen":
      // set status to reopenStatus
      if( $mutation == "reopen" )
        $newStatus = maybeStrip( $_POST['reopenStatus'] );

      if( empty( $newStatus ) )
      {
        $error = 'No status was selected.';
      }
      else
      if( !in_array( $newStatus, validStatuses() ) )
      {
        $error = 'The status you chose, &quot;' . $newStatus . '&quot;, is not valid.';
      }

      break;


    case "triage":
      // set status to Triaged, update tracbug
      $newStatus  = "triaged";
      $newTracBug = (int) $_POST['tracbug'];
      if( $newTracBug == 0 ) {
        $error = 'Invalid trac bug.';
      }
      break;


    case "none":
    default:
      // just update the remark, below
      break;
  }

  if( ! $error && ! empty( $newStatus ) )
  {
    $idList = implode( ',', $commentIds );
    switch( $newStatus )
    {
    case "closed":
      $query = "UPDATE LikeBack SET status=?, resolution=? WHERE id IN({$idList})";
      $placeholders = array( $newStatus, $newResolution, $idList );
      break;
    case "triaged":
      $query = "UPDATE LikeBack SET status=?, tracbug=? WHERE id IN({$idList})";
      $placeholders = array( $newStatus, $newTracBug, $idList );
      break;
    default:
      $query = "UPDATE LikeBack SET status=? WHERE id IN({$idList})";
      $placeholders = array( $newStatus, $idList );
    }

    if( ! db_query( $query, $placeholders ) )
    {
      $error = 'Unable to update the comments.<br/>' .
               'The failing query was: &laquo;' . db_get_last_query() . '&raquo;<br/>' .
               'Database error: &quot;' . db_error() . '&quot;.';
    }

  }

  if( $error )
  {
    echo '<h2 class="error">Error: ' . $error . '</h2>';
  }
}



// Fetch the updated comments, then update their remarks, and show the changed comments

$data = db_query( 'SELECT LikeBack.*, COUNT(LikeBackRemarks.id) AS remarkCount, ' .
                    '(SELECT remark ' .
                    'FROM LikeBackRemarks ' .
                    'WHERE commentId = LikeBack.id ' .
                    'ORDER BY dateTime DESC '.
                    'LIMIT 1 ) AS lastRemark ' .
                  'FROM LikeBack ' .
                  'LEFT JOIN LikeBackRemarks ON LikeBack.id = commentId ' .
                  'WHERE LikeBack.id IN(' . implode( ",", $commentIds ) . ')' .
                  'GROUP BY LikeBack.id' );

$comments = array();
$dupes = array();
while( $comment = db_fetch_object( $data ) )
{
  $comments[] = $comment;

  // Don't send remarks if there isn't a new one
  if( $comment->lastRemark === $newRemark )
  {
	$dupes[] = $comment->id;
    continue;
  }

  if( $mutation == 'none' && empty( $newRemark ) )
  {
    continue;
  }

  // Show the updated number of remarks in the comment summary
  if( ! empty( $newRemark ) )
  {
	$comment->remarkCount++;
  }

  $smarty->assign( 'comment', $comment );
  $smarty->assign( 'newStatus', $newStatus );
  $smarty->assign( 'newResolution', $newResolution );
  $smarty->assign( 'tracbug', $newTracBug );
  $smarty->assign( 'remark', $newRemark );
  $smarty->assign( 'tracurl', LIKEBACK_TRAC_URL . '/ticket/' . $newTracBug );

  $userNotified = !  empty( $comment->email )
                  && isset( $_POST['mailUser'] ) && ( $_POST['mailUser'] == 'checked' );

  // Send a mail to the original feedback poster
  if( $userNotified )
  {
    $from    = $likebackMail;
    $to      = $comment->email;
    $subject = $likebackMailSubject . " - Answer to your feedback, #" . $comment->id;

    $message = $smarty->fetch( 'email/devremark.tpl' );
    $message = wordwrap( $message, 80 );

    $headers = "From: $from\r\n" .
                "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
                "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();
    likeback_mail( $to, $subject, $message, $headers );
  }

  // Send a mail to all developers interested in this bug
  $sendMailTo = sendMailTo( $comment->type, $comment->locale );
  $sendMailTo = join( ", ", $sendMailTo );

  if( ! empty( $sendMailTo ) )
  {
    $from    = $likebackMail;
    $to      = $sendMailTo;
    $subject = $likebackMailSubject .
                ' - New remark for ' . messageForStatus( $comment->status ) .
                ' ' . messageForType( $comment->type ) . ' #' . $comment->id;

    $url     = getLikeBackUrl() . "/admin/comment.php?id=" . $comment->id;
    $message = $smarty->fetch( 'email/devremark_todev.tpl' );
    $message = wordwrap( $message, 80 );

    $smarty->assign( 'url', $url );

    $headers = "From: $from\r\n" .
                "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
                "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();

    likeback_mail( $to, $subject, $message, $headers );
  }

  $placeholders = '(?, ?, ?, ?, ';
  $values = array( get_iso_8601_date(time()), $developer->id, $comment->id, $newRemark );

  if( $userNotified )
    $placeholders .= "'1', ";
  else
    $placeholders .= "'0', ";

  if( $newStatus )
  {
    $placeholders .= '?, ';
    $values[] = $newStatus;
  }
  else
    $placeholders .= 'NULL, ';

  if( $newResolution )
  {
    $placeholders .= '?, ';
    $values[] = messageForResolution( $newResolution );
  }
  else
    $placeholders .= 'NULL, ';

  if( $newTracBug )
  {
    $placeholders .= '?';
    $values[] = $newTracBug;
  }
  else
    $placeholders .= 'NULL';

  $placeholders .= ')';

  $query = 'INSERT INTO LikeBackRemarks( ' .
              'dateTime, developer, commentId, remark, ' .
              'userNotified, statusChangedTo, resolutionChangedTo, tracbugChangedTo'.
            ' ) VALUES ' . $placeholders;
  if( ! db_query( $query, $values ) )
  {
    echo '<h2 class="error">Error: Failed to insert a new remark.<br/>' .
          'The failing query was: &laquo;' . db_get_last_query() . '&raquo;<br/>' .
          'Database error: &quot;' . db_error() . '&quot;.</h2>';
  }
}











// Only display the list of changed comments
if( $flag_MultipleComments )
{
  $smarty->assign( 'comments',           $comments );
  $smarty->assign( 'skipped',            count( $dupes ) );
  $smarty->assign( 'page',               1 );
  $smarty->assign( 'showEditingOptions', false );
  $smarty->display( 'html/comments.tpl' );

  $smarty->display( 'html/bottom.tpl' );
  return;
}


// There's only one comment, show it
$comment = array_pop( $comments );


$smarty->display( 'html/lbheader.tpl' );

if( isset( $_REQUEST['page'] ) )
  $page = "&amp;page=" . htmlentities( maybeStrip( $_REQUEST['page'] ) );
else
  $page = "";


$subBarContents = '<a href="view.php?useSessionFilter=true' . $page . '#comment_' . $comment->id . '"><img src="icons/gohome.png" width="32" height="32" alt="Go home"/></a>'."\n";
$subBarContents .= ' &nbsp; &nbsp;' . iconForType( $comment->type ) . ' ' . messageForType( $comment->type ) . ' &nbsp; #<strong>' . $comment->id . '</strong> &nbsp; &nbsp; ' . $comment->date;
$smarty->assign( 'subBarType',     $comment->type );
$smarty->assign( 'subBarContents', $subBarContents );
$smarty->display( 'html/lbsubbar.tpl' );

$smarty->assign( 'comment', $comment );
$smarty->assign( 'skipped', count( $dupes ) );
$smarty->display( 'html/comment.tpl' );

$remarks = db_fetchAll("SELECT   LikeBackRemarks.*, login " .
                  "FROM     LikeBackRemarks, LikeBackDevelopers " .
                  "WHERE    LikeBackDevelopers.id=developer AND commentId=? " .
                  "ORDER BY dateTime ASC", array($comment->id));

$smarty->assign( 'remarks', $remarks );
$smarty->assign( 'page', (isset($_REQUEST['page']) ? maybeStrip($_REQUEST['page']) : "") );
$smarty->display( 'html/remarks.tpl' );

$smarty->display( 'html/bottom.tpl' );
