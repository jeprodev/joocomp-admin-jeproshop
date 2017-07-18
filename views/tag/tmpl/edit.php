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

?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=tag'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setCatalogSubMenu('tag'); ?>
        <div class="separation" ></div>
        <div class="panel" >
            <div class="panel-title" ><?php if($this->tag->tag_id){ echo JText::_('COM_JEPROSHOP_EDIT_TAG_LABEL'); }else { echo JText::_('COM_JEPROSHOP_ADD_NEW_TAG_LABEL'); } ?></div>
            <div class="panel-content well" >
                <div class="control-group" >
                    <div class="control-label" ><label title="<?php echo JText::_('COM_JEPROSHOP_TAG_NAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_NAME_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('name', 'jform_', 'text',true, $this->tag->name); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_select_left"><?php echo JText::_('COM_JEPROSHOP_PRODUCTS_LABEL'); ?></label></div>
                    <div class="controls" >
                        <div class="one-third">
                            <select multiple id="jform_select_left" >
                                <?php foreach($this->products_unselected as $product){ ?>
                                    <option value="<?php echo $product->product_id; ?>"><?php echo $product->name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="one-third">
                            <a href="#" id="move_to_right" class="btn btn-default btn-block multiple_select_add">
                                <?php echo JText::_('COM_JEPROSHOP_ADD_LABEL'); ?> <i class="icon-arrow-right"></i>
                            </a>
                            <a href="#" id="move_to_left" class="btn btn-default btn-block multiple_select_remove">
                                <i class="icon-arrow-left"></i> <?php echo JText::_('COM_JEPROSHOP_REMOVE_LABEL'); ?>
                            </a>
                        </div>
                        <div class="one-third">
                            <select multiple id="jform_select_right" name="jform[products[]]" >
                                <?php foreach($this->products as $product){ ?>
                                    <option selected="selected" value="<?php echo $product->product_id; ?>"><?php echo $product->name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>