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

namespace Splash\Tests;

use ArrayObject;
use Exception;
use Splash\Local\Core\AttributesManager as Manager;
use Splash\Local\Core\PluginManager;
use Splash\Local\Local;
use Splash\Local\Objects\Core\MultiLangTrait;
use Splash\Tests\Tools\ObjectsCase;

/**
 * Local Objects Test Suite - Test of Products Variants Attributes Manager.
 */
class L01AttributesManagerTest extends ObjectsCase
{
    use PluginManager;
    use MultiLangTrait;

    /**
     * @var string Prefix for Tests Variants
     */
    const GROUPPREFIX = "TestVariant";

    /**
     * @var ArrayObject
     */
    protected $out;

    /**
     * Test Creation of a New Attribute Group
     *
     * @dataProvider sequencesProvider
     *
     * @param mixed $sequence
     */
    public function testCreateAttributeGroup($sequence)
    {
        /** Check if WooCommerce is active */
        if (!Local::hasWooCommerce()) {
            $this->markTestSkipped("WooCommerce Plugin is Not Active");

            return;
        }
        $this->loadLocalTestSequence($sequence);
        //====================================================================//
        //   Load Known Attribute Group
        $code = strtolower(self::GROUPPREFIX);
        //====================================================================//
        //   Ensure Attribute Group is Deleted
        $this->ensureAttributeGroupIsDeleted($code);
        //====================================================================//
        // Create Attribute Group Name
        $names = self::fakeFieldData(SPL_T_MVARCHAR, null, array("minLength" => 3, "maxLength" => 5));
        $this->assertIsArray($names);
        //====================================================================//
        //   Create a New Attribute Group
        $attributeGroup = Manager::addGroup($code, $names);
        //====================================================================//
        //   Verify Attribute Group
        $this->assertNotEmpty($attributeGroup);
        $this->assertNotEmpty($attributeGroup->id);
        $this->assertEquals("pa_".$code, $attributeGroup->slug);
        $this->verifyMultilangField($names, $attributeGroup->name);
        //====================================================================//
        //   Verify Attributes Group Identification
        $identifiedGroup = Manager::getGroupByCode($code);
        $this->assertNotEmpty($identifiedGroup);
        $this->assertEquals($attributeGroup->id, $identifiedGroup->id);

        //====================================================================//
        //   Test Update Attributes Group Names
        $newNames = self::fakeFieldData(SPL_T_MVARCHAR, null, array("minLength" => 3, "maxLength" => 5));
        $this->assertFalse(Manager::updateGroup($identifiedGroup, array()));
        $this->assertTrue(Manager::updateGroup($identifiedGroup, $newNames));

        //====================================================================//
        //   Verify Attributes Group Identification
        $renamedGroup = Manager::getGroupByCode($code);
        $this->assertNotEmpty($renamedGroup);
        $this->assertEquals($renamedGroup->id, $attributeGroup->id);
        $this->verifyMultilangField($newNames, $renamedGroup->name);
        //====================================================================//
        //   Ensure Attribute Group is Deleted
        $this->ensureAttributeGroupIsDeleted($code);
    }

    /**
     * Test Creation of a New Attribute Group
     *
     * @dataProvider sequencesProvider
     *
     * @param mixed $sequence
     *
     * @throws Exception
     */
    public function testCreateAttributeValues($sequence)
    {
        /** Check if WooCommerce is active */
        if (!Local::hasWooCommerce()) {
            $this->markTestSkipped("WooCommerce Plugin is Not Active");
        }
        $this->loadLocalTestSequence($sequence);

        //====================================================================//
        //   Load Known Attribute Group
        $code = strtolower(self::GROUPPREFIX.(string) rand(100, 500));
        //====================================================================//
        // Create Attribute Group Name
        $names = self::fakeFieldData(SPL_T_MVARCHAR, null, array("minLength" => 3, "maxLength" => 5));
        //====================================================================//
        //   Ensure Attribute Group is Deleted
        $this->ensureAttributeGroupIsDeleted($code);

        //====================================================================//
        //   Create a New Attribute Group
        $groupId = Manager::addGroup($code, $names);
        $this->assertNotEmpty($groupId);
        $group = Manager::getGroupByCode($code);
        $this->assertNotEmpty($group);

        //====================================================================//
        //   Create a New Attribute Values
        for ($i = 0; $i < 5; $i++) {
            //====================================================================//
            // Create Attribute Value Name
            $value = self::fakeFieldData(SPL_T_MVARCHAR, null, array("minLength" => 5, "maxLength" => 10));
            $valueCode = strtolower($value[self::getDefaultLanguage()]);

            //====================================================================//
            //   Verify Attributes Value Identification => Fails
            $this->assertNull(Manager::getValueByCode($group->slug, $valueCode));
            $this->assertNull(Manager::getValueByName($group->slug, $value));
            //====================================================================//
            //   Create Attribute Value
            $newValue = Manager::addValue($group->slug, $value);
            $this->assertNotEmpty($newValue);
            $this->assertNotEmpty($newValue->term_id);
            $attribute = get_term($newValue->term_id);
            $this->assertEquals($attribute, $newValue);
            $this->assertNotEmpty($attribute->term_id);
            $this->verifyMultilangField($value, $attribute->name);

            //====================================================================//
            //   Verify Attributes Value Identification
            $taximony = wc_attribute_taxonomy_name($code);
            $this->assertEquals($attribute, Manager::getValueByCode($taximony, $valueCode));
            $this->assertEquals($attribute, Manager::getValueByName($taximony, $value[self::getDefaultLanguage()]));
        }

        //====================================================================//
        //   Ensure Attribute Group is Deleted
        $this->ensureAttributeGroupIsDeleted($code);
    }

    /**
     * Verify Contents for a Multi-lang Field
     *
     * @param array  $expected
     * @param string $actual
     */
    private function verifyMultilangField($expected, $actual)
    {
        //====================================================================//
        // Walk on Available Languages
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->assertEquals(
                $expected[$isoCode],
                $this->encodeMultiLang($actual, $isoCode)
            );
        }
    }

    /**
     * Ensure Attribute Group Is Deleted
     *
     * @global array $wp_taxonomies
     *
     * @param string $code
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function ensureAttributeGroupIsDeleted($code)
    {
        global $wp_taxonomies;
        //====================================================================//
        //   Load Known Attribute Group
        $attributeGroup = Manager::getGroupByCode($code);
        //====================================================================//
        //   Delete Attribute Group
        if ($attributeGroup) {
            wc_delete_attribute($attributeGroup->id);
            unset($wp_taxonomies[wc_attribute_taxonomy_name($code)]);
        }
        //====================================================================//
        //   Load Known Attribute Group
        $this->assertNull(Manager::getGroupByCode($code));
    }
}
