<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\ImagesTrait;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use WP_Post;

/**
 * Wordpress Page Object
 */
class Page extends AbstractObject
{
    use IntelParserTrait;
    use ObjectsTrait;
    use ImagesTrait;
    use SimpleFieldsTrait;
    
    use \Splash\Local\Objects\Post\CRUDTrait;
    use \Splash\Local\Objects\Post\CoreTrait;
    use \Splash\Local\Objects\Post\MetaTrait;
    use \Splash\Local\Objects\Post\ThumbTrait;
    use \Splash\Local\Objects\Post\TaxTrait;
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
    protected static $NAME            =  "Page";
    
    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION     =  "Wordpress Page Object";
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-file";
    
    //====================================================================//
    // General Class Variables
    //====================================================================//
    
    protected $postType = "page";
    
    //====================================================================//
    // Class Main Functions
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);

        $data       = array();
        $statuses   = get_page_statuses();
        
        //====================================================================//
        // Load Data From DataBase
        $rawData = get_posts(array(
            'post_type'         =>      $this->postType,
            'post_status'       =>      array_keys(get_post_statuses()),
            's'                 =>      (!empty($filter)  ? $filter : ''),
            'numberposts'       =>      (!empty($params["max"])        ? $params["max"] : 10),
            'offset'            =>      (!empty($params["offset"])     ? $params["offset"] : 0),
            'orderby'           =>      (!empty($params["sortfield"])  ? $params["sortfield"] : 'id'),
            'order'             =>      (!empty($params["sortorder"])  ? $params["sortorder"] : 'ASC'),
        ));
        
        //====================================================================//
        // Store Meta Total & Current values
        $totals     =   wp_count_posts('page');
        $data["meta"]["total"]      =   $totals->publish + $totals->future + $totals->draft;
        $data["meta"]["total"]     +=   $totals->pending + $totals->private + $totals->trash;
        $data["meta"]["current"]    =   count($rawData);
        
        //====================================================================//
        // For each result, read information and add to $data
        /** @var WP_Post $page */
        foreach ($rawData as $page) {
            $data[] = array(
                "id"            =>  $page->ID,
                "post_title"    =>  $page->post_title,
                "post_name"     =>  $page->post_name,
                "post_status"   =>  (isset($statuses[$page->post_status]) ? $statuses[$page->post_status] : "...?"),
            );
        }
        
        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " " . count($rawData) . " Pages Found.");

        return $data;
    }
}
