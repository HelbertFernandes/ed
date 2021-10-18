<?php

namespace EstaleiroWeb\Traits;

trait SessionConfig {
	protected function getSession($id = '') {
		return @$_SESSION[__CLASS__][$id];
	}
	protected function setSession($value, $id = '') {
		if (!@$_SESSION[__CLASS__]) $_SESSION[__CLASS__] = array();
		$_SESSION[__CLASS__][$id] = $value;
		return $value;
	}
	protected function delSession($id = '') {
		unset($_SESSION[__CLASS__][$id]);
	}
}
