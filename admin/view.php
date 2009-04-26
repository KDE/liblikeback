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

echo lbHeader();
$subBarContents = '<span id="loadingMessage">Loading...</span><span id="countMessage">Number of displayed comments: <strong id="commentCount">Unknown</strong></span>';
echo subBar( 'Options', $subBarContents );

echo '<div class="content">';

  if (isset($_GET['useSessionFilter']) && $_GET['useSessionFilter'] == "true" && isset( $_SESSION['postedFilter'] ) )
    $_POST = $_SESSION['postedFilter'];
  $_SESSION['postedFilter'] = $_POST;

  // Figure out if we are filtering or if it is the first time:
  $filtering = isset($_POST['filtering']);

  // Gather the versions and version filter
  $versionFilter = (isset($_POST["version"]) ? substr( $_POST["version"], 8) : ""); // TODO remove substr() for 1.2
  if( $versionFilter == "*" )
    $versionFilter = "";
  $versions = db_fetchAll("SELECT version FROM LikeBack WHERE version!='' GROUP BY version ORDER BY date DESC") or die(mysql_error());

  // Gather the locales and locale filter
  $localesFilter = array();
  $locales = db_fetchAll("SELECT locale FROM LikeBack WHERE locale!='' GROUP BY locale ORDER BY locale ASC") or die(mysql_error());
  foreach( $locales as $locale ) {
    if (!$filtering || isset($_POST["locale_".$locale->locale])) {
      $localesFilter[] = $locale->locale;
    }
  }

  // Gather the values for the status filters
  $statusFilter = array();
  $newSelect = "";
  $validStatuses = validStatuses();
  $dontFilterByDefaultStatuses = array( "Solved", "Invalid" );
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

  $smarty = getSmartyObject();
  $smarty->assign( 'versions', $versions );
  $smarty->assign( 'selectedVersion', $versionFilter );
  $smarty->assign( 'locales', $locales );
  $smarty->assign( 'localesFilter', $localesFilter );
  $smarty->assign( 'statusFilter', $statusFilter );
  $smarty->assign( 'typesFilter', $typesFilter );
  $smarty->assign( 'textValue', $textValue );
  
  $smarty->display( 'html/viewfilters.tpl' );

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

  $statusJS = "";
  foreach ($statusFilter as $status) {
    if (empty($statusJS)) {
      $statusJS      = "$status: true";
    } else {
      $statusJS      .= ", $status: true";
    }
  }

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
  $page = (isset($_GET['page']) ? $_GET['page'] : "");
  $pageInfo = pageBrowser( 'view.php?useSessionFilter=true',
                           $page,
                           $numResults,
                           50 );
  $page = $pageInfo['page_current'];

  echo '<div id="navi">' . $pageInfo['navi'] . "</div>\n";

  $data = db_query("SELECT   LikeBack.*, COUNT(LikeBackRemarks.id) AS remarkCount " .
                   "FROM     LikeBack LEFT JOIN LikeBackRemarks ON LikeBack.id=commentId " .
                   "WHERE    ".$conditional." ".
                   "GROUP BY LikeBack.id " .
                   "ORDER BY date DESC " .
                   "LIMIT    ".$pageInfo['page_start'].", ".$pageInfo['page_count'], $placeholders );


  $comments = array();
  while ($line = db_fetch_object($data)) {
    $line->date = strtotime( $line->date );

    $line->window   = preg_replace( "/->\s*$/", "", $line->window );

    $lastSeparation = strrpos( $line->window, '->' );
    if ($lastSeparation !== false)
      $line->window = '... ' . trim( substr( $line->window, $lastSeparation + 2 ) );

    $comments[]     = $line;
  }

  $smarty->assign( 'comments', $comments );
  $smarty->assign( 'page',     $page );
  $smarty->display( 'html/commenttable.tpl' );
?>
  <script type="text/javascript">
    document.getElementById("commentCount").innerHTML = <?php echo count($comments); ?>;
    document.getElementById("loadingMessage").style.display = "none"; // Hide the span "Loading..."
    document.getElementById("countMessage").style.display = "inline"; // Shown the span "Number of displayed comments: X"

    var shownStatus = { <?php echo $statusJS; ?> };
  </script>
<?php
$smarty->display( 'html/bottom.tpl' );
