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

$compareDateFrom =  $this->getCompareDateFrom();
$compareDateTo =  $this->getCompareDateTo();
$actions = $this->getActions();

$script = ' var translatedDates = {
    days : ["' . JText::_('COM_JEPROSHOP_SUNDAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MONDAY_LABEL'). '", "' . JText::_('COM_JEPROSHOP_TUESDAY_LABEL') .'", "' . JText::_('COM_JEPROSHOP_WEDNESDAY_LABEL') . '", "'. JText::_('COM_JEPROSHOP_THURSDAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FRIDAY_LABEL') . '", "'. JText::_('COM_JEPROSHOP_SATURDAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SUNDAY_LABEL') . '"],
    daysShort : ["' . JText::_('COM_JEPROSHOP_SUN_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MON_LABEL') . '", "' . JText::_('COM_JEPROSHOP_TUE_LABEL') .'", "' . JText::_('COM_JEPROSHOP_WED_LABEL') . '", "' . JText::_('COM_JEPROSHOP_THU_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FRI_LABEL') . '", "' .  JText::_('COM_JEPROSHOP_SAT_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SUN_LABEL') . '"],
    daysMin : ["' . JText::_('COM_JEPROSHOP_SU_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MO_LABEL') . '", "' . JText::_('COM_JEPROSHOP_TU_LABEL') . '", "' . JText::_('COM_JEPROSHOP_WE_LABEL') . '", "' . JText::_('COM_JEPROSHOP_TH_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FR_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SA_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SU_LABEL') . '"], 
    months: ["' . JText::_('COM_JEPROSHOP_JANUARY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FEBRUARY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MARCH_LABEL') . '", "' . JText::_('COM_JEPROSHOP_APRIL_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_JUNE_LABEL') . '", "' . JText::_('COM_JEPROSHOP_JULY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_AUGUST_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SEPTEMBER_LABEL') . '", "' . JText::_('COM_JEPROSHOP_OCTOBER_LABEL') . '", "' . JText::_('COM_JEPROSHOP_NOVEMBER_LABEL') . '", "' . JText::_('COM_JEPROSHOP_DECEMBER_LABEL') . '"], 
    monthsShort: ["' . JText::_('COM_JEPROSHOP_JAN_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FEB_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MAR_LABEL') . '", "' . JText::_('COM_JEPROSHOP_APR_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_JUN_LABEL') . '", "' . JText::_('COM_JEPROSHOP_JUL_LABEL') . '", "' . JText::_('COM_JEPROSHOP_AUG_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SEP_LABEL') . '", "' . JText::_('COM_JEPROSHOP_OCT_LABEL') . '", "' . JText::_('COM_JEPROSHOP_NOV_LABEL') . '", "' . JText::_('COM_JEPROSHOP_DEC_LABEL') . '"]
};';

$script .= 'jQuery(document).ready(function(){
    jQuery("#jeproshop_date_picker").JeproCalendar({
        wrapper : "jeproshop_date_picker",
        start_date_class : "start-date-picker",
        end_date_class : "end-date-picker",
        start_date_id : "jeproshop_date_start",
        end_date_id : "jeproshop_date_end",
        date_input : "input-date",
        translated_dates: translatedDates
    });
})';

JFactory::getDocument()->addScriptDeclaration($script);

?>
<div id="jeproshop_date_picker" class="row row-padding-top hiddn" >
    <div id="calendar_date_range_picker_form" >
        <div class="date-range-picker-row" >
            <?php if(!$this->isRTL()) { ?>
                <div class="end-date-picker pull-right" data-date="<?php echo $this->getDateTo(); ?>" data-date-format="<?php echo $this->getDateFormat(); ?>" ></div>
                <div class="start-date-picker pull-right" data-date="<?php echo $this->getDateFrom(); ?>" data-date-format="<?php echo $this->getDateFormat(); ?>" ></div>
            <?php } else { ?>
                <div class="start-date-picker pull-right" data-date="<?php echo $this->getDateFrom(); ?>" data-date-format="<?php echo $this->getDateFormat(); ?>"></div>
                <div class="end-date-picker pull-right" data-date="<?php echo $this->getDateTo(); ?>" data-date-format="<?php echo $this->getDateFormat(); ?>"></div>
            <?php } ?>
            <div id="date-picker-form" class="horizontal-form pull-right" >
                <div id="date-range" >
                    <div  class="form-date-heading form-date-group" >
                                <span class="title"><?php echo JText::_('COM_JEPROSHOP_DATE_RANGE_LABEL'); ?></span>
                                <?php if(isset($actions) && count($actions) > 0){
                                    if(count($actions) > 1){ ?>
                                <button class="btn btn-default btn-xs pull-right dropdown-toggle" data-toggle="dropdown" type="button" >
                                    <?php echo JText::_('COM_JEPROSHOP_CUSTOM_LABEL'); ?> '<i class="icon-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php foreach ($actions as $action) { ?>
                                        <li>
                                            <a href="<?php echo (isset($action->href) ? $action->href : '#'); ?>" <?php if(isset($action->class)){ ?> class="<?php echo $action->class; ?>" <?php } ?> >
                                              <?php if(isset($action->icon)){ ?><i class="<?php echo $action->icon; ?>" ></i> <?php } ?>
                                            </a></li>
                                    <?php } ?>
                                </ul>
                                <?php  } else { ?>
                                 <a href="<?php echo (isset($actions[0]->href) ? $actions[0]->href : '#'); ?>"
                                        class="btn btn-default btn-xs pull-right <?php echo (isset($actions[0]->class) ? $actions[0]->class : ''); ?>" >
                                     <?php echo (isset($actions[0]->icon) ? '<i class="' . $actions[0]->icon . '"></i> ' : '') . $actions[0]->label ?></a>
                                    <?php }
                                } ?>
                    </div>
                    <div class="form-date-body form-date-group" >
                        <label class="form-label" ><?php echo JText::_('COM_JEPROSHOP_FROM_LABEL'); ?></label>
                        <input class="input-date" id="jeproshop_date_start" placeholder="Start" type="text" name="date_from" value="<?php echo $this->getDateFrom(); ?>" data-date-format="<?php echo $this->getDateFormat(); ?>" tabindex="1" />
                        <label class="form-label" ><?php echo JText::_('COM_JEPROSHOP_TO_LABEL'); ?></label>
                        <input class="input-date group-control" id="jeproshop_date_end" placeholder="End" type="text" name="date_to" value="<?php echo $this->getDateTo(); ?>" data-date-format="<?php echo $this->getDateFormat(); ?>" tabindex="2" />
                    </div>
                    <div id="date_compare_wrapper" class="form-date-group" >
                        <div class="form-date-heading" >
                            <span class="checkbox-title" >
                                <label class="checkbox" >
                                    <input type="checkbox" id="jeproshop_date_picker_compare" name="datepicker_compare" <?php if(isset($compareDateFrom) && isset($compareDateTo)){ ?>  checked="checked" <?php } ?>  tabindex="3" />
                                        <?php echo  JText::_('COM_JEPROSHOP_COMPARE_TO_LABEL'); ?>
                                </label>
                            </span>
                            <select id="jeproshop_compare_options" class="fixed-width-lg pull-right" name="compare_date_option"
                                        <?php echo ((is_null($compareDateFrom) || is_null($compareDateTo)) ? ' disabled="disabled" ' : ''); ?> >
                                <option value="1" <?php if($this->getCompareOption() == 1){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_PERIOD_LABEL'); ?></option>
                                <option value="2" <?php if($this->getCompareOption() == 2){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_YEAR_LABEL'); ?></option>
                                <option value="3" <?php if($this->getCompareOption() == 3){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_CUSTOM_LABEL'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-date-body form-date-group" id="form_date_body_compare" <?php if(is_null($compareDateFrom) || is_null($compareDateTo)){ ?>  style="display: none;" <?php } ?> >
                        <label class="form-label" ><?php echo JText::_('COM_JEPROSHOP_FROM_LABEL'); ?></label>
                        <input id="jeproshop_date_start_compare" class="input-date group-control" type="text" placeholder="Start" name="compare_date_from" value="<?php echo $compareDateFrom; ?>" data-date-format="<?php echo $this->getDateFormat(); ?>" tabindex="4" />
                        <label class="form-label" ><?php echo JText::_('COM_JEPROSHOP_TO_LABEL'); ?></label>
                        <input id="jeproshop_date_end_compare" class="input-date form-control" type="text" placeholder="End" name="compare_date_to" value="<?php echo $compareDateTo; ?>" data-date-format="<?php echo $this->getDateFormat(); ?>" tabindex="5" />
                    </div>
                    <div class="form-date-actions form-date-group" >
                        <button class="btn btn-link" type="button" id="date-picker-cancel" tabindex="7" >
                            <i class="icon-remove"></i> <?php echo JText::_('COM_JEPROSHOP_CANCEL_LABEL') ; // d='Admin.Actions'} ?>
                        </button>
                        <button class="btn btn-default pull-right" type="submit" name="submit_date_range" tabindex="6" >
                            <i class="icon-ok text-success" ></i> <?php echo JText::_('COM_JEPROSHOP_APPLY_LABEL') ; // d='Admin.Actions'} ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
