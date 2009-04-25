<?php
/***************************************************************************
                          ajax.php - The server-side handler for AJAX comment changes
                             -------------------
    begin                : 20 april 2009
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
include("../functions.inc.php");

// Verify incoming data
if( !isset( $_GET['markAs'] ) || !isset( $_GET['id'] ) )
  exit();

$mark = $_GET['markAs'];
if( !in_array( $mark, validStatuses() ) )
  exit;

db_query("UPDATE LikeBack SET status=? WHERE id=?", array( $_GET['markAs'], $_GET['id'] ) )
  or die(mysql_error());

