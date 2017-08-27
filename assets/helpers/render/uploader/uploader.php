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
    const DEFAULT_TEMPLATE = 'simple';
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

    public function getFilePath($fileName = null){
        if (!isset($fileName)){
            return tempnam($this->getSavePath(), $this->getUniqueFileName());
        }
        return $this->getSavePath() . $fileName;
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
            case 'g': $bytes *= 1024; break;
            case 'm': $bytes *= 1024; break;
            case 'k': $bytes *= 1024; break;
        }

        if ($bytes == '')
            $bytes = null;

        return $bytes;
    }

    public function getSavePath(){
        if (!isset($this->_save_path)){
            $this->setSavePath(COM_JEPROSHOP_TMP_IMAGE_DIR);
        }
        return $this->normalizeDirectory($this->_save_path);
    }

    public function getUniqueFileName($prefix = 'jeproshop'){
        return uniqid($prefix, true);
    }

    protected function checkUploadError($errorCode){
        $error = 0;
        switch ($errorCode){
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

    protected function getFileSize($filePath, $clearStatCache = false) {
        if ($clearStatCache)
            clearstatcache(true, $filePath);

        return filesize($filePath);
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

    public function process($destination = null){
        $jsonData = array("success" =>false, "found" => false);
        JClientHelper::setCredentialsFromRequest('ftp');
        $app = JFactory::getApplication();
        $input = $app->input;

        $productId = $input->get('product_id');
        $inputName = $input->get('field');
        //$jsonData['field_name'] = $inputName;

        $fileToUpload = $input->get($inputName, null, 'raw'); print_r($fileToUpload);
        /*$toUpload = array(
            array("name" => "13082011051.jpg", "lastModified" => 1313256842000, "lastModifiedDate" => "Sat Aug 13 2011 19:34:02", "webkitRelativePath" => "", "size" => "601161"), // "type" => "image/jpeg"),
            array("name" => "13082011052.jpg", "lastModified" => 1313256852000, "lastModifiedDate" => "Sat Aug 13 2011 19:34:12", "webkitRelativePath" => "", "size" => "617380"), // "type" => "image/jpeg"),
            array("name" => "13082011066.jpg", "lastModified" => 1313257434000, "lastModifiedDate" => "Sat Aug 13 2011 19:43:54", "webkitRelativePath" => "", "size" => "666300") // "type" => "image/jpeg")
        );* /
print_r($toUpload);
        if($toUpload && is_array($toUpload)) {
            $files = array();
            foreach ($toUpload as $file) {
                $files[] = $this->upload($file, $destination);
            }
            return $files;
        }else if($toUpload){
            $files = array();
            $files[] = $this->upload($toUpload, $destination);
            return $files;
        }
*/
        return null;
    }
    

    private function upload($file, $destination){
        //if($this->isFileSecure($file)){
            if(isset($destination) && is_dir($destination)){
                $filePath = $destination;
            }else{
                /*jimport('joomla.filesystem.folder');
                if(!JFolder::create($destination)){
                    JError::raiseError(500, JText::_('COM_JEPROSHOP_WE_CANNOT_CREATE_THIS_FOLDER_MESSAGE') . ' ' . $destination . ' ' . JText::_('COM_JEPROSHOP_PLEASE_CHECK_YOUR_CREDENTIALS_OR_CONTACT_AN_ADMIN_MESSAGE'));
                    return false;
                }*/
                $filePath = $this->getFilePath(isset($destination) ? $destination : $file['name']);
            }


            if(isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])){
                move_uploaded_file($file['tmp_name'], $destination);
            }else{
                file_put_contents($filePath, fopen('php://input', 'r'));
            }

            clearstatcache(true, $filePath);
            $fileSize = filesize($filePath);
echo $filePath;
            if($fileSize === $file['size']){
                $file['save_path'] = $filePath;
            }else{
                $file['size'] = $fileSize;
                unlink($filePath);
                $file['error'] = $file['error'] . '<br />' . JText::_('COM_JEPROSHOP_SERVER_FILE_SIZE_IS_DIFFERENT_FROM_LOCAL_FILE_SIZE_MODEL');
            }
        //}
        print_r($file);
        return $file;
    }

    private function isFileSecure($file){
        $file['error'] = $this->checkUploadError(isset($file['error']) ? $file['error'] : '');

        $postMaxSize = $this->getPostMaxSizeBytes();

        if ($postMaxSize && ($this->getServerVars('CONTENT_LENGTH') > $postMaxSize))
        {
            $file['error'] = $file['error'] . '<br />' . JText::_('JEPROSHOP_THE_UPLOADED_FILE_EXCEEDS_THE_POST_MAX_SIZE_DIRECTIVE_IN_MESSAGE');
            return false;
        }

        if (preg_match('/\%00/', $file['name']))
        {
            $file['error'] = $file['error'] . '<br />' . JTsxt::_('COM_JEPROSHOP_INVALID_FILE_NAME_LABEL') ;
            return false;
        }

        $types = $this->getAcceptTypes();
        print_r($file);

        //TODO check mime type.
        if (isset($types) && !in_array(pathinfo($file['name'], PATHINFO_EXTENSION), $types))
        {
            $file['error'] = $file['error'] . '<br />' . JText::_('COM_JEPROSHOP_FILE_TYPE_NOT_ALLOWED_LABEL');
            return false;
        }
        print_r($file);

        if ($file['size'] > $this->getMaxSize())
        {
            $file['error'] = $file['error'] . '<br />' . JText::_('COM_JEPROSHOP_FILE_IS_TOO_BIG_LABEL');
            return false;
        }
        print_r($file);

        return true;
    }

    public function render(){
        $document = JFactory::getDocument();
        $document->addScript('components/com_jeproshop/assets/javascript/jquery/ui/jquery.ui.widget.min.js');
        $document->addScript('components/com_jeproshop/assets/javascript/vendor/ladda.js');
        $document->addScript('components/com_jeproshop/assets/javascript/jquery/plugins/fileupload/jquery.iframe-transport.js');
        $document->addScript('components/com_jeproshop/assets/javascript/jquery/plugins/fileupload/jquery.fileupload.js');
        $document->addScript('components/com_jeproshop/assets/javascript/jquery/plugins/fileupload/jquery.fileupload-process.js');
        $document->addScript('components/com_jeproshop/assets/javascript/jquery/plugins/fileupload/jquery.fileupload-validate.js');
        $document->addScript('components/com_jeproshop/assets/javascript/vendor/spin.js');

        ob_start();
        //include (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $this->template . '.php');
        include (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'simple_uploader.php');
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }

}