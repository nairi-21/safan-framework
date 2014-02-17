<?php

namespace Framework\Core\FileSystem;

use Framework\Safan;
use Framework\Core\Exceptions\FileSystemBaseException;
use Framework\Core\Exceptions\FileSystemNotWritableException;

class FileSystem{
	
	
	protected $defaultFolderPermissions = 0775;
    protected $defaultFilePermissions = 0774;
    protected $mask;
    protected $storageRoot = 'resource/media';
	protected $fileHeaders = array(
            'png' => 'image/png',
            'css' => 'text/css',
            'js' => 'text/javascript',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'json' => 'application/json',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'xml' => 'text/xml',
    );
	
	
	public function __construct()
    {
		if (!is_writable($this->storageRoot))
			throw new FileSystemNotWritableException();
		$this->mask = '%0' . (strlen(PHP_INT_MAX) - 1) . 'd';
	}
	
	public function getFile($folder, $fileName)
	{
		$path = $this->getPath($folder, $fileName);
		if (!is_readable($path))
			return false;
		return $path;
	}
	
	public function getPath($folder, $fileName, $prependStorageRoot = true)
	{
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
		$fName = pathinfo($fileName, PATHINFO_FILENAME);
		$fNum = (int)$fName;
		if(!$fNum)
			throw new FileSystemBaseException();
		$fNum = sprintf($this->mask, $fNum);

		$file = 'file';
		$f = explode('_', $fName, 2);
		if (count($f) > 1 && !empty($f[1]))
			$file = $f[1];
	
		$path = sprintf('%s/%s/%s.%s', trim($folder, '/'), implode('/', str_split($fNum, 2)), $file, $ext);
		if ($prependStorageRoot)
			$path = $this->storageRoot . '/' . $path;
		return rtrim($path, '.');
	}
	
	public function storeFile($folder, $fileName, $pathToFile)
	{
		$path = $this->getPath($folder, $fileName);
		$dir = dirname($path);
		$file = pathinfo($path, PATHINFO_BASENAME);
		if (!is_dir($dir)) {
			if (!mkdir($dir, $this->defaultFolderPermissions, true))
				throw new FileSystemNotWritableException();
		}
		if (is_readable($pathToFile) && !file_exists($path))
			return copy($pathToFile, $path);
		return false;
	}
	
	public function storeFileWithContent($folder, $fileName, $content)
	{
		$path = $this->getPath($folder, $fileName);
		$dir = dirname($path);
		$file = pathinfo($path, PATHINFO_BASENAME);
		if (!is_dir($dir)) {
			if (!mkdir($dir, $this->defaultFolderPermissions, true))
				throw new FileSystemNotWritableException();
		}
		file_put_contents($path, $content);
		return true;
	}
	
	public function storeFileFromTemp($folder, $fileName, $tmpID, $isOverwrite = false)
	{
		$tmpExt = $this->getTempFileExtension($tmpID);
		$tmpFNum = sprintf($this->mask, $tmpID);
		$tmpFile = $this->storageRoot . '/tmp/' . implode('/', str_split($tmpFNum, 2)) . '/file.' . $tmpExt;
		//_dump($tmpFile);
	
		$toNum = explode('_', $fileName, 2);
		$toFileName = $toNum[1];
		$toNum = $toNum[0];
		$toFNum = sprintf($this->mask, $toNum);
		$toFile = $this->storageRoot . '/' . trim($folder, '/') . '/' . implode('/', str_split($toFNum, 2)) . '/' . $toFileName;
		//_dump($toFile);
	
		$this->deleteFile($folder, $fileName);
		
		// ensure destination folder exists
		$toPathinfo = pathinfo($toFile);
		$toPath = $toPathinfo['dirname'];
		if (!is_dir($toPath)) {
			if (!mkdir($toPath, $this->defaultFolderPermissions, true))
				throw new FileSystemNotWritableException();
		}
	
		rename($tmpFile, $toFile);
		return true;
	}
	
	public function storeFileFromUrl($folder, $fileName, $urlToFile)
	{
		$path = $this->getPath($folder, $fileName);
		$dir = dirname($path);
		$file = pathinfo($path, PATHINFO_BASENAME);
		if (!is_dir($dir)) {
			if (!mkdir($dir, $this->defaultFolderPermissions, true))
				throw new FileSystemNotWritableException();
		}
	
		if (!file_exists($path)) {
			$sourcecode = file_get_contents($urlToFile);
			$fh = fopen($path, 'w');
			fwrite($fh, $sourcecode);
			fclose($fh);
			//return copy($pathToFile, $path);
		}
		return false;
	}
	
	public function getTempFileExtension($tmpID)
	{
		$fNum = sprintf($this->mask, $tmpID);
		$folder = $this->storageRoot . '/tmp/' . implode('/', str_split($fNum, 2)) . '/';
		$fileNames = scandir($folder);
		$fileName = $fileNames[2];
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
		return $ext;
	}
	
	
	public function outputFile($folder, $fileName)
	{
		$path = $this->getPath($folder, $fileName);
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if (array_key_exists(strtolower($ext), $this->fileHeaders)) {
			if (!is_readable($path))
				Safan::app()->getObjectManager()->get('dispatcher')->respond404();
			$fh = fopen($path, 'rb');
			header('Content-type: ' . $this->fileHeaders[strtolower($ext)]);
			header("Content-Length: " . filesize($path));
			fpassthru($fh);
		} else {
			Safan::app()->getObjectManager()->get('dispatcher')->respond404();
		}
	}
	
	public function outputImg($folder, $fileName, $width = NULL)
	{
		$path = $this->getPath($folder, $fileName);
		if (!file_exists($path)) {
			$arr = explode('_', $fileName, 2);
			$arr = $arr[1];
			$pathinfo = pathinfo($arr);
			//_dump($arr);
			$arr = $pathinfo['filename'];
			$arr = explode('_', $arr);
			$arr = end($arr);
			$width = $arr;
	
			$arr = explode('_', $pathinfo['filename']);
			unset($arr[count($arr) - 1]);
			$arr = implode('_', $arr);
			$a = explode('_', $fileName);
			$fileName = reset($a) . '_' . $arr . '.' . $pathinfo['extension'];
		}
		$path = $this->getPath($folder, $fileName);
	
		if (empty($width)) {
			$this->outputFile($folder, $fileName);
			return;
		}
	
		$pi = pathinfo($fileName);
		$sizeFileName = $pi['filename'] . '_' . $width . '.' . $pi['extension'];
		$sizePath = $this->getPath($folder, $sizeFileName);
	
		if (file_exists($sizePath)) {
			$this->outputFile($folder, $sizeFileName);
			return;
		}
	
		if (!file_exists($sizePath)) {
			switch ((strtolower($pi['extension']))) {
				case 'png':
					$this->resizePng($path, $sizePath, $width);
					break;
				case 'jpeg':
				case 'jpg':
					$this->resizeJpg($path, $sizePath, $width);
					break;
				case 'gif':
					$this->resizeGif($path, $sizePath, $width);
					break;
				case 'bmp':
					$this->resizeBmp($path, $sizePath, $width);
					break;
				default:
					break;
			}
		}
		$this->outputFile($folder, $sizeFileName);
	}
	
	/**
	 * Resize Jpeg file
	 */
	public function resizeJpg($fromPath, $toPath, $width)
	{
		$img = new \Imagick($fromPath);
		$img->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
		$img->writeImage($toPath);
		$img->destroy();
	}
	/**
	 * Resize Gif file
	 */
	public function resizeGif($fromPath, $toPath, $width)
	{
		$img = new \Imagick($fromPath);
		$img->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
		$img->writeImage($toPath);
		$img->destroy();
	}
	/**
	 * Resize Png file
	 */
	public function resizePng($fromPath, $toPath, $width)
	{
		$img = new \Imagick($fromPath);
		$img->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
		$img->writeImage($toPath);
		$img->destroy();
	}
	/**
	 * Resize Bmp file
	 */
	public function resizeBmp($fromPath, $toPath, $width)
	{
		$img = new \Imagick($fromPath);
		$img->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
		$img->writeImage($toPath);
		$img->destroy();
	}
	
	public function convert($url, $forceWidth = NULL)
    {
        $matches = array();
		$isMacthed = preg_match('/\/(file)\/([A-Za-z0-9-_]+)\/([0-9]+)\/([a-zA-Z0-9-_]+)\.([a-zA-Z0-9]+)/i', $url, $matches);
		if (!$isMacthed)
			return $url;
		$fileName = $matches[3] . '_' . $matches[4];
	
		$a = explode('?', $url);
		if (is_null($forceWidth)) {
			if (isset($a[1])) {
				$a = explode('=', $a[1]);
				$width = (int)$a[1];
				if ($width > 0)
					$fileName .= '_' . $width;
			}
		} else {
            $fileName .= '_' . $forceWidth;
            $width = $forceWidth;
		}
	
		$fileName .= '.' . $matches[5];
        $path = $this->getPath($matches[2], $fileName, false);
       
        if(file_exists(BASE_PATH . DS . 'resource' . DS . 'media' . DS . $path))
            return Safan::app()->resourceUrl . DS . 'media' . DS . $path;
        elseif(isset($width) && $width > 0)
            return $url . '?width=' . $width;
        return $url;

	}
	
	public static function strElipses($str, $maxCount) {
		$charCount = mb_strlen($str);
		if ($charCount > $maxCount) {
			$str = mb_substr($str, 0, $maxCount) . '..';
        }
		return $str;
    }

    public function deleteFile($folder, $fileName){
        $path = $this->getPath($folder, $fileName);
        if (is_writable($path)) {
            unlink($path);
            $dir = dirname($path);
            $dirContent = scandir($dir);
            $pi = pathinfo($path);
            foreach ($dirContent as $n) {
                if (preg_match('/^' . $pi['filename'] . '_([0-9]+)(_[0-9]+)?\.' . $pi['extension'] . '$/', $n))
                    unlink($dir . '/' . $n);
            }
            return true;
        }
        return false;
    }


}
