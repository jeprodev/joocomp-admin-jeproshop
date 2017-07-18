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

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch ($task){
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_FEATURE_TITLE'), 'jeproshop-category');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            case 'edit':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_FEATURE_TITLE'), 'jeproshop-category');
                JToolBarHelper::apply('update');
                JToolBarHelper::addNew('add_value', JText::_('COM_JEPROSHOP_ADD_NEW_VALUE_LABEL'));
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
}