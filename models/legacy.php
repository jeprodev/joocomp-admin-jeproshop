<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net
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

class JeproshopModel extends  JModelLegacy{

    protected $pagination;
    
    public function isMultiShop($table, $multiLangShop){
        return JeproshopShopModelShop::isTableAssociated($table) || !empty($multiLangShop);
    }

    public function getPagination(){ return $this->pagination; }

    /**
     * Clears cache entries that have this object's ID.
     *
     * @param $table
     * @param $id
     * @param bool $all If true, clears cache for all objects
     */
    public function clearCache($table, $id, $all = false){
        if ($all) {
            JeproshopCache::clean('jeproshop_' . $table . '_model_*');
        } elseif ($id) {
            JeproshopCache::clean('jeproshop_' . $table . '_model_' . (int)$id .'_*');
        }
    }

}