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

  if( isset( $_POST['newRemark'] ) )
  {
    $information = array();
    $newRemark = maybeStrip( $_POST['newRemark'] );
    $continue = 1;

    // Gather a changed status or resolution
    $newStatus     = maybeStrip( $_POST['newStatus'] );
    $newResolution = 0;
    if( !$newStatus )
      unset ($newStatus);
    else if( substr( $newStatus, 0, 7 ) == 'closed_' )
    {
      // Check if it's a valid resolution and not set yet etc
      $newResolution = substr( $newStatus, 7 );
      $newStatus     = "closed";
      if( $comment->status == "closed" && $comment->resolution == $newResolution ) {
        unset ($newResolution);
        unset ($newStatus);
      }
      else if( !in_array( $newResolution, validResolutions() ) ) {
        // todo nicer warning
        echo "<h2>Warning: The resolution you chose is not in the list of valid resolutions, not changing the status.</h2>";
        unset ($newResolution);
        unset ($newStatus);
        $continue = 0;
      }
    }
    else
    {
      // Check if it's a valid status and not set yet etc
      if( $comment->status == $newStatus ) {
        unset ($newStatus);
      }
      else if( !in_array( $newStatus, validStatuses() ) ) {
        // todo nicer warning
        echo "<h2>Warning: The status you chose is not in the list of valid statuses, not changing the status.</h2>";
        unset ($newStatus);
        $continue = 0;
      }
    }

    // if it didn't change or was invalid, 
    if( $continue && isset( $newStatus ) ) {
      if( $newStatus == "closed" && !db_query( "UPDATE LikeBack SET status=?, resolution=? WHERE id=?", array( $newStatus, $newResolution, $comment->id ) ) )
      {
        // todo nicer warning
        echo "<h2>Warning: Couldn't set resolution for this bug to ".messageForResolution( $newResolution ).": ".mysql_error()."</h2>";
        $continue = 0;
      }
      else if( !db_query("UPDATE LikeBack SET status=? WHERE id=?", array( $newStatus, $comment->id ) ) )
      {
        // todo nicer warning
        echo "<h2>Warning: Couldn't set status for this bug to ".messageForStatus( $newStatus ).": ".mysql_error()."</h2>";
        $continue = 0;
      }
      else
      {
        // update the comment object
        if( $newStatus == "closed" ) {
          $comment->resolution = $newResolution;
        }
        $comment->status = $newStatus;

        $smarty->assign( 'comment', $comment );
      }
    }

    if( $continue && empty( $newRemark ) && !isset($newStatus) && !isset($newResolution) )
    {
      echo "<h2>Warning: Nothing to change, skipping remark!</h2>";
      $continue = 0;
    }
    
    if( isset( $newStatus ) )
      $smarty->assign( 'newStatus', $newStatus );
    else
      $smarty->assign( 'newStatus', "" );
    if( isset( $newResolution ) )
      $smarty->assign( 'newResolution', $newResolution );
    else
      $smarty->assign( 'newResolution', "" );
    $smarty->assign( 'remark', $newRemark );

    $userNotified = (!empty($comment->email) and isset($_POST['mailUser'])) and $_POST['mailUser'] == 'checked';

    // Send a mail to the original feedback poster
    if ( $continue && $userNotified ) {
      $from          = $likebackMail;
      $to            = $comment->email;
      $subject       = $likebackMailSubject . " - Answer to your feedback";

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

      if( isset( $newStatus ) )
      {
        $placeholders .= "?, ";
        $values[] = $newStatus;
      }
      else
        $placeholders .= "NULL, ";

      if( isset( $newResolution ) )
      {
        $placeholders .= "?";
        $values[] = messageForResolution( $newResolution );
      }
      else
        $placeholders .= "NULL";

      $placeholders .= ")";

      db_query("INSERT INTO LikeBackRemarks(dateTime, developer, commentId, "
        ."remark, userNotified, statusChangedTo, resolutionChangedTo) "
        ."VALUES " . $placeholders, $values );
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
