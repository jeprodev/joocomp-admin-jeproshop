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

require_once 'file.php';

class JeproshopImageUploader extends JeproshopFileUploader{
    public function getMaxSize(){
        return (int)JeproshopSettingModelSetting::getValue('product_picture_max_size');
    }

    public function getSavePath(){
        return $this->normalizeDirectory(COM_JEPROSHOP_IMAGE_DIR);
    }

    public function getFilePath($file_name = null){
        //Force file path
        return tempnam($this->getSavePath(), $this->getUniqueFileName());
    }

    protected function validate(&$file){
        $file['error'] = $this->checkUploadError($file['error']);

        $post_max_size = $this->getPostMaxSizeBytes();

        if ($post_max_size && ($this->getServerVars('CONTENT_LENGTH') > $post_max_size))
        {
            $file['error'] = JError::raiseError(500, 'The uploaded file exceeds the post_max_size directive in php.ini');
            return false;
        } 

        if ($error = JeproshopImageManager::validateUpload($file, JeproshopTools::getMaxUploadSize($this->getMaxSize()), $this->getAcceptTypes()))
        {
            $file['error'] = $error;
            return false;
        }

        if ($file['size'] > $this->getMaxSize())
        {
            $file['error'] = JError::raiseError(500, 'File is too big');
            return false;
        }
        return true;
    }
}