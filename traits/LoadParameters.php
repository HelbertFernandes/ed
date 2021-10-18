<?php

namespace EstaleiroWeb\Traits;

trait LoadParameters {
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $allParameters = [];
	/**
	 * Constructor
	 */
	function __construct() {
		$this->loadAllParameters(func_get_args());
	}
	/**
	 * Retorna o valor da variavel sobrecarregada allParameters
	 *
	 * @return array
	 */
	public function getAllParameters() {
		return $this->allParameters;
	}
	protected function loadAllParameters($args) {
		if ($args) {
			foreach ($this->allParameters as $k => $pr) if (isset($args[$k])) $this->$pr = $args[$k];
			else break;
		}
	}
}
