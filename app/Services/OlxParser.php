<?php

namespace App\Services;

use App\Traits\HttpResponses;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OlxParser
{

    use HttpResponses;
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

    public function __construct()
    {
        //
    }

    public function parseProduct(string $url)
    {
        try {

            if (!$this->isValidOlxUrl($url)) {
                return $this->error(message: __('Incorrect OLX link'));
            }

            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
            ])->timeout(30)->get($url);

            if ($response->status() !== 200) {
                return $this->error(message: sprintf('%s: %s', __('Failed to load page'), $response->status()));
            }

            $html = $response->body();

            $data = [
                'url' => $url,
                'title' => $this->extractTitle($html),
                'price' => $this->extractPrice($html),
                'currency' => $this->extractCurrency($html),
                'parsed_at' => now(),
            ];

            return $this->success($data);


        } catch (Exception $e) {

            Log::error($e->getMessage());
            return $this->error(message: $e->getMessage());

        }
    }

    private function isValidOlxUrl(string $url): bool
    {
        return preg_match('/^https?:\/\/(www\.)?olx\.(ua|kz|uz|pl)\/.*/', $url);
    }

    private function extractPrice(string $html): ?string
    {

        $patterns = [
            '/(\d+(?:\s+\d+)*)\s*(?:грн\.?|UAH|₴|тенге|сум|zł|PLN)/i',

            '/data-testid="ad-price-text"[^>]*>([^<]*\d[^<]*)/i',
            '/data-testid=\'ad-price-text\'[^>]*>([^<]*\d[^<]*)/i',

            '/class="[^"]*css-[^"]*"[^>]*>([^<]*\d[^<]*)/i',
            '/<span[^>]*class="[^"]*price[^"]*"[^>]*>([^<]*\d[^<]*)/i',

            // JSON
            '/"price":\s*"?(\d+)"?/i',
            '/"amount":\s*"?(\d+)"?/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $price = trim(strip_tags($matches[1]));
                $price = preg_replace('/[^\d\s]/', '', $price);
                $price = preg_replace('/\s+/', '', $price);
                if ($price && is_numeric($price)) {
                    return $price;
                }
            }
        }

        return null;
    }

    private function extractCurrency(string $html): ?string
    {

        $patterns = [
            '/\d+(?:\s+\d+)*\s*(грн\.?|UAH|₴|тенге|сум|zł|PLN)/i',
            '/data-testid="ad-price-text"[^>]*>([^<]*)(грн\.?|UAH|₴|тенге|сум|zł|PLN)/i',
            '/"currency":\s*"?([^"]*)"?/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $currency = trim($matches[count($matches) - 1], '.');
                return $this->normalizeCurrency($currency);
            }
        }

        return null;
    }


    private function normalizeCurrency(string $currency): string
    {
        $currencyMap = [
            'грн' => 'UAH',
            '₴' => 'UAH',
            'тенге' => 'KZT',
            'сум' => 'UZS',
            'zł' => 'PLN',
        ];

        return $currencyMap[strtolower($currency)] ?? strtoupper($currency);
    }

    private function extractTitle(string $html): ?string
    {
        $patterns = [
            '/<h1[^>]*data-cy=["\']ad_title["\'][^>]*>([^<]+)/i',
            '/<h1[^>]*class=["\'][^"\']*css-r9zjja[^"\']*["\'][^>]*>([^<]+)/i',
            '/<title[^>]*>([^<]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $title = trim(strip_tags($matches[1]));
                return $title !== '' ? $title : null;
            }
        }

        return null;
    }

}
