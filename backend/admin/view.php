<?php
/***************************************************************************
                          view.php - View a (filtered) list of comments
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

$title = "Comment List";
include("header.php");


  if (isset($_GET['useSessionFilter']) && $_GET['useSessionFilter'] == "true" && isset( $_SESSION['postedFilter'] ) )
    $_POST = $_SESSION['postedFilter'];
  $_SESSION['postedFilter'] = $_POST;

  // Figure out if we are filtering or if it is the first time:
  $filtering = isset($_POST['filtering']);

  // Retrieve the pager settings
  $pagerCount = (isset($_POST["pagerCount"]) ? maybeStrip( $_POST["pagerCount"] ) : 50);

  // Gather the versions and version filter
  $versionFilter = (isset($_POST["version"]) ? maybeStrip( $_POST["version"] ) : "");

  // remove version_
  if( substr( $versionFilter, 0, 8 ) == "version_" )
    $versionFilter = substr( $versionFilter, 8 );

  if( $versionFilter == "*" )
    $versionFilter = "";

  $versions = db_fetchAll("SELECT version FROM LikeBack WHERE version!='' GROUP BY version ORDER BY date DESC");


  // Gather the locales and locale filter
  $localesFilter = array();
  $locales = db_fetchAll("SELECT locale FROM LikeBack WHERE locale!='' GROUP BY locale ORDER BY locale ASC");
  foreach( $locales as $locale ) {
    if (!$filtering || isset($_POST["locale_".$locale->locale])) {
      $localesFilter[] = $locale->locale;
    }
  }

  // Gather the values for the status filters
  $statusFilter = array();
  $newSelect = "";
  $validStatuses = validStatuses();
  $dontFilterByDefaultStatuses = validDoneStatuses();
  foreach( $validStatuses as $status )
  {
    if( (!$filtering && !in_array( $status, $dontFilterByDefaultStatuses) ) || isset( $_POST[$status] ) ) {
      $statusFilter[] = $status;
    }
  }

  // Gather the values for the types filters
  $validTypes = validTypes();
  $typesFilter = array();
  $likeSelect = "";
  foreach( $validTypes as $type ) {
    if( !$filtering || isset( $_POST[$type] ) )
      $typesFilter[] = $type;
  }

  $textFilter = "";
  $textValue  = "";
  if (isset($_POST['text'])) {
    $textFilter = maybeStrip( $_POST['text'] );
    $textValue  = ' value="'.htmlentities( $textFilter, ENT_QUOTES, 'UTF-8' ).'"';
  }

  $smarty->assign( 'versions', $versions );
  $smarty->assign( 'selectedVersion', $versionFilter );
  $smarty->assign( 'locales', $locales );
  $smarty->assign( 'localesFilter', $localesFilter );
  $smarty->assign( 'statusFilter', $statusFilter );
  $smarty->assign( 'typesFilter', $typesFilter );
  $smarty->assign( 'textValue', $textValue );

  $conditional = '1+1';
  $placeholders = array();

  // Filter version:
  if (!empty($versionFilter)) {
    $conditional .= ' AND version=?';
    array_push( $placeholders, $versionFilter );
  }

  // Filter locales:
  $buildQuery    = db_buildQuery_checkArray( 'locale', $localesFilter );
  $conditional  .= ' AND ' . array_shift( $buildQuery );
  $placeholders  = array_merge( $placeholders, $buildQuery );

  // Filter types:
  $buildQuery    = db_buildQuery_checkArray( 'type', $typesFilter );
  $conditional  .= ' AND ' . array_shift( $buildQuery );
  $placeholders  = array_merge( $placeholders, $buildQuery );

  // Filter status:
  $buildQuery    = db_buildQuery_checkArray( 'status', $statusFilter );
  $conditional  .= ' AND ' . array_shift( $buildQuery );
  $placeholders  = array_merge( $placeholders, $buildQuery );

  // Filter text:
  if (!empty($textFilter))
  {
    $conditional .= " AND comment LIKE ?";
    $placeholders[] = "%$textFilter%";
  }

  // Get the total number of results
  $data = db_query("SELECT   COUNT(*) AS count " .
                   "FROM     LikeBack " .
                   "WHERE    ".$conditional, $placeholders);
  $numResults = db_fetch_object($data);
  $numResults = $numResults->count;

  // Show the pager
  $page = (isset($_GET['page']) ? maybeStrip( $_GET['page'] ) : "");
  $pageInfo = pageBrowser( 'view.php?useSessionFilter=true',
                           $page,
                           $numResults,
                           $pagerCount );
  $page = $pageInfo['page_current'];

  $data = db_query("SELECT   LikeBack.*, COUNT(LikeBackRemarks.id) AS remarkCount " .
                   "FROM     LikeBack LEFT JOIN LikeBackRemarks ON LikeBack.id=commentId " .
                   "WHERE    ".$conditional." ".
                   "GROUP BY LikeBack.id " .
                   "ORDER BY date DESC " .
                   "LIMIT    ".$pageInfo['page_start'].", ".$pageInfo['page_count'], $placeholders );


  $comments = array();
  $oldid    = -1;
  while ($line = db_fetch_object($data))
  {
    # add an <a name> for every skipped ID so #comment_n scrolls to the right position even if
    # it's been removed from the list
    $aname = "";
    if( $oldid != -1 )
    {
      $diff  = $line->id - $oldid;
      while( $diff > 1 ) {
        $aname .= '<a name="comment_' . ($oldid + --$diff) . '"></a>';
      }
      while( $diff < -1 ) {
        $aname .= '<a name="comment_' . ($oldid + ++$diff) . '"></a>';
      }
    }
    $oldid = $line->id;
    $line->aname = $aname;

    $line->date = strtotime( $line->date );

    // Fix the encoding of the comments
    $line->comment = utf8_decode( stripslashes( $line->comment ) );

    $line->window   = preg_replace( "/->\s*$/", "", $line->window );

    $lastSeparation = strrpos( $line->window, '->' );
    if ($lastSeparation !== false)
      $line->window = '... ' . trim( substr( $line->window, $lastSeparation + 2 ) );

    $comments[]     = $line;
  }

$smarty->display( 'html/lbheader.tpl' );
// $subBarContents = '<span id="countMessage">Number of displayed comments: <strong id="commentCount">' . count($comments). '</strong></span>';
$smarty->assign( 'subBarType',     'Options' );
$smarty->assign( 'isHome',         true );
$smarty->assign( 'subBarContents', $pageInfo['description'] );
$smarty->display( 'html/lbsubbar.tpl' );

$smarty->assign( 'pagerChoices', array( 10, 25, 50, 100, 500 ) );
$smarty->assign( 'pagerSelection', $pageInfo['page_count'] );
$smarty->display( 'html/viewfilters.tpl' );

$smarty->assign( 'comments',           $comments );
$smarty->assign( 'page',               $page );
$smarty->assign( 'pager',              $pageInfo['pager'] );
$smarty->assign( 'showEditingOptions', true );
$smarty->display( 'html/commenttable.tpl' );
$smarty->display( 'html/bottom.tpl' );
