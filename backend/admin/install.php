<?php
/***************************************************************************
                          install.php - Likeback installation script
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

require_once("../db.php");

$tables = db_query("SHOW TABLES LIKE `LikeBack`");
if( db_count_results( $tables ) > 0 )
  die("LikeBack seems to be already set up, not continuing... To reinstall, remove your LikeBack tables.");

  db_query("
    CREATE TABLE LikeBack (
      id      INT(4)        NOT NULL AUTO_INCREMENT PRIMARY KEY,
      date    DATETIME,
      version VARCHAR(255),
      fullVersion VARCHAR(255),
      locale  VARCHAR(10),
      window  VARCHAR(255),
      context VARCHAR(255),
      status  VARCHAR(10),
      `resolution` TINYINT UNSIGNED NOT NULL DEFAULT '0',
      type    VARCHAR(10),
      comment TEXT,
      email   VARCHAR(255),
      tracbug SMALLINT UNSIGNED NULL
    ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;"
  );
  echo mysql_error() . "<br/>";

  // KDE is translated in roughly 80 languages.
  // Each locale take maximum 7 charachers (eg. "+en_US;").
  // 80*7 = 560 : too much for VARCHAR
  // So we use TEXT for locales.
  db_query("
    CREATE TABLE LikeBackDevelopers (
      id        INT(4)        NOT NULL AUTO_INCREMENT PRIMARY KEY,
      login     VARCHAR(255)  NOT NULL,
      email     VARCHAR(255),
      lastVisit DATETIME,
      types     VARCHAR(64),
      locales   TEXT
    ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
  ");
  echo mysql_error() . "<br/>";

  db_query("
    CREATE TABLE LikeBackRemarks (
      id         INT(4)   NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `dateTime` DATETIME NOT NULL,
      developer  INT(4)   NOT NULL,
      commentId  INT(4)   NOT NULL,
      remark     TEXT     NOT NULL,
      userNotified TINYINT UNSIGNED NOT NULL DEFAULT '0',
      statusChangedTo VARCHAR(50) NULL,
      resolutionChangedTo VARCHAR(50) NULL,
      tracbugChangedTo SMALLINT UNSIGNED NULL
    ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
  ");
  echo mysql_error() . "<br/>";

  db_query("
    CREATE TABLE `LikeBackResolutions` (
      `id`        TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `printable` VARCHAR( 50 )    NOT NULL,
      `icon`      VARCHAR( 50 )    NOT NULL
    ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
  ");
  echo mysql_error() . "<br/>";

  db_query("INSERT INTO `LikeBackResolutions` ( `printable`, `icon` )
            VALUES ( 'Solved', 'solved.png' ), ( 'Invalid', 'invalid.png' ), ( 'Won\'t fix', 'invalid.png' ), ( 'Thanks', 'solved.png' )");
  echo mysql_error() . "<br/>";
?>
Installation done.
