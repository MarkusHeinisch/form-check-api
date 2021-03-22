<?php

ob_clean();
header_remove();

header("Content-type: application/json; charset=utf-8");

$code = 200;
$return = [
	"status" => "error",
	"message" => "Nothing happened",
	"errors" => null,
	//"data" => null
];

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType === "application/json") {

	//receive RAW post data
	$content = trim(file_get_contents("php://input"));

	//decoded can be used the same as you would use $_POST in $.ajax
	$decoded = json_decode($content, true);
	if(is_array($decoded) && json_last_error() == JSON_ERROR_NONE) {

		// NOTE: Sometimes for some reason I have to add the next line as well
    	// $decoded = json_decode($decoded, true);

		$errors = [];

		//input validation: tickets
		$tickets = isset($decoded["tickets"]) ? $decoded["tickets"] : '';
		$tickets = strip_tags(trim($tickets));
		if(empty($tickets)) {
			$errors["tickets"] = "This field is required";
		}

		//input validation: name
		$name = isset($decoded["name"]) ? $decoded["name"] : '';
		$name = strip_tags(trim($name));
		if(empty($name)) {
			$errors["name"] = "This field is required";
		}

		if($name != "foo") {
			$errors["name"] = "Something went wrong with username (maybe its not foo?)";
		}

		//input validation: email
		$email = isset($decoded["email"]) ? $decoded["email"] : '';
		$email = filter_var(strip_tags(trim($email)), FILTER_SANITIZE_EMAIL);
		if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors["email"] = "E-mail address is not valid";
		}

		if($email != "foo@bar.com") {
			$errors["email"] = "E-mail address is not valid (maybe its not foo@bar.com?)";
		}

		//input validation: message
		$message = isset($decoded["message"]) ? $decoded["message"] : '';
		$message = trim($message);

		//input validation: optin
		$optin = isset($decoded["optin"]) ? $decoded["optin"] : '';
		$optin = trim($optin);
		if(!$optin) {
			$errors["optin"] = "This field is required";
		}

		if($errors) {

			//input validation failed
			//$code = 403; //forbidden
			$return["errors"] = $errors;
			$return["message"] = "There was a problem with your submission, please try again.";
			$return["status"] = "error";


		} else {

			//send email to admin
			$recipient = "your@email.com";
			$subject = "Subject: New workshop registration";
			$email_content = "Name: $name\n";
			$email_content .= "Email: $email\n\n";
			$email_content .= "Subject: New workshop registration\n\n";
			$email_content .= "Message:\n$message\n\n";
			$email_headers = "From: $name <$email>";

			$mail = true;
			if($mail) {
			//if (mail($recipient, $subject, $email_content, $email_headers)) {

				//$code = 200; //okay
				//$return["data"] = $decoded;
				$return["status"] = "success";
				unset($return["errors"]);
				unset($return["message"]);

			} else {

				//$code = 500; //internal server error
				$return["message"] = "Oops! Something went wrong and we couldn\"t send your message";
				$return["status"] = "error";
				unset($return["errors"]);

			}
		}

	} else {
		//invalid json
		$return["message"] = 'Received JSON is improperly formatted';
	}

} else {
	// incorrect Content-Type
	$return["messsage"] = 'Content-Type is not set as "application/json"';
}

http_response_code($code);
echo json_encode($return);
exit();