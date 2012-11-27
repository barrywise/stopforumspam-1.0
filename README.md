stopforumspam-1.0

PHP class to check an IP, email or username against the stopforumspam.com database

stopforumspam.php v 1.0 November 27 2012

This is a simple PHP class to test an IP, email, and/or username against the stopforumspam.com database in order to determine if any of the three exist there and are considered possibly spam.

Right now this script only checks for spam; I think this is an invaluable service and I have plans to incorporate a form to SUBMIT spammer information to their database. First I am working on an algo to detect spammers as they signup for your site and submit to the API automatically.

This class uses the stopforumspam.com API

YOU NEED TO SIGNUP FOR AN API KEY

Signup here: http://www.stopforumspam.com/signup

Detailed instructions for API usage: http://www.stopforumspam.com/usage

QUICK START

Add your API key in this variable: $api_key = "";

Add your website URL or Name in this variable: $identification = "";

(This is used in CURL to let the stopforumspam.com folks know who you are)