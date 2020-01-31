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

namespace Splash\Local\Objects\Address;

/**
 * Wordpress Users Address User Link Access
 */
trait UserTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Address User Link Fields using FieldFactory
     *
     * @return void
     */
    private function buildUserFields()
    {
        //====================================================================//
        // Customer
        $this->fieldsFactory()->Create((string) self::objects()->Encode("ThirdParty", SPL_T_ID))
            ->Identifier("user")
            ->Name(__("Customer"))
            ->MicroData("http://schema.org/Organization", "ID")
            ->isReadOnly();
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
    private function getUserFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'user':
                $this->out[$fieldName] = self::objects()->Encode("ThirdParty", $this->object->ID);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
