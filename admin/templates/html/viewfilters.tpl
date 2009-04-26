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
{section name=k loop=$statuses}
{assign var=status value="$statuses[k]"}
{if in_array( $status, $statusFilter ) }{assign var=checked value='checked="checked"'}{else}{assign var=checked value=""}{/if}
        <label for="{$status}"><input type="checkbox" id="{$status}" name="{$status}" {$checked}/>{$status|message:'status'}</label>
{/section}
        <br/>
      <strong>Type:</strong>
{section name=l loop=$types}
{assign var=type value="$types[l]"}
{if in_array( $type, $typesFilter ) }{assign var=checked value='checked="checked"'}{else}{assign var=checked value=""}{/if}
        <label for="{$type}"><input type="checkbox" id="{$type}" name="{$type}" {$checked}/>{$type|message:'type'}</label>
{/section}
        <br/>
      <strong>Text:</strong>
        <input type="text" name="text" id="text" size="10" {$textValue}/>
        <br/>
      <input type="submit" name="filtering" value="Filter"/> &nbsp; &nbsp; <a href="view.php">Reset</a>
     </div>
     </form>
    </fieldset>

