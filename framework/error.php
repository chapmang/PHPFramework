<?php
namespace Framework {

	/**
	 * Error
	 * Class for handling exceptions
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman>
	 * @package Framework
	 */
	class Error {

		/**
		 * Handle an exception by logging it and either
		 * displaying detailed report or directing to base
		 * exceptions pages
		 * @param  Exception $exception The exception being handled
		 * @return void            
		 */
		public static function exception($exception) {

			// Send exception to be logged
			static::log($exception);

			// If detailed reports enabled, format the exception 
			// to clean message and display on screen
			if (Configuration::get('error.detail')) {
		        $message 	= $exception->getMessage();
				$code 		= $exception->getCode();
				$file		= $exception->getFile();
				$line		= $exception->getLine();
				$trace		= $exception->getTraceAsString();
				$date		= date('d-M-Y H:i:s');

				$logMessage = "<h3>System Exception:</h3>
					<p>
						<strong>Date:</strong> {$date}
					</p>
					<p>
						<strong>Message:</strong> {$message}
					</p>
					<p>
						<strong>Code:</strong> {$code}
					</p>
					<p>
						<strong>File:</strong> {$file}
					</p>
					<p>
						<strong>Line:</strong> {$line}
					</p>

					<h3>Stack Trace:</h3>
					<pre>{$trace}</pre>
					<br/>
					<hr/>
					<br/><br/>";
				echo $logMessage;
				return;
			}

			// If detailed reports NOT enabled direct to 
			// base exception pages
			// 
			// list exceptions
            $exceptions = array(
                "500",
                "404"
            );
            
            $code = $exception->getCode();
            if (in_array($code, $exceptions)) {
            	header("Content-type: text/html");
            	$view = file_get_contents(path('app') . "views/errors/{$code}".EXT);
            	echo $view;
            	exit;
        	}
            
            // Should it all fail render a fallback template
            header("Content-type: text/html");
            echo "An error occurred.";
            exit;
		}

		/**
		 * Handle a native PHP error as an ErrorException
		 * @param  int 		$code
		 * @param  string 	$error
		 * @param  string 	$file
		 * @param  int 		$line
		 * @return void
		 */
		public static function native($code, $error, $file, $line) {

			if (error_reporting() === 0) return;

			// For a native PHP error create an ErrorException and handle with
			// the exception method.
			$exception = new \ErrorException($error, $code, 0, $file, $line);

			if (in_array($code, Configuration::get('error.ignore'))) {
				return static::log($exception);
			}

			static::exception($exception);
		}

		/**
		 * If config set for logging then log the exception
		 * @param  Exception  $exception
		 * @return void
		 */
		public static function log($exception) {

			if ($file = Configuration::get('error.log')) {
				Log::exception($exception, $file);
			}
		}
	}
}