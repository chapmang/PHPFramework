<?php
namespace Shared {

	use Framework\Event as Event;
	use Framework\Registry as Registry;
	use Framework\Database as Database;

	class Model extends \Framework\Model {

		/**
		 * @column
		 * @readwrite
		 * @primary
		 * @type autonumber
		 */
		protected $_id;

		/**
		 * @column
		 * @readwrite
		 * @type datetime
		 */
		protected $_created;

		/**
		 * @column
		 * @readwrite
		 * @type datetime
		 */
		protected $_modified;

		public function __construct($options = array()) {

			parent::__construct($options);

			// Schedule: Update the users activity in the active users table
			Event::add("framework.router.beforehooks.after", function($name, $parameters) {
			
				$session = Registry::get('session');
				$user = $session->get('user');
				$time = date("Y-m-d H:i:s");

				if ($user) {
					// @todo REPLACE INTO very Mysql specific build merge method for alternate db
					Database::replace("REPLACE INTO user_active VALUES (:user, :time)", array(':user' => $user, ':time' => $time));
				}
			});
		}

		/**
		 * Set default values for fields to be set/updated 
		 * with any database interaction
		 * @return void 
		 */
		public function save() {

			$primary = $this->getPrimaryColumn();
			$raw = $primary['raw'];

			if (empty($this->$raw)) {
				$this->setCreated(date("Y-m-d H:i:s"));
			}

			$this->setModified(date("Y-m-d H:i:s"));
			parent::save();
		}
	}
}