<?php

namespace Framework\Core\LanguageManager;

use \Framework\Safan;
use \Application\Models\Languages;

class LanguageManager
{
	/**
	 * Default System Language
	 */
	private $defaultLanguage = 'en_EN';

	/**
	 * Set Application Language
	 */
	public function set($language=false){
		if($language)
			Safan::app()->language = $language;
		else
			Safan::app()->language = $this->defaultLanguage;
	}
	/**
	 * @return translated text into Application Message Path
	 */
	public function translate($language){
        $translationFile = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Messages' . DS . $language . '.php';
		if(file_exists($translationFile))
			$this->strings = include $translationFile;
	}
	
	public function __invoke($key)
	{
		return isset($this->strings[$key]) ? $this->strings[$key] : $key;
	}
}
