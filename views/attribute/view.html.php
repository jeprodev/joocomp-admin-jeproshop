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

class JeproshopAttributeViewAttribute extends JeproshopViewLegacy{
    protected $attribute;

    protected $attribute_group;
    
    protected $helper;

    public function renderDetails($tpl = null){
        $attributeModel = new JeproshopAttributeGroupModelAttributeGroup();
        $attribute_groups = $attributeModel->getAttributeGroupList();
        $this->assignRef('attribute_groups', $attribute_groups);
        $pagination = $attributeModel->getPagination();
        $this->assignRef('pagination', $pagination);
        $this->addToolBar();

        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $app = JFactory::getApplication();
        $attributeGroupId = $app->input->get('attribute_group_id');
        $attributeGroup = new JeproshopAttributeGroupModelAttributeGroup($attributeGroupId);
        $this->assignRef('attributeGroup', $attributeGroup);
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $this->addToolBar();

        parent::display($tpl);
    }

    public function editGroup($tpl =null){
        $app = JFactory::getApplication();
        $attributeGroupId = $app->input->get('attribute_group_id');
        $attributeGroup = new JeproshopAttributeGroupModelAttributeGroup($attributeGroupId);
        $this->assignRef('attributeGroup', $attributeGroup);
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $this->addToolBar();

        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        if(!isset($this->context)){ $this->context = JeproshopContext::getContext(); }
        $attributes_groups = JeproshopAttributeGroupModelAttributeGroup::getAttributesGroups($this->context->language->lang_id);
        $this->loadObject();
        $this->assignRef('attribute_groups', $attributes_groups);
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $this->addToolBar();

        parent::display($tpl);
    }

    public function renderAttributeGroupEditForm($tpl = null){
        if(!isset($this->context)){ $this->context = JeproshopContext::getContext(); }
        $this->loadAttributeGroup();
        $this->helper = new JeproshopHelper();
        $attributes = JeproshopAttributeModelAttribute::getAttributes($this->context->language->lang_id, true, $this->attribute_group->attribute_group_id);
        $this->assignRef('attributes', $attributes);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function loadObject($option = false){
        $app = JFactory::getApplication();
        $attribute_id = $app->input->get('attribute_id');
        if($attribute_id && JeproshopTools::isUnsignedInt($attribute_id)){
            if(!$this->attribute){
                $this->attribute = new JeproshopAttributeModelAttribute($attribute_id);
            }
            if(JeproshopTools::isLoadedObject($this->attribute, 'attribute_id')){
                return $this->attribute;
            }
            JError::raiseError(500, JText::_('COM_JEPROSHOP_ATTRIBUTE_CANNOT_BE_LOADED_OR_FOUND_LABEL'));
        }elseif($option){
            if($this->attribute){
                $this->attribute = new JeproshopAttributeModelAttribute();
            }
            return $this->attribute;
        }else{
            JError::raiseError(500, JText::_('COM_JEPROSHOP_THE_ATTRIBUTE_CANNOT_BE_LOADED_THE_IDENTIFIER_IS_MISSING_OR_INVALID_MESSAGE'));
            return false;
        }
    }

    public function loadAttributeGroup($option = false){
        $app = JFactory::getApplication();
        $attributeGroupId = $app->input->get('attribute_group_id');
        if($attributeGroupId && JeproshopTools::isUnsignedInt($attributeGroupId)){
            if(!$this->attribute_group){
                $this->attribute_group = new JeproshopAttributeGroupModelAttributeGroup($attributeGroupId);
            }
            if(JeproshopTools::isLoadedObject($this->attribute_group, 'attribute_group_id')){
                return $this->attribute_group;
            }
            JError::raiseError(500, JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUP_CANNOT_BE_LOADED_OR_FOUND_LABEL'));
        }else{
            if($this->attribute_group){
                $this->attribute_group = new JeproshopAttributeGroupModelAttributeGroup();
            }
            return $this->attribute_group;
        }
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch ($task) {
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_ATTRIBUTE_TITLE'), 'attribute-jeproshop');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            case 'edit' :
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_ATTRIBUTE_GROUP_TITLE'), 'attribute-jeproshop');
                JToolBarHelper::apply('update');
                JToolBarHelper::save('update_stay');
                JToolBarHelper::cancel('cancel');
                break;
            case 'add_group':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_ATTRIBUTE_TITLE'), 'attribute-jeproshop');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            case 'edit_group' :
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_ATTRIBUTE_GROUP_TITLE'), 'attribute-jeproshop');
                JToolBarHelper::apply('update_group');
                JToolBarHelper::cancel('cancel');
                break;
            default:
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUPS_LIST_TITLE'), 'attribute-jeproshop');
                JToolBarHelper::addNew('add');
                JToolBarHelper::custom('add_value', '', '', JText::_('COM_JEPROSHOP_ADD_ATTRIBUTE_VALUE_LABEL'));
                break;
        }
        $this->addSideBar('catalog');
    }
}