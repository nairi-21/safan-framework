<?php

return array(
 	// Test
 	'/^([A-Za-z-_]+)\:([A-Za-z-_]+)$/i' => array(
 			'type' => 'RegExp',
			'module' => 'Test',
 			'controller' => 'index',
 			'action' => 'index',
 			'matches' => array('', 'module', 'controller', 'action'),
 	),





);
