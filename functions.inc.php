<?php
/***************************************************************************
                    functions.inc.php - General functions
                             -------------------
    begin                : 21 Apr 2009
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

// Returns an array() of valid statuses in LikeBack.
function validStatuses()
{
  return array( "New", "Confirmed", "Progress", "Solved", "Invalid" );
}

// Returns an array() of valid types in LikeBack
function validTypes()
{
  return array( "Like", "Dislike", "Bug", "Feature" );
}

// Strip a variable of slashes if magic_quotes_gpc is enabled.
function maybeStrip( $variable )
{
  if( get_magic_quotes_gpc() )
    return stripslashes( $variable );
  return $variable;
}
