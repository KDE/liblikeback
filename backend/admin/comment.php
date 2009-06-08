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

  if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
    header("Location: view.php?useSessionFilter=true");
    exit();
  }

  $title = "View Comment";
  include("header.php");

  $data = db_query("SELECT * FROM LikeBack WHERE id=? LIMIT 1", array( maybeStrip( $_REQUEST['id'] ) ) );
  $comment = db_fetch_object($data);

  if (!$comment) {
    header("Location: view.php?useSessionFilter=true");
    exit();
  }
  
  $smarty->assign( 'comment', $comment );
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

  if( isset( $_POST['mutation'] ) )
  {
    $mutation  = maybeStrip( $_POST['mutation']  );
    $newRemark = maybeStrip( $_POST['newRemark'] );
    $continue = 1;

    switch( $mutation )
    {
    case "none":
      // just update the remark, below
      if( strlen($newRemark) == 0 )
      {
        echo '<h2><font color="#ff0000">Error: Nothing to do.</font></h2>';
        $continue = 0;
      }
      break;
    case "reclose":
      // set status to Closed, resolution to recloseResolution
      $newResolution = maybeStrip( $_POST['recloseResolution'] );
    case "close":
      // set status to Closed, resolution to closeResolution
      $newStatus     = "closed";
      if( $mutation == "close" )
        $newResolution = maybeStrip( $_POST['closeResolution'] );
      if( strToLower( $comment->status ) == "closed" && $comment->resolution == $newResolution ) {
        echo '<h2><font color="#ff0000">Error: The comment already has the chosen status.</font></h2>';
        $continue = 0;
      }
      elseif( !in_array( $newResolution, validResolutions() ) && !in_array( messageForResolution( $newResolution ), validResolutions() ) )
      {
        echo '<h2><font color="#ff0000">Error: The resolution you chose is not valid.</font></h2>';
        $continue = 0;
      }
      break;
    case "restatus":
      // set status to restatusStatus
      $newStatus = maybeStrip( $_POST['restatusStatus'] );
    case "reopen":
      // set status to reopenStatus
      if( $mutation == "reopen" )
        $newStatus = maybeStrip( $_POST['reopenStatus'] );

      if( $comment->status == $newStatus ) {
        echo '<h2><font color="#ff0000">Error: The comment already has the chosen status.</font></h2>';
        $continue = 0;
      }
      elseif( !in_array( $newStatus, validStatuses() ) )
      {
        echo '<h2><font color="#ff0000">Error: The status you chose is not valid.</font></h2>';
        $continue = 0;
      }
      break;
    case "triage":
      // set status to Triaged, update tracbug
      $newStatus  = "triaged";
      $newTracBug = (int) $_POST['tracbug'];
      if( $newTracBug == 0 ) {
        echo '<h2><font color="#ff0000">Error: Invalid trac bug.</font></h2>';
        $continue = 0;
      }
      break;
    }

    $oldStatus = $comment->status;

    if( $continue && isset( $newStatus ) ) {
      $query = "";
      $placeholders = array();
      switch( $newStatus )
      {
      case "closed":
        $query = "UPDATE LikeBack SET status=?, resolution=? WHERE id=?";
        $placeholders = array( $newStatus, $newResolution, $comment->id );
        break;
      case "triaged":
        $query = "UPDATE LikeBack SET status=?, tracbug=? WHERE id=?";
        $placeholders = array( $newStatus, $newTracBug, $comment->id );
        break;
      default:
        $query = "UPDATE LikeBack SET status=? WHERE id=?";
        $placeholders = array( $newStatus, $comment->id );
      }

      if( db_query( $query, $placeholders ) )
      {
        // update the comment object
        if( $newStatus == "closed" ) {
          $comment->resolution = $newResolution;
        }
        $comment->status = $newStatus;

        $smarty->assign( 'comment', $comment );
      }
      else
      {
        echo '<h2><font color="#ff0000">Unable to update game state; newStatus='.$newStatus.'; query='.$query.'; error='.mysql_error().'</font></h2>';
        $continue = 0;
      }
    }


    if( !isset( $newStatus ) ) $newStatus = "";
    if( !isset( $newResolution ) ) $newResolution = "";
    if( !isset( $newTracBug ) ) $newTracBug = 0;
    $smarty->assign( 'newStatus', $newStatus );
    $smarty->assign( 'newResolution', $newResolution );
    $smarty->assign( 'tracbug', $newTracBug );
    $smarty->assign( 'remark', $newRemark );
    $tracurl = LIKEBACK_TRAC_URL . "/ticket/$newTracBug";
    $smarty->assign( 'tracurl', $tracurl );

    $userNotified = (!empty($comment->email) and isset($_POST['mailUser'])) and $_POST['mailUser'] == 'checked';

    // Send a mail to the original feedback poster
    if ( $continue && $userNotified ) {
      $from          = $likebackMail;
      $to            = $comment->email;
      $subject       = $likebackMailSubject . " - Answer to your feedback, #" . $comment->id;

      $message = $smarty->fetch( 'email/devremark.tpl' );
      $message = wordwrap($message, 80);

      $headers = "From: $from\r\n" .
        "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
        "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();
      mail($to, $subject, $message, $headers);
    }

    // Send a mail to all developers interested in this bug
    $sendMailTo = sendMailTo( $comment->type, $comment->locale );
    $sendMailTo = join( ", ", $sendMailTo );

    if ( $continue && !empty($sendMailTo) ) {
      $from    = $likebackMail;
      $to      = $sendMailTo;
      $subject = $likebackMailSubject . ' - New remark for '.messageForStatus($comment->status).
        ' '.messageForType($comment->type).' #'.$comment->id
        .' ('.$comment->version.' - '.$comment->locale.')';

      $url     = getLikeBackUrl() . "/admin/comment.php?id=" . $comment->id;

      $smarty->assign( 'url',     $url );
      $message = $smarty->fetch( 'email/devremark_todev.tpl' );
      $message = wordwrap($message, 80);

      $headers = "From: $from\r\n" .
        "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
        "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();

      mail($to, $subject, $message, $headers);
    }

    if( $continue )
    {
      $placeholders = "(?, ?, ?, ?, ";
      $values = array( get_iso_8601_date(time()), $developer->id, $comment->id, $newRemark );

      if( $userNotified )
        $placeholders .= "'1', ";
      else
        $placeholders .= "'0', ";

      if( $newStatus && $newStatus != $oldStatus ) // when resolution changes, newStatus==oldStatus=="Closed"; don't save it
      {
        $placeholders .= "?, ";
        $values[] = $newStatus;
      }
      else
        $placeholders .= "NULL, ";

      if( $newResolution )
      {
        $placeholders .= "?, ";
        $values[] = messageForResolution( $newResolution );
      }
      else
        $placeholders .= "NULL, ";

      if( $newTracBug )
      {
        $placeholders .= "?";
        $values[] = $newTracBug;
      }
      else
        $placeholders .= "NULL";

      $placeholders .= ")";

      if( !db_query("INSERT INTO LikeBackRemarks(dateTime, developer, commentId, "
        ."remark, userNotified, statusChangedTo, resolutionChangedTo, tracbugChangedTo) "
        ."VALUES " . $placeholders, $values ) )
      {
        echo '<h2><font color="#ff0000">Error: Failed to insert new remark: ' . mysql_error() . '</font></h2>';
      }
    }
  }

  $smarty->display( 'html/comment.tpl' );

  $remarks = db_fetchAll("SELECT   LikeBackRemarks.*, login " .
                   "FROM     LikeBackRemarks, LikeBackDevelopers " .
                   "WHERE    LikeBackDevelopers.id=developer AND commentId=? " .
                   "ORDER BY dateTime ASC", array($comment->id));

  $smarty->assign( 'remarks', $remarks );
  $smarty->assign( 'page', (isset($_REQUEST['page']) ? maybeStrip($_REQUEST['page']) : "") );
  $smarty->display( 'html/remarks.tpl' );

  $smarty->display( 'html/bottom.tpl' );
