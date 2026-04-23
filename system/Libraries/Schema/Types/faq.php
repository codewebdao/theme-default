<?php
/**
 * Schema type: FAQPage (câu hỏi thường gặp)
 *
 * File trả về array. Nhận $context từ scope. Payload: mainEntity (array of Question).
 *
 * @package System\Libraries\Schema\Types
 * @since 1.0.0
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

$baseUrl   = rtrim(base_url(), '/');
$payload   = isset($context->payload) ? $context->payload : null;
$questions = [];

if ($payload) {
    $raw = is_object($payload) ? ($payload->mainEntity ?? $payload->questions ?? $payload->faq ?? []) : ($payload['mainEntity'] ?? $payload['questions'] ?? $payload['faq'] ?? []);
    if (is_array($raw)) {
        foreach ($raw as $q) {
            $rawQues = is_array($q) ? ($q['question'] ?? $q['name'] ?? '') : ($q->question ?? $q->name ?? '');
            $rawAnsw = is_array($q) ? ($q['answer'] ?? $q['acceptedAnswer'] ?? '') : ($q->answer ?? $q->acceptedAnswer ?? '');
            $ques = schema_safe_string($rawQues);
            $answ = schema_safe_string($rawAnsw);
            if ($ques !== '' || $answ !== '') {
                $questions[] = [
                    '@type'          => 'Question',
                    'name'           => $ques,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $answ,
                    ],
                ];
            }
        }
    }
}

if (empty($questions)) {
    return [];
}

return [
    '@type'      => 'FAQPage',
    '@id'        => $baseUrl . '/#faqpage',
    'mainEntity' => $questions,
];
