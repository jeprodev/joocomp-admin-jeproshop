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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=country'); ?>" method="post" name="adminForm" id="adminForm" class="jform-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo $this->renderLocalisationSubMenu('states'); ?>
        <div class="separation"></div>
        <div class="panel" >
            <div class="panel-content" >
                <table class="table table-striped" id="state-list" >
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone" >#</th>
                        <th width="1%" class="nowrap center hidden-phone" ><?php echo JHtml::_('grid.checkall'); ?></th>
                        <th width="60%" class="nowrap left " ><?php echo JText::_('COM_JEPROSHOP_STATE_NAME_LABEL'); ?></th>
                        <th width="3%" class="nowrap center hidden-phone" ><?php echo JText::_('COM_JEPROSHOP_ISO_CODE_LABEL'); ?></th>
                        <th width="3%" class="nowrap center hidden-phone" ><?php echo JText::_('COM_JEPROSHOP_ZONE_NAME_LABEL'); ?></th>
                        <th width="3%" class="nowrap center hidden-phone" ><?php echo JText::_('COM_JEPROSHOP_COUNTRY_NAME_LABEL'); ?></th>
                        <th width="3%" class="nowrap center hidden-phone" ><?php echo JText::_('COM_JEPROSHOP_PUBLISHED_LABEL'); ?></th>
                        <th width="1%" class="nowrap" ><span class="pull-right"><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(empty($this->states)){ ?>
                        <tr>
                            <td colspan="8" ><div class="alert alert-no-items" ><?php echo JText::_('COM_JEPROSHOP_NO_MACTHING_RESULTS'); ?></div></td>
                        </tr>
                    <?php } else {
                        foreach($this->states as $index => $state){ ?>
                            <tr>
                                <td class="order nowrap center hidden-phone"><?php echo $index + 1; ?></td>
                                <td class="order nowrap center hidden-phone"><?php echo JHtml::_('grid.id', $index, $state->state_id); ?></td>
                            </tr>
                        <?php }
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
