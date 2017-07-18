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
                    <div class="control-label" ><label for="jform_attribute_group_id" title="<?php echo JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUPS_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUPS_LABEL'); ?></div>
                    <div class="controls" >
                        <select id="jform_attribute_group_id" name="jform[attribute_group_id]" required="required">
                            <?php foreach($this->attribute_groups as $attribute_group){ ?>
                                <option value="<?php echo $attribute_group->attribute_group_id; ?>" <?php if($attribute_group->attribute_group_id == $this->attribute->attribute_group_id){ ?>selected="selected" <?php } ?> ><?php echo $attribute_group->name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_NAME_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_NAME_LABEL'); ?></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('name', 'jform_', 'text',  true, $this->attribute->name, null, JText::_('COM_JEPROSHOP_INVALID_CHARACTERS_LABEL')); ?> </div>
                </div>
                <?php if(JeproshopShopModelShop::isFeaturePublished()){ ?>
                    <div class="control-group" >
                        <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_ASSOCIATED_SHOP_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_ASSOCIATED_SHOP_LABEL'); ?></div>
                        <div class="controls" ><?php echo $this->shop_tree; ?></div>
                    </div>
                <?php } ?>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_COLOR_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_COLOR_LABEL'); ?></div>
                    <div class="controls" ></div>
                </div>
                <!--div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_LABEL'); ?></div>
                    <div class="controls" ></div>
                </div-->
            </div>
        </div>
    </div>
</form>
