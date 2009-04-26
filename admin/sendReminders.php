<?php
/***************************************************************************
                    sendReminders.php - Send weekly reminders of old bugs
                             -------------------
    begin                : 22 Apr 2009
    copyright            : (C) 2009 by the KMess team
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

require_once( "../db.php" );
require_once( "../functions.inc.php" );
require_once( "functions.php" );

// Get all developers
$developers = db_fetchAll( "SELECT id, login, email, types, locales FROM LikeBackDevelopers" );

// Prepare the queries
$unsolved     = array_diff( validStatuses(), validDoneStatuses() );
$placeholders = db_buildQuery_checkArray( 'status', $unsolved );
$conditional  = array_shift( $placeholders );

// Get the counts of unsolved comments
$statuses   = db_fetchAll( "SELECT status, COUNT(*) AS count FROM `LikeBack` WHERE $conditional GROUP BY status", $placeholders );
$totalCount = 0;
foreach( $statuses as $status )
{
  $statusCounts[$status->status] = $status->count;
  $totalCount                   += $status->count;
}

// Get the FETCH_NUM latest unsolved comments
$comments     = db_fetchAll( "SELECT    *"
                           ." FROM LikeBack"
                           ." WHERE $conditional"
                           ." ORDER BY `date` ASC"
                           ." LIMIT 1, 30", $placeholders );

$smarty = getSmartyObject( true );

header("Content-Type: text/plain");
$developerMails = array();
foreach( $developers as $developer ) {
  $from    = $likebackMail;
  $to      = $developer->email;
  $subject = $likebackMailSubject . " - Weekly comment reminders";

  $smarty->assign( 'developer',    $developer );
  $smarty->assign( 'statusCounts', $statusCounts );
  $smarty->assign( 'totalCount',   $totalCount );
  $smarty->assign( 'comments',     $comments );
  $smarty->assign( 'url',          getLikeBackUrl()."/admin/comment.php?id=" );

  $mail = $smarty->fetch( 'email/reminders.tpl' );
  // don't word wrap!
  
  $headers = "From: $from\r\n" .
    "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
    "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();

  mail( $to, $subject, $mail, $headers );
}
