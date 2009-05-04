<?php
/***************************************************************************
                    functions.inc.php - General functions
                             -------------------
    begin                : 21 Apr 2009
    copyright            : (C) 2009 by the KMess team
                           (C) by BasKet Note Pads developers
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

// From http://fr.php.net/manual/fr/function.date.php
// @Param $int_date Current date in UNIX timestamp
function get_iso_8601_date($int_date) {
  $date_mod      = date('Y-m-d\TH:i:s', $int_date);
  $pre_timezone  = date('O', $int_date);
  $time_zone     = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
  $date_mod     .= $time_zone;
  return $date_mod;
}

// Returns an array() of valid statuses in LikeBack.
function validStatuses()
{
  return array( "New", "Confirmed", "Progress", "Thanks", "Solved", "Wontfix", "Invalid" );
}

// Returns an array() of "done" statuses in LikeBack.
function validDoneStatuses()
{
  return array( "Thanks", "Solved", "Wontfix", "Invalid" );
}

// Returns an array() of valid types in LikeBack
function validTypes()
{
  return array( "Like", "Dislike", "Bug", "Feature" );
}

// Strip a variable of slashes if magic_quotes_gpc is enabled.
function maybeStrip( $variable )
{
  if( get_magic_quotes_gpc() )
    return stripslashes( $variable );
  return $variable;
}

function matchLocale($localeList, $localeToTest)
{
  if (empty($localeList))
    $localeList = "+*";
  $localeList = ";$localeList;";

  // Test if the developer explicitely checked the locale:
  $matchLocale = !( strstr($localeList, ";+$localeToTest;") === false );
  if ($matchLocale)
    return true;

  // Test if the developer explicitely discarded a locale:
  $matchLocale = !( strstr($localeList, ";-$localeToTest;") === false );
  if ($matchLocale)
    return false;

  // Test if the developer implicitely discard other locales:
  $matchLocale = !( strstr($localeList, ";-*;") === false );
  if ($matchLocale)
    return false;

  // The developer implicitely accept other locales:
  return true;
}

function matchType($typeList, $typeToTest)
{
  $typeList = ";$typeList;";
  return !( strstr($typeList, ";$typeToTest;") === false );
}

function getLikeBackUrl ()
{
  return LIKEBACK_URL;
}

// Returns an array() of developers interested in this $type, $locale
function sendMailTo ($type, $locale)
{
  $sendMailTo = array();
  $data = db_query("SELECT * FROM LikeBackDevelopers WHERE email!=''");
  while ($line = db_fetch_object($data)) {
    if (matchType($line->types, $type) && matchLocale($line->locales, $locale))
      array_push( $sendMailTo, $line->email );
  }
  return $sendMailTo;
}


