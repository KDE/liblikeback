  </div>
    {php}

      $debugQueries = db_debug_dump();
      if( LIKEBACK_DEBUG )
      {
        echo '<div class="debug"><h2>Performed database queries</h2><ul>';
        foreach( $debugQueries as $item )
        {
          echo "<li><pre>$item</li>";
        }
        echo '</ul></div>';
      }

    {/php}
 </body>
</html>
