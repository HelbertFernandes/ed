<?php

namespace EstaleiroWeb\Traits;

trait Debug {
	static $DEBUG = null;

	public function debug($text = null) {
		$class = __TRAIT__;
		if (is_bool($text)) $class::$DEBUG = $text;
		if (!$class::$DEBUG) return;
		if (is_null($text)) {
			$bt = debug_backtrace();
			$args = preg_replace('/^\[((.|\s)*)\]$/', '\1', json_encode($bt[1]['args']));
			//$text=$bt[0]['file'].'['.$bt[0]['line'].']#'; //file[line]:
			$text = '[' . $bt[0]['line'] . ']#';
			$text .= isset($bt[1]['class']) ? $bt[1]['class'] . $bt[1]['type'] : ''; //class-> or class:: (if exists)
			$text .= $bt[1]['function'] . '(' . $args . ');'; //function(
		}
		print strftime('[%F %T]: ') . "$text\n";
	}
}
