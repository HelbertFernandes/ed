<?php

namespace EstaleiroWeb\Traits;

trait Show {
	public function show($text = true, $class = 'MakeBox') {
		$c = __TRAIT__;
		print_r([$c]);
		$text = print_r($text, true);

		//Find the file and function caller
		$bt = debug_backtrace();
		foreach ($bt as $k => $v) {
			unset($bt[$k]['object']);
		}
		$file = $line = null;
		$args = [];
		while ($bt) {
			$oFrom = array_shift($bt);
			$class = @$oFrom['class'];
			$type = @$oFrom['type'];
			if (array_key_exists('file', $oFrom)) $file = $oFrom['file'];
			if (array_key_exists('line', $oFrom)) $line = $oFrom['line'];
			$function = $oFrom['function'];
			if (!array_key_exists('args', $oFrom)) continue;
			if ($class == __CLASS__ && ($function == __FUNCTION__ || $function == 'verbose')) continue;
			$args = $oFrom['args'];
			if ($function == '__callStatic' && $class == 'AL') continue;
			if ($function == 'call_user_func_array' && is_object(@$args[0][0]) && get_class($args[0][0]) == 'AL') continue;
			break;
		}
		$parm = [];
		$caller = $file . '[' . $line . ']:';    //file[line]:
		$caller .= $class ? $class . $type : ''; //class-> or class:: (if exists)
		$caller .= $function . '(';          //function(

		//Print
		if (@$_SERVER['SHELL'] || is_string($c::$__VERBOSE)) { //Line command print type
			foreach ($args as $v) $parm[] = gettype($v);
			if ($caller) $caller = '==>' . $caller;
			$caller .= implode(',', $parm) . ');'; //arg1,arg2,...);
			$out = $caller ? $caller . "\n" : '';
			if ($text) $out .= "$text\n";
			$out .= "\n";
			if (is_string($c::$__VERBOSE)) return file_put_contents($c::$__VERBOSE, $out, FILE_APPEND);
		} else { //Browser print type
			foreach ($args as $v) $parm[] = '<span title="' . $this->htmlScpChar(print_r($v, true), ENT_QUOTES) . '">' . gettype($v) . '</span>';
			$caller = $this->htmlScpChar($caller) . implode(',', $parm) . ');'; //arg1,arg2,...);
			$out = $this->makeBox($this->htmlScpChar($text), $caller, $class, 'pre');
		}
		if ($class === false) return $out;
		print $out;
	}
}
