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
            <?php echo $this->renderAdministrationSubMenu('shop'); ?>
            <div class="panel-content" >
                <table class="table table-striped" >
                    <thead>
                        <tr>
                            <th class="nowrap" >#</th>
                            <th class="nowrap" ><?php echo JHtml::_('grid.checkall'); ?></th>
                            <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_SHOP_NAME_LABEL'); ?></th>
                            <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_SHOP_GROUP_NAME_LABEL'); ?></th>
                            <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_CATEGORY_NAME_LABEL'); ?></th>
                            <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_MAIN_URL_LABEL'); ?></th>
                            <th class="nowrap pull-right" ><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($this->shops) && count($this->shops)){
                            foreach($this->shops as $index => $shop){
                                $shopEditLink = JRoute::_('index.php?option=com_jeproshop&view=shop&task=edit&shop_id=' . $shop->shop_id . '&' . JeproshopTools::getShopToken() . '=1');
                                $shopGroupEditLink = JRoute::_('index.php?option=com_jeproshop&view=shop&task=edit&tab=group&shop_group_id=' . $shop->shop_group_id . '&' . JeproshopTools::getShopToken() . '=1');
                                $shopCategoryLink = JRoute::_('index.php?option=com_jeproshop&view=category&task=edit&category_id=' . $shop->category_id . '&' . JeproshopTools::getCategoryToken() . '=1');
                                ?>
                                <tr class="row_<?php echo ($index % 2); ?>">
                                    <td></td>
                                    <td><?php echo JHtml::_('grid.id', $index, $shop->shop_id); ?></td>
                                    <td><a href="<?php echo $shopEditLink; ?>" ><?php echo $shop->shop_name; ?></a></td>
                                    <td><a href="<?php echo $shopGroupEditLink; ?>" ><?php echo $shop->shop_group_name; ?></td>
                                    <td><a href="<?php echo $shopCategoryLink; ?>" ><?php echo $shop->category_name; ?></td>
                                    <td><a href="" ><?php echo $shop->url; ?></td>
                                    <td class="nowrap pull-right">
                                        <div class="btn-group-action" >
                                            <div class="btn-group pull-right" >
                                                <a href="<?php echo $shopEditLink; ?>" class="btn btn-micro" ><i class="icon-edit" ></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_EDIT_LABEL'); ?></a>
                                                <button class="btn btn-micro dropdown_toggle" data-toggle="dropdown" ><i class="caret"></i> </button>
                                                <ul class="dropdown-menu">
                                                    <li></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                        <?php    }
                        }else { ?>
                         <tr>
                             <td colspan="7" class="alert alert-warning center" >
                                 <?php echo JText::_('COM_JEPROSHOP_NOT_MATCHING_MESSAGE'); ?>
                             </td>
                         </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="" />
</form>
