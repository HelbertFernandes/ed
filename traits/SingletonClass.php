<?php

namespace EstaleiroWeb\Traits;

trait SingletonClass {
	static protected $INSTANCE = [];

	static public function singleton() {
		$class = get_called_class();
		//$attr=get_class_vars($class);
		if (!array_key_exists($class, self::$INSTANCE)) {
			$parm = [];
			$args = func_get_args();
			foreach ($args as $k => $v) $parm[] = '$args[' . $k . ']';
			self::$INSTANCE[$class] = eval('return new ' . $class . '(' . implode(', ', $parm) . ');');
		}
		return self::$INSTANCE[$class];
	}
	static public function getInstance() {
		$class = get_called_class();
		return @self::$INSTANCE[$class];
	}
	static public function removeInstance($key) {
		$class = get_called_class();
		if (array_key_exists($class, self::$INSTANCE)) unset(self::$INSTANCE[$class]);
	}
	protected function __construct() {
	}
}
