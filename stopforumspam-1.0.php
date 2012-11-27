<?php
/* **********************

stopforumspam.php v 1.0
November 27 2012
bwise@knowem.com

This is a simple PHP class to test an IP, email, and/or username against
the stopforumspam.com database in order to determine if any of the three exist
there and are considered possibly spam.

Right now this script only checks for spam; I think this is an invaluable
service and I have plans to incorporate a form to SUBMIT spammer
information to their database.  First I am working on an algo to detect spammers
as they signup for your site and submit to the API automatically.

This class uses the stopforumspam.com API

*** YOU NEED TO SIGNUP FOR AN API KEY ***
Signup here: http://www.stopforumspam.com/signup

Detailed instructions for API usage:
http://www.stopforumspam.com/usage

********************** */

// CHANGE THESE 2 VARIABLES
$api_key = "";	// ENTER YOR API KEY HERE
$identification = "stopforumspam.php v 1.0 user";	// REPLACE THIS WITH YOUR WEBSITE ADDRESS

// THIS IS THE BASE URL FOR THE API, SHOULD STAY THE SAME
$base = "http://www.stopforumspam.com/api";

?>
<html>
<head>
	<title>Stopforumspam.com API useage via PHP</title>
</head>
<body>

<h3>Stopforumspam.com API useage via PHP</h3>
<?php

// CREATE INSTANCE
$StopSpamObject = new stopforumspam($api_key, $base, $identification);

	if ( isSet($_REQUEST["mode"]) && $_REQUEST["mode"] == "letsgo" ) {

		// NEED AT LEAST ONE ITEM TO TEST
		if ($_REQUEST['ip'] == "" && $_REQUEST['username'] == "" && $_REQUEST['email'] == "") {
			
			$error = "<p><b>You have to enter at least one of the 3 values: IP, username or Email</b></p>";

		} else {
		
			// SUBMITTING TO THE DATABASE TO TEST
			$error = "";
			echo $StopSpamObject->checkResults($ip, $username, $email);

		}

	}
	
	// DISPLAY THE USER SUBMISSION FORM
	$StopSpamObject->displayForm($error);

?>
</body>
</html>
<?php




class stopforumspam {
	var $api_key,
	$base,
	$identification;

	function __construct($api_key, $base, $identification) {
		$this->api_key = $api_key;
		$this->base = $base;
		$this->identification = $identification;
	}

	function checkResults($ip = null, $username = null, $email = null) {
			
		$url = $this->base . "?api_key=" . urlencode($this->api_key);
		$url .= ($ip != null) ? "&ip=" . urlencode($ip) : "";
		$url .= ($username != null) ? "&username=" . urlencode(iconv('GBK', 'UTF-8', $username)) : "";
		$url .= ($email != null) ? "&email=" . urlencode(iconv('GBK', 'UTF-8', $email)) : "";
		$url .= "&confidence";
		$url .= "&f=json";

		try {

			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_USERAGENT, "stopforumspam.php from " . urlencode(iconv('GBK', 'UTF-8', $identification)));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			$result = curl_exec($ch); 
			curl_close($ch);

			$parsed = json_decode($result, true);

/*
This is just a pretty display to see the results.  Ideally, you wouldn't display this info like this, you would automatically
integrate the function into your signup form and if you get a positive result with a good confidence score just block the signup.

For example, you can run a check like this:

	if ($parsed["username"]["appears"] == 1 && $parsed["username"]["confidence"] > 20) {
		return true;
	}
	
And then just block a signup like this:

	if ($StopSpamObject->checkResults($ip, $username, $email) == true) {
		// BLOCK SIGNUP
	}

You'll have to determine what confidence threshold is best for you so you don't block false positives.

*/
			
			if ($parsed["success"] == 0) {
				// ERROR
				$resultStr .= "<p><b>Stopforumspam.com has reported an error with this Request</b><br />";
				$resultStr .= "Request: " . $url . "<br />";
				$resultStr .= "<span style='color:red;'>Error: ". $parsed["error"] . "</span></p>";
			} else {
				$resultStr .= "<p><b>Request: </b>" . $url . "</p>";

				if ($parsed["username"]["appears"] == 1) {
					$resultStr .= "<p>Stopforumspam.com has the username <b>" . $username . "</b> in their database with a spam confidence score of <b>" . $parsed["username"]["confidence"] . "</b><br />";
					$resultStr .= "<b>Last Seen: </b>" . date("D M d, Y g:i a", strtotime($parsed["username"]["lastseen"])) . "<br />";
					$resultStr .= "<b>Frequency: </b>" . $parsed["username"]["frequency"] . "</p>";
				}
				if ($parsed["email"]["appears"] == 1) {
					$resultStr .= "<p>Stopforumspam.com has the email <b>" . $email . "</b> in their database with a spam confidence score of <b>" . $parsed["email"]["confidence"] . "</b><br />";
					$resultStr .= "<b>Last Seen: </b>" . date("D M d, Y g:i a", strtotime($parsed["email"]["lastseen"])) . "<br />";
					$resultStr .= "<b>Frequency: </b>" . $parsed["email"]["frequency"] . "</p>";
				}
				if ($parsed["ip"]["appears"] == 1) {
					$resultStr .= "<p>Stopforumspam.com has the IP address <b>" . $ip . "</b> in their database with a spam confidence score of <b>" . $parsed["ip"]["confidence"] . "</b><br />";
					$resultStr .= "<b>Last Seen: </b>" . date("D M d, Y g:i a", strtotime($parsed["ip"]["lastseen"])) . "<br />";
					$resultStr .= "<b>Frequency: </b>" . $parsed["ip"]["frequency"] . "</p>";
				}
			}

			return $resultStr;

		} catch (Exception $o) {
			return "We've got problems: " . $o->getMessage();

		}
	}


	function displayForm($error = null) {
?>
	<p>
		Only ONE of the 3 fields are required.  You can enter one, two, or all three for a report back
		from http://www.stopforumspam.com (who are awesome for making this project possible!)
	</p>

<?php
	if ($error != null) { echo $error; }
?>

	<p>
		<form action="stopforumspam.php" method="post">
	
		IP Address: <input type="text" name="ip" size="30" /><br />
		Username: <input type="text" name="username" size="30" /><br />
		Email: <input type="text" name="email" size="30" /><br />
	
		<br />
		<input type="hidden" name="mode" value="letsgo" />
		<input type="submit" name="Submit" value="Submit" />
	
		</form>
	</p>
<?php
	
	}

}

?>