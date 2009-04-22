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

$title = "View Comment";
include("../db.php");
require_once("functions.php");
require_once("../functions.inc.php");

$developer = getDeveloper();

if (isset($_POST['saveOptions'])) {
  $email = maybeStrip( $_POST['email'] );

  $types = array();
  foreach( validTypes() as $type ) {
    if( isset( $_POST['Match'.$type] ) )
      array_push( $types, $type );
  }
  $types = join( ";", $types );

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

$smarty = getSmartyObject();

$likeChecked    = (matchType($developer->types, "Like")    ? 'checked="checked"' : "");
$dislikeChecked = (matchType($developer->types, "Dislike") ? 'checked="checked"' : "");
$bugChecked     = (matchType($developer->types, "Bug")     ? 'checked="checked"' : "");
$featureChecked = (matchType($developer->types, "Feature") ? 'checked="checked"' : "");

$smarty->assign( 'likeChecked',    $likeChecked    );
$smarty->assign( 'dislikeChecked', $dislikeChecked );
$smarty->assign( 'bugChecked',     $bugChecked     );
$smarty->assign( 'featureChecked', $featureChecked );

$localesquery = db_query("SELECT locale FROM LikeBack GROUP BY locale ORDER BY locale ASC") or die(mysql_error());
$locales = array();
while ($line = db_fetch_object($localesquery)) {
  $locale = htmlentities($line->locale);
  $checked = (matchLocale($developer->locales, $locale) ? " checked=\"checked\"" : "");

  array_push( $locales, $line );
}
$smarty->assign( 'locales', $locales );

$smarty->display( 'html/options.tpl' );
$smarty->display( 'html/bottom.tpl' );
