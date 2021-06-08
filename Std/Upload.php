<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

class Upload Extends \Of\File\Dirmanager {
	

	/**
	 * the that is being uploaded to to server
	 * came from form field 
	 * array $_FILES
	 */
	protected $file;

	/**
	 * the location of the file to be uloaded
	 * string
	 */
	protected $path;

	/**
	 * tells either to save the file into Vendor/Module/ directory
	 * or use the full path that is set
	 * boolean 
	 */
	protected $isFullPath;
	
	/**
	 * the name that will be used as an alias of the file
	 * because the file name will be generated
	 * string
	 */
	protected $newName;

	/**
	 * this will be the file name when it is saved on the server
	 * this is being generated upon upload
	 * string
	 */
	protected $orignalName;

	/**
	 * the file extension
	 * string
	 */
	protected $ext;

	/**
	 * accepted file type for the upload
	 */
	protected $accepted = [];

	protected $phpFileUploadErrors = [
	    0 => 'There is no error, the file uploaded with success',
	    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
	    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
	    3 => 'The uploaded file was only partially uploaded',
	    4 => 'No file was uploaded',
	    6 => 'Missing a temporary folder',
	    7 => 'Failed to write file to disk.',
	    8 => 'A PHP extension stopped the file upload.',
	];

	/**
	 * re-arrange the files then set it same as per single file upload
	 * @param $key string, the name of the file from the form field
	 * return self instance
	 */
	public function setFiles($key){
		$files = [];

        if(isset($_FILES[$key])){
            $attach = $_FILES[$key];

            if(is_array($attach['name'] )){
                foreach ($attach['name'] as $key => $value) {
                    $files[$key] = [
                        'name' => $attach['name'][$key],
                        'type' => $attach['type'][$key],
                        'tmp_name' => $attach['tmp_name'][$key],
                        'error' => $attach['error'][$key],
                        'size' => $attach['size'][$key],
                    ];
                }
            } else {
                $files[] = $attach;
            }
        }
        return $files;
	}

	public function setFile($file){
		$this->file = $file;
		return $this;
	}

	public function setPath($path, $isFullPath=false, $isAddDate=true){
		$this->isFullPath = $isFullPath;

		$path = ltrim($path, '/');
		$path = ltrim($path, '\\');
		$path = rtrim($path, '/');
		$path = rtrim($path, '\\');

		$time = time();
		$year = date("Y", $time);  
		$month = date("m", $time);  
		$day = date("d", $time);  

		$this->path = $path;
		if($isAddDate){
			$this->path = $path . '/' . $year . '/' . $month . '/' . $day;
		}
		return $this;
	}

	public function setNewName($newName){
		$this->newName = $newName;
		return $this;
	}

	public function setAcceptedFile($accepted){
		$this->accepted = $accepted;
		return $this;
	}

	public function save(){
		$result = [
			'error' => 1,
			'message' => '',
			'file' => null
		];
		if($this->file['error'] == 0) {
			$result['error'] = 0;

			$this->getExtractName();

			if(!in_array($this->ext, $this->accepted)){
				$result['error'] = 1;
				$result['message'] = 'Invalid file, try uploading file with ext ' . implode(', ', $this->accepted) . '.';
			}

			if($result['error'] == 0){
				$tmp_name = $this->file['tmp_name'];

				$destinationPath = ROOT . DS . 'App' . DS . 'Ext' . DS .  $this->path;
				if($this->isFullPath){
					$destinationPath = ROOT . DS . $this->path;
				}

				$this->createDir($destinationPath);
				$this->checkExists($destinationPath, $this->newName);

				$destination = $destinationPath . DS . $this->newName . '.' . $this->ext;
				$move = move_uploaded_file($tmp_name, $destination);

				if($move){
					$this->file['orignalName'] = $this->orignalName;
					$this->file['ext'] = $this->ext;
					$this->file['newName'] = $this->newName;
					$this->file['path'] = $this->path;
					$result['error'] = 0;
					$result['message'] = 'File successfully uploaded';
				} else {
					$result['error'] = 1;
					$result['message'] = 'Cannot move file, make sure the path directory is writable.';
				}
			}
		} else {
			$result['error'] = 1;
			$result['message'] = $phpFileUploadErrors[$this->file['error']];
		}

		$result['file'] = $this->file;

		return $result;
	}

	protected function checkExists($destinationPath, $newName, $count=0){
		$filename = $newName;
		$_filename = $newName;
		if($count >= 1){
			$filename .= '_' . $count;
			$_filename .= '_' . $count;
		}
		$filename .= '.' . $this->ext;

		if(file_exists($destinationPath . DS . $filename)){
			$count++;
			$this->checkExists($destinationPath, $newName, $count);
		} else {
			$this->newName = $_filename;
		}
	}

	public function getExtractName(){
		$fileName = pathinfo($this->file['name']);
		$this->orignalName = $fileName['filename'];
		$this->ext = strtolower($fileName['extension']);
		$this->newName = strtolower(time() . '_' . $this->generate(20));
	}
	
	public function generate($length=10) {
		$key = '';
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float) $sec + ((float) $usec * 100000));
		
		$inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

		for($i=0; $i<$length; $i++)
		{
			$key .= $inputs{mt_rand(0,61)};
		}
		return $key;
	}
}
?>