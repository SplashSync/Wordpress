<?php


namespace Splash\Local\Objects\Order;


use Splash\Core\SplashCore as Splash;
use Splash\Local\Local;

trait DokanTrait
{
    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    private function buildDokanFields()
    {
        //====================================================================//
        // Check if Dokan is active
        if (!Local::hasDokan()) {
            return;
        }

        //====================================================================//
        // Dolibarr Entity ID
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("vendor_id")
            ->name("Vendor ID")
            ->group("Meta")
            ->microData("http://schema.org/Author", "identifier")
            ->isReadOnly()
            ->setPreferNone()
            ->isNotTested();
        //====================================================================//
        // Dolibarr Entity Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("vendor_code")
            ->name("Vendor Code")
            ->group("Meta")
            ->microData("http://schema.org/Author", "alternateName")
            ->isReadOnly()
            ->isNotTested();
        //====================================================================//
        // Dolibarr Entity Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("vendor_name")
            ->name("Entity Name")
            ->group("Meta")
            ->microData("http://schema.org/Author", "name")
            ->isReadOnly()
            ->setPreferNone()
            ->isNotTested();

    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getDokanFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'vendor_id':
                $this->out[$fieldName] = $this->getDokanSellerId();

                break;
            case 'vendor_code':
                $seller = get_user_by("ID", $this->getDokanSellerId());
                $this->out[$fieldName] = $seller? $seller->user_login : "default";

                break;
            case 'vendor_name':
                $seller = get_user_by("ID", $this->getDokanSellerId());
                $this->out[$fieldName] = $seller?  $seller->display_name : "default";

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Safe Get Dokan Seller ID
     *
     * @return int
     */
    private function getDokanSellerId(): int
    {
        if (function_exists("dokan_get_seller_id_by_order")) {
            return dokan_get_seller_id_by_order($this->object->get_id());
        }

        return 0;
    }
}