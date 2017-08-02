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

?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=attribute'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setCatalogSubMenu('attribute'); ?>
        <div class="separation" ></div>
        <div class="panel well">
            <div class="panel-content" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUP_NAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUP_NAME_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('name', 'jform', 'text', true, $this->attribute_group->name); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUP_PUBLIC_NAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUP_PUBLIC_NAME_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('public_name', 'jform', 'text', true, $this->attribute_group->public_name); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_IS_COLOR_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_IS_COLOR_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo  $this->helper->radioButton('is_color', 'jform', $this->attribute_group->is_color_group); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_TITLE_ATTRIBUTE_GROUP_GROUP_TYPE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_GROUP_TYPE_LABEL'); ?></label> </div>
                    <div class="controls" >
                        <select id="jform_attribute_group_type" name="jform[group_type]" >
                            <option value="select" <?php if($this->attribute_group->group_type == 'select'){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_SELECT_LABEL'); ?></option>
                            <option value="color" <?php if($this->attribute_group->group_type == 'color'){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_COLOR_LABEL'); ?></option>
                            <option value="radio" <?php if($this->attribute_group->group_type == 'radio'){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_RADIO_LABEL'); ?></option>
                        </select>
                    </div>
                </div>
                <!--div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_LABEL'); ?></label> </div>
                    <div class="controls" ></div>
                </div-->
            </div>
        </div>
        <div class="separation" ></div>
        <div class="panel well" >
            <div class="panel-content" >
                <table class="table table-striped" >
                    <thead>
                        <tr>
                            <th><?php echo JText::_('COM_JEPROSHOP_LABEL'); ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
