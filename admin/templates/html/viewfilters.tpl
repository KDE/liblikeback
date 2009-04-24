    <fieldset>
     <legend>Filtering options</legend>
     <form action="view.php" method="post">
     <div> {* todo make it a table or so *}
      <strong>Version:</strong>
        <select name="version">
{if empty( $selectedVersion ) }{assign var=selected value='selected="selected"'}{else}{assign var=selected value=""}{/if}
          <option value="version_*" {$selected}>(All)</option>
{section name=i loop=$versions}
{assign var=version value="`$versions[i]->version`"}
{if $version == $selectedVersion}{assign var=selected value='selected="selected"'}{else}{assign var=selected value=""}{/if}
          <option value="version_{$version|escape:quotes:'UTF-8'}" {$selected}>{$version|escape:html:'UTF-8'}</option>
{/section}
        </select><br/>
      <strong>Locale:</strong>
{section name=j loop=$locales}
{assign var=locale value="`$locales[j]->locale`"}
{assign var=arglocale value="locale_$locale"|escape:quotes:'UTF-8'}
{if in_array( $locale, $localesFilter )}{assign var=checked value='checked="checked"'}{else}{assign var=checked value=""}{/if}
        <label for="{$arglocale}"><input type="checkbox" id="{$arglocale}" name="{$arglocale}" {$checked}/>{$locale|escape:html:'UTF-8'}</label>
{/section}
        <br/>
      <strong>Status:</strong>
{if in_array( "New",       $statusFilter ) }{assign var=newSelect       value='checked="checked"'}{else}{assign var=checked value=""}{/if}
{if in_array( "Confirmed", $statusFilter ) }{assign var=confirmedSelect value='checked="checked"'}{else}{assign var=checked value=""}{/if}
{if in_array( "Progress",  $statusFilter ) }{assign var=progressSelect  value='checked="checked"'}{else}{assign var=checked value=""}{/if}
{if in_array( "Solved",    $statusFilter ) }{assign var=solvedSelect    value='checked="checked"'}{else}{assign var=checked value=""}{/if}
{if in_array( "Invalid",   $statusFilter ) }{assign var=invalidSelect   value='checked="checked"'}{else}{assign var=checked value=""}{/if}
        <label for="New"><input type="checkbox" id="New" name="New" {$newSelect}/>New</label>
        <label for="Confirmed"><input type="checkbox" id="Confirmed" name="Confirmed" {$confirmedSelect}/>Confirmed</label>
        <label for="Progress"><input type="checkbox" id="Progress" name="Progress" {$progressSelect}/>In progress</label>
        <label for="Solved"><input type="checkbox" id="Solved" name="Solved" {$solvedSelect}/>Solved</label>
        <label for="Invalid"><input type="checkbox" id="Invalid" name="Invalid" {$invalidSelect}/>Invalid</label>
        <br/>
      <strong>Type:</strong>
{if in_array( "Like",    $typesFilter ) }{assign var=likeSelect    value='checked="checked"'}{else}{assign var=checked value=""}{/if}
{if in_array( "Dislike", $typesFilter ) }{assign var=dislikeSelect value='checked="checked"'}{else}{assign var=checked value=""}{/if}
{if in_array( "Bug",     $typesFilter ) }{assign var=bugSelect     value='checked="checked"'}{else}{assign var=checked value=""}{/if}
{if in_array( "Feature", $typesFilter ) }{assign var=featureSelect value='checked="checked"'}{else}{assign var=checked value=""}{/if}
        <label for="Like"><input type="checkbox" id="Like" name="Like" {$likeSelect}/>Like</label>
        <label for="Dislike"><input type="checkbox" id="Dislike" name="Dislike" {$dislikeSelect}/>Do not like</label>
        <label for="Bug"><input type="checkbox" id="Bug" name="Bug" {$bugSelect}/>Bug</label>
        <label for="Feature"><input type="checkbox" id="Feature" name="Feature" {$featureSelect}/>Feature</label>
        <br/>
      <strong>Text:</strong>
        <input type="text" name="text" id="text" size="10" {$textValue}/>
        <br/>
      <input type="submit" name="filtering" value="Filter"/> &nbsp; &nbsp; <a href="view.php">Reset</a>
     </div>
     </form>
    </fieldset>

