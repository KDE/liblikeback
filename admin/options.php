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

// Move before 1.2 release!
$userName = $_SERVER['PHP_AUTH_USER'];
$data = db_query("SELECT * FROM LikeBackDevelopers WHERE login=? LIMIT 1", array( $userName ) );
$developer = db_fetch_object($data);
if (!$developer) {
  db_query("INSERT INTO LikeBackDevelopers(login, types, locales) VALUES(?, 'Like;Dislike;Bug;Feature', '+*')", array( $userName ) );
  $data = db_query("SELECT * FROM LikeBackDevelopers WHERE login=? LIMIT 1", array( $userName) );
  $developer = db_fetch_object($data);
}

if (isset($_POST['saveOptions'])) {
  if( get_magic_quotes_gpc() )
    $email = stripslashes( $_POST['email'] );
  else
    $email = $_POST['email'];

  $types = array();
  if (isset($_POST['MatchLike']))
    array_push($types, "Like");
  if (isset($_POST['MatchDislike']))
    array_push($types, "Dislike");
  if (isset($_POST['MatchBug']))
    array_push($types, "Bug");
  if (isset($_POST['MatchFeature']))
    array_push($types, "Feature");

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
require_once("../locales_string.php");

$smarty = getSmartyObject( $developer );

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
