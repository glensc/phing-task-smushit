<?php
/**
 * Phing task to run Smush.it by Elan Ruusamäe
 *
 * @author Elan Ruusamäe <glen@delfi.ee>
 * @copyright Copyright (c) 2011 Elan Ruusamäe
 * @license New BSD License
 * @link https://github.com/glensc/phing-task-smushit
 */

/**
 * Defines a Phing task to run the Smush.it compressor against a set of image 
 * files.
 *
 * This task makes use of {@link https://github.com/davgothic/SmushIt smushit} by
 * GitHub user {@link https://github.com/davgothic davgothic}.
 */
class SmushitCompressorTask extends Task
{

    /**
     * @var PhingFile
     */
    protected $_targetDir;

    /**
     * @var array
     */
    protected $_fileSets;

    /**
     * @var SmushIt
     */
    private static $_instance;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_fileSets = array ();
    }

    /**
     * @return boolean
     */
    public function init()
    {
        include_once 'smushit.php';

        if (!class_exists('SmushIt')) {
            throw new BuildException("To use SmushitCompressorTask, you must have the path to smushit.php on your include_path or your \$PHP_CLASSPATH environment variable.");
        }
    }

    /**
     * @return SmushIt
     */
    private function getSmushitInstance()
    {
		if (empty(self::$_instance)) {
			self::$_instance = new SmushIt();
		}
		return self::$_instance;
    }

	/**
	 * Return the filesize in a humanly readable format.
	 * Taken from http://www.php.net/manual/en/function.filesize.php#91477
	 */
	private function format_bytes($bytes, $precision = 2) {
		$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}

    /**
     * @return void
     */
    public function main()
    {
        $this->_checkTargetDir();

		$smushit = $this->getSmushitInstance();

        /* @var $fileSet FileSet */
        foreach ($this->_fileSets as $fileSet) {

            $files = $fileSet->getDirectoryScanner($this->project)
                ->getIncludedFiles();

            foreach ($files as $file) {

                $targetDir = new PhingFile($this->_targetDir, dirname($file));
                if (!$targetDir->exists()) {
                    $targetDir->mkdirs();
                }
                unset($targetDir);

                $source = new PhingFile($fileSet->getDir($this->project), $file);
                $target = new PhingFile($this->_targetDir, $file);

                $this->log("Processing ${file}");
                try {
					$res = $smushit->compress($source);
					$this->log($this->format_bytes($res->src_size). ' to '.
						$this->format_bytes($res->dest_size). ' ('.
					   	$res->percent. '% savings)'
					);
					$contents = file_get_contents($res->dest);
					file_put_contents($target, $contents);
				} catch (SmushItException $e) {
					$this->log($e->getMessage());
                } catch (Exception $e) {
                    $this->log("Failed processing ${file}!", Project::MSG_ERR);
                    $this->log($e->getMessage(), Project::MSG_DEBUG);
                }
            }
        }
    }

    /**
     * @return FileSet
     */
    public function createFileSet()
    {
        $num = array_push($this->_fileSets, new FileSet);
        return $this->_fileSets[$num - 1];
    }

    /**
     * @param PhingFile $path
     * @return void
     */
    public function setTargetDir(PhingFile $path)
    {
        $this->_targetDir = $path;
    }


    /**
     * @return void
     */
    protected function _checkTargetDir()
    {
        if ($this->_targetDir === null) {
            throw new BuildException(
                'Target directory must be specified',
                $this->location
            );
        }
    }

}
