<?php

namespace EstaleiroWeb\Traits;

trait HtmlText{
	static $__ENCODE=null;
	
	public function fixLink($link){
		do{
			$old=$link;
			$link=preg_replace('/([\/\\\])[^\/\\\]+?\1\.\.\1/','\1',$link);
		} while ($link!=$old);
		return $link;
	}
	public function getEncode() {
		$class=__TRAIT__;
		if(is_null($class::$__ENCODE)) {
			mb_detect_order('ASCII,UTF-8,ISO-8859-1,eucjp-win,sjis-win');
			$class::$__ENCODE=mb_detect_encoding('aeiouáéíóú');
			
			mb_internal_encoding($class::$__ENCODE);
			mb_http_output($class::$__ENCODE);
			mb_http_input($class::$__ENCODE);
			mb_regex_encoding($class::$__ENCODE);
			mb_language('uni');
		}
		return $class::$__ENCODE;
	}
	public function htmlConvert($text) {
		return mb_convert_encoding($text,$this->getEncode()); 
	}
	public function htmlScpChar($text,$quotes=ENT_NOQUOTES){ 
		return htmlentities($text,$quotes,$this->getEncode()); 
	}
}