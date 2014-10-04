<?php
namespace Framework {
	

	use Framework\StringMethods as StringMethods;
	use Framework\RequestMethods as RequestMethods;

	/**
	 * Validate
	 * Static helper to validate data against a given rule. Contains a 
	 * number of preset rules but can also take custom rules
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 * @todo Active URL validator (if really needed)
	 */
	
	class Validate {

		/**
		 * The array being validated
		 * @var array 
		 */
		
		public $fields = array();
		/**
		 * The post-validation errors
		 * @var array 
		 */
		
		public $errors = array();
		/**
		 * A count of the number of errors
		 * @var integer
		 */
		public $numErrors;

		/**
		 * The validation rules
		 * @var array 
		 */
		protected $_rules = array();

		/**
		 * The validation messages
		 * @var array  
		 */
		protected $_messages = array(); 

		/**
		 * The numeric validation rules
		 * @var array
		 */
		protected $_numericRules = array('numeric', 'integer','size');

		/**
		 * The size validation rules
		 * @var array
		 */
		protected $_sizeRules = array('size', 'between', 'min', 'max');

		/**
		 * Registered custom validators
		 * @var array
		 */
		protected static $_validators = array();

		/**
		 * Create a new validate instance
		 * @param mixed $fields   Field(s) to be validated
		 * @param array $rules    Rules to be used for validation
		 * @param array $messages Custom error messages
		 * @return void
		 */
		public function __construct($fields, $rules, $messages = array()) {

			if (is_object($fields)) {
				foreach ($rules as $key => $value){
					$this->fields[$key] = $fields->{"get".ucfirst($key)}();
				}
			} else {
				$this->fields = $fields;
			}

			$this->_rules = $rules; // Array of rules
			$this->_messages = $messages; // Custom error messages
			
		}

		/**
		 * Create a new validate instance
		 * @param mixed $fields   Field(s) to be validated
		 * @param array $rules    Rules to be used for validation
		 * @param array $messages Custom error messages
		 * @return Validate
		 */
		public static function run($fields, $rules, $messages = array()) {

			return new static($fields, $rules, $messages);
		}

		/**
		 * Register a custom validator
		 * @param  string  $name      Validator to be registered
		 * @param  Closure $validator Test for validating
		 * @return void
		 */
		public static function register($name, $validator) {

			static::$_validators[$name] = $validator;
		}


		/**
		 * Valid target array using given validation rules
		 * @return bool 
		 */
		public function invalid() {

			return ! $this->valid();
		}

		/**
		 * Valid target array using given validation rules
		 * @return bool
		 */
		public function valid() {

			// Each set of tests
			foreach ($this->_rules as $field => $rules) {
				// Each individual test
				foreach ($rules as $rule) {
					$this->_test($field, $rule);
				}
			}
			$this->_messages = array();
			return count($this->numErrors) == 0;
		}

		/**
		 * Evaluate a field against a validation rule
		 * @param  string $field Field to be validates
		 * @param  string $rule  Rule to be validated against
		 * @return void        
		 */
		protected function _test($field, $rule) {

			list($rule, $parameters) = $this->_parseRule($rule);

			$value = $this->fields[$field];

			// Check there is something to validate
			$validatable = $this->_validatable($rule, $field, $value);

			// Run validate function and if invalid set error message
			$function = "_validate".ucfirst($rule);

			if ($validatable and !$this->$function($field, $value, $parameters, $this)) {
				
				// Determine if there is a custom message for this error
				$message = $this->message($field, $rule);

				if (is_array($message) && in_array($rule, $this->_sizeRules)) {
					if ($this->_hasRule($field, $this->_numericRules)){
						$message = $message['numeric'];
					} else {
						$message = $message['string'];
					}
				}


				// Replace any placeholders in error messages 
				$replacements = array_merge(array($field), $parameters);
				foreach ($replacements as $i => $replacement) {

					$message = str_replace("{{$i}}", $replacement, $message);
				}

				if (!isset($this->errors[$field])) {
					$this->_errors[$field] = array();
				}

				$this->errors[$field][] = $message;
				$this->numErrors = count($this->errors);
			}
		}

		/**
		 * Determine the proper error message for a give field or rule
		 * @param string $field The field being validated
		 * @param string $rule 	The rule being applied to the field
		 * @return string 		The correct error message for the attribute/rule
		 */
		protected function message($field, $rule) {

			// Check for an attribute specific message
			$custom = $field . "_" . $rule;

			if (array_key_exists($custom, $this->_messages)) {
				return $this->_messages[$custom];
			} else if (Configuration::get("validation.".$custom)) {
				return Configuration::get("validation.".$custom);
			}
			
			// Check for a rule specific message
			if (array_key_exists($rule, $this->_messages)) {

				return $this->_messages[$rule];
			}

			// Resort to application wide config validation messages 
			return Configuration::get("validation.".$rule);
		}

		/**
		 * To be considered validatable, the field must either exist, or the rule
		 * being checked must implicitly validate "required", such as the "required"
	 	 * rule or the "accepted" rule.
		 * @param  string $rule     Rule being tested against
		 * @param  string $field 	Name of feature
		 * @param  mixed $value     Value being tested
		 * @return bool            
		 */
		protected function _validatable($rule, $field, $value) {

			return $this->_validateRequired($field, $value) or $this->_implicit($rule);
		}

		/**
		 * Test if rule implies that the field is required
		 * @param  string $rule Rule being tested
		 * @return bool
		 */
		protected function _implicit($rule) {

			return in_array($rule, array('required', 'accepted'));
		}

		/**
		 * Validate a required field exist
		 * @param  mixed $value Value being validated
		 * @return bool        
		 */
		protected function _validateRequired($field, $value) {
			
			if (is_null($value)) {
				return false;
			} else if (is_string($value) && trim($value) === "" ) {
				return false;
			} else if (RequestMethods::file($field) &&  $value['error'] != UPLOAD_ERR_NO_FILE) {
				return false;
			}

			return true;
		}

		/**
		 * Validate that a given field has a matching confirmation field
		 * @param  string $field Field being validated
		 * @param  mixed $value  Value being validated
		 * @return bool
		 */
		protected function _validateConfirmed($field, $value) {

			return $this->_validateSame($field, $value, array($field.'Confirm'));
		}

		/**
		 * Validate that a given field was accepted
		 * @param  string $field Field being validated
		 * @param  mixed $value  Value being validated
		 * @return bool        
		 */
		protected function _validateAccepted($field, $value) {

			return $this->_validateRequired($field, $value) and (in_array($value, array('yes', '1', 'no')));
		}

		/**
		 * Validate that a given field matches another
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Field(s) being compared with
		 * @return bool             
		 */
		protected function _validateSame($field, $value, $parameters) {

			$other = $parameters[0];
			return array_key_exists($other, $this->fields) and $value == $this->fields[$other];
		}

		/**
		 * Validate that a given field differs from another
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Field(s) being compared with
		 * @return bool             
		 */
		protected function _validateDifferent($field, $value, $parameters) {

			$other = $parameters[0];
			return array_key_exists($other, $this->fields) and $value != $this->fields[$other];
		}

		/**
		 * Validate that a given field contains only alphabetic characters
		 * @param  string $field Field being validated
		 * @param  mixed $value  Value being validated
		 * @return bool        
		 */
		protected function _validateAlpha($field, $value) {

			return StringMethods::match($value, "#^([a-zA-Z]+)$#");
		}

		/**
		 * Validate that a given field contains only alpha-numeric characters
		 * @param  string $field Field being validated
		 * @param  mixed $value Value being validated
		 * @return bool        
		 */
		protected function _validateAlphaNumeric($field, $value) {

			return StringMethods::match($value, "#^([a-zA-Z0-9]+)$#");
		}

		/**
		 * Validate that a given field contains only alpha-numeric characters, dashes, and underscores
		 * @param  string $field Field being validated
		 * @param  mixed $value Value being validated
		 * @return bool        
		 */
		protected function _validateAlphaNumericDash($field, $value) {

			return StringMethods::match($value, "#^(^[a-zA-Z0-9-_]+)$#");
		}

		/**
		 * Validate that a given field is numeric
		 * @param  string $field Field being validated
		 * @param  mixed $value  Value being validated
		 * @return bool        
		 */
		protected function _validateNumeric($field, $value) {

			return is_numeric($value);
		}

		/**
		 * Validate that a given field is an integer
		 * @param  string $field Field being validated
		 * @param  mixed $value  Value being validated
		 * @return bool        
		 */
		protected function _validateInteger($field, $value) {

			return filter_var($value, FILTER_VALIDATE_INT) !== false;
		}

		/**
		 * Validate that a given field is a certain size
		 * If a file validate against php.ini and HTML form settings
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Size being compared with
		 * @return bool             
		 */
		protected function _validateSize($field, $value, $parameters) {

			if (RequestMethods::file($value) && (in_array($value['error'], array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE)))) {
				return false;
			}

			return $this->_size($field, $value) == $parameters[0];
		}

		/**
		 * Validate that a given filed is between certain values
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Values being compared with
		 * @return bool             
		 */
		protected function _validateBetween($field, $value, $parameters) {

			$size = $this->_size($field, $value);
			return $size >= $parameters[0] and $size <= $parameters[1];
		}

		/**
		 * Validate that a given field is greater than the minimum
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Value(s) being compared with
		 * @return bool             
		 */
		protected function _validateMin($field, $value, $parameters) {

			return $this->_size($field, $value) >= $parameters[0];
		}

		/**
		 * Validate that a given field is less than the maximum
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Value(s) being compared with
		 * @return bool
		 */
		protected function _validateMax($field, $value, $parameters) {

			return $this->_size($field, $value) <= $parameters[0];
		}

		/**
		 * Validate that a given field is contained within a list of values
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Value(s) being compared with
		 * @return bool             
		 */
		protected function _validateIn($field, $value, $parameters) {

			return in_array($value, $parameters);
		}

		/**
		 * Validate that a given field is not contained within a list of values
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Value(s) being compared with
		 * @return bool
		 */
		protected function _validateNotIn($field, $value, $parameters) {

			return !in_array($value, $parameters);
		}

		/**
		 * Validate that a given field value is unique in a given database table
		 * If a column is not specified the field will be used
		 * @param  string $field     Field being validated
		 * @param  mixed $value      Value being validated
		 * @param  array $parameter  Optional column name and id value
		 * @return bool
		 */
		protected function _validateUnique($field, $value, $parameters) {
			
			// If column name does not match field name, it can be specified
			// in the second parameter position, after the table name
			if (isset($parameters[0])) {
				$table = $parameters[0];
			}

			$query = Database::query()->from($table)->where($field, "=", $value);
			
			// Allow a specific ID to not be include in the uniqueness check
			if (isset($parameters[2])) {
				$id = (isset($parameters[3])) ? $parameters[2] : "id";

				$query->where($id, "<>", $parameters[2]);
			}

			return $query->count()->num == 0;
		}

		/**
		 * Validate that a given field value exists in a given database table
		 * @param  string $field     Field being validated
		 * @param  mixed $value      Value being validated
		 * @param  array $parameter  Optional column name
		 * @return bool
		 */
		protected function _validateExists($field, $value, $parameters) {

			// If column name does not match field name, it can be specified
			// in the second parameter position, after the table name
			if (isset($parameters[0])) {
				$attribute = $parameters[0];
			} 

			// Count the number of elements to be looked for.
			// If array count all values in array else count equals 1
			if (is_array($value)) {
				$count = count($value);
			} else {
				$count = 1;
			}

			$query = Database::query()->from($attribute);

			// If given value was an array check for all values in array
			// otherwise check for single given value in the table
			if (is_array($value)) {
				$query = $query->whereIn($field, $value);
			} else {
				$query = $query->where($field, '=', $value);
			}

			return $query->count()->num >= $count;
		}

		/**
		 * Validate that a given field is a valid IP.
		 * @param  string $field Field being validated
		 * @param  mixed $value  Value being validated
		 * @return bool
		 */
		protected function _validateIp($field, $value) {

			return filter_var($value, FILTER_VALIDATE_IP) !== false;
		}

		/**
		 * Validate that a given field is a valid e-mail address
		 * @param  string $field Field being validated
		 * @param  mixed $value  Value being validated
		 * @return bool
		 */
		protected function _validateEmail($field, $value) {

			return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
		}

		/**
		 * Validate that a given field is a valid URL
		 * @param  string $field Field being validated
		 * @param  mixed $value  Value being validated
		 * @return bool
		 */
		protected function _validateUrl($field, $value) {

			return filter_var($value, FILTER_VALIDATE_URL) !== false;
		}

		/**
		 * Validate that a given field passes regular expression check
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Regular expression to be matched
		 * @return bool
		 */
		protected function _validateMatch($field, $value, $parameters) {

			return StringMethods::match($value, $parameters[0]);
		}

		/**
		 * Validate that a given file has a valid MIME type
		 * @param  string $field      Field being validated
		 * @param  array $value       File being validated
		 * @param  array $parameters  Mime types to be matched
		 * @return bool
		 */
		protected function _validateMimes($field, $value, $parameters) {

			if (!is_array($value) || $value['tmp_name'] == '') return false;

			$fileInfo = new \finfo(FILEINFO_MIME_TYPE);
			return array_search($fileInfo->file($value['tmp_name']), $parameters, true);
		}

		/**
		 * Validate that a given field is date before a given date
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Date to compare with 
		 * @return bool
		 */
		protected function _validateDateBefore($field, $value, $parameters) {

			return strtotime($value) < strtotime($parameters[0]);
		}

		/**
		 * Validate that a given field is a date after a given date
		 * @param  string $field      Field being validated
		 * @param  mixed $value       Value being validated
		 * @param  array $parameters  Date to compare with 
		 * @return bool
		 */
		protected function _validateDateAfter($field, $value, $parameters) {

			return strtotime($value) > strtotime($parameters[0]);
		}

		/**
		 * Get the size of the field be it a number or a string
		 * @param  string $attributes Field being tested
		 * @param  mixed $value       Value held in field
		 * @return mixed
		 */
		protected function _size($field, $value) {

			if (is_numeric($value) and $this->_hasRule($field, $this->_numericRules)) {
				return $this->fields[$field];
			} else if (RequestMethods::file($field)) {
				return $value['size'] / 1024;
 			} else {
				return StringMethods::length(trim($value));
			}
		}

		/**
		 * Determine if a field has a rule assigned
		 * @param  string $field Field to be tested
		 * @param  array  $rules   
		 * @return bool
		 */
		protected function _hasRule($field, $rules) {

			foreach ($this->_rules[$field] as $rule) {
				list($rule, $parameters) = $this->_parseRule($rule);
				if (in_array($rule, $rules)) return true;
			}

			return false;
		}

		/**
		 * Based on the format {rule}:{parameters} extract the 
		 * rule name and parameters from a rule 
		 * @param  string $rule Rule description
		 * @return array        
		 */
		protected function _parseRule($rule) {

			$parameters = array();

			// Test for ":" in rule and collect parameters
			if (($dividerPosition = strpos($rule, ":")) !== false) {
				$parameters = str_getcsv(substr($rule, $dividerPosition + 1));
			}

			if (is_numeric($dividerPosition)) {
				return array(substr($rule, 0, $dividerPosition), $parameters);
			} else {
				return array($rule, $parameters);
			}
		}

		/**
		 * Use magic method to call registered custom validators
		 * @param  string $method     Validator name
		 * @param  array $parameters  Parameters for custom validator
		 * @return mixed
		 */
		public function __call($method, $parameters) {
			// Clean up name to match custom validator,
			// then call the method with the given parameters.
			if (isset(static::$_validators[$method = lcfirst(substr($method, 9))])) {
				return call_user_func_array(static::$_validators[$method], $parameters);
			}

			throw new \Exception("Method '$method' does not exist.", 500);
		}
	}
}