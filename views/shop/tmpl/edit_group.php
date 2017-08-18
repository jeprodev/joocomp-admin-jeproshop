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
$app = JFactory::getApplication();
?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=shop'); ?>" method="post" id="adminForm" name="adminForm"  class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container"  <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <div class="panel" >
            <?php echo $this->renderAdministrationSubMenu('shop_group'); ?>
            <div class="panel-title" >
                <?php echo ($this->shop_group->shop_group_id ? JText::_('COM_JEPROSHOP_YOU_ARE_ABOUT_TO_EDIT_LABEL') : JText::_('COM_JEPROSHOP_YOU_ARE_ABOUT_TO_ADD_LABEL')) . ' ' .JText::_('COM_JEPROSHOP_A_SHOP_GROUP_LABEL'); ?>
            </div>
            <div class="panel-content" >
                <div class="control-group" >
                    <div class="control-label" >
                        <label for="jform_name" title="<?php echo JText::_('COM_JEPROSHOP_SHOP_GROUP_NAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_SHOP_GROUP_NAME_LABEL'); ?></label>
                    </div>
                    <div class="controls" ><input type="text" name="jform[name]" id="jform_name" required="required" value="<?php echo $this->shop_group->name; ?>"/></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" >
                        <label for="jform_share_customer" title="<?php echo JText::_('COM_JEPROSHOP_SHARE_CUSTOMER_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_SHARE_CUSTOMER_LABEL'); ?></label>
                    </div>
                    <div class="controls" ><?php echo $this->helper->radioButton('share_customer', 'jform', 'add', $this->shop_group->share_customer, $this->disable_share_customer); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" >
                        <label for="jform_share_stock" title="<?php echo JText::_('COM_JEPROSHOP_SHARE_STOCK_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_SHARE_STOCK_LABEL'); ?></label>
                    </div>
                    <div class="controls" ><?php echo $this->helper->radioButton('share_stock', 'jform', 'add', $this->shop_group->share_stock, $this->disable_share_stock); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" >
                        <label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_SHARE_ORDER_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_SHARE_ORDER_LABEL'); ?></label>
                    </div>
                    <div class="controls" ><?php echo $this->helper->radioButton('share_orders', 'jform', 'add', $this->shop_group->share_order, $this->disable_share_order); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" >
                        <label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_SHOP_GROUP_PUBLISHED_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PUBLISHED_LABEL'); ?></label>
                    </div>
                    <div class="controls" ><?php echo $this->helper->radioButton('published', 'jform', 'add', $this->shop_group->published, $this->disable_published); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" >
                        <label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_SHOP_GROUP_DELETED_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_DELETED_LABEL'); ?></label>
                    </div>
                    <div class="controls" ><?php echo $this->helper->radioButton('deleted', 'jform', 'add', $this->shop_group->deleted, $this->disable_deleted); ?></div>
                </div>

            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="tab" value="group" />
    <input type="hidden" name="shop_group_id" value="<?php echo isset($this->shop_group) ? $this->shop_group->shop_group_id : 0; ?>" />
    <input type="hidden" name="return" value="<?php echo $app->input->get('return'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
