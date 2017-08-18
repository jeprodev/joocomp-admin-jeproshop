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

if(isset($this->product->product_id) && !$this->product->is_virtual){ ?>
    <div id="product-combinations" >
        <div id="step-association" class="panel" >
            <div class="panel-title"><?php echo JText::_('COM_JEPROSHOP_ADD_OR_MODIFY_COMBINATIONS_FOR_THIS_PRODUCT_TITLE');  ?></div>
            <div class="panel-content">
                <div class="alert alert-info center">
                    <?php echo JText::_('COM_JEPROSHOP_YOU_CAN_ALSO_USE_THE_PRODUCT_COMBINATIONS_IN_ORDER_TO_AUTOMATICALLY_CREATE_A_SET_OF_COMBINATIONS_MESSAGE'); ?>
                    <a class="btn btn-link btn-icon confirm-leave" href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=attribute&task=generator&product_id=' . $this->product->product_id); ?>" ><i class="icon-external-link-sign"></i></a>
                </div>
                <?php if($this->combination_exists){ ?>
                <div class="alert alert-info" style="display:block">
                    <?php
                    echo JText::_('COM_JEPROSHOP_SOME_COMBINATION_ALREADY_EXISTS_IF_YOU_WANT_TO_GENERATE_A_SET_OF_NEW_COMBINATION_THE_QUANTITIES_FOR_THE_EXISTING_COMBINATIONS_WILL_BE_LOST_MESSAGE') . '<br/>';
                    echo JText::_('COM_JEPROSHOP_YOU_CAN_ADD_A_SINGLE_COMBINATION_BY_CLICKING_THE_NEW_COMBINATION_BUTTON_MESSAGE');
                    ?>
                </div>
                <?php }
                if(isset($this->display_multishop_checkboxes) && $this->display_multishop_checkboxes){ ?>
                    <br />
                    <?php echo $this->productMultiShopCheckFields('combinations');
                } ?>
                <div class="panel-sub-title" style="margin-bottom: 15px;"><?php echo JText::_('COM_JEPROSHOP_ADD_OR_MODIFY_COMBINATION_FOR_THIS_PRODUCT_TITLE'); ?></div>
                <div class="control-group">
                    <div class="control-label"><label for="jform_attribute_group"><?php echo JText::_('COM_JEPROSHOP_ATTRIBUTE_GROUP_LABEL'); ?></label></div>
                    <div class="controls">
                        <select name="declination[attribute_group]" id="jform_attribute_group" >
                            <?php if(isset($this->attributes_groups)){
                                foreach($this->attributes_groups as $key => $attribute_group){ ?>
                                    <option value="<?php echo$attribute_group->attribute_group_id; ?>"><?php echo htmlentities($attribute_group->name); ?>&nbsp;&nbsp;</option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_attribute" ><?php echo JText::_('COM_JEPROSHOP_VALUE_LABEL'); ?></label></div>
                    <div class="controls" >
                        <div class="attribute_selector one-third" >
                            <select name="declination[attribute]" id="jform_attribute" >
                                <option value="0">--</option>
                            </select>
                        </div>
                        <div class="attribute_buttons center one-third">
                            <button id="jform_add_attribute_btn" type="button" class="btn btn-default" ><i class="icon-plus-sign-alt"></i> <?php echo JText::_('COM_JEPROSHOP_ADD_LABEL'); ?></button>
                            <br /><div style="margin-bottom: 30px;" ></div>
                            <button id="jform_delete_attribute_btn" type="button" class="btn btn-default" ><i class="icon-minus-sign-alt"></i> <?php echo JText::_('COM_JEPROSHOP_DELETE_LABEL'); ?></button>
                        </div>
                        <div class="selected_attribute one-third">
                            <select id="jform_product_attribute_list" name="declination[attribute_combination_list[]]" multiple="multiple" ></select>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="control-group">
                    <div class="control-label"	><label  for="jform_attribute_reference" title="<?php echo JText::_('COM_JEPROSHOP_ALLOWED_CHARACTERS_TITLE_DESC'); ?>"><?php echo JText::_('COM_JEPROSHOP_REFERENCE_CODE_LABEL'); ?></label></div>
                    <div class="controls">
                        <input type="text" id="jform_attribute_reference" name="declination[attribute_reference]" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_attribute_ean13"><?php echo JText::_('COM_JEPROSHOP_EAN_13_OR_JAN_BARCODE_LABEL'); ?></label></div>
                    <div class="controls">
                        <input maxlength="13" type="text" id="jform_attribute_ean13" name="declination[attribute_ean13]" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"	><label for="jform_attribute_upc"><?php echo  JText::_('COM_JEPROSHOP_UPC_BARCODE_LABEL'); ?></label></div>
                    <div class="controls">
                        <input maxlength="12" type="text" id="jform_attribute_upc" name="declination[attribute_upc]" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"	><label for="jform_attribute_isbn"><?php echo  JText::_('COM_JEPROSHOP_ISBN_LABEL'); ?></label></div>
                    <div class="controls">
                        <input maxlength="12" type="text" id="jform_attribute_isbn" name="declination[attribute_isbn]" value="" />
                    </div>
                </div>
                <hr/>
                <div class="control-group">
                    <div class="control-label">
                        <label for="jform_attribute_wholesale_price" title="<?php echo JText::_('COM_JEPROSHOP_SET_TO_ZERO_IF_THE_PRICE_DOES_NOT_CHANGE_TITLE_DESC'); ?>">
                            <?php echo $this->productMultiShopCheckFields('attribute_wholesale_price', 'default');
                            echo JText::_('COM_JEPROSHOP_WHOLESALE_PRICE_LABEL');
                            ?>
                        </label>
                    </div>
                    <div class="controls" >
                        <div class="input-append" >
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input type="text" name="declination[attribute_wholesale_price]" id="jform_attribute_wholesale_price" value="0"  class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->suffix; ?></button><?php } ?>
                        </div>
                        <p style="display:none;" id="jform_attribute_wholesale_price_full">( <?php echo JText::_('COM_JEPROSHOP_OVERRIDES_WHOLESALE_PRICE_FROM_THE_PRICES_TAB'); ?> )</p>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <label for="jform_attribute_price_impact">
                            <?php  echo $this->productMultiShopCheckFields('attribute_price_impact', 'attribute_price_impact');
                            echo JText::_('COM_JEPROSHOP_IMPACT_ON_PRICE_LABEL');  ?>
                        </label>
                    </div>
                    <div class="controls">
                        <p class="col-lg-4">
                            <select name="declination[attribute_price_impact]" id="jform_attribute_price_impact" >
                                <option value="0"><?php echo JText::_('COM_JEPROSHOP_NONE_LABEL'); ?></option>
                                <option value="1"><?php echo JText::_('COM_JEPROSHOP_INCREASE_LABEL'); ?></option>
                                <option value="-1"><?php echo JText::_('COM_JEPROSHOP_DECREASE_LABEL'); ?></option>
                            </select>&nbsp;<?php echo JText::_('COM_JEPROSHOP_FOR_LABEL'); ?>&nbsp;
                        <div class="input-append" >
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input type="hidden"  id="jform_attribute_real_price_tax_excluded" name="declination[attribute_price]" value="0.00" />
                            <input type="text" id="jform_attribute_price" name="declination[attribute_price]" value="0.00" class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->suffix; ?></button><?php } ?>
                        </div>
                        <?php if($this->context->country->country_display_tax_label){ echo " " . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL'); } ?>&nbsp;
                        <?php if(!$this->tax_exclude_tax_option){  echo " " . JText::_('COM_JEPROSHOP_OR_LABEL') . " ";?>&nbsp;
                            <div class="input-append" >
                            <?php if($this->currency->prefix != ""){ ?><button type="button" class="btn"  ><?php echo $this->currency->prefix; ?></button><?php } ?>
                            <input type="text" name="declination[attribute_price_tax_included]" id="jform_attribute_price_tax_included" value="0.00"  class="price-box" />
                            <?php if($this->currency->suffix != ""){ ?><button type="button" class="btn"  ><?php echo $this->currency->suffix; ?></button><?php } ?>
                            </div><?php
                            echo " " . JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL');
                        } ?>
                        </p>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label" ></div>
                    <div class="controls alert" >
                        <?php echo JText::_('COM_JEPROSHOP_THE_FINAL_PRODUCT_PRICE_WILL_BE_SET_TO_LABEL');
                        if($this->currency->format % 2 != 0){ echo $this->currency->sign; } ?>
                        <span id="jform_attribute_new_total_price">0.00</span>
                        <?php if($this->currency->format % 2 == 0){ echo $this->currency->sign; } ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label " >
                        <label  for="jform_attribute_weight_impact">
                            <?php echo $this->productMultiShopCheckbox('attribute_weight_impact', 'attribute_weight_impact');
                            echo JText::_('COM_JEPROSHOP_IMPACT_ON_WEIGHT_LABEL');  ?>
                        </label>
                    </div>
                    <div class="controls">
                        <select name="declination[attribute_weight_impact]" id="jform_attribute_weight_impact"  >
                            <option value="0"><?php echo JText::_('COM_JEPROSHOP_NONE_LABEL'); ?></option>
                            <option value="1"><?php echo JText::_('COM_JEPROSHOP_INCREASE_LABEL'); ?></option>
                            <option value="-1"><?php echo JText::_('COM_JEPROSHOP_DECREASE_LABEL'); ?></option>
                        </select>
                        &nbsp;	<?php echo JText::_('COM_JEPROSHOP_OF_LABEL'); ?> &nbsp;
                        <div class="input-append" >
                            <?php if($this->currency->format % 2 != 0){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->sign; ?></button><?php } ?>
                            <input type="text" name="declination[attribute_weight_price]" id="jform_attribute_weight_price" value="0.00" class="price-box" />
                            <?php if($this->currency->format % 2 == 0){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->sign; ?></button> <?php } ?>
                        </div>
                        &nbsp;<?php echo JText::_('COM_JEPROSHOP_PER_LABEL') . '&nbsp;'; echo $this->weight_unit; ?>
                    </div>
                </div>
                <div id="tr_unit_impact" class="control-group">
                    <div class="control-label" >
                        <label  for="jform_attribute_unit_impact">
                            <?php echo $this->productMultiShopCheckFields('attribute_unit_impact', 'attribute_unit_impact');
                            echo JText::_('COM_JEPROSHOP_IMPACT_ON_UNIT_PRICE_LABEL'); ?>
                        </label>
                    </div>
                    <div class="controls">
                        <select name="declination[attribute_unit_impact]" id="jform_attribute_unit_impact" >
                            <option value="0"><?php echo JText::_('COM_JEPROSHOP_NONE_LABEL'); ?></option>
                            <option value="1"><?php echo JText::_('COM_JEPROSHOP_INCREASE_LABEL'); ?></option>
                            <option value="-1"><?php echo JText::_('COM_JEPROSHOP_DECREASE_LABEL'); ?></option>
                        </select>&nbsp;	<?php echo JText::_('COM_JEPROSHOP_OF_LABEL'); ?> &nbsp;
                        <div class="input-append">
                            <?php if($this->currency->format % 2 != 0){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->sign; ?></button><?php } ?>
                            <input type="text" name="declination[attribute_unity_price]" id="jform_attribute_unity_price" value="0.00"  class="price-box" />
                            <?php if($this->currency->format % 2 == 0){ ?><button type="button" class="btn" id="jform_img" ><?php echo $this->currency->sign; ?></button> <?php } ?>
                        </div> / <span id="unity_third"><?php echo $this->product->unity; ?></span>
                    </div>
                </div>
                <?php if($this->use_ecotax){ ?>
                    <div class="control-group">
                        <div class="control-label">
                            <label  for="jform_attribute_ecotax" title="<?php echo JText::_('COM_JEPROSHOP_OVERRIDES_THE_ECOTAX_FROM_THE_PRICE_TAB_TITLE_DESC'); ?>" >
                                <?php echo $this->productMultiShopCheckFields('attribute_ecotax', 'default');
                                echo JText::_('COM_JEPROSHOP_ECOTAX_LABEL'); ?> ( <?php echo JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL'); ?> )
                            </label>
                        </div>
                        <div class="controls">
                            <div class="input-append">
                                <?php if($this->currency->format % 2 != 0){ ?><button type="button" class="btn"  ><?php echo $this->currency->sign; ?></button><?php } ?>
                                <input type="text" name="declination[attribute_ecotax_price]" id="jform_attribute_ecotax_price" value="0.00" class="price-box" />
                                <?php if($this->currency->format % 2 == 0){ ?><button type="button" class="btn"  ><?php echo $this->currency->sign; ?></button><?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="control-group">
                    <div class="control-label" >
                        <label for="jform_attribute_minimal_quantity" title="<?php echo JText::_('COM_JEPROSHOP_THE_MINIMUM_QUANTITY_TO_BUY_THIS_PRODUCT_SET_TO_ONE_TO_DISABLE_THIS_FEATURE_TITLE_DESC'); ?>" >
                            <?php echo $this->productMultiShopCheckFields('attribute_minimal_quantity', 'default');
                            echo JText::_('COM_JEPROSHOP_MINIMUM_QUANTITY_LABEL'); ?>
                        </label>
                    </div>
                    <div class="controls" >
                        <b>&times;</b> <input maxlength="6" name="declination[attribute_minimal_quantity]" id="jform_attribute_minimal_quantity" type="text" value="<?php echo $this->product->minimal_quantity; ?>" class="quantity-box" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <label  for="jform_available_date_attribute" title="<?php echo JText::_('COM_JEPROSHOP_IF_THIS_PRODUCT_IS_OUT_OF_STOCK_YOU_CAN_INDICATE_WHEN_THE_PRODUCT_WILL_BE_AVAILABLE_AGAIN_TITLE_DESC'); ?>" >
                            <?php echo $this->productMultiShopCheckFields('attribute_available_date' , 'default');   echo JText::_('COM_JEPROSHOP_AVAILABLE_DATE_LABEL'); ?>
                        </label>
                    </div>
                    <div class="controls">
                        <div class="input-append" >
                        <input class="input-date datepicker" id="jform_attribute_available_date" name="declination[attribute_available_date]" value="<?php echo isset($this->available_date) ? $this->available_date : date('Y-m-d'); ?>" type="text" />
                        <!-- button class="btn" ><i class="icon-calendar-empty"></i></-button -->
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"	><label ><?php echo JText::_('COM_JEPROSHOP_IMAGE_LABEL'); ?></label></div>
                    <div class="controls">
                        <?php if(count($this->images)){ ?>
                            <ul id="jform_image_attribute_id" class="list-inline">
                                <?php foreach($this->images as $key=> $image){ ?>
                                    <li>
                                        <input type="checkbox" name="declination[image_attribute_id[]]" value="<?php echo $image->image_id; ?>" id="jform_image_attribute_id_<?php echo $image->image_id; ?>" />
                                        <label for="jform_image_attribute_id_<?php echo $image->image_id; ?>" >
                                            <img class="img-thumbnail" src="<?php echo $this->context->controller->getProductImageLink("", $this->product->product_id . '_' . $image->image_id, "default_small"); ?>" 
                                                 alt="<?php echo $image->legend; ?>" title="<?php echo $image->legend; ?>" />
                                        </label>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <div class="alert alert-warning">
                                <?php echo JText::_('COM_JEPROSHOP_YOU_MUST_UPLOAD_AN_IMAGE_BEFORE_YOU_CAN_SELECT_ONE_FOR_COMBINATION_LABEL'); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <hr/>
                <div class="control-group">
                    <div class="control-label">
                        <label for="jform_default_attribute">
                            <?php  echo $this->productMultiShopCheckFields('default_attribute', 'default_attribute');
                            echo JText::_('COM_JEPROSHOP_DEFAULT_LABEL'); ?>
                        </label>
                    </div>
                    <div class="controls">
                        <p class="checkbox" >
                            <input type="checkbox" name="declination[default_attribute]" id="jform_default_attribute" value="1" />
                            <label for="jform_default_attribute">    <?php echo JText::_('COM_JEPROSHOP_MAKE_THIS_COMBINATION_THE_DEFAULT_COMBINATION_FOR_THIS_PRODUCT_MESSAGE'); ?> </label>
                        </p>
                    </div>
                </div>
                <div class="control-group">
                    <table class="table table-striped"  >
                        <thead>
                            <tr>
                                <th class="nowrap" ><?php echo '#'; ?></th>
                                <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_ATTRIBUTE_VALUE_PAIR_LABEL'); ?></th>
                                <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_PRICE_IMPACT_LABEL'); ?></th>
                                <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_WEIGHT_IMPACT_LABEL'); ?></th>
                                <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_REFERENCE_LABEL'); ?></th>
                                <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_EAN13_LABEL'); ?></th>
                                <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_ISBN_LABEL'); ?></th>
                                <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_UPC_LABEL'); ?></th>
                                <th class="nowrap pull-right" ><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($this->attribute_list) && count($this->attribute_list)) {
                                foreach($this->attribute_list as $index => $combination){
                                    $attribute = array($combination->group_name, $combination->attribute_name, $combination->attribute_id);
                                    asort($attribute);
                                    ?>
                            <tr>
                                <td class="nowrap" ><?php echo $index; ?></td>
                                <td class="nowrap" ><?php echo  $attribute[0] . ' - ' . $attribute[1]; ?></td>
                                <td class="nowrap center" ><?php echo $combination->unit_price_impact; ?></td>
                                <td class="nowrap center" ><?php echo $combination->weight . ' ' . JeproshopSettingModelSetting::getValue('weight_unit'); ?></td>
                                <td class="nowrap" ><?php echo $combination->reference; ?></td>
                                <td class="nowrap" ><?php echo $combination->ean13; ?></td>
                                <td class="nowrap" ><?php echo $combination->isbn; ?></td>
                                <td class="nowrap" ><?php echo $combination->upc; ?></td>
                                <td class="nowrap" >
                                    <div class="btn-group-action" >
                                        <div class="btn-group pull-right" >
                                            <a href="" class="btn btn-micro" ><i class="icon-edit" ></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_EDIT_LABEL'); ?></a>
                                            <button class="btn btn-micro dropdown_toggle" data-toggle="dropdown" ><i class="caret"></i> </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="" ></a></li>
                                                <li><a href="" ></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php    }
                            }else{ ?>

                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php  }
