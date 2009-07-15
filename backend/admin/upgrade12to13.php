<?php
/***************************************************************************
                    upgrade12to13.php - Likeback upgrade script for 1.2 to 1.3
                             -------------------
    begin                : 13 May 2009
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
$q = db_query( "DESCRIBE `LikeBackResolutions`", array(), true );
if( db_fetch_object( $q ) ) {
  die( "Your installation appears to be already upgraded to LikeBack 1.3." );
}
print("Upgrading to LikeBack 1.3.");

if( !db_query( "ALTER TABLE `LikeBack`           DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;" )
 || !db_query( "ALTER TABLE `LikeBackDevelopers` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;" )
 || !db_query( "ALTER TABLE `LikeBackRemarks`    DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;" ) )
{
  die( "Couldn't set character set for all tables to utf8: " . mysql_error() );
}

// Create resolutions table
if( !db_query("CREATE TABLE `LikeBackResolutions` (
                 `id`        TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 `printable` VARCHAR( 50 )    NOT NULL,
                 `icon`      VARCHAR( 50 )    NOT NULL
               ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;") )
{
  die( "Couldn't create a LikeBackResolutions table: " . mysql_error() );
}

// Insert some standard resolutions
if( !db_query("INSERT INTO `LikeBackResolutions` ( `printable`, `icon` )
  VALUES ( 'Solved', 'solved.png' ), ( 'Invalid', 'invalid.png' ), ( 'Won\'t fix', 'invalid.png' ), ( 'Thanks', 'solved.png' )") )
{
  die( "Couldn't give LikeBackResolutions its initial content: " . mysql_error() );
}

// Add a 'resolution' column to the comments table
if( !db_query("ALTER TABLE `LikeBack` ADD `resolution` VARCHAR(50) NULL AFTER `status`;") )
{
  die( "Couldn't add 'resolution' to LikeBack table: " . mysql_error() );
}

# Now merge all "done" statuses to be set to "closed"
if( !db_query("UPDATE `LikeBack` SET `status`='Closed', `resolution`='Solved' WHERE `status`='Solved';")
  ||!db_query("UPDATE `LikeBack` SET `status`='Closed', `resolution`='Invalid' WHERE `status`='Invalid';")
  ||!db_query("UPDATE `LikeBack` SET `status`='Closed', `resolution`='Won\'t fix' WHERE `status`='Wontfix';")
  ||!db_query("UPDATE `LikeBack` SET `status`='Closed', `resolution`='Thanks' WHERE `status`='Thanks';\n" ) )
{
  die( "Couldn't update LikeBack comment statuses: " . mysql_error() );
}

if( !db_query( "ALTER TABLE `LikeBackRemarks` ADD `userNotified` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `remark` ,\n" .
  "ADD `statusChangedTo` VARCHAR( 50 ) NULL AFTER `userNotified` ,\n" .
  "ADD `resolutionChangedTo` VARCHAR( 50 ) NULL AFTER `statusChangedTo` ;" ) )
{
  die( "Couldn't insert new columns to the LikeBackRemarks table: " . mysql_error() );
}

// read all remarks, set the right flags and remove the notifications from the remarks themselves
$result = db_query( "SELECT `id`, `remark` FROM `LikeBackRemarks`" );
if( !$result )
{
  die( "Couldn't retrieve a list of LikeBack remarks: " . mysql_error() );
}
while( $remark = db_fetch_object( $result ) )
{
  $userNotified = 0;
  $statusChangedTo = "";
  $resolutionChangedTo = "";
  $oldRemark = $remark->remark;
  $remark->remark = str_replace( "\r", "", $remark->remark );

  $searchstring = "The developer closed the comment and set resolution to ";
  if( strpos( $remark->remark, $searchstring ) === 0 )
  {
    $newline = strpos( $remark->remark, "\n" ) === FALSE ? strlen( $remark->remark ) : strpos( $remark->remark, "\n" );
    $statusChangedTo = "Closed";
    $resolutionChangedTo = substr( $remark->remark, strlen( $searchstring ), $newline - strlen( $searchstring ) );
    $remark->remark = substr( $remark->remark, $newline + 1 );
  }

  $searchstring = "The developer set the resolution for this comment to ";
  if( strpos( $remark->remark, $searchstring ) === 0 )
  {
    $newline = strpos( $remark->remark, "\n" ) === FALSE ? strlen( $remark->remark ) : strpos( $remark->remark, "\n" );
    $resolutionChangedTo = substr( $remark->remark, strlen( $searchstring ), $newline - strlen( $searchstring ) );
    $remark->remark = substr( $remark->remark, $newline + 1 );
  }

  $searchstring = "The developer reopened the comment and set the status to ";
  if( strpos( $remark->remark, $searchstring ) === 0 )
  {
    $newline = strpos( $remark->remark, "\n" ) === FALSE ? strlen( $remark->remark ) : strpos( $remark->remark, "\n" );
    $statusChangedTo = substr( $remark->remark, strlen( $searchstring ), $newline - strlen( $searchstring ) );
    $remark->remark = substr( $remark->remark, $newline + 1 );
  }

  $searchstring = "The developer set the status for this comment to ";
  if( strpos( $remark->remark, $searchstring ) === 0 )
  {
    $newline = strpos( $remark->remark, "\n" ) === FALSE ? strlen( $remark->remark ) : strpos( $remark->remark, "\n" );
    $statusChangedTo = substr( $remark->remark, strlen( $searchstring ), $newline - strlen( $searchstring ) );
    $remark->remark = substr( $remark->remark, $newline + 1 );
  }

  // is followed by : or .
  $searchstring = "This remark has also been sent to the user";
  if( strpos( $remark->remark, $searchstring ) === 0 )
  {
    $newline = strpos( $remark->remark, "\n" ) === FALSE ? strlen( $remark->remark ) : strpos( $remark->remark, "\n" );
    $userNotified = 1;
    $remark->remark = substr( $remark->remark, strlen( $searchstring ) + 2 );
  }

  // if nothing was changed, go to the next one
  if( !$statusChangedTo && !$resolutionChangedTo && !$userNotified )
    continue;

  // remove an optional newline if anything was removed
  if( strpos( $remark->remark, "\n" ) === 0 )
  {
    $remark->remark = substr( $remark->remark, 1 );
  }

  // quick hacky fix :)
  if( $statusChangedTo == "In progress" )
    $statusChangedTo = "Progress";
  elseif( $statusChangedTo == "Won't fix" )
    $statusChangedTo = "Wontfix";

  $query = "UPDATE LikeBackRemarks SET ";
  $placeholders = array();

  // userNotified: 0 or 1
  $query .= "`userNotified`=" . $userNotified . ", ";

  // statusChangedTo, resolutionChangedTo
  if( $statusChangedTo )
  {
    $query .= "`statusChangedTo`=?, ";
    $placeholders[] = $statusChangedTo;
  } else $query .= "`statusChangedTo`=null, ";

  if( $resolutionChangedTo )
  {
    $query .= "`resolutionChangedTo`=?, ";
    $placeholders[] = $resolutionChangedTo;
  } else $query .= "`resolutionChangedTo`=null, ";

  // message
  $query .= "`remark`=? ";
  $placeholders[] = $remark->remark;

  $query .= "WHERE `id`=?;";
  $placeholders[] = $remark->id;

  if( !db_query( $query, $placeholders ) )
  {
    print "Failed to execute a query for remark ID " . $remark->id . ": " . mysql_error() . "\n";
    print "Query tried: $query\n";
    print "Placeholders: "; print_r($placeholders);
    print "\n";
    continue;
  }
}

// Add a Trac entry to the database
if( !db_query( "ALTER TABLE `LikeBack` ADD `tracbug` SMALLINT UNSIGNED NULL ;" ) )
{
  die("Failed to add tracbug to the LikeBack table: " . mysql_error());
}
if( !db_query( "ALTER TABLE `LikeBackRemarks` ADD `tracbugChangedTo` SMALLINT UNSIGNED NULL ;" ) )
{
  die("Failed to add tracbugchangedto to the LikeBackRemarks table: " . mysql_error() );
}
echo "Done with the upgrade, your LikeBack 1.3 installation is ready.\n";
