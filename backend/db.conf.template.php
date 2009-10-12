<?php
/***************************************************************************
                          db.conf.php - Database configuration file
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

  $dbType   = "mysql";
  $dbServer = "localhost";
  $dbBase   = "likeback";
  $dbUser   = "likeback";
  $dbPass   = "";

  // Email address to use when sending mail to the feedback poster
  $likebackMail = "likeback@example.org";

  // Name of the email
  $likebackMailSubject = "LikeBack";

  // Whether debugging is enabled in the likeback interface
  define( "LIKEBACK_DEBUG", 0 );

  // If this is enabled, no warning messages will be emitted.
  define( "LIKEBACK_PRODUCTION", 0 );

  // The name of this project
  define( "LIKEBACK_PROJECT", "Example project" );

  // The logo URL for this project
  define( "LIKEBACK_APP_LOGO", "http://example.org/icons/logo.png" );

  // The URL to LikeBack (without 'admin/', but it should exist after this link!)
  define( "LIKEBACK_URL", "http://example.org/likeback" );

  // The URL to a Trac instance (if empty, support for trac is disabled - also should *not* end in /!)
  define( "LIKEBACK_TRAC_URL", "" );

  // The secret compiled into the trac plugin
  define( "LIKEBACK_TRAC_SECRET", "" );

  // Don't change this:
  define( "LIKEBACK_VERSION", "1.4-svn" );
