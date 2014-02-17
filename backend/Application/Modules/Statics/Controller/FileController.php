<?php

namespace Application\Modules\Statics\Controller;

use \Framework\Api;
use Application\Models\Users;
use Application\Models\UsersInfo;
use Application\Models\Languages;
use Framework\Core\Globals\Get;

class FileController extends \Framework\Core\Mvc\Controller{
	
	public function getAction($id = NULL){
		$reqFolder = Get::str('folder');
		$reqID = is_null($id) ? Get::int('id') : $id;
		$reqFilename = Get::str('filename', 'file');
		$reqExt = Get::str('ext');
	
		// for images
		$width = Get::int('width');
	
		$FSManager = Api::app()->getObjectManager()->get('fileSystem');
		if (in_array(strtolower($reqExt), array('jpg', 'jpeg', 'png', 'gif', 'bmp')))
			$FSManager->outputImg($reqFolder, $reqID . '_' . $reqFilename . '.' . $reqExt, $width);
		else
			$FSManager->outputFile($reqFolder, $reqID . '_' . $reqFilename . '.' . $reqExt);
	}
	
	public function get2Action(){
		$id = Get::str('id1') . Get::str('id2') . Get::str('id3') . Get::str('id4')
		. Get::str('id5') . Get::str('id6') . Get::str('id7') . Get::str('id8') . Get::str('id9');
		$this->getAction($id);
	}
	
	public function storeAction(){
		$FSManager = Api::app()->getObjectManager()->get('fileSystem');
		$id = (int)(substr(time() . mt_rand(), 0, strlen(PHP_INT_MAX) - 2));
		if (!isset($_FILES['tempFile']))
			return;
		$f = $_FILES['tempFile'];
		$ext = pathinfo($f['name'], PATHINFO_EXTENSION);
	
		// store file in temp storage
		$FSManager->storeFile('tmp', $id . '_file.' . $ext, $f['tmp_name']);
	
		$a = array();
		$a['id'] = (string)$id;
		$a['url'] = Api::app()->baseUrl . '/file/tmp/' . $id . '/file.' . $ext;
		//$a['file'] = $id . '.' . $ext;
		$a['uploadName'] = $f['name'];
		$a['folder'] = 'tmp';
		$this->renderJson($a);
	}
	
	
}







