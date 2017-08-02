<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net

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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=country'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo $this->renderLocalisationSubMenu('zones'); ?>
        <div class="separation"></div>
        <div class="panel" >
            <div class="panel-title" >
                <i class="icon-globe" ></i>
                <?php
                if(isset($this->zone) && $this->zone->zone_id > 0) {
                    echo JText::_('COM_JEPROSHOP_YOU_ARE_ABOUT_TO_EDIT_LABEL') . ' ' . JText::_('COM_JEPROSHOP_ZONE_LABEL') . ' ' . $this->zone->name;
                }else {
                    echo JText::_('COM_JEPROSHOP_YOU_ARE_ABOUT_TO_ADD_LABEL') . ' ' . JText::_('COM_JEPROSHOP_ZONE_LABEL');
                } ?>
            </div>
            <div class="panel-content" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_name" title="<?php echo JText::_('COM_JEPROSHOP_ZONE_NAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ZONE_NAME_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_name" name="jform[name]" required="required" value="<?php echo (isset($this->zone) ? $this->zone->name : ''); ?>" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label title="<?php echo JText::_('COM_JEPROSHOP_ALLOW_DISALLOW_SHIPPING_TO_THIS_ZONE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ALLOW_DELIVERY_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('allow_delivery', 'edit', (isset($this->zone) ? $this->zone->allow_delivery : 0)); ?></div>
                </div>
                <?php if(JeproshopShopModelShop::isFeaturePublished()){ ?>
                    <div class="control-group" >
                        <div class="control-label" ><label title="<?php echo JText::_('COM_JEPROSHOP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_LABEL'); ?></label></div>
                        <div class="controls" ><?php echo $this->associatedShops; ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
