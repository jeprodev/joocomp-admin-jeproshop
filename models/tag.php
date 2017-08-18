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

class JeproshopTagModelTag extends JeproshopModel {
    public $tag_id;
    /** @var integer Language id */
    public $lang_id;

    /** @var string Name */
    public $name;

    public static function getProductTags($product_id){
        $db = JFactory::getDBO();

        $query = "SELECT tag." . $db->quoteName('lang_id') . ", tag." . $db->quoteName('name') . " FROM ";
        $query .= $db->quoteName('#__jeproshop_tag') . " AS tag LEFT JOIN " . $db->quoteName('#__jeproshop_product_tag');
        $query .= " AS product_tag ON (product_tag.tag_id = tag.tag_id ) WHERE product_tag.";
        $query .= $db->quoteName('product_id') . " = " . (int)$product_id;

        $db->setQuery($query);
        $tags = $db->loadObjectList();
        if(!$tags)
            return false;

        return $tags;
    }

    public function getTagsList(JeproshopContext $context = null){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!isset($context) || $context == null){ $context = JeproshopContext::getContext(); }

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limit_start = $app->getUserStateFromRequest($option. $view. '.limitstart', 'limitstart', 0, 'int');
        // v$lang_id = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $order_by = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'tag_id', 'string');
        $order_way = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC', 'string');

        $select = ", lang." . $db->quoteName('title') . " AS lang_name, COUNT(product_tag." . $db->quoteName('product_id') . ") AS products";
        $join = " LEFT JOIN " . $db->quoteName('#__jeproshop_product_tag') . " AS product_tag ON(tag." . $db->quoteName('tag_id') . " = product_tag.";
        $join .= $db->quoteName('tag_id') . ") LEFT JOIN " . $db->quoteName('#__languages') . " AS lang ON(lang." . $db->quoteName('lang_id') . " = tag.";
        $join .=  $db->quoteName('lang_id') . ") ";
        $group = " GROUP BY tag." . $db->quoteName('name') . ", tag." . $db->quoteName('lang_id');

        $use_limit = true;
        if ($limit === false)
            $use_limit = false;

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS tag." .  $db->quoteName('tag_id') .", tag." . $db->quoteName('name') . $select ;
            $query .= " FROM " . $db->quoteName('#__jeproshop_tag') . " AS tag " . $join . " WHERE 1 " . $group . " ORDER BY ";
            $query .= ((str_replace('`', '', $order_by) == 'tag_id') ? " tag." : "") . $order_by . " " . $order_way;
            $query .= (($use_limit === true) ? " LIMIT " .(int)$limit_start . ", " .(int)$limit : "");
            $db->setQuery($query);
            $tags = $db->loadObjectList();

            if($use_limit == true){
                $limit_start = (int)$limit_start -(int)$limit;
                if($limit_start < 0){ break; }
            }else{ break; }
        }while(empty($tags));
        return $tags;
    }

    public static function deleteTagsForProduct($product_id){
        $db = JFactory::getDBO();

        $query = "DELETE FROM " . $db->quoteName('#__jeproshop_product_tag') . " WHERE " . $db->quoteName('product_id') . " = " .(int)$product_id;

        $db->setQuery($query);
        return $db->query();
    }

    /**
     * Add several tags in database and link it to a product
     *
     * @param integer $langId Language id
     * @param integer $productId Product id to link tags with
     * @param string|array $tagList List of tags, as array or as a string with comas
     * @param string $separator
     * @return bool Operation success
     */
    public static function addTags($langId, $productId, $tagList, $separator = ','){
        $db = JFactory::getDBO();

        if (!JeproshopTools::isUnsignedInt($langId)){ return false; }

        if (!is_array($tagList)){
            $tagList = array_filter(array_unique(array_map('trim', preg_split('#\\'.$separator.'#', $tagList, null, PREG_SPLIT_NO_EMPTY))));
        }
        $list = array();
        if (is_array($tagList)){
            foreach ($tagList as $tag){
                if (!JeproshopTools::isGenericName($tag)){ return false; }
                $tag = trim(substr($tag, 0, 32));
                $tag_obj = new JeproshopTagModelTag(null, $tag, (int)$langId);

                /* Tag does not exist in database */
                if (!JeproshopTools::isLoadedObject($tag_obj, 'tag_id')){
                    $tag_obj->name = $tag;
                    $tag_obj->lang_id = (int)$langId;
                    $tag_obj->add();
                }
                if (!in_array($tag_obj->tag_id, $list))
                    $list[] = $tag_obj->tag_id;
            }
        }
        $data = '';
        $result = true;
        foreach ($list as $tag_id){
            $query = "INSERT INTO " . $db->quoteName('#__jeproshop_product_tag') . " ( " . $db->quoteName('tag_id') . ", ";
            $query .= $db->quoteName('product_id') . ") VALUES (" . (int)$tag_id . ", " . (int)$productId . ")";

            $db->setQuery($query);
            $result &= $db->query();
        }

        return $result;
    }
}