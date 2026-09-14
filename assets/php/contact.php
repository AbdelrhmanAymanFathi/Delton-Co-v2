<?php
/**
 * Delton Facility Management - Contact Inquiry Handler
 *
 * Receives submissions from index.html Contact form, saves a copy to
 * admin/contact_inquiries.json so the admin panel can review it, and
 * forwards the inquiry by email to the company's configured address.
 *
 * Response: JSON. Works with HostGator / any shared PHP hosting.
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

$CONTENT_FILE    = __DIR__ . '/../../admin/content.json';
$INQUIRIES_DIR   = __DIR__ . '/../../admin';
$INQUIRIES_FILE  = $INQUIRIES_DIR . '/contact_inquiries.json';
$MAX_KEEP        = 500;

/**
 * @param mixed $v
 * @return string
 */
function clean($v): string {
    return trim(strip_tags((string)($v === null ? '' : $v)));
}

/**
 * @param bool        $ok
 * @param string      $msg
 * @param array       $extra
 * @return never
 */
function json_result(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['ok' => $ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// ---- Read the admin-managed contact email (falls back to official) ----
$contactEmail = 'info@delton-eg.com';
if (file_exists($CONTENT_FILE)) {
    $cf = json_decode((string)file_get_contents($CONTENT_FILE), true);
    $cfgEmail = $cf['contact']['email'] ?? '';
    if ($cfgEmail !== '') $contactEmail = $cfgEmail;
}

// ---- Honeypot: bots fill hidden "website" field ----
if (clean($_POST['website'] ?? '') !== '') {
    json_result(true, 'ok');
}

// ---- Rate limiting: max 5 submissions per session per minute ----
$rateKey = 'delton_contact_' . date('YmdHi');
$hits = (int)($_SESSION['contact_rate'][$rateKey] ?? 0);
if ($hits >= 5) {
    json_result(false, 'rate_limited', ['code' => 'rate_limited']);
}
$_SESSION['contact_rate'][$rateKey] = $hits + 1;

// ---- Read & sanitize fields ----
$name     = clean($_POST['name'] ?? '');
$company  = clean($_POST['company'] ?? '');
$phone    = clean($_POST['phone'] ?? '');
$email    = clean($_POST['email'] ?? '');
$service  = clean($_POST['service'] ?? '');
$message  = clean($_POST['message'] ?? '');
$lang     = clean($_POST['lang'] ?? 'ar');

$errorMsg = $lang === 'en'
    ? 'Please complete all required fields correctly.'
    : 'يرجى التأكد من ملء جميع الحقول الإلزامية بشكل صحيح.';

// ---- Validate required fields ----
if ($name === '' || $company === '' || $phone === '' || $email === '' || $service === '' || $message === '') {
    json_result(false, $errorMsg, ['code' => 'validation']);
}

// ---- Egyptian phone validation ----
if (!preg_match('/^01[0125][0-9]{8}$/', $phone)) {
    json_result(false, $errorMsg, ['code' => 'phone']);
}

// ---- Strict email validation ----
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_result(false, $errorMsg, ['code' => 'email']);
}
if (strlen($email) > 254 || preg_match('/\s/', $email)) {
    json_result(false, $errorMsg, ['code' => 'email']);
}
$emailDomain = explode('@', $email);
if (count($emailDomain) !== 2 || strpos($emailDomain[1], '.') === false || strlen($emailDomain[1]) < 3) {
    json_result(false, $errorMsg, ['code' => 'email']);
}

// ---- Build the record ----
$senderIp = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
$record = [
    'id'         => 'inq_' . substr(md5(uniqid('', true)), 0, 10),
    'created_at' => date('Y-m-d H:i:s'),
    'ip'         => $senderIp,
    'name'       => $name,
    'company'    => $company,
    'phone'      => $phone,
    'email'      => $email,
    'service'    => $service,
    'message'    => $message,
];

// ---- Save copy to admin/contact_inquiries.json ----
$inqFileOk = true;
if (is_writable($INQUIRIES_DIR) || !file_exists($INQUIRIES_FILE)) {
    $inqs = json_decode(@file_get_contents($INQUIRIES_FILE), true);
    if (!is_array($inqs)) $inqs = [];
    $inqs[] = $record;
    if (count($inqs) > $MAX_KEEP) $inqs = array_slice($inqs, -$MAX_KEEP);
    $inqFileOk = (bool)@file_put_contents(
        $INQUIRIES_FILE,
        json_encode($inqs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

// ---- Compose & send the email ----
$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'delton-eg.com';
$hostSafe = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $host);

$serviceLabels = [
    'all'         => 'حلول متكاملة (Comprehensive FM)',
    'cleaning'    => 'خدمات النظافة والتعقيم',
    'maintenance' => 'الصيانة الفنية والمقاولات',
    'landscaping' => 'تنسيق المسطحات الخضراء',
    'supplies'    => 'التوريدات وتوظيف العمالة',
    'hospitality' => 'خدمات الضيافة والبوفيه',
    'renovation'  => 'المقاولات والتجديدات',
    'other'       => 'أخرى',
];
$serviceLabel = $serviceLabels[$service] ?? $service;

$subject = 'استفسار جديد: ' . $serviceLabel . ' - ' . $name;

$uaShort = !empty($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 120) : '';

$body = "<html><body dir='rtl' style='font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111C38;'>"
      . "<h3 style='color:#C9A227;margin-bottom:4px;'>استفسار عميل جديد / New Contact Inquiry</h3>"
      . "<p style='color:#64748B;margin-top:0;'>" . htmlspecialchars($record['created_at']) . " &mdash; IP: " . htmlspecialchars($senderIp) . "</p>"
      . "<table cellpadding='6' style='border-collapse:collapse;width:100%;max-width:640px;'>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;width:180px;'>الاسم / Name</td><td>" . htmlspecialchars($name) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>الشركة / Company</td><td>" . htmlspecialchars($company) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>الهاتف / Phone</td><td dir='ltr'>" . htmlspecialchars($phone) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>البريد / Email</td><td dir='ltr'>" . htmlspecialchars($email) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>الخدمة / Service</td><td>" . htmlspecialchars($serviceLabel) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;vertical-align:top;'>الرسالة / Message</td><td style='white-space:pre-line;'>" . nl2br(htmlspecialchars($message)) . "</td></tr>"
      . "</table>"
      . ($uaShort !== '' ? "<p style='color:#94A3B8;font-size:11px;margin-top:18px;'>UA: " . htmlspecialchars($uaShort) . "</p>" : '')
      . "</body></html>";

$replyToEmail = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : $contactEmail;

$headers   = [];
$headers[] = "From: Delton Contact <no-reply@{$hostSafe}>";
$headers[] = "Reply-To: " . $replyToEmail;
$headers[] = "Return-Path: no-reply@{$hostSafe}";
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-Type: text/html; charset=UTF-8";
$headers[] = "X-Mailer: PHP/" . phpversion();
$headers[] = "X-Originating-IP: " . $senderIp;
$headersStr = implode("\r\n", $headers);

$mailSent = @mail(
    $contactEmail,
    '=?UTF-8?B?' . base64_encode($subject) . '?=',
    $body,
    $headersStr
);

$ok = $mailSent || $inqFileOk;
if (!$mailSent && !$inqFileOk) {
    json_result(false, $lang === 'en'
        ? 'We could not process your inquiry right now. Please try again later or call us directly.'
        : 'لم نتمكن من معالجة استفسارك حالياً. يرجى المحاولة لاحقاً أو الاتصال بنا مباشرة.', ['code' => 'server']);
}

json_result(true, 'ok');
