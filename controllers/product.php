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

class JeproshopProductController extends JeproshopController{
    public $current_category_id;
    
    public function initContent($token = null){
        $app = JFactory::getApplication();
        $context = JeproshopContext::getContext();
        $task = $app->input->get('task');
        $view = $app->input->get('view');

        if($task == 'add' || $task == 'edit'){
            if($task == 'add'){

            }elseif($task == 'edit'){

            }
        }else{
            if($categoryId = (int)$this->current_category_id){
                self::$_current_index .= '&category_id=' . (int)$this->current_category_id;
            }

            if(!$categoryId){
                $this->_defaultOrderBy = 'product';
                if(isset($context->cookie->product_order_by) && $context->cookie->product_order_by == 'position'){
                    unset($context->cookie->product_order_by);
                    unset($context->cookie->product_order_way);
                }
                //$category_id = 1;
            }
        }
        parent::initContent();
    }
    

}