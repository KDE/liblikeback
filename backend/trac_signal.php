<?php
/***************************************************************************
                  trac_signal.php - External interface for Trac signals
                             -------------------
    begin                : Mon, 8 Jun 2009
    copyright            : (C) 2009 by Sjors Gielen
    email                : sjors@kmess.org
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

header("Content-Type: text/plain");

// LIKEBACK_TRAC_SECRET
// $_POST[ comment status author summary secret ticket ticketid resolution ]

// first: is the secret ok?
if( !isset( $_POST['secret'] ) || LIKEBACK_TRAC_SECRET != $_POST['secret'] )
{
  die("Secret incorrect.");
}

// second: is this bug actually closing? (likeback doesn't support reopening from trac yet)
if( strToLower( $_POST['status'] ) != "closed" )
{
  print "Will only handle status=closed reports.";
  exit;
}

// third: prepare the state etc
$newStatus = "Closed";
$newResolution = maybeStrip( $_POST['resolution'] );
switch( strToLower( $newResolution ) )
{
case "fixed":
  $newResolution = "Solved";
  break;
case "duplicate":
  // TODO!
  $newResolution = "Solved";
  break;
case "worksforme":
  $newResolution = "Invalid";
  break;
}

// fourth: make sure we can set a valid developer
$likebackTracUser = "LikeBackTracIntegration";
$query = "SELECT id FROM `LikeBackDevelopers` WHERE `login`=?";
$users = db_fetchAll( $query, array( $likebackTracUser ) );
if( count( $users ) > 0 )
{
  $likebackTracUser = $users[0]->id;
}
else
{
  $query = "INSERT INTO `LikeBackDevelopers` (`login`) VALUES (?)";
  if( ! db_query( $query, array( $likebackTracUser ) ) )
  {
    die("Error: Database query failed: " . mysql_error() );
  }
  $likebackTracUser = db_insert_id();
}

// fourth: do we have any comments triaged to this bug?
$ticketid = (int)$_POST['ticketid'];
$comments = db_fetchAll( "SELECT * FROM `LikeBack` WHERE (`status`='triaged' OR `status`='Triaged') AND `tracbug`=?", array( $ticketid ) );
if( $comments === false )
{
  die("Database query failed: " . mysql_error());
}
if( $comments === 0 )
{
  print "No comments triaged to this bug.";
  exit;
}

$query1 = "UPDATE `LikeBack` SET `status`='Closed', `resolution`=? WHERE `id`=?";
$placeholders1 = array();
$query2 = "INSERT INTO `LikeBackRemarks` (`dateTime`, `developer`, `commentId`, `remark`, `userNotified`, `statusChangedTo`, `resolutionChangedTo`) VALUES (NOW(), ?, ?, ?, ?, ?, ?)";
$placeholders2 = array();

foreach( $comments as $comment )
{
  $remark = "Trac bug #" . $ticketid . " has just been closed with resolution set to " . $newResolution . ". This comment was triaged to that bug, so closing this comment.\n";
  $remark .= "Please note that if the original bug is reopened, this comment will not be automatically reopened.\n- The LikeBack Trac integration service";
  
  $smarty  = getSmartyObject( true );
  $smarty->template_dir = 'admin/templates';
  $smarty->compile_dir  = 'admin/templates/cache';
  $smarty->assign( 'newResolution', $newResolution );
  $smarty->assign( 'comment',       $comment );
  $smarty->assign( 'ticketid',      $ticketid );
  $smarty->assign( 'remark',        $remark );

  $tracurl = LIKEBACK_TRAC_URL . "/ticket/$ticketid";
  $smarty->assign( 'tracurl',       $tracurl );

  $userNotified = 0;
  if( $comment->email )
  {
    $userNotified  = 1;
    $from          = $likebackMail;
    $to            = $comment->email;
    $subject       = $likebackMailSubject . " - Comment automatically closed, LikeBack#" . $comment->id . ", Trac#" . $ticketid;

    $message = $smarty->fetch( 'email/tracintegration_remark.tpl' );
    $message = wordwrap($message, 80);

    $headers = "From: $from\r\n" .
      "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
      "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();
    
    mail($to, $subject, $message, $headers);
  }
  
  // Send a mail to all developers interested in this bug
  $sendMailTo = sendMailTo( $comment->type, $comment->locale );
  $sendMailTo = join( ", ", $sendMailTo );

  if ( !empty($sendMailTo) ) {
    $from    = $likebackMail;
    $to      = $sendMailTo;
    $subject = $likebackMailSubject . ' - Comment automatically closed, LikeBack#' . $comment->id . ', Trac#' . $ticketid;

    $url     = getLikeBackUrl() . "/admin/comment.php?id=" . $comment->id;

    $smarty->assign( 'url',     $url );
    $message = $smarty->fetch( 'email/tracintegration_remark_todev.tpl' );
    $message = wordwrap($message, 80);

    $headers = "From: $from\r\n" .
      "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
      "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();

    mail($to, $subject, $message, $headers);
  }

  $array1 = array( $newResolution, $comment->id );
  $array2 = array( $likebackTracUser, $comment->id, $remark, $userNotified, "Closed", $newResolution );
  $placeholders1[] = $array1;
  $placeholders2[] = $array2;
}

for( $i = 0; $i < count( $placeholders1 ); $i++ )
{
  if( !db_query( $query1, $placeholders1[$i] ) || !db_query( $query2, $placeholders2[$i] ) )
  {
    print "Error: Query failed to run: " . mysql_error();
    print "Query1: $query1\nQuery2: $query2\n";
  }
}

// and done!
print "done - " . count( $placeholders1 ) . " entries updated.";
