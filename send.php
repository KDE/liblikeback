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
  require_once("locales_string.php");

  include_once('/usr/share/php/smarty/libs/Smarty.class.php');  
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

  // TODO: Check version (newest?), window and context?

  if ( $type != "Like"    &&
       $type != "Dislike" &&
       $type != "Bug"     &&
       $type != "Feature"    )
    die('<LikeBackReply><Result type="error" code="' . ERROR_UNKNOWN_REPORTTYPE . '" message="Invalid type, must be one of Like, Dislike, Bug or Feature."/></LikeBackReply>');

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
  <Result type="ok" code="<?=RESULT_SUCCESS?>" message="Comment registered. We will take it in account to design the next version of this application." />
</LikeBackReply>
