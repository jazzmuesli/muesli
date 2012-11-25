<?php

class TemplateException extends Exception {
}
class Template {
	function __construct($filename) {
		if (file_exists($filename)) {
			$this->filename = $filename;
		} else {
			throw new TemplateException('Template ' . $filename . ' not found');
		}
	}
	/**
	* Extract vars and grab the output using OB.
	*/
	function evaluate($vars=array()) {
		extract($vars);
		ob_start();
		require($this->filename);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function mail($vars, $email, $subject) {
		$vars['baseurl'] = getBaseUrl();
		$vars['recipient'] = $email;
		$vars['subject'] = $subject;
		// Get content from prepared vars
		$content = $this->evaluate($vars);
		// Now separate into headers and body, if relevant.
		$ar = explode('---cut---', $content);
		$body = count($ar) == 2 ? $ar[1] : $content;
		$headers = join("\r\n", explode("\n", count($ar) == 2 ? $ar[0] : ""));
		return mail($email, $subject, $body, $headers) ? TRUE : FALSE;
	}

	function display($vars=array()) {
		extract($vars);
		require($this->filename);
	}
}
?>
