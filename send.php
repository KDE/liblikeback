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

  require_once("db.conf.php");
  require_once("db.php");
  require_once("fix_magic_quotes.php");
  require_once("locales_string.php");

  include_once('/usr/share/php/smarty/libs/Smarty.class.php');  
  header("Content-Type: text/xml");

  $likeBackReplyError = "<LikeBackReply>\n" .
                        "	<Result type=\"error\" code=\"100\" message=\"Data were sent in invalid format. Perhaps your version of the application is too old.\" />\n" .
                        "</LikeBackReply>\n";

  if ( !isset($_POST['version'])  ||
       !isset($_POST['protocol']) ||
       !isset($_POST['locale'])   ||
       !isset($_POST['window'])   ||
       !isset($_POST['context'])  ||
       !isset($_POST['type'])     ||
       !isset($_POST['email'])    ||
       !isset($_POST['comment'])     )
    die($likeBackReplyError);

  $protocol = (isset($_POST['protocol']) ? $_POST['protocol'] : "0.0");
  $version  = $_POST['version'];
  $locale   = (isset($_POST['locale']) ? $_POST['locale'] : "");
  $window   = $_POST['window'];
  $context  = $_POST['context'];
  $type     = $_POST['type'];
  $comment  = $_POST['comment'];
  $email    = (isset($_POST['email']) ? $_POST['email'] : "");

  /// FIXME: Check version, window and context??

  if ( $type != "Like"    &&
       $type != "Dislike" &&
       $type != "Bug"     &&
       $type != "Feature"    )
    die($likeBackReplyError);

  /// From http://fr.php.net/manual/fr/function.date.php
  /// @Param $int_date Current date in UNIX timestamp
  function get_iso_8601_date($int_date) {
    $date_mod      = date('Y-m-d\TH:i:s', $int_date);
    $pre_timezone  = date('O', $int_date);
    $time_zone     = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
    $date_mod     .= $time_zone;
    return $date_mod;
  }

  db_query("INSERT INTO LikeBack(date, version, locale, window, context, type, status, comment, email) " .
                         "VALUES(?,    ?,       ?,      ?,      ?,       ?,    ?,      ?,       ?);",
                  array( get_iso_8601_date(time()), $version, $locale, $window, $context,
                    $type, 'New', $comment, $email) );
  $id = db_insert_id();

  $sendMailTo = array();
  $data = db_query("SELECT * FROM LikeBackDevelopers WHERE email!=''");
  while ($line = db_fetch_object($data)) {
    if (matchType($line->types, $type) && matchLocale($line->locales, $locale))
      array_push( $sendMailTo, $line->email );
  }

  $sendMailTo = join( ", ", $sendMailTo );

  if (!empty($sendMailTo)) {
    $from    = $likebackMail;
    $replyTo = (empty($email) ? $sendMailTo : $email);
    $to      = $sendMailTo;
    $subject = "[LikeBack: $type] #$id ($version - $locale)";

    $path    = dirname( $_SERVER['SCRIPT_NAME'] ) . "/";
    $serverPort = ":" . $_SERVER['SERVER_PORT'];
    if ($serverPort == ":80")
      $serverPort = "";
    $url     = "http://" . $_SERVER['HTTP_HOST'] . $serverPort . $path . "admin/comment.php?id=" . $id;
    
    $comment = str_replace( "\r", "", $comment );
    // Prepend every line with >
    $comment = "> " . str_replace( "\n", "\n> ", $comment );

    $smarty = new Smarty;
    $smarty->assign( 'project',   LIKEBACK_PROJECT );
    $smarty->assign( 'version',   $version );
    $smarty->assign( 'locale',    $locale );
    $smarty->assign( 'window',    $window );
    $smarty->assign( 'context',   $context );
    $smarty->assign( 'type',      $type );
    $smarty->assign( 'comment',   $comment );
    $smarty->assign( 'url',       $url );
    $smarty->template_dir = 'admin/templates';
    $smarty->compile_dir  = '/tmp';

    $message = $smarty->fetch( 'email/comment.tpl' );
    $message = wordwrap($message, 70);

    $headers = "From: $from\r\n" .
               "Reply-To: $replyTo\r\n" .
               "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();

//echo "***** To: $to<br>\r\n***** Subject: $subject<br>\r\n***** Message: $message<br>\r\n***** Headers: $headers";
    mail($to, $subject, $message, $headers);
  }

?>
<LikeBackReply>
	<Result type="ok" code="000" message="Comment registered. We will take it in account to design the next version of this application." />
</LikeBackReply>
