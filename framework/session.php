<?php
namespace Framework {

	use Framework\Base as Base;
	use Framework\Registry as Registry;
	use Framework\Session as Session;
	use Framework\Event as Event;

	/**
	 * Session
	 * Factory class for initializing the requested
	 * session driver class
	 * @version 1.0
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @package Framework
	 */
	class Session extends Base{

		/**
		 * Driver of Session driver to be used
		 * @var string
		 * @readwrite
		 */
		protected $_driver;

		/**
		 * Options for session driver
		 * @var array
		 * @readwrite
		 */
		protected $_options;

		/**
		 * Load details from configuration file and initialize
		 * driver class
		 */
		public function initialize() {

			Event::fire("framework.session.initialize.before", array($this->_driver, $this->_options));

			if (!$this->_driver) {
				$driver = Configuration::get('session');
                $this->setDriver($driver['driver']);
                unset($driver['driver']);
                $this->setOptions($driver);
			}

			if (!$this->_driver) {
                throw new \Exception("Invalid driver", 500);
            }

            Event::fire("framework.session.initialize.after", array($this->_driver, $this->_options));

            switch ($this->_driver) {
            	case 'server':
            		return new Session\Driver\Server($this->_options);
            		break;
            	case 'server':
            		return new Session\Driver\File($this->_options);
            		break;
            	default:
            		throw new \Exception("Invalid driver", 500);
            		break;
            }
		}
	}
}