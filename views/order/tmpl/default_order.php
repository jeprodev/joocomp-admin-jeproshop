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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=order'); ?>" method="post" name="adminForm" id="adminForm" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setOrdersSubMenu('order'); ?>
        <div class="separation" ></div>
        <div class="panel" >
            <div class="panel-content well" >
                <table class="table table-striped" id="orders-list" >
                    <thead>
                    <tr>
                        <th class="nowrap center" width="1%">#</th>
                        <th class="nowrap center" width="1%"><?php echo JHtml::_('grid.checkall'); ?></th>
                        <th width="6%" class="nowrap"><?php echo JHtml::_('searchtools.sort', JText::_('COM_JEPROSHOP_REFERENCE_LABEL'), 'o.reference', 'ASC'); ?></th>
                        <th width="2%" class="nowrap center hidden-phone"><?php echo JText::_('COM_JEPROSHOP_NEW_CLIENT_LABEL'); ?></th>
                        <th width="1%" class="nowrap hidden-phone"><?php echo JHtml::_('searchtools.sort', JText::_('COM_JEPROSHOP_DELIVERY_LABEL'), 'o.delivery', 'ASC'); ?></th>
                        <th width="5%" class="nowrap hidden-phone"><?php echo JHtml::_('searchtools.sort', JText::_('COM_JEPROSHOP_CUSTOMER_LABEL'), 'o.customer', 'ASC'); ?></th>
                        <th width="3%" class="nowrap center hidden-phone"><?php echo JHtml::_('searchtools.sort', JText::_('COM_JEPROSHOP_TOTAL_LABEL'), 'o.total', 'ASC'); ?></th>
                        <th width="4%" class="nowrap center hidden-phone"><?php echo JHtml::_('searchtools.sort', JText::_('COM_JEPROSHOP_PAYMENT_LABEL'), 'o.payment', 'ASC'); ?></th>
                        <th width="8%" class="nowrap hidden-phone"><?php echo JHtml::_('searchtools.sort', JText::_('COM_JEPROSHOP_STATUS_LABEL'), 'o.status', 'ASC'); ?></th>
                        <th width="3%" class="nowrap center hidden-phone"><?php echo JHtml::_('searchtools.sort', JText::_('COM_JEPROSHOP_CREATION_DATE_LABEL'), 'o.date', 'ASC'); ?></th>
                        <th width="3%" class="nowrap center hidden-phone"><?php echo JHtml::_('searchtools.sort', JText::_('COM_JEPROSHOP_ACTIONS_LABEL'), 'o.action', 'ASC'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(empty($this->orders)){ ?>
                        <tr><td colspan="11" ><div class="alert alert-no-items" ><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div></td></tr>
                    <?php } else {
                        foreach($this->orders as $index => $order){
                            $order_link = JRoute::_('index.php?option=com_jeproshop&view=order&task=view&order_id=' . (int)$order->order_id . '&' . JeproshopTools::getOrderFormToken() . '=1');
                            $customer_link = JRoute::_('index.php?option=com_jeproshop&view=customer&task=view&customer_id=' . (int)$order->customer_id . '&' . JeproshopTools::getCustomerToken() . '=1');
                            ?>
                            <tr class="row_<?php echo $index % 2; ?>" >
                                <td width="1%" class="nowrap center hidden-phone"><?php echo $index + 1; ?></td>
                                <td width="1%" class="nowrap center hidden-phone"><?php echo JHtml::_('grid.id', $index, $order->order_id); ?></td>
                                <td width="6%" class="nowrap "><a href="<?php echo $order_link ; ?>" ><?php echo $order->reference; ?></a></td>
                                <td width="2%" class="nowrap center hidden-phone"><?php echo ($order->new ? '<i class="icon-publish" ></i>' : '<i class="icon-unpublish" ></i>'); ?></td>
                                <td width="4%" class="nowrap hidden-phone"><?php echo $order->country_name; ?></td>
                                <td width="5%" class="nowrap hidden-phone"><a href="<?php echo $customer_link; ?>" ><?php echo $order->customer_name; ?></a></td>
                                <td width="2%" class="nowrap center hidden-phone"><?php echo JeproshopTools::displayPrice($order->total_paid_tax_incl); ?></td>
                                <td width="5%" class="nowrap center hidden-phone"><?php echo $order->payment; ?></td>
                                <td width="1%" class="nowrap hidden-phone"><?php echo $order->order_status_name; ?></td>
                                <td width="1%" class="nowrap center hidden-phone"><?php echo JeproshopTools::displayDate($order->date_add); ?></td>
                                <td width="1%" class="nowrap center hidden-phone" >
                                    <div class="btn-group-action" >
                                        <div class="btn-group" >
                                            <a href="<?php echo $order_link; ?>" class="btn btn-micro" ><i class="icon-edit" ></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_EDIT_LABEL'); ?></a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                    </tbody>
                    <tfoot><tr><td colspan="11"><?php if(isset($this->pagination)){ echo $this->pagination->getListFooter(); } ?></td></tr></tfoot>
                </table>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="render" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
