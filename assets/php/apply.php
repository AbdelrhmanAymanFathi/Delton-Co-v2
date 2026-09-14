<?php
/**
 * Delton Facility Management - Job Application Handler
 *
 * Receives submissions from careers.html, saves a copy to admin/applications.json
 * so the admin panel can review it, and forwards the application data by email to
 * the company's configured contact address (default: info@delton-eg.com).
 *
 * Response: JSON. Works with HostGator / any shared PHP hosting.
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

$CONTENT_FILE  = __DIR__ . '/../../admin/content.json';
$APPS_DIR      = __DIR__ . '/../../admin';
$APPS_FILE     = $APPS_DIR . '/applications.json';
$UPLOAD_DIR    = __DIR__ . '/../../uploads/applications';
$MAX_APPS_KEEP = 500;
$MAX_CV_BYTES  = 5 * 1024 * 1024;

function clean($v) {
    return trim(strip_tags((string)($v === null ? '' : $v)));
}

function json_result($ok, $msg, $extra = []) {
    echo json_encode(array_merge(['ok' => $ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// ---- Read the admin-managed contact email (falls back to the official one) ----
$contactEmail = 'info@delton-eg.com';
if (file_exists($CONTENT_FILE)) {
    $cf = json_decode(file_get_contents($CONTENT_FILE), true);
    $cfgEmail = $cf['contact']['email'] ?? '';
    if ($cfgEmail !== '') $contactEmail = $cfgEmail;
}

// ---- Honeypot: bots fill hidden "website" field ----
if (clean($_POST['website'] ?? '') !== '') {
    json_result(true, 'ok');
}

// ---- Basic rate limiting: max 5 submissions per session per minute ----
$rateKey = 'delton_apply_' . date('YmdHi');
$hits = (int)($_SESSION['apply_rate'][$rateKey] ?? 0);
if ($hits >= 5) {
    json_result(false, 'rate_limited');
}
$_SESSION['apply_rate'][$rateKey] = $hits + 1;

// ---- Read & sanitize fields ----
$name          = clean($_POST['name'] ?? '');
$phone         = clean($_POST['phone'] ?? '');
$email         = clean($_POST['email'] ?? '');
$position      = clean($_POST['position'] ?? '');
$city          = clean($_POST['city'] ?? '');
$experience    = clean($_POST['experience'] ?? '');
$qualification = clean($_POST['qualification'] ?? '');
$message       = clean($_POST['message'] ?? '');
$lang          = clean($_POST['lang'] ?? 'ar');

$errorMsg = $lang === 'en'
    ? 'Please complete all required fields correctly.'
    : 'يرجى التأكد من ملء جميع الحقول الإلزامية بشكل صحيح.';

// ---- Validate required fields ----
if ($name === '' || $phone === '' || $email === '' || $position === '' || $city === '') {
    json_result(false, $errorMsg, ['code' => 'validation']);
}

if (!preg_match('/^01[0125][0-9]{8}$/', $phone)) {
    json_result(false, $errorMsg, ['code' => 'phone']);
}

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

// ---- Optional CV upload — PDF ONLY ----
$cvPath = '';
if (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
        json_result(false, $lang === 'en' ? $errorMsg : 'الملف المرفق غير صالح أو يتجاوز الحجم المسموح به (5 ميجابايت).', ['code' => 'cv_pdf']);
    }
    if ($_FILES['cv']['size'] > $MAX_CV_BYTES) {
        json_result(false, $lang === 'en' ? $errorMsg : 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت.', ['code' => 'cv_pdf']);
    }
    $ext = strtolower((string)pathinfo((string)$_FILES['cv']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        json_result(false, $lang === 'en' ? 'Only PDF files are accepted for the CV.' : 'السيرة الذاتية يجب أن تكون بصيغة PDF فقط.', ['code' => 'cv_pdf']);
    }
    $finfoMime = '';
    if (function_exists('finfo_open') && is_uploaded_file((string)$_FILES['cv']['tmp_name'])) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        if ($fi) {
            $finfoMime = (string)finfo_file($fi, (string)$_FILES['cv']['tmp_name']);
            finfo_close($fi);
        }
    }
    if ($finfoMime !== '' && $finfoMime !== 'application/pdf' && $finfoMime !== 'application/octet-stream') {
        json_result(false, $lang === 'en' ? 'CV file is not a valid PDF.' : 'الملف ليس ملف PDF صالح.', ['code' => 'cv_pdf']);
    }
    if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);
    $cvName = 'cv_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6) . '.pdf';
    if (move_uploaded_file((string)$_FILES['cv']['tmp_name'], $UPLOAD_DIR . '/' . $cvName)) {
        $cvPath = 'uploads/applications/' . $cvName;
    }
}

// ---- Build the record ----
$record = [
    'id'            => 'app_' . substr(md5(uniqid('', true)), 0, 10),
    'created_at'    => date('Y-m-d H:i:s'),
    'name'          => $name,
    'phone'         => $phone,
    'email'         => $email,
    'position'      => $position,
    'city'          => $city,
    'experience'    => $experience,
    'qualification' => $qualification,
    'message'       => $message,
    'cv'            => $cvPath,
];

// ---- Save copy to admin/applications.json ----
$appsFileOk = true;
if (is_writable($APPS_DIR) || !file_exists($APPS_FILE)) {
    $apps = json_decode(@file_get_contents($APPS_FILE), true);
    if (!is_array($apps)) $apps = [];
    $apps[] = $record;
    if (count($apps) > $MAX_APPS_KEEP) $apps = array_slice($apps, -$MAX_APPS_KEEP);
    $appsFileOk = (bool)@file_put_contents(
        $APPS_FILE,
        json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

// ---- Compose & send the email ----
$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'delton-eg.com';
$hostSafe = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $host);
$siteBase = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
          . '://' . $host;

$subject = 'طلب توظيف جديد: ' . $position . ' - ' . $name;

$cvLine = $cvPath !== ''
    ? '<tr><td><strong>السيرة الذاتية / CV:</strong></td><td><a href="' . $siteBase . '/' . htmlspecialchars($cvPath, ENT_QUOTES, 'UTF-8') . '">' . $siteBase . '/' . htmlspecialchars($cvPath, ENT_QUOTES, 'UTF-8') . '</a></td></tr>'
    : '';

$senderIp = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
$uaShort  = !empty($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 120) : '';

$body = "<html><body dir='rtl' style='font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111C38;'>"
      . "<h3 style='color:#C9A227;margin-bottom:4px;'>طلب توظيف جديد / New Job Application</h3>"
      . "<p style='color:#64748B;margin-top:0;'>" . htmlspecialchars($record['created_at']) . " &mdash; IP: " . htmlspecialchars($senderIp) . "</p>"
      . "<table cellpadding='6' style='border-collapse:collapse;width:100%;max-width:640px;'>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;width:180px;'>الاسم / Name</td><td>" . htmlspecialchars($name) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>الهاتف / Phone</td><td dir='ltr'>" . htmlspecialchars($phone) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>البريد / Email</td><td dir='ltr'>" . htmlspecialchars($email) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>الوظيفة / Position</td><td>" . htmlspecialchars($position) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>المحافظة / City</td><td>" . htmlspecialchars($city) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>الخبرة / Experience</td><td>" . htmlspecialchars($experience) . "</td></tr>"
      . "<tr><td style='background:#F1F5F9;font-weight:bold;'>المؤهل / Qualification</td><td>" . htmlspecialchars($qualification) . "</td></tr>"
      . $cvLine
      . ($message !== ''
          ? "<tr><td style='background:#F1F5F9;font-weight:bold;'>نبذة / Message</td><td>" . nl2br(htmlspecialchars($message)) . "</td></tr>"
          : '')
      . "</table>"
      . ($uaShort !== '' ? "<p style='color:#94A3B8;font-size:11px;margin-top:18px;'>UA: " . htmlspecialchars($uaShort) . "</p>" : '')
      . "</body></html>";

$replyToEmail = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : $contactEmail;

$headers   = [];
$headers[] = "From: Delton Careers <no-reply@{$hostSafe}>";
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

// ---- Notify admin of the application count (optional lightweight) ----
$ok = $mailSent || $appsFileOk;
if (!$mailSent && !$appsFileOk) {
    json_result(false, $lang === 'en'
        ? 'We could not process your application right now. Please try again later or email us directly.'
        : 'لم نتمكن من معالجة طلبك حالياً. يرجى المحاولة لاحقاً أو مراسلتنا مباشرة عبر البريد الإلكتروني.', ['code' => 'server']);
}

json_result(true, 'ok');