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

class JeproshopAttachmentViewAttachment extends JeproshopViewLegacy {
    public  $attachment ;

    public function renderDetails($tpl = null){
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        $this->addToolBar();
        parent::display($tpl);
    }

    public function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch ($task){
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
        $attachmentId = $app->input->get('attachment_id');
        if($attachmentId && JeproshopTools::isUnsignedInt($attachmentId)){
            if(!$this->attachment){
                $this->attachment = new JeproshopAttachmentModelAttachment($attachmentId);
            }
            if(JeproshopTools::isLoadedObject($this->attachment, 'attachment_id')){
                return $this->attachment;
            }
            JError::raiseError(500, JText::_('COM_JEPROSHOP_ATTRIBUTE_CANNOT_BE_LOADED_OR_FOUND_LABEL'));
        }elseif($option){
            if($this->attachment){
                $this->attachment = new JeproshopAttachmentModelAttachment();
            }
            return $this->attachment;
        }else{
            JError::raiseError(500, JText::_('COM_JEPROSHOP_THE_ATTRIBUTE_CANNOT_BE_LOADED_THE_IDENTIFIER_IS_MISSING_OR_INVALID_MESSAGE'));
            return false;
        }
    }
}