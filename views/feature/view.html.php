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

class JeproshopFeatureViewFeature extends JeproshopViewLegacy {
    public $features = NULL;

    public $feature = NULL;

    public $feature_value = NULL;

    function renderDetails($tpl = null){
        $featureModel = new JeproshopFeatureModelFeature();
        $this->features = $featureModel->getFeatureList();

        $pagination = $featureModel->getPagination();
        $this->assignRef('pagination', $pagination);
        $this->addToolBar();
        parent::display($tpl);
    }

    function renderAddForm($tpl = null){
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);

        $this->addToolBar();
        parent::display($tpl);
}

    public function renderEditForm($tpl = null){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);

        $featureValues = $this->feature->getFeatureValues($this->context->language->lang_id);
        $this->assignRef('feature_values', $featureValues);

        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditFeatureValueForm($tpl = null){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        
        $this->features =  JeproshopFeatureModelFeature::getFeatures($this->context->language->lang_id);

        $this->loadValue();

        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch ($task){
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_FEATURE_TITLE'), 'jeproshop-category');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            case 'edit':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_FEATURE_TITLE'), 'jeproshop-feature');
                JToolBarHelper::apply('update');
                JToolBarHelper::addNew('add_value', JText::_('COM_JEPROSHOP_ADD_NEW_VALUE_LABEL'));
                JToolBarHelper::cancel('cancel');
                break;
            case 'add_value' :
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_FEATURE_VALUE_LABEL'));
                JToolBarHelper::apply('save_value', JText::_('COM_JEPROSHOP_SAVE_VALUE_LABEL'));
                JToolBarHelper::cancel('cancel');
                break;
            case 'edit_value' :
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_FEATURE_VALUE_LABEL'));
                JToolBarHelper::apply('update_value', JText::_('COM_JEPROSHOP_UPDATE_VALUE_LABEL'));
                JToolBarHelper::cancel('cancel');
                break;
            default:
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_FEATURES_LIST_TITLE'), 'jeproshop-category');
                JToolBarHelper::addNew('add');
                JToolBarHelper::addNew('add_value', JText::_('COM_JEPROSHOP_ADD_NEW_VALUE_LABEL'));
                break;
        }
        $this->addSideBar('catalog');
    }

    public function loadObject($option = false){
        $app = JFactory::getApplication();
        $feature_id = $app->input->get('feature_id');
        if($feature_id && JeproshopTools::isUnsignedInt($feature_id)){
            if(!$this->feature){
                $this->feature = new JeproshopFeatureModelFeature($feature_id);
            }
            if(JeproshopTools::isLoadedObject($this->feature, 'feature_id')){
                return $this->feature;
            }
            JError::raiseError(500, JText::_('COM_JEPROSHOP_FEATURE_CANNOT_BE_LOADED_OR_FOUND_LABEL'));
        }elseif($option){
            if($this->feature){
                $this->feature = new JeproshopFeatureModelFeature();
            }
            return $this->feature;
        }else{
            JError::raiseError(500, JText::_('COM_JEPROSHOP_THE_FEATURE_CANNOT_BE_LOADED_THE_IDENTIFIER_IS_MISSING_OR_INVALID_MESSAGE'));
            return false;
        }
    }


    public function loadValue(){
        $app = JFactory::getApplication();
        $featureValueId = $app->input->get('feature_value_id');
        if($featureValueId && JeproshopTools::isUnsignedInt($featureValueId)){
            if(!$this->feature_value){
                $this->feature_value = new JeproshopFeatureValueModelFeatureValue($featureValueId);
            }
            if(!JeproshopTools::isLoadedObject($this->feature_value, 'feature_value_id')){
                $this->feature_value = null;
            }
            JError::raiseError(500, JText::_('COM_JEPROSHOP_FEATURE_VALUE_CANNOT_BE_LOADED_OR_FOUND_LABEL'));
        }else{
            if(isset($this->feature_value)){
                $this->feature_value = new JeproshopFeatureValueModelFeatureValue();
            }

            $this->feature_value;
        }
    }
}