<?php

namespace Tests\Unit;

use Exception;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class PutMEthodApiTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_response_is_array()
    {
        $url = env('APP_URL') . ':8000/api/products/0000000000031';

		$client = new Client();

		$json = '{
			"status": "published",
			"url": "http://www.google.com",
			"creator": "Google LC",
			"name": "Google Chrome",
			"quantity": "",
			"brands": "",
			"labels": "",
			"cities": "",
			"purchase_places": "",
			"stores": "",
			"ingredients": "",
			"traces": "",
			"serving_size": "",
			"serving_quantity": "",
			"nutriscore_score": "",
			"nutriscore_grade": "",
			"main_category": "",
			"image_url": "https://www.google.com/logo"
		}';

		try {

			$request = $client->put($url, [
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'body' => \json_encode($json)
			]);

			$content = $request->getBody()->getContents();

			$json = \json_decode($content);

			$this->assertEquals(200, $request->getStatusCode());

		} catch (Exception $e) {
			
		}
    }
}
