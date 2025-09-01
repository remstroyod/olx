<?php

namespace Tests\Unit;

use App\Services\OlxParser;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OlxParserTest extends TestCase
{
    private OlxParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new OlxParser();
    }

    /** @test */
    public function it_returns_error_for_invalid_olx_url()
    {
        $result = $this->parser->parseProduct('https://invalid-site.com/item');
        
        $response = json_decode($result->getContent(), true);
        $this->assertFalse($response['status']);
        $this->assertEquals('Incorrect OLX link', $response['message']);
    }

    /** @test */
    public function it_accepts_valid_olx_urls()
    {
        Http::fake([
            '*' => Http::response($this->getValidHtml(), 200)
        ]);

        $validUrls = [
            'https://www.olx.ua/item/123',
            'https://olx.ua/item/123',
            'http://www.olx.ua/item/123',
            'https://www.olx.kz/item/123',
            'https://www.olx.uz/item/123',
            'https://www.olx.pl/item/123'
        ];

        foreach ($validUrls as $url) {
            $result = $this->parser->parseProduct($url);
            $response = json_decode($result->getContent(), true);
            $this->assertTrue($response['status'], "Failed for URL: {$url}");
        }
    }

    /** @test */
    public function it_handles_http_error_response()
    {
        Http::fake([
            '*' => Http::response('Not Found', 404)
        ]);

        $result = $this->parser->parseProduct('https://www.olx.ua/item/123');
        
        $response = json_decode($result->getContent(), true);
        $this->assertFalse($response['status']);
        $this->assertStringContainsString('Failed to load page: 404', $response['message']);
    }

    /** @test */
    public function it_parses_product_data_successfully()
    {
        Http::fake([
            '*' => Http::response($this->getValidHtml(), 200)
        ]);

        $result = $this->parser->parseProduct('https://www.olx.ua/item/123');
        
        $response = json_decode($result->getContent(), true);
        $this->assertTrue($response['status']);
        $data = (object) $response['data'];
        
        $this->assertEquals('https://www.olx.ua/item/123', $data->url);
        $this->assertEquals('Test Product Title', $data->title);
        $this->assertEquals('15000', $data->price);
        $this->assertEquals('UAH', $data->currency);
        $this->assertNotNull($data->parsed_at);
    }

    /** @test */
    public function it_extracts_price_from_different_patterns()
    {
        $htmlVariations = [
            '<div data-testid="ad-price-text">15 000 грн.</div>',
            '<span class="price">25000 UAH</span>',
            '{"price": "30000"}',
            '{"amount": "40000"}',
        ];

        foreach ($htmlVariations as $index => $html) {
            Http::fake([
                '*' => Http::response($html, 200)
            ]);

            $result = $this->parser->parseProduct('https://www.olx.ua/item/' . $index);
            $response = json_decode($result->getContent(), true);
            $this->assertTrue($response['status']);
            $this->assertNotNull($response['data']['price']);
        }
    }

    /** @test */
    public function it_extracts_currency_and_normalizes_it()
    {
        Http::fake([
            '*' => Http::response('<div>15000 грн.</div>', 200)
        ]);

        $result = $this->parser->parseProduct('https://www.olx.ua/item/test');
        $response = json_decode($result->getContent(), true);
        $this->assertTrue($response['status']);
        $this->assertEquals('UAH', $response['data']['currency']);
    }

    /** @test */
    public function it_extracts_title_from_different_patterns()
    {
        $titleTests = [
            '<h1 data-cy="ad_title">Product from data-cy</h1>',
            '<h1 class="css-r9zjja">Product from class</h1>',
            '<title>Product from title tag</title>',
        ];

        foreach ($titleTests as $html) {
            Http::fake([
                '*' => Http::response($html, 200)
            ]);

            $result = $this->parser->parseProduct('https://www.olx.ua/item/test');
            $response = json_decode($result->getContent(), true);
            $this->assertTrue($response['status']);
            $this->assertNotNull($response['data']['title']);
        }
    }

    /** @test */
    public function it_handles_missing_data_gracefully()
    {
        Http::fake([
            '*' => Http::response('<div>No product data here</div>', 200)
        ]);

        $result = $this->parser->parseProduct('https://www.olx.ua/item/test');
        
        $response = json_decode($result->getContent(), true);
        $this->assertTrue($response['status']);
        $data = (object) $response['data'];
        
        $this->assertNull($data->title);
        $this->assertNull($data->price);
        $this->assertNull($data->currency);
    }

    /** @test */
    public function it_handles_http_timeout_exception()
    {
        Http::fake([
            '*' => function () {
                throw new \Exception('Request timeout');
            }
        ]);

        $result = $this->parser->parseProduct('https://www.olx.ua/item/test');
        
        $response = json_decode($result->getContent(), true);
        $this->assertFalse($response['status']);
        $this->assertStringContainsString('Request timeout', $response['message']);
    }

    private function getValidHtml(): string
    {
        return '
        <html>
            <head>
                <title>Test Product Title</title>
            </head>
            <body>
                <h1 data-cy="ad_title">Test Product Title</h1>
                <div data-testid="ad-price-text">15 000 грн.</div>
            </body>
        </html>
        ';
    }
}