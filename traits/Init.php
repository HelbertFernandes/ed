<?php

namespace EstaleiroWeb\Traits;

trait Init {
	public function init() {
	}
	public function loadArray($array) {
		foreach ($array as $k => $v) $this->$k = $v;
	}
}
