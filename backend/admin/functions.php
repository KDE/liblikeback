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

// Include Smarty (from the system)
@include_once('/usr/share/php/smarty/libs/Smarty.class.php');
// Include Smarty (from a local Smarty lib (inside 'admin/smarty')
@include_once('smarty/Smarty.class.php');
if( ! class_exists( 'Smarty' ) )
{
  die( 'Unable to load the Smarty template engine. Please, place it in the admin/smarty/ directory to use LikeBack.' );
}


// Cache for the resolutions
$_LikeBackResolutions = array();



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

function iconForStatus($status)
{
  if( !in_array( ucfirst( $status ), validStatuses() ) )
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

  return '<img src="icons/' . strToLower( $status ) . '.png" width="16" height="16" alt="'
    . messageForStatus( $ostatus ) . '" title="' . messageForStatus( $ostatus ) . '" />';
}


function iconForResolution( $resolution )
{
  $isNumber = ( (int) $resolution ) == $resolution;
  $resolutions = getResolutions();

  foreach( $resolutions as $item )
  {
    if( (   $isNumber && $item->id == $resolution )
    ||  ( ! $isNumber && $item->printable == $resolution ) )
    {
		$message = messageForResolution( $resolution );
		return '<img src="icons/' . htmlentities( $item->icon ) . '" width="16" height="16" alt="' . $message . '" title="' . $message . '" />';
    }
  }

  return "[icon not found for $resolution]";
}


function messageForResolution( $resolution )
{
  $isNumber = is_numeric( $resolution );
  $resolutions = getResolutions();

  foreach( $resolutions as $item )
  {
    if( (   $isNumber && $item->id == $resolution )
    ||  ( ! $isNumber && $item->printable == $resolution ) )
    {
		return $item->printable;
    }
  }

	if( $isNumber )
		return "Unknown resolution #$resolution";
	else
		return "$resolution (unknown)";
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
  case "triaged": return "Triaged";
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

  if( $minPages > 1 )
  {
    $pagesList[] = ' <span>...</span> ';
  }

  for( $idx = $minPages; $idx <= $maxPages; $idx++ )
    $pagesList[] = ( $idx == $currentPage )
      ? ' <span>' . $idx . '</span> '
      : ' <a href="' . $url . $queryChar . 'page=' . $idx . '">' .
           $idx . '</a> ';

  if( $maxPages < $numPages )
  {
    $pagesList[] = ' <span>...</span> ';
  }

  // Now create the 'first' and 'last' pages links, if we need 'em

  $firstPage = ( $currentPage > 1 )
    ? ' <a href="' . $url . $queryChar . 'page=1">First page</a> '
    : ' <span>First page</span> ';

  $lastPage = ( $currentPage < $numPages )
    ? ' <a href="' . $url . $queryChar . 'page=' . ($numPages) . '">Last page (' . $numPages . ')</a> '
    : ' <span>Last page (' . $numPages . ')</span> ';


  // Lastly, make up the actual pages list and return it all to the caller.

  $description = 'Showing items from ' . ($firstItem + 1) . ' to ' . $lastItem . ', out of '.$numItems.' items';
  $pager = $firstPage . ' | ' .
           implode( ' &middot; ', $pagesList ) .
           ' | ' . $lastPage;

  return array(
    'description' => $description,
    'pager' => $pager,
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
  $smarty->compile_dir  = 'templates/cache';

  $smarty->register_modifier( 'wrapQuote', 'smarty_modifier_wrapQuote' );
  $smarty->register_modifier( 'message',   'smarty_modifier_message' );

  $smarty->assign( 'project',  LIKEBACK_PROJECT );
  $smarty->assign( 'appLogo',  LIKEBACK_APP_LOGO );
  $smarty->assign( 'lbversion', LIKEBACK_VERSION );
  $smarty->assign( 'tracurl',   LIKEBACK_TRAC_URL );
  $smarty->assign( 'statuses', validStatuses() );
  $smarty->assign( 'types',    validTypes() );
  $smarty->assign( 'resolutions', validResolutions() );

  if( !$noDeveloper ) {
    $developer = getDeveloper();
    if( isset($developer) && $developer )
      $smarty->assign( 'developer', $developer );
  }

  return $smarty;
}

// Returns the current developer if someone is logged in
function getDeveloper() {
  global $developer, $likebackMail;

  // Just in case HTTP authentication isn't working or is turned off
  $nobody = new stdclass();
  $nobody->id        = 0;
  $nobody->login     = 'Nobody';
  $nobody->email     = $likebackMail;
  $nobody->lastvisit = null;
  $nobody->types     = null;
  $nobody->locales   = null;

  if( isset( $_SERVER['PHP_AUTH_USER'] ) )
    $userName = $_SERVER['PHP_AUTH_USER'];
  if( isset( $_SERVER['REMOTE_USER'] ) )
    $userName = $_SERVER['REMOTE_USER'];
  else
    $userName = $nobody->login;

  if( isset( $developer ) && $developer && $developer->login == $userName )
    return $developer;

  if( !isset( $userName ) || empty( $userName ) || $userName == $nobody->login ) {
    if( !LIKEBACK_PRODUCTION )
      echo "<!-- Tried to fetch developer but nobody was logged in! -->";
    return $nobody;
  }

  $data = db_query("SELECT * FROM LikeBackDevelopers WHERE login=? LIMIT 1", array( $userName ) );
  if( !$data ) {
    if( !LIKEBACK_PRODUCTION )
      echo "<!-- Couldn't retrieve developer information from database: " . mysql_error() . " -->";
    return $nobody;
  }

  $developer = db_fetch_object($data);
  if( $developer )
    return $developer;

  if( !db_query("INSERT INTO LikeBackDevelopers(login, types, locales) VALUES(?, 'Like;Dislike;Bug;Feature', '+*')", array( $userName ) ) )
  {
    if( !LIKEBACK_PRODUCTION )
      echo "<!-- Couldn't insert new developer $userName in database: " . mysql_error() . " -->";
    return $nobody;
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
  if( $forWhat != "status" && $forWhat != "type" && $forWhat != "resolution")
    return "invalid forWhat to Smarty message modifier (must be status, type or resolution)";

  if( $iconOrMessage != "message" && $iconOrMessage != "icon" && $iconOrMessage != "both" )
    return "invalid iconOrMessage to Smarty message modifier (must be message, icon or both)";

  if( $forWhat == "status" )
  {
    if( $iconOrMessage == "message" ) {
      return messageForStatus( $text );
    } elseif( $iconOrMessage == "icon" ) {
      return iconForStatus( $text );
    } else {
      return iconForStatus( $text ) . " " . messageForStatus( $text );
    }
  }
  else if( $forWhat == "type" )
  {
    if( $iconOrMessage == "message" ) {
      return messageForType( $text );
    } elseif( $iconOrMessage == "icon" ) {
      return iconForType( $text );
    } else {
      return iconForType( $text ) . " " . messageForType( $text );
    }
  }
  else
  {
    if( $iconOrMessage == "message" ) {
      return messageForResolution( $text );
    } elseif( $iconOrMessage == "icon" ) {
      return iconForResolution( $text );
    } else {
      return iconForResolution( $text ) . " " . messageForResolution( $text );
    }
  }
}


function likeback_mail( $to, $subject, $message, $headers )
{
  if( ! LIKEBACK_DEBUG )
  {
    return mail( $to, $subject, $message, $headers );
  }

  static $messageShown = false;
  if( ! $messageShown )
  {
    $messageShown = true;
    echo "<h5>Email sending: Debugging mode is on, no real emails will be sent!</h5>";
  }

  echo <<<DEBUG
<fieldset>
  <legend>Debuggging email</legend>
  <pre style="font-size: smaller;">
To: {$to}
Subject: {$subject}
---------------------
Headers:
{$headers}
---------------------
Message:
{$message}
</pre>
</fieldset>
DEBUG;
}


