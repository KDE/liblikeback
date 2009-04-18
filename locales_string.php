<?php
/***************************************************************************
                          locales_string.php - locales handling
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
?>
