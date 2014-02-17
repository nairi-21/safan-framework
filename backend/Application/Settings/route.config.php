<?php
 return array(
		// Home page
		'/^\/$/i' => array(
				'type' => 'RegExp',
				'module' => 'Statics',
				'controller' => 'index',
				'action' => 'index',
				'matches' => array(''),
		),	
 		// Store and Get file from file storage
 		'/^\/(file)\/([A-Za-z0-9-_]+)\/([0-9]+)\/([a-zA-Z0-9-_]+)\.([a-zA-Z0-9]+)$/i' => array(
 				'type' => 'RegExp',
 				'module' => 'StaticPages',
 				'controller' => 'File',
 				'action' => 'get',
 				'matches' => array('', '', 'folder', 'id', 'filename', 'ext'),
 		),
 		// Get File from file storage, (64bit OS)
 		'/^\/(file)\/([A-Za-z0-9-_]+)\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([a-zA-Z0-9-_]+)\.([a-zA-Z0-9]+)$/i' => array(
 				'type' => 'RegExp',
 				'module' => 'Files',
 				'controller' => 'File',
 				'action' => 'get2',
 				'matches' => array('', '', 'folder', 'id1', 'id2', 'id3', 'id4', 'id5', 'id6', 'id7', 'id8', 'id9', 'filename', 'ext'),
 		),
		// login, logout
		'/^\/(login|logout|register)\/?$/i' => array(
				'type' => 'RegExp',
				'module' => 'user',
				'controller' => 'index',
				'action' => '',
				'matches' => array('', 'action', '', '', 'authParam'),
		),
        // Error 404
        '404' => array(
            'type' => 'RegExp',
            'module' => 'statics',
            'controller' => 'error',
            'action' => 'error404',
            'matches' => array(''),
        ),
		// Framework standart MVC route
		'/^\/([A-Za-z0-9_-]+)\/?([A-Za-z0-9_-]+)?\/?([A-Za-z0-9_-]+)?\/?$/i' => array(
				'type' => 'RegExp',
				'module' => 'statics',
				'controller' => 'index',
				'action' => 'index',
				'matches' => array('', 'module', 'controller', 'action'),
		),
);
 
