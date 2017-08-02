<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net

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
<div class="panel" >
    <div class="panel-title" > <i class="icon-eye-close"></i> <?php echo strtoupper(JText::_('COM_JEPROSHOP_ADD_A_PRIVATE_NOTE_LABEL')); ?></div>
    <div class="panel-content " >
        <div class="alert alert-info"><?php echo JText::_('COM_JEPROSHOP_THIS_NOTE_WILL_BE_DISPLAYED_TO_ALL_EMPLOYEES_BUT_NOT_TO_CUSTOMER_MESSAGE'); ?></div>
        <form id="customer_note" class="form-horizontal" action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=customer'); ?>jax.php" method="post" onsubmit="saveCustomerNote({$customer->id|intval});return false;" >
            <div class="form-group">
                <div class="col-lg-12">
                    <textarea name="jform[note]" id="jform_note_content"  rows="5" ><?php echo $this->customer_note; ?></textarea>
                </div>
            </div>
            <div class="panel_row" >
                <button type="submit" id="jform_submit_customer_note" class="btn btn-default pull-right" disabled="disabled" >
                    <i class="icon-save"></i> <?php echo JText::_('COM_JEPROSHOP_SAVE_LABEL'); ?>
                </button>
            </div>
            <span id="jform_note_feedback"></span>
        </form>
        <div style="clear: both; " ></div>
    </div>
</div>
<div class="panel" >
    <div class="panel-title" ><i class="icon-envelope"></i> <?php echo strtoupper(JText::_('COM_JEPROSHOP_MESSAGES_LABEL')); ?> <span class="badge badge-success" ><?php echo count($this->messages); ?></span></div>
    <div class="panel-content " >
        <?php if(count($this->messages)){ ?>
            <table class="table">
                <thead>
                <tr>
                    <th><span class="nowrap" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_STATUS_LABEL')); ?></span></th>
                    <th><span class="nowrap" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_MESSAGE_LABEL')); ?></span></th>
                    <th><span class="nowrap center" ><?php echo ucfirst(JText::_('COM_JEPROSHOP_SENT_ON_LABEL')); ?></span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($this->messages AS $message){ ?>
                    <tr>
                        <td class="nowrap" ><?php echo $message->status; ?></td>
                        <td class="nowrap" >
                            <a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=customer&task=view_threads&customer_thread_id=' . $message->customer_thread_id . '&' . JeproshopTools::getCustomerThreadToken()); ?>" >
                                <?php echo $message->message . '...'; ?>
                            </a>
                        </td>
                        <td class="nowrap center" ><?php echo $message->date_add; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php }else{ ?>
            <p class="text-muted text-center">
                <?php echo ucfirst($this->customer->firstname) . ' ' . strtoupper($this->customer->lastname) . ' ' . JText::_('COM_JEPROSHOP_HAS_NEVER_CONTACTED_YOU_MESSAGE'); ?>
            </p>
        <?php } ?>
    </div>
</div>
<div class="panel" >
    <div class="panel-title" ><i class="icon-ticket" ></i> <?php echo strtoupper(JText::_('COM_JEPROSHOP_VOUCHERS_LABEL')); ?> <span class="badge badge-success" ><?php echo count($this->discounts) ?></span></div>
    <div class="panel-content " >
        <?php if(count($this->discounts)){ ?>
            <table class="table">
                <thead>
                <tr>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_ID_LABEL')); ?></span></th>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_CODE_LABEL')); ?></span></th>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_NAME_LABEL')); ?></span></th>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_STATUS_LABEL')); ?></span></th>
                <tr/>
                </thead>
                <tbody>
                <?php foreach($this->discounts AS $key => $discount){ ?>
                    <tr>
                        <td class="nowrap" ><?php echo $discount->cart_rule_id; ?></td>
                        <td class="nowrap" ><?php echo $discount->code; ?></td>
                        <td class="nowrap" ><?php echo $discount->name; ?></td>
                        <td class="nowrap" >
                            <?php if($discount->published){ ?>
                                <i class="icon-ok"></i>
                            <?php }else{?>
                                <i class="icon-remove"></i>
                            <?php } ?>
                        </td>
                        <td class="nowrap" >
                            <a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=cart&task=edit_rule&cart_rule_id=' . $discount->cart_rule_id . '&' . JeproshopTools::getCartRuleToken()) ; ?>">
                                <i class="icon-pencil"></i>
                            </a>
                            <a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=cart&task=delete_cart_rule&cart_rule_id=' . $discount->cart_rule_id . '&' . JeproshopTools::getCartRuleToken()); ?>">
                                <i class="icon-remove"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php }else{ ?>
            <p class="text-muted text-center">
                <?php echo ucfirst($this->customer->firstname) . ' ' . strtoupper($this->customer->lastname) . ' ' . JText::_('COM_JEPROSHOP_HAS_NO_DISCOUNT_VOUCHERS_MESSAGE');  ?>
            </p>
        <?php } ?>
    </div>
</div>
<?php if(count($this->connections)){ ?>
    <div class="panel" >
        <div class="panel-title" ><i class="icon-time"></i> <?php echo ucfirst(JText::_('COM_JEPROSHOP_CONNECTIONS_LABEL')); ?></div>
        <div class="panel-content " >
            <table class="table">
                <thead>
                <tr>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_DATE_LABEL')); ?></span></th>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_PAGES_VIEWED_LABEL')); ?></span></th>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_TOTAL_TIME_LABEL')); ?></span></th>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_ORIGIN_LABEL')); ?></span></th>
                    <th class="nowrap" ><span><?php echo ucfirst(JText::_('COM_JEPROSHOP_ADDRESS_LABEL')); ?></span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($this->connections as $connection){ ?>
                    <tr>
                        <td class="nowrap" ><?php echo JeproshopTools::dateFormat($connection->date_add, false); ?></td>
                        <td class="nowrap" ><?php echo $connection->pages; ?></td>
                        <td class="nowrap" ><?php echo $connection->time; ?></td>
                        <td class="nowrap" ><?php echo $connection->http_referer; ?></td>
                        <td class="nowrap" ><?php echo $connection->ipaddress; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
<div class="panel" >
    <div class="panel-title" >
        <i class="icon-group"></i><?php echo strtoupper(JText::_('COM_JEPROSHOP_GROUPS_LABEL'));?>
        <span class="badge badge-success"> <?php echo count($this->customer_groups); ?></span>
        <a class="btn btn-default pull-right btn-micro" href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=customer&task=edit&customer_id=' . $this->customer->customer_id . '&' . JeproshopTools::getCustomerToken() . '=1'); ?>" >
            <i class="icon-edit"></i> <?php echo ucfirst(JText::_('COM_JEPROSHOP_EDIT_LABEL')); ?>
        </a>
    </div>
    <div class="panel-content " >
        <?php if($this->customer_groups AND count($this->customer_groups)){ ?>
            <table class="table">
                <thead>
                <tr>
                    <th><span class="title_box "> <?php echo ucfirst(JText::_('COM_JEPROSHOP_ID_LABEL'));?></span></th>
                    <th><span class="title_box "><?php echo ucfirst(JText::_('COM_JEPROSHOP_NAME_LABEL'));?></span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($this->customer_groups AS $group){
                    $groupLink = JRoute::_('index.php?option=com_jeproshop&view=group&task=view&group_id=' . $group->group_id . '&' . JeproshopTools::getGroupToken() . '=1'); ?>
                    <tr onclick="document.location = '<?php echo $groupLink; ?>'">
                        <td><?php echo $group->group_id; ?> </td>
                        <td><a href="<?php echo $groupLink; ?>" ><?php echo (is_array($group->name) ? $group->name[$this->context->language->lang_id] : $group->name); ?> </a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>
<?php if(count($this->referrers)){ ?>
    <div class="panel" >
        <div class="panel-title" ><i class="icon-share-alt"></i> <?php echo strtoupper(JText::_('COM_JEPROSHOP_REFERRERS_LABEL'));?></div>
        <div class="panel-content " >
            <table class="table">
                <thead>
                <tr>
                    <th><span class="title_box "><?php echo ucfirst(JText::_('COM_JEPROSHOP_DATE_LABEL'));?></span></th>
                    <th><span class="title_box "><?php echo ucfirst(JText::_('COM_JEPROSHOP_NAME_LABEL'));?></span></th>
                    <?php if($this->shop_is_feature_active){ ?><th><?php echo ucfirst(JText::_('COM_JEPROSHOP_SHOP_LABEL'));?></th> <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($this->referrers as $referrer){ ?>
                    <tr>
                        <td><?php echo JeproshopTools::dateFormat($this->order->date_add, false); ?></td>
                        <td><?php echo $referrer->name; ?></td>
                        <?php if($this->shop_is_feature_active){ ?><td><?php echo $referrer->shop_name; ?></td><?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
<!--  --div class="panel" >
	<div class="panel-title" ></div>
	<div class="panel-content" ></div>
</div -->
