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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=address'); ?>" method="post" name="adminForm" id="adminForm" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo $this->renderCustomerSubMenu('address'); ?>
        <div class="separation"></div>
        <div class="panel" >
            <div class="panel-content well form-horizontal" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_customer_id" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_CUSTOMER_LABEL')); ?></label></div>
                    <div class="controls" >
                        <?php if(isset($this->customer)){ ?>
                            <a class="btn btn-default" href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=customer&task=view&customer_id=' . (int)$this->customer->customer_id . '&' . JeproshopTools::getCustomerToken() . '=1'); ?>">
                                <i class="icon-eye-open"></i> <?php echo '<span >' . $this->customer->lastname . ' ' . $this->customer->firstname . '</span> (' . $this->customer->email . ')'; ?>
                            </a>
                        <input type="hidden" name="jform[customer_id]" value="<?php echo $this->customer->customer_id; ?>" />
                        <input type="hidden" name="jform[email]" value="<?php echo $this->customer->email; ?>" />
                        <?php }else{ ?>
                            <script type="text/javascript">
                                $('input[name=jform[email]]').live('blur', function(e){
                                    var email = $(this).val();
                                    if (email.length > 5){
                                        var data = {};
                                        data.email = email;
                                        data.token = "{$token|escape:'html':'UTF-8'}";
                                        data.ajax = 1;
                                        data.controller = "AdminAddresses";
                                        data.action = "loadNames";
                                        $.ajax({
                                            type: "POST",
                                            url: "ajax-tab.php",
                                            data: data,
                                            dataType: 'json',
                                            async : true,
                                            success: function(msg){
                                                if (msg){
                                                    var infos = msg.infos.replace("\\'", "'").split('_');

                                                    $('input[name=firstname]').val(infos[0]);
                                                    $('input[name=lastname]').val(infos[1]);
                                                    $('input[name=company]').val(infos[2]);
                                                }
                                            },
                                            error: function(msg){}
                                        });
                                    }
                                });
                            </script>
                        <input type="text" id="jform_email" name="jform[email]" value="{$fields_value[$input.name]|escape:'html':'UTF-8'}"/>

                        <?php } ?>
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_fisc_identification_number" ><?php echo JText::_('COM_JEPROSHOP_FISC_IDENTIFICATION_NUMBER_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_fisc_identification_number" name="jform[fisc_identification_number]" value="<?php echo $this->address->vat_number; ?>" class="large-input" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_ADDRESS_ALIAS_LABEL')); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_alias" name="jform[alias]" value="<?php echo $this->address->alias; ?>" class="large-input" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_firstname" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_FIRSTNAME_LABEL')); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_firstname" name="jform[firstname]" value="<?php echo $this->address->firstname; ?>" class="large-input" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_lastname" ><?php echo JText::_('COM_JEPROSHOP_LASTNAME_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_lastname" name="jform[lastname]" value="<?php echo $this->address->lastname; ?>" class="large-input" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_company" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_COMPANY_LABEL')); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_company" name="jform[company]" value="" class="large_input" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_vat_number" ><?php echo JText::_('COM_JEPROSHOP_VAT_NUMBER_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_vat_number" name="jform[vat_number]" value=""  /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_address" ><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_address" name="jform[address]" value="<?php echo $this->address->address1; ?>"  /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_address2" ><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?>(2)</label></div>
                    <div class="controls" ><input type="text" id="jform_address2" name="jform[address2]" value="<?php echo $this->address->address2; ?>"  /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_postcode" ><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_postcode" name="jform[postcode]" value="<?php echo $this->address->postcode; ?>"  /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_city" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_CITY_LABEL')); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_city" name="jform[city]" value="<?php echo $this->address->city; ?>" class="large-input" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" ><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_zone" name="jform[zone]">
                            <?php foreach($this->zones as $zone){  ?>
                                <option value="<?php echo $zone->zone_id; ?>" <?php if($this->context->country->zone_id == $zone->zone_id){ ?>selected="selected" <?php } ?>><?php echo ucfirst($zone->name); ?></option>
                            <?php } ?>
                        </select>&nbsp;
                        <select id="jform_country" name="jform[country]">
                            <?php foreach($this->countries as $country){ ?>
                                <option value="<?php echo $country->country_id; ?>" <?php if($this->address->country_id == $country->country_id){ ?>selected="selected" <?php } ?>><?php echo ucfirst($country->name); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_phone" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_PHONE_LABEL')); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_phone" name="jform[phone]" value="<?php echo $this->address->phone; ?>" class="large-input" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_phone_mobile" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_MOBILE_PHONE_LABEL')); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_phone_mobile" name="jform[phone_mobile]" value="<?php echo $this->address->phone_mobile; ?>" class="large-input" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_other" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_OTHER_LABEL')); ?></label></div>
                    <div class="controls" ><textarea id="jform_other" name="jform[other]"  class="large-input" ><?php echo $this->address->other; ?></textarea></div>
                </div>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
