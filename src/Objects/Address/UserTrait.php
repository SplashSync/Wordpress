<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
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
 * WordPress Users Address User Link Access
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
    protected function buildUserFields(): void
    {
        //====================================================================//
        // Customer
        $this->fieldsFactory()->create((string) self::objects()->encode("ThirdParty", SPL_T_ID))
            ->identifier("user")
            ->name(__("Customer"))
            ->microData("http://schema.org/Organization", "ID")
            ->isReadOnly()
        ;
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
    protected function getUserFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'user':
                $this->out[$fieldName] = self::objects()
                    ->encode("ThirdParty", (string) $this->object->ID)
                ;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
