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

class JeproshopTree {
    const DEFAULT_TEMPLATE  = 'tree';
    const DEFAULT_HEADER_TEMPLATE = 'tree_header';
    const DEFAULT_NODE_FOLDER_TEMPLATE = 'tree_node_folder';
    const DEFAULT_NODE_ITEM_TEMPLATE   = 'tree_node_item';

    protected $attributes;
    private   $context;
    protected $data;
    protected $header_template;
    private   $tree_id;
    protected $node_folder_template;
    protected $node_item_template;
    protected $template;
    private   $tree_title;
    private   $toolbar;

    public function __construct($id, $data = null){
        $this->setTreeId($id);

        if (isset($data)){ 	$this->setTreeData($data); }
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTreeId($value){
        $this->tree_id = $value;
        return $this;
    }

    public function setTreeData($value){
        if (!is_array($value) && !$value instanceof Traversable){
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_DATA_VALUE_MUST_BE_A_TRAVERSABLE_ARRAY_MESSAGE'));
        }
        $this->data = $value;
        return $this;
    }

    public function setTreeTitle($value){
        $this->tree_title = $value;
        return $this;
    }

    public function setTreeLayout($value){
        $this->template = $value;
        return $this;
    }

    public function setTreeActions($value) {
        if (!isset($this->toolbar))
            $this->setTreeToolbar(new JeproshopTreeToolbar());

        $this->getToolbar()->setTreeActions($value);
        return $this;
    }

    public function setTreeAttribute($name, $value){
        if (!isset($this->attributes))
            $this->attributes = array();

        $this->attributes[$name] = $value;
        return $this;
    }

    public function setTreeAttributes($value){
        if (!is_array($value) && !$value instanceof Traversable)
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_DATA_VALUE_MUST_BE_A_TRAVERSABLE_ARRAY_MESSAGE'));

        $this->attributes = $value;
        return $this;
    }

    public function setContext($value){
        $this->context = $value;
        return $this;
    }

    public function setHeaderTemplate($value){
        $this->header_template = $value;
        return $this;
    }

    public function setNodeFolderTemplate($value){
        $this->node_folder_template = $value;
        return $this;
    }

    public function setNodeItemTemplate($value){
        $this->node_item_template = $value;
        return $this;
    }

    public function setTreeTemplate($value){
        $this->template = $value;
        return $this;
    }

    public function setTreeToolbar($value){
        if (!is_object($value))
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_TOOLBAR_MUST_BE_A_CLASS_OBJECT_MESSAGE'));

        $reflection = new ReflectionClass($value);

        if (!$reflection->implementsInterface('JeproshopTreeToolbarInterface'))
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_TOOLBAR_CLASS_MUST_IMPLEMENTS_MESSAGE') . ' JeproshopTreeToolbarInterface interface');

        $this->toolbar = $value;
        return $this;
    }

    public function getContext(){
        if (!isset($this->context)){
            $this->context = JeproshopContext::getContext();
        }
        return $this->context;
    }

    public function getTreeId(){
        return $this->tree_id;
    }

    public function getTreeTitle(){
        return $this->tree_title;
    }

    public function getTreeToolbar(){
        if (isset($this->toolbar)){
            $this->toolbar->setTreeToolBarData($this->getTreeData());
        }
        return $this->toolbar;
    }

    public function getTreeData(){
        if (!isset($this->data))
            $this->data = array();

        return $this->data;
    }

    public function __toString(){
        return $this->render();
    }

    public function addAction($action){
        if (!isset($this->toolbar)){ $this->setTreeToolbar(new JeproshopTreeToolbar()); }

        $this->getTreeToolbar()->addTreeToolBarAction($action);
        return $this;
    }

    public function removeTreeActions(){
        if (!isset($this->toolbar))
            $this->setTreeToolbar(new JeproshopTreeToolbar());

        $this->getTreeToolbar()->removeTreeToolBarActions();
        return $this;
    }

    public function renderToolbar(){
        return $this->getTreeToolbar()->render();
    }

    public function useInput(){
        return isset($this->input_type);
    }

    public function useToolbar(){
        return isset($this->toolbar);
    }

    public function getInputName(){}

    public function getTreeTemplate() {
        if (!isset($this->template))
            $this->setTreeTemplate(self::DEFAULT_TEMPLATE);

        return $this->template;
    }

    public function getHeaderTemplate(){
        if (!isset($this->header_template)){
            $this->setHeaderTemplate(self::DEFAULT_HEADER_TEMPLATE);
        }
        return $this->header_template;
    }

    public function getNodeFolderTemplate(){
        if (!isset($this->node_folder_template))
            $this->setNodeFolderTemplate(self::DEFAULT_NODE_FOLDER_TEMPLATE);

        return $this->node_folder_template;
    }

    public function getNodeItemTemplate(){
        if (!isset($this->node_item_template))
            $this->setNodeItemTemplate(self::DEFAULT_NODE_ITEM_TEMPLATE);

        return $this->node_item_template;
    }

}

interface JeproshopTreeToolBarInterface
{
    public function __toString();
    public function setTreeToolBarActions($value);
    public function getTreeToolBarActions();
    public function setContext($value);
    public function getContext();
    public function setTreeToolBarData($value);
    public function getTreeToolBarData();
    public function setTreeToolBarTemplate($value);
    public function getTreeToolBarTemplate();
    public function addTreeToolBarAction($action);
    public function removeTreeToolBarActions();
    public function render();
}


interface JeproshopTreeToolbarButtonInterface
{
    public function __toString();
    public function setTreeToolBarAttribute($name, $value);
    public function getTreeToolBarAttribute($name);
    public function setTreeToolBarAttributes($value);
    public function getTreeToolBarAttributes();
    public function setTreeToolBarClass($value);
    public function getTreeToolBarClass();
    public function setContext($value);
    public function getContext();
    public function setTreeToolBarId($value);
    public function getTreeToolBarId();
    public function setTreeToolBarLabel($value);
    public function getTreeToolBarLabel();
    public function setTreeToolBarName($value);
    public function getTreeToolBarName();
    public function setTreeToolBarTemplate($value);
    public function getTreeToolBarTemplate();
    public function hasAttribute($name);
    public function render();
}