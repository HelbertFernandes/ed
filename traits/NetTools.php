<?php

namespace EstaleiroWeb\Traits;

trait NetTools {
	public function nmap($host, $port) {
		$er = \Scr\OutError::singleton();
		$er->disable();
		$conexao = @fsockopen($host, $port, $erro, $erro, 15);
		if (($ret = (bool)$conexao)) @fclose($conexao);
		//print "Testing $host:$port ".($ret?'[OK]':'[ERROR]')."\n";
		$er->restore();

		return $ret;
	}
	public function getFreeRandomPort($host = '127.0.0.1', $start = 1000, $end = 65536) {
		$i = 0;
		do {
			$i++;
			$port = rand($start, $end);
			$nmap = $this->nmap($host, $port);
		} while (!$nmap && $i < 100);
		if (!$nmap) return $port;
	}
	public function checkHost($url, $port = 80) {
		$urlSplit = parse_url($url);
		$host = @$urlSplit['host'];
		if (!$host || $host == 'localhost' || $host == '127.0.0.1') return true;
		($p = @$urlSplit['port']) || ($p = $port);
		return (bool)@fsockopen($urlSplit['host'], $p, $errno, $errstr, 5);
	}
	public function goURL($url = '/') {
		header('HTTP/1.0 301 Moved');
		header('Location: ' . $url);
		exit;
	}
	public function hostname() { //FIXME hostname to win || linux
		return trim(`hostname`);
	}
}
