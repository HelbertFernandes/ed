<?php

namespace EstaleiroWeb\Traits;

trait JSON {
	private static $__QUEUE = array();
	private static $__JSON_FNS = array();
	private static $__JSON_CONT = 0;

	public function json_stripSlashes(&$value = null, $id = null) {
		return $value = $this->json_stripSlashes_queue((string)$value, $id);
	}
	public function json_stripSlashes_get() {
		return $this->json_stripSlashes_queue();
	}
	public function json_stripSlashes_clear() {
		return $this->json_stripSlashes_queue(true);
	}
	public function json_stripSlashes_queue($value = null, $id = null) {
		$class = __TRAIT__;

		if ($id) {
			if (preg_match('/^%STRIP_SLASHES#\d+%$/', $id)) $name = $id;
			else return $id;
		} else {
			$id = null;
			$name = '%STRIP_SLASHES#' . $class::$__JSON_CONT . '%';
			$class::$__JSON_CONT++;
		}
		$out = $name;
		if (is_null($value) || is_bool($value)) {
			$out = $id ? $class::$__JSON_FNS[$name] : $class::$__JSON_FNS;
			if ($value === true) {
				$class::$__JSON_FNS = array();
				$class::$__JSON_CONT = 0;
			}
		} else $class::$__JSON_FNS[$name] = $value;
		return $out;
	}
	public function json_encode_full($mixed) {
		if ($a = is_array($mixed) || is_object($mixed)) {
			if ($mixed === $GLOBALS) return '$GLOBALS';
			if (array_search($mixed, self::$__QUEUE) !== false) return '**RECURSION**';
			$tam = count(self::$__QUEUE);
			self::$__QUEUE[$tam] = $mixed;
			if ($a) {
				$outO = array();
				$outA = array();
				$cont = 0;
				$isObj = false;
				foreach ($mixed as $k => $v) {
					if ($k !== $cont++) $isObj = true;
					$v = $this->json_encode_full($v);
					$outO[] = $this->json_encode_full($k) . ':' . $v;
					$outA[] = $v;
				}
				$out = $isObj ? '{' . implode(', ', $outO) . '}' : '[' . implode(', ', $outA) . ']';
			} else {
				$out = $this->json_encode_full([
					'type' => 'object',
					'name' => get_class($mixed),
					'content' => get_object_vars($mixed),
					'methods' => get_class_methods($mixed),
				]);
			}
			unset(self::$__QUEUE[$tam]);
			return $out;
		}
		if (is_bool($mixed)) return $mixed ? 'True' : 'False';
		if (is_null($mixed)) return 'null';
		if (is_numeric($mixed)) return $mixed;
		if (is_resource($mixed)) {
			return $this->json_encode_full([
				'type' => 'resource',
				'name' => (string)$mixed,
				'content' => get_resource_type($mixed),
			]);
		}
		return '\'' . addcslashes($mixed, "\x00..\x1F\x7E..\xFF'\"\\") . '\'';
	}
	public function json_encode2($mixed) {
		return strtr(
			preg_replace('/([\'"])(%STRIP_SLASHES#\d+%)\1/', '\2', json_encode($mixed)),
			$this->json_stripSlashes_queue()
		);
	}
}
