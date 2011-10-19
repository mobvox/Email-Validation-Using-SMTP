<?php

set_time_limit(0);

// include SMTP Email Validation Class
require_once('mail/smtp_validateEmail.class.php');

header('Content-type: text/html; charset=utf-8');

// configures the search limit
$offset = '0';
$amount = '15000';

// connects to the database
$db = mysql_connect('localhost', 'root', 'root') or die(mysql_error());
mysql_select_db('emails_vcb', $db) or die(mysql_error());
mysql_query("set names 'utf8'");

// gets the data
$query = sprintf("SELECT * FROM emails WHERE valid = 'U' LIMIT %s,%s",
    $offset,
    $amount
    );
$base = mysql_query($query) or die(mysql_error());

function validateEmails($email) {
	// the email to validate
	$emails = array($email);
	// the sender
	$sender = 'vcbmobvox@vps1.mobvox.com.br';
	// instantiate the class
	$SMTP_Validator = new SMTP_validateEmail();
	// turn on debugging if you want to view the SMTP transaction
	$SMTP_Validator->debug = false;
	// do the validation
	$results = $SMTP_Validator->validate($emails, $sender);

	// get results
	foreach($results as $email=>$result) {
		if ($result) {
			$validation['valid'] = 'Y';
		} else {
			$validation['valid'] = 'N';
		}
	}
	$validation['error'] = $SMTP_Validator->error;
	return $validation;
}

while ($row = mysql_fetch_assoc($base)) {
	$query = sprintf("UPDATE emails SET valid='%s' where ID='%s'",
	    mysql_real_escape_string('V'),
	    mysql_real_escape_string($row['ID'])
	    );
    mysql_query($query) or die(mysql_error());

	$email = str_replace(' ', '', $row['email']);
	
	$validation = validateEmails($email);
	if($validation['error'] == 'Socket error') $validation['valid'] = 'N';
	
	$matches = explode(' ',$row['first_name']);

    $query = sprintf("UPDATE emails SET email='%s',first_name='%s',valid='%s',error='%s' where ID='%s'",
	    mysql_real_escape_string($email),
	    mysql_real_escape_string($matches[0]),
	    mysql_real_escape_string($validation['valid']),
	    mysql_real_escape_string($validation['error']),
	    mysql_real_escape_string($row['ID'])
	    );
    mysql_query($query) or die(mysql_error());
}
echo $amount . ' VALIDADOS!';