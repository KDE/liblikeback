<?php
/***************************************************************************
                          options.php - Likeback settings
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

// prevent warning
$sessionStarted = 1;
session_start();

$title = "LikeBack settings";
include("../db.php");
require_once("functions.php");
require_once("../functions.inc.php");

$developer = getDeveloper();

if (isset($_POST['saveOptions'])) {
  $email = maybeStrip( $_POST['email'] );
  // just a precaution:
  if( strlen($email) > 250 )
    $email = substr($email, 0, 250);

  $types = array();
  foreach( validTypes() as $type ) {
    if( isset( $_POST['Match'.$type] ) )
      array_push( $types, $type );
  }
  $types = join( ";", $types );
  if( strlen($types) > 64 )
    $types = substr($types, 0, 64 );

  $locales = array();
  $localesData = db_query("SELECT locale FROM LikeBack GROUP BY locale ORDER BY locale ASC") or die(mysql_error());
  while ($line = db_fetch_object($localesData)) {
    if (isset($_POST["MatchLocale_".$line->locale]))
      array_push( $locales, '+'.$line->locale );
    else
      array_push( $locales, '-'.$line->locale );
  }

  if (isset($_POST['MatchOtherLocales']))
    array_push( $locales, '+*' );
  else
    array_push( $locales, '-*' );
  $locales = join( ";", $locales );

  db_query("UPDATE LikeBackDevelopers SET email=?, types=?, locales=? WHERE login=?", array( $email, $types, $locales, $developer->login ) );
}

include("header.php");

$smarty->display( 'html/lbheader.tpl' );

$subBarContents = '<a href="view.php?useSessionFilter=true"><img src="icons/gohome.png" width="32" height="32" alt="Back" /></a> &nbsp; &nbsp;
   <strong><img src="icons/email.png" width="16" height="16" alt="E-mail" /> E-Mail Options</strong> &nbsp; &nbsp; '.$developer->login;
$smarty->assign( 'subBarType',     'Options' );
$smarty->assign( 'subBarContents', $subBarContents );
$smarty->display( 'html/lbsubbar.tpl' );

$likeChecked    = (matchType($developer->types, "Like")    ? 'checked="checked"' : "");
$dislikeChecked = (matchType($developer->types, "Dislike") ? 'checked="checked"' : "");
$bugChecked     = (matchType($developer->types, "Bug")     ? 'checked="checked"' : "");
$featureChecked = (matchType($developer->types, "Feature") ? 'checked="checked"' : "");

$smarty->assign( 'likeChecked',    $likeChecked    );
$smarty->assign( 'dislikeChecked', $dislikeChecked );
$smarty->assign( 'bugChecked',     $bugChecked     );
$smarty->assign( 'featureChecked', $featureChecked );

$locales = db_fetchAll("SELECT locale FROM LikeBack GROUP BY locale ORDER BY locale ASC");
$smarty->assign( 'locales', $locales );

$rawResolutions = db_fetchAll( "SELECT `id`,`icon` FROM LikeBackResolutions" );
$resolutions = array();
$resolutionIcons = array();
$i=0;
foreach($rawResolutions as $rawResolution) {
  $resolutions    [ $i ] = $rawResolution->id;
  $resolutionIcons[ $i ] = $rawResolution->icon;
  $i++;
}
// overwrite resolutions in smarty
$smarty->assign( 'resolutions', $resolutions );
$smarty->assign( 'resolutionIcons', $resolutionIcons );

$smarty->display( 'html/options.tpl' );
$smarty->display( 'html/bottom.tpl' );