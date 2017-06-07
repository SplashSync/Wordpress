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

/**
 * @abstract    Wordpress Customer Object
 */
class ThirdParty extends AbstractObject
{
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use \Splash\Local\Objects\Users\CRUDTrait;
    use \Splash\Local\Objects\Users\CoreTrait;
    use \Splash\Local\Objects\Users\MetaTrait;
    use \Splash\Local\Objects\Users\HooksTrait;
    
    //====================================================================//
    // Object Definition Parameters	
    //====================================================================//
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static    $NAME            =  "ThirdParty";
    
    /**
     *  Object Description (Translated by Module) 
     */
    protected static    $DESCRIPTION     =  "Wordpress Customer Object";    
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag) 
     */
    protected static    $ICO     =  "fa fa-user";
      
    /**
     *  Object Synchronization Recommended Configuration 
     */
    protected static    $ENABLE_PUSH_CREATED       =  FALSE;        // Enable Creation Of New Local Objects when Not Existing
       
    //====================================================================//
    // Class Main Functions
    //====================================================================//
    
    /**
     * {@inheritdoc}
    */
    public function ObjectsList( $filter = NULL , $params = NULL )
    {
        //====================================================================//
        // Stack Trace
        Splash::Log()->Trace(__CLASS__,__FUNCTION__);  
        $data       = array();
        //====================================================================//
        // Load Dta From DataBase
        $RawData = get_users([
//            'role__in'     =>      ['administrator'],
            'numberposts'       =>      ( !empty($params["max"])        ? $params["max"] : 10  ),
            'offset'            =>      ( !empty($params["offset"])     ? $params["offset"] : 0  ),
            'orderby'           =>      ( !empty($params["sortfield"])  ? $params["sortfield"] : 'id'  ),
            'order'             =>      ( !empty($params["sortorder"])  ? $params["sortorder"] : 'ASC' ),
        ]);
        //====================================================================//
        // Store Meta Total & Current values 
        $Totals = count_users();
        $data["meta"]["total"]      =   $Totals['total_users'];  
        $data["meta"]["current"]    =   count($RawData);
        //====================================================================//
        // For each result, read information and add to $data
        foreach ($RawData as $User) {
            $data[] = array(
                "id"            =>  $User->ID,
                "user_login"    =>  $User->user_login,
                "user_email"    =>  $User->user_email,
            );
        }
        Splash::Log()->Deb("MsgLocalTpl",__CLASS__,__FUNCTION__, " " . count($RawData) . " Users Found.");
        return $data;
    }
}




?>
