<?php
/***************************************************************************
                         send.php - External interface for Likeback requests
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

require_once("db.php");
require_once("functions.inc.php");
$noadmin = 1;
require_once("admin/functions.php");

  header("Content-Type: text/xml");
  echo '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";

  define( 'RESULT_SUCCESS',             '000' );
  define( 'ERROR_MISSING_ARGUMENTS',    '100' );
  define( 'ERROR_MISSING_PROTOCOL',     '101' );
  define( 'ERROR_UNSUPPORTED_PROTOCOL', '102' );
  define( 'ERROR_UNKNOWN_REPORTTYPE',   '103' );

  // check protocol first
  if( !isset( $_POST['protocol'] ) )
    die('<LikeBackReply><Result type="error" code="' . ERROR_MISSING_PROTOCOL . '" message="Missing \'protocol\' parameter."/></LikeBackReply>');
  else if( $_POST['protocol'] != "1.0" )
    die('<LikeBackReply><Result type="error" code="' . ERROR_UNSUPPORTED_PROTOCOL . '" message="Incorrect protocol number for this server."/></LikeBackReply>');

  if ( !isset($_POST['version'])  ||
       !isset($_POST['type'])     ||
       !isset($_POST['comment'])     )
    die('<LikeBackReply><Result type="error" code="' . ERROR_MISSING_ARGUMENTS . '" message="Missing required parameters. Please give at least version, type and comment."/></LikeBackReply>');

  $quotes = get_magic_quotes_gpc();
  foreach( array('protocol', 'version', 'locale', 'window', 'context', 'type', 'comment', 'email') as $var )
  {
    if( ! isset( $_POST[$var] ) )
    {}
    else if( $quotes )
      $$var = stripslashes( $_POST[$var] );
    else
      $$var = $_POST[$var];
  }
  $locale  = isset ($locale ) ? $locale  : "";
  $window  = isset ($window ) ? $window  : "";
  $context = isset ($context) ? $context : "";
  $email   = isset ($email  ) ? $email   : "";

  $comment = utf8_decode( $comment );

  // TODO: Check version (newest?), window and context?

  // Hack up version into compact and full version
  // makes 2.0alpha2-svn (4306 >= 20090205) into:
  // version:     2.0alpha2-svn
  // fullVersion: 2.0alpha2-svn (4306 >= 20090205)
  $matches = array();
  $fullVersion = trim( $version );
  if( strPos( $fullVersion, "(" ) === FALSE )
    $version   = $fullVersion;
  else
    $version   = trim( substr( $fullVersion, 0, strpos( $fullVersion, "(" ) ) );

  if ( ! in_array( $type, validTypes() ) ) {
    $options = join ( ", ", validTypes() );
    die('<LikeBackReply><Result type="error" code="' . ERROR_UNKNOWN_REPORTTYPE . '" message="Invalid type, must be one of '.$options.'/></LikeBackReply>');
  }

  db_query("INSERT INTO LikeBack(date, fullVersion, version, locale, window, context, type, status, comment, email) " .
                         "VALUES(?,    ?,           ?,       ?,      ?,      ?,       ?,    ?,      ?,       ?);",
                  array( get_iso_8601_date(time()), $fullVersion, $version, $locale, $window, $context,
                    $type, 'New', $comment, $email) );
  $id = db_insert_id();

  $sendMailTo = sendMailTo( $type, $locale );
  $sendMailTo = join( ", ", $sendMailTo );

  if (!empty($sendMailTo)) {
    $from    = empty($email) ? $likebackMail : $email;
    // Don't send replies to the original poster
    $sender  = $likebackMail;
    $replyTo = $likebackMail; // (empty($email) ? $sendMailTo : $email);
    $to      = $sendMailTo;
    $subject = "[LikeBack: $type] #$id ($version - $locale)";

    $url     = getLikeBackUrl() . "/admin/comment.php?id=" . $id;

    $smarty  = getSmartyObject( true );
    $smarty->template_dir = 'admin/templates';
    $smarty->compile_dir  = '/tmp';

    $smarty->assign( 'version',   $version );
    $smarty->assign( 'fullVersion', $fullVersion );
    $smarty->assign( 'locale',    $locale );
    $smarty->assign( 'window',    $window );
    $smarty->assign( 'context',   $context );
    $smarty->assign( 'type',      $type );
    $smarty->assign( 'comment',   $comment );
    $smarty->assign( 'url',       $url );

    $message = $smarty->fetch( 'email/comment.tpl' );
    $message = wordwrap($message, 80);

    $headers = "From: $from\r\n" .
      "Sender: $sender\r\n" .
      "Reply-To: $replyTo\r\n" .
      "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
      "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();

//echo "***** To: $to<br>\r\n***** Subject: $subject<br>\r\n***** Message: $message<br>\r\n***** Headers: $headers";
    mail($to, $subject, $message, $headers);
  }

?>
<LikeBackReply>
  <Result type="ok" code="<?=RESULT_SUCCESS?>" message="Comment registered. We will take it in account to design the next version of this application." />
</LikeBackReply>
