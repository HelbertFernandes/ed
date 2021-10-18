#!/usr/bin/env php
<?php

use EstaleiroWeb\ED\_;
use EstaleiroWeb\ED\Func\CmdStdIO;
use EstaleiroWeb\ED\Vault;

require 'vendor/autoload.php';

new Admin;
class Admin {
	private $vlt, $std;

	public function __construct() {
		$this->check();
		$this->vlt = new Vault;
		$this->std = new CmdStdIO;
		//print_r($this->vlt->contents());
		if (preg_grep('/--install/', $GLOBALS['argv'])) $this->install();
		else $this->menu_Main();
	}

	public function check() {
		if (!Vault::checkClass()) {
			$filename = Vault::fileClassData();
			if (!file_put_contents($filename, '')) {
				die("Erro to create file $filename\n");
			}
		}
	}
	private function cls() {
		print chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';
	}
	private function pressKey() {
		print 'Press a key to continue...';
		$this->std->read();
	}
	private function confirm($text = '') {
		print $text . '[Yes/No]:';
		while (!preg_match('/^[YN]$/', $ret = strtoupper($this->std->read())));
		print PHP_EOL;
		return $ret;
	}
	private function menu($title, $aOptions, $text = '') {
		static $o = '123456789ABCDEFGHIJKLMNOPQRSTUVXWYZ';
		static $spc = '   ';

		$this->cls();
		$tam = strlen($spc . $title);
		print $spc . $title . PHP_EOL;
		print $spc . str_repeat('=', $tam) . PHP_EOL . PHP_EOL;

		$opt = [];
		foreach ($aOptions as $k => $line) {
			$opt[$o[$k]] = $k;
			$item = $spc . $o[$k] . ' - ' . $line['opt'];
			$tam = max(20, $tam, strlen($item));
			print $item . PHP_EOL;
		}
		$traco = str_repeat('-', $tam);
		if ($text) print $traco . PHP_EOL . $text . PHP_EOL . $traco . PHP_EOL;
		print PHP_EOL . 'Choice your option [0/ESC - To Exit]: ';

		$opt['0'] = $opt[chr(27)] = null;
		while (!array_key_exists($chr = strtoupper($this->std->read()), $opt));

		print PHP_EOL;
		print $traco . PHP_EOL . PHP_EOL;

		$k = @$opt[$chr];
		if (is_null($k)) return false;

		$callBack = @$aOptions[$k]['fn'];
		//print_r([$callBack,$k]);sleep(1);
		call_user_func($callBack, $k);
		return true;
	}

	private function install() {
		$this->main_Key();
		$this->conn_Add();
		$this->config_Paths();
		$this->build_db();
	}
	private function menu_Main() {
		while ($this->menu('Easy Data Main Menu', [
			['opt' => 'Alter the main Key', 'fn' => [__CLASS__, 'main_Key']],
			['opt' => 'Manager DSN Connections', 'fn' => [__CLASS__, 'menu_Conn']],
			['opt' => 'Config Directories', 'fn' => [__CLASS__, 'config_Paths']],
			['opt' => 'Repopulate Database', 'fn' => [__CLASS__, 'build_db']],
		]));
	}
	private function menu_Conn() {
		while ($this->menu('Easy Data DSN Menu', [
			['opt' => 'List DSN Connection', 'fn' => [__CLASS__, 'connList']],
			['opt' => 'Add DSN Connection', 'fn' => [__CLASS__, 'conn_Add']],
			['opt' => 'Remove DSN Connection', 'fn' => [__CLASS__, 'menu_ConnRm']],
			//['opt' => 'xxxx', 'fn' => 'yyy'],
		]));
	}
	private function menu_ConnRm() {
		print __FUNCTION__ . PHP_EOL;
		$conn = $this->vlt->contents();
		$opts = [];
		foreach ($conn as $name => $line) {
			$exclude = preg_grep(Vault::$erPass, array_keys($line));
			foreach ($exclude as $k) unset($line[$k]);

			$opts[] = ['opt' => '[' . $name . ']: ' . json_encode($line), 'fn' => [__CLASS__, 'conn_Rm']];
		}
		while ($this->menu('Easy Data DSN Remove List Menu', $opts));
	}

	private function main_Key() {
		$this->cls();
		print 'Alter Main key' . PHP_EOL;
		print '==============' . PHP_EOL . PHP_EOL;
		print 'WARNNIG:' . PHP_EOL;
		print '     After change that key, you must change every DSN Connections' . PHP_EOL;
		print '     Your database will keep if exists' . PHP_EOL . PHP_EOL;

		$filename = Vault::fileClass();
		$content = file_get_contents($filename);
		//if (!preg_match($er = '(/static\s+private\s+\$key\s*=\s+)([\'"])([a-f0-9]*)\1/i', $content, $ret)) {

		if (!preg_match($er = '/(static\s+private\s+\$key\s*=\s+)([\'"])([^\'"]*)\2/i', $content, $ret)) {
			print 'Erro to find the KEY';
			return $this->pressKey();
		}
		$key = $ret[3];

		print 'The current hex key is: ' . $key . PHP_EOL;
		$newKey = $this->std->readLine('New Hex Key (tam 16-64): ', 64, 120, '/^([a-f0-9]{16,64}|)$/i');
		$newKey = strtolower($newKey);

		if ($newKey == '') {
			print 'The key is keep' . PHP_EOL;
		} elseif ($this->confirm('Confirm a new key [' . $newKey . '] ') != 'Y') return;
		else {
			$content = preg_replace($er, '\1\'' . $newKey . '\'', $content);
			file_put_contents($filename, $content);
			print 'The key is changed' . PHP_EOL;
		}
		$this->pressKey();
	}
	private function connList() {
		$this->cls();
		print 'List of Connections' . PHP_EOL;
		print '===================' . PHP_EOL . PHP_EOL;
		print json_encode($this->vlt->contents(), JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
		$this->pressKey();
	}
	private function conn_Add() {
		$this->cls();
		print 'ADD DSN Connection' . PHP_EOL;
		print '===================' . PHP_EOL . PHP_EOL;

		print 'Insert PDO options' . PHP_EOL;
		$arr = [];
		$arr['dsn'] = $this->std->readLine('DSN String: ', null, 120, '/^([a-z]+:.+|)/i');
		if ($arr['dsn'] == '') return;
		$arr['user'] = $this->std->readLine('Username (max 32): ', 32, 120, '/^\w+$/');
		if ($arr['user'] == '') return;
		$arr['passwd'] = $this->std->readLine('Password (max 32): ', 32);
		$arr['options'] = trim($this->std->readLine('Options (json encoded): '));
		if ($arr['options'] == '') $arr['options'] = null;
		else $arr['options'] = json_decode($arr['options']);

		$key = $this->std->readLine('Connection Name [max 15 (a-z0-1)]: ', 15, null, '/^[a-z0-1]*$/i');

		print 'Confirm to create this connection:' . PHP_EOL;
		print "[$key]" . json_encode($arr, JSON_PRETTY_PRINT) . PHP_EOL;

		if ($this->confirm('Confirm a DSN adding ') == 'Y') {
			$this->vlt->add($arr, $key);
			$this->pressKey();
		}
	}
	private function conn_Rm($item) {
		print __FUNCTION__ . PHP_EOL;
		$conn = $this->vlt->contents();
		$akeys = array_keys($conn);
		if (!array_key_exists($item, $akeys)) {
			print 'Not Fount Connection' . PHP_EOL;
			return $this->pressKey();
		}
		$key = $akeys[$item];
		$this->cls();
		print "DSN Connection: [$key]\n";
		print json_encode($conn[$key], JSON_PRETTY_PRINT) . PHP_EOL;
		if ($this->confirm('Do you want to delete this DSN? ') == 'Y') {
			$this->vlt->del($key);
			print 'DSN deleted' . PHP_EOL;
			$this->pressKey();
		}
	}

	//TODO implements
	private function config_Paths() {
		$this->cls();
		print __FUNCTION__ . PHP_EOL;
		$this->pressKey();
	}
	private function build_db() {
		$this->cls();
		print __FUNCTION__ . PHP_EOL;
		$this->pressKey();
	}
}


//print_r([$std->r1($prompt)]);
//print_r([ord($std->read())]);
//print_r([$std->readLine($prompt,10,3)]);