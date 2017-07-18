<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net
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

class jeproshopTagViewTag extends JeproshopViewLegacy {
    public $tag;

    public function renderDetails($tpl = null){
        $tagModel = new JeproshopTagModelTag();
        $tags = $tagModel->getTagsList();
        $this->assignRef('tags', $tags);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $this->tag = new JeproshopTagModelTag();
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $this->addToolBar();

        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $products = $this->tag->getProducts(true);
        $products_unselected = $this->tag->getProducts(false);
        $this->assignRef('products', $products);
        $this->assignRef('products_unselected', $products_unselected);

        $this->addToolBar();

        parent::display($tpl);
    }

    private function addToolBar()
    {
        switch ($this->getLayout()) {
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_TAG_TITLE'), 'jeproshop-category');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            default:
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_TAGS_LIST_TITLE'), 'jeproshop-category');
                JToolBarHelper::addNew('add');
                break;
        }
        $this->addSideBar('catalog');
    }

    public function loadObject($option = false){
        $app = JFactory::getApplication();
        $tag_id = $app->input->get('tag_id');
        if($tag_id && JeproshopTools::isUnsignedInt($tag_id)){
            if(!$this->tag){
                $this->tag = new JeproshopTagModelTag($tag_id);
            }
            if(JeproshopTools::isLoadedObject($this->tag, 'tag_id')){
                return $this->tag;
            }
            JError::raiseError(500, JText::_('COM_JEPROSHOP_TAG_CANNOT_BE_LOADED_OR_FOUND_LABEL'));
        }elseif($option){
            if($this->tag){
                $this->tag = new JeproshopTagModelTag();
            }
            return $this->tag;
        }else{
            JError::raiseError(500, JText::_('COM_JEPROSHOP_THE_TAG_CANNOT_BE_LOADED_THE_IDENTIFIER_IS_MISSING_OR_INVALID_MESSAGE'));
            return false;
        }
    }
}