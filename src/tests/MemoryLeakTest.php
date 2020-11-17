<?php
namespace Keepa\tests;

use Keepa\API\Request;
use Keepa\KeepaAPI;
use Keepa\objects\AmazonLocale;

class MemoryLeakTest extends AbstractTest
{
    private static function getRequest(){
        return Request::getProductRequest(AmazonLocale::DE, 0, null, null, 1, true, ['B001G73S50']);
    }

    public function testNetMemory()
    {
        // first call
        $this->api->sendRequestWithRetry(self::getRequest());

        // check usage
        $start = memory_get_usage(false);

        // do first round of calls
        for($i=0;$i < 100;$i++)
        {
            $this->api->sendRequestWithRetry(self::getRequest());
        }

        // get mid memory
        $mid = memory_get_usage(false);

        // do second round of calls
        for($i=0;$i < 100;$i++)
        {
            $this->api->sendRequestWithRetry(self::getRequest());
        }

        // get end memory
        $end = memory_get_usage(false);

        self::assertTrue($start == $mid);
        self::assertTrue($mid == $end);
    }

    public function testNetFail()
    {
        // save away the result, to simulate increased memory usage
        $storage = [];

        // first call
        $this->api->sendRequestWithRetry(self::getRequest());

        // check usage
        $start = memory_get_usage(false);

        // do first round of calls (and save away the results)
        for($i=0;$i < 100;$i++)
        {
            $storage[] = $this->api->sendRequestWithRetry(self::getRequest());
        }

        // get mid memory
        $mid = memory_get_usage(false);

        // do second round of calls
        for($i=0;$i < 100;$i++)
        {
            $this->api->sendRequestWithRetry(self::getRequest());
        }

        // get end memory
        $end = memory_get_usage(false);

        self::assertTrue($start < $mid);
        self::assertTrue($mid == $end);
    }

    public function testCreateClass()
    {
        $apiKey = getenv("KEEPA_APIKEY");
        $api = new KeepaAPI($apiKey);
        $api->sendRequestWithRetry(self::getRequest());

        // check usage
        $start = memory_get_usage(false);

        // do first round of calls
        for($i=0;$i < 10;$i++)
        {
            $api = new KeepaAPI($apiKey);
            $api->sendRequestWithRetry(self::getRequest());
        }

        // get mid memory
        $mid = memory_get_usage(false);

        // do second round of calls
        for($i=0;$i < 10;$i++)
        {
            $api = new KeepaAPI($apiKey);
            $api->sendRequestWithRetry(self::getRequest());
        }
        $end = memory_get_usage(false);

        self::assertTrue($start == $mid);
        self::assertTrue($mid == $end);
    }
}