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

  $id = $_REQUEST['id'];
  $data = db_query("SELECT * FROM LikeBack WHERE id=? LIMIT 1", array($id) );
  $comment = db_fetch_object($data);

  if (!$comment) {
    header("Location: view.php?useSessionFilter=true");
    exit();
  }

  echo statusMenu();
  echo lbHeader();

  if( isset( $_REQUEST['page'] ) )
    $page = "&amp;page=" . htmlentities( stripslashes( $_REQUEST['page'] ) );
  else
    $page = "";

  $subBarContents = '<a href="view.php?useSessionFilter=true' . $page . '#comment_' . $comment->id . '"><img src="icons/gohome.png" width="32" height="32" alt="Go home"/></a>'."\n";
  $subBarContents .= ' &nbsp; &nbsp;' . iconForType( $comment->type ) . ' ' . messageForType( $comment->type ) . ' &nbsp; #<strong>' . $comment->id . '</strong> &nbsp; &nbsp; ' . $comment->date;
  echo subBar( $comment->type, $subBarContents );

  $email = htmlentities($comment->email, ENT_QUOTES, "UTF-8");

  if( isset( $_POST['newRemark'] ) )
  {
    $information = "";
    $newRemark = maybeStrip( $_POST['newRemark'] );

    // Gather a changed status
    if( isset( $_POST['newStatus'] ) && $_POST['newStatus'] != $comment->status ) {
      $newStatus = $_POST['newStatus'];
      if( !in_array( $newStatus, validStatuses() ) ) {
        // todo nicer warning
        echo "<h2>Warning: the status you chose is not in the list of valid statuses, not changing the status.";
      }
      else
      {
        if( !db_query("UPDATE LikeBack SET status=? WHERE id=?", array( $newStatus, $comment->id ) ) ) {
          // todo nicer warning
          echo "<h2>Warning: Couldn't set status for this bug to $newStatus: ".mysql_error()."</h2>";
        }
        else
        {
          $information = "The developer set the status for this comment to $newStatus.\r\n";
          $comment->status = $newStatus;
        }
      }
    }

    // Send a mail to the original feedback poster
    if (!empty($email) and isset($_POST['mailUser']) and $_POST['mailUser'] == '1' ) {
      $from          = $likebackMail;
      $to            = $email;
      $subject       = $likebackMailSubject . " - Answer to your feedback";

      $smarty = getSmartyObject();
      $smarty->assign( 'comment', $comment );
      $smarty->assign( 'remark',  $newRemark );

      $message = $smarty->fetch( 'email/devremark.tpl' );
      $message = wordwrap($message, 80);

      $headers = "From: $from\r\n" .
        "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
        "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();
      mail($to, $subject, $message, $headers);

      // Add a warning on the remark, to notify the developer that the message was also sent to the user
      // TODO: add this as a flag in the database
      $information = "This remark has also been sent to the user.\r\n";
    }

    if( !empty( $information ) )
      $newRemark = $information . "\r\n" . $newRemark;

    // Send a mail to all developers interested in this bug
    $sendMailTo = sendMailTo( $comment->type, $comment->locale );
    $sendMailTo = join( ", ", $sendMailTo );

    if (!empty($sendMailTo)) {
      $from    = $likebackMail;
      $to      = $sendMailTo;
      $subject = $likebackMailSubject . ' - New remark for '.messageForStatus($comment->status).
        ' '.messageForType($comment->type).' #'.$comment->id
        .' ('.$comment->version.' - '.$comment->locale.')';

      $url     = getLikeBackUrl() . "/admin/comment.php?id=" . $id;

      $smarty  = getSmartyObject();
      $smarty->assign( 'remark',  $newRemark );
      $smarty->assign( 'comment', $comment );
      $smarty->assign( 'url',     $url );

      $message = $smarty->fetch( 'email/devremark_todev.tpl' );
      $message = wordwrap($message, 80);

      $headers = "From: $from\r\n" .
        "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
        "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();

      mail($to, $subject, $message, $headers);
    }

    db_query("INSERT INTO LikeBackRemarks(dateTime, developer, commentId, remark) VALUES(?, ?, ?, ?);",
              array( get_iso_8601_date(time()), $developer->id, $id, $newRemark ) );
  }

  if (!empty($email))
  {
    $email .= " <em>(please reply by using the form below)</em>";
  }
  else
  {
    $email = "Anonymous";
  }

  $message = messageForStatus( $comment->status );
  $icon    = iconForStatus(    $comment->status, $id );
  $currentStatus = '<a href="#" onclick="return showStatusMenu(event)">' . $icon . '</a> ' . $message;

  if( empty( $comment->context ) )
    $comment->context = "None";

  $htmlComment = htmlentities( stripslashes( $comment->comment), ENT_QUOTES, "UTF-8" );
  $htmlComment = str_replace( "\r", "", $htmlComment );
  $htmlComment = str_replace( "\n", "<br/>", $htmlComment );

  $smarty = getSmartyObject();
  $smarty->assign( 'version', htmlentities($comment->version, ENT_QUOTES, "UTF-8" ) );
  $smarty->assign( 'locale',  htmlentities($comment->locale,  ENT_QUOTES, "UTF-8" ) );
  $smarty->assign( 'window',  htmlentities($comment->window,  ENT_QUOTES, "UTF-8" ) );
  $smarty->assign( 'context', htmlentities($comment->context, ENT_QUOTES, "UTF-8" ) );
  $smarty->assign( 'status',  $currentStatus );
  $smarty->assign( 'email',   $email );
  $smarty->assign( 'comment', $htmlComment );
  $smarty->display( 'html/comment.tpl' );

  $data = db_query("SELECT   LikeBackRemarks.*, login " .
                   "FROM     LikeBackRemarks, LikeBackDevelopers " .
                   "WHERE    LikeBackDevelopers.id=developer AND commentId=? " .
                   "ORDER BY dateTime ASC", array($id));

  $remarks = array();

  while ($line = db_fetch_object($data)) {
    //$remark = htmlentities( stripslashes( $line->remark ), ENT_QUOTES, "UTF-8" );
    //$remark = str_replace( "\r", "", $remark );
    //$remark = str_replace( "\n", "<br/>", $remark );
    //$line->remark = $remark;

    array_push( $remarks, $line );
  }

  $smarty->assign( 'comment', $comment );
  $smarty->assign( 'remarks', $remarks );
  $smarty->assign( 'page', (isset($_REQUEST['page']) ? $_REQUEST['page'] : "") );
  $smarty->assign( 'statuses', validStatuses() );
  $smarty->display( 'html/remarks.tpl' );
  $smarty->display( 'html/bottom.tpl' );
