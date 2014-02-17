<?php

namespace Framework\Core\ObjectManager;

use Framework\Core\Exceptions\ObjectDoesntExistsException;

class ObjectManager
{
	public $registry = array();
	private $initializers = array();
	private $shareds = array();
	
	public function get($name)
	{
		if (!isset($this->shareds[$name]))
			throw new ObjectDoesntExistsException(
					sprintf('Object %s doesn\'t exists in the object manager registry', $name));
	
		if ($this->shareds[$name] && isset($this->registry[$name])) {
			return $this->registry[$name];
		}
	
		if (isset($this->initializers[$name])) {
			$this->registry[$name] = call_user_func($this->initializers[$name], $this);
			return $this->registry[$name];
		}
			
		throw new ObjectDoesntExistsException(
				sprintf('Object %s doesn\'t exists in object manager registry', $name));
	}
	
	public function setInitializer($name, $initializer, $isShared = true)
	{
		$this->initializers[$name] = $initializer;
		$this->shareds[$name] = $isShared;
	}
	
	public function setObject($name, $object, $isShared = true)
	{
		$this->registry[$name] = $object;
		$this->shareds[$name] = $isShared;
	}
}
