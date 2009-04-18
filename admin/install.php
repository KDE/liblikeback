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

  require_once("../db.conf.php");
  require_once("../db.php");

  db_query("DROP TABLE IF EXISTS LikeBack");
  db_query("
    CREATE TABLE LikeBack (
      id      INT(4)        NOT NULL AUTO_INCREMENT PRIMARY KEY,
      date    DATETIME,
      version VARCHAR(255),
      locale  VARCHAR(10),
      window  VARCHAR(255),
      context VARCHAR(255),
      status  VARCHAR(10),
      type    VARCHAR(10),
      comment TEXT,
      email   VARCHAR(255)
    );"
  );
  echo mysql_error() . "<br>";

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
    );
  ");
  echo mysql_error() . "<br>";

  db_query("
    CREATE TABLE LikeBackRemarks (
      id         INT(4)   NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `dateTime` DATETIME NOT NULL,
      developer  INT(4)   NOT NULL,
      commentId  INT(4)   NOT NULL,
      remark     TEXT     NOT NULL
    );
  ");
  echo mysql_error() . "<br>";
?>
Installation done.
