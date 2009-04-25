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

echo '<div style="margin: 10px;">';
// Get the counts of all comments
$types      = db_fetchAll( "SELECT type, COUNT(*) AS count FROM `LikeBack` GROUP BY type" );
$totalCount = 0;
foreach( $types as $type )
{
  echo '<b>' . messageForType($type->type) . ':</b> ' . $type->count . " comments remaining<br/><br/>\n";
  $totalCount += $type->count;
}

echo '<b>Total:</b> ' . $totalCount . " comments remaining\n";
echo "</div>\n";

$smarty->display( 'html/bottom.tpl' );
