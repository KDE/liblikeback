#!/usr/bin/php
<?php
// Warning: This script is ran as nobody:nogroup, so we have no permissions here.
// We rely on the 'db.conf.php' file being readable, which is actually a bad thing.
// On the other hand, changing users is hard, because it requires the setuid bit
// *and* it requires us to know what user to change to.
// Update: Actually, we know the username: it's $USER in env.

ini_set( 'track_errors', 'On' );

// See /usr/include/sysexits.h
define( 'EX_DATAERR', 65 ); // "the input data was incorrect in some way"
define( 'EX_NOINPUT', 66 ); // "an input file did not exist or was not readable"
define( 'EX_UNAVAILABLE', 69); // "service is unavailable" (e.g. support file doesn't exist / isn't readable)

// Change directory to the LikeBack root
$lbpath = dirname( dirname( $_SERVER['argv'][0] ) );
if( !@chdir( $lbpath ) ) {
  echo "Error: Could not change directory to LikeBack (I think LikeBack is at $lbpath): $php_errormsg\n";
  exit( EX_UNAVAILABLE );
}

// Load configuration etc
require_once( 'db.php' );

// Analyse the e-mail
if( isset( $_ENV['LOCAL'] ) )
  $emailTo    = $_ENV['LOCAL'];
else
  $emailTo    = "";
$forComment = substr( $emailTo, strpos( $emailTo, '+' ) + 1 );
if( strpos( $emailTo, '+' ) == -1 or $forComment == "" ) {
  echo "Ignoring e-mail, not meant for a bug report\n";
  exit( 0 );
}
if( !is_numeric( $forComment ) ) {
  echo "Error: Could not decide what comment this e-mail was for - e-mail was sent to: $emailTo\n";
  exit( EX_DATAERR );
}
$comment = db_query( "SELECT * FROM `LikeBack` WHERE `id`=?", array( $forComment ) );
$comment = db_fetch_object( $comment );
if( !$comment ) {
  echo "Error: No comment found with ID: $forComment\n";
  exit( EX_NOINPUT );
}

// Read the headers
$headers = 1;
$instructions = 0;
unset( $setStatusTo );
$newRemark = "";

$responseTop = "Hello,\r\n\r\nThis is the LikeBack e-mail gateway responder.";
$responseModify = "";

$fh = fopen( "php://stdin", "r" );

$email = "";
while( $line = fgets( $fh ) ) {
  $line = str_replace( "\r", "", $line );
  $line = str_replace( "\n", "", $line );

  if( $line == "" && $headers ) {
    $headers = 0;
    continue;
  }

  // handle
  if( !$headers ) {
    if( $instructions == 1 ) {
      $responseModify .= "> $line\r\n";
      $parts = explode(" ", strToLower( $line ) );
      if( strToLower( $line ) == "thanks" )
      {
        $instructions = -1;
        $responseModify .= "Stopped processing here.\r\n";
      }
      else if( $parts[0] == "set" )
      {
        if( $parts[1] != "status" ) {
          $responseModify .= "Invalid syntax: set status [to] <status>.\r\n";
          continue;
        }

        if( $parts[2] == "to" && isset( $parts[3] ) )
          $setStatusTo = $parts[3];
        else if( isset( $parts[2] ) )
          $setStatusTo = $parts[2];
        else {
          $responseModify .= "Invalid syntax: set status [to] <status>.\r\n";
          continue;
        }

        $responseModify .= "Setting status of comment ".$comment->id." to $setStatusTo.\r\n";
      }
      else
      {
        $responseModify .= "Didn't understand this command, sorry.\r\n";
      }
      continue;
    }

    if( strToLower( $line ) == "please" && $instructions != -1 ) { 
      $responseModify .= "> $line\r\n";
      $responseModify .= "Started processing.\r\n";
      $instructions    = 1;
      continue;
    }

    // It's a regular line, not to be parsed by us, so it's part of the new remark
    $newRemark .= $line."\r\n";
  }
}

$newRemark  = trim( $newRemark );

$responseRemark = "Added your new remark to the comment:";
$niceRemark = wordwrap( $newRemark, 73 );
$niceRemark = '> ' . str_replace( "\n", "\n> ", $niceRemark );
$responseBottom = "Thank you,\r\nLikeBack";

$subject   = "Response to your LikeBack e-mail";
$response  = $responseTop . "\r\n\r\n" . $responseModify . "\r\n" . $responseRemark . "\r\n" . $niceRemark . "\r\n\r\n" . $responseBottom;
$from      = $likebackMail;
if( isset( $_ENV['RECIPIENT'] ) )
  $replyto = $_ENV['RECIPIENT'];
else
  $replyto = $likebackMail;
if( isset( $_ENV['SENDER'] ) )
  $to      = $_ENV['SENDER'] ;
else
  $to      = $likebackMail; // todo an admin of some kind?

$response = wordwrap( $response, 80 );

$headers = "From: $from\r\n" .
  "Reply-To: $replyto\r\n" .
  "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
  "X-Mailer: Likeback/" . LIKEBACK_VERSION . " using PHP/" . phpversion();

mail($to, $subject, $response, $headers);
exit( 0 );
?>
