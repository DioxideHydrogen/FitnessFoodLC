<?php

namespace Tests\Unit;

use Exception;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class GetMEthodApiTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_response_is_array()
    {
        $url = env('APP_URL') . ':8000/api/products';

		$client = new Client();

		try {

			$request = $client->get($url);

			$content = $request->getBody()->getContents();

			$json = \json_decode($content);

			$this->assertIsArray($json);

		} catch (Exception $e) {

		}
    }
}
