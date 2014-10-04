<?php
return array(

	/*
	|--------------------------------------------------------------------------
	| Validation Messages
	|--------------------------------------------------------------------------
	|
	| The following are the default error messages used by the
	| validation class.
	| These messages may be easily changed to provide custom error
	| messages in your application. Error messages for custom validation
	| rules may also be added to this file.	
	|
	 */

	"required" 			=> "The {0} field is required.",
	"confirmed"			=> "The {0} confirmation does not match.",
	"accepted"			=> "The {0} must be accepted.",
	"same"				=> "The {0} and {1} must match.",
	"different"			=> "The {0} and {1} must be different.",
	"alpha" 			=> "The {0} field can only contain letters.",
	"alphaNumeric" 	=> "The {0} field can only contain letters and numbers.",
	"alphaNumericDash" => "The {0} may only contain letters, numbers, and dashes.",
	"numeric" 			=> "The {0} field can only contain numbers.",
	"integer"			=> "The {0} field must be an integer.",
	"size"				=> array(
		"numeric" => "The {0} must be {1}.",
		"string"  => "The {0} must be {1} characters.",
	),
	"between"			=> array(
		"numeric" => "The {0} must be between {1} - {2}.",
		"string"  => "The {0} must be between {1} - {2} characters.",
	),
	"min" 				=> array(
		"numeric" => "The {0} must be more than {1}.",
		"string"  => "The {0} must be more than {1} characters.",
	),
	"max"				=> array(
		"numeric" => "The {0} must be less than {1}.",
		"string"  => "The {0} must be less than {1} characters.",
	),
	"in"				=> "The selected {0} is invalid.",
	"notIn"				=> "The selected {0} is invalid.",
	"unique"			=> "The {0} has already been taken.",
	"exists"			=> "The selected {0} is invalid.",
	"ip"				=> "The {0} field must be a valid IP address.",
	"email"				=> "The {0} format is invalid.",
	"url"				=> "The {0} format is invalid.",
	"dateBefore"		=> "The {0} field is invalid",
	"dateAfter"		=> "The {0} field is invalid",
	"match"				=> "The {0} format is invalid.",
	"mimes"				=> "The {0} is not a valid file type"

);