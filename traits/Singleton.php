<?php

namespace EstaleiroWeb\Traits;

trait Singleton {
	static protected $INSTANCE = null;

	static public function singleton() {
		if (is_null(self::$INSTANCE)) {
			$args = func_get_args();
			$parm = [];
			foreach ($args as $k => $v) $parm[] = '$args["' . $k . '"]';
			self::$INSTANCE = eval($x = 'return new ' . __CLASS__ . '(' . implode(', ', $parm) . ');');

			//$reflect=new \ReflectionClass(__CLASS__); self::$INSTANCE=$reflect->newInstanceArgs(func_get_args()); //PROBLEMA ao chamar método privado
			//$class=__CLASS__; self::$INSTANCE=new $class; //PROBLEMA por não passar parametros
			//$x=call_user_func_array('TestSingleton',$args); //PROBLEMA não instancia classes
		}
		return self::$INSTANCE;
	}
	static public function getInstance() {
		return self::singleton();
	}
	static public function removeInstance($key) {
		self::$INSTANCE = null;
	}
	protected function __construct() {
	}
}
