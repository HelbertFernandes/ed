<?php

namespace EstaleiroWeb\Traits;

//similar ao getopt ('a:bcd::',['parametro1:','parametro2::','parametro3']);
trait Options {
	use OverLoadElements;

	protected $options = [
		''            => ['range' => '0-',],
		'--help'      => ['tip' => 'Mostra essa Ajuda', 'fn' => '$this->help()',],
		'--debub'     => ['tip' => 'Ativa/Desativa o debug', 'type' => 'b', 'range' => '0-1', 'fn' => '$this->setDebug($value)',],
		'--superDebug' => ['tip' => 'Ativa/Desativa o Super Debug', 'type' => 'b', 'range' => '0-1', 'fn' => '$this->setSuperDebug($value)',],
		'-h'          => ['ref' => '--help'],
		'-d'          => ['ref' => '--debub'],
		'-sd'         => ['ref' => '--superDebug'],
	];
	protected $optionsParameters = [
		'ref'     => ['io' => 'rw', 'type' => 's',     'def' => null,  'ex' => ['--help'],                                                'tip' => 'Valor de algum option que deseja espelhar as configurações',],
		'tip'     => ['io' => 'rw', 'type' => 's',     'def' => '',    'ex' => ['Descrição'],                                             'tip' => 'Descrição para que serve o parametro',],
		'required' => ['io' => 'rw', 'type' => 'b',     'def' => false, 'ex' => [true, false, 1, 0],                                          'tip' => 'Se é requerido ou não',],
		'type'    => ['io' => 'rw', 'type' => 's',     'def' => 's',   'ex' => ['s', 'n', 'b', 'a'],                                         'tip' => 'String, Numérico, Boleano, Array',],
		'range'   => ['io' => 'rw', 'type' => 's|n|a', 'def' => 0,     'ex' => [0, '0-', '0-1', '1-1', '1-3', [0, 0], [0, 1]],                    'tip' => 'Quantidade de valores que podem ter o argumento',],
		'fn'      => ['io' => 'rw', 'type' => 's',     'def' => null,  'ex' => ['$this->_options_example($option,$value,$line,"texto")'], 'tip' => 'Função a ser executada na integra para cada valor deste argumento. Variáveis pode ser passadas: $value, $option e $line ou os elementos de $line (estes parametros)',],
		'fnLine'  => ['io' => 'rw', 'type' => 's',     'def' => null,  'ex' => ['$this->_options_example($option,$value,$line,"texto")'], 'tip' => 'Função a ser executada na integra para todo argumento. Variáveis pode ser passadas: $value, $option e $line ou os elementos de $line (estes parametros)',],
		'er'      => ['io' => 'rw', 'type' => 's',     'def' => null,  'ex' => ['regexp/', '!/regexp/'],                                   'tip' => 'Expressão regular para validar o valor do argumento',],
		'value'   => ['io' => 'ro', 'type' => 's|a',   'def' => null,  'ex' => [1, 'texto', [1, 'texto']],                                   'tip' => 'Os valores carregados',],
		'checked' => ['io' => 'ro', 'type' => 'b',     'def' => true,  'ex' => [null, true],                                               'tip' => 'Se a linha de argumento já foi iniciada',],
		'len'     => ['io' => 'ro', 'type' => 'n',     'def' => 0,     'ex' => [0, 1, 2, 3],                                                 'tip' => 'Quantidade de argumentos passados. Obs: Quando type=a e passado um valor de argumento apenas, len será 1 porém count(value) será o tamanho do array',],
	];
	protected $debug = false;
	protected $superDebug = false;
	protected $help = '';

	final public  function Options(array $options = null) {
		if ($options && is_array($options)) $this->options = array_merge($this->options, $options);
		foreach ($this->options as $item => $line) $this->_options_paramLineStart($item, $line);

		$argv = @$GLOBALS['argv'];
		array_shift($argv);
		while ($argv) $this->_options_argv2param($argv);

		foreach ($this->options as $item => $line) $this->_options_paramLineCheckErros($item, $line);
		//show($this->options);exit;
		$this->_options_checkErros();

		foreach ($this->options as $option => $line) if ($line['len'] && !$line['ref']) {
			if ($line['fnLine']) $this->_options_eval($option, $line['value'], $line, $line['fnLine']);
			if ($line['fn']) {
				if ($line['len'] == 1) $this->_options_eval($option, $line['value'], $line, $line['fn']);
				else foreach ($line['value'] as $value) $this->_options_eval($option, $value, $line, $line['fn']);
			}
		}
	}
	final private function _options_paramLineStart($item = null, $line = null) {
		if (is_null($item)) $item = '';
		if (is_null($line)) $line = @$this->options[$item];
		if (!@$line['checked']) {
			$ref = @$line['ref'];
			if ($ref && @$this->options[$ref]) {
				$this->_options_paramLineStart($ref, $this->options[$ref]);
				$this->options[$item] = ['ref' => $ref];
				foreach ($this->optionsParameters as $k => $v) if ($k != 'ref' || $this->options[$ref]['ref']) $this->options[$item][$k] = &$this->options[$ref][$k];
			} else {
				$line['required'] = (bool)@$line['required'];
				$line['type'] = $this->_options_str2type(@$line['type']);
				if ($item == '' && @$line['range'] == '') $line['range'] = '0-';
				$line['range'] = $this->_options_str2range(@$line['range']);
				$this->options[$item] = [];
				foreach ($this->optionsParameters as $k => $v) $this->options[$item][$k] = array_key_exists($k, $line) ? $line[$k] : $v['def'];
			}
		}
		return $this->options[$item];
	}
	final private function _options_argv2param(&$argv) {
		$item = array_shift($argv);
		$value = null;
		$type = @$this->options[$item]['type'];

		$cont = 0;
		if ($type) {
			$range = @$this->options[$item]['range'];
			$er = @$this->options[$item]['er'];
			if ($range[1] === 0) $this->_options_paramSetValue($item);
			else while ($argv) if (is_null($range[1]) || $cont++ < $range[1]) {
				$value = reset($argv);
				if ($this->_options_check($er, $type, $value)) {
					array_shift($argv);
					$this->_options_paramAddValue($item, $value);
				} else {
					if ($range[0] === 0) $this->_options_paramAddValue($item);
					else $this->_options_paramAddValue('', $item);
					break;
				}
			}
			return $this->options[$item]['value'];
		} else {
			$split = explode('=', $item, 2);
			$type = @$this->options[$split[0]]['type'];
			if ($type) {
				$sItem = $split[0];
				$value = @$split[1];
				$range = @$this->options[$sItem]['range'];
				$er = @$this->options[$sItem]['er'];
				if ($this->_options_check($er, $type, $value)) return $this->_options_paramAddValue($sItem, $value);
				else return $this->_options_paramAddValue('', $item);
			}
		}

		if ($item[0] == '-') {
			$ini = '-';
			$item = substr($item, 1);
		} else $ini = '';
		do {
			$shortItem = $ini . $item[0];
			$item = substr($item, 1);
			$type = @$this->options[$shortItem]['type'];
			if ($type) {
				$range = @$this->options[$shortItem]['range'];
				$er = @$this->options[$shortItem]['er'];
				if ($range[1] === 0) {
					$this->_options_paramSetValue($shortItem);
					continue;
				} else {
					if ($this->_options_check($er, $type, $item)) {
						$this->_options_paramAddValue($shortItem, $item);
						break;
					} else {
						if ($range[0] === 0) {
							$this->_options_paramSetValue($shortItem);
							continue;
						}
						$this->_options_paramAddValue('', $shortItem . $item);
						break;
					}
				}
			}

			$this->_options_paramAddValue('', $shortItem . $item);
			break;
		} while ($item);
	}
	final private function _options_paramLineCheckErros($item, $line) {
		if ($line['ref']) return;

		//check required
		$item = $item ? $item . ': ' : 'ARGUMENTOS: ';

		//check range
		if ($line['required'] && $line['len'] == 0) $this->erro[] = $item . 'É requerido';
		if ($line['len'] != 0 && $line['range'][0] > $line['len']) $this->erro[] = $item . 'Deve haver no mínimo ' . $line['range'][0] . ' íten(s)';
		else
		if (!is_null($line['range'][1]) && $line['range'][1] < $line['len']) {
			if (!($line['len'] == 1 && is_bool($line['value']))) $this->erro[] = $item . ($line['range'][1] == 0 ? 'Não deve haver ítens (' . implode(', ', (array)$line['value']) . ')' : 'Deve haver máximo ' . $line['range'][1] . ' ítens');
		}

		return;
		//check er
		$value = $line['value'];
		if (!$this->_options_checkEr($line['er'], $value)) {
			if (is_array($value)) $this->erro[] = $item . 'Seu(s) valor(es) "' . implode('","', $value) . '" não correspondem a RegExp ' . $line['er'];
			else                 $this->erro[] = $item . 'Seu valor "' .       $value               . '" não corresponde a RegExp ' . $line['er'];
		}
	}
	final private function _options_paramAddValue($item, $value = true) {
		if (is_null($this->options[$item]['value'])) return $this->_options_paramSetValue($item, $value);
		if (!is_array($this->options[$item]['value'])) $this->options[$item]['value'] = [$this->options[$item]['value']];
		$this->options[$item]['value'][] = $value;
		$this->options[$item]['len']++;
		return $this->options[$item]['value'];
	}
	final private function _options_paramSetValue($item, $value = true) {
		$this->options[$item]['len'] = 1;
		return $this->options[$item]['value'] = $value;
	}
	final private function _options_checkErros() {
		if (!$this->erro) return;
		print "ERROS:\n";
		foreach ($this->erro as $e) print "$e\n";
		print "\n";

		$this->help();
	}
	final private function _options_str2range($str) {
		if (!is_array($str)) $str = explode('-', $str);
		$str[0] = max(0, @$str[0] + 0);
		if (count($str) == 1) return [$str[0], $str[0]];
		return [$str[0], $str[1] == '' ? null : max($str[0], @$str[1] + 0)];
	}
	final private function _options_str2type($type) {
		$type = substr(strtolower($type . '*'), 0, 1);
		switch ($type) {
			case 'a': //array 1,2,3,a,b,c,0-1,0-
			case 'n': //numeric -76781.2e-123
			case 'b': //bool [NULL|0|1|true|false|on|off|verdadeiro|falso|ligado|desligado]]
				break;
			case 's': //string
			default:
				$type = 's';
		}
		return $type;
	}
	final private function _options_trType($type, $value) {
		switch ($type) {
			case 'n': //numeric -76781.2e-123
				return $value + 0;
			case 'b': //bool [NULL|0|1|true|false|on|off|verdadeiro|falso|ligado|desligado]]
				return is_null($value) || preg_match('/^\s*(|null|0+([.,]0*)?|fals[eo]|off|desligado|none)\s*$/i', $value) ? false : true;
			case 'a': //array 1,2,3,a,b,c
				return preg_split('/\s*,\s*/', trim($value));
			default:
				return "$value";
		}
	}
	final private function _options_checkType($type, $value) {
		if (is_null($value)) return true;

		switch ($type) {
			case 'n':
				$er = '/^[+-]?(\d+(\.\d*)?|\d*\.\d+)(e[+-]\d+)?$/i';
				break;
			case 'b':
				$er = '/^(|0|1|null|true|fals[eo]|on|off|verdadeiro|(des)?ligado|none)$/i';
				break;
			case 'a':
			case 's':
			default:
				return true;
		}
		return preg_match($er, $value);
	}
	final private function _options_checkEr($er, &$value) {
		if (!$er) return true;

		if ($er[0] == '!') {
			$er = substr($er, 1);
			$grep = 0;
		} else $grep = PREG_GREP_INVERT;
		if (is_array($value)) {
			$value = preg_grep($er, $value, $grep);
			if ($value) return false;
		} elseif (preg_match($er, $value) - $grep) return false;
		else $value = '';
		return true;
	}
	final private function _options_check($er, $type, &$value) {
		if ($this->_options_checkType($type, $value)) {
			$value = $v = $this->_options_trType($type, $value);
			if ($this->_options_checkEr($er, $v)) return true;
		}
		return false;
	}
	final private function _options_eval($option, $value, $line, $fn) {
		return eval("return {$fn};");
	}
	final private function _options_example($option, $value, $line) {
		print_r(['option' => $option, 'value' => $value, 'line' => json_encode($line)]);
	}

	public function getOptions() {
		return $this->options;
	}
	public function getOptionsParameters() {
		return $this->optionsParameters;
	}
	public function setDebug($value = true) {
		$this->debug = $value;
		return $this;
	}
	public function setSuperDebug($value = true) {
		$this->superDebug = $value;
		return $this;
	}

	public function help($tamPar = 30, $tamTip = 80, $tamRecuo = 2) {
		$types = ['s' => 'val_str', 'b' => 'val_bool', 'n' => 'val_num', 'a' => 'val_array'];
		$args = $out = [];
		foreach ($this->options as $k => $line) if (!$line['ref'] && $line['tip']) {
			$out[$k] = $line;
			$out[$k]['options'] = [$k];
		}
		foreach ($this->options as $k => $line) if ($line['ref'] && @$out[$line['ref']]) $out[$line['ref']]['options'][] = $k;

		$tam = 0;
		foreach ($out as $item => &$line) {
			$ordem = ['-' => [], '--' => [], '' => []];
			$arg = [];
			foreach ($line['options'] as $v) {
				if ($v[0] == '-') {
					if ($v[1] == '-') $ordem['--'][] = $v;
					else $ordem['-'][] = $v;
				} else $ordem[''][] = $v;
			}

			$line['options'] = [];
			foreach ($ordem as $tpar => $pars) foreach ($pars as $v) $line['options'][] = $v;

			$arg = implode('|', $line['options']);
			$args[$item] = $line['required'] ? "<{$arg}>" : "[{$arg}]";

			$arg = implode(', ', $line['options']);
			if ($line['range'][1] !== 0) {
				$type = $types[$line['type']];
				if ($line['range'][0] == 0) {
					$typeI = '[';
					$typeF = ']';
				} else {
					$typeI = '<';
					$typeF = '>';
				}

				$arg .= ' ' . $typeI;
				if (is_null($line['range'][1])) $arg .= $type . ',...';
				else $arg .= substr(str_repeat(',' . $type, $line['range'][1]), 1);
				$arg .= $typeF;
			}
			$tam = max($tam, strlen($arg) + $tamRecuo);
			$line['options'] = $arg;
		}
		$args = $args ? ' ' . implode(' ', $args) : '';
		$tamPar = min($tamPar, $tam);

		$cmd = basename(@$GLOBALS['argv'][0]);
		print "$cmd$args\n";
		if ($this->help) print "  {$this->help}\n";
		$strBase = str_repeat(' ', $tamPar);
		$strRecuo = str_repeat(' ', $tamRecuo);

		foreach ($out as $v) {
			$arg = str_pad($strRecuo . $v['options'], $tamPar);
			if (strlen($arg) > $tamPar) {
				print "$arg\n";
				$arg = $strBase;
			}
			$tip = explode("\n", wordwrap($v['tip'], $tamTip, "\n", true));
			foreach ($tip as $v) {
				print "$arg  $v\n";
				$arg = $strBase;
			}
		}
		exit;
	}
	public function show($text) {
		$text = print_r($text, true);
		if (@$_SERVER['SHELL']) return utf8_encode($text) . "\n";
		else return "<pre style='font-size:x-small; text-align:left;'>$text</pre>";
	}
	protected function pr($text, $force = false) {
		if ($this->debug || $force) $this->show($text);
	}
	protected function prs($text) {
		if ($this->superDebug) $this->show($text);
	}
}
