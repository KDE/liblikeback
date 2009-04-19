<?php
/***************************************************************************
                          options.php - Likeback settings
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

  $title = "View Comment";
  include("header.php");
  require_once("../locales_string.php");

$smarty = getSmartyObject( $developer );

$likeChecked    = (matchType($developer->types, "Like")    ? 'checked="checked"' : "");
$dislikeChecked = (matchType($developer->types, "Dislike") ? 'checked="checked"' : "");
$bugChecked     = (matchType($developer->types, "Bug")     ? 'checked="checked"' : "");
$featureChecked = (matchType($developer->types, "Feature") ? 'checked="checked"' : "");

$smarty->assign( 'likeChecked',    $likeChecked    );
$smarty->assign( 'dislikeChecked', $dislikeChecked );
$smarty->assign( 'bugChecked',     $bugChecked     );
$smarty->assign( 'featureChecked', $featureChecked );

$localesquery = db_query("SELECT locale FROM LikeBack GROUP BY locale ORDER BY locale ASC") or die(mysql_error());
$locales = array();
while ($line = db_fetch_object($localesquery)) {
  $locale = htmlentities($line->locale);
  $checked = (matchLocale($developer->locales, $locale) ? " checked=\"checked\"" : "");

  array_push( $locales, $line );
}
$smarty->assign( 'locales', $locales );

$smarty->display( 'html/options.tpl' );

?>
   <script type="text/javascript">
     document.getElementById("email").focus();
   </script>
  </div>
 </body>
</html>
