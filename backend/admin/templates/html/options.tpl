  
  <div class="content">
   <form action="options.php" method="post">
    <div class="Options" style="padding: 5px">
     <fieldset>
      <legend><label for="email"><strong>Your e-mail address: </strong></label></legend>
      <input type="text" name="email" id="email" value="{$developer->email|escape}" style="width: 300px;" />
     </fieldset>
     <fieldset>
     <legend><strong>Receive e-mails when</strong> new comments matching those criteria are posted:</legend>
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
     </fieldset>
     <p style="text-align: center"><input type="submit" name="saveOptions" value="Save changes"/></p>
    </div>
   </form>
   <div class="Options" style="padding: 5px; margin-top: 12px; overflow: hidden;">
     <!-- <div style="float: left;"> -->
      <fieldset>
       <legend style="font-weight: bold;">Edit resolutions</legend>
       <table style="margin: 0 auto;">
        <thead>
         <tr>
          <th>Resolution</th>
          <th>Rename</th>
          <th>Change icon</th>
          <th>Delete</th>
         </tr>
        </thead>
        <tbody>
{section name=j loop=$resolutions}
         <tr>
          <td>{$resolutions[j]|message:'resolution':'both'}</td>
          {*on div: input is not allowed directly in form per specs *}
          <td><form method="post" action="chresolution.php"><div>
              <input type="text" name="newname" value="{$resolutions[j]|message:'resolution':'message'}"/>
              <input type="hidden" name="id" value="{$resolutions[j]}"/>
              <input type="submit" name="rename" value="Go"/>
              </div></form></td>
          <td><form method="post" action="chresolution.php"><div>
              <input type="text" name="newicon" value="{$resolutionIcons[j]|htmlentities}"/>
              <input type="hidden" name="id" value="{$resolutions[j]}"/>
              <input type="submit" name="reicon" value="Go"/>
              </div></form></td>
          <td style="text-align: center;">
            <a href="chresolution.php?id={$resolutions[j]}&amp;delete=1">
              <img src="icons/invalid.png" alt="Delete"/>
            </a>
          </td>
         </tr>
{sectionelse}
         <tr>
          <td colspan="3"><strong>Error: No known resolutions</strong></td>
         </tr>
{/section}
        </tbody>
      </table>
      </fieldset>
      <form method="post" action="chresolution.php">
       <fieldset>
        <legend style="font-weight: bold;">New resolution</legend>
        <table style="margin: 0 auto;">
          <thead>
           <tr>
            <th>New name</th>
            <th>New icon</th>
            <th></th>
           </tr>
          </thead>
          <tbody>
           <tr>
            <td><input type="text" name="newname" value=""/></td>
            <td><input type="text" name="newicon" value="solved.png"/></td>
            <td><input type="submit" name="new" value="Create"/></td>
           </tr>
          </tbody>
         </table>
        </fieldset>
      </form>
     <!-- </div> -->
   </div>

   <script type="text/javascript">
     document.getElementById("email").focus();
   </script>
