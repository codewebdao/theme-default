<?php

namespace App\Controllers\Api\V2;

use App\Controllers\ApiController;
use App\Services\Posts\PostsService;

/**
 * Public form submissions (JSON hoặc x-www-form-urlencoded / multipart).
 *
 * POST /api/v2/form/contact — cùng field với theme contact: name, email, inquiry, subject, message
 */
class FormController extends ApiController
{
    public function __construct()
    {
        if (function_exists('_cors')) {
            _cors();
        }
        parent::__construct();
    }

    /**
     * POST (OPTIONS preflight qua CorsMiddleware)
     */
    public function contact()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
        if ($method !== 'POST') {
            return $this->error('Method not allowed', [], 405);
        }

        load_helpers(['posts', 'string', 'query']);

        $input = $this->parseBody();

        $lang = $this->sanitizeRequestLang((string) ($input['lang'] ?? ''));
        if ($lang === '') {
            $lang = defined('APP_LANG') ? APP_LANG : 'en';
        }

        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $inquiry = strtolower(preg_replace('/[^a-z0-9_-]/i', '', (string) ($input['inquiry'] ?? '')));
        $subject = trim((string) ($input['subject'] ?? ''));
        $message = trim((string) ($input['message'] ?? ''));

        $allowedInquiry = ['general', 'bug', 'feature', 'feedback', 'other'];
        if (!in_array($inquiry, $allowedInquiry, true)) {
            $inquiry = 'general';
        }

        if ($name === '') {
            return $this->error('Validation failed', ['name' => ['Name is required']], 422);
        }
        if ($email === '') {
            return $this->error('Validation failed', ['email' => ['Email is required']], 422);
        }
        if (!$this->isValidEmail($email)) {
            return $this->error('Validation failed', ['email' => ['Invalid email address']], 422);
        }
        if ($subject === '') {
            return $this->error('Validation failed', ['subject' => ['Subject is required']], 422);
        }
        if ($message === '') {
            return $this->error('Validation failed', ['message' => ['Message is required']], 422);
        }

        $slug = url_slug($subject);
        if ($slug === '') {
            $slug = 'contact-' . uniqid('', true);
        }

        $now = date('Y-m-d H:i:s');

        $data = [
            'title'        => $subject,
            'slug'         => $slug,
            'your_name'    => $name,
            'email'        => $email,
            'subject'      => $subject,
            'inquirytype'  => $inquiry,
            'message'      => $message,
            'status'       => 'pending',
            'lang'         => $lang,
            'created_at'   => $now,
            'updated_at'   => $now,
            'create_draft' => true,
        ];

        // Cột thường có trên bảng fast_contact_* (lang_slug, seo_*, description, user_id…) — tránh INSERT thiếu NOT NULL
        $pt = posttype_db('contact');
        $ptFields = [];
        if (!empty($pt)) {
            $decodedFields = _json_decode($pt['fields'] ?? '[]');
            $ptFields = is_array($decodedFields) ? $decodedFields : [];
        }

        $data = $this->mergeMissingContactFields($data, $slug, $subject, $pt);
        $data = $this->coerceSelectValuesToPosttype($data, $ptFields);

        $result = (new PostsService())->create('contact', $data, $lang);
        if (empty($result['success'])) {
            $errors = $result['errors'] ?? [];
            $status = $this->httpStatusForCreateErrors($errors);
            $message = $status === 422 ? 'Validation failed' : 'Could not save message';
            $outErrors = $this->exposeCreateErrors($errors, $status);

            return $this->error($message, $outErrors, $status);
        }

        return $this->success(['post_id' => (int) $result['post_id']], 'Message received', 201);
    }

    /**
     * Select/Radio trong admin: value thường là nhãn hoặc mã khác form (general vs "General Support") — map vào giá trị hợp lệ.
     *
     * @param array<string, mixed> $data
     * @param array<int, mixed> $fields
     * @return array<string, mixed>
     */
    private function coerceSelectValuesToPosttype(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            $fn = $field['field_name'] ?? '';
            $type = $field['type'] ?? '';
            if ($fn === '' || !in_array($type, ['Select', 'Radio'], true)) {
                continue;
            }
            if (!array_key_exists($fn, $data)) {
                continue;
            }

            $allowed = $this->selectFieldAllowedValues($field);
            if ($allowed === []) {
                continue;
            }

            $raw = $data[$fn];
            if ($raw === null || (is_string($raw) && trim($raw) === '')) {
                continue;
            }

            $valStr = is_scalar($raw) ? (string) $raw : '';
            if ($valStr === '') {
                continue;
            }

            if ($this->valueInAllowedList($valStr, $allowed)) {
                $data[$fn] = $this->canonicalAllowedValue($valStr, $allowed);

                continue;
            }

            if ($fn === 'inquirytype') {
                $data[$fn] = $this->mapInquirySlugToOption($valStr, $allowed, $field);

                continue;
            }

            if ($fn === 'status') {
                $data[$fn] = $this->mapStatusToOption($valStr, $allowed);

                continue;
            }

            $data[$fn] = $allowed[0];
        }

        return $data;
    }

    /**
     * @return list<string|int|float>
     */
    private function selectFieldAllowedValues(array $field): array
    {
        $out = [];
        $options = $field['options'] ?? [];
        if (!is_array($options)) {
            return $out;
        }
        foreach ($options as $option) {
            if (is_array($option) && array_key_exists('value', $option)) {
                $out[] = $option['value'];
            } elseif (is_scalar($option)) {
                $out[] = $option;
            }
        }

        return $out;
    }

    /**
     * @param list<string|int|float> $allowed
     */
    private function valueInAllowedList(string $valStr, array $allowed): bool
    {
        foreach ($allowed as $a) {
            if (is_int($a) || is_float($a)) {
                if ($valStr === (string) $a) {
                    return true;
                }
            } elseif ((string) $a === $valStr) {
                return true;
            } elseif (is_string($a) && strcasecmp($a, $valStr) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string|int|float> $allowed
     *
     * @return string|int|float
     */
    private function canonicalAllowedValue(string $valStr, array $allowed)
    {
        foreach ($allowed as $a) {
            if (is_string($a) && strcasecmp($a, $valStr) === 0) {
                return $a;
            }
            if ((string) $a === $valStr) {
                return $a;
            }
        }

        return $allowed[0];
    }

    /**
     * @param list<string|int|float> $allowed
     *
     * @return string|int|float
     */
    private function mapInquirySlugToOption(string $slug, array $allowed, array $field)
    {
        $slug = strtolower($slug);
        $keywords = [
            'general'  => ['general', 'support', 'chung'],
            'bug'      => ['bug', 'lỗi', 'loi', 'report'],
            'feature'  => ['feature', 'request', 'tính năng', 'tinh nang'],
            'feedback' => ['feedback', 'góp ý', 'gop y'],
            'other'    => ['other', 'khác', 'khac'],
        ];
        $needles = $keywords[$slug] ?? [$slug];

        $options = $field['options'] ?? [];
        if (is_array($options)) {
            foreach ($options as $opt) {
                if (!is_array($opt) || !array_key_exists('value', $opt)) {
                    continue;
                }
                $val = $opt['value'];
                $label = isset($opt['label']) ? (string) $opt['label'] : '';
                $hay = mb_strtolower($label !== '' ? $label : (is_string($val) ? $val : (string) $val));
                foreach ($needles as $n) {
                    if ($hay !== '' && mb_strpos($hay, mb_strtolower($n)) !== false) {
                        return $val;
                    }
                }
            }
        }

        foreach ($allowed as $opt) {
            if (!is_string($opt)) {
                continue;
            }
            $hay = mb_strtolower($opt);
            foreach ($needles as $n) {
                if ($hay !== '' && mb_strpos($hay, mb_strtolower($n)) !== false) {
                    return $opt;
                }
            }
        }

        foreach ($allowed as $opt) {
            if (is_string($opt) && strcasecmp(rtrim($opt), $slug) === 0) {
                return $opt;
            }
        }

        return $allowed[0];
    }

    /**
     * @param list<string|int|float> $allowed
     *
     * @return string|int|float
     */
    private function mapStatusToOption(string $wanted, array $allowed)
    {
        $order = ['pending', 'draft', 'active', 'inactive'];
        foreach ($order as $w) {
            foreach ($allowed as $opt) {
                if (is_string($opt) && strcasecmp($opt, $w) === 0) {
                    return $opt;
                }
                if ((string) $opt === $w) {
                    return $opt;
                }
            }
        }

        foreach ($allowed as $opt) {
            if (is_string($opt) && strcasecmp($opt, $wanted) === 0) {
                return $opt;
            }
        }

        return $allowed[0];
    }

    /**
     * Merge default DB/posttype fields; Select/Radio nhận option đầu tiên thay vì ''.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function mergeMissingContactFields(array $data, string $slug, string $subject, ?array $pt = null): array
    {
        if ($pt === null) {
            $pt = posttype_db('contact');
        }
        if (empty($pt)) {
            return array_merge([
                'description' => '',
                'seo_title'   => $subject,
                'seo_desc'    => '',
                'lang_slug'   => $slug,
                'user_id'     => 0,
            ], $data);
        }

        $fields = _json_decode($pt['fields']);
        if (empty($fields) || !is_array($fields)) {
            return array_merge([
                'description' => '',
                'seo_title'   => $subject,
                'seo_desc'    => '',
                'lang_slug'   => $slug,
                'user_id'     => 0,
            ], $data);
        }

        $skipTypes = ['Reference', 'Repeater', 'Flexible', 'Variations', 'Gallery', 'File', 'Image'];
        $defaults = [];

        foreach ($fields as $field) {
            $fn = $field['field_name'] ?? '';
            $type = $field['type'] ?? 'Text';
            if ($fn === '' || $fn === 'id') {
                continue;
            }
            if (array_key_exists($fn, $data)) {
                continue;
            }
            if (in_array($type, $skipTypes, true)) {
                continue;
            }

            if (in_array($fn, ['user_id', 'author', 'author_id', 'creator_id', 'vendor_id', 'created_by'], true)) {
                $defaults[$fn] = 0;
                continue;
            }

            if ($fn === 'lang_slug') {
                $defaults[$fn] = $slug;
                continue;
            }

            if ($fn === 'seo_title') {
                $defaults[$fn] = $subject;
                continue;
            }

            if (in_array($type, ['Number', 'Integer', 'Float', 'Decimal'], true)) {
                $defaults[$fn] = 0;
                continue;
            }
            if (in_array($type, ['Boolean', 'Checkbox'], true)) {
                $defaults[$fn] = 0;
                continue;
            }

            if (in_array($type, ['Select', 'Radio'], true)) {
                $firstOpt = null;
                $opts = $field['options'] ?? [];
                if (is_array($opts)) {
                    foreach ($opts as $opt) {
                        if (is_array($opt) && array_key_exists('value', $opt)) {
                            $firstOpt = $opt['value'];
                            break;
                        }
                        if (is_scalar($opt)) {
                            $firstOpt = $opt;
                            break;
                        }
                    }
                }
                if ($firstOpt !== null) {
                    $defaults[$fn] = $firstOpt;
                } else {
                    $defaults[$fn] = '';
                }
                continue;
            }

            $defaults[$fn] = '';
        }

        return array_merge($defaults, $data);
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function httpStatusForCreateErrors(array $errors): int
    {
        if (isset($errors['posttype'])) {
            return 404;
        }
        if (isset($errors['database']) || isset($errors['exception'])) {
            return 500;
        }

        return 422;
    }

    /**
     * @param array<string, mixed> $errors
     * @return array<string, mixed>
     */
    private function exposeCreateErrors(array $errors, int $status): array
    {
        $debug = defined('APP_DEBUG') && APP_DEBUG;
        if ($debug) {
            return $errors;
        }
        if ($status === 422) {
            return $errors;
        }

        return ['contact' => ['Try again later']];
    }

    /**
     * Lang từ JSON/form: chỉ chấp nhận mã ngắn (en, vi, …).
     */
    private function sanitizeRequestLang(string $raw): string
    {
        $raw = strtolower(trim($raw));
        if ($raw === '') {
            return '';
        }
        if (!preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $raw)) {
            return '';
        }

        return $raw;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseBody(): array
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($ct, 'application/json') !== false) {
            $raw = (string) file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    private function isValidEmail(string $email): bool
    {
        if ($email === '') {
            return false;
        }
        $flags = defined('FILTER_FLAG_EMAIL_UNICODE') ? FILTER_FLAG_EMAIL_UNICODE : 0;
        if (filter_var($email, FILTER_VALIDATE_EMAIL, $flags)) {
            return true;
        }
        if (!function_exists('idn_to_ascii')) {
            return false;
        }
        $at = strrpos($email, '@');
        if ($at === false || $at === 0) {
            return false;
        }
        $local = substr($email, 0, $at);
        $domain = substr($email, $at + 1);
        if ($domain === '') {
            return false;
        }
        $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
        $ascii = @idn_to_ascii($domain, IDNA_DEFAULT, $variant);
        if ($ascii === false || $ascii === '') {
            return false;
        }
        return (bool) filter_var($local . '@' . $ascii, FILTER_VALIDATE_EMAIL, $flags);
    }
}
