<?php
if(!class_exists('DevLog')){
	/**
	 * This log uses a singleton pattern to get around the issues of scope and allow global logging
	 * @author   Nathan DeSelm
	 * @version  $2019-02-01$
	 */
	class DevLog {
		private static $instance;
		private $log = array();
		private function __construct(){}

		/**
		 * DevLog::add('message'); called from any scope and appends $message to the main log (supports strings, arrays, objects, booleans and null)
		 * @param mixed $message
		 */
		public static function add($message){
			if(empty(self::$instance)) self::$instance = new DevLog();

			if(is_array($message) || is_object($message)){
				self::$instance->log[] = print_r($message,true);
			} elseif(is_null($message)) {
				self::$instance->log[] = 'null';
			} elseif(is_bool($message)) {
				self::$instance->log[] = $message ? 'true (boolean)':'false (boolean)';
			} else {
				self::$instance->log[] = (string)$message;
			}
		}

		/**
		 * DevLog::display(); called from any scope to return the log up to the point it was called
		 * @return string
		 */
		public static function display(){
			if(empty(self::$instance)) self::$instance = new DevLog();

			if(count(self::$instance->log) > 0){
				return '<div class="dhwp_dev_log"><pre class="wrapper">'.print_r(implode("\n",self::$instance->log),true).'</pre></div>';
			} else {
				return '';
			}
		}
	}
}
