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
        if (!JeproshopCustomization::isFeaturePublished()){ return false; }

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
            if (!JeproshopCustomization::isFeaturePublished()){
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


}