<?php

require_once DOCUMENT_ROOT. 'lib/TimThumb/timthumb.php';

class timthumb_v2 Extends timthumb {
	public static function start($save_as_uri=false){
		$tim = new timthumb_v2();
		if($save_as_uri) $tim->save_as_uri($save_as_uri);
		$tim->handleErrors();
		$tim->securityChecks();
		if($tim->tryBrowserCache()){
			exit(0);
		}
		$tim->handleErrors();
		if(FILE_CACHE_ENABLED && $tim->tryServerCache()){
			exit(0);
		}
		$tim->handleErrors();
		$tim->run();
		$tim->handleErrors();
		exit(0);
	}

	public function save_as_uri($uri){
		if(! is_file($this->cachefile)){
			return false;
		}

		$filename 	= DOCUMENT_ROOT . $uri;
		$dir 		= dirname($filename);
		if(!is_dir($dir)){
			mkdir($dir, 0755, true);
		}

		$content = file_get_contents ($this->cachefile);
		if ($content != FALSE) {
			$content = substr($content, strlen($this->filePrependSecurityBlock) + 6);
			file_put_contents($filename, $content);
		}
	}

}