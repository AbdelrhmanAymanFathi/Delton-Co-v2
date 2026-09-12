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

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_result(false, $errorMsg, ['code' => 'email']);
}

// ---- Optional CV upload ----
$cvPath = '';
if (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
        json_result(false, $lang === 'en' ? $errorMsg : 'الملف المرفق غير صالح أو يتجاوز الحجم المسموح به (5 ميجابايت).', ['code' => 'cv']);
    }
    if ($_FILES['cv']['size'] > $MAX_CV_BYTES) {
        json_result(false, $lang === 'en' ? $errorMsg : 'الملف المرفق غير صالح أو يتجاوز الحجم المسموح به (5 ميجابايت).', ['code' => 'cv']);
    }
    $allowedExt = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        json_result(false, $lang === 'en' ? $errorMsg : 'الملف المرفق غير صالح أو يتجاوز الحجم المسموح به (5 ميجابايت).', ['code' => 'cv']);
    }
    if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);
    $cvName = 'cv_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;
    if (move_uploaded_file($_FILES['cv']['tmp_name'], $UPLOAD_DIR . '/' . $cvName)) {
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
$siteBase = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
          . '://' . ($_SERVER['HTTP_HOST'] ?? 'delton-eg.com');

$subject = 'طلب توظيف جديد: ' . $position . ' - ' . $name;

$cvLine = $cvPath !== ''
    ? '<tr><td><strong>السيرة الذاتية / CV:</strong></td><td><a href="' . $siteBase . '/' . $cvPath . '">' . $siteBase . '/' . $cvPath . '</a></td></tr>'
    : '';

$body = "<html><body dir='rtl' style='font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111C38;'>"
      . "<h3 style='color:#C9A227;margin-bottom:4px;'>طلب توظيف جديد / New Job Application</h3>"
      . "<p style='color:#64748B;margin-top:0;'>" . $record['created_at'] . "</p>"
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
      . '</table></body></html>';

$headers  = "From: Delton Careers <no-reply@{$_SERVER['HTTP_HOST']}>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

$mailSent = @mail(
    $contactEmail,
    '=?UTF-8?B?' . base64_encode($subject) . '?=',
    $body,
    $headers
);

// ---- Notify admin of the application count (optional lightweight) ----
$ok = $mailSent || $appsFileOk;
if (!$mailSent && !$appsFileOk) {
    json_result(false, $lang === 'en'
        ? 'We could not process your application right now. Please try again later or email us directly.'
        : 'لم نتمكن من معالجة طلبك حالياً. يرجى المحاولة لاحقاً أو مراسلتنا مباشرة عبر البريد الإلكتروني.', ['code' => 'server']);
}

json_result(true, 'ok');