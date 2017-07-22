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
        <?php echo $this->renderLocalisationSubMenu('country'); ?>
        <div class="separation"></div>
        <div class="panel" >
            <div class="panel-content well" >
                <div class="control-group">
                    <div class="control-label" ><label for="jform_name" title="<?php echo JText::_('COM_JEPROSHOP_COUNTRY_NAME_TITLE_DESC') . ' - ' . JText::_('COM_JEPROSHOP_INVALID_CHARACTERS_LABEL'); ?>" ><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('name', 'jform', 'text', true, $this->country->name); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_iso_code" title="<?php echo JText::_('COM_JEPROSHOP_ISO_CODE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ISO_CODE_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_iso_code" name="jform[iso_code]" required="required" maxlength="3" value="<?php echo $this->country->iso_code; ?>" class="uppercase" /></div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_call_prefix" title="<?php echo JText::_('COM_JEPROSHOP_CALL_PREFIX_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_CALL_PREFIX_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_call_prefix" name="jform[call_prefix]" required="required" maxlength="3" value="<?php echo $this->displayCallPrefix($this->country->call_prefix); ?>" class="uppercase" /></div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_need_zip_code" title="<?php echo JText::_('COM_JEPROSHOP_NEED_ZIP_CODE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_NEED_ZIP_CODE_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('need_zip_code', 'edit', $this->country->need_zip_code); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_zip_code_format" title="<?php echo JText::_('COM_JEPROSHOP_ZIP_CODE_FORMAT_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ZIP_CODE_FORMAT_LABEL'); ?></label> </div>
                    <div class="controls" ><input type="text" id="jform_zip_code_format" name="jform[zip_code_format]" required="required" value="<?php echo $this->country->zip_code_format; ?>" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_address_layout" title="<?php echo JText::_('COM_JEPROSHOP_ADDRESS_LAYOUT_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LAYOUT_LABEL'); ?></label></div>
                    <div class="controls" >
                        <textarea id="jform_address_layout" name="jform[address_layout]" rows="10" style="float: left; margin-right: 10px;" ><?php echo $this->address_layout; ?></textarea>
                        <div >
                            <?php echo JText::_('COM_JEPROSHOP_REQUIRED_FIELD_FOR_THE_ADDRESS_CLICK_FOR_MORE_DETAILS_MESSAGE') . $this->display_valid_fields; ?>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_default_currency" title="<?php echo JText::_('COM_JEPROSHOP_DEFAULT_CURRENCY_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_DEFAULT_CURRENCY_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_default_currency" name="jform[default_currency]" >
                            <option value="0" ><?php echo JText::_('COM_JEPROSHOP_DEFAULT_SHOP_CURRENCY_LABEL'); ?></option>
                            <?php foreach($this->currencies as $currency){ ?>
                                <option value="<?php echo $currency->currency_id; ?>" <?php if($this->country->currency_id == $currency->currency_id){ ?> selected="selected" <?php } ?> ><?php echo ucfirst($currency->name); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_zone_id" title="<?php echo JText::_('COM_JEPROSHOP_GEOGRAPHICAl_REGION_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ZONE_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_zone_id" name="jform[zone_id]" >
                            <?php foreach($this->zones as $zone){ ?>
                                <option value="<?php echo $zone->zone_id; ?>" <?php if($zone->zone_id == $this->country->zone_id){ ?> selected="selected" <?php } ?> ><?php echo ucfirst($zone->name); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_display_country_customer" title="<?php echo JText::_('COM_JEPROSHOP_DISPLAY_COUNTRY_TO_CUSTOMERS_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PUBLISHED_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('published', 'edit', $this->country->published); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_contains_states" title="<?php echo JText::_('COM_JEPROSHOP_CONTAINS_STATES_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_CONTAINS_STATES_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('contains_states', 'edit', $this->country->contains_states); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_need_identification_number" title="<?php echo JText::_('COM_JEPROSHOP_NEED_IDENTIFICATION_NUMBER_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_NEED_IDENTIFICATION_NUMBER_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('need_identification_number', 'edit', $this->country->need_identification_number); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_display_tax_label" title="<?php echo JText::_('COM_JEPROSHOP_DISPLAY_TAX_LABEL_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_DISPLAY_TAX_LABEL_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('display_tax_label', 'edit', $this->country->display_tax_label); ?></div>
                </div>
                <?php if(JeproshopShopModelShop::isFeaturePublished()) { ?>
                    <div class="control-group">
                        <div class="control-label" ><label for="jform_associated_shop" title="<?php echo JText::_('COM_JEPROSHOP_ASSOCIATED_SHOP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ASSOCIATED_SHOP_LABEL'); ?></label></div>
                        <div class="controls" ><?php echo $this->associated_shop; ?></div>
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

