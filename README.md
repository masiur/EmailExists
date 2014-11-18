EmailExists
===========

A simple PHP class that checks if an email address exists on the server. The class simply SMTP's into the server of the email to be verified and checks if the email's mailbox exists.


How to Use
------------
```
	$email = 'emailtocheck@coolmail.com';
	$email = EmailExists::check($email);

	if( $email->passed()) {

		echo 'This email is real<br>';
		echo 'Because EmailExists said: <strong>' . $email->messages()->valid . '</strong>';
	} else {

		echo 'This email was not found, OUCH!!!<br>';
		echo 'Because EmailExists: <strong>' . $email->messages()->invalid . '</strong>';
	}
```

The `check` method of the `EmailExists` class checks if the email exists and then returns the instance of the class which returns the following methods `failed`, `passed`, `messages`, `toJSON`


### failed method
Returns true if the email does not exist and false if it was found.

### passed method
Returns true if the email exists and false otherwise

### messages method
Returns an `StdClass` class with two attributes either a `valid` or `invalid` property and a status property with either true or false as its value.

### toJSON method
Returns a JSON representation of the messages StdClass


Notes:
------
This method might not work on some servers as some companies deny this request to prevent spammers from stealing their users email.