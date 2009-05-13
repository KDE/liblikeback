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

if( !isset($noadmin) or !$noadmin )
  require_once("../functions.inc.php");

// Include Smarty
include_once('/usr/share/php/smarty/libs/Smarty.class.php');

function smarty_iconForType( $params, &$smarty)
{
  if( !isset($params['type']) or empty($params['type']) )
    return "";
  return iconForType( $params['type'] );
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

function smarty_iconForStatus( $params, &$smarty )
{
  if( !isset($params['id'])    or empty($params['id'])
  or !isset($params['status']) or empty($params['status']) )
    return "";
  return iconForStatus( $params['status'], $params['id'] );
}

function iconForStatus($status, $id = -1)
{
  if( $id != -1 )
    $id = 'id="status_comment_'.$id.'"';
  else
    $id = '';

  if( !in_array( $status, validStatuses() ) )
    return "";

  $ostatus = $status;
  switch( strToLower( $status ) )
  {
  case "thanks":
    $status = "solved";
    break;
  case "wontfix":
    $status = "invalid";
    break;
  case "closed":
    $status = "solved";
    break;
  }

  return '<img src="icons/' . strToLower( $status ) . '.png" '. $id . ' width="16" height="16" alt="'
    . messageForStatus( $ostatus ) . '" title="' . messageForStatus( $ostatus ) . '" />';
}

function messageForType( $type )
{
  if( in_array( $type, validTypes() ) )
  {
    return ucfirst( strToLower( $type ) );
  }
  if( ! LIKEBACK_PRODUCTION )
    echo "<!-- Warning: messageForType( $type ) == unknown type! -->";
  return "Unknown type";
}

function messageForStatus($status)
{
  switch( strToLower( $status ) )
  {
  case "new": return "New";
  case "confirmed": return "Confirmed";
  case "progress": return "In progress";
  case "thanks": return "Thanks";
  case "solved": return "Solved";
  case "wontfix": return "Won't fix";
  case "invalid": return "Invalid";
  case "closed": return "Closed";
  default: 
    if( ! LIKEBACK_PRODUCTION )
      echo "<!-- Warning: messageForStatus( $status ) == unknown status! -->";
    return "Unknown status";
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

  $navi = '<h4>Showing items from ' . ($firstItem + 1) . ' to ' . $lastItem . ', out of '.$numItems.' items</h4>' .
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

function getSmartyObject ( $noDeveloper = false )
{
  $smarty = new Smarty;

  $smarty->template_dir = 'templates';
  $smarty->compile_dir  = '/tmp';
  
  $smarty->register_function( 'iconForType',    'smarty_iconForType'   );
  $smarty->register_function( 'iconForStatus',  'smarty_iconForStatus' );

  $smarty->register_modifier( 'wrapQuote', 'smarty_modifier_wrapQuote' );
  $smarty->register_modifier( 'message',   'smarty_modifier_message' );

  $smarty->assign( 'project',  LIKEBACK_PROJECT );
  $smarty->assign( 'appLogo',  LIKEBACK_APP_LOGO );
  $smarty->assign( 'lbversion', LIKEBACK_VERSION );
  $smarty->assign( 'statuses', validStatuses() );
  $smarty->assign( 'types',    validTypes() );

  if( !$noDeveloper ) {
    $developer = getDeveloper();
    if( isset($developer) && $developer )
      $smarty->assign( 'developer', $developer );
  }

  return $smarty;
}

// Returns the current developer if someone is logged in
function getDeveloper() {
  global $developer;

  if( isset( $_SERVER['PHP_AUTH_USER'] ) )
    $userName = $_SERVER['PHP_AUTH_USER'];

  if( isset( $developer ) && $developer && $developer->login == $userName)
    return $developer;

  if( !isset( $userName ) || empty( $userName ) ) {
    if( !LIKEBACK_PRODUCTION )
      echo "<!-- Tried to fetch developer but nobody was logged in! -->";
    return FALSE;
  }

  $data = db_query("SELECT * FROM LikeBackDevelopers WHERE login=? LIMIT 1", array( $userName ) );
  if( !$data ) {
    if( !LIKEBACK_PRODUCTION )
      echo "<!-- Couldn't retrieve developer information from database: " . mysql_error() . " -->";
    return FALSE;
  }

  $developer = db_fetch_object($data);
  if( $developer )
    return $developer;

  if( !db_query("INSERT INTO LikeBackDevelopers(login, types, locales) VALUES(?, 'Like;Dislike;Bug;Feature', '+*')", array( $userName ) ) )
  {
    if( !LIKEBACK_PRODUCTION )
      echo "<!-- Couldn't insert new developer $userName in database: " . mysql_error() . " -->";
    return FALSE;
  }
  return getDeveloper();
}

// We're doing 75 because of an apparant bug in wordwrap(), 
// during testing it wrapped lines of exactly 80 characters even if the
// second parameter was 80; this was not reproducable so the fix for now is
// to let wrapQuote wrap 75 characters.
function smarty_modifier_wrapQuote ( $text, $length = 75, $prepend = '> ' )
{
  // Remove any \r
  $text = str_replace( "\r", "", $text );

  // Word wrap it
  $text = wordwrap( $text, $length - strlen($prepend) );

  // Prepend every line with $prepend
  $text = $prepend . str_replace( "\n", "\n".$prepend, $text );

  return $text;
}

function smarty_modifier_message( $text, $forWhat, $iconOrMessage = "message" )
{
  if( $forWhat != "status" && $forWhat != "type" )
    return "invalid forWhat to Smarty message modifier (must be status or type)";
  
  if( $iconOrMessage != "message" && $iconOrMessage != "icon" && $iconOrMessage != "both" )
    return "invalid iconOrMessage to Smarty message modifier (must be message, icon or both)";

  if( $forWhat == "status" )
    if( $iconOrMessage == "message" ) {
      return messageForStatus( $text );
    } elseif( $iconOrMessage == "icon" ) {
      return iconForStatus( $text );
    } else {
      return iconForStatus( $text ) . " " . messageForStatus( $text );
    }
  else
    if( $iconOrMessage == "message" ) {
      return messageForType( $text );
    } elseif( $iconOrMessage == "icon" ) {
      return iconForType( $text );
    } else {
      return iconForType( $text ) . " " . messageForType( $text );
    }
}
