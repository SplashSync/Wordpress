<?php
namespace Splash\Local\Tests\Products;

use Splash\Tests\Tools\ObjectsCase;
use Splash\Client\Splash;
use ArrayObject;

/**
 * @abstract    Objects Test Suite - Objects List Reading Verifications
 *
 * @author SplashSync <contact@splashsync.com>
 */
class DummyTest extends ObjectsCase {
    
    
    /**
     * @dataProvider ObjectTypesProvider
     */
    public function testFromModule($ObjectType)
    {
//        var_dump($ObjectType);
        $this->assertNotEmpty($ObjectType);
    }
    
}
