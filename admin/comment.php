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

  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view.php?useSessionFilter=true");
    exit();
  }

  $title = "View Comment";
  include("header.php");

  $id = $_GET['id'];
  $data = db_query("SELECT * FROM LikeBack WHERE id=? LIMIT 1", array($id) );
  $comment = db_fetch_object($data);

  if (!$comment) {
    header("Location: view.php?useSessionFilter=true");
    exit();
  }

  echo statusMenu();
  echo lbHeader();

  if( isset( $_GET['page'] ) )
    $page = "&amp;page=" . htmlentities( stripslashes( $_GET['page'] ) );
  else
    $page = "";

  $subBarContents = '<a href="view.php?useSessionFilter=true' . $page . '#comment_' . $comment->id . '"><img src="icons/gohome.png" width="32" height="32" alt="Go home"/></a>'."\n";
  $subBarContents .= ' &nbsp; &nbsp;' . iconForType( $comment->type ) . ' ' . messageForType( $comment->type ) . ' &nbsp; #<strong>' . $comment->id . '</strong> &nbsp; &nbsp; ' . $comment->date;
  echo subBar( $comment->type, $subBarContents );

  $email = htmlentities($comment->email, ENT_QUOTES, "UTF-8");

  if( !empty( $_POST['newRemark'] ) )
  {
    // Send a mail to the original feedback poster:
    if (!empty($email) and isset($_POST['mailUser']) and $_POST['mailUser'] == '1' ) {
      $from          = $likebackMail;
      $to            = $email;
      $subject       = $likebackMailSubject . " - Answer to your feedback";

      $rawComment    = str_replace( "\r", "", $comment->comment );
      // Prepend every line with >
      $rawComment    = "> " . str_replace( "\n", "\n> ", $rawComment );
      $rawComment    = wordwrap( $rawComment, 60, "\n> " );

      $remark        = str_replace( "\r", "", $_POST['newRemark'] );
      // Prepend every line with >
      $remark        = "> " . str_replace( "\n", "\n> ", $remark );
      $remark        = wordwrap( $remark, 60, "\n> " );

      $smarty = getSmartyObject( $developer );
      $smarty->assign( 'comment', $rawComment );
      $smarty->assign( 'remark', $remark );

      $message = $smarty->fetch( 'email/devremark.tpl' );
      $message = wordwrap($message, 70);

      $headers = "From: $from\r\n" .
        "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
        "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();
      mail($to, $subject, $message, $headers);

      // Add a warning on the remark, to notify the developer that the message was also sent to the user
      $_POST['newRemark'] = "This remark has also been sent to the user:\r\n\r\n" . $_POST['newRemark'];
    }

    db_query("INSERT INTO LikeBackRemarks(dateTime, developer, commentId, remark) VALUES(?, ?, ?, ?);",
              array( get_iso_8601_date(time()), $developer->id, $id, $_POST['newRemark'] ) );
  }

  if (!empty($email))
  {
    $email .= " <em>(please reply by using the form below)</em>";
    $disabled = 'class="mailRemark"';
  }
  else
  {
    $email = "Anonymous";
    $disabled = 'disabled class="mailRemarkOff"';
  }
  $mailUserCheckBox = "<br><label $disabled name='mailUserBox'>" .
                      "<input $disabled type='checkbox' name='mailUser' value='1'>" .
                      "Also send this comment to the author</label><br>";

  $message = messageForStatus( $comment->status );
  $icon    = iconForStatus(    $comment->status, $id );
  $currentStatus = '<a href="#" onclick="return showStatusMenu(event)">' . $icon . '</a> ' . $message;

  if( empty( $comment->context ) )
    $comment->context = "None";

  $htmlComment = htmlentities( stripslashes( $comment->comment), ENT_QUOTES, "UTF-8" );
  $htmlComment = str_replace( "\r", "", $htmlComment );
  $htmlComment = str_replace( "\n", "<br/>", $htmlComment );

  $smarty = getSmartyObject( $developer );
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
    $remark = htmlentities( stripslashes( $line->remark ), ENT_QUOTES, "UTF-8" );
    $remark = str_replace( "\r", "", $remark );
    $remark = str_replace( "\n", "<br/>", $remark );
    $line->remark = $remark;

    array_push( $remarks, $line );
  }

  $smarty->assign( 'commenttype', $comment->type );
  $smarty->assign( 'commentid', $id );
  $smarty->assign( 'remarks', $remarks );
  $smarty->assign( 'checkBoxHtml', $mailUserCheckBox );
  $smarty->assign( 'page', (isset($_GET['page']) ? $_GET['page'] : "") );
  $smarty->display( 'html/remarks.tpl' );
?>
   <script type="text/javascript">
     document.getElementById("newRemark").focus();
   </script>
<?
  $smarty->display( 'html/bottom.tpl' );
