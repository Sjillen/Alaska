<?php 
namespace Alaska\DAO;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileDAO 
{
	
	/**
	 * Verify if the file is uploadable
	 * @param UploadedFile $file
	 * @param array $acceptedExtension
	 * @return string[]|string[]|boolean|boolean|string[]
	 */
	public function uploadable(UploadedFile $file, array $acceptedExtension){
		$strSizeMax = ini_get('upload_max_filesize');
 		$sizeMax = $this->convertMaxUploadSrv($strSizeMax);
		if ($sizeMax > 0)
		{
			//If the file is too big for the server
			if ($file->getSize() >= $sizeMax || $file->getSize() == 0)
			{
				$message = array();
				$message[0] = 'error';
				$message[1] = 'Votre serveur ne peut supporter des fichiers supérieur à ' . $strSizeMax;
				return $message;
			}
			//If the file is bigger that 3MB
			elseif ($file->getSize() >= 3145728)
			{
				$message = array();
				$message[0] = 'error';
				$message[1] = 'La taille de votre fichier est trop importante (max 3M)';
				return $message;
			}
			else {
				$message = $this->checkExtension($file, $acceptedExtension);
				return $message;
			}
		}
		else {
			$message = $this->checkExtension($file, $acceptedExtension);
			return $message;
		}
	}
	/**
	 * Check if the picture could fit in the header. 
	 * @param UploadedFile $img
	 * @param integer $newWidth
	 * @param integer $maxHeight
	 * @return string[]|string[]|number[]
	 */
	public function checkImageDimension(UploadedFile $img, $newWidth = 750, $maxHeight = 700)
	{
		$imgSize = getimagesize($img->getPathName());
		$resizeReduction = (($newWidth * 100)/$imgSize[0]);
		$newHeight = (($imgSize[1] * $resizeReduction)/100);
		$message = array();
		if ($newHeight > 700 )
		{
			$message[0] = 'error';
			$message[1] = 'Le format de votre image n\'est pas valide...';
			return $message;
		}
		$message['newHeight'] = $newHeight;
		return $message;
	}
	/**
	 * Upload the file on the server in the directory ($path)
	 * @param UploadedFile $file
	 * @param string $path
	 * @param integer $newWidth
	 * @param integer $newHeight
	 */
	public function uploadFile(UploadedFile $file, $path = null, $newWidth = null, $newHeight = null)
	{
		$extension = $file->guessExtension();
		if ($newHeight == null && $newWidth == null)
		{
			$fileName = uniqid('JForteroche_') . "." . $extension;
			$file->move($path, $fileName);
			return $fileName;
		}
		else 
		{
			$imgSize = getimagesize($file->getPathName());
			$imgFileName = uniqid('JForteroche_') . "." . $extension;
			$file->move($path, $imgFileName);
			$this->resizeImage($path, $imgFileName, $extension, $imgSize, $newWidth, $newHeight);
			return $imgFileName;
		}
		
	}
	/**
	 * Resize the picture for fitting the header
	 * @param string $path
	 * @param string $imgFileName
	 * @param array $imgSize
	 * @param integer $newWidth
	 * @param integer $newHeight
	 */
	private function resizeImage($path, $imgFileName, $extension, $imgSize, $newWidth, $newHeight)
	{
		switch ($extension)
		{
			case 'jpeg':
				$imgOrigin = imagecreatefromjpeg($path . $imgFileName);
				$imageResize = imagecreatetruecolor($newWidth, $newHeight);
				imagecopyresampled($imageResize, $imgOrigin, 0, 0, 0, 0, $newWidth, $newHeight, $imgSize[0],$imgSize[1]);
				imagejpeg($imageResize, $path . $imgFileName, 100);
				break;
			case 'png':
				$imgOrigin = imagecreatefrompng($path . $imgFileName);
				$imageResize = imagecreatetruecolor($newWidth, $newHeight);
				imagecopyresampled($imageResize, $imgOrigin, 0, 0, 0, 0, $newWidth, $newHeight, $imgSize[0],$imgSize[1]);
				imagepng($imageResize, $path . $imgFileName, 9);
				break;
		}
		
	}
	/**
	 * 
	 * @param string $maxUpload
	 * @return number|string
	 */
	private function convertMaxUploadSrv($maxUpload)
	{
		$lengthString = strlen($maxUpload);
		$sizeMax = substr($maxUpload, 0, $lengthString -1);
		$unit = strtolower(substr($maxUpload, $lengthString -1));
		switch ($unit)
		{
			case 'k':
				$sizeMax = $sizeMax * 1024;
				break;
			case 'm':
				$sizeMax = $sizeMax * 1048576;
				break;
			case 'g':
				$sizeMax = $sizeMax * 1073741824;
				break;
		}
		return $sizeMax;
	}
	/**
	 * 
	 * @param UploadedFile $file
	 * @param array $acceptedExtension
	 * @return string[]|boolean
	 */
	private function checkExtension(UploadedFile $file, array $acceptedExtension)
	{
		if (in_array($file->guessExtension(), $acceptedExtension))
		{
			$message = true;
			return $message;
		}
		else 
		{
			$message = array();
			$message[0] = 'error';
			$message[1] = 'Votre fichier est invalide, il doit être de type jpeg ou png...';
			return $message;
			
		}
	}
}



