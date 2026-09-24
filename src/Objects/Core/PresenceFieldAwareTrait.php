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

namespace Splash\Local\Objects\Core;

/**
 * Add Splash Presence Decision Field to Objects
 */
trait PresenceFieldAwareTrait
{
    /**
     * Build Presence Decision Field using FieldFactory
     */
    protected function buildPresenceDeciderFields(): void
    {
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("splash_presence")
            ->name("Presence")
            ->group("Meta")
            ->description("Presence Decision Field")
            ->microData("http://splashync.com/schemas", "Presence")
            ->isWriteOnly()
            ->setPreferWrite()
            ->isNotTested()
        ;
    }

    /**
     * Common Reading of a Presence Decision Field
     * Just for fixing Phpunit, never ued
     */
    protected function getPresenceDeciderFields(string $key, string $fieldName): self
    {
        if ("splash_presence" === $fieldName) {
            unset($this->in[$key]);
        }

        return $this;
    }

    /**
     * Common Writing of a Presence Decision Field
     *
     * @SuppressWarnings(UnusedFormalParameter)
     */
    protected function setPresenceDeciderFields(string $fieldName, float $fieldData): self
    {
        if ("splash_presence" === $fieldName) {
            unset($this->in[$fieldName]);
        }

        return $this;
    }
}
