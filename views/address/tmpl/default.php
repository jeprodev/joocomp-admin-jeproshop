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
            <div class="panel-content well" >
                <table class="table table-striped" id="addressList">
                    <thead>
                    <tr>
                        <th class="nowrap center" width="1%">#</th>
                        <th class="nowrap center" width="1%"><?php echo JHtml::_('grid.checkall'); ?></th>
                        <th class="nowrap" width="10%"><?php echo JText::_('COM_JEPROSHOP_LAST_NAME_LABEL'); ?></th>
                        <th class="nowrap" width="10%"><?php echo JText::_('COM_JEPROSHOP_FIRST_NAME_LABEL'); ?></th>
                        <th class="nowrap hidden-phone" width="6%"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?></th>
                        <th class="nowrap" width="4%"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?></th>
                        <th class="nowrap hidden-phone" width="8%"><?php echo JText::_('COM_JEPROSHOP_CITY_LABEL'); ?></th>
                        <th class="nowrap hidden-phone" width="8%"><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?></th>
                        <th class="nowrap hidden-phone" width="5%"><span class="pull-right" ><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(empty($this->addresses)){ ?>
                        <tr>
                            <td colspan="9" ><div class="alert alert-no-items" ><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div></td>
                        </tr>
                    <?php } else {
                        foreach($this->addresses as $index => $address){
                            $address_view_link = JRoute::_('index.php?option=com_jeproshop&view=address&task=edit&address_id=' .(int)$address->address_id . '&' . JeproshopTools::getAddressToken() . '=1');
                            $delete_address_link = JRoute::_('index.php?option=com_jeproshop&view=address&task=delete&address_id=' . (int)$address->address_id . '&' . JeproshopTools::getAddressToken() . '=1');
                            ?>
                            <tr class="row_<?php echo ($index%2); ?>" >
                                <td class="nowrap center"><?php echo $index + 1; ?></td>
                                <td class="nowrap center hidden-phone"><?php echo JHtml::_('grid.id', $index, $address->address_id); ?></td>
                                <td class="nowrap" ><?php echo ucfirst($address->firstname); ?></td>
                                <td class="nowrap" ><?php echo ucfirst($address->lastname); ?></td>
                                <td class="nowrap" ><?php echo $address->address1; ?></td>
                                <td class="nowrap" ><?php echo $address->postcode; ?></td>
                                <td class="nowrap" ><?php echo $address->city; ?></td>
                                <td class="nowrap" ><?php echo $address->country; ?></td>
                                <td class="nowrap" >
                                    <div class="btn-group-action" >
                                        <div class="btn-group pull-right" >
                                            <a href="<?php echo $address_view_link; ?>" class="btn btn-micro" ><i class="icon-edit" ></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_EDIT_LABEL'); ?></a>
                                            <button class="btn btn-micro dropdown_toggle" data-toggle="dropdown" ><i class="caret"></i> </button>
                                            <ul class="dropdown-menu" >
                                                <li><a href="<?php echo $delete_address_link; ?>" onclick="if(confirm('<?php echo JText::_('COM_JEPROSHOP_DELETE_LABEL') . $address->firstname; ?>')){ return true; }else{ event.stopPropagation(); event.preventDefault(); }" title="<?php echo ucfirst(JText::_('COM_JEPROSHOP_DELETE_LABEL')); ?>" class="delete"><i class="icon-trash" ></i>&nbsp;<?php echo ucfirst(JText::_('COM_JEPROSHOP_DELETE_LABEL')); ?></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><?php if(isset($this->pagination)){ echo $this->pagination->getListFooter(); } ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
