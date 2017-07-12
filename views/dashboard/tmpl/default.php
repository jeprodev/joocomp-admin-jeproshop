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

<div id="jeproshop-dashboard" >
    <div class="row" >
        <div class="col-lg-12">
            <div id="jeproshop-calendar" class="panel" >
                <form action="<?php echo JRoute::_('index.php?option=com_jeproshop') ?>" id="jeproshop-calendar-form" name="jeproshop-calendar-form" class="form-inline" >
                    <div class="btn-group" >
                        <button type="button" name="jeproshop_submit_date_day" class="btn btn-default" ><?php echo JText::_('COM_JEPROSHOP_DAY_LABEL'); ?></button>
                        <button type="button" name="jeproshop_submit_date_month" class="btn btn-default" ><?php echo JText::_('COM_JEPROSHOP_MONTH_LABEL'); ?></button>
                        <button type="button" name="jeproshop_submit_date_year" class="btn btn-default" ><?php echo JText::_('COM_JEPROSHOP_YEAR_LABEL'); ?></button>
                        <button type="button" name="jeproshop_submit_date_previous_day" class="btn btn-default" ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_DAY_LABEL'); ?></button>
                        <button type="button" name="jeproshop_submit_date_previous_month" class="btn btn-default" ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_MONTH_LABEL'); ?></button>
                        <button type="button" name="jeproshop_submit_date_previous_year" class="btn btn-default" ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_YEAR_LABEL'); ?></button>
                    </div>
                    <div class="pull-right" >
                        <button id="jeproshop-date-picker-expand" class="btn btn-default" type="button" >
                            <i class="icon-calendar-empty" ></i>
                            <span class="hidden-xs" >
                                <?php echo JText::_('COM_JEPROSHOP_FROM_LABEL'); ?>
                                <strong class="text-info" id="jeproshop-date-picker-from-info" ><?php echo JeproshopTools::escape($this->date_from); ?></strong>
                                <?php echo JText::_('COM_JEPROSHOP_TO_LABEL'); ?>
                                <strong class="text-info" id="jeproshop-date-picker-to-info" ><?php echo JeproshopTools::escape($this->date_to); ?></strong>
                                <strong class="text-info" id="jeproshop-date-picker-diff-info" ></strong>
                            </span>
                        </button>
                    </div>
                    <?php echo $this->calendar; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="row" >
        <div class="col-md-4 col-lg-3" id="jeproshop-dashboard-zone-one" ><?php echo $this->dashbord_zone_one; ?></div>
        <div class="col-md-8 col-lg-7" id="jeproshop-dashboard-zone-two" >
            <?php echo $this->dashbord_zone_two; ?>
            <div id="jeproshop-dashboard-addons"></div>
        </div>
        <div class="col-md-12 col-lg-2" >
            <section class="jeproshop-dash-news panel" >
                <h3><i class="icon-rss"></i> <?php echo JText::_('COM_JEPROSHOP_NEWS_LABEL'); ?></h3>
                <div class="jeproshop-dash-news-content" ></div>
                <div class="text-center"><h4><a href="http://www.jeprodev.net" ><?php echo JText::_('COM_JEPROSHOP_MORE_NEWS_LABEL'); ?></h4></div>
            </section>
            <section class="jeproshop-dash-links panel">
            <h3><i class="icon-link"></i> {l s="Useful links"}</h3>
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
            </dl>
            <dl>
                <dt><a href="http://www.jeprodev.net/en/contact-us?utm_source=back-office&amp;utm_medium=dashboard&amp;utm_campaign=back-office-{$lang_iso|upper}&amp;utm_content={if $host_mode}cloud{else}download{/if}" class="_blank"><?php echo JText::_('COM_JEPROSHOP_CONTACT_US_LABEL'); ?></a></dt>
                <dd></dd>
            </dl>
            </section>
        </div>
    </div>
</div>
