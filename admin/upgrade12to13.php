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
die("Upgrading to LikeBack 1.3.");

// Create resolutions table
if( !db_query("CREATE TABLE `LikeBackResolutions` (
                 `id`        TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 `printable` VARCHAR( 50 )    NOT NULL,
                 `icon`      VARCHAR( 50 )    NOT NULL,
               );") )
{
  die( "Couldn't create a LikeBackResolutions table: " . mysql_error() );
}

// Insert some standard resolutions
if( !db_query("INSERT INTO `LikeBackResolutions` ( `printable` )
  VALUES ( 'Solved', 'solved.png' ), ( 'Invalid', 'invalid.png' ), ( 'Won\'t fix', 'invalid.png' ), ( 'Thanks', 'solved.png' )") )
{
  die( "Couldn't give LikeBackResolutions its initial content: " . mysql_error() );
}

// Add a 'resolution' column to the comments table
if( !db_query("ALTER TABLE `LikeBack` ADD `resolution` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `status`;") )
{
  die( "Couldn't add 'resolution' to LikeBack table: " . mysql_error() );
}

# Now merge all "done" statuses to be set to "closed"
if( !db_query("UPDATE `LikeBack` SET `status`='Closed', `resolution`=1 WHERE `status`='Solved';\n" .
              "UPDATE `LikeBack` SET `status`='Closed', `resolution`=2 WHERE `status`='Invalid';\n" .
              "UPDATE `LikeBack` SET `status`='Closed', `resolution`=3 WHERE `status`='Wontfix';\n" .
              "UPDATE `LikeBack` SET `status`='Closed', `resolution`=4 WHERE `status`='Thanks';\n" ) )
{
  die( "Couldn't update LikeBack comment statuses: " . mysql_error() );
}

?>
Installation done.
echo "Done with the upgrade, your LikeBack 1.3 installation is ready.\n";
