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

namespace Splash\Local\Objects\Users;

use Splash\Core\SplashCore      as Splash;

/**
 * Wordpress Users Core Data Access
 */
trait CoreTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Core Fields using FieldFactory
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function buildCoreFields()
    {
        global $wp_roles;
        
        //====================================================================//
        // Email
        $this->fieldsFactory()->Create(SPL_T_EMAIL)
            ->Identifier("user_email")
            ->Name(__("Email"))
            ->MicroData("http://schema.org/ContactPoint", "email")
            ->isRequired()
            ->isListed();
        
        //====================================================================//
        // User Role
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("roles")
            ->Name(__("Role"))
            ->MicroData("http://schema.org/Person", "jobTitle")
            ->isListed()
            ->AddChoices($wp_roles->get_names());
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
    
    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getCoreFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'user_email':
                $this->getSimple($fieldName);

                break;
            case 'roles':
                $userRoles  =    $this->object->roles;
                $this->out[$fieldName] = array_shift($userRoles);

                break;
            default:
                return;
        }
        
        unset($this->in[$key]);
    }
        
    //====================================================================//
    // Fields Writting Functions
    //====================================================================//
      
    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function setCoreFields($fieldName, $fieldData)
    {
        global $wp_roles;
        
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'user_email':
                $this->setSimple($fieldName, $fieldData);

                break;
            case 'roles':
                // Duplicate User Role Array
                $userRoles  =    $this->object->roles;
                // No Changes
                if (array_shift($userRoles) === $fieldData) {
                    break;
                }
                // Validate Role
                $roles = $wp_roles->get_names();
                if (!isset($roles[$fieldData])) {
                    Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Requested User Role Doesn't Exists.");

                    return;
                }
                $this->object->set_role($fieldData);
                $this->needUpdate();

                break;
            default:
                return;
        }
        
        unset($this->in[$fieldName]);
    }
}
