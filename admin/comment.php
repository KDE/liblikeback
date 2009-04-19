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

?>
  <div id="statusMenu">
   <strong>Mark As:</strong>
   <a id="markAsNew"       href="#"><img src="icons/new.png"       width="16" height="16" alt="" />New</a>
   <a id="markAsConfirmed" href="#"><img src="icons/confirmed.png" width="16" height="16" alt="" />Confirmed</a>
   <a id="markAsProgress"  href="#"><img src="icons/progress.png"  width="16" height="16" alt="" />In progress</a>
   <a id="markAsSolved"    href="#"><img src="icons/solved.png"    width="16" height="16" alt="" />Solved</a>
   <a id="markAsInvalid"   href="#"><img src="icons/invalid.png"   width="16" height="16" alt="" />Invalid</a>
<!--
   <strong>Duplicate Of:</strong>
   <a id="markAsDuplicate" href="#"><img src="icons/duplicate.png" width="16" height="16" alt="" />Choose...</a>
   <div style="vertical-align: middle; padding: 2px"><img src="icons/id.png"     width="16" height="16" alt="" style="vertical-align: middle; padding: 1px 1px 3px 3px"/><input type="text" size="3"> <input type="submit" value="Ok"></div>
-->
  </div>

  <p class="header">
  </p>

  <div class="subBar <?php echo $comment->type; ?>">
   <a href="view.php?useSessionFilter=true&amp;page=<?php echo $_GET['page']; ?>#comment_<?php echo $id ?>"><img src="icons/gohome.png" width="32" height="32" alt=""></a> &nbsp; &nbsp;
   <?=iconForType($comment->type)?> <?=messageForType( $comment->type )?> &nbsp; #<strong><?=$comment->id?></strong> &nbsp; &nbsp; <?=$comment->date?>
  </div>
<?php
  $email = htmlentities($comment->email, ENT_QUOTES, "UTF-8");

  if( !empty( $_POST['newRemark'] ) )
  {
    // Send a mail to the original feedback poster:
    if (!empty($email) AND $_POST['mailUser'] == '1' ) {
      $from          = $likebackMail;
      $to            = $email;
      $subject       = $likebackMailSubject . " - Answer to your feedback";

      $rawComment    = str_replace( "\r", "", $comment->comment );
      // Prepend every line with >
      $rawComment    = "> " . str_replace( "\n", "\n> ", $rawComment );

      $developerName = $developer->login;

      $remark        = str_replace( "\r", "", $_POST['newRemark'] );
      // Prepend every line with >
      $remark        = "> " . str_replace( "\n", "\n> ", $remark );

      $smarty = getSmartyObject();
      $smarty->assign( 'comment', $rawComment );
      $smarty->assign( 'remark', $remark );

      $message = $smarty->fetch( 'email/devremark.tpl' );
      $message = wordwrap($message, 70);

      $headers = "From: $from\r\n" .
                 "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();
      mail($to, $subject, $message, $headers);

      // Add a warning on the remark, to notify the developer that the message was also sent to the user
      $_POST['newRemark'] = "This remark has also been sent to the user:\r\n\r\n" . $_POST['newRemark'];
    }

    db_query("INSERT INTO LikeBackRemarks(dateTime, developer, commentId, remark) VALUES(?, ?, ?, ?);",
              array( get_iso_8601_date(time()), $developer->id, $id, $_POST['newRemark'] ) );
  }
?>

<?php
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

  $comment = htmlentities( stripslashes( $comment->comment), ENT_QUOTES, "UTF-8" );

?>
  <div class="content">
   <table class="summary">
    <tr><th>Version:</th> <td><?php echo htmlentities($comment->version, ENT_QUOTES, "UTF-8"); ?></td></tr>
    <tr><th>Locale:</th>  <td><?php echo htmlentities($comment->locale,  ENT_QUOTES, "UTF-8"); ?></td></tr>
    <tr><th>Window:</th>  <td><?php echo htmlentities($comment->window,  ENT_QUOTES, "UTF-8"); ?></td></tr>
    <tr><th>Context:</th> <td><?php echo htmlentities($comment->context, ENT_QUOTES, "UTF-8"); ?></td></tr>
    <tr><th>Status:</th>  <td><?php echo $currentStatus; ?></td></tr>
    <tr><th>E-Mail:</th>  <td><?php echo $email; ?></td></tr>
   </table>
   <div class="comment">
   <?=$comment?>
   </div>


<?php
  $data = db_query("SELECT   LikeBackRemarks.*, login " .
                   "FROM     LikeBackRemarks, LikeBackDevelopers " .
                   "WHERE    LikeBackDevelopers.id=developer AND commentId=? " .
                   "ORDER BY dateTime DESC", array($id));

  $numRemarks = db_count_results($data);
?>

   <h2><img src="icons/remarks.png" width="16" height="16" alt=""> <?php echo $numRemarks; ?> remarks</h2>
<?php

  while ($line = db_fetch_object($data)) {
    echo "   <div class=\"remark $comment->type\">\n";
    echo "    <h3>On <strong>" . $line->dateTime . "</strong>, by <strong>" . htmlentities($line->login, ENT_QUOTES, "UTF-8") . "</strong></h3>\n";
    echo "    <p>" . htmlentities(stripslashes($line->remark), ENT_QUOTES, "UTF-8") . "</h3>\n";
    echo "   </div>\n";
  }
?>

   <div class="remark <?php echo $comment->type; ?>">
    <form action="comment.php?id=<?php echo $id; ?>" method="post">
    <h4>New remark:</h4>
     <textarea name="newRemark" id="newRemark" style="width: 50%; height: 100px; vertical-align: middle"></textarea>
     <?php echo $mailUserCheckBox; ?>
     <input type="submit" value="Add New Remark" style="vertical-align: middle">
    </form>
   </div>
   <script type="text/javascript">
     document.getElementById("newRemark").focus();
   </script>

  </div>
 </body>
</html>
