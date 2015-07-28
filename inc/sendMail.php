<?php 	
	$name = trim($_POST['name']);
	
	$email = trim($_POST['email']);
			
	if(function_exists('stripslashes')) {
		$message = stripslashes(trim($_POST['message']));
	} else {
		$message = trim($_POST['message']);
	}
		
	$emailTo = 'support@joaopedro.de';
	$subject = 'Contact Form Submission from '.$name;
	$sendCopy = trim($_POST['sendCopy']);
	$body = "Name: $name \n\nEmail: $email \n\nMessage: $message";
	$headers = 'From: Cotton Contact Form <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $email;
	
	mail($emailTo, $subject, $body, $headers);
	
	return true;
?>