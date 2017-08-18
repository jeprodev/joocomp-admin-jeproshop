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

class JeproshopSupplierModelSupplier extends JeproshopModel {
    /** @var integer supplier ID */
    public $supplier_id;

    /** @var string Name */
    public $name;

    /** @var string A short description for the discount */
    public $description;

    public $short_description;

    public $lang_id;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var string Friendly URL */
    public $link_rewrite;

    /** @var string Meta title */
    public $meta_title;

    /** @var string Meta keywords */
    public $meta_keywords;

    /** @var string Meta description */
    public $meta_description;

    /** @var boolean active */
    public $published;


    public function __construct($supplierId = null, $langId = null) {
        if ($langId !== null) {
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($langId) !== false) ? $langId : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if ($supplierId) {
            // Load object from database if object id is present
            $cacheKey = 'jeproshop_supplier_model_' . (int)$supplierId . '_' . (int)$langId;
            $db = JFactory::getDBO();
            if (!JeproshopCache::isStored($cacheKey)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_supplier') . " AS supplier ";

                // Get lang information
                if ($langId){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_supplier_lang') . " AS supplier_lang ON (supplier." . $db->quoteName('supplier_id');
                    $query .= " = supplier_lang." . $db->quoteName('supplier_id') . " AND supplier_lang." . $db->quoteName('lang_id') . " = " . (int)$langId . ")";
                }
                $query .= " WHERE supplier." . $db->quoteName('supplier_id') . " = " . (int)$supplierId;

                $db->setQuery($query);
                $supplierData = $db->loadObject();
                if ($supplierData){
                    if (!$langId ){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_supplier_lang')  . " WHERE " . $db->quoteName('supplier_id') . " = " . (int)$supplierId;

                        $db->setQuery($query);
                        $supplierDataLang = $db->loadObjectList();
                        if ($supplierDataLang)
                            foreach ($supplierDataLang as $row)
                                foreach ($row as $key => $value){
                                    if (array_key_exists($key, $this) && $key != 'supplier_id'){
                                        if (!isset($supplierData->{$key}) || !is_array($supplierData->{$key}))
                                            $supplierData->{$key} = array();
                                        $supplierData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                    }
                    JeproshopCache::store($cacheKey, $supplierData);
                }
            } else {
                $supplierData = JeproshopCache::retrieve($cacheKey);
            }

            if ($supplierData){
                //$this->id = (int)$id;
                foreach ($supplierData as $key => $value) {
                    if (array_key_exists($key, $this)) {
                        $this->{$key} = $value;
                    }
                }
            }
        }

        $this->link_rewrite = $this->getLink();
    }

    public function getLink(){
        return JeproshopTools::str2url($this->name);
    }

    /**
     * Return suppliers
     *
     * @param bool $get_nb_products
     * @param int $langId
     * @param bool $published
     * @param bool $p
     * @param bool $n
     * @param bool $allGroups
     * @return array Suppliers
     */
    public static function getSuppliers($get_nb_products = false, $langId = 0, $published = true, $p = false, $n = false, $allGroups = false){
        if (!$langId){ $langId = JeproshopSettingModelSetting::getValue('default_lang'); }
        if (!JeproshopGroupModelGroup::isFeaturePublished()){ $allGroups = true; }

        $db = JFactory::getDBO();

        $query = "SELECT supplier.*, supplier_lang." . $db->quoteName('description') . " FROM " . $db->quoteName('#__jeproshop_supplier');
        $query .= " AS supplier LEFT JOIN " . $db->quoteName('#__jeproshop_supplier_lang') . " AS supplier_lang ON(supplier.";
        $query .= $db->quoteName('supplier_id') . " = supplier_lang." . $db->quoteName('supplier_id') . " AND supplier_lang.";
        $query .= $db->quoteName('lang_id') . " = " . (int)$langId . JeproshopShopModelShop::addSqlAssociation('supplier') . ")";
        $query .= ($published ? " WHERE supplier." . $db->quoteName('published') . " = 1" : "") . " ORDER BY supplier.";
        $query .= $db->quoteName('name') . " ASC " . (($p && $n) ? " LIMIT " . $n . ", " . ($p - 1)*$n : "") ; //. " GROUP BY supplier" . $db->quoteName('supplier_id');

        $db->setQuery($query);
        $suppliers = $db->loadObjectList();

        if ($suppliers === false){ return false; }
        if ($get_nb_products){
            $sqlGroups = '';
            if (!$allGroups)
            {
                $groups = FrontController::getCurrentCustomerGroups();
                $sqlGroups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
            }

            foreach ($suppliers as $key => $supplier){
                $query = "SELECT DISTINCT(product_supplier." . $db->quoteName('product_id') . " FROM " . $db->quoteName('#__jeproshop_product_supplier');
                $query .= " AS product_supplier JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON (product_supplier.";
                $query .= $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . ") " . JeproshopShopModelShop::addSqlAssociation('product');
                $query .= " WHERE product_supplier." . $db->quoteName('supplier_id') . " = " .(int)$supplier->supplier_id  . " AND product_supplier.";
                $query .= $db->quoteName('product_attribute_id') . " = 0 " . ($published ? " AND product_shop." . $db->quoteName('published') . " = 1" : '');
                $query .= " AND product_shop." . $db->quoteName('visibility') . " NOT IN ('none') " .
                    ($allGroups ? " " :
					" AND product_supplier." . $db->quoteName('product_id') . " IN ( SELECT product_category." . $db->quoteName('product_id') .
                    " FROM " . $db->quoteName('#__jeproshop_category_group') . " AS category_group LEFT JOIN " . $db->quoteName('#__jeproshop_product_category') .
                    " AS product_category ON (product_category." . $db->quoteName('category_id') . " = category_group." . $db->quoteName('category_id') .
                    ") WHERE category_group." . $db->quoteName('group_id') .$sqlGroups . ")");

                $db->setQuery($query);
                $result = $db->loadObjectList();
                $suppliers[$key]['nb_products'] = count($result);
            }
        }

        $nb_suppliers = count($suppliers);
        $rewrite_settings = (int)JeproshopSettingModelSetting::getValue('rewrite_settings');
        for ($i = 0; $i < $nb_suppliers; $i++){
            $suppliers[$i]->link_rewrite = ($rewrite_settings ? JeproshopTools::str2url($suppliers[$i]->name) : 0);
        }
        return $suppliers;
    }

    public function getSuppliersList(JeproshopContext $context = null){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!isset($context) || $context == null){ $context = JeproshopContext::getContext(); }

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limit_start = $app->getUserStateFromRequest($option. $view. '.limitstart', 'limitstart', 0, 'int');
        $lang_id = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $order_by = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'supplier_id', 'string');
        $order_way = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC', 'string');

        $use_limit = true;
        if ($limit === false)
            $use_limit = false;

        do{//", supplier." . $db->quoteName('logo') .
            $query = "SELECT SQL_CALC_FOUND_ROWS supplier." . $db->quoteName('supplier_id') . ", supplier."  .$db->quoteName('name');
            $query .= ", COUNT(DISTINCT product_supplier." . $db->quoteName('product_id') . ") AS products, supplier." . $db->quoteName('published') . " FROM ";
            $query .= $db->quoteName('#__jeproshop_supplier') . " AS supplier LEFT JOIN " . $db->quoteName('#__jeproshop_product_supplier') . " AS product_supplier ON (supplier.";
            $query .= $db->quoteName('supplier_id') . " = product_supplier." . $db->quoteName('supplier_id') . ") GROUP BY supplier." . $db->quoteName('supplier_id') . " ORDER BY ";
            $query .= ((str_replace('`', '', $order_by) == 'supplier_id') ? "supplier." : "") . $order_by . " " . $order_way;

            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($use_limit === true) ? " LIMIT " .(int)$limit_start . ", " .(int)$limit : "");

            $db->setQuery($query);
            $suppliers = $db->loadObjectList();

            if($use_limit == true){
                $limit_start = (int)$limit_start -(int)$limit;
                if($limit_start < 0){ break; }
            }else{ break; }
        }while(empty($suppliers));

        $this->pagination = new JPagination($total, $limit_start, $limit);
        return $suppliers;
    }

    public function getSupplierAddress(){
        $db = JFactory::getDBO();

        $query = "SELECT address." . $db->quoteName('company') . ", address." . $db->quoteName('phone') . ", address." . $db->quoteName('phone_mobile') . ", address." . $db->quoteName('address1');
        $query .= ", address." . $db->quoteName('address2') . ", address." . $db->quoteName('postcode') . ", address." . $db->quoteName('country_id') . ", address." . $db->quoteName('state_id');
        $query .= ", address." . $db->quoteName('city') . " FROM " . $db->quoteName('#__jeproshop_address') . " AS address WHERE address." . $db->quoteName('supplier_id') . " = " . (int)$this->supplier_id;

        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
	 * Tells if a supplier exists
	 *
	 * @param $supplierId JeproshopSupplierModelSupplier id
	 * @return boolean
	 */
    public static function supplierExists($supplierId){
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('supplier_id') . " FROM  " . $db->quoteName('#__jeproshop_supplier') . " WHERE ";
        $query .= $db->quoteName('supplier_id') . " = " . (int)$supplierId;

        $db->setQuery($query);
        $res = $db->loaObject();

        return (isset($res) && ($res->supplier_id > 0));
    }

    public function add($fromPost = true){
        if($fromPost){
            $this->copyFromPost();
        }

        $db = JFactory::getDBO();

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_supplier') . "(" . $db->quoteName('name') . ", " . $db->quoteName('date_add');
        $query .= ", " . $db->quoteName('published') . ", " . $db->quoteName('date_upd') . ") VALUES (" . $db->quote($this->name) . ", ";
        $query .= $db->quote($this->date_add) . ", " . ($this->published ? 1 : 0) . ", " . $db->quote($this->date_upd) . ")";

        $db->setQuery($query);
        if($db->query()){
            $this->supplier_id = $db->insertid();
            $languages = JeproshopLanguageModelLanguage::getLanguages(true);

            foreach($languages as $language){
                $query = "INSERT INTO " . $db->quoteName('#__jeproshop_supplier_lang') . "(" . $db->quoteName('description') . ", ";
                $query .= $db->quoteName('short_description') . ", " . $db->quoteName('meta_title') . ", " . $db->quoteName('meta_keywords') ;
                $query .= ", " . $db->quoteName('meta_description') . ", " . $db->quoteName('supplier_id'). ", " . $db->quoteName('lang_id');
                $query .= ") VALUES (" . $db->quote($this->description[$language->lang_id]) . ", " . $db->quote($this->short_description[$language->lang_id]);
                $query .= ", " . $db->quote($this->meta_title[$language->lang_id]) . ", " . $db->quote($this->meta_keywords[$language->lang_id]);
                $query .= ", " . $db->quote($this->meta_description[$language->lang_id]) . ", " . ($this->supplier_id) . ", " . $db->quote($language->lang_id) . ")";

                $db->setQuery($query);
                $db->query();
            }
        }
    }

}