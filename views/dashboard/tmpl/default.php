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
<?php if(!empty($this->side_bar)){ ?>
    <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
<?php } ?>
<div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
    <div class="row" >
        <div id="jeproshop-calendar" class="panel-content "  >
            <form action="<?php echo JRoute::_('index.php?option=com_jeproshop') ?>" id="jeproshop-calendar-form" name="jeproshop-calendar-form" class="form-inline" >
                <div class="btn-group" >
                    <a id="jeproshop_submit_date_day" name="submit_date_day" class="btn btn-default <?php if(isset($this->preselect_date_range) && $this->preselect_date_range == 'day'){ ?> btn-success <?php } ?>" ><?php echo JText::_('COM_JEPROSHOP_DAY_LABEL'); ?></a>
                    <button type="button" id="jeproshop_submit_date_month" name="submit_date_month" class="btn btn-default <?php if(isset($this->preselect_date_range) && $this->preselect_date_range == 'month'){ ?> btn-success <?php } ?>" ><?php echo JText::_('COM_JEPROSHOP_MONTH_LABEL'); ?></button>
                    <button type="button" id="jeproshop_submit_date_year" name="submit_date_year" class="btn btn-default <?php if(isset($this->preselect_date_range) && $this->preselect_date_range == 'year'){ ?> btn-success <?php } ?>" ><?php echo JText::_('COM_JEPROSHOP_YEAR_LABEL'); ?></button>
                    <button type="button" id="jeproshop_submit_date_previous_day" name="submit_date_previous_day" class="btn btn-default <?php if(isset($this->preselect_date_range) && $this->preselect_date_range == 'previous-day'){ ?> btn-success <?php } ?>" ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_DAY_LABEL'); ?></button>
                    <button type="button" id="jeproshop_submit_date_previous_month" name="submit_date_previous_month" class="btn btn-default <?php if(isset($this->preselect_date_range) && $this->preselect_date_range == 'previous-month'){ ?> btn-success <?php } ?>" ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_MONTH_LABEL'); ?></button>
                    <button type="button" id="jeproshop_submit_date_previous_year" name="submit_date_previous_year" class="btn btn-default <?php if(isset($this->preselect_date_range) && $this->preselect_date_range == 'previous-year'){ ?> btn-success <?php } ?>" ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_YEAR_LABEL'); ?></button>
                </div>
                <input type="hidden" name="datepicker_from" id="jeproshop_datepicker_from" value="<?php echo $this->date_from; ?>" class="controls">
                <input type="hidden" name="datepicker_to" id="jeproshop_datepicker_to" value="<?php echo $this->date_to; ?>" class="controls">
                <input type="hidden" name="preselect_date_range" id="jeproshop_preselect_date_range" value="<?php if(isset($this->preselect_date_range)){ echo $this->preselect_date_range; } ?>" class="controls">
                <div class="pull-right" >
                    <button id="jeproshop_date_picker_expand" class="btn btn-default" type="button" >
                        <i class="icon-calendar-empty" ></i>
                        <span class="hidden-xs" >
                            <strong ><?php echo JText::_('COM_JEPROSHOP_FROM_LABEL'); ?>&nbsp;</strong>
                            <strong class="text-info" id="jeproshop_date_picker_from_info" ><?php echo JeproshopTools::escape($this->date_from); ?></strong>
                            <strong >&nbsp;<?php echo JText::_('COM_JEPROSHOP_TO_LABEL'); ?>&nbsp;</strong>
                            <strong class="text-info" id="jeproshop_date_picker_to_info" ><?php echo JeproshopTools::escape($this->date_to); ?></strong>
                            <strong class="text-info" id="jeproshop_date_picker_diff_info" ></strong>
                        </span>
                    </button>
                </div>
                <?php echo $this->calendar; ?>
            </form>
        </div>
    </div>
    <div class="row" >
        <div class="span9"  >
            <div class="col-md-4 col-lg-3" id="jeproshop_dashboard_zone_one" ><?php echo $this->dashboard_zone_one; ?></div>
            <div class="col-md-8 col-lg-7" id="jeproshop_dashboard_zone_two" >
                <?php echo $this->dashboard_zone_two; ?>
                <div id="jeproshop-dashboard-addons"></div>
            </div>
        </div>
        <div class="span3 pull-right" >
            <section class="jeproshop-dash-news panel" >
                <h3><i class="icon-rss"></i> <?php echo JText::_('COM_JEPROSHOP_NEWS_LABEL'); ?></h3>
                <div class="jeproshop-dash-news-content" ></div>
                <div class="text-center"><h4><a href="http://www.jeprodev.net" ><?php echo JText::_('COM_JEPROSHOP_MORE_NEWS_LABEL'); ?></h4></div>
            </section>
            <section class="jeproshop-dash-links panel">
            <!--h3><i class="icon-link"></i> {l s="Useful links"}</h3>
            <dl>
                <dt><a href="http://www.jeprodev.net/index.php?option=com_jeproshop&view=productdisplay/PS16?utm_source=back-office&amp;utm_medium=dashboard&amp;utm_campaign=back-office-{$lang_iso|upper}&amp;utm_content={if $host_mode}cloud{else}download{/if}" class="_blank">{l s="Official Documentation"}</a></dt>
                <dd>{l s="User, Developer and Designer Guides"}</dd>
            </dl>
            <dl>
                <dt><a href="http://www.prestashop.com/forums?utm_source=back-office&amp;utm_medium=dashboard&amp;utm_campaign=back-office-{$lang_iso|upper}&amp;utm_content={if $host_mode}cloud{else}download{/if}" class="_blank">{l s="PrestaShop Forum"}</a></dt>
                <dd>{l s="Connect with the PrestaShop community"}</dd>
            </dl>
            <dl>
                <dt><a href="http://addons.prestashop.com?utm_source=back-office&amp;utm_medium=dashboard&amp;utm_campaign=back-office-{$lang_iso|upper}&amp;utm_content={if $host_mode}cloud{else}download{/if}" class="_blank">{l s="PrestaShop Addons"}</a></dt>
                <dd>{l s="Enhance your store with templates & modules"}</dd>
            </dl>
            <dl>
                <dt><a href="http://forge.prestashop.com?utm_source=back-office&amp;utm_medium=dashboard&amp;utm_campaign=back-office-{$lang_iso|upper}&amp;utm_content={if $host_mode}cloud{else}download{/if}" class="_blank">{l s="The Forge"}</a></dt>
                <dd>{l s="Report issues in the Bug Tracker"}</dd>
            </dl -->
                <dl>
                    <dt><a href="http://www.jeprodev.net/en/contact-us?utm_source=back-office&amp;utm_medium=dashboard&amp;utm_campaign=back-office-{$lang_iso|upper}&amp;utm_content={if $host_mode}cloud{else}download{/if}" class="_blank"><?php echo JText::_('COM_JEPROSHOP_CONTACT_US_LABEL'); ?></a></dt>
                    <dd></dd>
                </dl>
            </section>
        </div>
    </div>
</div>
