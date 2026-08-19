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

/**
 * Splash Settings Screen — progressive disclosure of dependent fields.
 *
 * Map: master checkbox field id => list of dependent field ids.
 * Dependent rows are hidden while the master checkbox is unchecked.
 */
(function () {
    "use strict";

    var DEPENDENCIES = {
        advanced_mode: ["server_url", "ws_protocol"]
    };

    document.addEventListener("DOMContentLoaded", function () {
        Object.keys(DEPENDENCIES).forEach(function (masterId) {
            var master = document.getElementById(masterId);
            if (!master) {
                return;
            }
            var rows = DEPENDENCIES[masterId]
                .map(function (fieldId) {
                    var input = document.getElementById(fieldId);

                    return input ? input.closest("tr") : null;
                })
                .filter(Boolean);

            var apply = function () {
                rows.forEach(function (row) {
                    row.style.display = master.checked ? "" : "none";
                });
            };

            master.addEventListener("change", apply);
            apply();
        });
    });
})();
