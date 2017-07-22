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
<div class="form-box-wrapper"  id="product-price" >
    <div id="step-price" >
        <div class="panel" >
            <div class="panel-title"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_EDIT_PRICE_INFORMATION_TITLE'); ?></div>
            <div class="panel-content well" >
                <?php echo $this->productMultiShopCheckFields('Prices'); ?>
                <div class="alert alert-info"><?php echo JText::_('COM_JEPROSHOP_MUST_ENTER_EITHER_PRE_TAX_RETAIL_PRICE_MESSAGE'); ?></div>
                <div class="separation"></div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->productMultiShopCheckbox('wholesale_price', 'default'); ?>
                        <label for="jform_wholesale_price" title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_PRE_TAX_WHOLE_SALE_PRICE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_PRE_TAX_WHOLE_SALE_PRICE_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <div class="input-append" >
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input type="text" maxlength="14" name="price_field[wholesale_price]" id="jform_wholesale_price" value="<?php echo JeproshopTools::convertPrice($this->product->wholesale_price); ?>" onchange=" this.value.replace(/,/g,'.');" class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->suffix; ?></button><?php } ?>
                        </div>
                        <p class="preference-description small"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_WHOLE_SALE_PRICE_DESCRIPTION'); ?></p>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->productMultiShopCheckbox('price', 'price'); ?>
                        <label title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_PRE_TAX_RETAIL_PRICE_TITLE_DESC'); ?>" ><?php if(!$this->context->country->display_tax_label || $this->tax_exclude_tax_option){ echo JText::_('COM_JEPROSHOP_PRODUCT_RETAIL_PRICE_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_PRODUCT_PRE_TAX_RETAIL_PRICE_LABEL'); } ?></label>
                    </div>
                    <div class="controls">
                        <input type="hidden" id="jform_real_price_tax_excluded" name="price_field[price]" value=""/>
                        <div class="input-append" >
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input type="text" maxlength="14" id="jform_price_tax_excluded" name="price_field[price_displayed]" value="" class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->suffix; ?></button><?php } ?>
                        </div>
                        <p class="small" ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_PRE_TAX_RETAIL_PRICE_DESCRIPTION'); ?></p>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->productMultiShopCheckbox('tax_rules_group_id', 'default');  ?>
                        <label for="jform_tax_rules_group_id" title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_TAX_RULE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_TAX_RULE_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <span  >
                            <select  name="price_field[tax_rules_group_id]" id="jform_tax_rules_group_id" <?php if($this->tax_exclude_tax_option){ ?> disabled="disabled" <?php } ?> >
                                <option value="0"  ><?php echo JText::_('COM_JEPROSHOP_NO_TAX_LABEL'); ?></option>
                                <?php foreach($this->tax_rules_groups as $tax_rules_group){ ?>
                                    <option value="<?php echo $tax_rules_group->tax_rules_group_id; ?>" <?php if($this->product->getTaxRulesGroupId() == $tax_rules_group->tax_rules_group_id){ ?>selected="selected" <?php } ?> ><?php echo $tax_rules_group->name; ?></option>
                                <?php } ?>
                            </select>
                            <a class="button btn confirm_leave" href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=tax&task=add_rules_group&product_id=' . $this->product->product_id); ?>" >
                                <i class="icon-plus" ></i> &nbsp;<?php echo JText::_('COM_JEPROSHOP_CREATE_LABEL'); ?>
                            </a>
                            <?php if($this->tax_exclude_tax_option){ ?>
                                <span style="margin-left:10px; " ></span>
                                <input type="hidden" value="<?php echo $this->product->getTaxRulesGroupId(); ?>" name="price_field[tax_rules_group_id]" />
                            <?php } ?>
                        </span>
                    </div>
                </div>
                <?php if($this->tax_exclude_tax_option){ ?>
                    <div class="control-group alert" >
                        <?php echo JText::_('COM_JEPROSHOP_TAXES_ARE_CURRENTLY_DISABLED_LABEL'); ?>
                        <a class="btn btn-default" href="<?php echo JText::_('index.php?option=com_jeproshop&view=setting&task=tax'); ?>" ><?php  echo JText::_('COM_JEPROSHOP_CLICK_HERE_TO_OPEN_TAXES_CONFIGURATION_PAGE_LABEL'); ?>.</a>
                        <input type="hidden" value="<?php echo $this->product->getTaxRulesGroupId(); ?>" name="price_field[tax_rules_group_id]" />
                    </div>
                <?php } ?>
                <div class="control-group" <?php if(!$this->use_ecotax){ ?> style="display: none;" <?php } ?> >
                    <div class="control-label">
                        <?php echo $this->productMultiShopCheckbox('ecotax', 'default'); ?>
                        <label for="jform_ecotax" title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_PRICE_USE_ECO_TAX_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_PRICE_USE_ECO_TAX_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <div class="input-append" >
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input type="text" size="11" maxlength="14" id="jform_ecotax" name="price_field[ecotax]" value="<?php echo $this->product->ecotax; ?>"  class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->suffix; ?></button><?php } ?>
                        </div>
                        <span style="margin-left: 10px;" ><?php echo "(" . JText::_('COM_JEPROSHOP_ALREADY_INCLUDED_IN_PRICE_MESSAGE') . ")"; ?></span>
                    </div>
                </div>
                <div class="control-group" <?php if(!$this->context->country->country_display_tax_label || $this->tax_exclude_tax_option){ ?> style="display:none;" <?php } ?> >
                    <div class="control-label"><label for="jform_price_tax_included" title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_RETAIL_PRICE_WITH_TAX_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_RETAIL_PRICE_WITH_TAX_LABEL'); ?></label></div>
                    <div class="controls">
                        <div class="input-append" >
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input size="11" maxlength="14" id="jform_price_tax_included" type="text" value=""   class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->suffix; ?></button><?php } ?>
                        </div>
                        <input id="jform_price_type" name="price_field[price_type]" type="hidden" value="TE" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php  echo $this->productMultiShopCheckbox('unit_price','unit_price'); ?>
                        <label for="jform_unit_price" title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_UNIT_PRICE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_UNIT_PRICE_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <div class="input-append" >
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input id="jform_unit_price" name="price_field[unit_price]" type="text" value="<?php echo $this->unit_price;  ?>"  class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->suffix; ?></button><?php } ?>
                        </div> &nbsp;&nbsp;
                        <?php echo JText::_('COM_JEPROSHOP_PER_LABEL'); ?>&nbsp;&nbsp;<input id="jform_unity" name="price_field[unity]" type="text" value="<?php echo htmlentities($this->product->unity); ?>"  class="price-box" />
                        <?php if($this->use_tax && $this->context->country->country_display_tax_label){ ?>
                            <span style="margin-left:15px">
                                <?php echo JText::_('COM_JEPROSHOP_OR_LABEL'); ?>
                                <?php echo $this->context->currency->prefix; ?> <span id="jform_unit_price_with_tax">0.00</span><?php echo $this->context->currency->suffix; ?>
                                <?php echo JText::_('COM_JEPROSHOP_PER_LABEL'); ?> <span id="jform_unity_second"><?php echo $this->product->unity; ?></span> <?php echo JText::_('COM_JEPROSHOP_WITH_TAX_LABEL'); ?>
                            </span>
                        <?php } ?>
                        <p class="small" ><?php echo JText::_('COM_JEPROSHOP_EG_LABEL') . " " . JText::_('COM_JEPROSHOP_PER_LABEL') . " " . JText::_('COM_JEPROSHOP_UNIT_LABEL'); ?></p>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php  echo $this->productMultiShopCheckbox('on_sale','default'); ?>
                        <label title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_ON_SALE_PRICE_TITLE_DESC'); ?>" >&nbsp;</label>
                    </div>
                    <div class="controls">
                        <p class="checkbox"  >
                            <input type="checkbox" name="price_field[on_sale]" id="jform_on_sale" style="padding-top: 5px;" <?php if($this->product->on_sale){ ?>checked="checked" <?php } ?> value="1" /><label for="jform_on_sale" class="t" style="font-weight: bold; "><?php echo JText::_('COM_JEPROSHOP_PRODUCT_DISPLAY_ON_SALE_ICON_MESSAGE'); ?></label>
                        </p>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_FINAL_RETAIL_PRICE_TITLE_DESC'); ?>" ><b><?php echo JText::_('COM_JEPROSHOP_PRODUCT_FINAL_RETAIL_PRICE_LABEL'); ?></b></label></div>
                    <div class="controls">
                        <span style="font-weight: bold;">
                            <?php echo $this->context->currency->prefix; ?><span id="jform_final_price" >0.00</span><?php echo $this->context->currency->suffix; ?>
                            <span <?php if(!$this->use_tax){ ?> style="display:none; " <?php } ?> > ( <?php echo JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL'); ?>)</span>
                        </span>
                        <span <?php if(!$this->use_tax){ ?> style="display:none; " <?php } ?> >
                            <?php if($this->context->country->display_tax_label){ echo ' / '; } ?>
                            <?php echo $this->context->currency->prefix . " "; ?><span id="jform_final_price_without_tax" ></span><?php echo  ' ' . $this->context->currency->suffix; if( $this->context->country->display_tax_label){ echo ' (' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ')'; } ?>
                        </span>
                    </div>
                </div>
                <div class="panel-footer">
                    <a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=product'); ?>" class="btn btn-default"><i class="process-icon-cancel"></i> <?php echo JText::_('COM_JEPROSHOP_CANCEL_LABEL'); ?></a>
                    <button type="submit" name="save_price" class="btn btn-default pull-right"  onclick="Joomla.submitbutton('save_price'); " ><i class="process-icon-save"></i> <?php echo JText::_('COM_JEPROSHOP_SAVE_AND_STAY_LABEL'); ?></button>
                </div>
            </div>
        </div>
        <div class="panel" >
            <?php if(isset($this->specific_price_modification_form)) { ?>
            <div class="panel-title" ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_SPECIFIC_PRICE_LABEL'); ?></div>
            <div class="panel-content well" >
                <div class="hint" style="display:none; min-height:0;">
                    <?php echo JText::_('COM_JEPROSHOP_PRODUCT_SPECIFIC_PRICE_SETTING_MESSAGE'); ?>
                </div>
                <div class="control-group" >
                    <div class="control-label" ></div>
                    <div class="controls" >
                        <br /><a class="button btn btn-icon" href="#" id="jform_show_specific_price"  onclick="Joomla.submitbutton('add_specific_price'); "><i class="add-icon" ></i> <span><?php echo JText::_('COM_JEPROSHOP_ADD_NEW_SPECIFIC_PRICE_LABEL'); ?></span></a>
                        <a class="button bt-icon" href="#" id="jform_hide_specific_price" style="display:none" ><i class="cross-icon" ></i> <span><?php echo JText::_('COM_JEPROSHOP_CANCEL_NEW_SPECIFIC_PRICE_LABEL'); ?></span></a>
                        <br/>
                    </div>
                </div>
                <div class="control-group" id="jform_add_specific_price" style="display:none;">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_SPECIFIC_PRICE_SHOP_ID_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_SPECIFIC_PRICE_SHOP_ID_LABEL'); ?></label></div>
                    <div class="controls margin-form" >
                        <?php if(!$this->multi_shop){ ?>
                            <input type="hidden" name="price_field[sp_shop_id]" value="" />
                        <?php }else{ ?>
                            <select name="price_field[sp_shop_id]" id="jform_specific_price_shop_id" class="medium_select" >
                                <?php if(!$this->admin_one_shop){ ?><option value="0"><?php echo JText::_('COM_JEPROSHOP_ALL_SHOPS_LABEL'); ?></option><?php } ?>
                                <?php foreach($this->shops as $shop){ ?>
                                    <option value="<?php echo $shop->shop_id; ?>"><?php echo htmlentities($shop->name); ?></option>
                                <?php } ?>
                            </select>&nbsp;&gt;&nbsp;
                        <?php } ?>
                        <select name="price_field[sp_currency_id]" id="jform_specific_price_currency_0" class="medium_select" >
                            <option value="0"><?php echo JText::_('COM_JEPROSHOP_ALL_CURRENCIES_LABEL'); ?></option>
                            <?php foreach($this->currencies as $currency){ ?>
                                <option value="<?php echo $currency->currency_id; ?>"><?php echo htmlentities($currency->name); ?></option>
                            <?php } ?>
                        </select>&nbsp;&gt;&nbsp;
                        <select name="price_field[sp_country_id]" id="jform_specific_price_country_id" class="medium_select" >
                            <option value="0"><?php echo JText::_('COM_JEPROSHOP_ALL_COUNTRIES_LABEL'); ?></option>
                            <?php foreach($this->countries as $country){ ?>
                                <option value="<?php echo $country->country_id; ?>"><?php echo htmlentities($country->name); ?></option>
                            <?php } ?>
                        </select>&nbsp;&gt;&nbsp;
                        <select name="price_field[sp_group_id]" id="jform_specific_price_group_id" class="medium_select" >
                            <option value="0"><?php echo JText::_('COM_JEPROSHOP_ALL_GROUPS_LABEL'); ?></option>
                            <?php foreach($this->groups as $group){ ?>
                                <option value="<?php echo $group->group_id; ?>"><?php echo htmlentities($group->name); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_SPECIFIC_PRICE_CUSTOMER_ID_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_SPECIFIC_PRICE_CUSTOMER_ID_LABEL'); ?></label></div>
                    <div class="controls">
                        <input type="hidden" name="price_field[sp_customer_id]" id="jform_customer_id" value="0" />
                        <input type="text" name="price_field[customer]" value="<?php echo JText::_('COM_JEPROSHOP_ALL_CUSTOMERS_LABEL'); ?>" id="jform_customer" autocomplete="off" />
                        <i class="loading-icon"  id="jform_customer_loader" style="display: none;" ></i>
                        <div id="jform_customers"></div>
                    </div>
                </div>
                <?php if(count($this->combinations) != 0) { ?>
                    <div class="control-group">
                        <div class="control-label"><label><?php echo JText::_('COM_JEPROSHOP_SPECIFIC_PRICE_COMBINATION_LABEL'); ?></label></div>
                        <div class="controls">
                            <select id="jform_specific_price_product_attribute_id" name="price_field[sp_product_attribute_id]" >
                                <option value="0"><?php echo JText::_('COM_JEPROSHOP_APPLY_TO_ALL_COMBINATIONS_LABEL'); ?></option>
                                <?php foreach($this->combinations as $combination) { ?>
                                    <option value="<?php echo $combination->product_attribute_id; ?>"><?php echo $combination->attributes; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php }?>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_SPECIFIC_PRICE_AVAILABLE_FROM_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_SPECIFIC_PRICE_AVAILABLE_FROM_LABEL') ; ?></label></div>
                    <div class="controls">
                        <input class="datepicker input-date" type="text" name="price_field[specific_price_from]" value="" style="text-align: center" id="jform_specific_price_from"  /><span style="font-weight:bold; color:#000000; font-size:12px; padding: 5px;"><?php echo " ". JText::_('COM_JEPROSHOP_TO_LABEL') . " "; ?></span>
                        <input class="datepicker input-date" type="text" name="price_field[specific_price_to]" value="" style="text-align: center" id="jform_specific_price_to" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label><?php echo JText::_('COM_JEPROSHOP_STARTING_AT_LABEL'); ?></label></div>
                    <div class="controls">
                        <input type="text" name="price_field[specific_price_from_quantity]" value="1" size="3" class="quantity-box"/> <span style="font-weight:bold; color:#000000; font-size:12px"><?php echo JText::_('COM_JEPROSHOP_UNIT_LABEL'); ?></span>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <label>
                            <?php echo JText::_('COM_JEPROSHOP_PRODUCT_PRICE_LABEL'); ?>
                            <?php if($this->context->country->country_display_tax_label){ echo JText::_('COM_JEPROSHOP_PRODUCT_PRICE_TAX_EXCLUDED_LABEL'); } ?>
                        </label>
                    </div>
                    <div class="controls" ><div class="input-append">
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input type="text" disabled="disabled" name="price_field[specific_price_price]" id="jform_specific_price_price" value="<?php echo $this->product->price; ?>" class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->suffix; ?></button><?php } ?>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label for="jform_leave_base_price" ><?php echo JText::_('COM_JEPROSHOP_LEAVE_BASE_PRICE_LABEL'); ?></label></div>
                    <div class="controls"><p class="checkbox" ><input id="jform_leave_base_price" type="checkbox" value="1" checked="checked" name="price_field[leave_base_price]" /></p></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label><?php echo JText::_('COM_JEPROSHOP_APPLY_DISCOUNT_OF_LABEL'); ?></label></div>
                    <div class="controls">
                        <input type="text" name="price_field[specific_price_reduction]" value="0.00" class="price-box" />&nbsp;
                        <select name="price_field[specific_price_reduction_type]" id="jform_reduction" class="medium-select" >
                            <option selected="selected" >---</option>
                            <option value="amount"><?php echo JText::_('COM_JEPROSHOP_AMOUNT_LABEL'); ?></option>
                            <option value="percentage"><?php echo JText::_('COM_JEPROSHOP_PERCENTAGE_LABEL'); ?></option>
                        </select>
                        <p class="field_description"><?php echo JText::_('COM_JEPROSHOP_DISCOUNT_APPLIED_AFTER_TAX_MESSAGE'); ?></p>
                    </div>
                </div>
                <?php if($this->specific_price_modification_form){ ?>
                <div class="control-group">
                    <table class="table table-striped" >
                        <thead>
                            <tr>
                                <th><?php echo JText::_('COM_JEPROSHOP_RULES_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_COMBINATION_LABEL'); ?></th>
                                <?php if(JeproshopShopModelShop::isFeaturePublished()){ ?><th><?php echo JText::_('COM_JEPROSHOP_SHOP_LABEL'); ?></th><?php } ?>
                                <th><?php echo JText::_('COM_JEPROSHOP_CURRENCY_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_GROUP_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_CUSTOMER_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_FIXED_PRICE_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_IMPACT_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_PERIOD_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_FROM_LABEL'); ?></th>
                                <th><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!isset($this->specific_prices) || !is_array($this->specific_prices) || !count($this->specific_prices)){ ?>
                            <tr>
                                <td class="center warning" colspan="13" ><i class="icon-warning-sign"></i>&nbsp;
                                    <?php echo JText::_('COM_JEPROSHOP_NO_SPECIFIC_PRICES_MESSAGE'); ?>
                                </td>
                            </tr>
                            <?php }else{
                                $i = 0;
                                foreach ($this->specific_prices as $specificPrice){
                                    $currentSpecificCurrency = $this->currencies[($specificPrice->currency_id ? $specificPrice->currency_id : $this->default_currency->currency_id)];
                                    if ($specificPrice->reduction_type == 'percentage'){
                                        $impact = '- '.($specificPrice->reduction * 100).' %';
                                    }elseif ($specificPrice->reduction > 0){
                                        $impact = '- '. JeproshopTools::displayPrice(JeproshopTools::roundPrice($specificPrice->reduction, 2), $currentSpecificCurrency);
                                    }else{
                                        $impact = '--';
                                    }

                                    if ($specificPrice->from == '0000-00-00 00:00:00' && $specificPrice->to == '0000-00-00 00:00:00') {
                                        $period = JText::_('COM_JEPROSHOP_UNLIMITED_LABEL');
                                    }else {
                                        $period = JText::_('COM_JEPROSHOP_FROM_LABEL') . ' ' . ($specificPrice->from != '0000-00-00 00:00:00' ? $specificPrice['from'] : '0000-00-00 00:00:00') . '<br />' . JText::_('COM_JEPROSHOP_TO_LABEL') . ' ' . ($specificPrice['to'] != '0000-00-00 00:00:00' ? $specificPrice['to'] : '0000-00-00 00:00:00');
                                    }

                                    if ($specificPrice->product_attribute_id){
                                        $combination = new JeproshopCombinationModelCombination((int)$specificPrice->product_attribute_id);
                                        $attributes = $combination->getAttributesName((int)$this->context->language->lang_id);
                                        $attributes_name = '';
                                        foreach ($attributes as $attribute){
                                            $attributes_name .= $attribute->name .' - ';
                                        }
                                        $attributes_name = rtrim($attributes_name, ' - ');
                                    }else{
                                        $attributes_name = JText::_('COM_JEPROSHOP_ALL_COMBINATIONS_LABEL');
                                    }

                                    $rule = new JeproshopSpecificPriceRuleModelSpecificPriceRule((int)$specificPrice->specific_price_rule_id);
                                    $rule_name = ($rule->specific_price_rule_id ? $rule->name : '--');

                                    if ($specificPrice->customer_id){
                                        $customer = new JeproshopCustomerModelCustomer((int)$specificPrice->customer_id);
                                        if (JeproshopTools::isLoadedObject($customer, 'customer_id'))
                                            $customer_full_name = $customer->firstname.' '.$customer->lastname;
                                        unset($customer);
                                    }
                                    if (!$specificPrice->shop_id || in_array($specificPrice->shop_id, JeproshopShopModelShop::getContextListShopID())){
                                ?>
                            <tr class="row_<?php echo ($i % 2 ? '0' : '1'); ?>">
                                <td><?php echo  $rule_name; ?></td>
                                <td><?php echo $attributes_name; ?></td>
                                <?php $can_delete_specific_prices = true;
                                        if (JeproshopShopModelShop::isFeaturePublished()){
                                            $sp_shop_id = $specificPrice->shop_id;
                                            $can_delete_specific_prices = (count($this->context->employee->getAssociatedShops()) > 1 && !$sp_shop_id) || $sp_shop_id;
                                ?>
                                <td><?php echo ($sp_shop_id ? $this->shops[$sp_shop_id]['name'] : JText::_('COM_JEPROSHOP_ALL_SHOPS_LABEL')); ?></td>
                                        <?php }
                                $price = JeproshopTools::roundPrice($specificPrice->price, 2);
                                $fixed_price = ($price == JeproshopTools::roundPrice($this->product->price, 2) || $specificPrice->price == -1) ? '--' : JeproshopTools::displayPrice($price, $currentSpecificCurrency);
                                ?>
                                <td><?php echo ($specificPrice->currency_id ? $this->currencies[$specificPrice->currency_id]->name : JText::_('COM_JEPROSHOP_ALL_CURRENCIES_LABEL')); ?></td>
                                <td><?php echo ($specificPrice->country_id ? $this->countries[$specificPrice->country_id]->name : JText::_('COM_JEPROSHOP_ALL_COUNTRIES_LABEL')); ?></td>
                                <td><?php echo ($specificPrice->group_id ? $this->groups[$specificPrice->group_id]->name : JText::_('COM_JEPROSHOP_ALL_GROUPS_LABEL')); ?></td>
                                <td title="<?php echo  JText::_('COM_JEPROSHOP_ID_LABEL') . ' '.$specificPrice->customer_id; ?>"><?php echo (isset($customer_full_name) ? $customer_full_name : JText::_('COM_JEPROSHOP_ALL_CUSTOMERS_LABEL')); ?></td>
                                <td><?php echo $fixed_price; ?></td>
                                <td><?php echo $impact; ?></td>
                                <td><?php echo $period; ?></td>
                                <td><?php echo $specificPrice->from_quantity; ?></td>
                                <td><?php if(!$rule->specific_price_rule_id && $can_delete_specific_prices){ ?>
                                        <a class="btn btn-default" name="delete_link" href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=price&product_id='.(int)$app->input->get('product_id').'&task=delete_specific_price&specific_price_id='.(int)($specificPrice->specific_price_id).'&' . JSession::getFormToken() .'=1'); ?>"><i class="icon-trash"></i></a>
                                    <?php } ?>
                                </td>
                            </tr>

                            <?php       $i++;
                                        unset($customer_full_name);
                                    }
                                }
                            } ?>
                        </tbody>
                    </table>
                    <?php // Not use id_customer
                    if ($this->specific_price_priorities[0] == 'customer_id'){ unset($this->specific_price_priorities[0]); }
                    // Reindex array starting from 0
                    $specificPricePriorities = array_values($this->specific_price_priorities);  ?>
                    <div class="panel">
                        <div class="panel-title" ><?php echo JText::_('CIM_JEPROSHOP_PRIORITY_MANAGEMENT_LABEL'); ?></div>
                        <div class="panel-content" >
                            <div class="alert alert-info center" style="width: 98%;">
                                <?php echo JText::_('COM_JEPROSHOP_SOMETIMES_ONE_CUSTOMER_CAN_FIT_INTO_MULTIPLE_PRICE_RULES_MESSAGE') . ' ' .
                                JText::_('COM_JEPROSHOP_PRIORITIES_ALLOW_YOU_TO_DEFINE_WHICH_RULE_APPLIES_TO_THE_CUSTOMER_MESSAGE'); ?>
                            </div>
                            <div class="input-group center" >
                                <?php if(isset($specificPricePriorities[0])){ ?>
                                <select id="jform_specific_price_priority_1" name="price_field[specific_price_priority[]]" class="middle-size" >
                                    <option value="shop_id" <?php echo ($specificPricePriorities[0] == 'shop_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_SHOP_LABEL'); ?></option>
                                    <option value="currency_id" <?php echo ($specificPricePriorities[0] == 'currency_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_CURRENCY_LABEL'); ?></option>
                                    <option value="country_id" <?php echo ($specificPricePriorities[0] == 'country_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?></option>
                                    <option value="group_id" <?php echo ($specificPricePriorities[0] == 'group_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_GROUP_LABEL'); ?></option>
                                </select>
                                <?php } if(isset($specificPricePriorities[1])){ ?>
                                &nbsp;<span class="input-group-addon"><i class="icon-chevron-right"></i></span>&nbsp;
                                <select id="jform_specific_price_priority_2" name="price_field[specific_price_priority[]]" class="middle-size" >
                                    <option value="shop_id" <?php echo ($specificPricePriorities[1] == 'shop_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_SHOP_LABEL'); ?></option>
                                    <option value="currency_id" <?php echo ($specificPricePriorities[1] == 'currency_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_CURRENCY_LABEL'); ?></option>
                                    <option value="country_id" <?php echo ($specificPricePriorities[1] == 'country_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?></option>
                                    <option value="group_id" <?php echo ($specificPricePriorities[1] == 'group_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_GROUP_LABEL'); ?></option>
                                </select>
                                <?php } if(isset($specificPricePriorities[2])){ ?>
                                &nbsp;<span class="input-group-addon"><i class="icon-chevron-right"></i></span>&nbsp;
                                <select id="jform_specific_price_priority_3" name="price_field[specific_price_priority[]]" class="middle-size" >
                                    <option value="shop_id" <?php echo ($specificPricePriorities[2] == 'shop_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_SHOP_LABEL'); ?></option>
                                    <option value="currency_id" <?php echo ($specificPricePriorities[2] == 'currency_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_CURRENCY_LABEL'); ?></option>
                                    <option value="country_id" <?php echo ($specificPricePriorities[2] == 'country_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?></option>
                                    <option value="group_id" <?php echo ($specificPricePriorities[2] == 'group_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_GROUP_LABEL'); ?></option>
                                </select>
                                <?php } if(isset($specificPricePriorities[3])){ ?>
                                &nbsp;<span class="input-group-addon"><i class="icon-chevron-right"></i></span>&nbsp;
                                <select id="jform_specific_price_priority_4" name="price_field[specific_price_priority[]]" class="middle-size" >
                                    <option value="shop_id" <?php echo ($specificPricePriorities[3] == 'shop_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_SHOP_LABEL'); ?></option>
                                    <option value="currency_id" <?php echo ($specificPricePriorities[3] == 'currency_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_CURRENCY_LABEL'); ?></option>
                                    <option value="country_id" <?php echo ($specificPricePriorities[3] == 'country_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?></option>
                                    <option value="group_id" <?php echo ($specificPricePriorities[3] == 'group_id' ? ' selected="selected"' : ''); ?> ><?php echo JText::_('COM_JEPROSHOP_GROUP_LABEL'); ?></option>
                                </select>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

