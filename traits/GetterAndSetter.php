<?php

namespace EstaleiroWeb\Traits;

trait GetterAndSetter {
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $readonly = [];
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $protect = [];
	/**
	 * Retorna o valor da variavel sobrecarregada
	 * Implemente os métodos geteres caso queira um melhor encapsulamento
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	public function __get($name) {
		if (method_exists($this, $fn = 'get_' . $name)) return $this->$fn();
		if (method_exists($this, $fn = 'get' . $name)) return $this->$fn();
		elseif (array_key_exists($name, $this->protect)) {
			return isset($this->protect[$name]) ? $this->protect[$name] : $this->get_backtrace($name);
		} elseif (array_key_exists($name, $this->readonly)) {
			return isset($this->readonly[$name]) ? $this->readonly[$name] : $this->get_backtrace($name);
		} else return $this->checkAllParameters($name);
	}
	/**
	 * Sobrecarrega as variaveis
	 * Implemente os métodos seteres caso queira um melhor encapsulamento
	 *
	 * @param string $nm Nome da Variavel
	 * @param mixed $val Valor
	 */
	public function __set($name, $value) {
		if (method_exists($this, $fn = 'set_' . $name)) $this->$fn($value);
		elseif (method_exists($this, $fn = 'set' . $name)) $this->$fn($value);
		elseif (array_key_exists($name, $this->protect)) $this->protect[$name] = $value;
		else return $this->checkAllParameters($name, $value);
		return true;
	}
	/**
	 * verifica se existe atributo allParameters e se existe um conteúdo com o $name (nome do atributo) 
	 * a ser procurado hieraquicamente nos callers desta instância 
	 * até nas variáveis globais
	 */
	public function checkAllParameters($name, $value = null) {
		$attr = get_object_vars($this);
		if (array_key_exists('allParameters', $attr) && in_array($name, $this->allParameters)) {
			if (is_null($value)) $value = $this->get_backtrace($name);
			return $this->readonly[$name] = $value;
		}
		return null;
	}
	/*
	* Inicia os parametros de acordo com a ordem ou chave do artributo $this->protect
	* Ex:
	 class X {
		use \Traits\OO\GetterAndSetter;
		public function __construct(){
			{$this->protect=[
				'start'=>null,
				'end'=>null,
				'step'=>null,
				'min'=>null,
				'max'=>null,
			];}
			//$this->init(func_get_args()); //uma forma de forçar a carga do jeito que quiser
			$this->init();
		}
		public function __invoke(){
			$this->init();
			return $this->to[];
		}
	 }
	 //casos de instância
	 print_r([
		new X(0),
		new X(0,100,2),
		new X([0,100,2]),
		new X(0,100,2,null,100),
		new X([0,100,2,null,100]),
		new X([0,100,'max'=>200,'step'=>2]),
	]);
	
	//ATENÇÃO: implemente o método initOne para que o primeiro caso "new X(0)" siga corretamente o desejado
	*/
	protected function init(array $args = null) {
		if (is_null($args)) {
			$bt = debug_backtrace();
			while ($bt) {
				$item = array_shift($bt);
				if (@$item['object'] && $item['object'] instanceof self && @$item['function'] != __FUNCTION__) {
					$args = @$item['args'];
				}
			}
		}
		if (!$args) {
			foreach ($this->protect as &$v) $v = null;
			return;
		}
		if (count($args) != 1) return $this->init([$args]);
		$seq = array_keys($this->protect);
		$args = $args[0];
		if (is_array($args)) foreach ($args as $k => $v) {
			if (array_key_exists($k, $seq)) $this->{$seq[$k]} = $v;
			if (array_key_exists($k, $this->protect)) $this->$k = $v;
		}
		elseif (is_object($args)) {
			if ($args instanceof self) return $this->init([$args->protect]);
			return $this->init([(array)$args]);
		} else return $this->initOne($args);
		return $this;
	}
	/*
	* Iniciação do $this->init($args) caso houver apenas um parametro
	*/
	protected function initOne($arg) {
		return $this->init([$arg]);
	}

	/**
	 * Retorna o valor da variavel sobrecarregada readonly
	 *
	 * @return array
	 */
	public function getReadonly() {
		return $this->readonly;
	}
	/**
	 * Retorna o valor da variavel sobrecarregada protect
	 *
	 * @return array
	 */
	public function getProtect() {
		return $this->protect;
	}
	/**
	 * Retorna o valor da variavel sobrecarregada de debug_backtrace
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	public function get_backtrace($name) {
		$bt = debug_backtrace();
		foreach ($bt as $line) if (
			isset($line['object']) &&
			$line['object'] !== $this &&
			isset($line['object']->$name) &&
			!is_null($line['object']->$name)
		) return $this->$name = $line['object']->$name;
		if (isset($GLOBALS[$name])) return $this->$name = $GLOBALS[$name];
	}
	/**
	 * Retorna o valor da variavel sobrecarregada de debug_backtrace
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	public function get_caller($class = null) {
		$bt = debug_backtrace();
		while ($bt) {
			$obj = array_shift($bt);
			if (array_key_exists('object', $obj)) {
				if (!($obj['object'] instanceof $this)) {
					if (is_null($class) || get_class($obj['object']) == $class) return $obj['object'];
				}
			} elseif (array_key_exists('function', $obj)) return $obj['function'];
			else return $obj;
		}
		return false;
	}
}
