<?php
/***************************************************************************
                          stats.php - Likeback statistics
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

$title = "Stats";
include("header.php");

// Get the counts of all comments
$typesRaw   = db_fetchAll( "SELECT type, COUNT(*) AS count FROM `LikeBack` GROUP BY type" );
$totalCount = 0;
$types      = array();
// change sorting to be what's returned by validTypes()
foreach( validTypes() as $type ) {
  $done = 0;
  foreach( $typesRaw as $typeRaw ) {
    if( $typeRaw->type == $type ) {
      $done = 1;
      $types[] = $typeRaw;
      $totalCount += $typeRaw->count;
    }
  }
  if( !$done )
    $types[] = (object) array( 'type' => $type, 'count' => 0 );
}

$statusesRaw= db_fetchAll( "SELECT status, COUNT(*) AS count FROM `LikeBack` GROUP BY status" );
$statuses   = array();
// change sorting to be what's returned by validStatuses()
foreach( validStatuses() as $status ) {
  $done = 0;
  foreach( $statusesRaw as $statusRaw ) {
    if( $statusRaw->status == $status ) {
      $done = 1;
      $statuses[] = $statusRaw;
    }
  }
  if( !$done )
    $statuses[] = (object) array( 'status' => $status, 'count' => 0 );
}

// Get the counts of developers
$numDevsWE  = db_query( "SELECT COUNT(*) AS count FROM `LikeBackDevelopers` WHERE `email`!=''" );
$numDevsWE  = db_fetch_object( $numDevsWE );
if( $numDevsWE )
  $numDevsWE= $numDevsWE->count;
$numDevs    = db_query( "SELECT COUNT(*) AS count FROM `LikeBackDevelopers`" );
$numDevs    = db_fetch_object( $numDevs );
if( $numDevs )
  $numDevs  = $numDevs->count;

$subBarContents = '<a href="view.php?useSessionFilter=true"><img src="icons/gohome.png" width="32" height="32" alt=""></a> &nbsp; &nbsp;
   <strong>LikeBack statistics</strong>';

$smarty->assign( 'subBarType',     'Options' );
$smarty->assign( 'subBarContents', $subBarContents );
$smarty->assign( 'totalCount',     $totalCount );
$smarty->assign( 'typeCounts',     $types );
$smarty->assign( 'statusCounts',   $statuses );
$smarty->assign( 'numDevelopers',  $numDevs );
$smarty->assign( 'numDevelopersWithEmail', $numDevsWE );
$smarty->display( 'html/stats.tpl' );
