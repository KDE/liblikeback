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


  echo "<b>Total:</b> ";
  $data = db_query("SELECT COUNT(*) FROM LikeBack");
  $line = mysql_fetch_array($data);
  $count = $line[0];
  echo "$count<br>\n";

  echo "<b>Like:</b> ";
  $data = db_query("SELECT COUNT(*) FROM LikeBack WHERE type='Like'");
  $line = mysql_fetch_array($data);
  $count = $line[0];
  echo "$count<br>\n";

  echo "<b>Do not like:</b> ";
  $data = db_query("SELECT COUNT(*) FROM LikeBack WHERE type='Dislike'");
  $line = mysql_fetch_array($data);
  $count = $line[0];
  echo "$count<br>\n";

  echo "<b>Bug:</b> ";
  $data = db_query("SELECT COUNT(*) FROM LikeBack WHERE type='Bug'");
  $line = mysql_fetch_array($data);
  $count = $line[0];
  echo "$count<br>\n";

  echo "<b>Feature:</b> ";
  $data = db_query("SELECT COUNT(*) FROM LikeBack WHERE type='Feature'");
  $line = mysql_fetch_array($data);
  $count = $line[0];
  echo "$count<br>\n";
?>
