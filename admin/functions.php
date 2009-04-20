<?php
/***************************************************************************
                          functions.php - Helper functions
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

// Include Smarty
include_once('/usr/share/php/smarty/libs/Smarty.class.php');

// From http://fr.php.net/manual/fr/function.date.php
// @Param $int_date Current date in UNIX timestamp
function get_iso_8601_date($int_date) {
  $date_mod      = date('Y-m-d\TH:i:s', $int_date);
  $pre_timezone  = date('O', $int_date);
  $time_zone     = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
  $date_mod     .= $time_zone;
  return $date_mod;
}

function iconForType($type)
{
  switch( strToLower( $type ) )
  {
  case "like":
  case "dislike":
  case "feature":
  case "bug":
    return '<img src="icons/' . strToLower( $type ) . '.png" width="16" height="16" alt="[' . messageForType( $type ) . ']" title="' . messageForType( $type ).'" />';
  default: return "";
  }
}

function iconForStatus($status, $id)
{
  switch( strToLower( $status ) )
  {
  case "new":
  case "confirmed":
  case "progress":
  case "solved":
  case "invalid":
    return '<img src="icons/' . strToLower( $status ) . '.png" id="status_comment_'
      . $id . '" width="16" height="16" title="' . messageForStatus( $status ) . '" />';
  default: return "";
  }
}

function messageForType( $type )
{
  if( in_array( strToLower( $type ), array("like", "dislike", "feature", "bug") ) )
  {
    return ucfirst( strToLower( $type ) );
  }
  return "Unknown type";
}

function messageForStatus($status)
{
  switch( strToLower( $status ) )
  {
  case "new": return "New";
  case "confirmed": return "Confirmed";
  case "progress": return "In progress";
  case "solved": return "Solved";
  case "invalid": return "Invalid";
  default: return "Unknown status";
  }
}

/**
 *  Generates pages navigation controls to use in lists of items
 *
 *  @param $url string
 *    The url to redirect to in navigation links
 *  @param $currentPage integer
 *    Current page number, defaults to 1
 *  @param $numItems integer
 *    The total number of items present in the list
 *  @param $itemsPerPage integer
 *    How many items to show per single page
 *  @return array
 *    Array with indices:
 *    'controls'    =>  the HTML of the controls,
 *    'page_start'  =>  the first item number, needed for db
 *              call's 'limit' parameter,
 *    'page_count'  =>  the number of items for db call's 'limit'
 *              parameter
 *    'current_page'  =>  current page number
 *    'showing_first' =>  First numeric item of the list
 *    'showing_last'  =>  Last numeric item of the list
 *    'showing_total' =>  Total items in the list
 */
function pageBrowser( $url, $currentPage, $numItems, $itemsPerPage )
{
  // Obtain some needed values
  $numPages = ceil( (float)$numItems / (float)$itemsPerPage );
  $numPages = ( $numPages < 1 ) ? 1 : $numPages;

  if( ! is_numeric( $currentPage ) )
  {
    $currentPage = 1;
  }

  $currentPage = ( $currentPage < 1 ) ? 1 : (int) $currentPage;
  $currentPage = ( $currentPage > $numPages ) ? $numPages : (int) $currentPage;

  $minPages = ($currentPage > 3) ? ($currentPage - 3) : 1;
  $maxPages = ($currentPage < $numPages - 3) ? ($currentPage + 3) : $numPages;

  $firstItem = ($currentPage - 1) * $itemsPerPage;
  $lastItem = $firstItem + $itemsPerPage;
  $lastItem = ($lastItem < $numItems) ? $lastItem : $numItems;

  // Make the destination url
  $queryChar = '?';
  if( strpos( $url, $queryChar ) !== false )
  {
    $queryChar = '&amp;';
  }

  // Then generate the links for the list of pages

  $pagesList = array();

  //     $pagesList[] = ( $currentPage > $minPages )
  //       ? ' <a href="' . $url . $queryChar . 'page=' . ($currentPage - 1) . '">' .
  //            ($currentPage - 1) . '</a> '
  //       : ' <span>' . ($currentPage - 1) . '</span> ';

  for( $idx = $minPages; $idx <= $maxPages; $idx++ )
    $pagesList[] = ( $idx == $currentPage )
      ? ' <span>' . $idx . '</span> '
      : ' <a href="' . $url . $queryChar . 'page=' . $idx . '">' .
           $idx . '</a> ';

  //     $pagesList[] = ( $currentPage != $maxPages )
  //       ? ' <a href="' . $url . $queryChar . 'page=' . ($currentPage + 1) . '">' .
  //           ($currentPage + 1) . '</a> '
  //       : ' <span>' . ($currentPage + 1) . '</span> ';

  // Now create the 'first' and 'last' pages links, if we need 'em

  $firstPage = ( $currentPage > 1 )
    ? ' <a href="' . $url . $queryChar . 'page=1">First</a> '
    : ' <span>First</span> ';

  $lastPage = ( $currentPage < $numPages )
    ? ' <a href="' . $url . $queryChar . 'page=' . ($numPages) . '">Last</a> '
    : ' <span>Last</span> ';


  // Lastly, make up the actual pages list and return it all to the caller.

  $navi = '<h4>Showing items from ' . ($firstItem + 1) . ' to ' . $lastItem . '</h4>' .
          '<p>' .
          $firstPage . ' &mdash; ' .
          implode( ' &middot; ', $pagesList ) .
          ' &mdash; ' . $lastPage .
          '</p>';

  return array(
    'navi' => $navi,
    'page_start' => $firstItem,
    'page_count' => $itemsPerPage,
    'page_current' => $currentPage,
    'page_total' => $numPages,
    'items_from' => $firstItem + 1,
    'items_to' => $lastItem,
  );
}

function getSmartyObject ( $developer )
{
  $smarty = new Smarty;

  $smarty->template_dir = 'templates';
  $smarty->compile_dir  = '/tmp';

  $smarty->assign( 'project', LIKEBACK_PROJECT );
  if( !is_null($developer) )
    $smarty->assign( 'developer', $developer );

  return $smarty;
}

// make this right for the 1.2 release
function sendMailTo ($type, $locale)
{
  $sendMailTo = "";
  $data = db_query("SELECT * FROM LikeBackDevelopers WHERE email!=''");
  while ($line = db_fetch_object($data)) {
    if (matchType($line->types, $type) && matchLocale($line->locales, $locale))
      $sendMailTo .= (empty($sendMailTo) ? "" : "; ") . $line->email;
  }
  return $sendMailTo;
}
