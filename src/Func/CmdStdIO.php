<?php

namespace EstaleiroWeb\ED\Func;

class CmdStdIO {
	public function readLine($prompt = null, $length = null, $timeout = null, $erPattern = '') {
		while (true) {
			$do = true;
			$read = [STDIN];
			$write = null;
			$expect = null;
			$tv_sec = is_null($timeout) ? null : 0;
			$tv_usec = $timeout * 1000000;
			$str = '';

			readline_callback_handler_install($prompt, function ($content) use (&$do, &$str) {
				$do = false;
				$str = $content;
				readline_callback_handler_remove();
			});

			$n = stream_select($read, $write, $expect, $tv_sec, $tv_usec);
			do {
				if ($n && in_array(STDIN, $read)) {
					readline_callback_read_char();
				} else {
					print PHP_EOL;
					return;
				}
				if ($do) $str = readline_info('line_buffer');
				$strLen = strlen($str);
				if (!is_null($length) && $strLen >= $length) {
					print PHP_EOL;
					break;
				}
			} while ($do);
			//if (preg_match('/^(.*)[\n\r]+$/', $str, $ret)) $str = $ret[1];
			if ($erPattern == '' || preg_match($erPattern, $str)) break;
			print 'In the text: ' . $str . PHP_EOL;
			print 'Worng Pattern, reenter the information, please!' . PHP_EOL;
		}
		return $str;
	}
	public function read() {
		readline_callback_handler_install('', function () {
		});
		$stdin = fopen("php://stdin", 'r');
		$val = stream_get_contents($stdin, 1);
		fclose($stdin);
		readline_callback_handler_remove();
		return $val;
	}
	public function readline_old($prompt = null) {
		return readline($prompt);
	}
}
