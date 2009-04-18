<?php
/***************************************************************************
                          fix_magic_quotes.php - PHP Magic Quotes fix
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

  function fix_magic_quotes ($var = NULL, $sybase = NULL)
  {
    // si $sybase n'est pas specifie, on regarde la configuration ini
    if ( !isset ($sybase) )
    {
      $sybase = ini_get ('magic_quotes_sybase');
    }

    // si $var n'est pas specifie, on corrige toutes les variables superglobales
    if ( !isset ($var) )
    {
      // si les magic_quotes sont activees
      if ( get_magic_quotes_gpc () )
      {
        // tableaux superglobaux a corriger
        $array = array ('_REQUEST', '_GET', '_POST', '_COOKIE');
        if ( substr (PHP_VERSION, 0, 1) <= 4 )
        {
          // PHP5 semble ne pas changer _ENV et _SERVER
          array_push ($array, '_ENV', '_SERVER');
          // les magic_quotes ne changent pas $_SERVER['argv']
          $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : NULL;
        }
        foreach ( $array as $var )
        {
          $GLOBALS[$var] = fix_magic_quotes ($GLOBALS[$var], $sybase);
        }
        if ( isset ($argv) )
        {
          $_SERVER['argv'] = $argv;
        }
        // desactive les magic quotes dans ini_set pour que les
        // scripts qui y sont sensibles fonctionnent
        ini_set ('magic_quotes_gpc', 0);
      }

      // idem, pour magic_quotes_sybase
      if ( $sybase )
      {
        ini_set ('magic_quotes_sybase', 0);
      }

      // desactive magic_quotes_runtime
      set_magic_quotes_runtime (0);
      return TRUE;
    }

    // si $var est un tableau, appel recursif pour corriger chaque element
    if ( is_array ($var) )
    {
      foreach ( $var as $key => $val )
      {
        $var[$key] = fix_magic_quotes ($val, $sybase);
      }

      return $var;
    }

    // si $var est une chaine on utilise la fonction stripslashes,
    // sauf si les magic_quotes_sybase sont activees, dans ce cas on
    // remplace les doubles apostrophes par des simples apostrophes
    if ( is_string ($var) )
    {
      return $sybase ? str_replace ('\'\'', '\'', $var) : stripslashes ($var);
    }

    // sinon rien
    return $var;
  }

// Makes the server to abort the request... it's useless with a configured PHP5 too
//   fix_magic_quotes();
?>
