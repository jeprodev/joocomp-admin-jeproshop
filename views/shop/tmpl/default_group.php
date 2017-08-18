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
$app = JFactory::getApplication();
?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=shop'); ?>" method="post" id="adminForm" name="adminForm"  class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container"  <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <div class="panel" >
            <?php echo $this->renderAdministrationSubMenu('shop_group'); ?>
            <div class="panel-content" >
                <table class="table table-striped" >
                    <thead>
                        <tr>
                            <th class="nowrap" >#</th>
                            <th class="nowrap" ><?php echo JHtml::_('grid.checkall'); ?></th>
                            <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_SHOP_GROUP_NAME_LABEL'); ?></th>
                            <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_SHARE_CUSTOMER_LABEL'); ?></th>
                            <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_SHARE_ORDER_LABEL'); ?></th>
                            <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_SHARE_STOCK_LABEL'); ?></th>
                            <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_STATUS_LABEL'); ?></th>
                            <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_DELETED_LABEL'); ?></th>
                            <th class="nowrap pull-right" ><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($this->shop_groups) && count($this->shop_groups)){
                            $pageCounter = (isset($this->pagination) ? ($this->pagination->limit_start * $this->pagination->limit) : 0);
                            foreach($this->shop_groups as $index => $shopGroup){
                                $shopGroupEditLink = JRoute::_('index.php?option=com_jeproshop&view=shop&task=edit&tab=group&shop_group_id=' . $shopGroup->shop_group_id . '&' . JeproshopTools::getShopToken() . '=1');
                                ?>
                        <tr class="row_<?php echo ($index % 2); ?>">
                            <td class="nowrap" ><?php echo $pageCounter + $index; ?></td>
                            <td class="nowrap" ><?php echo JHtml::_('grid.id', $index, $shopGroup->shop_group_id); ?></td>
                            <td class="nowrap" ><a href="<?php echo $shopGroupEditLink; ?>" ><?php echo $shopGroup->name; ?></td>
                            <td class="nowrap center" ><a href="#" class="btn btn-micro hasTooltip" ><i class="<?php echo 'icon-' . ($shopGroup->share_customer ? '' : 'un') .'publish'; ?>" ></i> </a></td>
                            <td class="nowrap center" ><a href="#" class="btn btn-micro hasTooltip" ><i class="<?php echo 'icon-' . ($shopGroup->share_order ? '' : 'un') .'publish'; ?>" ></i> </a></td>
                            <td class="nowrap center" ><a href="#" class="btn btn-micro hasTooltip" ><i class="<?php echo 'icon-' . ($shopGroup->share_stock ? '' : 'un') .'publish'; ?>" ></i> </a></td>
                            <td class="nowrap center" ><a href="#" class="btn btn-micro hasTooltip" ><i class="<?php echo 'icon-' . ($shopGroup->published ? '' : 'un') .'publish'; ?>" ></i> </a></td>
                            <td class="nowrap center" ><a href="#" class="btn btn-micro hasTooltip" ><i class="<?php echo 'icon-' . ($shopGroup->deleted ? '' : 'un') .'publish'; ?>" ></i> </a></td>
                            <td class="nowrap" >
                                <div class="btn-group-action" >
                                    <div class="btn-group pull-right" >
                                        <a href="<?php echo $shopGroupEditLink; ?>" class="btn btn-micro" ><i class="icon-edit" ></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_EDIT_LABEL'); ?></a>
                                        <button class="btn btn-micro dropdown_toggle" data-toggle="dropdown" ><i class="caret"></i> </button>
                                        <ul class="dropdown-menu">
                                            <li></li></ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php    }
                        } else{ ?>
                            <tr>
                                <td class="center alert alert-warning " colspan="9" ><?php echo JText::_('COM_JEPROSHOP_NOT_MATCHING_MESSAGE'); ?></td>
                            </tr>
                        <?php }?>
                    </tbody>
                    <tfoot></tfoot>
                </table>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="" />
</form>
