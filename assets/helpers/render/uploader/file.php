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

require_once 'uploader.php';

class JeproshopFileUploader extends JeproshopUploader{
    const DEFAULT_TEMPLATE_DIRECTORY = 'helpers/uploader';
    const DEFAULT_TEMPLATE           = 'simple';
    const DEFAULT_AJAX_TEMPLATE      = 'ajax';

    const TYPE_IMAGE                 = 'image';
    const TYPE_FILE                  = 'file';

    private   $_context;
    private   $_drop_zone;
    private   $_id;
    private   $_files;
    private   $_name;
    private   $_max_files;
    private   $_multiple;
    private   $_post_max_size;
    protected $_template;
    private   $_template_directory;
    private   $_title;
    private   $_url;
    private   $_use_ajax;

    public function setContext($value){
        $this->_context = $value;
        return $this;
    }

    public function getContext(){
        if (!isset($this->_context)){
            $this->_context = JeproshopContext::getContext();
        }
        return $this->_context;
    }

    public function setDropZone($value)
    {
        $this->_drop_zone = $value;
        return $this;
    }

    public function getDropZone(){
        if (!isset($this->_drop_zone))
            $this->setDropZone("$('#".$this->getId()."-add-button')");

        return $this->_drop_zone;
    }

    public function setId($value){
        $this->_id = (string)$value;
        return $this;
    }

    public function getId(){
        if (!isset($this->_id) || trim($this->_id) === '')
            $this->_id = $this->getName();

        return $this->_id;
    }

    public function setFiles($value)
    {
        $this->_files = $value;
        return $this;
    }

    public function getFiles(){
        if (!isset($this->_files))
            $this->_files = array();

        return $this->_files;
    }

    public function setMaxFiles($value)
    {
        $this->_max_files = isset($value) ? intval($value) : $value;
        return $this;
    }

    public function getMaxFiles()
    {
        return $this->_max_files;
    }

    public function setMultiple($value)
    {
        $this->_multiple = (bool)$value;
        return $this;
    }

    public function setName($value)
    {
        $this->_name = (string)$value;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setPostMaxSize($value){
        $this->_post_max_size = $value;
        return $this;
    }

    public function getPostMaxSize(){
        if (!isset($this->_post_max_size))
            $this->_post_max_size = parent::getPostMaxSize();

        return $this->_post_max_size;
    }

    public function setTemplate($value)
    {
        $this->_template = $value;
        return $this;
    }

    public function getTemplate(){
        if (!isset($this->_template)){
            $this->setTemplate(self::DEFAULT_TEMPLATE);
        }
        return $this->_template;
    }

    public function setTemplateDirectory($value){
        $this->_template_directory = $value;
        return $this;
    }

    public function getTemplateDirectory(){
        if (!isset($this->_template_directory))
            $this->_template_directory = self::DEFAULT_TEMPLATE_DIRECTORY;

        return $this->normalizeDirectory($this->_template_directory);
    }

    public function setTitle($value){
        $this->_title = $value;
        return $this;
    }

    public function getTitle(){
        return $this->_title;
    }

    public function setUrl($value){
        $this->_url = (string)$value;
        return $this;
    }

    public function getUrl(){
        return $this->_url;
    }

    public function setUseAjax($value){
        $this->_use_ajax = (bool)$value;
        return $this;
    }

    public function isMultiple(){
        return (isset($this->_multiple) && $this->_multiple);
    }

    public function useAjax(){
        return (isset($this->_use_ajax) && $this->_use_ajax);
    }

}