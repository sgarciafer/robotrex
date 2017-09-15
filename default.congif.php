<?php

// This is where your private and secret keys from Bittrex are stored.
// Rename the file into config.php and add the key values.
// You are the only responsable for keeping those API keys secure and secret.

$key = '';
$secret = '';

// The time span in minutes to 'pack' orders together and analyse.
// The largest the slowest the reaction of the robot and the more time it get to load up enough data to operate.
// It needs 20 * $mode minutes to be ready.
$mode = '3';
