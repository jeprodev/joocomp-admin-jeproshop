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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=attachment'); ?>" method="post" name="adminForm" id="adminForm" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setCatalogSubMenu('attachment'); ?>
        <div class="separation" ></div>
        <div class="panel" >
            <div class="panel-content well" >
                <table class="table table-striped" id="attachment-list">
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center" >#</th>
                        <th width="1%" class="nowrap center" ><?php echo JHtml::_('grid.checkall'); ?></th>
                        <th class="nowrap" width="60%" ><?php echo JText::_('COM_JEPROSHOP_ATTACHMENT_NAME_LABEL'); ?></th>
                        <th class="nowrap" width="8%" ><?php echo JText::_('COM_JEPROSHOP_MANUFACTURER_VALUES_COUNT_LABEL'); ?></th>
                        <th class="nowrap" width="8%" ><?php echo JText::_('COM_JEPROSHOP_POSITION_LABEL'); ?></th>
                        <th class="nowrap" width="8%" ><span class="pull-right" ><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(isset($this->attachments) && count($this->attachments)){ ?>
                        <tr></tr>
                    <?php }else{ ?>
                        <tr>
                            <td colspan="6" ><div class="alert warning" ><?php echo JText::_('COM_JEPROSHOP_NOT_MATCHING_MESSAGE'); ?></div></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                    <tfoot><tr><td colspan="7" ><?php if(isset($this->pagination)){ echo $this->pagination->getListFooter(); } ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</form>
