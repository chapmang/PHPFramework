<?php
namespace Shared {

	use Framework\Event as Event;
	use Framework\Registry as Registry;
    use Framework\RequestMethods;
	
	/**
	 * Controller
	 * Methods that are better held in a shared environment
	 * thus making them available to all other controllers
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Shared
	 */
	class Controller extends \Framework\Controller {
		
		/**
		 * @readwrite
		 */
		protected $_user;

                /**
         * @readwrite
         */
        protected $_ajax = false;

		public function __construct($options = array()){

			parent::__construct($options);

			// Schedule: Load user from session
			Event::add("framework.router.beforehooks.before", function($name, $parameters) {
				$session = Registry::get('session');
				$controller = Registry::get('controller');
				$user = $session->get('user');

				if ($user) {
					$controller->user = \User::first(array(
						array('id', '=', $user)
					));
					$acl = new \Acl($user);
					$controller->user->userPerms = $acl->perms;
				}
			});

			// Shedule: Save user to session
			Event::add("framework.router.afterhooks.after", function($name, $parameters) {
				$session = Registry::get('session');
				$controller = Registry::get('controller');

				if ($controller->user) {
					$session->set('user', $controller->user->id);
				}
			});

		}

		/**
		 * Hook for determining if an action is secure,
		 * i.e., Does a valid user session exist?
        * @protected
        */
        public function _secure() {

            $user = $this->getUser();
            if (!$user) {
                header("Location: /users/login");
                exit();
            }
        }

        /**
         * Hook for determining if the user is a valid administrator
         * @protected
         */
        public function _admin(){

        	$user = $this->getUser();
        	if (!$user->hasPermission('access_admin')) {
        		throw new \Exception("Not a valid admin user account", 500);
        	}
        }

        /**
         * Hook for determining if the request was XHR
         * also works as a direct method call
         * @protected
         */
        public function _ajax(){

            // If request was XHR
            $request = RequestMethods::server('HTTP_X_REQUESTED_WITH');
            if(isset($request) && $request === 'XMLHttpRequest') {
                $this->ajax = true;
                return true;
            };

            // If request was not manage the correct response
            // If ajax test was via @before hook
            $caller = list(, $caller) = debug_backtrace(false);
            if ($caller[1]['function'] == 'Framework\{closure}' && $caller[1]['args'][1] == '@before') {
                throw new \Exception("NOT AJAX", 1);    
            } else {
                // If ajax test was simple method call
                return false;
            }
        
        }

        /**
         * Utility method for standardizing page redirects 
         * @param  string $url URL to be redirected to
         * @return void
         */
        public static function redirect($url) {
        	
            header("Location: {$url}");
            exit();
        }

        /**
         * Override the $_user setter to simplify adding 
         * only the user id to the session
         * @param object $user The user to be stored
         */
        public function setUser($user) {

        	$session = Registry::get('session');

        	if ($user) {
        		$session->set("user", $user->id);
        	} else {
        		$session->erase("user");
        	}

        	$this->_user = $user;
        	return $this;
        }

		/**
		 * Override \Framework\Controller\render() and
		 * assign the $_user to both action and layout views
		 * @return void 
		 */
		public function render() {

            if ($this->user) {
                if($this->actionView) {
                    $key = "user";
                    if ($this->actionView->get($key, false)) {
                        $key = "__user";
                    }
                    $this->actionView->set($key, $this->user);
                }
                if ($this->layoutView) {
                    $key = "user";
                    if ($this->layoutView->get($key, false)) {
                        $key = "__user";
                    }
                    $this->layoutView->set($key, $this->user);
                }
            }
            // If the request was not XHR then render the view
            if (!$this->ajax)
            parent::render();
        }
	}
}