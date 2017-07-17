<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net/index.php?option=com_jeproshop&view=product&product_id=1
 *
 * @copyright (C)   2009 - 2011
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of,
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JeproshopUploader{
    const DEFAULT_MAX_SIZE = 10485760;

    private $_accept_types;
    private $_files;
    private $_max_size;
    private $_name;
    private $_save_path;

    public function __construct($name = null){
        $this->setName($name);
        $this->_files = array();
    }

    public function setAcceptTypes($value){
        $this->_accept_types = $value;
        return $this;
    }

    public function getAcceptTypes(){
        return $this->_accept_types;
    }

    public function getFilePath($file_name = null){
        if (!isset($file_name)){
            return tempnam($this->getSavePath(), $this->getUniqueFileName());
        }
        return $this->getSavePath().$file_name;
    }

    public function getFiles(){
        if (!isset($this->_files)){
            $this->_files = array();
        }
        return $this->_files;
    }

    public function setMaxSize($value){
        $this->_max_size = intval($value);
        return $this;
    }

    public function getMaxSize(){
        if (!isset($this->_max_size)){
            $this->setMaxSize(self::DEFAULT_MAX_SIZE);
        }
        return $this->_max_size;
    }

    public function setName($value){
        $this->_name = $value;
        return $this;
    }

    public function getName(){
        return $this->_name;
    }

    public function setSavePath($value){
        $this->_save_path = $value;
        return $this;
    }

    public function getPostMaxSizeBytes() {
        $post_max_size = ini_get('post_max_size');
        $bytes         = trim($post_max_size);
        $last          = strtolower($post_max_size[strlen($post_max_size) - 1]);

        switch ($last){
            case 'g': $bytes *= 1024;
            case 'm': $bytes *= 1024;
            case 'k': $bytes *= 1024;
        }

        if ($bytes == '')
            $bytes = null;

        return $bytes;
    }

    public function getSavePath(){
        if (!isset($this->_save_path)){
            $this->setSavePath(COM_JEPROSHOP_UPLOAD_DIRECTORY);
        }
        return $this->normalizeDirectory($this->_save_path);
    }

    public function getUniqueFileName($prefix = 'jeproshop'){
        return uniqid($prefix, true);
    }

    protected function checkUploadError($error_code){
        $error = 0;
        switch ($error_code){
            case 1:
                $error = JError::raiseError(500, sprintf('The uploaded file exceeds %s', ini_get('post_max_size')));
                break;
            case 2:
                $error = JError::raiseError(500, sprintf('The uploaded file exceeds %s', JeproshopTools::formatBytes((int)$_POST['MAX_FILE_SIZE'])));
                break;
            case 3:
                $error = JError::raiseError(500, 'The uploaded file was only partially uploaded');
                break;
            case 4:
                $error = JError::raiseError(500, 'No file was uploaded');
                break;
            case 6:
                $error = JError::raiseError(500, 'Missing temporary folder');
                break;
            case 7:
                $error = JError::raiseError(500, 'Failed to write file to disk');
                break;
            case 8:
                $error = JError::raiseError(500, 'A PHP extension stopped the file upload');
                break;
            default;
                break;
        }
        return $error;
    }

    protected function getFileSize($file_path, $clear_stat_cache = false) {
        if ($clear_stat_cache)
            clearstatcache(true, $file_path);

        return filesize($file_path);
    }

    protected function getServerVars($var){
        return (isset($_SERVER[$var]) ? $_SERVER[$var] : '');
    }

    protected function normalizeDirectory($directory){
        $last = $directory[strlen($directory) - 1];

        if (in_array($last, array('/', '\\'))) {
            $directory[strlen($directory) - 1] = DIRECTORY_SEPARATOR;
            return $directory;
        }

        $directory .= DIRECTORY_SEPARATOR;
        return $directory;
    }

    public function render(){
        return '';
    }

}