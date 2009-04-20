  
  <p class="header">
  </p>

  <div class="subBar Options">
   <a href="view.php?useSessionFilter=true"><img src="icons/gohome.png" width="32" height="32" alt=""></a> &nbsp; &nbsp;
   <strong><img src="icons/email.png" width="16" height="16" alt="" title="" /> E-Mail Options</strong> &nbsp; &nbsp; {$developer->login}
  </div>

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
        <strong>Type:</strong><br>
        <input type="checkbox" name="MatchLike" id="MatchLike" {$likeChecked}><label for="MatchLike">Like</label><br>
        <input type="checkbox" name="MatchDislike" id="MatchDislike" {$dislikeChecked}><label for="MatchDislike">Do not like</label><br>
        <input type="checkbox" name="MatchBug" id="MatchBug" {$bugChecked}><label for="MatchBug">Bug</label><br>
        <input type="checkbox" name="MatchFeature" id="MatchFeature" {$featureChecked}><label for="MatchFeature">Feature</label>
       </td>
       <td style="vertical-align: top">
        <strong>Locale:</strong><br>
{section name=i loop=$locales}
{assign var=locale value=`$locales[i]->locale`}
{if matchLocale($developer->locales, $locale)}{assign var=checked value='checked="checked"'}{else}{assign var=checked value=''}{/if}
        <input type="checkbox" name="MatchLocale_{$locale|escape}" id="MatchLocale_{$locale|escape}" {$checked}><label for="MatchLocale_{$locale|escape}">{$locale|escape}</label><br>
{/section}
{if matchLocale($developer->locales, "*")}{assign var=checked value='checked="checked"'}{else}{assign var=checked value=''}{/if}
        <input type="checkbox" name="MatchOtherLocales" id="MatchOtherLocales" {$checked}><label for="MatchOtherLocales">Others</label>
       </td>
      </tr>
     </table>
    </div>
    <p style="text-align: center"><input type="submit" name="saveOptions" value="Ok"></p>
   </form>
