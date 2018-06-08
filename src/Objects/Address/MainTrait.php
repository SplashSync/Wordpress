<?php
/*
 * Copyright (C) 2017   Splash Sync       <contact@splashsync.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

namespace Splash\Local\Objects\Address;

/**
 * @abstract    Wordpress Users Address Main Data Access
 */
trait MainTrait
{
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Main Fields using FieldFactory
    */
    private function buildMainFields()
    {

        //====================================================================//
        // Company
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("company")
                ->Name(__("Company"))
                ->MicroData("http://schema.org/Organization", "legalName");
        
        //====================================================================//
        // Firstname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("first_name")
                ->Name(__("First Name"))
                ->MicroData("http://schema.org/Person", "familyName")
                ->Association("first_name", "last_name")
                ->isListed();
        
        //====================================================================//
        // Lastname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("last_name")
                ->Name(__("Last Name"))
                ->MicroData("http://schema.org/Person", "givenName")
                ->Association("first_name", "last_name")
                ->isListed();
        
        //====================================================================//
        // Addess
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("address_1")
                ->Name(__("Address line 1"))
                ->isLogged()
                ->MicroData("http://schema.org/PostalAddress", "streetAddress");

        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("postcode")
                ->Name(__("Postcode / ZIP"))
                ->MicroData("http://schema.org/PostalAddress", "postalCode")
                ->isLogged()
                ->isListed();
        
        //====================================================================//
        // City Name
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("city")
                ->Name(__("City"))
                ->MicroData("http://schema.org/PostalAddress", "addressLocality")
                ->isListed();
        
        //====================================================================//
        // Country Name
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//                ->Identifier("country")
//                ->Name($langs->trans("CompanyCountry"))
//                ->isReadOnly()
//                ->Group($GroupName)
//                ->isListed();
        
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->Create(SPL_T_COUNTRY)
                ->Identifier("country")
                ->Name(__("Country"))
                ->isLogged()
                ->MicroData("http://schema.org/PostalAddress", "addressCountry");

        //====================================================================//
        // State code
        $this->fieldsFactory()->Create(SPL_T_STATE)
                ->Identifier("state")
                ->Name(__("State / County"))
                ->MicroData("http://schema.org/PostalAddress", "addressRegion")
                ->isNotTested();

        //====================================================================//
        // Phone Pro
        $this->fieldsFactory()->Create(SPL_T_PHONE)
                ->Identifier("phone")
                ->Name(__("Phone"))
                ->MicroData("http://schema.org/Person", "telephone")
                ->isLogged()
                ->isListed();

        //====================================================================//
        // Email
        $this->fieldsFactory()->Create(SPL_T_EMAIL)
                ->Identifier("email")
                ->Name(__("Email address"))
                ->MicroData("http://schema.org/ContactPoint", "email")
                ->isLogged()
                ->isListed();
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
    
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return         none
     */
    private function getMainFields($Key, $FieldName)
    {
  
        //====================================================================//
        // Check Address Type Is Defined
        if (empty($this->AddressType)) {
            return;
        }
        
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case 'company':
            case 'first_name':
            case 'last_name':
            case 'address_1':
            case 'postcode':
            case 'city':
            case 'country':
            case 'state':
                $this->Out[$FieldName] = get_user_meta($this->Object->ID, $this->EncodeFieldId($FieldName), true);
                break;
            
            case 'phone':
            case 'email':
                $this->Out[$FieldName] = get_user_meta($this->Object->ID, $this->EncodeFieldId($FieldName, $this->Billing), true);
                break;
            
            default:
                return;
        }
        
        unset($this->In[$Key]);
    }
        
    //====================================================================//
    // Fields Writting Functions
    //====================================================================//
      
    /**
     *  @abstract     Write Given Fields
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Data                   Field Data
     *
     *  @return         none
     */
    private function setMainFields($FieldName, $Data)
    {
        //====================================================================//
        // Check Address Type Is Defined
        if (empty($this->AddressType)) {
            return;
        }
        
        //====================================================================//
        // WRITE Field
        switch ($FieldName) {
            case 'company':
            case 'first_name':
            case 'last_name':
            case 'address_1':
            case 'postcode':
            case 'city':
            case 'country':
            case 'state':
                $this->setUserMeta($this->EncodeFieldId($FieldName), $Data);
                break;
            
            case 'phone':
            case 'email':
                $this->setUserMeta($this->EncodeFieldId($FieldName, $this->Billing), $Data);
                break;

            
            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
}
