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
$timeStamp = strtotime($this->customer->birthday);
$customerBirthDay = date('d', $timeStamp);
$customerBirthMonth = date('m', $timeStamp);
$customerBirthYear = date('Y', $timeStamp);
?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=customer'); ?>" method="post" name="adminForm" id="adminForm" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo $this->renderCustomerSubMenu('customer'); ?>
        <div class="separation"></div>
        <div class="panel form-horizontal" >
            <div class="panel-title" ><i class="icon-customer" ></i>
                <?php echo ($this->customer->customer_id > 0 ? JText::_('COM_JEPROSHOP_EDIT_CUSTOMER_LABEL') : JText::_('COM_JEPROSHOP_ADD_NEW_CUSTOMER_LABEL')); ?></div>
            <div class="panel-content well" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_title" title="<?php echo JText::_('COM_JEPROSHOP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_TITLE_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_title" name="jform[title]" class="year-wrapper" >
                            <option value="mr" <?php if($this->customer->title =='mr'){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MR_LABEL'); ?></option>
                            <option value="mrs" <?php if($this->customer->title =='mrs'){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MRS_LABEL'); ?></option>
                            <option value="miss" <?php if($this->customer->title =='miss'){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MISS_LABEL'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_firstname" title="<?php echo JText::_('COM_JEPROSHOP_FIRSTNAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_FIRSTNAME_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_firstname" name="jform[firstname]" value="<?php echo $this->customer->firstname; ?>" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_lastname" title="<?php echo JText::_('COM_JEPROSHOP_LASTNAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_LASTNAME_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_lastname" name="jform[lastname]" value="<?php echo $this->customer->lastname; ?>" required="required" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_email" title="<?php echo JText::_('COM_JEPROSHOP_EMAIL_ADDRESS_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_EMAIL_ADDRESS_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_email" name="jform[email]" value="<?php echo $this->customer->email; ?>" required="required" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_private_key" title="<?php echo JText::_('COM_JEPROSHOP_PASSWORD_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PASSWORD_LABEL'); ?></label></div>
                    <div class="controls" >
                        <input type="password" id="jform_private_key" name="jform[private_key]" value="<?php echo $this->customer->private_key; ?>" required="required" />
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_birth" title="<?php echo JText::_('COM_JEPROSHOP_BIRTHDAY_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_BIRTHDAY_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_day" name="jform[day]" class="day-wrapper" >
                            <?php foreach(JeproshopTools::dateDays() as $day){ ?>
                                <option value="<?php echo (int)$day; ?>"  <?php if($customerBirthDay == $day){ ?> selected="selected" <?php } ?> ><?php echo $day; ?></option>
                            <?php } ?>
                        </select>
                        <select id="jform_month" name="jform[month]" class="month-wrapper" >
                            <?php foreach(JeproshopTools::dateMonths() as $month){ ?>
                                <option value="<?php echo $month; ?>" <?php if($customerBirthMonth == $month){ ?> selected="selected" <?php } ?> ><?php echo $month; ?></option>
                            <?php } ?>
                        </select>
                        <select id="jform_year" name="jform[year]" class="year-wrapper" >
                            <?php foreach(JeproshopTools::dateYears() as $year){ ?>
                                <option value="<?php echo $year; ?>" <?php if($customerBirthYear == $year){ ?> selected="selected" <?php } ?> ><?php echo $year; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_published" title="<?php echo JText::_('COM_JEPROSHOP_ENABLE_OR_DISABLE_CUSTOMER_LOGIN_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PUBLISHED_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('published', 'add', $this->customer->published); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_newsletter" title="<?php echo JText::_('COM_JEPROSHOP_CUSTOMER_WILL_RECEIVE_YOUR_NEWS_LETTER_VIA_EMAIL_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_NEWSLETTER_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('newsletter', 'add', $this->customer->newsletter); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_optin" title="<?php echo JText::_('COM_JEPROSHOP_CUSTOMER_WILL_RECEIVE_YOUR_ADS_VIA_EMAIL_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_OPTIN_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('optin', 'add', $this->customer->optin); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_optin" title="<?php echo JText::_('COM_JEPROSHOP_CUSTOMER_WILL_RECEIVE_YOUR_ADS_VIA_EMAIL_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_GROUP_ACCESS_LABEL'); ?></label></div>
                    <div class="controls" >
                        <table class="table stripped-table" width="60%" >
                            <thead>
                            <tr>
                                <th class="nowrap center" width="2%">#</th>
                                <th class="nowrap center" width="2%"><?php echo JHtml::_('grid.checkall'); ?></th>
                                <th width="95%" class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_GROUP_NAME_LABEL')?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($this->groups as $index => $group){
                                $groupEditLink = JRoute::_('index.php?option=com_jeproshop&view=group&task=edit&group_id=' . (int)$group->group_id . '&'. JeproshopTools::getGroupToken() . '=1'); ?>
                                <tr>
                                    <td class="order nowrap center hidden-phone"><?php echo $index + 1; ?></td>
                                    <td class="order nowrap center hidden-phone"><?php echo JHtml::_('grid.id', $index, $group->group_id); ?></td>
                                    <td class="order nowrap "><a href="<?php echo $groupEditLink; ?>" ><?php echo ucfirst($group->name); ?></a></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                            <tfoot><?php if(isset($this->group_pagination)){ echo $this->group_pagination->getFootList(); } ?></tfoot>
                        </table>
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_default_customer_group" title="<?php echo JText::_('COM_JEPROSHOP_CUSTOMER_WILL_RECEIVE_YOUR_ADS_VIA_EMAIL_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_DEFAULT_GROUP_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_default_customer_group" name="jform[default_customer_group]" >
                            <?php foreach($this->groups as $group){ ?>
                                <option value="<?php echo $group->group_id; ?>" ><?php echo $group->name; ?></option>
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
</form>
