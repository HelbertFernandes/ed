<?php

use EstaleiroWeb\ED\_;
use EstaleiroWeb\ED\Func\FileSessionHandler;

mb_detect_order('ASCII,UTF-8,ISO-8859-1,eucjp-win,sjis-win');
_::$encode = mb_detect_encoding('aeiouáéíóú');
mb_internal_encoding(_::$encode);
mb_http_output(_::$encode);
mb_http_input(_::$encode);
mb_regex_encoding(_::$encode);
mb_language('uni');

@date_default_timezone_set('America/Sao_Paulo');
//@date_default_timezone_set('Brazil/EAST');

if (@$_SERVER['SHELL']) session_set_save_handler(new FileSessionHandler(true), true);
//if (!session_id()) session_start();

_::init();
