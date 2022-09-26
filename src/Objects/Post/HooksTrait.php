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

namespace Splash\Local\Objects\Post;

use Exception;
use Splash\Client\Splash      as Splash;
use Splash\Local\Core\PrivacyManager;
use Splash\Local\Notifier;
use Splash\Local\Objects\Product;
use WP_Post;

/**
 * WordPress Post Hook Manager
 */
trait HooksTrait
{
    /**
     * @var string
     */
    private static string $postClass = "\\Splash\\Local\\Objects\\Post";

    /**
     * Register Post & Pages, Product Hooks
     *
     * @return void
     */
    public static function registerHooks(): void
    {
        //====================================================================//
        // Setup Post Saved Hook
        $createCall = array( self::$postClass , "updated");
        if (is_callable($createCall)) {
            add_action('save_post', $createCall, 10, 3);
        }
        //====================================================================//
        // Setup Post Deleted Hook
        $deleteCall = array( self::$postClass , "deleted");
        if (is_callable($deleteCall)) {
            // add_action('before_delete_post', $deleteCall, 10, 1);
            add_action('deleted_post', $deleteCall, 10, 1);
        }
    }

    /**
     * Main Post Updated Hook Action
     *
     * @param int|string $postId
     * @param WP_Post    $post
     * @param bool       $updated
     *
     * @throws Exception
     *
     * @return void
     */
    public static function updated($postId, WP_Post $post, bool $updated)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Safety Checks
        if (!self::isUpdatedCommitAllowed($postId, $post)) {
            return;
        }
        //====================================================================//
        // Prepare Commit Parameters
        $action = $updated ? SPL_A_UPDATE : SPL_A_CREATE;
        $objectType = self::getSplashType($post);
        if (!is_string($objectType)) {
            return;
        }
        $comment = $objectType.($updated ? " Updated" : " Created")." on Wordpress";
        //====================================================================//
        // Catch Wc Actions on variable products
        if (("product" == $post->post_type) && did_action('woocommerce_init')) {
            $postId = Product::getIdsForCommit((int) $postId);
        }
        //====================================================================//
        // Check Commit is Allowed
        if (!self::isCommitAllowed($post->post_type, $objectType, $action)) {
            return;
        }
        //====================================================================//
        // Do Commit
        Splash::commit($objectType, $postId, $action, "Wordpress", $comment);
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }

    /**
     * Post Updated Hook Pre-Checks
     *
     * @param int|string $postId
     * @param WP_Post    $post
     *
     * @return bool
     */
    public static function isUpdatedCommitAllowed($postId, WP_Post $post): bool
    {
        //====================================================================//
        // Check Id is Not Empty
        if (empty($postId)) {
            return false;
        }
        //====================================================================//
        // Check Post is Not a Auto-Draft
        if ("auto-draft" == $post->post_status) {
            return false;
        }
        //====================================================================//
        // Check Order Not Anonymize
        if (PrivacyManager::isAnonymize($post)) {
            Splash::log()->war("Commit is Disabled for Anonymize Data");

            return false;
        }

        return true;
    }

    /**
     * Detect Splash Object Type Name
     *
     * @param WP_Post $post
     *
     * @return null|string
     */
    public static function getSplashType(WP_Post $post): ?string
    {
        switch ($post->post_type) {
            //====================================================================//
            // Core Wp Objects Types
            case "post":
                return "Post";
            case "page":
                return "Page";
                //====================================================================//
                // WooCommerce Objects Types
            case "product":
            case "product_variation":
                return "Product";
            case "shop_order":
                return "Order";
        }
        Splash::log()->deb("Unknown Object Type => ".$post->post_type);

        return null;
    }

    /**
     * Main Post Deleted Hook Action
     *
     * @param int|string $postId
     *
     * @return void
     */
    public static function deleted($postId): void
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        /** @var WP_Post $post */
        $post = get_post((int) $postId);
        if ("post" == $post->post_type) {
            Splash::commit("Post", $postId, SPL_A_DELETE, "Wordpress", "Post Deleted");
        }
        if ("page" == $post->post_type) {
            Splash::commit("Page", $postId, SPL_A_DELETE, "Wordpress", "Page Deleted");
        }
        if ("product" == $post->post_type) {
            $postIds = Product::getIdsForCommit((int) $postId);
            Splash::commit("Product", $postIds, SPL_A_DELETE, "Wordpress", "Product Deleted");
        }
        if ("product_variation" == $post->post_type) {
            Splash::commit(
                "Product",
                Product::getMultiLangMaster((int) $postId),
                SPL_A_DELETE,
                "Wordpress",
                "Product Deleted"
            );
        }
        if ("shop_order" == $post->post_type) {
            Splash::commit("Invoice", $postId, SPL_A_DELETE, "Wordpress", "Invoice Deleted");
            Splash::commit("Order", $postId, SPL_A_DELETE, "Wordpress", "Order Deleted");
        }
        //====================================================================//
        // Store User Messages
        Notifier::getInstance()->importLog();
    }

    /**
     * Check if Commit is Allowed
     *
     * @param string $postType
     * @param string $objectType
     * @param string $action
     *
     * @throws Exception
     *
     * @return bool
     */
    private static function isCommitAllowed(string $postType, string $objectType, string $action): bool
    {
        //====================================================================//
        // Prevent Commit on Variant Product Create
        if (("product" == $postType)
                && (SPL_A_CREATE == $action)
                && Splash::object($objectType)->isLocked("onVariantCreate")) {
            return false;
        }
        //====================================================================//
        // Prevent Repeated Commit if Needed
        if ((SPL_A_UPDATE == $action) && Splash::object($objectType)->isLocked()) {
            return false;
        }

        return true;
    }
}
