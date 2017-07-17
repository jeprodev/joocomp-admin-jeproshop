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
     * @since 1.5.0
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
     * @param integer $lang_id Language id
     * @param string $attribute_value_separator
     * @param string $attribute_separator
     * @return array Product attributes combinations
     */
    public function getAttributesResume($lang_id, $attribute_value_separator = ' - ', $attribute_separator = ', '){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return array(); }
        $add_shop = '';

        $db = JFactory::getDBO();

        $query = "SELECT product_attribute.*, product_attribute_shop.* FROM " . $db->quoteName('#__jeproshop_product_attribute');
        $query .= " AS product_attribute " . JeproshopShopModelShop::addSqlAssociation('product_attribute') . "	WHERE product_attribute.";
        $query .= $db->quoteName('product_id') . " = " .(int)$this->product_id . " GROUP BY product_attribute." . $db->quoteName('product_attribute_id');

        $db->setQuery($query);
        $combinations = $db->loadObjectList();

        if (!$combinations){ return false; }

        $product_attributes = array();
        foreach ($combinations as $combination){
            $product_attributes[] = (int)$combination->product_attribute_id;
        }
        $query = "SELECT product_attribute_combination.product_attribute_id, GROUP_CONCAT(attribute_group_lang." . $db->quoteName('name') . ", ";
        $query .= $db->quote($attribute_value_separator) . ",attribute_lang." . $db->quoteName('name') . " ORDER BY attribute_group_lang.";
        $query .= $db->quoteName('attribute_group_id') . " SEPARATOR " . $db->quote($attribute_separator).") as attribute_designation FROM ";
        $query .= $db->quoteName('#__jeproshop_product_attribute_combination') . " AS product_attribute_combination LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_attribute') . " AS attribute ON attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination.";
        $query .= $db->quoteName('attribute_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group ON attribute_group.";
        $query .= $db->quoteName('attribute_group_id') . " = attribute." . $db->quoteName('attribute_group_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang');
        $query .= " AS attribute_lang ON (attribute." . $db->quoteName('attribute_id') . " = attribute_lang." . $db->quoteName('attribute_id') . " AND attribute_lang.";
        $query .= $db->quoteName('lang_id') . " = " .(int)$lang_id . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang');
        $query .= " AS attribute_group_lang ON (attribute_group." . $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id');
        $query .= " AND attribute_group_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") WHERE product_attribute_combination.product_attribute_id IN (";
        $query .= implode(',', $product_attributes).") GROUP BY product_attribute_combination.product_attribute_id";

        $db->setQuery($query);
        $lang = $db->loadObjectList();

        foreach ($lang as $k => $row)
            $combinations[$k]->attribute_designation = $row->attribute_designation;


        //Get quantity of each variations
        foreach ($combinations as $key => $row){
            $cache_key = $row->product_id.'_'.$row->product_attribute_id.'_quantity';

            if (!JeproshopCache::isStored($cache_key))
                JeproshopCache::store(
                    $cache_key,
                    JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($row->product_id, $row->product_attribute_id)
                );

            $combinations[$key]->quantity = JeproshopCache::retrieve($cache_key);
        }

        return $combinations;
    }

    public function getCustomizationFieldIds()
    {
        if (!JeproshopCustomization::isFeaturePublished()) {
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
        if (!JeproshopCustomization::isFeaturePublished()){ return false; }

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


}