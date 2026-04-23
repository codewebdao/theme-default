<?php

namespace App\Controllers;

use App\Controllers\BackendController;
use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;

/**
 * Localization Test Controller
 * 
 * Comprehensive testing interface for all Localization_helper.php functions
 * 
 * @package App\Controllers\Backend
 */
class LocalizationTestController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        
        // Load ecommerce localization helper
        $localizationHelper = PATH_PLUGINS . 'ecommerce/Helpers/Localization_helper.php';
        if (file_exists($localizationHelper)) {
            require_once $localizationHelper;
        }
        
        // Load ecommerce helper (for ec_option)
        $ecommerceHelper = PATH_PLUGINS . 'ecommerce/Helpers/Ecommerce_helper.php';
        if (file_exists($ecommerceHelper)) {
            require_once $ecommerceHelper;
        }
        
        Flang::load('Backend/Localization');
    }

    /**
     * Main test dashboard
     */
    public function index()
    {
        $this->data['page_title'] = 'Localization System Test';
        
        // Current user preferences
        $this->data['current_preferences'] = ec_user_preferences();
        
        // Available options
        $this->data['currencies'] = ec_currencies();
        $this->data['countries'] = ec_countries();
        $this->data['languages'] = ec_languages();
        $this->data['languages_full'] = ec_languages_list();
        $this->data['languages_intl'] = ec_languages_all_list();
        
        // Sample prices for preview
        $this->data['sample_prices'] = $this->getSamplePrices();
        
        // Run all tests
        $this->data['tests'] = [
            'currency' => $this->testCurrency(),
            'price' => $this->testPrice(),
            'country' => $this->testCountry(),
            'language' => $this->testLanguage(),
            'preferences' => $this->testPreferences(),
            'detection' => $this->testDetection(),
            'exchange_rates' => $this->testExchangeRates(),
            'validation' => $this->testValidation(),
            'utilities' => $this->testUtilities(),
        ];
        
        // Overall stats
        $this->data['stats'] = $this->calculateStats($this->data['tests']);
        
        echo View::make('localization_test', $this->data)->render();
    }
    
    /**
     * Get sample prices for preview
     */
    private function getSamplePrices()
    {
        $amounts = [99.99, 1234.56, 5000, 12345.67];
        $samples = [];
        
        foreach ($amounts as $amount) {
            $samples[] = [
                'amount' => $amount,
                'formatted' => ec_format_price($amount),
                'currency' => ec_user_currency(),
            ];
        }
        
        return $samples;
    }

    /**
     * Test currency functions
     */
    private function testCurrency()
    {
        $tests = [];
        
        // ec_currency()
        $tests['ec_currency'] = [
            'function' => 'ec_currency()',
            'result' => ec_currency(),
            'status' => is_string(ec_currency()) && strlen(ec_currency()) === 3,
            'description' => 'Get active currency code',
        ];
        
        // ec_currency_symbol()
        $tests['ec_currency_symbol'] = [
            'function' => 'ec_currency_symbol()',
            'result' => ec_currency_symbol('USD'),
            'status' => ec_currency_symbol('USD') === '$',
            'description' => 'Get currency symbol (USD)',
        ];
        
        $tests['ec_currency_symbol_eur'] = [
            'function' => 'ec_currency_symbol("EUR")',
            'result' => ec_currency_symbol('EUR'),
            'status' => ec_currency_symbol('EUR') === '€',
            'description' => 'Get currency symbol (EUR)',
        ];
        
        // ec_currencies()
        $currencies = ec_currencies();
        $tests['ec_currencies'] = [
            'function' => 'ec_currencies()',
            'result' => count($currencies) . ' currencies',
            'status' => is_array($currencies) && count($currencies) > 0,
            'description' => 'Get all currencies',
        ];
        
        // ec_currency_decimals() - NEW
        $tests['ec_currency_decimals_usd'] = [
            'function' => 'ec_currency_decimals("USD")',
            'result' => ec_currency_decimals('USD'),
            'status' => ec_currency_decimals('USD') === 2,
            'description' => 'Get decimals for USD (2)',
        ];
        
        $tests['ec_currency_decimals_jpy'] = [
            'function' => 'ec_currency_decimals("JPY")',
            'result' => ec_currency_decimals('JPY'),
            'status' => ec_currency_decimals('JPY') === 0,
            'description' => 'Get decimals for JPY (0)',
        ];
        
        $tests['ec_currency_decimals_kwd'] = [
            'function' => 'ec_currency_decimals("KWD")',
            'result' => ec_currency_decimals('KWD'),
            'status' => ec_currency_decimals('KWD') === 3,
            'description' => 'Get decimals for KWD (3)',
        ];
        
        return $tests;
    }

    /**
     * Test price functions
     */
    private function testPrice()
    {
        $tests = [];
        
        // ec_format_price()
        $formatted = ec_format_price(1234.56, ['currency' => 'USD']);
        $tests['ec_format_price'] = [
            'function' => 'ec_format_price(1234.56, [\'currency\' => \'USD\'])',
            'result' => $formatted,
            'status' => !empty($formatted) && strpos($formatted, '1234') !== false,
            'description' => 'Format price with currency',
        ];
        
        // ec_parse_price()
        $parsed = ec_parse_price('$1,234.56', 'USD');
        $tests['ec_parse_price'] = [
            'function' => 'ec_parse_price("$1,234.56", "USD")',
            'result' => $parsed,
            'status' => abs($parsed - 1234.56) < 0.01,
            'description' => 'Parse price string to float',
        ];
        
        // ec_round_price()
        $rounded = ec_round_price(1234.567);
        $tests['ec_round_price'] = [
            'function' => 'ec_round_price(1234.567)',
            'result' => $rounded,
            'status' => is_float($rounded) || is_int($rounded),
            'description' => 'Round price',
        ];
        
        // ec_convert()
        $converted = ec_convert(100, 'USD', 'EUR');
        $tests['ec_convert'] = [
            'function' => 'ec_convert(100, "USD", "EUR")',
            'result' => $converted,
            'status' => is_numeric($converted) && $converted > 0,
            'description' => 'Convert between currencies',
        ];
        
        // ec_tax()
        $tax = ec_tax(100, 10); // 10% tax
        $tests['ec_tax'] = [
            'function' => 'ec_tax(100, 10)',
            'result' => $tax,
            'status' => abs($tax - 10) < 0.01,
            'description' => 'Calculate tax amount (10%)',
        ];
        
        // ec_price_with_tax()
        $withTax = ec_price_with_tax(100, 10);
        $tests['ec_price_with_tax'] = [
            'function' => 'ec_price_with_tax(100, 10)',
            'result' => $withTax,
            'status' => abs($withTax - 110) < 0.01,
            'description' => 'Add tax to price',
        ];
        
        // ec_price_no_tax()
        $noTax = ec_price_no_tax(110, 10);
        $tests['ec_price_no_tax'] = [
            'function' => 'ec_price_no_tax(110, 10)',
            'result' => $noTax,
            'status' => abs($noTax - 100) < 0.01,
            'description' => 'Remove tax from price',
        ];
        
        // ec_format_number()
        $formatted_num = ec_format_number(123456.789, 2);
        $tests['ec_format_number'] = [
            'function' => 'ec_format_number(123456.789, 2)',
            'result' => $formatted_num,
            'status' => !empty($formatted_num),
            'description' => 'Format number according to locale',
        ];
        
        // ec_sanitize_price()
        $sanitized = ec_sanitize_price('$1,234.56');
        $tests['ec_sanitize_price'] = [
            'function' => 'ec_sanitize_price("$1,234.56")',
            'result' => $sanitized,
            'status' => abs($sanitized - 1234.56) < 0.01,
            'description' => 'Sanitize price input',
        ];
        
        return $tests;
    }

    /**
     * Test country functions
     */
    private function testCountry()
    {
        $tests = [];
        
        // ec_countries()
        $countries = ec_countries();
        $tests['ec_countries'] = [
            'function' => 'ec_countries()',
            'result' => count($countries) . ' countries<br />' . '<pre>' . print_r($countries, true) . '</pre>',
            'status' => is_array($countries) && count($countries) > 0,
            'description' => 'Get all countries',
        ];
        
        // ec_country_name()
        $tests['ec_country_name'] = [
            'function' => 'ec_country_name("US")',
            'result' => ec_country_name('US'),
            'status' => ec_country_name('US') === 'United States',
            'description' => 'Get country name',
        ];
        
        // ec_country_currency()
        $tests['ec_country_currency_us'] = [
            'function' => 'ec_country_currency("US")',
            'result' => ec_country_currency('US'),
            'status' => ec_country_currency('US') === 'USD',
            'description' => 'Get currency for US',
        ];
        
        $tests['ec_country_currency_vn'] = [
            'function' => 'ec_country_currency("VN")',
            'result' => ec_country_currency('VN'),
            'status' => ec_country_currency('VN') === 'VND',
            'description' => 'Get currency for Vietnam',
        ];
        
        // ec_country_lang()
        $tests['ec_country_lang_vn'] = [
            'function' => 'ec_country_lang("VN")',
            'result' => ec_country_lang('VN'),
            'status' => ec_country_lang('VN') === 'vi',
            'description' => 'Get language for Vietnam',
        ];
        
        $tests['ec_country_lang_de'] = [
            'function' => 'ec_country_lang("DE")',
            'result' => ec_country_lang('DE'),
            'status' => ec_country_lang('DE') === 'de',
            'description' => 'Get language for Germany',
        ];
        
        return $tests;
    }

    /**
     * Test language functions
     */
    private function testLanguage()
    {
        $tests = [];
        
        // ec_languages()
        $languages = ec_languages();
        $tests['ec_languages'] = [
            'function' => 'ec_languages()',
            'result' => count($languages) . ' languages<br />' . '<pre>' . print_r($languages, true) . '</pre>',
            'status' => is_array($languages) && count($languages) > 0,
            'description' => 'Get all languages',
        ];
        
        // ec_language_name()
        $tests['ec_language_name'] = [
            'function' => 'ec_language_name("en")',
            'result' => ec_language_name('en'),
            'status' => !empty(ec_language_name('en')),
            'description' => 'Get language name',
        ];
        
        return $tests;
    }

    /**
     * Test user preference functions
     */
    private function testPreferences()
    {
        $tests = [];
        
        // ec_user_currency()
        $tests['ec_user_currency'] = [
            'function' => 'ec_user_currency()',
            'result' => ec_user_currency(),
            'status' => is_string(ec_user_currency()) && strlen(ec_user_currency()) === 3,
            'description' => 'Get user currency',
        ];
        
        // ec_user_locale()
        $tests['ec_user_locale'] = [
            'function' => 'ec_user_locale()',
            'result' => ec_user_locale(),
            'status' => !empty(ec_user_locale()),
            'description' => 'Get user locale',
        ];
        
        // ec_user_country()
        $tests['ec_user_country'] = [
            'function' => 'ec_user_country()',
            'result' => ec_user_country(),
            'status' => is_string(ec_user_country()) && strlen(ec_user_country()) === 2,
            'description' => 'Get user country',
        ];
        
        // ec_user_preferences()
        $prefs = ec_user_preferences();
        $tests['ec_user_preferences'] = [
            'function' => 'ec_user_preferences()',
            'result' => json_encode($prefs) . '<br />' . '<pre>' . print_r($prefs, true) . '</pre>',
            'status' => is_array($prefs) && isset($prefs['currency']) && isset($prefs['locale']) && isset($prefs['country']),
            'description' => 'Get all user preferences',
        ];
        
        return $tests;
    }

    /**
     * Test auto-detection functions
     */
    private function testDetection()
    {
        $tests = [];
        
        // ec_ip_country()
        $ipCountry = ec_ip_country();
        $tests['ec_ip_country'] = [
            'function' => 'ec_ip_country()',
            'result' => $ipCountry,
            'status' => is_string($ipCountry) && strlen($ipCountry) === 2,
            'description' => 'Detect country from IP',
        ];
        
        // ec_browser_language()
        $browserLang = ec_browser_language();
        $tests['ec_browser_language'] = [
            'function' => 'ec_browser_language()',
            'result' => $browserLang ?? 'not detected',
            'status' => true, // Can be null
            'description' => 'Detect language from browser',
        ];
        
        return $tests;
    }

    /**
     * Test exchange rate functions
     */
    private function testExchangeRates()
    {
        $tests = [];
        
        // ec_exchange_rate()
        $rate = ec_exchange_rate('USD', 'EUR');
        $tests['ec_exchange_rate'] = [
            'function' => 'ec_exchange_rate("USD", "EUR")',
            'result' => $rate,
            'status' => is_numeric($rate) && $rate > 0,
            'description' => 'Get exchange rate USD to EUR',
        ];
        
        // ec_rates_info()
        $info = ec_rates_info();
        $tests['ec_rates_info'] = [
            'function' => 'ec_rates_info()',
            'result' => json_encode($info) . '<br />' . '<pre>' . print_r($info, true) . '</pre>',
            'status' => is_array($info) && isset($info['cached']),
            'description' => 'Get exchange rates cache info',
        ];
        
        return $tests;
    }

    /**
     * Test validation functions
     */
    private function testValidation()
    {
        $tests = [];
        
        // ec_is_currency()
        $tests['ec_is_currency_valid'] = [
            'function' => 'ec_is_currency("USD")',
            'result' => ec_is_currency('USD') ? 'true' : 'false',
            'status' => ec_is_currency('USD') === true,
            'description' => 'Validate valid currency',
        ];
        
        $tests['ec_is_currency_invalid'] = [
            'function' => 'ec_is_currency("XXX")',
            'result' => ec_is_currency('XXX') ? 'true' : 'false',
            'status' => ec_is_currency('XXX') === false,
            'description' => 'Validate invalid currency',
        ];
        
        // ec_is_country()
        $tests['ec_is_country_valid'] = [
            'function' => 'ec_is_country("US")',
            'result' => ec_is_country('US') ? 'true' : 'false',
            'status' => ec_is_country('US') === true,
            'description' => 'Validate valid country',
        ];
        
        $tests['ec_is_country_invalid'] = [
            'function' => 'ec_is_country("XX")',
            'result' => ec_is_country('XX') ? 'true' : 'false',
            'status' => ec_is_country('XX') === false,
            'description' => 'Validate invalid country',
        ];
        
        // ec_is_locale()
        $tests['ec_is_locale_valid'] = [
            'function' => 'ec_is_locale("en")',
            'result' => ec_is_locale('en') ? 'true' : 'false',
            'status' => ec_is_locale('en') === true,
            'description' => 'Validate valid locale',
        ];
        
        return $tests;
    }

    /**
     * Test utility functions
     */
    private function testUtilities()
    {
        $tests = [];
        
        // ec_create_money()
        $money = ec_create_money(100, 'USD');
        $tests['ec_create_money'] = [
            'function' => 'ec_create_money(100, "USD")',
            'result' => $money ? 'Money object created' : 'null (brick/money not available)',
            'status' => true, // Can be null if brick/money not installed
            'description' => 'Create Money object',
        ];
        
        return $tests;
    }

    /**
     * Calculate overall statistics
     */
    private function calculateStats($tests)
    {
        $total = 0;
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $category => $categoryTests) {
            foreach ($categoryTests as $test) {
                $total++;
                if ($test['status']) {
                    $passed++;
                } else {
                    $failed++;
                }
            }
        }
        
        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Clear exchange rate cache (AJAX action)
     */
    public function clear_rates()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $result = ec_rates_clear();
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Exchange rates cache cleared successfully' : 'Failed to clear cache',
        ]);
    }

    /**
     * Refresh exchange rates (AJAX action)
     */
    public function refresh_rates()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $rates = ec_rates_refresh();
        $info = ec_rates_info();
        
        echo json_encode([
            'success' => !empty($rates),
            'message' => !empty($rates) ? 'Exchange rates refreshed successfully' : 'Failed to refresh rates',
            'info' => $info,
        ]);
    }
    
    /**
     * Set user preferences (AJAX action)
     */
    public function set_preferences()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $currency = $input['currency'] ?? null;
        $country = $input['country'] ?? null;
        $locale = $input['locale'] ?? null;
        
        $errors = [];
        $success = true;
        
        // Validate and set currency
        if ($currency) {
            if (!ec_is_currency($currency)) {
                $errors[] = 'Invalid currency code';
                $success = false;
            } else {
                ec_user_currency_set($currency);
            }
        }
        
        // Validate and set country
        if ($country) {
            if (!ec_is_country($country)) {
                $errors[] = 'Invalid country code';
                $success = false;
            } else {
                ec_user_country_set($country);
            }
        }
        
        // Validate and set locale
        if ($locale) {
            if (!ec_is_locale($locale)) {
                $errors[] = 'Invalid locale';
                $success = false;
            } else {
                ec_user_locale_set($locale);
            }
        }
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Preferences updated successfully' : 'Failed to update preferences',
            'errors' => $errors,
            'preferences' => ec_user_preferences(),
        ]);
    }
    
    /**
     * Clear user preferences (AJAX action)
     */
    public function clear_preferences()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $result = ec_user_preferences_clear();
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Preferences cleared successfully' : 'Failed to clear preferences',
        ]);
    }
    
    /**
     * Auto-detect preferences (AJAX action)
     */
    public function auto_preferences()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        if (headers_sent()) {
            echo json_encode([
                'success' => false,
                'message' => 'Headers already sent, cannot set cookies',
            ]);
            return;
        }
        
        $prefs = ec_auto_preferences();
        
        echo json_encode([
            'success' => true,
            'message' => 'Preferences auto-detected and set',
            'preferences' => $prefs,
        ]);
    }
}

