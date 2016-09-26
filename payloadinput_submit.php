<?php

require 'payload.php';

function checkInput($keys) {
	foreach ($keys as $key) {
		if (empty($_POST[$key])) {
			die("" . ucfirst($key) . " must be set for config to be valid!");
		}
	}
}

function prettyXML($xml, $debug=false) {
  //http://www.devnetwork.net/viewtopic.php?f=29&t=40331
  //http://www.daveperrett.com/articles/2007/04/05/format-xml-with-php/
  // add marker linefeeds to aid the pretty-tokeniser
  // adds a linefeed between all tag-end boundaries
  $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

  // now pretty it up (indent the tags)
  $tok = strtok($xml, "\n");
  $formatted = ''; // holds pretty version as it is built
  $pad = 4; // initial indent
  $matches = array(); // returns from preg_matches()

  /* pre- and post- adjustments to the padding indent are made, so changes can be applied to
   * the current line or subsequent lines, or both
  */
  while($tok !== false) { // scan each line and adjust indent based on opening/closing tags

    // test for the various tag states
    if (preg_match('/.+<\/[^>]*>$/', $tok, $matches)) { // open and closing tags on same line
      if($debug) echo " =$tok= ";
      $indent = 0; // no change
    }
    else if (preg_match('/^<\//', $tok, $matches)) { // closing tag
      if($debug) echo " -$tok- ";
      $pad -= 4; //  outdent now
    }
    else if (preg_match('/^<[^>]*[^\/]>.*$/', $tok, $matches)) { // opening tag
      if($debug) echo " +$tok+ ";
      $indent = 4; // don't pad this one, only subsequent tags
    }
    else {
      if($debug) echo " !$tok! ";
      $indent = 0; // no indentation needed
    }
    
    // pad the line with the required number of leading spaces
    $prettyLine = str_pad($tok, strlen($tok)+$pad, ' ', STR_PAD_LEFT);
    $formatted .= $prettyLine . "\n"; // add to the cumulative result, with linefeed
    $tok = strtok("\n"); // get the next token
    $pad += $indent; // update the pad size for subsequent lines
  }
  return $formatted; // pretty format
}

checkInput(['description', 'email', 'name', 'username']);
$description = $_POST["description"];
$email = $_POST["email"];
$fullname = $_POST["name"];
$username = $_POST["username"];
$org = 'SomeOrg';
$file_name = $username . '_profile.mobileconfig';
$server_in = "some.server.com";
$server_out = $server_in;
$file_name = $username . '_mail.mobileconfig';

// Add headers to set content type to xml and download on submit

header("Content-Type: application/xml; charset=utf-8");
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: Attachment; filename=\"" . $file_name . "\"");

$payload = new Payload($org);
$identifier = $payload->getIdentifier();
$mail = new Mail($description, $fullname, $email, $username, $identifier, $server_in, $server_out, $org);
$payload->addPayloadContent($mail->getXML());
$xml = $payload->getXML()
?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
<?php echo prettyXML($xml, false);?>
</dict>
</plist>
