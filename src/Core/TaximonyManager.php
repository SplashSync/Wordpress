<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Core;

use Splash\Core\SplashCore      as Splash;
use WP_Error;
use WP_Term;

/**
 * Wordpress Taximony Manager
 */
class TaximonyManager
{
    //====================================================================//
    // TAXIMONY READINGS
    //====================================================================//

    /**
     * Get List of Post Taximony Slugs
     *
     * @param int    $postId   Post Id
     * @param string $taxonomy Taximony Code
     *
     * @return array Taximony Slugs List
     */
    public static function getSlugs($postId, $taxonomy)
    {
        //====================================================================//
        // Ensure Taximony is Valid
        if (!is_string($taxonomy) || empty($taxonomy) || !taxonomy_exists($taxonomy)) {
            return array();
        }
        //====================================================================//
        // Get Slugs List
        $terms = get_terms(array(
            'taxonomy' => array( $taxonomy ),
            'object_ids' => array( $postId ),
            'fields' => 'id=>slug',
        ));
        //====================================================================//
        // Safety Check
        if (is_wp_error($terms) || ($terms instanceof WP_Error)) {
            Splash::log()->errTrace("Unable to Search for Taximony Slugs. ".$terms->get_error_message());

            return array();
        }

        return is_array($terms) ? $terms : array();
    }

    /**
     * Get List of Post Taximony Names
     *
     * @param int    $postId   Post Id
     * @param string $taxonomy Taximony Code
     *
     * @return array Taximony Slugs List
     */
    public static function getNames($postId, $taxonomy)
    {
        //====================================================================//
        // Ensure Taximony is Valid
        if (!is_string($taxonomy) || empty($taxonomy) || !taxonomy_exists($taxonomy)) {
            return array();
        }
        //====================================================================//
        // Get Slugs List
        $terms = get_terms(array(
            'taxonomy' => array( $taxonomy ),
            'object_ids' => array( $postId ),
            'fields' => 'id=>name',
        ));
        //====================================================================//
        // Safety Check
        if (is_wp_error($terms) || ($terms instanceof WP_Error)) {
            Splash::log()->errTrace("Unable to Search for Taximony Names. ".$terms->get_error_message());

            return array();
        }

        return is_array($terms) ? $terms : array();
    }

    //====================================================================//
    // TAXIMONY WRITING
    //====================================================================//

    /**
     * Set List of Post Taximony Slugs
     *
     * @param int    $postId   Post Id
     * @param string $taxonomy Taximony Code
     * @param array  $slugs    List of Slugs
     *
     * @return bool
     */
    public static function setSlugs($postId, $taxonomy, $slugs)
    {
        $slugs = is_array($slugs) ? $slugs : array();
        //====================================================================//
        // Ensure Taxi is Valid
        if (!is_string($taxonomy) || empty($taxonomy) || !taxonomy_exists($taxonomy)) {
            return false;
        }
        //====================================================================//
        // Get Current Slugs List
        $currentTerms = self::getSlugs($postId, $taxonomy);
        //====================================================================//
        // Walk on Slugs List for ADD
        foreach ($slugs as $slug) {
            //====================================================================//
            // NOT Already Associated
            if (!in_array($slug, $currentTerms, true)) {
                self::addBy("slug", $postId, $taxonomy, $slug);
            }
        }
        //====================================================================//
        // Walk on Current List for REMOVE
        foreach ($currentTerms as $termId => $slug) {
            //====================================================================//
            // NOT Already Associated
            if (!in_array($slug, $slugs, true)) {
                wp_remove_object_terms($postId, $termId, $taxonomy);
            }
        }

        return true;
    }

    /**
     * Set List of Post Taximony Names
     *
     * @param int    $postId   Post Id
     * @param string $taxonomy Taximony Code
     * @param array  $names    List of Names
     *
     * @return bool
     */
    public static function setNames($postId, $taxonomy, $names)
    {
        $names = is_array($names) ? $names : array();
        //====================================================================//
        // Ensure Taxi is Valid
        if (!is_string($taxonomy) || empty($taxonomy) || !taxonomy_exists($taxonomy)) {
            return false;
        }
        //====================================================================//
        // Get Current Names List
        $currentTerms = self::getNames($postId, $taxonomy);
        //====================================================================//
        // Walk on Names List for ADD
        foreach ($names as $name) {
            //====================================================================//
            // NOT Already Associated
            if (!in_array($name, $currentTerms, true)) {
                self::addBy("name", $postId, $taxonomy, $name);
            }
        }
        //====================================================================//
        // Walk on Current List for REMOVE
        foreach ($currentTerms as $termId => $name) {
            //====================================================================//
            // NOT Already Associated
            if (!in_array($name, $names, true)) {
                wp_remove_object_terms($postId, $termId, $taxonomy);
            }
        }

        return true;
    }

    //====================================================================//
    // TAXIMONY LISTING
    //====================================================================//

    /**
     * Get List of Available Post Taximony Slugs
     *
     * @param string $taxonomy Taximony Code
     *
     * @return array Taximony Slugs List
     */
    public static function getSlugsChoices($taxonomy)
    {
        //====================================================================//
        // Ensure Taximony is Valid
        if (!is_string($taxonomy) || empty($taxonomy) || !taxonomy_exists($taxonomy)) {
            return array();
        }
        //====================================================================//
        // Get Slugs List
        $terms = get_terms(array(
            'taxonomy' => array( $taxonomy ),
            'orderby' => 'id',
            'order' => 'ASC',
            'hide_empty' => false,
        ));
        //====================================================================//
        // Safety Check
        if (!is_array($terms)) {
            return array();
        }
        $choices = array();
        foreach ($terms as $term) {
            //====================================================================//
            // Safety Check
            if (!($term instanceof WP_Term)) {
                continue;
            }
            $choices[$term->slug] = $term->name;
        }

        return $choices;
    }

    /**
     * Get List of Available Post Taximony Names
     *
     * @param string $taxonomy Taximony Code
     *
     * @return array Taximony Slugs List
     */
    public static function getNamesChoices($taxonomy)
    {
        //====================================================================//
        // Ensure Taximony is Valid
        if (!is_string($taxonomy) || empty($taxonomy) || !taxonomy_exists($taxonomy)) {
            return array();
        }
        //====================================================================//
        // Get Slugs List
        $terms = get_terms(array(
            'taxonomy' => array($taxonomy),
            'orderby' => 'id',
            'order' => 'ASC',
            'hide_empty' => false,
        ));
        //====================================================================//
        // Safety Check
        if (!is_array($terms)) {
            return array();
        }
        $choices = array();
        foreach ($terms as $term) {
            //====================================================================//
            // Safety Check
            if (!($term instanceof WP_Term)) {
                continue;
            }
            $choices[$term->name] = $term->name;
        }

        return $choices;
    }

    //====================================================================//
    // PRIVATE METHODS
    //====================================================================//

    /**
     * Add a Slug to Post Taximony
     *
     * @param string $field    Filed used for Association
     * @param int    $postId   Post Id
     * @param string $taxonomy Taximony Code
     * @param string $slug     Slug to Associate
     *
     * @return null|int
     */
    public static function addBy($field, $postId, $taxonomy, $slug)
    {
        //====================================================================//
        // Search for Wp Term
        $term = get_term_by($field, $slug, $taxonomy);
        if (!($term instanceof WP_Term)) {
            return null;
        }
        //====================================================================//
        // Add Post to Taximony
        wp_set_post_terms($postId, $term->term_id, $taxonomy, true);

        return $term->term_id;
    }
}
