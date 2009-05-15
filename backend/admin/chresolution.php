<?php
/***************************************************************************
                          chresolutions.php - Change resolution settings
                             -------------------
    begin                : 14 May 2009
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

include("../db.php");
header("Content-Type: text/plain");

if( isset( $_POST['new'] ) ) {
  // add a new resolution
  // todo: check if there's not an existing resolution with this name, and
  // if it's not longer than 50 chars, etc
  if( !db_query("INSERT INTO `LikeBackResolutions` (`printable`, `icon`) VALUES (?,?)",
    array( $_POST['newname'], $_POST['newicon'] ) ) )
  {
    echo "Error: Couldn't create new resolution.";
    die( mysql_error() );
  }
}
elseif( isset( $_GET['delete'] ) ) {
  // delete resolution
  // todo: set all comments with this id to a different one
  if( !db_query("DELETE FROM `LikeBackResolutions` WHERE `id`=?",
    array( $_GET['id'] ) ) )
  {
    echo "Error: Couldn't delete resolution.";
    die( mysql_error() );
  }
}
elseif( isset( $_POST['reicon'] ) ) {
  // change the icon
  // todo: check if the icon isn't longer than 50 characters, etc
  if( !db_query("UPDATE `LikeBackResolutions` SET `icon`=? WHERE `id`=?",
    array( $_POST['newicon'], $_POST['id'] ) ) )
  {
    echo "Error: Couldn't change settings for resolution.";
    die( mysql_error() );
  }
}
elseif( isset( $_POST['rename'] ) ) {
  // change the name
  // todo: check if the name isn't longer than 50 characters, doesn't
  // already exist, etc
  if( !db_query("UPDATE `LikeBackResolutions` SET `printable`=? WHERE `id`=?",
    array( $_POST['newname'], $_POST['id'] ) ) )
  {
    echo "Error: Couldn't change settings for resolution.";
    die( mysql_error() );
  }
}
else
{
  die( "Error: Didn't know what to do." );
}

header("Location: options.php");
echo "Success. Please follow the Location header.\n";
