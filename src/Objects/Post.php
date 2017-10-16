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
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\ImagesTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * @abstract    Wordpress Page Object
 */
class Post extends AbstractObject
{
    use IntelParserTrait;
    use ObjectsTrait;
    use ImagesTrait;
    use SimpleFieldsTrait;
    
    // Post Fields
    use \Splash\Local\Objects\Post\CRUDTrait;
    use \Splash\Local\Objects\Post\CoreTrait;
    use \Splash\Local\Objects\Post\MetaTrait;
    use \Splash\Local\Objects\Post\ThumbTrait;
    use \Splash\Local\Objects\Post\TaxTrait;
    use \Splash\Local\Objects\Post\HooksTrait;
    use \Splash\Local\Objects\Post\CustomTrait;                 // Custom Fields

    //====================================================================//
    // Object Definition Parameters	
    //====================================================================//
    
    /**
     *  Object Disable Flag. Uncomment thius line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static    $NAME            =  "Post";
    
    /**
     *  Object Description (Translated by Module) 
     */
    protected static    $DESCRIPTION     =  "Wordpress Post Object";    
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag) 
     */
    protected static    $ICO     =  "fa fa-rss-square";
    
    //====================================================================//
    // General Class Variables	
    //====================================================================//
    
    var $post_type = "post";
    
    //====================================================================//
    // Class Constructor
    //====================================================================//
        
    /**
     *      @abstract       Class Constructor (Used only if localy necessary)
     *      @return         int                     0 if KO, >0 if OK
     */
    function __construct()
    {
        return True;
    }    
    
    //====================================================================//
    // Class Main Functions
    //====================================================================//

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
    public function ObjectsList($filter=NULL,$params=NULL)
    {
        //====================================================================//
        // Stack Trace
        Splash::Log()->Trace(__CLASS__,__FUNCTION__);  

        $data       = array();
        $statuses   = get_page_statuses();
        
        //====================================================================//
        // Load Dta From DataBase
        $RawData = get_posts([
            'post_type'         =>      $this->post_type,
            'post_status'       =>      array_keys(get_post_statuses()),
            'numberposts'       =>      ( !empty($params["max"])        ? $params["max"] : 10  ),
            'offset'            =>      ( !empty($params["offset"])     ? $params["offset"] : 0  ),
            'orderby'           =>      ( !empty($params["sortfield"])  ? $params["sortfield"] : 'id'  ),
            'order'             =>      ( !empty($params["sortorder"])  ? $params["sortorder"] : 'ASC' ),
            's'                 =>      ( !empty($filter)  ? $filter : '' ),
        ]);
        
        //====================================================================//
        // Store Meta Total & Current values 
        $Totals     =   wp_count_posts('post');
        $data["meta"]["total"]      =   $Totals->publish + $Totals->future + $Totals->draft + $Totals->pending + $Totals->private + $Totals->trash;  
        $data["meta"]["current"]    =   count($RawData);
        
        //====================================================================//
        // For each result, read information and add to $data
        foreach ($RawData as $Page) {
            $data[] = array(
                "id"            =>  $Page->ID,
                "post_title"    =>  $Page->post_title,
                "post_name"     =>  $Page->post_name,
                "post_status"   =>  ( isset($statuses[$Page->post_status]) ? $statuses[$Page->post_status] : "...?" ),
            );
        }
        
        Splash::Log()->Deb("MsgLocalTpl",__CLASS__,__FUNCTION__, " " . count($RawData) . " Post Found.");
        return $data;
    }
    
}




?>
