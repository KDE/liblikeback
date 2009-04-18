<?php
/***************************************************************************
                          comment.php - description
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
  $data = db_query("SELECT * FROM LikeBack WHERE id=$id LIMIT 1");
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
   <strong><?php echo iconForType($comment->type) . " #$comment->id"; ?></strong> &nbsp; &nbsp; <?php echo $comment->date . "\n"; ?>
  </div>
<?php
  $email = htmlentities($comment->email, ENT_QUOTES, "UTF-8");

  if (isset($_POST['newRemark'])) {
    // Send a mail to the original feedback poster:
    if (!empty($email) AND $_POST['mailUser'] == '1' ) {
      $from    = $likebackMail;
      $to      = $email;
      $subject = $likebackMailSubject . " - Answer to your feedback";
      $message = "Hi!\r\n" .
                 "In response to your feedback,\r\n" .
                 "\r\n" .
                 preg_replace( "#\r?\n|<br */?>#i", "\r\n", $comment->comment ) .
                 "\r\n" .
                 "a developer wrote the following answer:\r\n" .
                 "\r\n" .
                 preg_replace( "#\r?\n|<br */?>#i", "\r\n", $_POST['newRemark'] ) .
                 "\r\n" .
                 "\r\n" .
                 "If you need to further reply to this mail, please " .
                 "use the original LikeBack form within the application. " .
                 "Thank you for using LikeBack!\r\n";
      $message = wordwrap($message, 70);
      $headers = "From: $from\r\n" .
                 "X-Mailer: PHP/" . phpversion();

  //echo "***** To: $to<br>\r\n***** Subject: $subject<br>\r\n***** Message: $message<br>\r\n***** Headers: $headers";
      mail($to, $subject, $message, $headers);

      // Add a warning on the remark, to notify the developer that the message was also sent to the user
      $_POST['newRemark'] = "This remark has also been sent to the user:\r\n\r\n" . $_POST['newRemark'];
    }

    db_query("INSERT INTO LikeBackRemarks(dateTime, developer, commentId, remark) " .
             "VALUES('" . get_iso_8601_date(time()) . "', " .
                    "'$developer->id', " .
                    "'$id', " .
                    "'" . addslashes($_POST['newRemark']) . "')");
  }
?>

<?php
  if (!empty($email))
  {
    $email = "<a href=\"mailto:$email?subject=Your%20$comment->type%20Comment\">$email</a>";
    $disabled = " class='mailRemark'";
  }
  else
  {
    $email = "Anonymous";
    $disabled = "disabled class=\"mailRemarkOff\" ";
  }
    $mailUserCheckBox = "<br><label $disabled name='mailUserBox'>" .
                        "<input $disabled type='checkbox' name='mailUser' value='1'>" .
                        "Also send this comment to the author</label><br>";


    if ($comment->status == "New")
      $currentStatus = "<img src=\"icons/new.png\"       id=\"status_comment_$id\" width=\"16\" height=\"16\" title=\"New\" />";
    else if ($comment->status == "Confirmed")
      $currentStatus = "<img src=\"icons/confirmed.png\" id=\"status_comment_$id\" width=\"16\" height=\"16\" title=\"Confirmed\" />";
    else if ($comment->status == "Progress")
      $currentStatus = "<img src=\"icons/progress.png\"  id=\"status_comment_$id\" width=\"16\" height=\"16\" title=\"In progress\" />";
    else if ($comment->status == "Solved")
      $currentStatus = "<img src=\"icons/solved.png\"    id=\"status_comment_$id\" width=\"16\" height=\"16\" title=\"Solved\" />";
    else
      $currentStatus = "<img src=\"icons/invalid.png\"   id=\"status_comment_$id\" width=\"16\" height=\"16\" title=\"Invalid\" />";
    $currentStatus = "<a href=\"#\" onclick=\"return showStatusMenu(event)\">$currentStatus</a>";

    if( empty( $comment->context ) )
      $comment->context = "None";


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
   <?php echo htmlentities(stripslashes($comment->comment), ENT_QUOTES, "UTF-8"); ?>
   </div>


<?php
  $data = db_query("SELECT   LikeBackRemarks.*, login " .
                   "FROM     LikeBackRemarks, LikeBackDevelopers " .
                   "WHERE    LikeBackDevelopers.id=developer AND commentId=$id " .
                   "ORDER BY dateTime DESC");

  $numRemarks = db_count_results($data);
?>

   <h2><img src="icons/remarks.png" width="16" height="16" alt=""> <?php echo $numRemarks; ?> remarks</h2>
<?php

  while ($line = db_fetch_object($data)) {
    echo "   <div class=\"remark $comment->type\">\n";
    echo "    <h3>On <strong>$line->dateTime</strong>, by <strong>" . htmlentities($line->login, ENT_QUOTES, "UTF-8") . "</strong></h3>\n";
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
