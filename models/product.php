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

class JeproshopProductModelProduct extends JeproshopModel{
    /** @var int product id */
    public $product_id;

    private $context;

    public $product_redirected_id;

    /** @var int default category id */
    public $default_category_id;

    /** @var int default shop id */
    public $default_shop_id;

    /** @var int manufacturer  */
    public $manufacturer_id;

    /** @var int supplier  */
    public $supplier_id;

    public $lang_id ;

    /** @var int  developer id*/
    public $developer_id;

    /** @var array shop list id */
    public $shop_list_id;

    public $shop_id;

    public $name = array();

    public $ecotax;

    public $unity = null;

    public $tax_rules_group_id = 1;

    /**
     * We keep this variable for retro_compatibility for themes
     * @deprecated 1.5.0
     */
    public $default_color_id = 0;

    public $meta_title = array();
    public $meta_keywords = array();
    public $meta_description = array();

    /** @var string Friendly URL */
    public $link_rewrite;

    /**
     * @since 1.5.0
     * @var boolean Tells if the product uses the advanced stock management
     */
    public $advanced_stock_management = 0;
    public $out_of_stock;
    public $depends_on_stock;

    public $isFullyLoaded = false;
    public $cache_is_pack;
    public $cache_has_attachments;
    public $is_virtual;
    public $cache_default_attribute;

    /**
     * @var string If product is populated, this property contain the rewrite link of the default category
     */
    public $category;

    /** @var string Tax name */
    public $tax_name;

    /** @var string Tax rate */
    public $tax_rate;

    /** @var DateTime date_add */
    public $date_add;

    /** @var DateTime date_upd */
    public $date_upd;

    public $manufacturer_name;

    public $supplier_name;

    public $developer_name;

    /** @var string Long description */
    public $description;

    /** @var string Short description */
    public $short_description;

    /** @var float Price in euros */
    public $price = 0;
    public $base_price = 0;

    /** @var float price for product's unity */
    public $unit_price;

    /** @var float price for product's unity ratio */
    public $unit_price_ratio = 0;

    /** @var float Additional shipping cost */
    public $additional_shipping_cost = 0;

    /** @var float Wholesale Price in euros */
    public $wholesale_price = 0;

    /** @var boolean on_sale */
    public $on_sale = false;

    /** @var boolean online_only */
    public $online_only = false;

    /** @var integer Quantity available */
    public $quantity = 0;

    /** @var integer Minimal quantity for add to cart */
    public $minimal_quantity = 1;

    /** @var string available_now */
    public $available_now;

    /** @var string available_later */
    public $available_later;

    /** @var string Reference */
    public $reference;

    /** @var string Supplier Reference */
    public $supplier_reference;

    /** @var string Location */
    public $location;

    /** @var string Width in default width unit */
    public $width = 0;

    /** @var string Height in default height unit */
    public $height = 0;

    /** @var string Depth in default depth unit */
    public $depth = 0;

    /** @var string Weight in default weight unit */
    public $weight = 0;

    /** @var string Ean-13 barcode */
    public $ean13;

    /** @var string Upc barcode */
    public $upc;

    public $isbn;

    /** @var boolean Product status */
    public $quantity_discount = 0;

    public $current_stock;

    /** @var boolean Product customization */
    public $customizable;

    /** @var boolean Product is new */
    public $is_new = null;

    public $uploadable_files;

    /** @var int Number of text fields */
    public $text_fields;

    /** @var boolean Product status */
    public $published = true;

    /** @var boolean Table records are not deleted but marked as deleted if set to true */
    protected $deleted_product = false;

    /** @var boolean Product status */
    public $redirect_type = '';

    /** @var boolean Product available for order */
    public $available_for_order = true;

    /** @var enum Product condition (new, used, refurbished) */
    public $condition;

    /** @var boolean Show price of Product */
    public $show_price = true;

    /** @var boolean is the product indexed in the search index? */
    public $indexed = 0;

    /** @var string Object available order date */
    public $available_date = '0000-00-00';

    /** @var string ENUM('both', 'catalog', 'search', 'none') front office visibility */
    public $visibility;

    /*** @var array Tags */
    public $tags;

    /**
     * Note:  prefix is "PRODUCT_TYPE" because TYPE_ is used in ObjectModel (definition)
     */
    const SIMPLE_PRODUCT = 1;
    const PACKAGE_PRODUCT = 2;
    const VIRTUAL_PRODUCT = 3;

    const CUSTOMIZE_FILE = 0;
    const CUSTOMIZE_TEXT_FIELD = 1;

    public $product_type = self::SIMPLE_PRODUCT;

    public static $_taxCalculationMethod = null;
    protected static $_prices = array();
    protected static $_pricesLevel2 = array();
    protected static $_in_category = array();
    protected static $_cart_quantity = array();
    protected static $_tax_rules_group = array();
    protected static $_cacheFeatures = array();
    protected static $_frontFeaturesCache = array();
    protected static $_productPropertiesCache = array();

    /** @var array cache stock data in getStock() method */
    protected static $cacheStock = array();

    /** definition element */
    public $multiLangShop = true;

    public $multiLang = true;

    

    public function __construct($productId = NULL, $full = FALSE, $langId = NULL, $shopId = NULL, JeproshopContext $context = NULL){
        if($langId !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($langId) ? (int)$langId : JeproshopSettingModelSetting::getValue('default_lang'));
        }

        if($shopId && $this->isMultiShop('product', $this->multiLangShop)){
            $this->shop_id = (int)$shopId;
            $this->get_shop_from_context = FALSE;
        }

        if($this->isMultiShop('product', $this->multiLangShop) && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if($productId){
            $cacheKey = 'jeproshop_product_model_' . $productId . '_' . $langId . '_' . $shopId;
            if(!JeproshopCache::isStored($cacheKey)){
                $db = JFactory::getDBO();
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_product') . " AS product ";
                $where = "";
                /** get language information **/
                if($langId){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ON (product.product_id = product_lang.product_id AND product_lang.lang_id = " . (int)$langId . ") ";
                    if($this->shop_id && !(empty($this->multiLangShop))){
                        $where = " AND product_lang.shop_id = " . $this->shop_id;
                    }
                }

                /** Get shop information **/
                if(JeproshopShopModelShop::isTableAssociated('product')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_shop') . " AS product_shop ON (";
                    $query .= "product.product_id = product_shop.product_id AND product_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE product.product_id = " . (int)$productId . $where;

                $db->setQuery($query);
                $productData = $db->loadObject();

                if($productData){
                    if(!$langId && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_product_lang');
                        $query .= " WHERE product_id = " . (int)$productId;

                        $db->setQuery($query);
                        $productLangData = $db->loadObjectList();
                        if($productLangData){
                            foreach ($productLangData as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'product_id'){
                                        if(!isset($productData->{$key}) || !is_array($productData->{$key})){
                                            $productData->{$key} = array();
                                        }
                                        $productData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cacheKey, $productData);
                }else{
                    $productData = JeproshopCache::retrieve($cacheKey);
                }

                if($productData){
                    $productData->product_id = $productId;
                    foreach($productData as $key => $value){
                        if(array_key_exists($key, $this)){
                            $this->{$key} = $value;
                        }
                    }
                }
            }

            if(!$context){
                $context = JeproshopContext::getContext();
            }

            if($full && $this->product_id){
                $this->isFullyLoaded = $full;
                $this->manufacturer_name = JeproshopManufacturerModelManufacturer::getNameById((int)$this->manufacturer_id);
                $this->supplier_name = JeproshopSupplierModelSupplier::getNameById((int)$this->supplier_id);
                if($this->getType() == self::VIRTUAL_PRODUCT){
                    $this->developer_name = JeproshopDeveloperModelDeveloper::getNameById((int)$this->developer_id);
                }
                $address = NULL;
                if(is_object($context->cart) && $context->cart->{JeproshopSettingModelSetting::getValue('tax_address_type')} != null){
                    $address = $context->cart->{JeproshopSettingModelSetting::getValue('tax_address_type')};
                }

                $this->tax_rate = $this->getTaxesRate(new JeproshopAddressModelAddress($address));

                $this->is_new = $this->isNew();

                $this->base_price = $this->price;

                $this->price = JeproshopProductModelProduct::getStaticPrice((int)$this->product_id, false, null, 6, null, false, true, 1, false, null, null, null, $this->specific_price);
                $this->unit_price = ($this->unit_price_ratio != 0 ? $this->price / $this->unit_price_ratio : 0);
                if($this->product_id){
                    $this->tags = JeproshopTagModelTag::getProductTags((int)$this->product_id);
                }
                $this->loadStockData();
            }

            if($this->default_category_id){
                $this->category = JeproshopCategoryModelCategory::getLinkRewrite((int)$this->default_category_id, (int)$langId);
            }
        }
    }

    public function getType(){
        if(!$this->product_id){
            return JeproshopProductModelProduct::SIMPLE_PRODUCT;
        }

        if(JeproshopProductPack::isPack($this->product_id)){
            return JeproshopProductModelProduct::PACKAGE_PRODUCT;
        }

        if($this->is_virtual){
            return JeproshopProductModelProduct::VIRTUAL_PRODUCT;
        }
        return JeproshopProductModelProduct::SIMPLE_PRODUCT;
    }


    public function isNew(){
        $db = JFactory::getDBO();
        $query = "SELECT product.product_id FROM " . $db->quoteName('#__jeproshop_product') . " AS product ";
        $query .= JeproshopShopModelShop::addSqlAssociation('product') . " WHERE product.product_id = " . (int)$this->product_id;
        $query .= " AND DATEDIFF(product_shop." . $db->quoteName('date_add') . ", DATE_SUB(NOW(), INTERVAL " ;
        $query .= (JeproshopTools::isUnsignedInt(JeproshopSettingModelSetting::getValue('nb_days_product_new')) ? JeproshopSettingModelSetting::getValue('nb_days_product_new') : 20);
        $query .= " DAY) ) > 0";

        $db->setQuery($query);
        $result = $db->loadObjectList();
        return count($result) > 0;
    }

    function getProductList(JeproshopContext $context = NULL){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!$context){ $context = JeproshopContext::getContext(); }

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitStart = $app->getUserStateFromRequest($option. $view. '.limitstart', 'limitstart', 0, 'int');
        $langId = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $shopId = $app->getUserStateFromRequest($option. $view. '.shop_id', 'shop_id', $context->shop->shop_id, 'int');
        $shopGroupId = $app->getUserStateFromRequest($option. $view. '.shop_group_id', 'shop_group_id', $context->shop->shop_group_id, 'int');
        $categoryId = $app->getUserStateFromRequest($option. $view. '.cat_id', 'cat_id', 0, 'int');
        $orderBy = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'date_add', 'string');
        $orderWay = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC', 'string');
        $published = $app->getUserStateFromRequest($option. $view. '.published', 'published', 0, 'string');
        $productAttributeId = $app->getUserStateFromRequest($option. $view. '.product_attribute_id', 'product_attribute_id', 0, 'int');


        if(JeproshopShopModelShop::isFeaturePublished() && $context->cookie->products_filter_category_id){
            $category = new JeproshopCategoryModelCategory((int)$context->cookie->products_filter_category_id);
            if(!$category->inShop()){
                $context->cookie->products_filter_category_id = null;
                $app->redirect('index.php?option=com_jeproshop&view=product');
            }
        }

        //Join categories table
        $categoryId = (int)$app->input->get('product_filter_category_lang!name');
        if($categoryId){
            $category = new JeproshopCategoryModelCategory($categoryId);
            $app->input->set('product_filter_category_lang!name', $category->name[$context->language->lang_id]);
        }else {
            $categoryId = $app->input->get('category_id');
            $currentCategoryId = null;
            if ($categoryId) {
                $currentCategoryId = $categoryId;
                $context->cookie->products_filter_category_id = $categoryId;
            } elseif ($categoryId = $context->cookie->products_filter_category_id) {
                $currentCategoryId = $categoryId;
            }

            if ($currentCategoryId) {
                $category = new JeproshopCategoryModelCategory((int)$currentCategoryId);
            } else {
                $category = new JeproshopCategoryModelCategory();
            }
        }
        $joinCategory = false;
        if(JeproshopTools::isLoadedObject($category, 'category_id') && empty($filter)){
            $joinCategory = true;
        }

        $shopId = (JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP) ? (int)$this->context->shop->shop_id  : "product." . $db->quoteName('default_shop_id');

        $join = " LEFT JOIN " . $db->quoteName('#__jeproshop_image') . " AS image ON (image." . $db->quoteName('product_id') . " = product.";
        $join .= $db->quoteName('product_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_stock_available') . " AS stock_available ON (stock_available.";
        $join .= $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . " AND stock_available." . $db->quoteName('product_attribute_id');
        $join .= " = 0 " . JeproshopStockAvailableModelStockAvailable::addShopRestriction(null, 'stock_available') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_shop');
        $join .= " AS product_shop ON (product." . $db->quoteName('product_id') . " = product_shop." . $db->quoteName('product_id'). " AND product_shop." . $db->quoteName('shop_id');
        $join .= " = " . $shopId . ") LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (product_shop." . $db->quoteName('default_category_id');
        $join .= " = category_lang." . $db->quoteName('category_id') . " AND product_lang." . $db->quoteName('lang_id') . " = category_lang." . $db->quoteName('lang_id') . " AND category_lang.";
        $join .= $db->quoteName('shop_id') . " = " . $shopId . ") LEFT JOIN  " . $db->quoteName('#__jeproshop_shop') . " AS shop ON (shop." . $db->quoteName('shop_id') . " = " . $shopId;
        $join .= ")	LEFT JOIN  " . $db->quoteName('#__jeproshop_image_shop') . " AS image_shop ON (image_shop." . $db->quoteName('image_id') . " = image." . $db->quoteName('image_id');
        $join .= " AND image_shop." . $db->quoteName('cover') . " = 1 AND image_shop." . $db->quoteName('shop_id') . " = " . $shopId . ") LEFT JOIN  " . $db->quoteName('#__jeproshop_product_download');
        $join .= " AS product_download ON (product_download." . $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . ") ";

        $select = "shop." . $db->quoteName('shop_name') . " AS shop_name, product." . $db->quoteName('default_shop_id') . ", MAX(image_shop." . $db->quoteName('image_id') . ") AS image_id, category_lang.";
        $select .= $db->quoteName('name') . " AS category_name, product_shop." . $db->quoteName('price') . ", 0 AS final_price, product." . $db->quoteName('is_virtual') . ", product_download.";
        $select .= $db->quoteName('nb_downloadable') . ", stock_available." . $db->quoteName('quantity') . " AS stock_available_quantity, product_shop." . $db->quoteName('published');
        $select .= ", IF(stock_available." . $db->quoteName('quantity') . " <= 0, 1, 0) badge_danger";

        if($joinCategory){
            $join .= " INNER JOIN " .  $db->quoteName('#__jeproshop_category_product') . " product_category ON (product_category." .  $db->quoteName('product_id') . " = product.";
            $join .=  $db->quoteName('product_id') . " AND product_category." .  $db->quoteName('category_id') . " = " . (int)$category->category_id .") ";
            $select .= " , product_category." .  $db->quoteName('position') . ", ";
        }

        $group = " GROUP BY product_shop." . $db->quoteName('product_id');

        $useLimit = true;
        if ($limit === false)
            $useLimit = false;

        // Add SQL shop restriction
        $selectShop = $joinShop = $whereShop = $where = $filter = "";
        if ($context->controller->shop_link_type){
            $selectShop = ", shop.shop_name AS shopname ";
            $joinShop = ") LEFT JOIN " . $db->quoteName('#__jeproshop_shop') . " AS shop ON product.shop_id = shop.shop_id";
            $whereShop = JeproshopShopModelShop::addSqlRestriction($this->shopShareDatas, 'product'); //, $this->shopLinkType
        }

        /* Query in order to get results with all fields */
        $langJoin = '';
        if ($langId){
            $langJoin = " LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ON (product_lang.";
            $langJoin .= $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . " AND product_lang.";
            $langJoin .= $db->quoteName('lang_id') . " = " . (int)$langId;

            if (!JeproshopShopModelShop::isFeaturePublished()) {
                $langJoin .= " AND product_lang." . $db->quoteName('shop_id') . " = 1";
            }elseif (JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP) {
                //$langJoin .= " AND product_lang." . $db->quoteName('shop_id') . " = " . (int)$shop_lang_id;
            }else{
                $langJoin .= " AND product_lang." . $db->quoteName('shop_id') ." = product.default_shop_id";
            }
            $langJoin .= ")";
        }

        if ($context->controller->multi_shop_context && JeproshopShopModelShop::isTableAssociated('product')){
            if (JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_ALL || !$this->context->employee->isSuperAdmin()){
                $testJoin = !preg_match('/`?'.preg_quote('#__jeproshop_product_shop').'`? *product_shop/', $join);
                if (JeproshopShopModelShop::isFeaturePublished() && $testJoin && JeproshopShopModelShop::isTableAssociated('product')){
                    $where .= " AND product.product_id IN (	SELECT product_shop.product_id FROM " . $db->quoteName('#__jeproshop_product_shop');
                    $where .= " AS product_shop WHERE product_shop.shop_id IN (";
                    $where .= implode(', ', JeproshopShopModelShop::getContextListShopIds())."))";
                }
            }
        }

        $havingClause = '';
        if (isset($filterHaving) || isset($having)){
            $havingClause = " HAVING ";
            if (isset($filterHaving)){
                $havingClause .= ltrim($filterHaving, " AND ");
            }
            if (isset($having)){ $havingClause .= $having . " "; }
        }

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS product." . $db->quoteName('product_id') . ", product_lang." . $db->quoteName('name') . ", product.";
            $query .= $db->quoteName('reference') . ", " . $select . $selectShop . " FROM " . $db->quoteName('#__jeproshop_product') . " AS product ";
            $query .= $langJoin . $join . $joinShop . " WHERE 1 " . $where . $filter .$whereShop . $group . $havingClause . " ORDER BY ";
            $query .= ((str_replace('`', '', $orderBy) == 'product_id') ? "product." : " product.") . $db->quoteName($orderBy) . " " . $db->escape($orderWay);


            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($useLimit === true) ? " LIMIT " .(int)$limitStart . ", " .(int)$limit : "");

            $db->setQuery($query);
            $products = $db->loadObjectList();

            if($useLimit == true){
                $limitStart = (int)$limitStart -(int)$limit;
                if($limitStart < 0){ break; }
            }else{ break; }
        }while(empty($products));

        $this->pagination = new JPagination($total, $limitStart, $limit);
        return $products;
    }

    /**
     * Check if product has attributes combinations
     *
     * @return integer Attributes combinations number
     */
    public function hasAttributes(){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return 0; }

        $db = JFactory::getDBO();
        $query = "SELECT COUNT(*) FROM " .$db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
        $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute') . " WHERE ";
        $query .= "product_attribute." . $db->quoteName('product_id') . " = " . (int)$this->product_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Gets the name of a given product, in the given lang
     *
     * @param $productId
     * @param null $productAttributeId
     * @param null $langId
     * @return string
     */
    public static function getProductName($productId, $productAttributeId = null, $langId = null){
        // use the lang in the context if $langId is not defined
        if (!$langId){
            $langId = (int)JeproshopContext::getContext()->language->lang_id;
        }
        // creates the query object
        $db = JFactory::getDBO();

        // selects different names, if it is a combination
        if ($productAttributeId){
            $query = "SELECT IFNULL(CONCAT(product_lang.name, ' : ', GROUP_CONCAT(DISTINCT attribute_group_lang.";
            $query .= $db->quoteNam('name') . ", ' - ', attribute_lang.name SEPARATOR ', ')),product_lang.name) AS name FROM ";
        }else{
            $query = "SELECT DISTINCT product_lang.name AS name FROM ";
        }
        // adds joins & where clauses for combinations
        if($productAttributeId){
            $query .= $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
            $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute');
            $query .= $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ON(product_lang.product_id = product_attribute.";
            $query .= "product_id AND product_lang.lang_id = " . (int)$langId . JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang');
            $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_combination') . " AS product_attribute_combination ON (";
            $query .= "product_attribute_combination.product_attribute_id = product_attribute.product_attribute_id) LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_attribute') . " AS attribute ON (attribute.attribute_id = product_attribute_combination.attribute_id)";
            $query .= $db->quoteName('#__jeproshop_attribute_lang') . " AS attribute_lang ON (attribute_lang.attribute_id = attribute.attribute_id AND ";
            $query .= " attribute_lang.lang_id = " .(int)$langId . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang');
            $query .= " AS attribute_group_lang ON(attribute_group_lang.attribute_group_id = attribute.attribute_group_id AND attribute_group_lang.lang_id = ";
            $query .= (int)$langId . " WHERE product_attribute.product_id = ".(int)$productId ." AND product_attribute.product_attribute_id = ".(int)$productAttributeId;
        }
        else // or just adds a 'where' clause for a simple product
        {
            $query .= $db->quoteName('#__jeproshop_product_lang') . " AS product_lang WHERE product_lang.product_id = " . (int)$productId . " AND product_lang.";
            $query .= "lang_id = " . (int)$langId . JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang');
        }

        $db->setQuery($query);
        return $db->loadResult();
    }

    public function getAttributesGroups($lang_id){
        if(!JeproshopCombinationModelCombination::isFeaturePublished()){ return array(); }

        $db = JFactory::getDBO();
        $query = "SELECT attribute_group." . $db->quoteName('attribute_group_id') . ", attribute_group." . $db->quoteName('is_color_group');
        $query .= ", attribute_group_lang." . $db->quoteName('name') . " AS group_name, attribute_group_lang." . $db->quoteName('public_name');
        $query .= " AS public_group_name, attribute." . $db->quoteName('attribute_id') . ", attribute_lang." . $db->quoteName('name') . " AS ";
        $query .= "attribute_name, attribute." . $db->quoteName('color') . " AS attribute_color, product_attribute_shop." . $db->quoteName('product_attribute_id');
        $query .= ", IFNULL(stock.quantity, 0) AS quantiy, product_attribute_shop." . $db->quoteName('price') .  ", product_attribute_shop.";
        $query .= $db->quoteName('ecotax') . ", product_attribute_shop." . $db->quoteName('weight') . ", product_attribute_shop." . $db->quoteName('default_on');
        $query .= ", product_attribute." . $db->quoteName('reference') . ", product_attribute_shop." .  $db->quoteName('unit_price_impact');
        $query .= ", product_attribute_shop." . $db->quoteName('minimal_quantity') . ", product_attribute_shop." .  $db->quoteName('available_date');
        $query .= ", attribute_group." .  $db->quoteName('group_type') . " FROM " .  $db->quoteName('#__jeproshop_product_attribute') . " AS ";
        $query .= " product_attribute " . JeproshopShopModelShop::addSqlAssociation('product_attribute'). JeproshopProductModelProduct::sqlStock('product_attribute');
        $query .= " LEFT JOIN " .  $db->quoteName('#__jeproshop_product_attribute_combination') . " AS  product_attribute_combination ON ( product_attribute_combination.";
        $query .=  $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . ") LEFT JOIN " .  $db->quoteName('#__jeproshop_attribute');
        $query .= " AS attribute ON ( attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination." .  $db->quoteName('attribute_id') . " ) LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group ON ( attribute_group." . $db->quoteName('attribute_group_id') . " = attribute.";
        $query .= $db->quoteName('attribute_group_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang') . " AS attribute_lang ON ( attribute." . $db->quoteName('attribute_id');
        $query .= " = attribute_lang." . $db->quoteName('attribute_id') . " ) LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ON ( attribute_group.";
        $query .= $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') . ") " . JeproshopShopModelShop::addSqlAssociation('attribute');
        $query .= " WHERE product_attribute." . $db->quoteName('product_id') . " = " . (int)$this->product_id . " AND attribute_lang." . $db->quoteName('lang_id') . " = ". (int)$lang_id ;
        $query .= " AND attribute_group_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . " GROUP BY attribute_group_id, product_attribute_id ORDER BY attribute_group.";
        $query .= $db->quoteName('position') . " ASC, attribute." . $db->quoteName('position') . " ASC, attribute_group_lang." . $db->quoteName('name') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Fill the variables used for stock management
     */
    public function loadStockData(){
        if (JeproshopTools::isLoadedObject($this, 'product_id')){
            // By default, the product quantity correspond to the available quantity to sell in the current shop
            $this->quantity = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($this->product_id, 0);
            $this->out_of_stock = JeproshopStockAvailableModelStockAvailable::outOfStock($this->product_id);
            $this->depends_on_stock = JeproshopStockAvailableModelStockAvailable::dependsOnStock($this->product_id);
            if (JeproshopContext::getContext()->shop->getShopContext() == JeproshopShopModelShop::CONTEXT_GROUP && JeproshopContext::getContext()->shop->getContextShopGroup()->share_stock == 1){
                $this->advanced_stock_management = $this->useAdvancedStockManagement();
            }
        }
    }

    /**
     * @param type $product_alias
     * @param int|\type $product_attribute
     * @param bool|\type $inner_join
     * @param JeproshopShopModelShop $shop
     * @return string
     */
    public static function sqlStock($product_alias, $product_attribute = 0, $inner_join = FALSE, JeproshopShopModelShop $shop = NULL){
        $db = JFactory::getDBO();
        $shop_id = ($shop !== NULL ? (int)$shop->shop_id : NULL);
        $query = (( $inner_join) ? " INNER " : " LEFT ") . "JOIN " . $db->quoteName('#__jeproshop_stock_available');
        $query .= " stock ON(stock.product_id = " . $db->escape($product_alias) . ".product_id";

        if(!is_null($product_attribute)){
            if(!JeproshopCombinationModelCombination::isFeaturePublished()){
                $query .= " AND stock.product_attribute_id = 0";
            }elseif(is_numeric($product_attribute)){
                $query .= " AND stock.product_attribute_id = " . $product_attribute;
            }elseif (is_string($product_attribute)) {
                $query .= " AND stock.product_attribute_id = IFNULL(" . $db->quoteName($db->escape($product_attribute)) . ".product_attribute_id, 0)";
            }
        }
        $query .=  JeproshopStockAvailableModelStockAvailable::addShopRestriction($shop_id, 'stock') . ")";

        return $query;
    }

    /**
     *
     * @param JeproshopAddressModelAddress $address
     * @return the total taxes rate applied to the product
     */
    public function getTaxesRate(JeproshopAddressModelAddress $address = null){
        if(!$address || $address->country_id){
            $address = JeproshopAddressModelAddress::initialize();
        }

        $taxManager = JeproshopTaxManagerFactory::getManager($address, $this->tax_rules_group_id);
        $taxCalculator = $taxManager->getTaxCalculator();

        return $taxCalculator->getTotalRate();
    }

    public function isAssociatedToShop($shop_id = NULL){
        if($shop_id === NULL){
            $shop_id = (int)JeproshopContext::getContext()->shop->shop_id;
        }

        $cache_id = 'jeproshop_shop_model_product_' . (int)$this->product_id . '_' . (int)$this->shop_id;
        if(!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT shop_id FROM " . $db->quoteName('#__jeproshop_product_shop') . " WHERE " . $db->quoteName('product_id') . " = " . (int)$this->product_id;
            $query .= " AND shop_id = " . (int)$shop_id;

            $db->setQuery($query);
            $result = (bool)$db->loadResult();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Get product accessories (only names)
     *
     * @param integer $lang_id Language id
     * @param integer $product_id Product id
     * @param JeproshopContext $context
     * @return array Product accessories
     */
    public static function getAccessoriesLight($lang_id, $product_id, JeproshopContext $context = null){
        if (!$context){
            $context = JeproshopContext::getContext();
        }
        $db = JFactory::getDBO();

        $query = "SELECT product." . $db->quoteName('product_id') . ", product." . $db->quoteName('reference');
        $query .= ", product_lang." . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_accessory');
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON (product.";
        $query .= $db->quoteName('product_id') . " = " . $db->quoteName('product_2_id') . ") " . JeproshopShopModelShop::addSqlAssociation('product');
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ON ( product.";
        $query .= $db->quoteName('product_id') . " = product_lang." . $db->quoteName('product_id') . " AND product_lang.";
        $query .= $db->quoteName('lang_id') ." = " .(int)$lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang');
        $query .= ") WHERE " . $db->quoteName('product_1_id') . " = " .(int)$product_id;

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * get the default category according to the shop
     */
    public function getDefaultCategoryId(){
        $db = JFactory::getDBO();

        $query = "SELECT product_shop." . $db->quoteName('default_category_id') . " FROM " . $db->quoteName('#__jeproshop_product') . " AS product " ;
        $query .= JeproshopShopModelShop::addSqlAssociation('product') . " WHERE product." . $db->quoteName('product_id') . " = " . (int)$this->product_id;

        $db->setQuery($query);
        $default_category_id = $db->loadResult();

        if (!$default_category_id){
            return JeproshopContext::getContext()->shop->category_id;
        }else{
            return $default_category_id;
        }
    }

    /**
     * Get all available product attributes resume
     *
     * @param integer $langId Language id
     * @param string $attributeValueSeparator
     * @param string $attributeSeparator
     * @return array Product attributes combinations
     */
    public function getAttributesResume($langId, $attributeValueSeparator = ' - ', $attributeSeparator = ', '){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return array(); }
        $addShop = '';

        $db = JFactory::getDBO();

        $query = "SELECT product_attribute.*, product_attribute_shop.* FROM " . $db->quoteName('#__jeproshop_product_attribute');
        $query .= " AS product_attribute " . JeproshopShopModelShop::addSqlAssociation('product_attribute') . "	WHERE product_attribute.";
        $query .= $db->quoteName('product_id') . " = " .(int)$this->product_id . " GROUP BY product_attribute." . $db->quoteName('product_attribute_id');

        $db->setQuery($query);
        $combinations = $db->loadObjectList();

        if (!$combinations){ return false; }

        $productAttributes = array();
        foreach ($combinations as $combination){
            $productAttributes[] = (int)$combination->product_attribute_id;
        }
        $query = "SELECT product_attribute_combination.product_attribute_id, GROUP_CONCAT(attribute_group_lang." . $db->quoteName('name') . ", ";
        $query .= $db->quote($attributeValueSeparator) . ",attribute_lang." . $db->quoteName('name') . " ORDER BY attribute_group_lang.";
        $query .= $db->quoteName('attribute_group_id') . " SEPARATOR " . $db->quote($attributeSeparator).") as attribute_designation FROM ";
        $query .= $db->quoteName('#__jeproshop_product_attribute_combination') . " AS product_attribute_combination LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_attribute') . " AS attribute ON attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination.";
        $query .= $db->quoteName('attribute_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group ON attribute_group.";
        $query .= $db->quoteName('attribute_group_id') . " = attribute." . $db->quoteName('attribute_group_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang');
        $query .= " AS attribute_lang ON (attribute." . $db->quoteName('attribute_id') . " = attribute_lang." . $db->quoteName('attribute_id') . " AND attribute_lang.";
        $query .= $db->quoteName('lang_id') . " = " .(int)$langId . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang');
        $query .= " AS attribute_group_lang ON (attribute_group." . $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id');
        $query .= " AND attribute_group_lang." . $db->quoteName('lang_id') . " = " . (int)$langId . ") WHERE product_attribute_combination.product_attribute_id IN (";
        $query .= implode(',', $productAttributes).") GROUP BY product_attribute_combination.product_attribute_id";

        $db->setQuery($query);
        $lang = $db->loadObjectList();

        foreach ($lang as $k => $row)
            $combinations[$k]->attribute_designation = $row->attribute_designation;


        //Get quantity of each variations
        foreach ($combinations as $key => $row){
            $cacheKey = $row->product_id.'_'.$row->product_attribute_id.'_quantity';

            if (!JeproshopCache::isStored($cacheKey))
                JeproshopCache::store(
                    $cacheKey,
                    JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($row->product_id, $row->product_attribute_id)
                );

            $combinations[$key]->quantity = JeproshopCache::retrieve($cacheKey);
        }

        return $combinations;
    }

    public function getCustomizationFieldIds()
    {
        if (!JeproshopCustomizationModelCustomization::isFeaturePublished()) {
            return array();
        }

        $db = JFactory::getDBO();

        $query = "SELECT customization_field." . $db->quoteName('customization_field_id') . ", customization_field." . $db->quoteName('type');
        $query .= ", customization_field." . $db->quoteName('required') . " FROM " . $db->quoteName('#__jeproshop_customization_field');
        $query .= " as customization_field WHERE customization_field." . $db->quoteName('product_id') . " = " .(int)$this->product_id ;

        $db->setQuery($query);

        if (!$result = $db->loadObjectList()) {
            return false;
        }

        return $result;
    }

    public function getCustomizationFields($langId = false){
        if (!JeproshopCustomizationModelCustomization::isFeaturePublished()){ return false; }

        $db = JFactory::getDBO();

        $query = "SELECT customization_field." . $db->quoteName('customization_field_id') . ", customization_field." . $db->quoteName('type');
        $query .= ", customization_field." . $db->quoteName('required') . ", customization_field_lang." . $db->quoteName('name');
        $query .= ", customization_field_lang." . $db->quoteName('lang_id') . " FROM " . $db->quoteName('#__jeproshop_customization_field');
        $query .= " as customization_field NATURAL JOIN " . $db->quoteName('#__jeproshop_customization_field_lang') . " as customization_field_lang";
        $query .= " WHERE customization_field." . $db->quoteName('product_id') . " = " .(int)$this->product_id ;
        $query .= ($langId ? " AND customization_field_lang." . $db->quoteName('lang_id') . " = " .(int)$langId : '') . " ORDER BY customization_field.";
        $query .= $db->quoteName('customization_field_id');

        $db->setQuery($query);

        if (!$result = $db->loadObjectList()) {
            return false;
        }

        if ($langId)
            return $result;

        $customization_fields = array();
        foreach ($result as $row)
            $customization_fields[(int)$row['type']][(int)$row['id_customization_field']][(int)$row['id_lang']] = $row;

        return $customization_fields;
    }

    /**
     * Gets carriers assigned to the product
     */
    public function getCarriers(){
        $db = JFactory::getDBO();

        $query = "SELECT carrier.* FROM " . $db->quoteName('#__jeproshop_product_carrier') . " AS product_carrier INNER JOIN ";
        $query .= $db->quoteName('#__jeproshop_carrier') . " AS carrier ON (carrier." . $db->quoteName('reference_id') . " = ";
        $query .= "product_carrier." . $db->quoteName('carrier_reference_id') . " AND carrier." . $db->quoteName('deleted');
        $query .= " = 0) WHERE product_carrier." . $db->quoteName('product_id') . " = " .(int)$this->product_id . "	AND ";
        $query .= "product_carrier." . $db->quoteName('shop_id') . " = " .(int)$this->shop_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get product price
     *
     * @param integer $productId Product id
     * @param boolean $useTax With taxes or not (optional)
     * @param integer $productAttributeId Product attribute id (optional).
     *    If set to false, do not apply the combination price impact. NULL does apply the default combination price impact.
     * @param integer $decimals Number of decimals (optional)
     * @param boolean $onlyReduction Returns only the reduction amount
     * @param boolean $useReduction Set if the returned amount will include reduction
     * @param integer $quantity Required for quantity discount application (default value: 1)
     * @param integer $customerId Customer ID (for customer group reduction)
     * @param integer $cartId Cart ID. Required when the cookie is not accessible (e.g., inside a payment module, a cron task...)
     * @param integer $addressId Customer address ID. Required for price (tax included) calculation regarding the guest localization
     * @param null $specificPriceOutput
     * @param boolean $withEcoTax insert ecotax in price output.
     * @param bool $useGroupReduction
     * @param JeproshopContext $context
     * @param bool $useCustomerPrice
     * @internal param int $divisor Useful when paying many time without fees (optional)
     * @internal param \variable_reference $specificPriceOutput .
     *    If a specific price applies regarding the previous parameters, this variable is filled with the corresponding SpecificPrice object
     * @return float Product price
     */
    public static function getStaticPrice($productId, $useTax = true, $productAttributeId = null, $decimals = 6, $onlyReduction = false, $useReduction = true, $quantity = 1, $customerId = null,
                                          $cartId = null, $addressId = null, $specificPriceOutput = null, $withEcoTax = true, $useGroupReduction = true, JeproshopContext $context = null, $useCustomerPrice = true){
        if(!$context){
            $context = JeproshopContext::getContext();
        }

        $cur_cart = $context->cart;

        if (!JeproshopTools::isBool($useTax) || !JeproshopTools::isUnsignedInt($productId)){
            //die(Tools::displayError());
        }

        // Initializations
        $groupId = (int)JeproshopGroupModelGroup::getCurrent()->group_id;

        // If there is cart in context or if the specified id_cart is different from the context cart id
        if (!is_object($cur_cart) || (JeproshopTools::isUnsignedInt($cartId) && $cartId && $cur_cart->cart_id != $cartId)){
            /*
             * When a user (e.g., guest, customer, Google...) is on Jeproshop, he has already its cart as the global (see /init.php)
             * When a non-user calls directly this method (e.g., payment module...) is on JeproShop, he does not have already it BUT knows the cart ID
             * When called from the back office, cart ID can be inexistent
             * /
            if (!$cartId && !isset($context->employee)){
                JError::raiseError(500, __FILE__ . ' ' . __LINE__);
            }*/
            $currentCart = new JeproshopCartModelCart($cartId);
            // Store cart in context to avoid multiple instantiations in BO
            if (!JeproshopTools::isLoadedObject($context->cart, 'cart_id')){
                $context->cart = $currentCart;
            }
        }
        $db = JFactory::getDBO();
        $cartQuantity = 0;
        if ((int)$cartId){
            $cacheKey= 'jeproshop_product_model_get_price_static_' . (int)$productId .'_' . (int)$cartId;
            $cart_qty = JeproshopCache::retrieve($cacheKey);
            if (!JeproshopCache::isStored($cacheKey) || ( $cart_qty != (int)$quantity)){
                $query = "SELECT SUM(" . $db->quoteName('quantity') . ") FROM " . $db->quoteName('#__jeproshop_cart_product');
                $query .= " WHERE " . $db->quoteName('product_id') . " = " . (int)$productId . " AND " . $db->quoteName('cart_id');
                $query .= " = " .(int)$cartId;
                $db->setQuery($query);
                $cartQuantity = (int)$db->loadResult();
                JeproshopCache::store($cacheKey, $cartQuantity);
            }
            $cartQuantity = JeproshopCache::retrieve($cacheKey);
        }

        $currencyId = (int)JeproshopTools::isLoadedObject($context->currency, 'currency_id') ? $context->currency->currency_id : JeproshopSettingModelSetting::getValue('default_currency');

        // retrieve address information
        $countryId = (int)$context->country->country_id;
        $stateId = 0;
        $zipCode = 0;

        if (!$addressId && JeproshopTools::isLoadedObject($cur_cart, 'cart_id')){
            $addressId = $cur_cart->{JeproshopSettingModelSetting::getValue('tax_address_type')};
        }

        if ($addressId){
            $addressInfo = JeproshopAddressModelAddress::getCountryAndState($addressId);
            if ($addressInfo->country_id){
                $countryId = (int)$addressInfo->country_id;
                $stateId = (int)$addressInfo->state_id;
                $zipCode = $addressInfo->postcode;
            }
        }else if (isset($context->customer->geoloc_country_id)){
            $countryId = (int)$context->customer->geoloc_country_id;
            $stateId = (int)$context->customer->state_id;
            $zipCode = (int)$context->customer->postcode;
        }

        if (JeproshopTaxModelTax::taxExcludedOption()){
            $useTax = false;
        }

        if ($useTax != false && !empty($addressInfo->vat_number)
            && $addressInfo->country_id != JeproshopSettingModelSetting::getValue('vat_number_country')
            && JeproshopSettingModelSetting::getValue('vat_number_management')){
            $useTax = false;
        }

        if (is_null($customerId) && JeproshopTools::isLoadedObject($context->customer, 'customer_id')){
            $customerId = $context->customer->customer_id;
        }

        return JeproshopProductModelProduct::priceCalculation($context->shop->shop_id, $productId,
            $productAttributeId, $countryId, $stateId, $zipCode, $currencyId, $groupId,
            $quantity, $useTax, $decimals, 	$onlyReduction, $useReduction, $withEcoTax, $specificPriceOutput,
            $useGroupReduction, $customerId, $useCustomerPrice, $cartId, $cartQuantity
        );
    }

    /**
     * Price calculation / Get product price
     *
     * @param integer $shopId Shop id
     * @param integer $productId Product id
     * @param integer $productAttributeId Product attribute id
     * @param integer $countryId Country id
     * @param integer $stateId State id
     * @param $zipCode
     * @param integer $currencyId Currency id
     * @param integer $groupId Group id
     * @param integer $quantity Quantity Required for Specific prices : quantity discount application
     * @param boolean $useTax with (1) or without (0) tax
     * @param integer $decimals Number of decimals returned
     * @param boolean $onlyReduction Returns only the reduction amount
     * @param boolean $useReduction Set if the returned amount will include reduction
     * @param boolean $withEcoTax InsertEcotax in price output.
     * @param $specificPrice
     * @param $useGroupReduction
     * @param int $customerId
     * @param bool $useCustomerPrice
     * @param int $cartId
     * @param int $realQuantity
     * @internal param \variable_reference $specific_price_output If a specific price applies regarding the previous parameters, this variable is filled with the corresponding SpecificPrice object*    If a specific price applies regarding the previous parameters, this variable is filled with the corresponding SpecificPrice object
     * @return float Product price
     */
    public static function priceCalculation($shopId, $productId, $productAttributeId, $countryId, $stateId, $zipCode, $currencyId, $groupId, $quantity, $useTax,
                                            $decimals, $onlyReduction, $useReduction, $withEcoTax, &$specificPrice, $useGroupReduction, $customerId = 0, $useCustomerPrice = true, $cartId = 0, $realQuantity = 0){
        static $address = null;
        static $context = null;

        if ($address === null){
            $address = new JeproshopAddressModelAddress();
        }

        if ($context == null){
            $context = JeproshopContext::getContext()->cloneContext();
        }

        if ($shopId !== null && $context->shop->shop_id != (int)$shopId){
            $context->shop = new JeproshopShopModelShop((int)$shopId);
        }

        if (!$useCustomerPrice){
            $customerId = 0;
        }

        if ($productAttributeId === null){
            $productAttributeId = JeproshopProductModelProduct::getDefaultAttribute($productId);
        }

        $cacheKey = $productId . '_' .$shopId . '_' . $currencyId . '_' . $countryId . '_' . $stateId . '_' . $zipCode . '_' . $groupId .
            '_' . $quantity . '_' . $productAttributeId . '_' .($useTax ? '1' : '0').'_' . $decimals.'_'. ($onlyReduction ? '1' :'0').
            '_'.($useReduction ?'1':'0') . '_' . $withEcoTax. '_' . $customerId . '_'.(int)$useGroupReduction.'_'.(int)$cartId.'-'.(int)$realQuantity;

        // reference parameter is filled before any returns
        $specificPrice = JeproshopSpecificPriceModelSpecificPrice::getSpecificPrice((int)$productId, $shopId, $currencyId,
            $countryId, $groupId, $quantity, $productAttributeId, $customerId, $cartId, $realQuantity
        );

        if (isset(self::$_prices[$cacheKey])){
            return self::$_prices[$cacheKey];
        }
        $db = JFactory::getDBO();
        // fetch price & attribute price
        $cacheKey2 = $productId . '_' . $shopId;
        if (!isset(self::$_pricesLevel2[$cacheKey])){
            $select = "SELECT product_shop." . $db->quoteName('price') . ", product_shop." . $db->quoteName('ecotax');
            $from = $db->quoteName('#__jeproshop_product') . " AS product INNER JOIN " . $db->quoteName('#__jeproshop_product_shop');
            $from .= " AS product_shop ON (product_shop.product_id = product.product_id AND product_shop.shop_id = " .(int)$shopId  . ")";

            if (JeproshopCombinationModelCombination::isFeaturePublished()){
                $select .= ", product_attribute_shop.product_attribute_id, product_attribute_shop." . $db->quoteName('price') . " AS attribute_price, product_attribute_shop.default_on";
                $leftJoin = " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute') .  " AS product_attribute ON product_attribute.";
                $leftJoin .= $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_shop');
                $leftJoin .= " AS product_attribute_shop ON (product_attribute_shop.product_attribute_id = product_attribute.product_attribute_id AND product_attribute_shop.shop_id = " .(int)$shopId .")";
            }else{
                $select .= ", 0 as product_attribute_id";
                $leftJoin = "";
            }
            $query = $select . " FROM " . $from . $leftJoin . " WHERE product." . $db->quoteName('product_id') . " = " . (int)$productId;

            $db->setQuery($query);
            $results = $db->loadObjectList();

            foreach ($results as $row){
                $array_tmp = array(
                    'price' => $row->price, 'ecotax' => $row->ecotax,
                    'attribute_price' => (isset($row->attribute_price) ? $row->attribute_price : null)
                );

                self::$_pricesLevel2[$cacheKey2][(int)$row->product_attribute_id] = $array_tmp;

                if (isset($row->default_on) && $row->default_on == 1){
                    self::$_pricesLevel2[$cacheKey2][0] = $array_tmp;
                }
            }
        }

        if (!isset(self::$_pricesLevel2[$cacheKey2][(int)$productAttributeId])){
            return;
        }

        $result = self::$_pricesLevel2[$cacheKey2][(int)$productAttributeId];

        if (!$specificPrice || $specificPrice->price < 0){
            $price = (float)$result['price'];
        }else{
            $price = (float)$specificPrice->price;
        }

        // convert only if the specific price is in the default currency (id_currency = 0)
        if (!$specificPrice || !($specificPrice->price >= 0 && $specificPrice->currency_id)){
            $price = JeproshopTools::convertPrice($price, $currencyId);
        }

        // Attribute price
        if (is_array($result) && (!$specificPrice || !$specificPrice->product_attribute_id || $specificPrice->price < 0)){
            $attributePrice = JeproshopTools::convertPrice($result['attribute_price'] !== null ? (float)$result['attribute_price'] : 0, $currencyId);
            // If you want the default combination, please use NULL value instead
            if ($productAttributeId !== false){
                $price += $attributePrice;
            }
        }

        // Tax
        $address->country_id = $countryId;
        $address->state_id = $stateId;
        $address->postcode = $zipCode;

        $taxManager = JeproshopTaxManagerFactory::getManager($address, JeproshopProductModelProduct::getTaxRulesGroupIdByProductId((int)$productId, $context));
        $productTaxCalculator = $taxManager->getTaxCalculator();

        // Add Tax
        if ($useTax){
            $price = $productTaxCalculator->addTaxes($price);
        }

        // Reduction
        $specificPriceReduction = 0;
        if (($onlyReduction || $useReduction) && $specificPrice){
            if ($specificPrice->reduction_type == 'amount'){
                $reductionAmount = $specificPrice->reduction;

                if (!$specificPrice->currency_id){
                    $reductionAmount = JeproshopTools::convertPrice($reductionAmount, $currencyId);
                }
                $specificPriceReduction = !$useTax ? $productTaxCalculator->removeTaxes($reductionAmount) : $reductionAmount;
            }else{
                $specificPriceReduction = $price * $specificPrice->reduction;
            }
        }

        if ($useReduction){
            $price -= $specificPriceReduction;
        }

        // Group reduction
        if($useGroupReduction){
            $reductionFromCategory = JeproshopGroupReductionModelGroupReduction::getValueForProduct($productId, $groupId);
            if ($reductionFromCategory !== false){
                $groupReduction = $price * (float)$reductionFromCategory;
            }else {
                // apply group reduction if there is no group reduction for this category
                $groupReduction = (($reduction = JeproshopGroupModelGroup::getReductionByGroupId($groupId)) != 0) ? ($price * $reduction / 100) : 0;
            }
        }else{
            $groupReduction = 0;
        }

        if ($onlyReduction){
            return JeproshopTools::roundPrice($groupReduction + $specificPriceReduction, $decimals);
        }

        if ($useReduction){  $price -= $groupReduction;   }

        // Eco Tax
        if (($result['ecotax'] || isset($result['attribute_ecotax'])) && $withEcoTax){
            $ecoTax = $result['ecotax'];
            if (isset($result['attribute_ecotax']) && $result['attribute_ecotax'] > 0){
                $ecoTax = $result['attribute_ecotax'];
            }
            if ($currencyId){
                $ecoTax = JeproshopTools::convertPrice($ecoTax, $currencyId);
            }

            if ($useTax){
                // re-init the tax manager for eco-tax handling
                $taxManager = JeproshopTaxManagerFactory::getManager($address, (int)  JeproshopSettingModelSetting::getValue('ecotax_tax_rules_group_id'));
                $ecoTaxTaxCalculator = $taxManager->getTaxCalculator();
                $price += $ecoTaxTaxCalculator->addTaxes($ecoTax);
            }else{
                $price += $ecoTax;
            }
        }
        $price = JeproshopTools::roundPrice($price, $decimals);
        if ($price < 0){
            $price = 0;
        }

        self::$_prices[$cacheKey] = $price;
        return self::$_prices[$cacheKey];
    }

    public static function getMostUsedTaxRulesGroupId(){
        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('tax_rules_group_id') . " FROM ( SELECT COUNT(*) n, product_shop." . $db->quoteName('tax_rules_group_id');
        $query .= " FROM " . $db->quoteName('#__jeproshop_product') . " AS product " . JeproshopShopModelShop::addSqlAssociation('product');
        $query .=  " JOIN " . $db->quoteName('#__jeproshop_tax_rules_group') . " AS tax_rule_group ON (product_shop." . $db->quoteName('tax_rules_group_id');
        $query .= " = tax_rule_group." . $db->quoteName('tax_rules_group_id') . ") WHERE tax_rule_group." . $db->quoteName('published');
        $query .= " = 1 AND tax_rule_group." . $db->quoteName('deleted') . " = 0 GROUP BY product_shop." . $db->quoteName('tax_rules_group_id');
        $query .= " ORDER BY n DESC LIMIT 1 ) most_used";

        $db->setQuery($query);
        $data = $db->loadObject();;

        return (isset($data) ? $data->tax_rules_group_id : 0);
    }

    public function getTaxRulesGroupId(){
        return $this->tax_rules_group_id;
    }

    /**
     * Select all features for the object
     *
     * @return array Array with feature product's data
     */
    public function getFeatures(){
        return JeproshopProductModelProduct::getStaticFeatures((int)$this->product_id);
    }

    public static function getStaticFeatures($productId){
        if (!JeproshopFeatureModelFeature::isFeaturePublished()){ return array(); }
        if (!array_key_exists($productId, self::$_cacheFeatures)){
            $db = JFactory::getDBO();

            $query = "SELECT product_feature.feature_id, product_feature.product_id, product_feature.feature_value_id, custom FROM ";
            $query .= $db->quoteName('#__jeproshop_feature_product') . " AS product_feature LEFT JOIN " . $db->quoteName('#__jeproshop_feature_value');
            $query .= " AS feature_value ON (product_feature.feature_value_id = feature_value.feature_value_id ) WHERE ";
            $query .= $db->quoteName('product_id') . " = " .(int)$productId;

            $db->setQuery($query);
            self::$_cacheFeatures[$productId] = $db->loadObjectList();
        }
        return self::$_cacheFeatures[$productId];
    }

    public function getCombinationImages($langId){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return false; }

        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('product_attribute_id') . " FROM " . $db->quoteName('#__jeproshop_product_attribute');
        $query .= " WHERE " . $db->quoteName('product_id') . " = " . (int)$this->product_id;
        $db->setQuery($query);
        $product_attributes = $db->loadObjectList();

        if (!$product_attributes)
            return false;

        $ids = array();

        foreach ($product_attributes as $product_attribute){
            $ids[] = (int)$product_attribute->product_attribute_id;
        }

        $query = "SELECT product_attribute_image." . $db->quoteName('image_id') . ", product_attribute_image." . $db->quoteName('product_attribute_id');
        $query .= ", image_lang." . $db->quoteName('legend') . " FROM " . $db->quoteName('#__jeproshop_product_attribute_image') . " AS product_attribute_image";
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image_lang." . $db->quoteName('image_id') . " = product_attribute_image.";
        $query .= $db->quoteName('image_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_image') . " AS image ON (image." . $db->quoteName('image_id');
        $query .= " = product_attribute_image." . $db->quoteName('image_id') . ") WHERE product_attribute_image." . $db->quoteName('product_attribute_id');
        $query .= " IN (" .implode(', ', $ids). ") AND image_lang." . $db->quoteName('lang_id') . " = " .(int)$langId . " ORDER by image." . $db->quoteName('position');

        $db->setQuery($query);
        $result = $db->loadObjectList();

        if (!$result)
            return false;

        $images = array();

        foreach ($result as $row)
            $images[$row->product_attribute_id][] = $row;

        return $images;
    }

    public static function getTaxRulesGroupIdByProductId($productId, JeproshopContext $context = null) {
        if (!$context){
            $context = JeproshopContext::getContext();
        }
        $key = 'product_tax_rules_group_id_'.(int)$productId .'_'.(int)$context->shop->shop_id;
        if (!JeproshopCache::isStored($key)){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('tax_rules_group_id') . " FROM " . $db->quoteName('#__jeproshop_product_shop') . " WHERE ";
            $query .= $db->quoteName('product_id') . " = " .(int)$productId . " AND shop_id = " .(int)$context->shop->shop_id;

            $db->setQuery($query);
            $taxRulesGroupId = $db->loadObject()->tax_rules_group_id;
            JeproshopCache::store($key, $taxRulesGroupId);
        }
        return JeproshopCache::retrieve($key);
    }

    public static function cacheProductsFeatures($productIds) {
        if (!JeproshopFeatureModelFeature::isFeaturePublished()){ return; }

        $product_implode = array();
        foreach ($productIds as $productId) {
            if ((int)$productId && !array_key_exists($productId, self::$_cacheFeatures)) {
                $product_implode[] = (int)$productId;
            }
        }

        if (!count($product_implode)){ return; }

        $db = JFactory::getDBO();

        $query = "SELECT feature_id, product_id, feature_value_id FROM " . $db->quoteName('#__jeproshop_feature_product') . " WHERE ";
        $query .= $db->quoteName('product_id') . " IN (" .implode($product_implode, ','). ")";

        $db->setQuery($query);
        $result = $db->loadObjectList();

        foreach ($result as $row){
            if (!array_key_exists($row->product_id, self::$_cacheFeatures))
                self::$_cacheFeatures[$row->product_id] = array();
            self::$_cacheFeatures[$row->product_id][] = $row;
        }
    }

    public static function defineProductImage($row, $langId){
        if (isset($row->image_id) && $row->image_id)
            return $row->product_id . '_' .$row->image_id;

        return JeproshopLanguageModelLanguage::getIsoCodeByLanguageId((int)$langId).'_default';
    }

    public static function isAvailableWhenOutOfStock($outOfStock){
        // @TODO Update of STOCK_MANAGEMENT & ORDER_OUT_OF_STOCK
        $return = (int)$outOfStock == 2 ? (int)JeproshopSettingModelSetting::getValue('order_out_of_stock') : (int)$outOfStock;
        return !JeproshopSettingModelSetting::getValue('stock_management')? true : $return;
    }

    public static function getTaxesInformation($row, JeproshopContext $context = null){
        static $address = null;

        if ($context === null)
            $context = JeproshopContext::getContext();
        if ($address === null)
            $address = new JeproshopAddressModelAddress();

        $address->country_id = (int)$context->country->country_id;
        $address->state_id = 0;
        $address->postcode = 0;

        $taxManager = JeproshopTaxManagerFactory::getManager($address, JeproshopProductModelProduct::getTaxRulesGroupIdByProductId((int)$row->product_id, $context));
        $row->rate = $taxManager->getTaxCalculator()->getTotalRate();
        $row->tax_name = $taxManager->getTaxCalculator()->getTaxesName();

        return $row;
    }

    public static function getCoverImage($productId){
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('image_id') . " FROM " . $db->quoteName('#__jeproshop_image') . " WHERE ";
        $query .= $db->quoteName('product_id') . " = " . (int)$productId . " AND cover = 1 ";

        $db->setQuery($query);
        return $db->loadObject();
    }

    /*
	 ** Customization management
	 */
    public static function getAllCustomizedData($cartId, $langId = null, $onlyInCart = true, $shopId = null, $customizationId = null){
        if (!JeproshopCustomizationModelCustomization::isFeaturePublished()){ return false; }

        $db = JFactory::getDBO();

        // No need to query if there isn't any real cart!
        if (!$cartId){ return false; }
        if (!$langId){	$langId = JeproshopContext::getContext()->language->lang_id; }

        if(JeproshopShopModelShop::isFeaturePublished() && !$shopId){
            $shopId = JeproshopContext::getContext()->shop->shop_id;
        }

        $query = "SELECT customized_data." . $db->quoteName('customization_id') . ", customization." . $db->quoteName('delivery_address_id');
        $query .= ", customization." . $db->quoteName('product_id') . ", customization_field_lang." . $db->quoteName('customization_field_id');
        $query .= ", customization." . $db->quoteName('product_attribute_id') . ", customized_data." . $db->quoteName('type') . ", ";
        $query .= "customized_data." . $db->quoteName('index') . ", customized_data." . $db->quoteName('value') . ", ";
        $query .= "customization_field_lang." . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_customized_data') . " AS ";
        $query .= " customized_data NATURAL JOIN " . $db->quoteName('#__jeproshop_customization') . " AS customization LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_customization_field_lang') . " AS customization_field_lang ON (customization_field_lang.";
        $query .= "customization_field_id = customized_data." . $db->quoteName('index') . " AND lang_id = " .(int)$langId ;
        $query .= ((int)$shopId ? " AND customized_field." . $db->quoteName('shop_id') . " = " . $shopId : "" ) . ") WHERE ";
        $query .= "customization." . $db->quoteName('cart_id') . " = " . (int)$cartId . ($onlyInCart ? " AND customization." .$db->quoteName('in_cart') . " = 1"  : "");
        $query .= ($customizationId ? " AND customized_data." . $db->quoteName('customization_id') . " = " . (int)$customizationId : " ");
        $query .= " ORDER BY " . $db->quoteName('product_id'). ", " . $db->quoteName('product_attribute_id') . ", " . $db->quoteName('type') . ", " . $db->quoteName('index');

        $db->setQuery($query);
        $result = $db->loadObjectList();

        if (!$result){ return false; }

        $customizedData = array();

        foreach ($result as $row){
            $customizedData[(int)$row->product_id][(int)$row->product_attribute_id][(int)$row->delivery_address_id][(int)$row->customization_id]['data'][(int)$row->type][] = $row;
        }

        $query = "SELECT " . $db->quoteName('product_id') . ", " . $db->quoteName('product_attribute_id') . ", " . $db->quoteName('customization_id');
        $query .= ", " . $db->quoteName('delivery_address_id') . ", " . $db->quoteName('quantity') . ", " . $db->quoteName('quantity_refunded') . ", ";
        $query .= $db->quoteName('quantity_returned') . " FROM " . $db->quoteName('#__jeproshop_customization') . " WHERE " . $db->quoteName('cart_id');
        $query .= " = " . (int)($cartId) . ($onlyInCart ? " AND " . $db->quoteName('in_cart') . " = 1"  : "");
        $query .= ($customizationId ? " AND " . $db->quoteName('customization_id') . " = " . (int)$customizationId : " ");

        $db->seQuery($query);
        $result = $db->loadObjectList();
        if (!$result ){ return false; }

        foreach ($result as $row){
            $customizedData[(int)$row->product_id][(int)$row->product_attribute_id][(int)$row->delivery_address_id][(int)$row->customization_id]['quantity'] = (int)$row->quantity;
            $customizedData[(int)$row->product_id][(int)$row->product_attribute_id][(int)$row->delivery_address_id][(int)$row->customization_id]['quantity_refunded'] = (int)$row->quantity_refunded;
            $customizedData[(int)$row->product_id][(int)$row->product_attribute_id][(int)$row->delivery_address_id][(int)$row->customization_id]['quantity_returned'] = (int)$row->quantity_returned;
        }

        return $customizedData;
    }


    public static function addCustomizationPrice(&$products, &$customizedData){
        /*if (!$customizedData) {
            return;
        } */

        foreach ($products as &$productUpdate){
            if (!JeproshopCustomizationModelCustomization::isFeaturePublished()){
                $productUpdate->customization_quantity_total = 0;
                $productUpdate->customization_quantity_refunded = 0;
                $productUpdate->customization_quantity_returned = 0;
            }else{
                $customizationQuantity = 0;
                $customizationQuantityRefunded = 0;
                $customizationQuantityReturned = 0;

                /* Compatibility */
                $productId = (int)(isset($productUpdate->product_id) ? $productUpdate->product_id : $productUpdate->product_id);
                $productAttributeId = (int)(isset($productUpdate->product_attribute_id) ? $productUpdate->product_attribute_id : $productUpdate->product_attribute_id);
                $deliveryAddressId = (int)$productUpdate->delivery_address_id;
                $productQuantity = (int)(isset($productUpdate->cart_quantity) ? $productUpdate->cart_quantity : $productUpdate->product_quantity);
                $price = isset($productUpdate->price) ? $productUpdate->price : 0;
                if (isset($productUpdate->price_with_tax) && $productUpdate->price_with_tax)
                    $priceWithTax = $productUpdate->price_with_tax;
                else
                    $priceWithTax = $price * (1 + ((isset($productUpdate->tax_rate) ? $productUpdate->tax_rate : $productUpdate->rate) * 0.01));

                if (!isset($customizedData[$productId][$productAttributeId][$deliveryAddressId]))
                    $deliveryAddressId = 0;
                if (isset($customizedData[$productId][$productAttributeId][$deliveryAddressId])){
                    foreach ($customizedData[$productId][$productAttributeId][$deliveryAddressId] as $customization){
                        $customizationQuantity += (int)$customization->quantity;
                        $customizationQuantityRefunded += (int)$customization->quantity_refunded;
                        $customizationQuantityReturned += (int)$customization->quantity_returned;
                    }
                }

                $productUpdate->customization_quantity_total = $customizationQuantity;
                $productUpdate->customization_quantity_refunded = $customizationQuantityRefunded;
                $productUpdate->customization_quantity_returned = $customizationQuantityReturned;

                if ($customizationQuantity){
                    $productUpdate->total_with_tax = $priceWithTax * ($productQuantity - $customizationQuantity);
                    $productUpdate->total_customization_with_tax = $priceWithTax * $customizationQuantity;
                    $productUpdate->total = $price * ($productQuantity - $customizationQuantity);
                    $productUpdate->total_customization = $price * $customizationQuantity;
                }
            }
        }
    }

    public static function getProductAttributeCoverImage($productAttributeId){
        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('image_id') . " FROM " . $db->quoteName('#__jeproshop_product_attribute_image');
        $query .= " WHERE product_attribute_id = " . (int)$productAttributeId;

        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Admin panel product search
     *
     * @param int $langId Language id
     * @param string $search Search query
     * @param JeproshopContext $context
     * @return array Matching products
     */
    public static function searchProductsByName($langId, $search, JeproshopContext $context = null){
        if (!$context) {
            $context = JeproshopContext::getContext();
        }

        $db = JFactory::getDBO();

        $search = $db->quote('%' . $db->escape($search) . '%');

        $query = "SELECT product." . $db->quoteName('product_id') . ", product_lang." . $db->quoteName('name') . ", product.";
        $query .= $db->quoteName('ean13') . ", product."  . $db->quoteName('upc') . ", product." . $db->quoteName('reference');;
        $query .= ", manufacturer." . $db->quoteName('name') . " AS manufacturer_name, stock." . $db->quoteName('quantity');
        $query .= ", product_shop." . $db->quoteName('advanced_stock_management') . ", product." . $db->quoteName('customizable') ;
        $query .= " FROM " . $db->quoteName('#__jeproshop_product') . " AS product " . JeproshopShopModelShop::addSqlAssociation('product');
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ON (product." . $db->quoteName('product_id');
        $query .= " = product_lang." . $db->quoteName('product_id') . " AND product_lang." . $db->quoteName('lang_id') . " = " . $langId;
        $query .= JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_manufacturer');
        $query .= " AS manufacturer ON (manufacturer." . $db->quoteName('manufacturer_id') . " = product." . $db->quoteName('manufacturer_id') . ")";
        $query .= JeproshopProductModelProduct::sqlStock('product', 0) . " WHERE product_lang." . $db->quoteName('name') . " LIKE ";
        $query .= $search . " OR product." . $db->quoteName('ean13') . " LIKE " . $search . " OR product." . $db->quoteName('upc');
        $query .= " LIKE " . $search . " OR product." . $db->quoteName('reference') . " LIKE " . $search . " OR product.";
        $query .= $db->quoteName('supplier_reference') . " LIKE " . $search . " OR EXISTS (SELECT * FROM " . $db->quoteName('#__jeproshop_product_supplier');
        $query .= " AS product_supplier WHERE product_supplier." . $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . " AND product_supplier.";
        $query .= $db->quoteName('product_supplier_reference') . " LIKE " . $search . ")";

        if(JeproshopCombinationModelCombination::isFeaturePublished()){
            $query .= " OR EXISTS (SELECT * FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
            $query .= " WHERE product_attribute." . $db->quoteName('product_id') . " = product." . $db->quoteName('product_id');
            $query .= " AND (product_attribute." . $db->quoteName('reference') . " LIKE " . $search . " OR product_attribute.";
            $query .= $db->quoteName('ean13') . " LIKE " . $search . " OR product_attribute." . $db->quoteName('upc') . " LIKE ";
            $query .= $search . ") )";
        }


        $db->setQuery($query);

        $result = $db->loadObjectList();

        if (!$result) {
            return false;
        }

        $resultsArray = array();
        foreach ($result as $row) {
            $row->price_tax_incl = JeproshopProductModelProduct::getStaticPrice($row->product_id, true, null, 2);
            $row->price_tax_excl = JeproshopProductModelProduct::getStaticPrice($row->product_id, false, null, 2);
            $resultsArray[] = $row;
        }
        return $resultsArray;
    }

    /**
     * Get the default attribute for a product
     *
     * @param $productId
     * @param int $minimumQuantity
     * @param bool $reset
     * @return int Attributes list
     */
    public static function getDefaultAttribute($productId, $minimumQuantity = 0, $reset = false){
        static $combinations = array();

        if (!JeproshopCombinationModelCombination::isFeaturePublished()) {
            return 0;
        }

        if ($reset && isset($combinations[$productId])) {
            unset($combinations[$productId]);
        }

        if (!isset($combinations[$productId])) {
            $combinations[$productId] = array();
        }
        if (isset($combinations[$productId][$minimumQuantity])) {
            return $combinations[$productId][$minimumQuantity];
        }

        $db = JFactory::getDBO();

        $query = "SELECT product_attribute_shop." . $db->quoteName('product_attribute_id') . " FROM " . $db->quoteName('#__jeproshop_product_attribute');
        $query .= " AS product_attribute " . JeproshopShopModelShop::addSqlAssociation('product_attribute') . " WHERE product_attribute.";
        $query .= $db->quoteName('product_id') . " = " .(int)$productId;

        $db->setQuery($query);
        $productAttribute = $db->loadObject();

        $resultNoFilter = (is_object($productAttribute) ? $productAttribute->product_attribute_id : 0);

        if (!$resultNoFilter) {
            $combinations[$productId][$minimumQuantity] = 0;
            return 0;
        }

        $query = "SELECT product_attribute_shop." . $db->quoteName('product_attribute_id') . " FROM " . $db->quoteName('#__jeproshop_product_attribute');
        $query .= " AS product_attribute " . JeproshopShopModelShop::addSqlAssociation('product_attribute');
        $query .= ($minimumQuantity > 0 ? JeproshopProductModelProduct::sqlStock('product_attribute') : "");
        $query .= " WHERE product_attribute_shop." . $db->quoteName('default_on') . " = 1 " . ($minimumQuantity > 0 ? " AND IFNULL(stock." . $db->quoteName('quantity') .  ", 0) >= " . (int)$minimumQuantity : "");
        $query .= " AND product_attribute." . $db->quoteName('product_id') . " = " . (int)$productId;

        $db->setQuery($query);
        $productAttribute = $db->loadObject();
        $result = (is_object($productAttribute) ? $productAttribute->product_attribute_id : 0);

        if (!$result) {
            $query = "SELECT product_attribute_shop." . $db->quoteName('product_attribute_id') . " FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
            $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute') . ($minimumQuantity > 0 ? JeproshopProductModelProduct::sqlStock('product_attribute') : "");
            $query .= " WHERE product_attribute." . $db->quoteName('product_id') . " = " . (int)$productId . ($minimumQuantity > 0 ? " AND IFNULL(stock." . $db->quoteName('quantity') .  ", 0) >= " . (int)$minimumQuantity : "");

            $db->setQuery($query);
            $productAttribute = $db->loadObject();
            $result = (is_object($productAttribute) ? $productAttribute->product_attribute_id : 0);
        }

        if (!$result) {
            $query = "SELECT product_attribute_shop." . $db->quoteName('product_attribute_id') . " FROM " ;
            $query .= $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
            $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute') . " WHERE product_attribute_shop.";
            $query .= $db->quoteName('default_on') . " = 1  AND product_attribute." . $db->quoteName('product_id') . " = " . (int)$productId;

            $db->setQuery($query);
            $productAttribute = $db->loadObject();
            $result = (is_object($productAttribute) ? $productAttribute->product_attribute_id : 0);
        }

        if (!$result) {
            $result = $resultNoFilter;
        }

        $combinations[$productId][$minimumQuantity] = $result;
        return $result;
    }

    /**
     * For a given product, returns its real quantity
     *
     * @param int $productId
     * @param int $productAttributeId
     * @param int $warehouseId
     * @param int $shopId
     * @return int realQuantity
     */
    public static function getRealQuantity($productId, $productAttributeId = 0, $warehouseId = 0, $shopId = null){
        static $manager = null;

        if (JeproshopSettingModelSetting::getValue('advanced_stock_management') && is_null($manager)) {
            $manager = JeproshopStockManagerFactory::getManager();
        }

        if (JeproshopSettingModelSetting::getValue('advanced_stock_management') && JeproshopProductModelProduct::usesAdvancedStockManagement($productId) &&
            JeproshopStockAvailableModelStockAvailable::dependsOnStock($productId, $shopId)) {
            return $manager->getProductRealQuantities($productId, $productAttributeId, $warehouseId, true);
        } else {
            return JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($productId, $productAttributeId, $shopId);
        }
    }

    /**
     * Get all available product attributes combinations
     *
     * @param int $langId Language id
     * @param bool $groupByIdAttributeGroup
     * @return array Product attributes combinations
     */
    public function getAttributeCombinations($langId, $groupByIdAttributeGroup = true)
    {
        if (!JeproshopCombinationModelCombination::isFeaturePublished()) {
            return array();
        }

        $db = JFactory::getDBO();
        $query = "SELECT product_attribute.*, product_attribute_shop.*, attribute_group." . $db->quoteName('attribute_group_id');
        $query .= ", attribute_group." . $db->quoteName('is_color_group') . ", attribute_group_lang." . $db->quoteName('name');
        $query .= " AS group_name, attribute_lang." . $db->quoteName('name') . " AS attribute_name, attribute." . $db->quoteName('attribute_id');
        $query .= " FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute " . JeproshopShopModelShop::addSqlAssociation('product_attribute');
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_combination') . " AS product_attribute_combination ON (product_attribute_combination.";
        $query .= $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . ") LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_attribute') . " AS attribute ON (attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination.";
        $query .= $db->quoteName('attribute_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group ON (attribute_group.";
        $query .= $db->quoteName('attribute_group_id') . " = attribute." . $db->quoteName('attribute_group_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang');
        $query .= " AS attribute_lang ON (attribute." . $db->quoteName('attribute_id') . " = attribute_lang." . $db->quoteName('attribute_id') . " AND attribute_lang.";
        $query .= $db->quoteName('lang_id') . " = " . (int)$langId . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ";
        $query .= " ON (attribute_group." . $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') ;
        $query .= " AND attribute_group_lang." . $db->quoteName('lang_id') . " = " .(int)$langId . ") WHERE product_attribute." . $db->quoteName('product_id') ;
        $query .= " = " . (int)$this->product_id . " GROUP BY product_attribute." . $db->quoteName('product_attribute_id') ;
        $query .= ($groupByIdAttributeGroup ? ", attribute_group." . $db->quoteName('attribute_group_id') : '') . " ORDER BY product_attribute.";
        $query .= $db->quoteName('product_attribute_id');

        $db->setQuery($query);

        $res = $db->loadObjectList();

        //Get quantity of each variations
        foreach ($res as $key => $row) {
            $cacheKey = $row->product_id.'_'.$row->product_attribute_id .'_quantity';
            if (!JeproshopCache::isStored($cacheKey)) {
                JeproshopCache::store(
                    $cacheKey,
                    JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($row->product_id, $row->product_attribute_id)
                );
            }

            $res[$key]->quantity = JeproshopCache::retrieve($cacheKey);
        }

        return $res;
    }

    public function add(){
        $context = JeproshopContext::getContext();
        $db = JFactory::getDBO();
        $data = JRequest::get('post');

        $informationData = $data['information'];

        $this->date_add = date('Y-m-d H:i:s');
        $this->date_upd = date('Y-m-d H:i:s');

        $shopListIds = array();
        if(JeproshopShopModelShop::isTableAssociated('product')){
            $shopListIds = JeproshopShopModelShop::getContextListShopIds();
            if(count($this->shop_list_id) > 0){ $shopListIds = $this->shop_list_id; }
        }

        if(JeproshopShopModelShop::checkDefaultShopId('product')){
            $this->default_shop_id = min($shopListIds);
        }

        $languages = JeproshopLanguageModelLanguage::getLanguages(false);

        $this->copyFromPost($data);

        $isVirtual = ($informationData['product_type'] == JeproshopProductModelProduct::VIRTUAL_PRODUCT) ? 1 : 0;

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_product') . "(" . $db->quoteName('supplier_id') . ", " . $db->quoteName('developer_id');
        $query .= ", " . $db->quoteName('manufacturer_id') . ", " . $db->quoteName('default_category_id') . ", " . $db->quoteName('default_shop_id') . ", ";
        $query .= $db->quoteName('tax_rules_group_id') . ", " . $db->quoteName('on_sale') . ", " . $db->quoteName('online_only') . ", " . $db->quoteName('ean13') . ", " . $db->quoteName('upc');
        $query .= ", " . $db->quoteName('isbn') . ", " . $db->quoteName('ecotax') . ", " . $db->quoteName('quantity') . ", " . $db->quoteName('minimal_quantity');
        $query .= ", " . $db->quoteName('price') . ", " . $db->quoteName('wholesale_price') . ", " . $db->quoteName('unity') . ", " . $db->quoteName('reference');
        $query .= ", " . $db->quoteName('unit_price_ratio') . ", " . $db->quoteName('additional_shipping_cost') . ", " . $db->quoteName('supplier_reference');
        $query .= ", " . $db->quoteName('location') . ", " . $db->quoteName('width') . ", " . $db->quoteName('height') . ", " . $db->quoteName('depth');
        $query .= ", " . $db->quoteName('weight') . ", " . $db->quoteName('out_of_stock') . ", " . $db->quoteName('quantity_discount') . ", ";
        $query .= $db->quoteName('customizable') . ", " . $db->quoteName('uploadable_files') . ", " . $db->quoteName('text_fields') . ", " ;
        $query .= $db->quoteName('published') . ", " . $db->quoteName('redirect_type') . ", " . $db->quoteName('product_redirected_id') . ", ";
        $query .= $db->quoteName('available_for_order') . ", " . $db->quoteName('available_date') . ", " . $db->quoteName('condition') . ", ";
        $query .= $db->quoteName('show_price') . ", " . $db->quoteName('indexed') . ", " . $db->quoteName('visibility') . ", " . $db->quoteName('cache_is_pack');
        $query .= ", " . $db->quoteName('cache_has_attachments') . "," . $db->quoteName('is_virtual') . ", " . $db->quoteName('cache_default_attribute') . ", ";
        $query .= $db->quoteName('date_add') . ", " . $db->quoteName('date_upd') . ", " . $db->quoteName('advanced_stock_management') ;
        $query .= " ) VALUES( ";
        $query .= (int)$this->supplier_id . ", " . (int)$this->developer_id . ", " . (int)$this->manufacturer_id . ", " . (int)$this->default_category_id;
        $query .= ", " . (int)$this->default_shop_id . ", " . (int)$this->tax_rules_group_id . ", " . ($this->on_sale ? 1 : 0) . ", " . ($this->online_only ? 1 : 0);
        $query .= ", " . $db->quote($this->ean13, true) . ", " . $db->quote($this->upc, true) . ", " . $db->quote($this->isbn, true) . ", " . (float)$this->ecotax;
        $query .= ", " . (int)$this->quantity . ", " . (int)$this->minimal_quantity . ", " . (float)$this->price . ", " . (float)$this->wholesale_price . ", ";
        $query .= $db->quote($this->unity) . ", " . $db->quote($this->reference) . ", " . (float)$this->unit_price_ratio . ", " . (float)$this->additional_shipping_cost;
        $query .= ", " . $db->quote($this->supplier_reference) . ", " . $db->quote($this->location) . ", " . (float)$this->width . ", " . (float)$this->height ;
        $query .= ", " . (float)$this->depth . ", " . (float)$this->weight . ", " . (int)$this->out_of_stock . ", " . (int)$this->quantity_discount . ", ";
        $query .= ($this->customizable ? 1 : 0) . ", " . (int)$this->uploadable_files . ", " . (int)$this->text_fields . ", " . ($this->published ? 1 : 0) . ", ";
        $query .= $db->quote($this->redirect_type, true) . ", " . (int)$this->product_redirected_id . ", " . ($this->available_for_order ? 1 : 0) . ", ";
        $query .= $db->quote($this->available_date) . ", " . $db->quote($this->condition) . ", " . ($this->show_price ? 1 : 0) . ", " . ($this->indexed ? 1 : 0);
        $query .= ", " . $db->quote($this->visibility) . ", " . ($this->cache_is_pack ? 1 : 0) . ", " . ($this->cache_has_attachments ? 1 : 0) . ", ";
        $query .= ($isVirtual ? 1 : 0) . ", " . ($this->cache_default_attribute ? 1 : 0) . ", " .  $db->quote($this->date_add) . ", " . $db->quote($this->date_upd) ;
        $query .= ", " . ($this->advanced_stock_management ? 1 : 0) ;
        $query .= ") ";

        $db->setQuery($query);

        if($db->query()){
            $this->product_id = $db->insertid();
            /** Update shop fields */
            if(JeproshopShopModelShop::isTableAssociated('product')){
                $result = true;
                foreach($shopListIds as $shopId) {
                    $query = "SELECT " . $db->quoteName('product_id') . " FROM " . $db->quoteName('#__jeproshop_product_shop') . " WHERE ";
                    $query .= $db->quoteName('product_id') . " = " . $this->product_id . " AND " . $db->quoteName('shop_id') . " = " . $shopId;
                    $db->setQuery($query);
                    $shopData = $db->loadObject();
                    $shopExists = (isset($shopData) ? ($shopData->product_id > 0) : false);

                    if ($shopExists) {
                        $result &= $this->updateProductShopData($shopId);
                    } else {
                        $result &= (bool)$this->insertProductShopData($shopId);
                    }

                }
            }
        }
        return true;
    }


    public function update(){
        $context = JeproshopContext::getContext();

        $data = JRequest::get('post');

        $existingProduct = $this;

        $informationData = $data['information'];
        $priceData = $data['price_field'];

        if(isset($priceData['ecotax'])){
            $ecoTaxWithoutTax = JeproshopTools::roundPrice($priceData['ecotax']/ (1 + JeproshopTaxModelTax::getProductEcotaxRate()/100), 6);
        }else{
            $ecoTaxWithoutTax = 0;
        }
        $this->ecotax = $ecoTaxWithoutTax;
        $productTypeBeforeUpdate = $this->getType();
        $this->indexed = 0;

        $this->copyFromPost($data);

        if(JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_SHOP){

        }

        if($context->shop->getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && !$this->isAssociatedToShop()){
            $isAssociatedToShop = false;
            $combinations = JeproshopProductModelProduct::getProductAttributesIds($this->product_id);
            if($combinations){
                foreach($combinations as $combinationId){
                    $combination = new JeproshopCombinationModelCombination($combinationId);
                    $defaultCombination = new JeproshopCombinationModelCombination($combinationId, $this->default_shop_id);

                    $combination->product_id = $defaultCombination->product_id;
                    $combination->location = $defaultCombination->location;
                    $combination->ean13 = $defaultCombination->ean13;
                    $combination->isbn = $defaultCombination->isbn;
                    $combination->upc = $defaultCombination->upc;
                    $combination->quantity = $defaultCombination->quantity;
                    $combination->reference = $defaultCombination->reference;
                    $combination->supplier_reference = $defaultCombination->supplier_reference;

                    $combination->wholesale_price = $defaultCombination->wholesale_price;
                    $combination->price = $defaultCombination->price;
                    $combination->ecotax = $defaultCombination->ecotax;
                    $combination->weight = $defaultCombination->weight;
                    $combination->unit_price_impact = $defaultCombination->unit_price_impact;
                    $combination->minimal_quantity = $defaultCombination->minimal_quantity;
                    $combination->default_on = $defaultCombination->default_on;
                    $combination->available_date = $defaultCombination->available_date;
                    $combination->save();
                }
            }
        }else{
            $isAssociatedToShop = true;
        }

        $db = JFactory::getDBO();

        /*** updating data for the object **/
        $this->clearCache('product', $this->product_id);

        $this->date_upd = date('Y-m-d H:i:s');

        if($this->date_add == null){ $this->date_add = date('Y-m-d H:is'); }

        $shopListIds = JeproshopShopModelShop::getContextListShopIds();
        if(count($shopListIds) > 0){ $shopListIds = $this->shop_list_id; }

        if(!$this->default_shop_id){
            $this->default_shop_id = (in_array(JeproshopSettingModelSetting::getValue('default_shop'), $shopListIds) == true) ?
                JeproshopSettingModelSetting::getValue('default_shop') : min($shopListIds);
        }

        /** update database */
        $query = "UPDATE " . $db->quoteName('#__jeproshop_product') . " SET " . $db->quoteName('ean13') . " = " . $db->quote($this->ean13);
        $query .= ", " . $db->quoteName('upc') . " = " . $db->quote($this->upc) . ", " . $db->quoteName('isbn') . " = " . $db->quote($this->isbn);
        $query .= ", " . $db->quoteName('published') . " = " . ($this->published ? 1 : 0) . ", " . $db->quoteName('redirect_type')  . " = ";
        $query .= $db->quote($this->redirect_type) . ", " . $db->quoteName('product_redirected_id') . " = " . (int)$this->product_redirected_id;
        $query .= ", " . $db->quoteName('visibility') . " = " . $db->quote($this->visibility) . ", " . $db->quoteName('available_for_order');
        $query .= " = " . ($this->available_for_order ? 1 : 0) . ", " . $db->quoteName('show_price') . " = " . ($this->show_price ? 1 : 0) . ", ";
        $query .= $db->quoteName('online_only') . " = " . ($this->online_only ? 1 : 0) . ", " . $db->quoteName('condition') . " = " ;
        $query .= $db->quote($this->condition) . ", " . $db->quoteName('default_shop_id') . " = " . (int)$this->default_shop_id . ", ";
        $query .= $db->quoteName('supplier_id') . " = " . (int)$this->supplier_id . ", " . $db->quoteName('manufacturer_id') . " = ";
        $query .= (int)$this->manufacturer_id . ", " . $db->quoteName('developer_id') . " = " . (int)$this->developer_id . ", ";
        $query .= $db->quoteName('default_category_id') . " = " . (int)$this->default_category_id . ", " . $db->quoteName('on_sale') . " = ";
        $query .= ($this->on_sale ? 1 : 0) .", " . $db->quoteName('tax_rules_group_id') . " = " . (int)$this->tax_rules_group_id . ", ";
        $query .= $db->quoteName('isbn') . " = " . $db->quote($this->isbn) . ", " . $db->quoteName('ecotax') . " = " . (float)$this->ecotax . ", ";
        $query .= $db->quoteName('quantity') . " = " . (int)$this->quantity . ", " . $db->quoteName('minimal_quantity') . " = " ;
        $query .= (int)$this->minimal_quantity . ", " . $db->quoteName('price') . " = " . (float)$this->price . ", " . $db->quoteName('unit_price_ratio');
        $query .= " = " . (float)$this->unit_price_ratio . ", " . $db->quoteName('additional_shipping_cost') . " = " . (float)$this->additional_shipping_cost;
        $query .= ", " . $db->quoteName('reference') . " = " . $db->quote($this->reference) . ", " . $db->quoteName('supplier_reference') . " = ";
        $query .= $db->quote($this->supplier_reference) . ", " . $db->quoteName('location') . " = " . $db->quote($this->location) . ", ";
        $query .= $db->quoteName('width') . " = " . (float)$this->width . ", " . $db->quoteName('height') . " = " . (float)$this->height . ", ";
        $query .= $db->quoteName('depth') . " = " . (float)$this->depth . ", " . $db->quoteName('weight') . " = " . (float)$this->weight . ", ";
        $query .= $db->quoteName('out_of_stock') . " = " . ($this->out_of_stock ? 1 : 0) . ", " . $db->quoteName('quantity_discount') . " = ";
        $query .= (int)$this->quantity_discount . ", " . $db->quoteName('customizable') . " = " . ($this->customizable ? 1 : 0) . ", ";
        $query .= $db->quoteName('uploadable_files') . " = " . (int)$this->uploadable_files . ", " . $db->quoteName('text_fields') . " = " . (int)$this->text_fields . ", ";
        $query .= $db->quoteName('available_date') . " = " . $db->quote($this->available_date) . ", " . $db->quoteName('cache_is_pack') . " = ";
        $query .= ($this->cache_is_pack ? 1 : 0) . ", " . $db->quoteName('cache_has_attachments') . " = " . ($this->cache_has_attachments ? 1 : 0);
        $query .= ", " . $db->quoteName('date_add') . " = " . $db->quote($this->date_add) . ", " . $db->quoteName('date_upd') . " = ";
        $query .= $db->quote($this->date_upd) . ", " . $db->quoteName('advanced_stock_management') . " = " . ($this->advanced_stock_management ? 1 : 0);
        $query .= " WHERE " . $db->quoteName('product_id') . " = " . (int)$this->product_id;

        $db->setQuery($query);

        if($db->query()){
            /** Update shop fields */
            if(JeproshopShopModelShop::isTableAssociated('product')){
                $result = true;
                foreach($shopListIds as $shopId){
                    $query = "SELECT " . $db->quoteName('product_id') . " FROM " . $db->quoteName('#__jeproshop_product_shop') . " WHERE " ;
                    $query .= $db->quoteName('product_id') . " = " . $this->product_id . " AND " . $db->quoteName('shop_id') . " = " . $shopId;
                    $db->setQuery($query);
                    $shopData = $db->loadObject();
                    $shopExists = (isset($shopData) ? ($shopData->product_id > 0) : false);

                    if($shopExists){
                        $result &= $this->updateProductShopData($shopId);
                    }else{
                        $result &= $this->insertProductShopData($shopId);
                    }

                    $result &= $this->setProductLanguageInformation($shopId);
                }
            }

        }

        $this->setGroupReduction();
        /** synchronizing stock reference **/
        if(JeproshopSettingModelSetting::getValue('advanced_stock_management') && JeproshopStockAvailableModelStockAvailable::dependsOnStock($this->product_id, $context->shop->shop_id)){
            $query = "UPDATE " . $db->quoteName('#__jeproshop_stock') . " SET " . $db->quoteName('reference') . " = " . $db->quote($this->reference);
            $query .= ", " . $db->quoteName('ean13') . " = " . $db->quote($this->ean13) . ", " . $db->quoteName('isbn') . " = " . $db->quote($this->isbn);
            $query .= ", " . $db->quoteName('upc') . " = " . $db->quote($this->upc) . " WHERE "  . $db->quoteName('product_id') . " = " . $this->product_id;
            $query .= " AND " . $db->quoteName('product_attribute_id') . " = 0";

            $db->setQuery($query);
            $db->query();
        }

        if($this->getType() == JeproshopProductModelProduct::VIRTUAL_PRODUCT && $this->published && !JeproshopSettingModelSetting::getValue('virtual_product_feature_active')){
            JeproshopSettingModelSetting::updateValue('virtual_product_feature_active', 1);
        }

        if(JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && !$existingProduct->isAssociatedToShop($context->shop->shop_id)){
            $outOfStock = JeproshopStockAvailableModelStockAvailable::outOfStock($existingProduct->product_id, $existingProduct->default_shop_id);
            $dependsOnStock = JeproshopStockAvailableModelStockAvailable::dependsOnStock($existingProduct->product_id, $existingProduct->default_shop_id);
            JeproshopStockAvailableModelStockAvailable::setProductOutOfStock((int)$this->product_id, $outOfStock, $context->shop->shop_id);
            JeproshopStockAvailableModelStockAvailable::setProductDependsOnStock((int)$this->product_id, $dependsOnStock, $context->shop->shop_id);
        }

        if (in_array($context->shop->getShopContext(), array(JeproshopShopModelShop::CONTEXT_SHOP, JeproshopShopModelShop::CONTEXT_ALL))) {
            if(isset($data['shipping'])) {
                $this->setCarriers($data['shipping']);
            }
            if(isset($data['association'])) {
                $this->updateAccessories($data['association']);
            }
            if (isset($data['supplier'])) {
                $this->updateSuppliers($data['supplier']);
            }
            if (isset($data['feature'])) {
                //todo analyze $this->updateFeatures($data['feature']);
            }
            if (isset($data['declination'])) {
                $this->updateProductAttribute($data['declination']);
            }
            if (isset($data['price_field'])) {
                $this->updatePriceAddition($data['price_field']);
                $this->updateSpecificPricePriorities($data['price_field']);
            }
            if (isset($data['customization'])) {
                $this->updateCustomizationConfiguration($data['customization']);
            }
            if (isset($data['attachment'])) {
                $this->updateAttachments($data['attachment']);
            }
            if (isset($data['images'])) {
                $this->updateImageLegends($data['images']);
            }

            $this->updatePackItems();
            // Disable advanced stock management if the product become a pack
            if ($productTypeBeforeUpdate == JeproshopProductModelProduct::SIMPLE_PRODUCT && $this->getType() == JeproshopProductModelProduct::PACKAGE_PRODUCT) {
                JeproshopStockAvailableModelStockAvailable::setProductDependsOnStock((int)$this->product_id, false);
            }
            $this->updateDownloadProduct(1);
            $this->updateTags();

            $categoryBox = (isset($data['association']['category_box']) ? $data['association']['category_box'] : null);

            if (isset($categoryBox) && !$this->updateCategories($categoryBox)) {
                JeproshopTools::raiseError(500, 'An error occurred while linking the  <b> product object</b>');
                JeproshopTools::raiseError(500, 'To categories');
            }
        }

        if (isset($data['warehouses'])) {
            $this->updateWarehouses($data['warehouses']);
        }
        return true;
    }

    private function updateProductShopData($shopId){
        $db = JFactory::getDBO();

        $query = "UPDATE " . $db->quoteName('#__jeproshop_product_shop') . " SET " . $db->quoteName('on_sale') . "= ";
        $query .= ($this->on_sale ? 1 : 0) . ", " . $db->quoteName('online_only') . " = " . ($this->online_only ? 1 : 0);
        $query .= ", " . $db->quoteName('tax_rules_group_id') . " = " . (int)$this->tax_rules_group_id .  $db->quoteName('ecotax');
        $query .= " = " . (float)$this->ecotax . ", " . $db->quoteName('default_category_id') . " = " . (int)$this->default_category_id;
        $query .= ", " . $db->quoteName('minimal_quantity') . " = " . (int)$this->minimal_quantity . ", " . $db->quoteName('price');
        $query .= " = " . $this->price . ", ". $db->quoteName('wholesale_price') . " = " . (float)$this->wholesale_price . ", " ;
        $query .= $db->quoteName('unity') . " = " . $db->quote($this->unity) . ", " . $db->quoteName('unit_price_ratio') . " = ";
        $query .= (float)$this->unit_price_ratio . ", " . $db->quoteName('additional_price_ratio') . " = " . (float)$this->additional_shipping_cost;
        $query .= ", " . $db->quoteName('customizable') . " = " . ($this->customizable ? 1 : 0) . ", " . $db->quoteName('uploadable_files') ;
        $query .= " = " . (int)$this->uploadable_files . ", " . $db->quoteName('text_files') . " = " . (int)$this->text_fields . ", ";
        $query .= $db->quoteName('published') . " = " . ($this->published ? 1 : 0) . " = " . $db->quoteName('redirected_type');
        $query .= " = " . $db->quote($this->redirect_type) . ", " . $db->quoteName('product_redirected_id') . " = " . (int)$this->product_redirected_id;
        $query .= ", " . $db->quoteName('available_for_order') . " = " . ($this->available_for_order ? 1 : 0) . ", " . $db->quoteName('available_date');
        $query .= ", " . $db->quoteName('condition') . " = " . $db->quote($this->condition) . ", " . $db->quoteName('show_price') . " = ";
        $query .= ($this->show_price ? 1 : 0) . ", " . $db->quoteName('indexed') . " = " . ($this->indexed ? 1 : 0) . ", " . $db->quoteName('visibility');
        $query .= " = " . $db->quote($this->visibility) . ", " . $db->quoteName('cache_default_attribute') . " = " . ($this->cache_default_attribute ? 1: 0);
        $query .= ", " . $db->quoteName('advanced_stock_management') . " = " . ($this->advanced_stock_management ? 1 : 0) . ", " . $db->quoteName('date_add');
        $query .= " = " . $db->quote($this->date_add) . ", " . $db->quoteName('date_upd') . " = " . $db->quote($this->date_upd) ;
        $query .=" WHERE " . $db->quoteName('product_id') . " = " . (int)$this->product_id . " AND " . $db->quoteName('shop_id') . " = " . $shopId;

        $db->setQuery($query);
        return $db->query();
    }

    public function insertProductShopData($shopId){
        $db = JFactory::getDBO();

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_product_shop') . "(" . $db->quoteName('product_id') . ", ";
        $query .= $db->quoteName('shop_id') . ", " . $db->quoteName('default_category_id')  . ", " . $db->quoteName('tax_rules_group_id');
        $query .= ", " . $db->quoteName('on_sale') . ", " . $db->quoteName('online_only') . ", " . $db->quoteName('ecotax') . ", ";
        $query .= $db->quoteName('minimal_quantity') . ", " . $db->quoteName('price') . ", " . $db->quoteName('wholesale_price') . ", ";
        $query .= $db->quoteName('unity') . ", " . $db->quoteName('unit_price_ratio') . ", " . $db->quoteName('additional_shipping_cost');
        $query .= ", " . $db->quoteName('customizable')  . ", " . $db->quoteName('text_fields') . ", " . $db->quoteName('uploadable_files');
        $query .= ", " . $db->quoteName('published') . ", " . $db->quoteName('redirect_type') . ", " . $db->quoteName('product_redirected_id');
        $query .= ", " . $db->quoteName('available_for_order') . ", " . $db->quoteName('available_date') . ", " . $db->quoteName('condition');
        $query .= ", " . $db->quoteName('indexed') . ", " . $db->quoteName('visibility') . ", " . $db->quoteName('cache_default_attribute');
        $query .= ", " . $db->quoteName('advanced_stock_management') . ", " . $db->quoteName('date_add') . ", " . $db->quoteName('date_upd');
        $query .= ") VALUES (" . (int)$this->product_id . ", " . (int)$shopId . ", " . (int)$this->default_category_id . ", ";
        $query .= (int)$this->tax_rules_group_id . ", " . ($this->on_sale ? 1 : 0) . ", " . ($this->online_only ? 1 : 0) . ", ";
        $query .= (float)$this->ecotax . ", " . (int)$this->minimal_quantity . ", " . (float)$this->price . ", " . (float)$this->wholesale_price;
        $query .= ", " . $db->quote($this->unity) . ", " . (float)$this->unit_price_ratio . ", " . (float)$this->additional_shipping_cost;
        $query .= ", " . ($this->customizable ? 1 : 0) . ", " . (int)$this->text_fields . ", " . (int)$this->uploadable_files . ", " ;
        $query .= ($this->published ? 1 : 0) . ", " . $db->quote($this->redirect_type) . ", " . (int)$this->product_redirected_id . ", ";
        $query .= ($this->available_for_order ? 1 : 0) . ", " . $db->quote($this->available_date) . ", " . $db->quote($this->condition) . ", ";
        $query .= (int)$this->indexed . ", " . $db->quote($this->visibility) . ", " . ($this->cache_default_attribute ? 1 : 0) . ", " ;
        $query .= ($this->advanced_stock_management ? 1 : 0) . ", " . $db->quote($this->date_add) . ", " . $db->quote($this->date_upd) . ") ";

        $db->setQuery($query);
        return $db->query();
    }

    private function setProductLanguageInformation($shopId){
        $db = JFactory::getDBO();
        $data = JRequest::get('post');
        $informationData = $data['information'];
        $result = true;
        foreach(JeproshopLanguageModelLanguage::getLanguages(true) as $language){
            $whereClause = $db->quoteName('product_id') . " = " . $this->product_id . " AND " . $db->quoteName('lang_id') . " = ";
            $whereClause .= (int)$language->lang_id . " AND " . $db->quoteName('shop_id') . " = " . $shopId;

            $query = "SELECT COUNT(*) AS langs FROM " . $db->quoteName('#__jeproshop_product_lang') . " WHERE " . $whereClause;

            $db->setQuery($query);
            $langData = $db->loadObject();

            if(isset($langData) && $langData->langs > 0){
                $query = "UPDATE " . $db->quoteName('#__jeproshop_product_lang') . " SET " . $db->quoteName('description') . " = ";
                $query .= $db->quote($informationData['description_' . $language->lang_id]) . ", " . $db->quoteName('short_description');
                $query .= " = " . $db->quote($informationData['short_description_' . $language->lang_id]) . ", " . $db->quoteName('name');
                $query .= " = " . $db->quote($informationData['name_' . $language->lang_id]) . " WHERE " . $db->quoteName('product_id');
                $query .= " = " . (int)$this->product_id . " AND " . $db->quoteName('shop_id') . " = " . (int)$shopId . " AND ";
                $query .= $db->quoteName('lang_id') . " = " . (int)$language->lang_id;
            }else{
                $query = "INSERT INTO " . $db->quoteName('#__jeproshop_product_lang') . "(" . $db->quoteName('product_id') . ", ";
                $query .= $db->quoteName('shop_id') . ", " . $db->quoteName('lang_id') . ", "  . $db->quoteName('description');
                $query .= ", " . $db->quoteName('short_description') . ", " . $db->quoteName('link_rewrite') . ", " ;
                $query .= $db->quoteName('meta_description') . ", " . $db->quoteName('meta_keywords') . ", "  . $db->quoteName('meta_title');
                $query .= ", " . $db->quoteName('name') . ", " . $db->quoteName('available_now') . ", " . $db->quoteName('available_later');
                $query .= ") VALUES (" . (int)$this->product_id . ", " . (int)$this->shop_id . ", " . (int)$this->lang_id . ", ";
                $query .= $db->quote($informationData['description_' . $language->lang_id]) . ", " . $db->quote($informationData['short_description_' . $language->lang_id]);
                $query .= ", " . (isset($metaData['link_rewrite_' . $language->lang_id]) ? $db->quote($metaData['link_rewrite_' . $language->lang_id]) : '') ;
                $query .= ", " . (isset($metaData['meta_description_' . $language->lang_id]) ? $db->quote($metaData['meta_description_' . $language->lang_id]) : '');
                $query .= ", " . (isset($metaData['meta_keywords_' . $language->lang_id]) ? $db->quote($metaData['meta_keywords_' . $language->lang_id]) : '');
                $query .= ", " . (isset($metaData['meta_title_' . $language->lang_id]) ? $db->quote($metaData['meta_title_' . $language->lang_id]) : '');
                $query .= ", " . (isset($informationData['name_' . $language->lang_id]) ? $db->quote($informationData['name_' . $language->lang_id]) : '');
                $query .= ", " . $db->quote('') . ", " . $db->quote('') . ")";
            }

            $db->setQuery($query);
            $result &= $db->query();
        }
        return $result;
    }

    /**
     * Set Group reduction if needed
     */
    public function setGroupReduction(){
        return JeproshopGroupReductionModelGroupReduction::setProductReduction($this->product_id, null, $this->default_category_id);
    }

    public function updateFeatures($featureData = null)    {
        if (!JeproshopFeatureModelFeature::isFeaturePublished()) {
            return;
        }

        if($this->product_id <=  0) { return; }

        if(isset($featureData) || $featureData == null){
            $request = JRequest::get('post');
            $featureData = $request['feature'];
        }


        // delete all objects
        $this->deleteFeatures();

        // add new objects
        $languages = JeproshopLanguageModelLanguage::getLanguages(false);
        foreach ($featureData as $key => $val) {
            if (preg_match('/^feature_([0-9]+)_value/i', $key, $match)) {
                if ($val) {
                    $this->addFeatures($match[1], $val);
                } else {
                    if ($defaultValue = $this->checkFeatures($languages, $match[1])) {
                        $valueId = $this->addFeatures($match[1], 0, 1);
                        foreach ($languages as $language) {
                            if ($customData = $featureData['custom_' . $match[1] . '_' . (int)$language->lang_id]){
                                $this->addCustomFeatures($valueId, (int)$language->lang_id, $customData);
                            }else {
                                $this->addCustomFeatures($valueId, (int)$language->lang_id, $defaultValue);
                            }
                        }
                    }
                }
            }
        }
        /*} else {
            $this->errors[] = Tools::displayError('A product must be created before adding features.');
        }*/
    }

    /**
     * Checking customs feature
     * @param $languages
     * @param $featureId
     * @return int
     */
    protected function checkFeatures($languages, $featureId){
        $rules = call_user_func(array('JeproshopFeatureValueModelFeatureValue', 'getValidationRules'), 'FeatureValue');
        $feature = JeproshopFeatureModelFeature::getFeature((int)JeproshopSettingModelSetting::getValue('default_lang'), $featureId);

        $request = JRequest::get('post');
        $featureData = $request['feature'];
        foreach ($languages as $language) {
            if (isset($featureData['custom_'.$featureId.'_'.$language->lang_id])){
                $val = $featureData['custom_'.$featureId.'_'.$language->lang_id];
                $currentLanguage = new JeproshopLanguageModelLanguage($language->lang_id);
                if (JeproshopTools::strlen($val) > $rules['sizeLang']['value']) {
                    /*$this->errors[] = sprintf(
                        Tools::displayError('The name for feature %1$s is too long in %2$s.'),
                        ' <b>'.$feature['name'].'</b>',
                        $currentLanguage->name
                    ); */
                } elseif (!call_user_func(array('Validate', $rules['validateLang']['value']), $val)) {
                    /*$this->errors[] = sprintf(
                        Tools::displayError('A valid name required for feature. %1$s in %2$s.'),
                        ' <b>'.$feature['name'].'</b>',
                        $currentLanguage->name
                    );*/
                }
                if (count($this->errors)) {
                    return 0;
                }
                // Getting default language
                if ($language->lang_id == (int)JeproshopSettingModelSetting::getValue('default_lang')) {
                    return $val;
                }
            }
        }
        return 0;
    }


    /**
     * Add new feature to product
     * @param $valueId
     * @param $langId
     * @param $custom
     * @return
     */
    public function addCustomFeatures($valueId, $langId, $custom){
        $db = JFactory::getDBO();

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_feature_value_lang') . "(" . $db->quoteName('feature_value_id') . ", ";
        $query .= $db->quoteName('lang_id') . ", " . $db->quoteName('custom') . ") VALUES  (" . (int)$valueId . ", " . (int)$langId;
        $query .= ", " . ($custom ? 1 : 0) . ")";

        $db->setQuery($query);
        return $db->query();
    }

    public function addFeatures($featureId, $valueId, $custom = 0){
        $db = JFactory::getDBO();
        if ($custom){
            $query = "INSERT INTO " . $db->quoteName('#__jeproshop_feature_value') . "(" . $db->quoteName('feature_value_id') . ", ";
            $query .= $db->quoteName('custom') . ") VALUES (" . (int)$featureId . ", 1)";

            $db->setQuery($query);
            $db->query();
            $valueId = $db->insertId();
        }
        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_feature_product') . "(" . $db->quoteName('feature_id') . ", ";
        $query .= $db->quoteName('feature_value_id') . ", " . $db->quoteName('product_id') . ") VALUES (" . (int)$featureId ;
        $query .= ", " . (int) $valueId . ", " . (int)$this->product_id . ")";

        $db->setQuery($query);
        $db->query();
        JeproshopSpecificPriceRuleModelSpecificPriceRule::applyAllRules(array((int)$this->product_id));
        if ($valueId) {
            return ($valueId);
        }
    }

    /**
     * Delete features
     *
     */
    public function deleteFeatures(){
        $db = JFactory::getDBO();
        // List products features
        $query = "SELECT product.*, feature.* FROM " . $db->quoteName('#__jeproshop_feature_product') . " AS product LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_feature_value') . " AS feature ON (feature." . $db->quoteName('feature_value_id');
        $query .= " = product." . $db->quoteName('feature_value_id') . ") WHERE " . $db->quoteName('product_id') . " = " .(int)$this->product_id;

        $db->setQuery($query);
        $features = $db->loadObjectList();

        foreach ($features as $feature) {
            // Delete product custom features
            if ($feature->custom){
                $query = "DELETE FROM " . $db->quoteName('#__jeproshop_feature_value') . " WHERE " . $db->quoteName('feature_value_id') . " = " . (int)$feature->feature_value_id;

                $db->setQuery($query);
                $db->query();

                $query = "DELETE FROM " . $db->quoteName('#__jeproshop_feature_value_lang') . " WHERE " . $db->quoteName('feature_value_id') . " = " . (int)$feature->feature_value_id;

                $db->setQuery($query);
                $db->query();
            }
        }
        // Delete product features
        $query = "DELETE FROM " . $db->quoteName('#__jeproshop_feature_product') . " WHERE " . $db->quoteName('product_id') . " = " .(int)$this->product_id;

        $db->setQuery($query);
        $result = $db->query();

        JeproshopSpecificPriceRuleModelSpecificPriceRule::applyAllRules(array((int)$this->product_id));
        return ($result);
    }


    /**
     * Delete product accessories.
     * Wrapper to static method deleteAccessories($product_id).
     *
     * @return mixed Deletion result
     */
    public function deleteAccessories(){
        $db = JFactory::getDBO();

        $query = "DELETE FROM " . $db->quoteName('#__jeproshop_accessory') . " WHERE " . $db->quoteName('product_1_id') . " = " . $this->product_id;

        $db->setQuery($query);
        return $db->query();
    }


    public function setCarriers($shippingData = null){
        if($this->product_id == null || $this->product_id <= 0){ return; }

        if(JeproshopTools::isLoadedObject($this, 'product_id')){
            if(!isset($shippingData)){
                $request = JRequest::get('post');
                $shippingData = $request['shipping'];
            }

            if(isset($shippingData['selected_carrier'])){
                /** cleaning previous data**/
                $db = JFactory::getDBO();

                $query = "DELETE FROM " . $db->quoteName('#__jeproshop_product_carrier') . " WHERE " . $db->quoteName('product_id');
                $query .= " = " . $this->product_id . " AND " . $db->quoteName('shop_id') . " = " . (int)$this->shop_id;

                $db->setQuery($query);
                $db->query();

                $uniqueArray = array();
                foreach($shippingData['selected_carrier'] as $carrierId){
                    if(!in_array($carrierId, $uniqueArray)){
                        $uniqueArray[] =$carrierId;
                        $query = "INSERT INTO  " . $db->quoteName('#__jeproshop_product_carrier') . "(" . $db->quoteName('product_id') . ", ";
                        $query .= $db->quoteName('carrier_reference_id') . ", " . $db->quoteName('shop_id') . ") VALUES (" . (int)$this->product_id;
                        $query .= ", " . (int)$this->shop_id . ", " . (int)$carrierId . ")";

                        $db->setQuery($query);
                        $db->query();
                    }
                }
            }
        }
    }

    /**
     * Post treatment for suppliers
     * @param $supplierData
     */
    public function updateSuppliers($supplierData){
        if($this->product_id == null || $this->product_id <= 0){ return; }
        
        $request = JRequest::get('post');

        if(isset($request['supplier_loaded']) && (int)$request['supplier_loaded'] === 1 ) {
            // Get all product_attribute_id
            $context = JeproshopContext::getContext();
            $attributes = $this->getAttributesResume($context->language->lang_id);
            if (empty($attributes)) {
                $attributes[] = array(
                    'product_attribute_id' => 0,
                    'attribute_designation' => ''
                );
            }

            // Get all available suppliers
            $suppliers = JeproshopSupplierModelSupplier::getSuppliers();

            // Get already associated suppliers
            $associatedSuppliers = JeproshopProductSupplierModelProductSupplier::getSuppliers($this->product_id);

            $suppliersToAssociate = array();
            $newDefaultSupplierId = 0;

            if ($supplierData['default_supplier']) {
                $newDefaultSupplierId = (int)$supplierData['default_supplier'];
            }

            // Get new associations
            foreach ($suppliers as $supplier) {
                if (isset($supplierData['check_supplier_'.$supplier->supplier_id])) {
                    $suppliersToAssociate[] = $supplier->supplier_id;
                }
            }

            // Delete already associated suppliers if needed
            foreach ($associatedSuppliers as $key => $associatedSupplier) {
                /** @var JeproshopProductSupplierModelProductSupplier $associatedSupplier */
                if (!in_array($associatedSupplier->supplier_id, $suppliersToAssociate)) {
                    $associatedSupplier->delete();
                    unset($associatedSuppliers[$key]);
                }
            }

            // Associate suppliers
            foreach ($suppliersToAssociate as $id) {
                $toAdd = true;
                foreach ($associatedSuppliers as $as) {
                    /** @var JeproshopProductSupplierModelProductSupplier $as */
                    if ($id == $as->supplier_id) {
                        $toAdd = false;
                    }
                }

                if ($toAdd) {
                    $productSupplier = new JeproshopProductSupplierModelProductSupplier();
                    $productSupplier->product_id = $this->product_id;
                    $productSupplier->product_attribute_id = 0;
                    $productSupplier->supplier_id = $id;
                    if ($context->currency->currency_id) {
                        $productSupplier->currency_id = (int)$context->currency->currency_id;
                    } else {
                        $productSupplier->currency_id = (int)JeproshopSettingModelSetting::getValue('default_currency');
                    }
                    $productSupplier->save(false);

                    $associatedSuppliers[] = $productSupplier;
                    foreach ($attributes as $attribute) {
                        if ((int)$attribute->product_attribute_id > 0) {
                            $productSupplier = new JeproshopProductSupplierModelProductSupplier();
                            $productSupplier->product_id = $this->product_id;
                            $productSupplier->product_attribute_id = (int)$attribute->product_attribute_id;
                            $productSupplier->supplier_id = $id;
                            $productSupplier->save(false);
                        }
                    }
                }
            }

            // Manage references and prices
            foreach ($attributes as $attribute) {
                $db = JFactory::getDBO();
                foreach ($associatedSuppliers as $supplier) {
                    /** @var JeproshopProductSupplierModelProductSupplier $supplier */
                    if (isset($supplierData['supplier_reference_' . $this->product_id . '_' . $attribute->product_attribute_id .'_'.$supplier->supplier_id]) ||
                        (isset($supplierData['product_price_' . $this->product_id . '_' . $attribute->product_attribute_id . '_' . $supplier->supplier_id]) &&
                            isset($supplierData['product_price_currency_' . $this->product_id . '_' .$attribute->product_attribute_id .'_'.$supplier->supplier_id]))) {
                        $reference =
                            isset($supplierData['supplier_reference_'.$this->product_id . '_' . $attribute->product_attribute_id . '_' . $supplier->supplier_id]) ?
                            $supplierData['supplier_reference_'.$this->product_id . '_' . $attribute->product_attribute_id . '_' . $supplier->supplier_id] :
                                '';
                        $price = isset($supplierData['product_price_'.$this->product_id.'_'.$attribute->product_attribute_id.'_'.$supplier->supplier_id]) ?
                            $supplierData['product_price_'.$this->product_id.'_'.$attribute->product_attribute_id.'_'.$supplier->supplier_id] : 0.00;

                        $price = (float)str_replace(
                            array(' ', ','),
                            array('', '.'),
                            $price
                        );

                        $price = JeproshopTools::roundPrice($price, 6);

                        $currencyId = (int)
                            isset($supplierData['product_price_currency_'.$this->product_id.'_'.$attribute->product_attribute_id.'_'.$supplier->supplier_id]) ?
                            $supplierData['product_price_currency_'.$this->product_id.'_'.$attribute->product_attribute_id.'_'.$supplier->supplier_id] :
                            0
                        ;

                        if ($currencyId <= 0 || (!($result = JeproshopCurrencyModelCurrency::getCurrencyInstance($currencyId)) || empty($result))) {
                            JError::raiseError(500, 'The selected currency is not valid');
                        }

                        // Save product-supplier data
                        $productSupplierId = (int)JeproshopProductSupplierModelProductSupplier::getProductSupplierIdByProductAndSupplier($this->product_id, $attribute->product_attribute_id, $supplier->supplier_id);

                        if (!$productSupplierId) {
                            $this->addSupplierReference($supplier->supplier_id, (int)$attribute->product_attribute_id, $reference, (float)$price, (int)$currencyId);
                            if ($this->supplier_id == $supplier->supplier_id) {
                                $this->supplier_reference = $reference;
                                $this->wholesale_price = (float)JeproshopTools::convertPrice($price, $currencyId);

                                $query = "UPDATE " . $db->quoteName('#__jeproshop_product_attribute') . " SET " . $db->quoteName('supplier_reference');
                                $query .= " = " . $db->quote($reference) . ", " . $db->quoteName('wholesale_price') . " = ". $this->wholesale_price;
                                $query .= " WHERE " . $db->quoteName('product_id') . " = " . (int)$this->product_id ;
                                if ((int)$attribute->product_attribute_id > 0) {
                                    $query .= " AND " . $db->quoteName('product_attribute_id') . " = " . (int)$attribute->product_attribute_id;
                                }
                                
                                $db->setQuery($query);
                                $db->query();
                            }
                        } else {
                            $productSupplier = new JeproshopProductSupplierModelProductSupplier($productSupplierId);
                            $productSupplier->currency_id = (int)$currencyId;
                            $productSupplier->product_supplier_price_tax_excluded = (float)$price;
                            $productSupplier->product_supplier_reference = $reference;
                            $productSupplier->update(false);
                        }
                    } elseif (isset($supplierData['supplier_reference_'.$this->product_id.'_'.$attribute->product_attribute_id.'_'.$supplier->supplier_id])) {
                        //int attribute with default values if possible
                        if ((int)$attribute->product_attribute_id > 0) {
                            $productSupplier = new JeproshopProductSupplierModelProductSupplier();
                            $productSupplier->product_id = $this->product_id;
                            $productSupplier->product_attribute_id = (int)$attribute->product_attribute_id;
                            $productSupplier->supplier_id = $supplier->supplier_id;
                            $productSupplier->save(false);
                        }
                    }
                }
            }
            // Manage default supplier for product
            if ($newDefaultSupplierId != $this->supplier_id) {
                $this->supplier_id = $newDefaultSupplierId;
                $query = "UPDATE " . $db->quoteName('#__jeproshop_product') . " SET " . $db->quoteName('supplier_id') . " = ";
                $query .= (int)$this->supplier_id . " WHERE " . $db->quoteName('product_id') . " = " . (int)$this->supplier_id;

                $db->setQuery($query);
                $db->query();
            }
        }
    }

    /**
     * Update product accessories
     *
     * @param $accessories
     */
    public function updateAccessories($accessories = null){
        $this->deleteAccessories();

        if(!isset($accessories)){
            $request = JRequest::get('post');
            if(isset($request['association'])){
                $accessories = $request['association'];
            }
        }
        if(isset($accessories)) {
            $accessoriesIds = array_unique(explode('_', $accessories));
            if (count($accessoriesIds)) {
                array_pop($accessoriesIds);
                JeproshopProductModelProduct::updateProductAccessories($accessoriesIds, $this->product_id);
            }
        }
    }

    public function copyFromPost($data = null){

        if(!isset($data) || $data == null){ $data = JRequest::get('post'); }

        $informationData = $data['information'];
        if(!isset($informationData) || $informationData ==  null){ return; }
        $this->reference = $informationData['reference'];
        $this->ean13 = $informationData['ean13'];
        $this->upc = $informationData['upc'];
        $this->isbn = $informationData['isbn'];
        $this->published = $informationData['published'];
        $this->redirect_type = $informationData['redirect_type'];
        $this->visibility = $informationData['visibility'];
        $this->available_for_order = $informationData['available_for_order'];
        $this->show_price = $informationData['show_price'];
        $this->online_only = $informationData['online_only'];
        $this->condition = $informationData['condition'];

        if(isset($data['price_field'])) {
            $priceData = $data['price_field'];
            $this->wholesale_price = $priceData['wholesale_price'];
            $this->price = $priceData['price'];
            $this->tax_rules_group_id = $priceData['tax_rules_group_id'];
            $this->ecotax = $priceData['ecotax'];
            $this->unity = $priceData['unity'];
            $this->on_sale = $priceData['on_sale'];
        }

        if(isset($data['association'])) {
            $associationData = $data['association'];
            $this->default_category_id = $associationData['default_category_id'];
            $this->manufacturer_id = $associationData['manufacturer_id'];
            /*$this-> = $associationData[''];
            $this-> = $associationData[''];
            $this-> = $associationData['']; */
        }
        
        if(isset($data['declination'])){
            $declinationData = $data['declination'];
            /*$this-> = $declinationData[''];
            $this-> = $declinationData[''];
            $this-> = $declinationData[''];*/
        }


        //$metaData = $data['referencing'];
    }

    public function updateProductAttribute($attributeData = null){
        if($attributeData == null){
            $data = JRequest::get('post');
            $attributeData = $data['declination'];
        }

        // Don't process if the combination fields have not been submitted
        if (!JeproshopCombinationModelCombination::isFeaturePublished() || !isset($attributeData['attribute_combination_list'])) {
            return;
        }

        if ((!isset($attributeData['attribute_price']) || $attributeData['attribute_price'] == null)) {
            JError::raiseError(500, 'The price attribute is required.');
        }
        if(!isset($attributeData['attribute_combination_list']) || empty($attributeData['attribute_combination_list'])){
            JError::raiseError(500, 'You must add at least one attribute.');
        }

        $attributeReference = (isset($attributeData['attribute_reference']) && JeproshopTools::isReference($attributeData['attribute_reference'])) ? $attributeData['attribute_reference'] : '';
        $attributeSupplierReference = (isset($attributeData['']) && JeproshopTools::isReference($attributeData[''])) ? $attributeData[''] :'';
        $attributeLocation = (isset($attributeData['']) && JeproshopTools::isReference($attributeData[''])) ? $attributeData[''] : '';
        $attributeEan13 = (isset($attributeData['attribute_ean13']) && JeproshopTools::isEan13($attributeData['attribute_ean13'])) ? $attributeData['attribute_ean13'] : '';
        $attributeIsbn = (isset($attributeData['attribute_isbn']) && JeproshopTools::isIsbn($attributeData['attribute_isbn'])) ? $attributeData['attribute_isbn'] : '';
        $attributeUpc = (isset($attributeData['attribute_upc']) && JeproshopTools::isUpc($attributeData['attribute_upc'])) ? $attributeData['attribute_upc'] : '';
        $attributeWholesalePrice = (isset($attributeData['attribute_wholesale_price']) && JeproshopTools::isPrice($attributeData['attribute_wholesale_price'])) ? $attributeData['attribute_wholesale_price'] : null;
        $attributePrice = (isset($attributeData['attribute_price']) && JeproshopTools::isPrice($attributeData['attribute_price'])) ? $attributeData['attribute_price'] : 0.00;
        $attributePriceWeight = (isset($attributeData['attribute_price_weight']) && JeproshopTools::isPrice($attributeData['attribute_price_weight'])) ? $attributeData['attribute_price_weight'] : 0.00;
        $attributePriceImpact = (isset($attributeData['attribute_price_impact']) && JeproshopTools::isPrice($attributeData['attribute_price_impact'])) ? $attributeData['attribute_price_impact'] : 0.00;
        $attributeEcotax = (isset($attributeData['attribute_ecotax_price']) && JeproshopTools::isPrice($attributeData['attribute_ecotax_price'])) ? $attributeData['attribute_ecotax_price'] : 0.00;
        $attributeQuantity = (isset($attributeData['']) && JeproshopTools::isInt($attributeData[''])) ? $attributeData[''] : 1;
        $attributeWeight = (isset($attributeData['']) && JeproshopTools::isUnsignedFloat($attributeData[''])) ? $attributeData[''] : 0.00;
        $attributeUnitPriceImpact = (isset($attributeData['']) && JeproshopTools::isPrice($attributeData[''])) ? $attributeData[''] : 0.00;
        $attributeDefaultOn = (isset($attributeData['']) && JeproshopTools::isBool($attributeData['default_attribute'])) ? 1 : 0;
        $attributeMinimalQuantity = (isset($attributeData['attribute_minimal_quantity']) && JeproshopTools::isUnsignedInt($attributeData['attribute_minimal_quantity'])) ? $attributeData['attribute_minimal_quantity'] : 1;
        $attributeAvailableDate = (isset($attributeData['attribute_available_date']) && JeproshopTools::isDateFormat($attributeData['attribute_available_date'])) ? $attributeData['attribute_available_date'] : date('Y-m-d m:i:s');


        if($attributeDefaultOn){ $this->deleteDefaultAttributes(); }

        // Change existing one
        if (($productAttributeId = (int)$attributeData['product_attribute_id']) || ($productAttributeId = $this->productAttributeExists(Tools::getValue('attribute_combination_list'), false, null, true, true))) {
            if ($this->access('edit')) {
                if ($this->isProductFieldUpdated('available_date_attribute') && (Tools::getValue('available_date_attribute') != '' && !Validate::isDateFormat(Tools::getValue('available_date_attribute')))) {
                    JError::raiseError(500, 'Invalid date format.');
                } else {
                    $this->updateAttribute((int)$productAttributeId, $attributeWholesalePrice,
                        $attributePrice * $attributePriceImpact,
                        $attributeWeight * $attributeWeightImpact,
                        $attributePrice * $attributePriceImpact,
                        $attributeEcotax,
                        Tools::getValue('id_image_attr'),
                        $attributeReference,
                        $attributeEan13,
                        $attributeDefaultOn,
                        $attributeLocation,
                        $attributeUpc,
                        $attributeMinimalQuantity,
                        $attributeAvailableDate,
                        false,
                        array(),
                        $attributeIsbn);
                    JeproshopStockAvailableModelStockAvailable::setProductDependsOnStock((int)$this->product_id, $this->depends_on_stock, null, (int)$productAttributeId);
                    JeproshopStockAvailableModelStockAvailable::setProductOutOfStock((int)$this->product_id, $this->out_of_stock, null, (int)$productAttributeId);
                }
            } else {
                JError::raiseError(500, 'You do not have permission to add this.');
            }
        } // Add new
        else {
            if ($this->access('add')) {
                if ($this->productAttributeExists(Tools::getValue('attribute_combination_list'))) {
                    JError::raiseError(500, 'This combination already exists.');
                } else {
                    $productAttributeId = $this->addCombinationEntity(
                        $attributeWholesalePrice,
                        $attributePrice * $attributePriceImpact,
                        $attributeWeight * $attributeWeightImpact,
                        $attributeUnity * $attributeUnitImpact,
                        $attributeEcotax,
                        0,
                        Tools::getValue('id_image_attr'),
                        $attributeReference,
                        null,
                        $attributeEan13,
                        $attributeDefault,
                        $attributeLocation,
                        $attributeUpc,
                        $attributeMinimalQuantity,
                        array(),
                        $attributeAvailableDate,
                        $attributeIsbn
                    );
                    JeproshopStockAvailableModelStockAvailable::setProductDependsOnStock((int)$this->product_id, $this->depends_on_stock, null, (int)$productAttributeId);
                    JeproshopStockAvailableModelStockAvailable::setProductOutOfStock((int)$this->product_id, $this->out_of_stock, null, (int)$productAttributeId);
                }
            } else {
                JError::raiseError(500, 'You do not have permission to' . '<hr>' . 'edit here.');
            }
        }

        if (!count($this->errors)) {
            $combination = new JeproshopCombinationModelCombination((int)$productAttributeId);
            $combination->setAttributes($attributeData['attribute_combination_list']);

            // images could be deleted before
            $imageIds = $attributeData['image_attribute_id'];
            if (!empty($imageIds)) {
                $combination->setImages($imageIds);
            }

            $this->checkDefaultAttributes();
            if (isset($attributeDefault)) {
                JeproshopProductModelProduct::updateDefaultAttribute((int)$this->product_id);
                if (isset($productAttributeId)) {
                    $this->cache_default_attribute = (int)$productAttributeId;
                }

                if (isset($attributeAvailableDate)) {
                    $this->setAvailableDate($attributeAvailableDate);
                } else {
                    $this->setAvailableDate();
                }
            }
        }        
    }

    public function updatePriceAddition($priceData = null)
    {
        // Check if a specific price has been submitted
        if (!isset($priceData) || $priceData == null) {
            $request = JRequest::get('post');
            $priceData = $request['price_field'];
        }

        $productAttributeId = (isset($priceData['specific_price_product_attribute_id']) && JeproshopTools::isUnsignedInt($priceData['specific_price_product_attribute_id'])) ? $priceData['specific_price_product_attribute_id'] : 0;
        $shopId = (isset($priceData['specific_price_shop_id']) && JeproshopTools::isUnsignedInt($priceData['specific_price_shop_id'])) ? $priceData['specific_price_shop_id'] : JeproshopSettingModelSetting::getValue('default_shop');
        $currencyId = (isset($priceData['specific_price_currency_id']) && JeproshopTools::isUnsignedInt($priceData['specific_price_currency_id'])) ? $priceData['specific_price_currency_id'] : JeproshopSettingModelSetting::getValue('default_currency');
        $countryId = (isset($priceData['specific_price_country_id']) && JeproshopTools::isUnsignedInt($priceData['specific_price_country_id'])) ? $priceData['specific_price_country_id'] : JeproshopSettingModelSetting::getValue('default_country');
        $groupId = (isset($priceData['specific_price_group_id']) && JeproshopTools::isUnsignedInt($priceData['specific_price_group_id'])) ? $priceData['specific_price_group_id'] : JeproshopSettingModelSetting::getValue('customer_group');
        $customerId = (isset($priceData['specific_price_customer_id']) && JeproshopTools::isUnsignedInt($priceData['specific_price_customer_id'])) ? $priceData['specific_price_customer_id'] : 0;
        $price = (isset($priceData['leave_base_price']) && JeproshopTools::isBool($priceData['leave_base_price'])) ? '-1' : $priceData['specific_price_price'];
        $fromQuantity = (isset($priceData['specific_price_from_quantity']) && JeproshopTools::isUnsignedInt($priceData['specific_price_from_quantity'])) ? $priceData['specific_price_from_quantity'] : 1;
        $reduction = (isset($priceData['specific_price_reduction']) && JeproshopTools::isNumeric($priceData['specific_price_reduction'])) ? (float)$priceData['specific_price_reduction'] : 0.00;
        $reductionTax = (isset($priceData['specific_price_reduction_tax']) && JeproshopTools::isNumeric($priceData['specific_price_reduction_tax'])) ? (float)$priceData['specific_price_reduction_tax'] : 0.00;
        $reductionType = (isset($priceData['specific_price_reduction_type']) && in_array($priceData['specific_price_reduction_type'], array('amount', 'percentage', '--'))) ? $priceData['specific_price_reduction_type'] : '--';
        $reductionType = !$reduction ? 'amount' : $reductionType;
        $reductionType = $reductionType == '-' ? 'amount' : $reductionType;
        $from = (isset($priceData['specific_price_from']) && JeproshopTools::isDate($priceData['specific_price_from'])) ? $priceData['specific_price_from'] : null;
        if (!$from) {
            $from = '0000-00-00 00:00:00';
        }
        $to = (isset($priceData['specific_price_to']) && JeproshopTools::isDate($priceData['specific_price_to'])) ? $priceData['specific_price_to'] : null;
        if (!$to) {
            $to = '0000-00-00 00:00:00';
        }

        if(!(($price == '-1') && ((float)$reduction == '0'))) {
            if (($price == '-1') && ((float)$reduction == '0')) {
                JError::raiseError(500, 'No reduction value has been submitted');
            } elseif ($to != '0000-00-00 00:00:00' && strtotime($to) < strtotime($from)) {
                JError::raiseError(500, 'Invalid date range');
            } elseif ($reductionType == 'percentage' && ((float)$reduction <= 0 || (float)$reduction > 100)) {
                JError::raiseError('Submitted reduction value (0-100) is out-of-range');
            } elseif ($this->validateSpecificPrice($shopId, $currencyId, $countryId, $groupId, $customerId, $price, $fromQuantity, $reduction, $reductionType, $from, $to, $productAttributeId)) {
                $specificPrice = new JeproshopSpecificPriceModelSpecificPrice();
                $specificPrice->product_id = (int)$this->product_id;
                $specificPrice->product_attribute_id = (int)$productAttributeId;
                $specificPrice->shop_id = (int)$shopId;
                $specificPrice->currency_id = (int)($currencyId);
                $specificPrice->country_id = (int)($countryId);
                $specificPrice->group_id = (int)($groupId);
                $specificPrice->customer_id = (int)$customerId;
                $specificPrice->price = (float)($price);
                $specificPrice->from_quantity = (int)($fromQuantity);
                $specificPrice->reduction = (float)($reductionType == 'percentage' ? $reduction / 100 : $reduction);
                $specificPrice->reduction_tax = $reductionTax;
                $specificPrice->reduction_type = $reductionType;
                $specificPrice->from = $from;
                $specificPrice->to = $to;
                if (!$specificPrice->add()) {
                    JError::raiseError('An error occurred while updating the specific price.');
                }
            }
        }
    }

    protected  function validateSpecificPrice($shopId, $currencyId, $countryId, $groupId, $customerId, $price, $fromQuantity, $reduction, $reductionType, $from, $to, $productAttributeId){
        if(!JeproshopTools::isUnsignedInt($shopId) || !JeproshopTools::isUnsignedInt($currencyId) || !JeproshopTools::isUnsignedInt($countryId) || !JeproshopTools::isUnsignedInt($groupId) || !JeproshopTools::isUnsignedInt($customerId)){
            return false;
        }else if(!isset($price) && !isset($reduction) || (isset($price) && !JeproshopTools::isNegativePrice($price)) || (isset($reduction) && !JeproshopTools::isPrice($reduction))){
            return false;
        }else if (!JeproshopTools::isUnsignedInt($fromQuantity)){
            return false;
        }else if ($reduction && !JeproshopTools::isReductionType($reductionType)){
            return false;
        }else if ($from && $to && (!JeproshopTools::isDateFormat($from) || !JeproshopTools::isDateFormat($to))){
            return false;
        }elseif (JeproshopSpecificPriceModelSpecificPrice::exists((int)$this->product_id, $productAttributeId, $shopId, $groupId, $countryId, $currencyId, $customerId, $fromQuantity, $from, $to, false)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * This method allows to flush price cache
     * @static
     */
    public static function flushPriceCache(){
        self::$_prices = array();
        self::$_pricesLevel2 = array();
    }

    public function updateSpecificPricePriorities($priorityData = null) {
        if($priorityData == null){
            $request = JRequest::get('post');
            $priorityData = $request['price_field'];
        }
        $priorityArray = array('shop_id', 'country_id', 'currency_id', 'group_id');
        $productAttributeId = (isset($priceData['specific_price_product_attribute_id']) && JeproshopTools::isUnsignedInt($priceData['specific_price_product_attribute_id'])) ? $priceData['specific_price_product_attribute_id'] : 0;
        $priority1 = (isset($priorityData['specific_price_priority_1']) && in_array($priorityData['specific_price_priority_1'], $priorityArray)) ? $priorityData['specific_price_priority_1'] : null;
        $priority2 = (isset($priorityData['specific_price_priority_2']) && in_array($priorityData['specific_price_priority_2'], $priorityArray)) ? $priorityData['specific_price_priority_2'] : null;
        $priority3 = (isset($priorityData['specific_price_priority_3']) && in_array($priorityData['specific_price_priority_3'], $priorityArray)) ? $priorityData['specific_price_priority_3'] : null;
        $priority4 = (isset($priorityData['specific_price_priority_4']) && in_array($priorityData['specific_price_priority_4'], $priorityArray)) ? $priorityData['specific_price_priority_4'] : null;
        
        $priorities = array($priority1, $priority2, $priority3, $priority4);
        if ($priority1 == null || $priority2 == null || $priority3  == null || $priority4 == null) {
            JError::raiseError(500, 'Please specify priorities.');
        } elseif (!$productAttributeId) {
            if (!JeproshopSpecificPriceModelSpecificPrice::setPriorities($priorities)) {
                JError::displayError(500, 'An error occurred while updating priorities.');
            } /* else {
                $this->confirmations[] = $this->l('The price rule has successfully updated');
            }*/
        } elseif (!JeproshopSpecificPriceModelSpecificPrice::setSpecificPriority((int)$this->product_id, $priorities)) {
            JError::raiseError('An error occurred while setting priorities.');
        }
    }

    public function updateCustomizationConfiguration($customizationData = null){
        if($customizationData == null){
            $request = JRequest::get('post');
            $customizationData = $request['customization'];
        }
        // Get the number of existing customization fields ($product->text_fields is the updated value, not the existing value)
        $currentCustomization = $this->getCustomizationFieldIds();
        $filesCount = 0;
        $textCount = 0;
        if (is_array($currentCustomization)) {
            foreach ($currentCustomization as $field) {
                if ($field->type == JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD) {
                    $textCount++;
                } else {
                    $filesCount++;
                }
            }
        }

        if (!$this->createLabels((int)$this->uploadable_files - $filesCount, (int)$this->text_fields - $textCount)) {
            JError::raiseError(500, 'An error occurred while creating customization fields.');
        }
        if (!$this->updateLabels()) {
            JError::raiseError(500, 'An error occurred while updating customization fields.');
        }
        $this->customizable = ($this->uploadable_files > 0 || $this->text_fields > 0) ? 1 : 0;
        if (($this->uploadable_files != $filesCount || $this->text_fields != $textCount) &&  !$this->update()) {
            JError::raiseError(500, 'An error occurred while updating the custom configuration.');
        }
    }


    /**
     * Attach an existing attachment to the product
     *
     * @param null $attachmentsData
     */
    public function updateAttachments($attachmentsData = null){
        if($attachmentsData == null){
            $request = JRequest::get('post');
            $attachmentsData = $request['attachments'];
        }
        $attachments = (isset($attachmentsData) && is_array($attachmentsData)) ? trim($attachmentsData, ',') : array();
        $attachments = explode(',', $attachments);
        if (!JeproshopAttachmentModelAttachment::attachToProduct($this->product_id, $attachments)) {
            JError::raiseError(500, 'An error occurred while saving product attachments.');
        }
    }

    public function updateImageLegends($imageData = null){
        //if (Tools::getValue('key_tab') == 'Images' && Tools::getValue('submitAddproductAndStay') == 'update_legends' && Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product')))) {
            $imageId = (int)(isset($imageData['caption_id']) ? $imageData['caption_id'] : 0);
            $languages = JeproshopLanguageModelLanguage::getLanguages(false);
            $db = JFactory::getDBO();
            foreach ($imageData as $key => $val) {
                if (preg_match('/^legend_([0-9]+)/i', $key, $match)) {
                    foreach ($languages as $lang) {
                        if ($val && $lang->lang_id == $match[1]) {
                            $query = "UPDATE " . $db->quoteName('#__jeproshop_image_lang') . " AS image_lang SET image_lang." . $db->quoteName('legend') . " = ";
                            $query .= $db->quote($db->escape($val)) . " WHERE " . ($imageId ?
                                    $db->quoteName('image_id') . " = " . (int)$imageId : " EXISTS (SELECT 1 FROM "
                                    . $db->quoteName('#__jeproshop_image') . " AS image WHERE image." . $db->quoteName('image_id') . " = image_lang."
                                    . $db->quoteName('image_id') . " AND image." . $db->quoteName('product_id') . " = " . (int)$this->product_id.')');
                            $query .= " AND image_lang." . $db->quoteName('lang_id') . " = " .(int)$lang->lang_id;

                            $db->setQuery($query);
                            $db->query();
                        }
                    }
                }
            }
        //}
    }

    /**
     * delete all items in pack, then check if type_product value is 2.
     * if yes, add the pack items from input "inputPackItems"
     *
     * @return bool
     */
    public function updatePackItems(){
        JeproshopProductPack::deleteItems($this->product_id);
        $request = JRequest::get('post');
        $informationData = $request['information'];
        $productType = (isset($informationData['product_type'])) ? $informationData['product_type'] : JeproshopProductModelProduct::SIMPLE_PRODUCT;
        // lines format: QTY x ID-QTY x ID
        if ($productType == JeproshopProductModelProduct::PACKAGE_PRODUCT) {
            $this->setDefaultAttribute(0);//reset cache_default_attribute
            $items = isset($informationData['input_pack_items']) ? $informationData['input_pack_items'] : $array;
            $lines = array_unique(explode('-', $items));

            // lines is an array of string with format : QTYxIDxID_PRODUCT_ATTRIBUTE
            if (count($lines)) {
                foreach ($lines as $line) {
                    if (!empty($line)) {
                        $itemAttributeId = 0;
                        count($array = explode('x', $line)) == 3 ? list($quantity, $itemId, $itemAttributeId) = $array : list($quantity, $itemId) = $array;
                        if ($quantity > 0 && isset($itemId)) {
                            if (JeproshopProductPack::isPack((int)$itemId)) {
                                JError::raiseError(500, 'You can\'t add product packs into a pack');
                            } elseif (!JeproshopProductPack::addItem((int)$this->product_id, (int)$itemId, (int)$quantity, (int)$itemAttributeId)) {
                                JError::raiseError(500, 'An error occurred while attempting to add products to the pack.');
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Update product download
     *
     * @param int     $edit
     *
     * @return bool
     */
    public function updateDownloadProduct($edit = 0){
        //legacy/sf2 form workaround
        //if is_virtual_file parameter was not send (SF2 form case), don't process virtual file
        $data = JRequest::get('post');
        $informationData = $data['information'];
        $isVirtualFile = (isset($informationData['is_virtual_file']) ? $informationData['is_virtual_file'] : false);
        if($isVirtualFile === false) {
            return false;
        }

        if ((int)$isVirtualFile == 1) {
            if (isset($_FILES['virtual_product_file_uploader']) && $_FILES['virtual_product_file_uploader']['size'] > 0) {
                $virtualProductFilename = JeproshopProductDownloadModelProductDownload::getNewFilename();
                $helper = new JeproshopUploaderHelper('virtual_product_file_uploader');
                $helper->setPostMaxSize(JeproshopTools::getOctets(ini_get('upload_max_filesize')))
                    ->setSavePath(COM_JEPROSHOP_DOWNLOAD_DIR_)->upload($_FILES['virtual_product_file_uploader'], $virtualProductFilename);
            } else {
                $virtualProductFilename = Tools::getValue('virtual_product_filename', JeproshopProductDownloadModelProductDownload::getNewFilename());
            }

            $this->setDefaultAttribute(0);//reset cache_default_attribute
            if (Tools::getValue('virtual_product_expiration_date') && !JeproshopTools::isDate(Tools::getValue('virtual_product_expiration_date'))) {
                if (!Tools::getValue('virtual_product_expiration_date')) {
                    $this->errors[] = Tools::displayError('The expiration-date attribute is required.');
                    return false;
                }
            }

            // Trick's
            if ($edit == 1) {
                $productDownloadId = (int)JeproshopProductDownloadModelProductDownload::getProductDownloadIdFromProductId((int)$this->product_id);
                if (!$productDownloadId) {
                    $productDownloadId = (int)Tools::getValue('virtual_product_id');
                }
            } else {
                $productDownloadId = Tools::getValue('virtual_product_id');
            }

            $isShareable = Tools::getValue('virtual_product_is_shareable');
            $virtualProductName = Tools::getValue('virtual_product_name');
            $virtualProductNumberOfDays = Tools::getValue('virtual_product_nb_days');
            $virtualProductNumberOfDownloadable = Tools::getValue('virtual_product_nb_downloadable');
            $virtualProductExpirationDate = Tools::getValue('virtual_product_expiration_date');

            $download = new JeproshopProductDownloadModelProductDownload((int)$productDownloadId);
            $download->product_id = (int)$this->product_id;
            $download->display_filename = $virtualProductName;
            $download->filename = $virtualProductFilename;
            $download->date_add = date('Y-m-d H:i:s');
            $download->expiration_date = $virtualProductExpirationDate ? $virtualProductExpirationDate.' 23:59:59' : '';
            $download->nb_days_accessible = (int)$virtualProductNumberOfDays;
            $download->nb_downloadable = (int)$virtualProductNumberOfDownloadable;
            $download->published = 1;
            $download->is_sharable = (int)$isShareable;
            if ($download->save()) {
                return true;
            }
        } else {
            /* un-active download product if checkbox not checked */
            if ($edit == 1) {
                $productDownloadId = (int)JeproshopProductDownloadModelProductDownload::getProductDownloadIdFromProductId((int)$this->product_id);
                if (!$productDownloadId) {
                    $productDownloadId = (int)Tools::getValue('virtual_product_id');
                }
            } else {
                $productDownloadId = JeproshopProductDownloadModelProductDownload::getProductDownloadIdFromProductId($this->product_id);
            }

            if (!empty($id_product_download)) {
                $productDownload = new JeproshopProductDownloadModelProductDownload((int)$productDownloadId);
                $productDownload->expiration_date = date('Y-m-d H:i:s', time() - 1);
                $productDownload->published = 0;
                return $productDownload->save();
            }
        }
        return false;
    }

    /**
     * Update product tags
     *
     * @param array $languages Array languages
     * @return bool Update result
     */
    public function updateTags($languages = null){
        $tagSuccess = true;

        $data = JRequest::get('post');
        $informationData = $data['information'];

        if($languages == null){ $languages = JeproshopLanguageModelLanguage::getLanguages(true); }
        /* Reset all tags for THIS product */
        if (!JeproshopTagModelTag::deleteTagsForProduct((int)$this->product_id)) {
            JError::raiseError(500, 'An error occurred while attempting to delete previous tags.');
        }
        /* Assign tags to this product */
        foreach ($languages as $language) {
            $value = isset($informationData['tags_' . $language->lang_id]) ? $informationData['tags_' . $language->lang_id] : null;
            if ($value) {
                $tagSuccess &= JeproshopTagModelTag::addTags($language->lang_id, (int)$this->product_id, $value);
            }
        }

        if (!$tagSuccess) {
            JError::raiseError(500, 'An error occurred while adding tags.');
        }
        return $tagSuccess;
    }

    public function createLabels($uploadable_files, $text_fields){
        $languages = JeproshopLanguageModelLanguage::getLanguages();
        if ((int)$uploadable_files > 0){
            for ($i = 0; $i < (int)$uploadable_files; $i++){
                if (!$this->createLabel($languages, JeproshopProductModelProduct::CUSTOMIZE_FILE)){ return false; }
            }
        }

        if ((int)$text_fields > 0){
            for ($i = 0; $i < (int)$text_fields; $i++){
                if (!$this->createLabel($languages, JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD)){ 	return false; }
            }
        }
        return true;
    }

    protected function createLabel(&$languages, $type){
        $db = JFactory::getDBO();

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_customization_field') . "( " . $db->quoteName('product_id') . ", " . $db->quoteName('type') . ", ";
        $query .= $db->quoteName('required') . ") VALUES (" . (int)$this->product_id . ", " . (int)$type . ", 0)";

        $db->setQuery($query);
        $result = $db->query();
        $customization_field_id = $db->insertid();
        // Label insertion
        if (!$result ||	!$customization_field_id){ return false; }

        // Multilingual label name creation
        $values = '';
        foreach ($languages as $language) {
            $values .= '(' . (int)$customization_field_id . ', ' . (int)$language->lang_id . ', \'\'), ';


            $values = rtrim($values, ', ');
            $query = "INSERT INTO " . $db->quoteName('#__jeproshop_customization_field_lang') . "(" . $db->quoteName('customization_field_id') . ", " . $db->quoteName('lang_id') . ", ";
            $query .= $db->quoteName('name') . ") VALUES (" . (int)$customization_field_id . ", " . (int)$language->lang_id . ", " . $db->quote($values) . ")";

            $db->setQuery($query);
            if (!$db->query()) {
                return false;
            }
        }

        // Set cache of feature detachable to true
        JeproshopSettingModelSetting::updateValue('customization_feature_active', '1');

        return true;
    }

    public function updateLabels(){
        $has_required_fields = 0;
        $db = JFactory::getDBO();
        foreach ($_POST as $field => $value) {
            /* Label update */
            if (strncmp($field, 'label_', 6) == 0) {
                if (!$tmp = $this->checkLabelField($field, $value)) {
                    return false;
                }
                /* Multilingual label name update */
                if (JeproshopShopModelShop::isFeaturePublished()) {
                    foreach (JeproshopShopModelShop::getContextListShopIds() as $shopId){
                        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_customization_field_lang') . "(" . $db->quoteName('customization_field_id');
                        $query .= ", "  . $db->quoteName('lang_id') . ", " . $db->quoteName('shop_id') . ", " . $db->quoteName('name') . ") VALUES (";
                        $query .= (int)$tmp[2] . ", " . (int)$tmp[3] . ", " .(int)$shopId . ", " . $db->quote($value)  . ") ON DUPLICATE KEY UPDATE ";
                        $query .= $db->quoteName('name') . " = " . $db->quoteName($value) ;

                        $db->setQuery($query);
                        if (!$db->query()) {
                            return false;
                        }
                    }
                } else{
                    $query = "INSERT INTO " . $db->quoteName('#__jeproshop_customization_field_lang') . "(" . $db->quoteName('customization_field_id') ;
                    $query .= ", " . $db->quoteName('lang_id') . ", " . $db->quoteName('name') . ") VALUES (" . (int)$tmp[2] . ", " . (int)$tmp[3] . ", ";
                    $query .= $db->quuoteName($value) . ") ON DUPLICATE KEY UPDATE " . $db->quoteName('name') ." = " . $db->quote($value);

                    $db->setQuery($query);
                    if (!$db->query()) {
                        return false;
                    }
                }

                $is_required = isset($_POST['require_'.(int)$tmp[1].'_'.(int)$tmp[2]]) ? 1 : 0;
                $has_required_fields |= $is_required;
                /* Require option update */
                $query = "UPDATE " . $db->quoteName('#__jeproshop_customization_field') . " SET " . $db->quoteName('required');
                $query .= " = " . ($is_required ? 1 : 0) . " WHERE " . $db->quteName('customization_field_id') . " = " .(int)$tmp[2];

                $db->setQuery($query);
                if (!$db->query()) {
                    return false;
                }
            }
        }

        if ($has_required_fields && !ObjectModel::updateMultishopTable('product', array('customizable' => 2), 'a.id_product = '.(int)$this->id)) {
            return false;
        }

        if (!$this->deleteOldLabels()) {
            return false;
        }

        return true;
    }

    protected function deleteOldLabels(){
        $max = array(
            JeproshopProductModelProduct::CUSTOMIZE_FILE => (int)$this->uploadable_files,
            JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD => (int)$this->text_fields
        );

        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('customization_field_id') . ", " . $db->quoteName('type') . " FROM " . $db->quoteName('#__jeproshop_customization_field');
        $query .= " WHERE " . $db->quoteName('product_id') . " = " . (int)$this->product_id . " ORDER BY " . $db->quoteName('customization_field_id') ;

        $db->setQuery($query);
        /* Get customization field ids */
        if (($result = $db->loadObjectList()) === false) {
            return false;
        }

        if (empty($result)) {
            return true;
        }

        $customization_fields = array(
            JeproshopProductModelProduct::CUSTOMIZE_FILE => array(),
            JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD => array()
        );

        foreach ($result as $row) {
            $customization_fields[(int)$row->type][] = (int)$row->customization_field_id;
        }

        $extra_file = count($customization_fields[JeproshopProductModelProduct::CUSTOMIZE_FILE]) - $max[JeproshopProductModelProduct::CUSTOMIZE_FILE];
        $extra_text = count($customization_fields[JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD]) - $max[JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD];

        /* If too much inside the database, deletion */
        $query = "DELETE customization_field.*, customization_field_lang.* FROM " . $db->quoteName('#__jeproshop_customization_field');
        $query .= " AS customization_field JOIN " . $db->quoteName('#__jeproshop_customization_field_lang') . " AS customization_field_lang ";
        $query .= " WHERE customization_field`." . $db->quoteName('product_id') . " = " . (int)$this->product_id . " AND customization_field.";
        $query .= $db->quoteName('type') . " = " . JeproshopProductModelProduct::CUSTOMIZE_FILE . "	AND customization_field_lang." ;
        $query .= $db->quoteName('customization_field_id') . " = customization_field." . $db->quoteName('customization_field_id') ;
        $query .= " AND customization_field." . $db->quoteName('customization_field_id') . " >= " .(int)$customization_fields[JeproshopProductModelProduct::CUSTOMIZE_FILE][count($customization_fields[JeproshopProductModelProduct::CUSTOMIZE_FILE]) - $extra_file];

        $db->setQuery($query);
            
        if ($extra_file > 0 && count($customization_fields[JeproshopProductModelProduct::CUSTOMIZE_FILE]) - $extra_file >= 0 &&
            (!$db->query())) {
            return false;
        }
        $query = "DELETE customization_field, customization_field_lang	FROM " . $db->quoteName('#__jeproshop_customization_field') ;
        $query .= " AS customization_field JOIN " . $db->quoteName('#__jeproshop_customization_field_lang') . " AS customization_field_lang ";
        $query .= " WHERE customization_field." . $db->quoteName('product_id') . " = " .(int)$this->product_id . " AND customization_field.";
        $query .= $db->quoteName('type') . " = " . JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD . " AND customization_field_lang.";
        $query .= $db->quoteName('customization_field_id') . " = customization_field." . $db->quoteName('customization_field_id');
        $query .= " AND customization_field." . $db->quoteName('customization_field_id') . " >= ";
        $query .= (int)$customization_fields[JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD][count($customization_fields[JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD]) - $extra_text];

        $db->setQuery($query);
        if ($extra_text > 0 && count($customization_fields[JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD]) - $extra_text >= 0 &&
            (!$db->query())) {
            return false;
        }

        // Refresh cache of feature detachable
       JeproshopSettingModelSetting::updateValue('customization_feature_active',
           JeproshopCustomizationModelCustomization::isCurrentlyUsed());

        return true;
    }


    public function updateImage($imageData = null){
        /*$id_image = (int)Tools::getValue('id_image');
        $image = new Image((int)$id_image);
        if (Validate::isLoadedObject($image)) {
            /* Update product image/legend * /
            // @todo : move in processEditProductImage
            if (Tools::getIsset('editImage')) {
                if ($image->cover) {
                    $_POST['cover'] = 1;
                }

                $_POST['id_image'] = $image->id;
            } elseif (Tools::getIsset('coverImage')) {
                /* Choose product cover image * /
                Image::deleteCover($image->id_product);
                $image->cover = 1;
                if (!$image->update()) {
                    $this->errors[] = Tools::displayError('You cannot change the product\'s cover image.');
                } else {
                    $productId = (int)Tools::getValue('id_product');
                    @unlink(_PS_TMP_IMG_DIR_.'product_'.$productId.'.jpg');
                    @unlink(_PS_TMP_IMG_DIR_.'product_mini_'.$productId.'_'.$this->context->shop->id.'.jpg');
                    $this->redirect_after = self::$currentIndex.'&id_product='.$image->id_product.'&id_category='.(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '').'&action=Images&addproduct'.'&token='.$this->token;
                }
            } elseif (Tools::getIsset('imgPosition') && Tools::getIsset('imgDirection')) {
                /* Choose product image position * /
                $image->updatePosition(Tools::getValue('imgDirection'), Tools::getValue('imgPosition'));
                $this->redirect_after = self::$currentIndex.'&id_product='.$image->id_product.'&id_category='.(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '').'&add'.$this->table.'&action=Images&token='.$this->token;
            }
        } else {
            $this->errors[] = Tools::displayError('The image could not be found. ');
        } */
    }

    public function updatePrice(){
        $app = JFactory::getApplication();
        $useAjax = $app->input->get('use_ajax', 0);

        if($useAjax){
            $wholeSalePrice = $app->input->get('whole_sale_price');
            $price = $app->input->get('price');
            $unitPriceRatio = $app->input->get('unit_price_ratio');
            $onSale = $app->input->get('on_sale');
            $taxRulesGroupId = $app->input->get('tax_rule_group_id');
            $ecoTax = $app->input->get('eco_tax');
            $unity = $app->input->get('unity');
        }else{
            $wholeSalePrice = $this->wholesale_price;
            $price = $this->price;
            $unitPriceRatio = $this->unit_price_ratio;
            $onSale = $this->on_sale;
            $taxRulesGroupId = $this->tax_rules_group_id;
            $ecoTax = $this->ecotax;
            $unity = $this->unity;
        }

        $db = JFactory::getDBO();

        $query = "UPDATE " . $db->quoteName('#__jeproshop_product') . " SET " . $db->quoteName('wholesale_price') . " = " . (float)$wholeSalePrice;
        $query .= ", " . $db->quoteName('price') . " = " . (float)$price . ", " . $db->quoteName('unit_price_ratio') . " = " . (float)$unitPriceRatio;
        $query .= ", " . $db->quoteName('ecotax') . " = " . (float)$ecoTax . ", " . $db->quoteName('tax_rules_group_id') . " = " . (int)$taxRulesGroupId;
        $query .= ", " . $db->quoteName('on_sale') . " = " . (int)$onSale . ", " . $db->quoteName('unity') . " = " . $db->quote($unity) . " WHERE ";
        $query .= $db->quoteName('product_id') . " = " . $this->product_id;

        $db->setQuery($query);

        if($db->query()) {
            $query = "UPDATE " . $db->quoteName('#__jeproshop_product_shop') . " SET " . $db->quoteName('wholesale_price') . " = " . (float)$wholeSalePrice;
            $query .= ", " . $db->quoteName('price') . " = " . (float)$price . ", " . $db->quoteName('unit_price_ratio') . " = " . (float)$unitPriceRatio;
            $query .= ", " . $db->quoteName('ecotax') . " = " . (float)$ecoTax . ", " . $db->quoteName('tax_rules_group_id') . " = " . (int)$taxRulesGroupId;
            $query .= ", " . $db->quoteName('on_sale') . " = " . $db->quoteName('unity') . " = " . $db->quote($unity) . " WHERE " . $db->quoteName('product_id');
            $query .= " = " . $this->product_id . " AND " . $db->quoteName('shop_id') . " = " . JeproshopContext::getContext()->shop->shop_id;

            $db->setQuery($query);
            if($db->query()){ return true; }
        }
        return false;
    }

    public function addSpecificPrice(){
        $app = JFactory::getApplication();
        $useAjax = $app->input->get('use_ajax', 0);
        //$db = JFactory::getDBO();

        $specificPrice = new JeproshopSpecificPriceModelSpecificPrice();

        if($useAjax){
            $specificPriceRuleId = $app->input->get('specific_price_rule_id') ;
            $cartId = $app->input->get('cart_id') ;
            $shopId  = $app->input->get('shop_id') ;
            $shopGroupId = $app->input->get('shop_group_id') ;
            $currencyId = $app->input->get('currency_id') ;
            $countryId = $app->input->get('country_id') ;
            $groupId = $app->input->get('group_id') ;
            $customerId = $app->input->get('customer_id') ;
            $productAttributeId = $app->input->get('product_attribute_id') ;
            $price = $app->input->get('price') ;
            $fromQuantity = $app->input->get('starting_at') ;
            $reduction = $app->input->get('reduction') ;
            $reductionType = $app->input->get('reduction_type') ;
            $startingFrom = $app->input->get('from') ;
            $endsOn = $app->input->get('to') ;
        }else{
            $specificPriceRuleId = $app->input->get('specific_price_rule_id') ;
            $cartId = $app->input->get('cart_id') ;
            $shopId  = $app->input->get('shop_id') ;
            $shopGroupId = $app->input->get('shop_group_id') ;
            $currencyId = $app->input->get('currency_id') ;
            $countryId = $app->input->get('country_id') ;
            $groupId = $app->input->get('group_id') ;
            $customerId = $app->input->get('customer_id') ;
            $productAttributeId = $app->input->get('product_attribute_id') ;
            $price = $app->input->get('price') ;
            $fromQuantity = $app->input->get('starting_at') ;
            $reduction = $app->input->get('reduction') ;
            $reductionType = $app->input->get('reduction_type') ;
            $startingFrom = $app->input->get('from') ;
            $endsOn = $app->input->get('to') ;
        }

        $specificPrice->product_id = $this->product_id;
        $specificPrice->specific_price_rule_id = (isset($specificPriceRuleId) && JeproshopTools::isUnsignedInt($specificPriceRuleId)) ? $specificPriceRuleId : 0;
        $specificPrice->cart_id = (isset($cartId) && JeproshopTools::isUnsignedInt($cartId)) ? $cartId : 0;
        $specificPrice->shop_id = (isset($shopId) && JeproshopTools::isUnsignedInt($shopId)) ? $shopId : JeproshopContext::getContext()->shop->shop_id;
        $specificPrice->shop_group_id = (isset($shopGroupId) && JeproshopTools::isUnsignedInt($shopGroupId)) ? $shopGroupId : JeproshopContext::getContext()->shop->shop_group_id;
        $specificPrice->currency_id = (isset($currencyId) && JeproshopTools::isUnsignedInt($currencyId)) ? $currencyId : 0;
        $specificPrice->country_id = (isset($countryId) && JeproshopTools::isUnsignedInt($countryId)) ? $countryId : 0;
        $specificPrice->group_id = (isset($groupId) && JeproshopTools::isUnsignedInt($groupId)) ? $groupId : 0;
        $specificPrice->customer_id = (isset($customerId) && JeproshopTools::isUnsignedInt($customerId)) ? $customerId : 0;
        $specificPrice->product_attribute_id = (isset($productAttributeId) && JeproshopTools::isUnsignedInt($productAttributeId)) ? $productAttributeId : 0;
        $specificPrice->price = (isset($price) && JeproshopTools::isPrice($price)) ? $price : 0.00;
        $specificPrice->from_quantity = (isset($fromQuantity) && JeproshopTools::isUnsignedInt($fromQuantity)) ? $fromQuantity : 1;
        $specificPrice->reduction_type = (isset($reductionType) && in_array($reductionType, array('amount', 'percentage'))) ? $reductionType : 'amount';
        $specificPrice->reduction = (isset($reduction) && JeproshopTools::isNumeric($reduction)) ? $reduction : 0.00;
        if($specificPrice->reduction_type == 'percentage'){ $specificPrice->reduction /= 100;}
        $specificPrice->to = (isset($endsOn) && JeproshopTools::isDate($endsOn)) ? $endsOn : '0000-00-00 00:00:00';
        $specificPrice->from = (isset($startingFrom) && JeproshopTools::isDate($startingFrom)) ? $startingFrom : '0000-00-00 00:00:00';
        
        if($specificPrice->add()){
            return $specificPrice;
        }else{
            return null;
        }

        /*$query = "INSERT INTO " . $db->quoteName('#__jeproshop_specific_price') . "(" . $db->quoteName('specific_price_rule_id') . ", ";
        $query .= $db->quoteName('cart_id') . ", " . $db->quoteName('product_id') . ", " . $db->quoteName('shop_id') . ", ";
        $query .= $db->quoteName('shop_group_id') . ", " . $db->quoteName('currency_id') . ", " . $db->quoteName('country_id') . ", ";
        $query .= $db->quoteName('group_id') . ", " . $db->quoteName('customer_id') . ", " . $db->quoteName('product_attribute_id') . ", ";
        $query .= $db->quoteName('price') . ", " . $db->quoteName('from_quantity') . ", " . $db->quoteName('reduction') . ", ";
        $query .= $db->quoteName('reduction_type') . ", " . $db->quoteName('from') . ", " . $db->quoteName('to') . ") VALUES (";
        $query .= (int)$specificPriceRuleId .", " . (int)$cartId . ", " . (int)$this->product_id . ", " . (int)$shopId . ", ";
        $query .= (int)$shopGroupId . ", " . (int)$currencyId . ", " . (int)$countryId . ", " . (int)$groupId . ", " . (int)$customerId . ", ";
        $query .= (int)$productAttributeId . ", " . (float)$price . ", " . (int)$fromQuantity . ", " . (float)$reduction . ", ";
        $query .= $db->quote($reductionType) . ", " . $db->quote($startingFrom) . ", " . $db->quote($endsOn) . ")"; */
    }

    /**
     * Link accessories with product. No need to inflate a full Product (better performances).
     *
     * @param array $accessoriesIds Accessories ids
     * @param int $productId The product ID to link accessories on.
     */
    public static function updateProductAccessories($accessoriesIds, $productId){

    }
}