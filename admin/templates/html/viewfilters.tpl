    <fieldset>
      <legend>Filtering options</legend>
      <strong>Version:</strong> {$versionString}<br/>
      <strong>Locale:</strong> {$localeString}<br/>
      <strong>Status:</strong>
        <label for="New"><input type="checkbox" id="New" name="New" {$newSelect}>New</label>
        <label for="Confirmed"><input type="checkbox" id="Confirmed" name="Confirmed" {$confirmedSelect}>Confirmed</label>
        <label for="Progress"><input type="checkbox" id="Progress" name="Progress" {$progressSelect}>In progress</label>
        <label for="Solved"><input type="checkbox" id="Solved" name="Solved" {$solvedSelect}>Solved</label>
        <label for="Invalid"><input type="checkbox" id="Invalid" name="Invalid" {$invalidSelect}>Invalid</label>
        <br>
      <strong>Type:</strong>
        <label for="Like"><input type="checkbox" id="Like" name="Like" {$likeSelect}>Like</label>
        <label for="Dislike"><input type="checkbox" id="Dislike" name="Dislike" {$dislikeSelect}>Do not like</label>
        <label for="Bug"><input type="checkbox" id="Bug" name="Bug" {$bugSelect}>Bug</label>
        <label for="Feature"><input type="checkbox" id="Feature" name="Feature" {$featureSelect}>Feature</label>
        <br/>
      <strong>Text:</strong>
        <input type="text" name="text" id="text" size="10" {$textValue}>
        <br/>
      <input type="submit" name="filtering" value="Filter"> &nbsp; &nbsp; <a href="view.php">Reset</a>
      </form>
    </fieldset>

