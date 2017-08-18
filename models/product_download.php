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

class JeproshopProductDownloadModelProductDownload extends JeproshopModel {
    /** @var integer Product id which download belongs */
    public $product_download_id;

    public $product_id;

    /** @var string DisplayFilename the name which appear */
    public $display_filename;

    /** @var string PhysicallyFilename the name of the file on hard disk */
    public $filename;

    /** @var string DateDeposit when the file is upload */
    public $date_add;

    /** @var string DateExpiration deadline of the file */
    public $expiration_date;

    /** @var string NbDaysAccessible how many days the customer can access to file */
    public $nb_days_accessible;

    /** @var string NbDownloadable how many time the customer can download the file */
    public $nb_downloadable;

    /** @var boolean Active if file is accessible or not */
    public $published = 1;

    /** @var boolean is_sharable indicates whether the product can be shared */
    public $is_sharable = 0;

    protected static $_productIds = array();

    /**
     * Return the id_product_download from an id_product
     *
     * @param $productId
     * @return integer Product the id for this virtual product
     */
    public static function getProductDownloadIdFromProductId($productId){
        if (!JeproshopProductDownloadModelProductDownload::isFeaturePublished()){
            return false;
        }
        if (array_key_exists((int)$productId, self::$_productIds)){
            return self::$_productIds[$productId];
        }
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('product_download_id') . " FROM " . $db->quoteName('#__jeproshop_product_download');
        $query .= " WHERE " . $db->quoteName('product_id') . " = " .(int)$productId . " AND " . $db->quoteName('published') . " = 1 ";
        $query .= "	ORDER BY " . $db->quoteName('product_download_id') . " DESC";

        $db->setQuery($query);
        self::$_productIds[$productId] = (int)$db->loadResult();

        return self::$_productIds[$productId];
    }

    /**
     * This method is allow to know if a feature is used or active
     * 
     * @return bool
     */
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('virtual_product_feature_active');
    }
}