<?php
/***************************************************************************
                    upgrade11to12.php - Likeback upgrade script for 1.1 to 1.2
                             -------------------
    begin                : 22 Apr 2009
    copyright            : (C) 2009 by the KMess team
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

require_once("../db.php");
require_once("../functions.inc.php");

header("Content-Type: text/plain");

// Check if upgrade has already finished
$tables = db_query("DESCRIBE `LikeBack`");
while( $row = mysql_fetch_assoc( $tables ) )
{
  if( $row['Field'] == "fullVersion" )
    die("The LikeBack database seems to be already upgraded for LikeBack v1.2. Cancelling upgrade.");
}

// Add a new row to the LikeBack table
echo "Upgrading your database format...";
if( !db_query("ALTER TABLE `LikeBack` ADD `fullVersion` VARCHAR( 255 ) NULL AFTER `version`;") )
  die( "Upgrade failed while altering table LikeBack: " . mysql_error() );
echo "Done.\n";

// Convert all versions
echo "Upgrading your database data... ";
$rows = db_query( "SELECT id, version FROM `LikeBack`" );
$versions = array();
$handled = 0;
while( $row = mysql_fetch_assoc( $rows ) )
{
  $id           = $row['id'];
  $fullVersion  = trim( $row['version'] );
  if( strpos( $fullVersion, "(" ) === FALSE )
    $version    = $fullVersion;
  else
    $version    = trim( substr( $fullVersion, 0, strpos( $fullVersion, "(" ) ) );

  $query        = "UPDATE `LikeBack` SET `fullVersion`=?, `version`=? WHERE `id`=?";
  $placeholders = array( $fullVersion, $version, $id );

  $result = db_query( $query, $placeholders );
  if( !$result ) {
    echo "Failed to upgrade comment $id! Please edit the row yourself. This is the data you should fill in:";
    echo "for ID      = $id\n";
    echo "fullVersion = $fullVersion\n";
    echo "version     = $version\n";
    echo "-----------------------------------------------------\n";
    continue;
  }

  if( !in_array( $version, $versions ) )
    $versions[] = $version;

  $handled++;
}

echo "Done. $handled entries converted. " . count($versions) . " versions detected:\n";
echo join(" - ", $versions );

echo "\n";

// Run stripslashes on all remarks
echo "Upgrading the format of all LikeBack remarks...";
$remarks = db_fetchAll( "SELECT id, remark  FROM `LikeBackRemarks`" );
$updated = 0;
foreach( $remarks as $remark )
{
  $id    = $remark->id;
  $text  = $remark->remark;
  $strip = maybeStrip( $text );

  if( $strip != $text ) {
    ++$updated;
    $query        = "UPDATE `LikeBackRemarks` SET `remark`=? WHERE `id`=?";
    $placeholders = array( $strip, $id );
    $result       = db_query( $query, $placeholders );
    if( !$result ) {
      echo "Failed to upgrade remark $id! Please edit the row yourself. This is the data you should fill in:";
      echo "for ID      = $id\n";
      echo "remark      = $strip\n";
      echo "-----------------------------------------------------\n";
      continue;
    }
  }
}

echo "Done. $updated entries converted.\n";

echo "Resetting Smarty cache...";
require_once "functions.php";
$smarty = getSmartyObject();
$smarty->clear_all_cache();
echo "Done.\n";
echo "Done with the upgrade, your LikeBack 1.2 installation is ready.\n";
