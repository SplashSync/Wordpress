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

/**
 * Wordpress Posts Counter
 */
trait CounterTrait
{
    /**
     * Count Number of Posts By Type
     *
     * @param array     $postTypes
     * @param null|bool $includeTrash
     *
     * @return int
     */
    protected function countPostsByTypes(array $postTypes = array("post"), bool $includeTrash = null): int
    {
        $total = 0;
        //====================================================================//
        // Walk on Posts Types
        foreach ($postTypes as $postType) {
            // Get Posts Counts by Statues
            $counters = (array) wp_count_posts($postType);
            if (empty($includeTrash)) {
                $counters["trash"] = 0;
            }
            $total += (int) array_sum($counters);
        }

        return $total;
    }
}
