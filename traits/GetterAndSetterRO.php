<?php

namespace EstaleiroWeb\Traits;


trait GetterAndSetterRO {
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $readonly = [];
	/**
	 * Retorna o valor da variavel sobrecarregada
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	public function __get($name) {
		return array_key_exists($name, $this->readonly) ? $this->readonly[$name] : @$this->$name;
	}
	/**
	 * Sobrecarrega as variaveis
	 *
	 * @param string $nm Nome da Variavel
	 * @param mixed $val Valor
	 */
	public function __set($name, $value) {
		if (array_key_exists($name, $this->readonly)) $this->readonly[$name] = $value;
	}
	/**
	 * Retorna o valor da variavel sobrecarregada readonly
	 *
	 * @return array
	 */
	public function getReadonly() {
		return $this->readonly;
	}
}
