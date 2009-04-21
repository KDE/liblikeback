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

require_once("../locales_string.php");

$placeholders = array();

echo statusMenu();
echo lbHeader();
$subBarContents = '<span id="loadingMessage">Loading...</span><span id="countMessage">Number of displayed comments: <strong id="commentCount">Unknown</strong></span>';
echo subBar( 'Options', $subBarContents );

?>

  <div class="content">
    <a href="options.php" class="link">E-Mail Options...</a> <em>This should move somewhere else for the 1.2 release</em><br/><br/>

<?php

  if (isset($_GET['useSessionFilter']) && $_GET['useSessionFilter'] == "true" && isset( $_SESSION['postedFilter'] ) )
    $_POST = $_SESSION['postedFilter'];
  $_SESSION['postedFilter'] = $_POST;

  // Figure out if we are filtering or if it is the first time:
  $filtering = isset($_POST['filtering']);

  // Gather the versions and version filter
  $versionFilter = (isset($_POST["version"]) ? substr( $_POST["version"], 8) : ""); // TODO remove substr() for 1.2
  if( $versionFilter == "*" )
    $versionFilter = "";
  $versionQuery = db_query("SELECT version FROM LikeBack WHERE version!='' GROUP BY version ORDER BY date DESC") or die(mysql_error());
  $versions = array();
  while ($line = db_fetch_object($versionQuery)) {
    array_push( $versions, $line );
  }

  // Gather the locales and locale filter
  $localesFilter = array();
  $localeQuery = db_query("SELECT locale FROM LikeBack WHERE locale!='' GROUP BY locale ORDER BY locale ASC") or die(mysql_error());
  $locales = array();
  while ($line = db_fetch_object($localeQuery)) {
    array_push( $locales, $line );

    if (!$filtering || isset($_POST["locale_".$line->locale])) {
      $localesFilter[] = $line->locale;
    }
  }

  // Gather the values for the status filters
  $statusFilter = array();
  $newSelect = "";
  $validStatuses = array( "New", "Confirmed", "Progress", "Solved", "Invalid" );
  $dontFilterByDefaultStatuses = array( "Solved", "Invalid" );
  foreach( $validStatuses as $status )
  {
    if( (!$filtering && !in_array( $status, $dontFilterByDefaultStatuses) ) || isset( $_POST[$status] ) ) {
      $statusFilter[] = $status;
    }
  }

  // Gather the values for the types filters
  $validTypes = array( "Like", "Dislike", "Bug", "Feature" );
  $typesFilter = array();
  $likeSelect = "";
  foreach( $validTypes as $type ) {
    if( !$filtering || isset( $_POST[$type] ) )
      $typesFilter[] = $type;
  }

  $textFilter = "";
  $textValue  = "";
  if (isset($_POST['text'])) {
    if( get_magic_quotes_gpc() )
      $textFilter = stripslashes( $_POST['text'] );
    else
      $textFilter = $_POST['text'];
    $textValue  = ' value="'.htmlentities( $textFilter, ENT_QUOTES, 'UTF-8' ).'"';
  }

  $smarty = getSmartyObject( $developer );
  $smarty->assign( 'versions', $versions );
  $smarty->assign( 'selectedVersion', $versionFilter );
  $smarty->assign( 'locales', $locales );
  $smarty->assign( 'localesFilter', $localesFilter );
  $smarty->assign( 'statusFilter', $statusFilter );
  $smarty->assign( 'typesFilter', $typesFilter );
  $smarty->assign( 'textValue', $textValue );
  
  $smarty->display( 'html/viewfilters.tpl' );

  $request = "";

  // Filter version:
  if (!empty($versionFilter))
    $request .= " AND version='$versionFilter'";

  // Filter locales:
  $localesRequest = "";
  foreach ($localesFilter as $locale) {
    if (empty($localesRequest))
      $localesRequest = "locale='$locale'";
    else
      $localesRequest .= " OR locale='$locale'";
  }
  if (!empty($localesRequest))
    $request .= " AND ($localesRequest)";

  // Filter types:
  $typesRequest = "";
  foreach ($typesFilter as $type) {
    if (empty($typesRequest))
      $typesRequest = "type='$type'";
    else
      $typesRequest .= " OR type='$type'";
  }
  if (!empty($typesRequest))
    $request .= " AND ($typesRequest)";

  // Filter status:
  $statusRequest = "";
  $statusJS = "";
  foreach ($statusFilter as $status) {
    if (empty($statusRequest)) {
      $statusRequest = "status='$status'";
      $statusJS      = "$status: true";
    } else {
      $statusRequest .= " OR status='$status'";
      $statusJS      .= ", $status: true";
    }
  }
  if (!empty($statusRequest))
    $request .= " AND ($statusRequest)";

  // Filter text:
  if (!empty($textFilter))
  {
    $request .= " AND comment LIKE ?";
    array_push( $placeholders, "%$textFilter%" );
  }

  // Get the total number of results
  $data = db_query("SELECT   COUNT(*) AS count " .
                   "FROM     LikeBack " .
                   "WHERE    1=1$request ", $placeholders);
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
                   "WHERE    1=1$request ".
                   "GROUP BY LikeBack.id " .
                   "ORDER BY date DESC " .
                   "LIMIT {$pageInfo['page_start']}, {$pageInfo['page_count']}", $placeholders );


  $comments = array();
  while ($line = db_fetch_object($data)) {
    $line->date = strtotime( $line->date );

    $line->window   = preg_replace( "/->\s*$/", "", $line->window );

    $lastSeparation = strrpos( $line->window, '->' );
    if ($lastSeparation !== false)
      $line->window = '... ' . trim( substr( $line->window, $lastSeparation + 2 ) );

    array_push( $comments, $line );
  }

  $smarty->register_function( 'iconForType',   'smarty_iconForType'   );
  $smarty->register_function( 'iconForStatus', 'smarty_iconForStatus' );
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
