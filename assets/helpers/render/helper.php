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

class JeproshopHelper {
    public $context;

    public $languages = null;

    public function __construct() {
        $this->context = JeproshopContext::getContext();
    }

    public function multiLanguageInputField($fieldName, $wrapper, $type, $required = false, $content = null, $maxLength = null, $hint = '', $width = '650', $height = '150'){
        if(!isset($this->languages) || !$this->languages){
            $this->languages = JeproshopLanguageModelLanguage::getLanguages(false);
        }
        ob_start();
        include(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'multi_lang_field.php');
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }

    public function multiLanguageTextAreaField($fieldName, $wrapper, $content = NULL, $width = '550', $height = '100'){
        ob_start();
        include(__DIR__ . DIRECTORY_SEPARATOR .'templates' . DIRECTORY_SEPARATOR . 'multi_lang_field.php');
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }

    public function radioButton($fieldName, $wrapper = 'jform', $layout = 'add', $state = 1, $disable = false){
        ob_start();
        include (__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'radio.php');
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }
    
    public function imageFileChooser(){
        ob_start();
        include(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'file_chooser.php');
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }

    public function inputFileUploader(){
        ob_start();
        include(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'file_uploader.php');
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }
}