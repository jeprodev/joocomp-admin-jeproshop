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

class JeproshopFeatureModelFeature extends JeproshopModel{
    public $feature_id;

    public $shop_id;

    public $lang_id;

    /** @var string Name */
    public $name;
    public $position;

    public $multiLang = true;
    public $multiLangShop = false;

    public function getFeatureList($explicitSelect = TRUE){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        $context = JeproshopContext::getContext();

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');
        $lang_id = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $shop_id = $app->getUserStateFromRequest($option. $view. '.shop_id', 'shop_id', $context->shop->shop_id, 'int');
        $shop_group_id = $app->getUserStateFromRequest($option. $view. '.shop_group_id', 'shop_group_id', $context->shop->shop_group_id, 'int');
        $category_id = $app->getUserStateFromRequest($option. $view. '.category_id', 'category_id', 0, 'int');
        $order_by = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'position', 'string');
        $order_way = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC', 'string');
        $published = $app->getUserStateFromRequest($option. $view. '.published', 'published', 0, 'string');

        /* Manage default params values */
        $use_limit = true;
        if ($limit === false)
            $use_limit = false;

        /* Query in order to get results with all fields */
        $lang_join = '';
        if ($lang_id){
            $lang_join = " LEFT JOIN " . $db->quoteName('#__jeproshop_feature_lang') . " AS feature_lang ON (";
            $lang_join .= "feature_lang." . $db->quoteName('feature_id') . " = feature." . $db->quoteName('feature_id');
            $lang_join .= " AND feature_lang." . $db->quoteName('lang_id') . " = " .(int)$lang_id .") ";
        }

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS ";
            if($explicitSelect){
                $query .= "feature." . $db->quoteName('feature_id') . ", feature_lang.";
                $query .= $db->quoteName('name') . ", feature." . $db->quoteName('position');
            }

            $query .= " FROM " . $db->quoteName('#__jeproshop_feature') . " AS feature " . $lang_join;

            $query .= " ORDER BY " . ((str_replace('`', '', $order_by) == 'feature_id') ? "feature." : ""). " feature." ;
            $query .= $order_by ." " . $db->escape($order_way) . (($use_limit === true) ? " LIMIT " . (int)$limitstart.", ".(int)$limit : "" );


            $db->setQuery($query);
            $features = $db->loadObjectList();

            if ($use_limit === true){
                $limitstart = (int)$limitstart - (int)$limit;
                if ($limitstart < 0){ break; }
            }else{ break; }
        }while(empty($features));

        foreach($features as $feature){
            $query = "SELECT feature_value.feature_value_id AS count_values FROM " . $db->quoteName('#__jeproshop_feature_value');
            $query .= " AS feature_value WHERE feature_value.feature_id = " . $feature->feature_id . " AND (feature_value.custom";
            $query .= " = 0 OR feature_value.custom IS NULL)";

            $db->setQuery($query);
            $feature->count_values = count($db->loadObjectList());
        }

        $total = count($features);

        $this->pagination = new JPagination($total, $limitstart, $limit);
        return $features;
    }

    /**
     * This method is allow to know if a feature is used or active
     * @since 1.5.0.1
     * @return bool
     */
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('feature_feature_active');
    }
}