<?php

require_once 'EmailExists.php';

$emails = array(
	'emailaddressgoes@here.okay',
	'emailaddressgoes@here.okay',
	'emailaddressgoes@here.okay',
	'emailaddressgoes@here.okay',
	'emailaddressgoes@here.okay'
);

$success = array();
$failed  = array();

foreach( $emails as $email) {
	$e = EmailExists::check($email);

	if( $e->passed()) {
		$success[] = $email;
		continue;
	}

	$failed[] = $email;
}

echo count($success) . ' emails are valid<br><br>';
echo count($failed) . ' emails are invalid';