<?php

namespace EstaleiroWeb\Traits;

trait SingletonKey {
	static protected $INSTANCE = [];

	static public function singleton($key) {
		if (!array_key_exists($key, self::$INSTANCE)) {
			$args = func_get_args();
			array_shift($args);
			$parm = [];
			foreach ($args as $k => $v) $parm[] = '$args["' . $k . '"]';
			self::$INSTANCE[$key] = eval($x = 'return new ' . __CLASS__ . '(' . implode(', ', $parm) . ');');
		}
		return self::$INSTANCE[$key];
	}
	static public function getInstance($key) {
		return @self::$INSTANCE[$key];
	}
	static public function removeInstance($key) {
		if (array_key_exists($key, self::$INSTANCE)) unset(self::$INSTANCE[$key]);
	}
	protected function __construct() {
	}
}
