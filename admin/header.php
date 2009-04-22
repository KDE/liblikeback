<?php
/***************************************************************************
                          header.php - Admin interface header
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

  ob_start();

  if(!isset($sessionStarted))
    session_start();
  require_once("../db.php");
  require_once("../fix_magic_quotes.php");
  require_once("functions.php");
  require_once("../functions.inc.php");

$developer = getDeveloper();

?>
<html>
 <head>
  <title><?php echo $title; ?> - LikeBack</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="style.css">
  <script type="text/javascript" src="xmlhttprequest.js"></script>
  <script type="text/javascript" src="jsUtilities.js"></script>
  <script type="text/javascript" src="scripts.js"></script>
 </head>
 <body onmousedown="hideStatusMenu(event)">
