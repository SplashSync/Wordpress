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

namespace Splash\Local;

use Splash\Core\SplashCore  as Splash;

/**
 * Worpress Splash Log Notifier Class
 */
final class Notifier
{
    /**
     * @var string
     */
    const NOTICE_FIELD = 'splash_admin_messages';

    /**
     * @var Notifier
     */
    private static $instance;

    protected function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Get a New Instance of Notifier
     *
     * @return self
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Register Post & Pages, Product Hooks
     *
     * @return void
     */
    public static function registerHooks()
    {
        add_action('admin_notices', array(self::class, 'displayAdminNotice'));
    }

    /**
     * Display an Admin Wp Notification
     *
     * @return void
     */
    public static function displayAdminNotice()
    {
        $option = get_option(self::NOTICE_FIELD);
        $message = isset($option['message']) ? $option['message'] : false;
        $noticeLevel = ! empty($option['notice-level']) ? $option['notice-level'] : 'notice-error';

        if ($message) {
            echo "<div class='notice {$noticeLevel} is-dismissible'><p>{$message}</p></div>";
            delete_option(self::NOTICE_FIELD);
        }
    }

    /**
     * Import Splash Log to Notifier After Background Action
     *
     * @return void
     */
    public function importLog()
    {
        $rawLog = Splash::log()->getRawLog();
        $type = null;
        $contents = null;

        //====================================================================//
        // Store Log - Debug
        if (!empty($rawLog->deb)) {
            $type = 'notice-info';
            $contents .= Splash::log()->getHtml($rawLog->deb);
        }
        //====================================================================//
        // Store Log - Messages
        if (!empty($rawLog->msg)) {
            $type = 'notice-success';
            $contents .= Splash::log()->getHtml($rawLog->msg, "", "#006600");
        }
        //====================================================================//
        // Store Log - Warnings
        if (!empty($rawLog->war)) {
            $type = 'notice-warning';
            $contents .= Splash::log()->getHtml($rawLog->war, "", "#FF9933");
        }
        //====================================================================//
        // Store Log - Errors
        if (!empty($rawLog->err)) {
            $type = 'notice-error';
            $contents .= Splash::log()->getHtml($rawLog->err, "", "#FF3300");
        }

        if (!empty($type) && !empty($contents)) {
            $this->updateOption($contents, $type);
        }
    }

    /**
     * Add Error Notification to Display
     *
     * @param string $message
     *
     * @return void
     */
    public function displayError($message)
    {
        $this->updateOption($message, 'notice-error');
    }

    /**
     * Add Warning Notification to Display
     *
     * @param string $message
     *
     * @return void
     */
    public function displayWarning($message)
    {
        $this->updateOption($message, 'notice-warning');
    }

    /**
     * Add Info Notification to Display
     *
     * @param string $message
     *
     * @return void
     */
    public function displayInfo($message)
    {
        $this->updateOption($message, 'notice-info');
    }

    /**
     * Add Success Notification to Display
     *
     * @param string $message
     *
     * @return void
     */
    public function displaySuccess($message)
    {
        $this->updateOption($message, 'notice-success');
    }

    /**
     * Update Notification Array
     *
     * @param string $message
     * @param string $noticeLevel
     *
     * @return void
     */
    protected function updateOption($message, $noticeLevel)
    {
        update_option(self::NOTICE_FIELD, array(
            'message' => $message,
            'notice-level' => $noticeLevel
        ));
    }
}
