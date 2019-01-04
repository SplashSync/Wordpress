<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2017 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/
                    
namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;

use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\PricesTrait;
use Splash\Models\Objects\ImagesTrait;
use Splash\Models\Objects\ListsTrait;

/**
 * @abstract    WooCommerce Order Object
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Order extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;
    use PricesTrait;
    use ImagesTrait;
    use ListsTrait;
    
    // Core Fields
    use \Splash\Local\Objects\Core\WooCommerceObjectTrait;      // Trigger WooCommerce Module Activation
    
    // Post Fields
//    use \Splash\Local\Objects\Post\CRUDTrait;                   // Objects CRUD
//    use \Splash\Local\Objects\Post\HooksTrait;                  // Wordpress Events
//    use \Splash\Local\Objects\Post\MetaTrait;                   // Object MetaData
//    use \Splash\Local\Objects\Post\ThumbTrait;                  // Thumbnail Image
    
    // WooCommerce Order Field
    use \Splash\Local\Objects\Order\CRUDTrait;                  // Objects CRUD
    use \Splash\Local\Objects\Order\HooksTrait;                 // Objects CRUD
    use \Splash\Local\Objects\Order\CoreTrait;                  // Order Core Infos
    use \Splash\Local\Objects\Order\ItemsTrait;                 // Order Items List
    use \Splash\Local\Objects\Order\PaymentsTrait;              // Order Payments List
    use \Splash\Local\Objects\Order\TotalsTrait;                // Order Totals
    use \Splash\Local\Objects\Order\StatusTrait;                // Order Status Infos
    use \Splash\Local\Objects\Order\AddressTrait;               // Order Billing & Delivery Infos
    use \Splash\Local\Objects\Order\BookingTrait;               // Order Booking Infos
    
    // Products Fields
//    use \Splash\Local\Objects\Product\CoreTrait;
//    use \Splash\Local\Objects\Product\MainTrait;
//    use \Splash\Local\Objects\Product\StockTrait;
//    use \Splash\Local\Objects\Product\PriceTrait;
//    use \Splash\Local\Objects\Product\ImagesTrait;
    
    
    //====================================================================//
    // Object Definition Parameters
    //====================================================================//
    
    /**
     *  Object Disable Flag. Uncomment this line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME            =  "Order";
    
    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION     =  "WooCommerce Order Object";
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-shopping-cart";
    
    /**
     *  Object Synchronization Recommended Configuration
     */
    // Enable Creation Of New Local Objects when Not Existing
    protected static $ENABLE_PUSH_CREATED       =  false;
    // Enable Update Of Existing Local Objects when Modified Remotly
    protected static $ENABLE_PUSH_UPDATED       =  false;
    // Enable Delete Of Existing Local Objects when Deleted Remotly
    protected static $ENABLE_PUSH_DELETED       =  false;
        
    //====================================================================//
    // General Class Variables
    //====================================================================//
    
    protected $postType = "shop_order";
    
    /**
    *   @abstract     Return List Of Customer with required filters
    *   @param        array   $filter               Filters for Customers List.
    *   @param        array   $params              Search parameters for result List.
    *                         $params["max"]       Maximum Number of results
    *                         $params["offset"]    List Start Offset
    *                         $params["sortfield"] Field name for sort list (Available fields listed below)
    *                         $params["sortorder"] List Order Constraign (Default = ASC)
    *   @return       array   $data             List of all customers main data
    *                         $data["meta"]["total"]     ==> Total Number of results
    *                         $data["meta"]["current"]   ==> Total Number of results
    */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);

        $data       = array();
        $statuses   = get_page_statuses();
        
        //====================================================================//
        // Load Dta From DataBase
        $RawData = get_posts([
            'post_type'         =>      $this->postType,
            'post_status'       =>      array_keys(wc_get_order_statuses()),
            'numberposts'       =>      ( !empty($params["max"])        ? $params["max"] : 10  ),
            'offset'            =>      ( !empty($params["offset"])     ? $params["offset"] : 0  ),
            'orderby'           =>      ( !empty($params["sortfield"])  ? $params["sortfield"] : 'id'  ),
            'order'             =>      ( !empty($params["sortorder"])  ? $params["sortorder"] : 'ASC' ),
            's'                 =>      ( !empty($filter)  ? $filter : '' ),
        ]);
        
        //====================================================================//
        // Store Meta Total & Current values
        $data["meta"]["total"]      =   array_sum((array) wp_count_posts('shop_order'));
        $data["meta"]["current"]    =   count($RawData);
        
        //====================================================================//
        // For each result, read information and add to $data
        foreach ($RawData as $Order) {
            $data[] = array(
                "id"            =>  $Order->ID,
                "post_title"    =>  $Order->post_title,
                "post_name"     =>  $Order->post_name,
                "post_status"   =>  ( isset($statuses[$Order->post_status]) ? $statuses[$Order->post_status] : "...?" ),
                "total"         =>  get_post_meta($Order->ID, "_order_total", true),
                "reference"     =>  "#" . $Order->ID
//                "_stock"        =>  get_post_meta( $this->object->ID, "_stock", True ),
//                "_price"        =>  get_post_meta( $this->object->ID, "_price", True ),
//                "_regular_price"=>  get_post_meta( $this->object->ID, "_regular_price", True ),
            );
        }
        
        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " " . count($RawData) . " Orders Found.");
        return $data;
    }
}
