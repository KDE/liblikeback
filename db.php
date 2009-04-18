<?php
/***************************************************************************
                          db.php - Database functions
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

  switch ($dbType) {
    default:
    case "mysql":
      @mysql_connect($dbServer, $dbUser, $dbPass) or die('Database server connection failed.');
      @mysql_select_db($dbBase)                   or die('Database connection failed.');
      break;
  }
  // Security if there is an hole in the remaining code:
  unset($dbServer);
  unset($dbBase);
  unset($dbUser);
  unset($dbPass);

  function db_query($query, $debug = false)
  {
    global $dbType;
    if ($debug)
      echo $query;
    switch ($dbType) {
      default:
      case "mysql":
        return mysql_query($query);
    }
  }

  function db_fetch_object($result)
  {
    global $dbType;
    switch ($dbType) {
      default:
      case "mysql":
        return mysql_fetch_object($result);
    }
  }

  function db_count_results($result)
  {
    global $dbType;
    switch ($dbType) {
      default:
      case "mysql":
        return mysql_num_rows($result);
    }
  }

  function db_insert_id()
  {
    return mysql_insert_id();
  }
?>
