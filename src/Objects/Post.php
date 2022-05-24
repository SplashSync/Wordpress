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

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\ImagesTrait;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use WP_Post;

/**
 * WordPress Page Object
 */
class Post extends AbstractObject
{
    use IntelParserTrait;
    use ObjectsTrait;
    use ImagesTrait;
    use SimpleFieldsTrait;

    // Post Fields
    use Post\CRUDTrait;
    use Post\CoreTrait;
    use Post\MetaTrait;
    use Post\ThumbTrait;
    use Post\TaxTrait;
    use Post\HooksTrait;
    use Post\CustomTrait;                 // Custom Fields

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $name = "Post";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $description = "Wordpress Post Object";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-rss-square";

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var WP_Post;
     */
    protected object $object;

    /**
     * @var string
     */
    protected string $postType = "post";

    //====================================================================//
    // Class Main Functions
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        $data = array();
        $statuses = get_page_statuses();

        //====================================================================//
        // Load Data From DataBase
        $rawData = get_posts(array(
            'post_type' => $this->postType,
            'post_status' => array_keys(get_post_statuses()),
            'numberposts' => (!empty($params["max"])        ? $params["max"] : 10),
            'offset' => (!empty($params["offset"])     ? $params["offset"] : 0),
            'orderby' => (!empty($params["sortfield"])  ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"])  ? $params["sortorder"] : 'ASC'),
            's' => (!empty($filter)  ? $filter : ''),
        ));

        //====================================================================//
        // Store Meta Total & Current values
        $totals = wp_count_posts('post');
        $data["meta"]["total"] = $totals->publish + $totals->future + $totals->draft;
        $data["meta"]["total"] += $totals->pending + $totals->private + $totals->trash;
        $data["meta"]["current"] = count($rawData);

        //====================================================================//
        // For each result, read information and add to $data
        /** @var WP_Post $post */
        foreach ($rawData as $post) {
            $data[] = array(
                "id" => $post->ID,
                "post_title" => $post->post_title,
                "post_name" => $post->post_name,
                "post_status" => ($statuses[$post->post_status] ?? "...?"),
            );
        }

        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " ".count($rawData)." Post Found.");

        return $data;
    }
}
