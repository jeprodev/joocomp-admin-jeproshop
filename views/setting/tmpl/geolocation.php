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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=setting');?>"  method="post" name="adminForm" id="adminForm" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo $this->renderSettingSubMenu('geolocation'); ?>
        <div class="separation"></div>
        <div class="panel" >
            <div class="panel-title" ><i class="icon-setting" ></i> <?php echo JText::_('COM_JEPROSHOP_GEOLOCATION_BY_IP_ADDRESS_SETTING_TITLE'); ?></div>
            <div class="panel-content well" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_improve_front_safety" title="<?php echo JText::_('COM_JEPROSHOP_ENABLE_GEOLOCATION_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_ENABLE_GEOLOCATION_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('enable_geolocation', 'edit', $this->enable_geolocation); ?></div>
                </div>
            </div>
        </div>
        <div class="separation" ></div>
        <div class="panel" >
            <div class="panel-title" ><i class="icon-map-marker" ></i> <?php echo JText::_('COM_JEPROSHOP_GEOLOCATION_OPTIONS_LABEL'); ?></div>
            <div class="panel-content well" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_geolocation_behavior" title="<?php echo JText::_('COM_JEPROSHOP_GEOLOCATION_RESTRICTED_COUNTRIES_BEHAVIOR_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_GEOLOCATION_RESTRICTED_COUNTRIES_BEHAVIOR_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_geolocation_behavior" name="jform[geolocation_behavior]" >
                            <option value="<?php echo COM_JEPROSHOP_GEOLOCATION_NO_CATALOG; ?>" <?php if($this->geolocation_behavior == COM_JEPROSHOP_GEOLOCATION_NO_CATALOG){ ?> selected="selected" <?php } ?>><?php echo JText::_('COM_JEPROSHOP_VISITOR_CANT_SEE_CATALOGS_LABEL'); ?></option>
                            <option value="<?php echo COM_JEPROSHOP_GEOLOCATION_NO_ORDER; ?>" <?php if($this->geolocation_behavior == COM_JEPROSHOP_GEOLOCATION_NO_ORDER){ ?> selected="selected" <?php } ?>><?php echo JText::_('COM_JEPROSHOP_VISITOR_CANT_PASS_ORDER_LABEL'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_geolocation_un_allowed_behavior" title="<?php echo JText::_('COM_JEPROSHOP_GEOLOCATION_ALLOWED_COUNTRY_BEHAVIOR_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_GEOLOCATION_ALLOWED_COUNTRIES_BEHAVIOR_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_geolocation_un_allowed_behavior" name="jform[geolocation_un_allowed_behavior]">
                            <option value="<?php echo COM_JEPROSHOP_GEOLOCATION_NO_CATALOG; ?>" <?php if($this->geolocation_un_allowed_behavior == COM_JEPROSHOP_GEOLOCATION_NO_CATALOG){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_VISITOR_CANT_SEE_CATALOGS_LABEL'); ?></option>
                            <option value="<?php echo COM_JEPROSHOP_GEOLOCATION_NO_ORDER; ?>" <?php if($this->geolocation_un_allowed_behavior == COM_JEPROSHOP_GEOLOCATION_NO_ORDER){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_VISITOR_CANT_PASS_ORDER_LABEL'); ?></option>
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
</form>
