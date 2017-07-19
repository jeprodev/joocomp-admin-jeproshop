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


require_once __DIR__ . DIRECTORY_SEPARATOR . 'cache.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'context.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'cookie.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'customization.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'defines.inc.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'tools.php';
require_once 'render' . DIRECTORY_SEPARATOR. 'helper.php';
require_once 'render' . DIRECTORY_SEPARATOR. 'calendar.php';
require_once 'render'. DIRECTORY_SEPARATOR . 'tree' . DIRECTORY_SEPARATOR. 'category.php';
require_once 'render'. DIRECTORY_SEPARATOR . 'uploader' . DIRECTORY_SEPARATOR. 'image.php';

$modelsPath =  dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;

require_once $modelsPath . 'legacy.php';
require_once $modelsPath . 'attribute.php';
require_once $modelsPath . 'address.php';
require_once $modelsPath . 'category.php';
require_once $modelsPath . 'carrier.php';
require_once $modelsPath . 'combination.php';
require_once $modelsPath . 'attachment.php';
require_once $modelsPath . 'country.php';
require_once $modelsPath . 'customer.php';
require_once $modelsPath . 'currency.php';
require_once $modelsPath . 'cart.php';
require_once $modelsPath . 'message.php';
require_once $modelsPath . 'connection.php';
require_once $modelsPath . 'employee.php';
require_once $modelsPath . 'group.php';
//require_once $modelsPath . '.php';
require_once $modelsPath . 'image.php';
require_once $modelsPath . 'image_type.php';
require_once $modelsPath . 'language.php';
require_once $modelsPath . 'order.php';
//require_once $modelsPath . '.php';
require_once $modelsPath . 'manufacturer.php';
require_once $modelsPath . 'feature.php';
require_once $modelsPath . 'product.php';
require_once $modelsPath . 'product_download.php';
require_once $modelsPath . 'product_supplier.php';
require_once $modelsPath . 'pack.php';
require_once $modelsPath . 'setting.php';
require_once $modelsPath . 'supplier.php';
require_once $modelsPath . 'specific_price.php';
//require_once $modelsPath . '.php';
require_once $modelsPath . 'shop.php';
require_once $modelsPath . 'shop_group.php';
//require_once $modelsPath . '.php';
//require_once $modelsPath . '.php';
require_once $modelsPath . 'tag.php';
require_once $modelsPath . 'tax.php';
require_once $modelsPath . 'tax_factory.php';
require_once $modelsPath . 'stock.php';
require_once $modelsPath . 'stock_mvt_reason.php';

require_once  dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'JeproshopViewLegacy.php';