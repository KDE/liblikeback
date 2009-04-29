  
  <div class="content">
   <form action="options.php" method="post">
    <p class="Options" style="padding: 5px">
     <label for="email"><strong>Your e-mail address: </strong></label><input type="text" name="email" id="email" value="{$developer->email|escape}">
    </p>
    <div class="Options" style="padding: 5px">
     <p style="margin: 0"><strong>Receive e-mails when</strong> new comments matching those criteria are posted:</p>
     <table>
      <tr>
       <td style="vertical-align: top">
        <strong>Type:</strong><br/>
        <input type="checkbox" name="MatchLike" id="MatchLike" {$likeChecked}/><label for="MatchLike">{"Like"|message:'type':'both'}</label><br/>
        <input type="checkbox" name="MatchDislike" id="MatchDislike" {$dislikeChecked}/><label for="MatchDislike">{"Dislike"|message:'type':'both'}</label><br/>
        <input type="checkbox" name="MatchBug" id="MatchBug" {$bugChecked}/><label for="MatchBug">{"Bug"|message:'type':'both'}</label><br/>
        <input type="checkbox" name="MatchFeature" id="MatchFeature" {$featureChecked}/><label for="MatchFeature">{"Feature"|message:'type':'both'}</label>
       </td>
       <td style="vertical-align: top; padding-left: 15px;">
        <strong>Locale:</strong><br/>
{if matchLocale($developer->locales, "*")}{assign var=checked value='checked="checked"'}{else}{assign var=checked value=''}{/if}
        <input type="checkbox" name="MatchOtherLocales" id="MatchOtherLocales" {$checked}/><label for="MatchOtherLocales">All others</label><br/>
        <hr/>
{section name=i loop=$locales}
{assign var=locale value="`$locales[i]->locale`"|escape:'html':'utf-8'}
{if matchLocale($developer->locales, $locale)}{assign var=checked value='checked="checked"'}{else}{assign var=checked value=''}{/if}
        <input type="checkbox" name="MatchLocale_{$locale}" id="MatchLocale_{$locale}" {$checked}/><label for="MatchLocale_{$locale}">{$locale}</label><br/>
{/section}
       </td>
      </tr>
     </table>
    </div>
    <p style="text-align: center"><input type="submit" name="saveOptions" value="Ok"/></p>
   </form>

   <script type="text/javascript">
     document.getElementById("email").focus();
   </script>

