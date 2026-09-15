<?php
/**
 * Delton Facility Management - Full Content Administration Panel
 * Standalone PHP dashboard for HostGator shared hosting.
 * No SQL database needed; reads & writes to slider.json + content.json.
 *
 * Tabs: Slider | Services | Why Us | Clients | Stats | About | Settings
 */

session_start();

// ======================= CONFIG =======================
$ADMIN_PIN     = "delton2026"; // Change your admin password here
$SLIDER_JSON   = __DIR__ . "/slider.json";
$CONTENT_JSON  = __DIR__ . "/content.json";
$HERO_DIR      = "assets/images/hero/";
$SERVICES_DIR  = "assets/images/services/";
$CLIENTS_DIR   = "assets/images/clientLogo/";
$BRAND_DIR     = "assets/images/brand/";
$APPLICATIONS_JSON  = __DIR__ . "/applications.json";
$INQUIRIES_JSON     = __DIR__ . "/contact_inquiries.json";

// ======================= HELPERS =======================
/**
 * @param string $k
 * @param mixed  $def
 * @return string
 */
function req(string $k, $def = ''): string {
    return trim((string)($_POST[$k] ?? $def));
}

/**
 * @param string $file
 * @return array|null
 */
function json_load(string $file): ?array {
    if (!file_exists($file)) return null;
    $data = json_decode((string)file_get_contents($file), true);
    return is_array($data) ? $data : null;
}

/**
 * @param string $file
 * @param array  $data
 * @return int|false
 */
function json_save(string $file, array $data) {
    return file_put_contents(
        $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

/**
 * @param string $str
 * @return array<int, string>
 */
function text_to_lines(string $str): array {
    $lines = preg_split('/\r\n|\r|\n/', $str);
    $out = [];
    foreach ($lines as $line) {
        $t = trim((string)$line);
        if ($t !== '') $out[] = $t;
    }
    return $out;
}

/**
 * @param string $field
 * @param string $rel_dir
 * @return string
 */
function upload_image(string $field, string $rel_dir): string {
    if (empty($_FILES[$field]['name'])) return '';
    $f = $_FILES[$field];
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'avif'];
    $ext = strtolower((string)pathinfo((string)$f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true) || (int)$f['error'] !== UPLOAD_ERR_OK) return '';
    $abs_dir = __DIR__ . '/../' . $rel_dir;
    if (!is_dir($abs_dir)) mkdir($abs_dir, 0755, true);
    $name = 'upload-' . time() . '-' . rand(100, 999) . '.' . $ext;
    if (move_uploaded_file((string)$f['tmp_name'], $abs_dir . $name)) {
        return $rel_dir . $name;
    }
    return '';
}

/**
 * @param string $path
 * @return string
 */
function thumb_src(string $path): string {
    if ($path === '') return '';
    return (preg_match('#^https?://#i', $path) === 1) ? $path : ('../' . $path);
}

/**
 * @param mixed $str
 * @return string
 */
function e($str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function default_content() {
    return [
        'stats'          => [],
        'services'       => ['header' => ['ar' => [], 'en' => []], 'items' => []],
        'whyUs'          => ['header' => ['ar' => [], 'en' => []], 'cards' => []],
        'clients'        => [],
        'clientsHeader'  => ['ar' => [], 'en' => []],
        'about'          => ['ar' => [], 'en' => []],
        'contact'        => ['phones' => [], 'email' => '', 'ar' => [], 'en' => []],
        'footer'         => ['phones' => '', 'ar' => [], 'en' => []],
        'careers'        => [
            'header'   => ['ar' => [], 'en' => []],
            'whyJoin'  => [],
            'vacancies' => [],
            'lists'    => [
                'jobRoles'     => [],
                'experiences'  => [],
                'governorates' => []
            ]
        ],
        'branding'       => [
            'navbar' => ['image' => 'newlogo.jpeg', 'width' => '110', 'height' => '52'],
            'footer' => ['image' => 'assets/images/logoFooter.png', 'width' => '150', 'height' => '56']
        ]
    ];
}

function load_content() {
    $d = json_load(__DIR__ . "/content.json");
    if ($d === null) $d = [];
    $def = default_content();
    foreach ($def as $k => $v) {
        if (!array_key_exists($k, $d)) $d[$k] = $v;
    }
    return $d;
}

$content = load_content();

// ======================= AUTH =======================
$message = "";
$messageType = "";

$ADMIN_MAX_LOGIN_ATTEMPTS = 5;
$ADMIN_LOCKOUT_SECONDS  = 300;

function admin_login_rate_ok() {
    global $ADMIN_MAX_LOGIN_ATTEMPTS, $ADMIN_LOCKOUT_SECONDS;
    $now = time();
    if (empty($_SESSION['admin_login_attempts'])) {
        $_SESSION['admin_login_attempts'] = [];
    }
    $_SESSION['admin_login_attempts'] = array_filter(
        $_SESSION['admin_login_attempts'],
        function ($t) use ($now, $ADMIN_LOCKOUT_SECONDS) { return ($now - (int)$t) < $ADMIN_LOCKOUT_SECONDS; }
    );
    $remaining = $ADMIN_LOCKOUT_SECONDS - (empty($_SESSION['admin_login_attempts']) ? 0 : ($now - (int)end($_SESSION['admin_login_attempts'])));
    if (count($_SESSION['admin_login_attempts']) >= $ADMIN_MAX_LOGIN_ATTEMPTS) {
        return ['ok' => false, 'wait' => max(1, $remaining)];
    }
    return ['ok' => true, 'remaining' => $ADMIN_MAX_LOGIN_ATTEMPTS - count($_SESSION['admin_login_attempts'])];
}

function admin_record_failed_login() {
    if (empty($_SESSION['admin_login_attempts'])) {
        $_SESSION['admin_login_attempts'] = [];
    }
    $_SESSION['admin_login_attempts'][] = time();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // ---- LOGIN ----
    if ($_POST['action'] === 'login') {
        $rate = admin_login_rate_ok();
        if (!$rate['ok']) {
            $message = "محاولات تسجيل دخول كثيرة جداً. يرجى المحاولة بعد {$rate['wait']} ثانية / Too many attempts. Please wait {$rate['wait']}s.";
            $messageType = "error";
        } elseif (req('pin') === $ADMIN_PIN) {
            $_SESSION['delton_admin_logged'] = true;
            unset($_SESSION['admin_login_attempts']);
            header("Location: index.php");
            exit;
        } else {
            admin_record_failed_login();
            $rate = admin_login_rate_ok();
            $left = $rate['ok'] ? " — متبقية: {$rate['remaining']}" : '';
            $message = "كلمة المرور غير صحيحة / Invalid Admin Password{$left}";
            $messageType = "error";
        }
    }

    if (!empty($_SESSION['delton_admin_logged'])) {
        $ok = false; $msg = "";

        // ---------- SLIDER ----------
        if ($_POST['action'] === 'save_slide') {
            $slides = json_load($SLIDER_JSON) ?: [];
            $slide_id = req('slide_id') !== '' ? intval(req('slide_id')) : null;
            $image_path = req('image_url');
            $up = upload_image('image_file', $HERO_DIR);
            if ($up !== '') $image_path = $up;
            if ($image_path === '') $image_path = $HERO_DIR . "hero-1.svg";

            $new_slide = [
                "id" => $slide_id ?: (count($slides) ? max(array_column($slides, 'id')) + 1 : 1),
                "image" => $image_path,
                "ar" => [
                    "badge" => req('ar_badge'), "title" => req('ar_title'), "description" => req('ar_description'),
                    "ctaPrimary" => req('ar_cta_primary', 'طلب عرض سعر'), "ctaPrimaryLink" => req('ar_cta_primary_link', '#contact'),
                    "ctaSecondary" => req('ar_cta_secondary', 'استكشف خدماتنا'), "ctaSecondaryLink" => req('ar_cta_secondary_link', '#services')
                ],
                "en" => [
                    "badge" => req('en_badge'), "title" => req('en_title'), "description" => req('en_description'),
                    "ctaPrimary" => req('en_cta_primary', 'Request a Quote'), "ctaPrimaryLink" => req('en_cta_primary_link', '#contact'),
                    "ctaSecondary" => req('en_cta_secondary', 'Explore Services'), "ctaSecondaryLink" => req('en_cta_secondary_link', '#services')
                ]
            ];
            $found = false;
            foreach ($slides as $k => $s) {
                if (($s['id'] ?? null) == $slide_id) { $slides[$k] = $new_slide; $found = true; break; }
            }
            if (!$found) $slides[] = $new_slide;
            $ok = json_save($SLIDER_JSON, $slides);
            $msg = $ok ? "تم حفظ الشريحة وتحديث الموقع مباشرة!" : "خطأ في أذونات الكتابة على slider.json";
        }

        if ($_POST['action'] === 'delete_slide') {
            $slides = json_load($SLIDER_JSON) ?: [];
            $del = intval(req('delete_id'));
            $slides = array_values(array_filter($slides, function ($s) use ($del) { return ($s['id'] ?? 0) !== $del; }));
            $ok = json_save($SLIDER_JSON, $slides);
            $msg = $ok ? "تم حذف الشريحة بنجاح!" : "خطأ في الحذف";
        }

        // ---------- SERVICES ----------
        if ($_POST['action'] === 'save_service') {
            $items = $content['services']['items'];
            $sid = req('service_id');
            $img = req('image_url');
            $up = upload_image('image_file', $SERVICES_DIR);
            if ($up !== '') $img = $up;
            $g1 = req('gallery_1');
            $g2 = req('gallery_2');
            if ($up !== '') $g1 = '';
            $gallery = array_values(array_filter([
                $up !== '' ? $up : ($g1 !== '' ? $g1 : $img),
                $g2
            ], function ($x) { return $x !== ''; }));
            if (empty($gallery)) $gallery = [$img];

            $item = [
                "id" => $sid !== '' ? $sid : ('service-' . substr(md5(microtime()), 0, 6)),
                "icon" => req('icon', 'fa-gear'),
                "image" => $img,
                "gallery" => $gallery,
                "ar" => [
                    "title" => req('ar_title'), "shortDesc" => req('ar_shortDesc'), "modalTitle" => req('ar_modalTitle'),
                    "overview" => req('ar_overview'), "tasks" => text_to_lines(req('ar_tasks')), "equipment" => req('ar_equipment')
                ],
                "en" => [
                    "title" => req('en_title'), "shortDesc" => req('en_shortDesc'), "modalTitle" => req('en_modalTitle'),
                    "overview" => req('en_overview'), "tasks" => text_to_lines(req('en_tasks')), "equipment" => req('en_equipment')
                ]
            ];

            $found = false;
            foreach ($items as $k => $it) {
                if (($it['id'] ?? '') === $sid) { $items[$k] = $item; $found = true; break; }
            }
            if (!$found) $items[] = $item;
            $content['services']['items'] = $items;
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ الخدمة بنجاح!" : "خطأ في أذونات الكتابة على content.json";
        }

        if ($_POST['action'] === 'delete_service') {
            $del = req('delete_id');
            $content['services']['items'] = array_values(array_filter($content['services']['items'], function ($it) use ($del) { return ($it['id'] ?? '') !== $del; }));
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حذف الخدمة بنجاح!" : "خطأ في الحذف";
        }

        if ($_POST['action'] === 'save_services_header') {
            $content['services']['header']['ar'] = [
                "badge" => req('h_ar_badge'), "title" => req('h_ar_title'), "subtitle" => req('h_ar_subtitle'),
                "viewDetails" => req('h_ar_view'), "closeModal" => req('h_ar_close'), "requestService" => req('h_ar_request')
            ];
            $content['services']['header']['en'] = [
                "badge" => req('h_en_badge'), "title" => req('h_en_title'), "subtitle" => req('h_en_subtitle'),
                "viewDetails" => req('h_en_view'), "closeModal" => req('h_en_close'), "requestService" => req('h_en_request')
            ];
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ عنوان القسم بنجاح!" : "خطأ في الحفظ";
        }

        // ---------- WHY US ----------
        if ($_POST['action'] === 'save_why') {
            $cards = $content['whyUs']['cards'];
            $wid = req('why_id');
            $card = [
                "icon" => req('icon', 'fa-award'),
                "ar" => ["title" => req('ar_title'), "desc" => req('ar_desc')],
                "en" => ["title" => req('en_title'), "desc" => req('en_desc')]
            ];
            $found = false;
            if ($wid !== '') {
                $widInt = intval($wid);
                if ($widInt >= 0 && $widInt < count($cards)) {
                    $cards[$widInt] = $card;
                    $found = true;
                }
            }
            if (!$found) $cards[] = $card;
            $content['whyUs']['cards'] = array_values($cards);
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ البطاقة بنجاح!" : "خطأ في الحفظ";
        }

        if ($_POST['action'] === 'delete_why') {
            $del = intval(req('delete_id'));
            $cards = [];
            foreach ($content['whyUs']['cards'] as $k => $c) { if ($k !== $del) $cards[] = $c; }
            $content['whyUs']['cards'] = array_values($cards);
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حذف البطاقة بنجاح!" : "خطأ في الحذف";
        }

        if ($_POST['action'] === 'save_why_header') {
            $content['whyUs']['header']['ar'] = [
                "badge" => req('h_ar_badge'), "title" => req('h_ar_title'), "subtitle" => req('h_ar_subtitle')
            ];
            $content['whyUs']['header']['en'] = [
                "badge" => req('h_en_badge'), "title" => req('h_en_title'), "subtitle" => req('h_en_subtitle')
            ];
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ عنوان القسم بنجاح!" : "خطأ في الحفظ";
        }

        // ---------- CLIENTS ----------
        if ($_POST['action'] === 'save_client') {
            $clients = $content['clients'];
            $cid = req('client_id');
            $img = req('image_url');
            $up = upload_image('image_file', $CLIENTS_DIR);
            if ($up !== '') $img = $up;
            $client = [
                "id" => $cid !== '' ? $cid : ('client-' . substr(md5(microtime()), 0, 6)),
                "sector" => req('sector', 'corporate'),
                "image" => $img,
                "ar" => ["name" => req('ar_name')],
                "en" => ["name" => req('en_name')]
            ];
            $found = false;
            foreach ($clients as $k => $c) {
                if (($c['id'] ?? '') === $cid) { $clients[$k] = $client; $found = true; break; }
            }
            if (!$found) $clients[] = $client;
            $content['clients'] = $clients;
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ العميل بنجاح!" : "خطأ في الحفظ";
        }

        if ($_POST['action'] === 'delete_client') {
            $del = req('delete_id');
            $content['clients'] = array_values(array_filter($content['clients'], function ($c) use ($del) { return ($c['id'] ?? '') !== $del; }));
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حذف العميل بنجاح!" : "خطأ في الحذف";
        }

        if ($_POST['action'] === 'save_clients_header') {
            $content['clientsHeader']['ar'] = [
                "badge" => req('h_ar_badge'), "title" => req('h_ar_title'), "subtitle" => req('h_ar_subtitle'),
                "filterAll" => req('h_ar_filterAll'), "filterBanking" => req('h_ar_filterBanking'), "filterCorporate" => req('h_ar_filterCorporate')
            ];
            $content['clientsHeader']['en'] = [
                "badge" => req('h_en_badge'), "title" => req('h_en_title'), "subtitle" => req('h_en_subtitle'),
                "filterAll" => req('h_en_filterAll'), "filterBanking" => req('h_en_filterBanking'), "filterCorporate" => req('h_en_filterCorporate')
            ];
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ عنوان القسم بنجاح!" : "خطأ في الحفظ";
        }

        // ---------- STATS ----------
        if ($_POST['action'] === 'save_stats') {
            $stats = [];
            for ($i = 1; $i <= 4; $i++) {
                $stats[] = [
                    "ar" => ["num" => req("s{$i}_ar_num"), "label" => req("s{$i}_ar_label"), "desc" => req("s{$i}_ar_desc")],
                    "en" => ["num" => req("s{$i}_en_num"), "label" => req("s{$i}_en_label"), "desc" => req("s{$i}_en_desc")]
                ];
            }
            $content['stats'] = $stats;
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ الإحصائيات بنجاح!" : "خطأ في الحفظ";
        }

        // ---------- ABOUT ----------
        if ($_POST['action'] === 'save_about') {
            foreach (['ar', 'en'] as $l) {
                $a = [
                    "badge" => req("about_{$l}_badge"), "title" => req("about_{$l}_title"), "lead" => req("about_{$l}_lead"),
                    "p1" => req("about_{$l}_p1"), "p2" => req("about_{$l}_p2"), "pillarsTitle" => req("about_{$l}_pillarsTitle"),
                    "pillars" => [
                        req("about_{$l}_pillar1"), req("about_{$l}_pillar2"), req("about_{$l}_pillar3"), req("about_{$l}_pillar4")
                    ],
                    "downloadAr" => req("about_{$l}_downloadAr"), "downloadEn" => req("about_{$l}_downloadEn"),
                    "companyInfo" => [
                        "legalLabel" => req("about_{$l}_legalLabel"), "legalValue" => req("about_{$l}_legalValue"),
                        "foundedLabel" => req("about_{$l}_foundedLabel"), "foundedValue" => req("about_{$l}_foundedValue"),
                        "headquartersLabel" => req("about_{$l}_headquartersLabel"), "headquartersValue" => req("about_{$l}_headquartersValue"),
                        "scopeLabel" => req("about_{$l}_scopeLabel"), "scopeValue" => req("about_{$l}_scopeValue"),
                        "quote" => req("about_{$l}_quote")
                    ]
                ];
                $content['about'][$l] = $a;
            }
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ قسم \"عن ديلتون\" بنجاح!" : "خطأ في الحفظ";
        }

        // ---------- SETTINGS (Contact + Footer) ----------
        if ($_POST['action'] === 'save_settings') {
            $content['contact']['phones'] = array_values(array_filter([req('phone1'), req('phone2'), req('phone3')], function ($p) { return $p !== ''; }));
            $content['contact']['email'] = req('email');
            $content['footer']['phones'] = req('footer_phones');

            $navLogo = req('navbar_logo_url', 'newlogo.jpeg');
            $navUpload = upload_image('navbar_logo_file', $BRAND_DIR);
            if ($navUpload !== '') $navLogo = $navUpload;
            $footerLogo = req('footer_logo_url', 'assets/images/logoFooter.png');
            $footerUpload = upload_image('footer_logo_file', $BRAND_DIR);
            if ($footerUpload !== '') $footerLogo = $footerUpload;

            $content['branding']['navbar'] = [
                'image' => $navLogo,
                'width' => req('navbar_logo_width', '110'),
                'height' => req('navbar_logo_height', '52')
            ];
            $content['branding']['footer'] = [
                'image' => $footerLogo,
                'width' => req('footer_logo_width', '150'),
                'height' => req('footer_logo_height', '56')
            ];

            foreach (['ar', 'en'] as $l) {
                $content['contact'][$l] = [
                    "badge" => req("c_{$l}_badge"), "title" => req("c_{$l}_title"), "subtitle" => req("c_{$l}_subtitle"),
                    "addressTitle" => req("c_{$l}_addressTitle"), "address" => req("c_{$l}_address"),
                    "phoneTitle" => req("c_{$l}_phoneTitle"), "emailTitle" => req("c_{$l}_emailTitle"),
                    "hoursTitle" => req("c_{$l}_hoursTitle"), "hours" => req("c_{$l}_hours")
                ];
                $content['footer'][$l] = [
                    "aboutText" => req("f_{$l}_about"),
                    "quickLinks" => req("f_{$l}_quickLinks"), "ourServices" => req("f_{$l}_ourServices"),
                    "contactInfo" => req("f_{$l}_contactInfo"), "copyright" => req("f_{$l}_copyright"),
                    "address" => req("f_{$l}_address")
                ];
            }
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ الإعدادات بنجاح!" : "خطأ في الحفظ";
        }

        // ---------- JOB APPLICATIONS ----------
        if ($_POST['action'] === 'delete_application') {
            $apps = json_load($APPLICATIONS_JSON) ?: [];
            $del = req('delete_id');
            $apps = array_values(array_filter($apps, function ($a) use ($del) { return ($a['id'] ?? '') !== $del; }));
            $ok = json_save($APPLICATIONS_JSON, $apps);
            $msg = $ok ? "تم حذف الطلب بنجاح!" : "خطأ في الحذف";
        }

        // ---------- CONTACT INQUIRIES ----------
        if ($_POST['action'] === 'delete_inquiry') {
            $inqs = json_load($INQUIRIES_JSON) ?: [];
            $del = req('delete_id');
            $inqs = array_values(array_filter($inqs, function ($i) use ($del) { return ($i['id'] ?? '') !== $del; }));
            $ok = json_save($INQUIRIES_JSON, $inqs);
            $msg = $ok ? "تم حذف الرسالة بنجاح!" : "خطأ في الحذف";
        }

        // ---------- CAREERS: Header ----------
        if ($_POST['action'] === 'save_careers_header') {
            $content['careers']['header']['ar'] = [
                "badge"        => req('h_ar_badge'),
                "title"        => req('h_ar_title'),
                "subtitle"     => req('h_ar_subtitle'),
                "lead"         => req('h_ar_lead'),
                "whyBadge"     => req('h_ar_whyBadge'),
                "whyTitle"     => req('h_ar_whyTitle'),
                "whySubtitle"  => req('h_ar_whySubtitle'),
                "vacBadge"     => req('h_ar_vacBadge'),
                "vacTitle"     => req('h_ar_vacTitle'),
                "vacSubtitle"  => req('h_ar_vacSubtitle'),
                "formTitle"    => req('h_ar_formTitle'),
                "formDesc"     => req('h_ar_formDesc'),
                "directTitle"  => req('h_ar_directTitle'),
                "directNote"   => req('h_ar_directNote')
            ];
            $content['careers']['header']['en'] = [
                "badge"        => req('h_en_badge'),
                "title"        => req('h_en_title'),
                "subtitle"     => req('h_en_subtitle'),
                "lead"         => req('h_en_lead'),
                "whyBadge"     => req('h_en_whyBadge'),
                "whyTitle"     => req('h_en_whyTitle'),
                "whySubtitle"  => req('h_en_whySubtitle'),
                "vacBadge"     => req('h_en_vacBadge'),
                "vacTitle"     => req('h_en_vacTitle'),
                "vacSubtitle"  => req('h_en_vacSubtitle'),
                "formTitle"    => req('h_en_formTitle'),
                "formDesc"     => req('h_en_formDesc'),
                "directTitle"  => req('h_en_directTitle'),
                "directNote"   => req('h_en_directNote')
            ];
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ عناوين صفحة التوظيف بنجاح!" : "خطأ في الحفظ";
        }

        // ---------- CAREERS: Why Join Cards ----------
        if ($_POST['action'] === 'save_careers_why') {
            $cards = is_array($content['careers']['whyJoin'] ?? null) ? $content['careers']['whyJoin'] : [];
            $wid = req('why_id');
            $card = [
                "icon" => req('icon', 'fa-award'),
                "ar"   => ["title" => req('ar_title'), "desc" => req('ar_desc')],
                "en"   => ["title" => req('en_title'), "desc" => req('en_desc')]
            ];
            $found = false;
            foreach ($cards as $k => $c) {
                if ($wid !== '' && (string)$k === (string)$wid) {
                    $cards[$k] = $card;
                    $found = true;
                }
            }
            if (!$found) $cards[] = $card;
            $content['careers']['whyJoin'] = array_values($cards);
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ بطاقة لماذا تنضم بنجاح!" : "خطأ في الحفظ";
        }

        if ($_POST['action'] === 'delete_careers_why') {
            $del = intval(req('delete_id'));
            $cards = [];
            $cur = is_array($content['careers']['whyJoin'] ?? null) ? $content['careers']['whyJoin'] : [];
            foreach ($cur as $k => $c) { if ($k !== $del) $cards[] = $c; }
            $content['careers']['whyJoin'] = array_values($cards);
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حذف البطاقة بنجاح!" : "خطأ في الحذف";
        }

        // ---------- CAREERS: Vacancies ----------
        if ($_POST['action'] === 'save_careers_vacancy') {
            $vac = is_array($content['careers']['vacancies'] ?? null) ? $content['careers']['vacancies'] : [];
            $vid = req('vac_id');
            $item = [
                "id"       => req('vac_slug') ?: ('job-' . substr(md5(uniqid('', true)), 0, 6)),
                "icon"     => req('icon', 'fa-briefcase'),
                "location" => req('location'),
                "type"     => req('type'),
                "salary"   => req('salary'),
                "ar"       => [
                    "title"   => req('ar_title'),
                    "tagline" => req('ar_tagline'),
                    "reqs"    => text_to_lines(req('ar_reqs'))
                ],
                "en" => [
                    "title"   => req('en_title'),
                    "tagline" => req('en_tagline'),
                    "reqs"    => text_to_lines(req('en_reqs'))
                ]
            ];
            $found = false;
            foreach ($vac as $k => $v) {
                if ($vid !== '' && (string)$k === (string)$vid) {
                    $vac[$k] = $item;
                    $found = true;
                }
            }
            if (!$found) $vac[] = $item;
            $content['careers']['vacancies'] = array_values($vac);
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ الوظيفة متاحةة بنجاح!" : "خطأ في الحفظ";
        }

        if ($_POST['action'] === 'delete_careers_vacancy') {
            $del = intval(req('delete_id'));
            $out = [];
            $cur = is_array($content['careers']['vacancies'] ?? null) ? $content['careers']['vacancies'] : [];
            foreach ($cur as $k => $v) { if ($k !== $del) $out[] = $v; }
            $content['careers']['vacancies'] = array_values($out);
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حذف الوظيفة بنجاح!" : "خطأ في الحذف";
        }

        // ---------- CAREERS: Form Lists (jobRoles, experiences, governorates) ----------
        if ($_POST['action'] === 'save_careers_lists') {
            if (!isset($content['careers']['lists']) || !is_array($content['careers']['lists'])) {
                $content['careers']['lists'] = ['jobRoles' => [], 'experiences' => [], 'governorates' => []];
            }
            $content['careers']['lists']['jobRoles']     = text_to_lines(req('list_jobs'));
            $content['careers']['lists']['experiences']  = text_to_lines(req('list_exp'));
            $content['careers']['lists']['governorates'] = text_to_lines(req('list_gov'));
            $ok = json_save($CONTENT_JSON, $content);
            $msg = $ok ? "تم حفظ قوائم النموذج بنجاح!" : "خطأ في الحفظ";
        }

        if ($ok) { $message = $msg; $messageType = "success"; }
        elseif ($msg !== "") { $message = $msg; $messageType = "error"; }
    }
}

$isLoggedIn = !empty($_SESSION['delton_admin_logged']);
$tab = isset($_GET['tab']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['tab']) : 'slider';
$allowedTabs = ['slider', 'services', 'whyus', 'clients', 'stats', 'about', 'careers', 'settings', 'applications', 'inquiries'];
if (!in_array($tab, $allowedTabs)) $tab = 'slider';

$slides = json_load($SLIDER_JSON) ?: [];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة تحكم ديلتون | Delton Admin Panel</title>
  <link rel="stylesheet" href="../assets/css/tailwind.build.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      function normalizeSize(value, fallback) {
        if (!value || value === '') return fallback + 'px';
        const v = String(value).trim();
        return /^\d+(\.\d+)?$/.test(v) ? v + 'px' : v;
      }

      function toAdminPreviewPath(value) {
        if (!value) return '';
        const text = String(value).trim();
        if (text === '') return '';
        if (/^https?:\/\//i.test(text) || text.startsWith('data:') || text.startsWith('blob:')) return text;
        if (text.startsWith('../') || text.startsWith('/') || text.startsWith('./')) return text;
        return '../' + text.replace(/^\//, '');
      }

      function updatePreview(id, urlInputId, widthInputId, heightInputId) {
        const img = document.getElementById(id);
        const urlInput = document.getElementById(urlInputId);
        const widthInput = document.getElementById(widthInputId);
        const heightInput = document.getElementById(heightInputId);
        if (!img || !urlInput || !widthInput || !heightInput) return;

        const src = toAdminPreviewPath(urlInput.value.trim()) || '../newlogo.jpeg';
        const width = normalizeSize(widthInput.value, 110);
        const height = normalizeSize(heightInput.value, 52);

        img.src = src;
        img.style.width = width;
        img.style.height = height;
      }

      const bind = (previewId, urlId, widthId, heightId, fallback) => {
        const urlInput = document.getElementById(urlId);
        const widthInput = document.getElementById(widthId);
        const heightInput = document.getElementById(heightId);
        if (!urlInput || !widthInput || !heightInput) return;

        [urlInput, widthInput, heightInput].forEach(el => {
          el.addEventListener('input', () => updatePreview(previewId, urlId, widthId, heightId));
        });

        if (fallback) {
          urlInput.value = urlInput.value || fallback;
        }
        updatePreview(previewId, urlId, widthId, heightId);
      };

      bind('navbarLogoPreview', 'navbarLogoUrl', 'navbarLogoWidth', 'navbarLogoHeight', 'newlogo.jpeg');
      bind('footerLogoPreview', 'footerLogoUrl', 'footerLogoWidth', 'footerLogoHeight', 'assets/images/logoFooter.png');
    });
  </script>
  <style>
    body { font-family: 'Cairo', sans-serif; background-color: #0B132B; color: #F8FAFC; }
    .gold-gradient { background: linear-gradient(135deg, #DFB739 0%, #C9A227 60%, #A88118 100%); }
    .tab-active { background: #C9A227; color: #0B132B; }
    label.lbl { display: block; font-size: 11px; color: #94A3B8; margin-bottom: 4px; }
    input.inp, textarea.inp, select.inp {
      width: 100%; padding: 8px 12px; border-radius: 10px; background: #0F1B36;
      border: 1px solid #23395F; color: #fff; font-size: 12px; outline: none;
    }
    input.inp:focus, textarea.inp:focus { border-color: #C9A227; }
    .field { margin-bottom: 10px; }
    .admin-modal { inset: 0; z-index: 50; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); }
    .admin-modal-panel {
      width: min(95vw, 900px);
      max-height: 90vh;
      overflow-y: auto;
      border-radius: 1rem;
      background: #0D1733;
      border: 1px solid #334155;
      box-shadow: 0 30px 70px rgba(0,0,0,0.45);
    }
    @media (max-width: 640px) {
      .admin-modal-panel {
        width: min(95vw, 100%);
        max-height: 85vh;
      }
    }
  </style>
</head>
<body class="min-h-screen py-8 px-4">

<?php if (!$isLoggedIn): ?>

  <!-- ==================== LOGIN ==================== -->
  <div class="max-w-md mx-auto my-12 p-8 rounded-2xl bg-[#111C38] border border-slate-700 shadow-2xl">
    <div class="text-center mb-6">
      <div class="w-16 h-16 rounded-2xl bg-yellow-500/15 border border-yellow-500/30 flex items-center justify-center text-[#C9A227] text-3xl mx-auto mb-4">
        <i class="fa-solid fa-lock"></i>
      </div>
      <h2 class="text-xl font-bold text-white mb-1">تسجيل دخول المدير</h2>
      <p class="text-xs text-slate-400">لإدارة كامل محتوى الموقع</p>
    </div>

    <?php if ($message): ?>
      <div class="mb-4 p-3 rounded-xl text-xs font-semibold bg-red-500/20 border border-red-500/40 text-red-200"><?php echo e($message); ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input type="hidden" name="action" value="login">
      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-2">كلمة المرور (Admin PIN):</label>
        <input type="password" name="pin" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 focus:border-[#C9A227] text-white outline-none">
      </div>
      <button type="submit" class="w-full py-3 rounded-xl gold-gradient text-[#0B132B] font-bold shadow-lg transition transform hover:-translate-y-0.5">دخول لوحة التحكم</button>
    </form>
  </div>

<?php else: ?>

  <!-- ==================== HEADER ==================== -->
  <header class="max-w-6xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-700/80 pb-5">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-yellow-500/10 border border-yellow-500/40 flex items-center justify-center text-[#C9A227] text-2xl">
        <i class="fa-solid fa-sliders"></i>
      </div>
      <div>
        <h1 class="text-xl font-extrabold text-white">لوحة تحكم ديلتون</h1>
        <p class="text-xs text-[#C9A227] font-semibold">Full Content Management System</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <a href="../index.html" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition">
        <i class="fa-solid fa-arrow-up-right-from-square text-[#C9A227]"></i> معاينة الموقع
      </a>
      <a href="?logout=1" class="px-4 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs font-semibold border border-red-500/40 transition">
        <i class="fa-solid fa-arrow-right-from-bracket"></i> تسجيل الخروج
      </a>
    </div>
  </header>

  <?php if ($message): ?>
  <div class="max-w-6xl mx-auto mb-6 p-4 rounded-xl border text-sm font-semibold <?php echo $messageType === 'success' ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-200' : 'bg-red-500/20 border-red-500/40 text-red-200'; ?>">
    <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> ml-1"></i>
    <?php echo e($message); ?>
  </div>
  <?php endif; ?>

  <!-- ==================== TABS ==================== -->
  <nav class="max-w-6xl mx-auto mb-8 flex flex-wrap gap-2">
    <?php
    $tabs = [
        'slider'       => ['fa-sliders', 'السلايدر'],
        'services'     => ['fa-screwdriver-wrench', 'الخدمات'],
        'whyus'        => ['fa-shield-halved', 'لماذا نحن'],
        'clients'      => ['fa-handshake', 'العملاء'],
        'stats'        => ['fa-chart-simple', 'الإحصائيات'],
        'about'        => ['fa-building-circle-check', 'عن ديلتون'],
        'careers'      => ['fa-user-tie', 'صفحة التوظيف'],
        'settings'     => ['fa-gear', 'الإعدادات والاتصال'],
        'applications' => ['fa-briefcase', 'طلبات التوظيف'],
        'inquiries'    => ['fa-envelope-open-text', 'رسائل العملاء'],
    ];
    foreach ($tabs as $key => $info):
    ?>
    <a href="?tab=<?php echo $key; ?>" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition border border-slate-700 <?php echo $tab === $key ? 'tab-active border-transparent' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'; ?>">
      <i class="fa-solid <?php echo $info[0]; ?>"></i> <?php echo $info[1]; ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <main class="max-w-6xl mx-auto space-y-8">

    <!-- ================================================================
         TAB: SLIDER
    ================================================================= -->
    <?php if ($tab === 'slider'): ?>
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-layer-group text-[#C9A227]"></i> شرائح السلايدر (<?php echo count($slides); ?>)
        </h2>
        <button onclick="openSlideModal()" class="px-5 py-2.5 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg">+ إضافة شريحة</button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($slides as $idx => $s): ?>
        <div class="p-5 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition shadow-xl">
          <div class="w-full h-36 rounded-xl overflow-hidden mb-4 border border-slate-800 relative bg-slate-900">
            <img src="<?php echo e(thumb_src($s['image'] ?? '')); ?>" alt="Slide" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1600&q=80'">
            <div class="absolute top-2 right-2 bg-slate-900/80 px-2.5 py-1 rounded-lg text-xs font-bold text-[#C9A227]">شريحة رقم <?php echo $idx + 1; ?></div>
          </div>
          <h3 class="text-sm font-bold text-white mb-1"><?php echo e($s['ar']['title'] ?? ''); ?></h3>
          <p class="text-[11px] text-slate-500 italic mb-3">EN: <?php echo e($s['en']['title'] ?? ''); ?></p>
          <div class="flex items-center justify-between border-t border-slate-700/60 pt-3">
            <button onclick='editSlide(<?php echo json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>)'
                    class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700">
              <i class="fa-solid fa-pen-to-square text-[#C9A227]"></i> تعديل
            </button>
            <form method="POST" onsubmit="return confirm('حذف هذه الشريحة؟');">
              <input type="hidden" name="action" value="delete_slide">
              <input type="hidden" name="delete_id" value="<?php echo e($s['id'] ?? ''); ?>">
              <button class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs font-semibold border border-red-500/30"><i class="fa-solid fa-trash"></i> حذف</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <!-- ================================================================
         TAB: SERVICES
    ================================================================= -->
    <?php elseif ($tab === 'services'):
          $items = $content['services']['items'];
          $hdr = $content['services']['header'];
    ?>
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-screwdriver-wrench text-[#C9A227]"></i> الخدمات (<?php echo count($items); ?>)</h2>
        <div class="flex gap-2">
          <button onclick='openHeaderModal("services")' class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700"><i class="fa-solid fa-pen-nib text-[#C9A227]"></i> تحرير عنوان القسم</button>
          <button onclick="openAddService()" class="px-5 py-2.5 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg">+ إضافة خدمة</button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($items as $it): ?>
        <div class="p-5 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center text-[#C9A227]">
              <i class="fa-solid <?php echo e($it['icon'] ?? 'fa-gear'); ?>"></i>
            </div>
            <div class="min-w-0">
              <h3 class="text-sm font-bold text-white truncate"><?php echo e($it['ar']['title'] ?? ''); ?></h3>
              <span class="text-[10px] text-slate-500"><?php echo e($it['id'] ?? ''); ?></span>
            </div>
          </div>
          <div class="w-full h-28 rounded-xl overflow-hidden mb-3 border border-slate-800 bg-slate-900">
            <img src="<?php echo e(thumb_src($it['image'] ?? '')); ?>" alt="srvc" class="w-full h-full object-cover" onerror="this.style.display='none'">
          </div>
          <p class="text-[11px] text-slate-400 mb-3 line-clamp-2"><?php echo e($it['ar']['shortDesc'] ?? ''); ?></p>
          <div class="flex items-center justify-between border-t border-slate-700/60 pt-3">
            <button onclick='editService(<?php echo json_encode($it, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>)'
                    class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700"><i class="fa-solid fa-pen-to-square text-[#C9A227]"></i> تعديل</button>
            <form method="POST" onsubmit="return confirm('حذف هذه الخدمة؟');">
              <input type="hidden" name="action" value="delete_service">
              <input type="hidden" name="delete_id" value="<?php echo e($it['id'] ?? ''); ?>">
              <button class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs font-semibold border border-red-500/30"><i class="fa-solid fa-trash"></i> حذف</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <!-- ================================================================
         TAB: WHY US
    ================================================================= -->
    <?php elseif ($tab === 'whyus'):
          $cards = $content['whyUs']['cards'];
          $hdr = $content['whyUs']['header'];
    ?>
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-shield-halved text-[#C9A227]"></i> بطاقات "لماذا نحن" (<?php echo count($cards); ?>)</h2>
        <div class="flex gap-2">
          <button onclick='openHeaderModal("why")' class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700"><i class="fa-solid fa-pen-nib text-[#C9A227]"></i> تحرير عنوان القسم</button>
          <button onclick="openAddWhy()" class="px-5 py-2.5 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg">+ إضافة بطاقة</button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($cards as $k => $c): ?>
        <div class="p-5 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center text-[#C9A227]">
              <i class="fa-solid <?php echo e($c['icon'] ?? 'fa-award'); ?>"></i>
            </div>
            <h3 class="text-sm font-bold text-white"><?php echo e($c['ar']['title'] ?? ''); ?></h3>
          </div>
          <p class="text-[11px] text-slate-400 line-clamp-2 mb-3"><?php echo e($c['ar']['desc'] ?? ''); ?></p>
          <div class="flex items-center justify-between border-t border-slate-700/60 pt-3">
            <button onclick='editWhy(<?php echo $k; ?>)'
                    class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700"><i class="fa-solid fa-pen-to-square text-[#C9A227]"></i> تعديل</button>
            <form method="POST" onsubmit="return confirm('حذف هذه البطاقة؟');">
              <input type="hidden" name="action" value="delete_why">
              <input type="hidden" name="delete_id" value="<?php echo $k; ?>">
              <button class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs font-semibold border border-red-500/30"><i class="fa-solid fa-trash"></i> حذف</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <!-- ================================================================
         TAB: CLIENTS
    ================================================================= -->
    <?php elseif ($tab === 'clients'):
          $clients = $content['clients'];
          $chdr = $content['clientsHeader'];
    ?>
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-handshake text-[#C9A227]"></i> العملاء والشركاء (<?php echo count($clients); ?>)</h2>
        <div class="flex gap-2">
          <button onclick='openHeaderModal("clients")' class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700"><i class="fa-solid fa-pen-nib text-[#C9A227]"></i> تحرير عنوان القسم</button>
          <button onclick="openAddClient()" class="px-5 py-2.5 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg">+ إضافة عميل</button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($clients as $cl): ?>
        <div class="p-4 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition shadow-xl">
          <div class="w-full h-20 mb-3 p-2 bg-white rounded-xl flex items-center justify-center overflow-hidden">
            <img src="<?php echo e(thumb_src($cl['image'] ?? '')); ?>" alt="logo" class="max-h-full max-w-full object-contain" onerror="this.style.display='none'">
          </div>
          <h3 class="text-sm font-bold text-white mb-0.5"><?php echo e($cl['ar']['name'] ?? ''); ?></h3>
          <p class="text-[10px] text-slate-500 mb-2"><?php echo e($cl['sector'] ?? 'corporate'); ?> · <?php echo e($cl['en']['name'] ?? ''); ?></p>
          <div class="flex items-center justify-between border-t border-slate-700/60 pt-3">
            <button onclick='editClient(<?php echo json_encode($cl, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>)'
                    class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700"><i class="fa-solid fa-pen-to-square text-[#C9A227]"></i> تعديل</button>
            <form method="POST" onsubmit="return confirm('حذف هذا العميل؟');">
              <input type="hidden" name="action" value="delete_client">
              <input type="hidden" name="delete_id" value="<?php echo e($cl['id'] ?? ''); ?>">
              <button class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs font-semibold border border-red-500/30"><i class="fa-solid fa-trash"></i> حذف</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <!-- ================================================================
         TAB: STATS
    ================================================================= -->
    <?php elseif ($tab === 'stats'):
          $stats = $content['stats'];
          for ($i = 1; $i <= 4; $i++) $stats[] = isset($stats[$i-1]) ? $stats[$i-1] : ['ar'=>[],'en'=>[]];
    ?>
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white"><i class="fa-solid fa-chart-simple text-[#C9A227]"></i> إحصائيات أعلى الموقع (4 عناصر)</h2>
      </div>
      <form method="POST" class="space-y-6 p-6 rounded-2xl bg-[#111C38] border border-slate-700 shadow-xl">
        <input type="hidden" name="action" value="save_stats">
        <?php foreach ($stats as $i => $st): $n = $i + 1; ?>
        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
          <h4 class="text-xs font-bold text-yellow-400 mb-3">الإحصائية رقم <?php echo $n; ?></h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="field">
              <label class="lbl">الرقم (عربي)</label>
              <input class="inp" name="s<?php echo $n; ?>_ar_num" value="<?php echo e($st['ar']['num'] ?? ''); ?>">
              <label class="lbl mt-2">الاسم (عربي)</label>
              <input class="inp" name="s<?php echo $n; ?>_ar_label" value="<?php echo e($st['ar']['label'] ?? ''); ?>">
              <label class="lbl mt-2">الوصف (عربي)</label>
              <input class="inp" name="s<?php echo $n; ?>_ar_desc" value="<?php echo e($st['ar']['desc'] ?? ''); ?>">
            </div>
            <div class="field" dir="ltr">
              <label class="lbl">Number (EN)</label>
              <input class="inp" name="s<?php echo $n; ?>_en_num" value="<?php echo e($st['en']['num'] ?? ''); ?>">
              <label class="lbl mt-2">Label (EN)</label>
              <input class="inp" name="s<?php echo $n; ?>_en_label" value="<?php echo e($st['en']['label'] ?? ''); ?>">
              <label class="lbl mt-2">Description (EN)</label>
              <input class="inp" name="s<?php echo $n; ?>_en_desc" value="<?php echo e($st['en']['desc'] ?? ''); ?>">
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <button class="px-6 py-3 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg">حفظ الإحصائيات</button>
      </form>

    <!-- ================================================================
         TAB: ABOUT
    ================================================================= -->
    <?php elseif ($tab === 'about'):
          $about = $content['about'];
    ?>
      <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-white"><i class="fa-solid fa-building-circle-check text-[#C9A227]"></i> قسم "عن ديلتون"</h2></div>
      <form method="POST" class="space-y-6 p-6 rounded-2xl bg-[#111C38] border border-slate-700 shadow-xl">
        <input type="hidden" name="action" value="save_about">
        <?php
        $abic = $about['ar']['companyInfo'] ?? [];
        $ebic = $about['en']['companyInfo'] ?? [];
        $apil = $about['ar']['pillars'] ?? ['','','',''];
        $epil = $about['en']['pillars'] ?? ['','','',''];
        ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <!-- AR -->
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">المحتوى بالعربية</h4>
            <?php
            foreach ([
              'badge'=>'الوسم','title'=>'العنوان الرئيسي','lead'=>'المقدمة (الجملة البارزة)',
              'p1'=>'الفقرة الأولى','p2'=>'الفقرة الثانية','pillarsTitle'=>'عنوان الركائز'
            ] as $fld=>$label): ?>
            <div class="field">
              <label class="lbl"><?php echo $label; ?></label>
              <textarea class="inp" name="about_ar_<?php echo $fld; ?>" rows="<?php echo in_array($fld,['lead','p1','p2']) ? 3 : 2; ?>"><?php echo e($about['ar'][$fld] ?? ''); ?></textarea>
            </div>
            <?php endforeach; ?>
            <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="field"><label class="lbl">الركيزة <?php echo $i; ?></label><input class="inp" name="about_ar_pillar<?php echo $i; ?>" value="<?php echo e($apil[$i-1] ?? ''); ?>"></div>
            <?php endfor; ?>
            <h5 class="text-[11px] font-bold text-slate-300 mt-4 mb-2">بيانات الشركة</h5>
            <?php
            foreach ([
              'legalLabel'=>'تسمية الشكل القانوني','legalValue'=>'قيمة الشكل القانوني','foundedLabel'=>'تسمية التأسيس',
              'foundedValue'=>'سنة التأسيس','headquartersLabel'=>'تسمية المقر','headquartersValue'=>'المقر',
              'scopeLabel'=>'تسمية النطاق','scopeValue'=>'النطاق','quote'=>'الاقتباس'
            ] as $fld=>$label): ?>
            <div class="field"><label class="lbl"><?php echo $label; ?></label><input class="inp" name="about_ar_<?php echo $fld; ?>" value="<?php echo e($abic[$fld] ?? ''); ?>"></div>
            <?php endforeach; ?>
            <div class="field"><label class="lbl">نص زر تحميل عربي</label><input class="inp" name="about_ar_downloadAr" value="<?php echo e($about['ar']['downloadAr'] ?? ''); ?>"></div>
            <div class="field"><label class="lbl">نص زر تحميل إنجليزي</label><input class="inp" name="about_ar_downloadEn" value="<?php echo e($about['ar']['downloadEn'] ?? ''); ?>"></div>
          </div>
          <!-- EN -->
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800" dir="ltr">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">English Content</h4>
            <?php
            foreach ([
              'badge'=>'Badge','title'=>'Main Title','lead'=>'Lead sentence','p1'=>'Paragraph 1','p2'=>'Paragraph 2','pillarsTitle'=>'Pillars Title'
            ] as $fld=>$label): ?>
            <div class="field">
              <label class="lbl"><?php echo $label; ?></label>
              <textarea class="inp" name="about_en_<?php echo $fld; ?>" rows="<?php echo in_array($fld,['lead','p1','p2']) ? 3 : 2; ?>"><?php echo e($about['en'][$fld] ?? ''); ?></textarea>
            </div>
            <?php endforeach; ?>
            <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="field"><label class="lbl">Pillar <?php echo $i; ?></label><input class="inp" name="about_en_pillar<?php echo $i; ?>" value="<?php echo e($epil[$i-1] ?? ''); ?>"></div>
            <?php endfor; ?>
            <h5 class="text-[11px] font-bold text-slate-300 mt-4 mb-2">Company Info</h5>
            <?php
            foreach ([
              'legalLabel'=>'Legal Label','legalValue'=>'Legal Value','foundedLabel'=>'Founded Label','foundedValue'=>'Founded Year',
              'headquartersLabel'=>'HQ Label','headquartersValue'=>'HQ Value','scopeLabel'=>'Scope Label','scopeValue'=>'Scope Value','quote'=>'Quote'
            ] as $fld=>$label): ?>
            <div class="field"><label class="lbl"><?php echo $label; ?></label><input class="inp" name="about_en_<?php echo $fld; ?>" value="<?php echo e($ebic[$fld] ?? ''); ?>"></div>
            <?php endforeach; ?>
            <div class="field"><label class="lbl">Arabic Download Label</label><input class="inp" name="about_en_downloadAr" value="<?php echo e($about['en']['downloadAr'] ?? ''); ?>"></div>
            <div class="field"><label class="lbl">English Download Label</label><input class="inp" name="about_en_downloadEn" value="<?php echo e($about['en']['downloadEn'] ?? ''); ?>"></div>
          </div>
        </div>
        <button class="px-6 py-3 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg">حفظ قسم "عن ديلتون"</button>
      </form>

    <!-- ================================================================
         TAB: CAREERS PAGE (Full Control)
    ================================================================= -->
    <?php elseif ($tab === 'careers'):
          $hdr = $content['careers']['header'] ?? ['ar'=>[], 'en'=>[]];
          $whyCards = is_array($content['careers']['whyJoin'] ?? null) ? $content['careers']['whyJoin'] : [];
          $vacs = is_array($content['careers']['vacancies'] ?? null) ? $content['careers']['vacancies'] : [];
          $lists = $content['careers']['lists'] ?? ['jobRoles'=>[], 'experiences'=>[], 'governorates'=>[]];
    ?>
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-user-tie text-[#C9A227]"></i> التحكم الكامل في صفحة التوظيف (careers.html)</h2>
        <a href="careers.html" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700">
          <i class="fa-solid fa-up-right-from-square text-[#C9A227]"></i> فتح الصفحة في تبويب جديد
        </a>
      </div>

      <!-- ========== الجزء الأول: العناوين الرئيسية + رأس الصفحة + رأس النموذج ========== -->
      <form method="POST" class="space-y-6 p-6 rounded-2xl bg-[#111C38] border border-slate-700 shadow-xl">
        <input type="hidden" name="action" value="save_careers_header">
        <h3 class="text-sm font-bold text-[#C9A227] border-b border-slate-700 pb-2 mb-2 flex items-center gap-2"><i class="fa-solid fa-heading"></i> 1. عناوين الصفحة (Hero + النموذج + جهة الاتصال)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">المحتوى بالعربية</h4>
            <?php
            $arFields = [
              'badge'       => 'وسم / البادج أعلى Hero (مثال: فرص العمل)',
              'title'       => 'عنوان Hero الرئيسي',
              'subtitle'    => 'عنوان Hero الفرعي (الوصف القصير)',
              'lead'        => 'جملة Call-to-Action تحت Hero (مثال: قدّم سيرتك خلال 5 أيام...)',
              'whyBadge'    => 'وسم / البادج أعلى قسم "لماذا تنضم إلينا"',
              'whyTitle'    => 'عنوان قسم "لماذا تنضم إلينا"',
              'whySubtitle' => 'الوصف تحت عنوان "لماذا تنضم إلينا"',
              'vacBadge'    => 'وسم / البادج أعلى قسم "الوظائف متاحةة"',
              'vacTitle'    => 'عنوان قسم "الوظائف متاحةة"',
              'vacSubtitle' => 'الوصف تحت عنوان "الوظائف متاحةة"',
              'formTitle'   => 'عنوان نموذج التقديم على وظيفة',
              'formDesc'    => 'وصف نموذج التقديم على وظيفة',
              'directTitle' => 'عنوان بطاقة "أو أرسل سيرتك مباشرة"',
              'directNote'  => 'نص تحت عنوان البطاقة الجانبية'
            ];
            foreach ($arFields as $fld => $label):
              $rows = in_array($fld, ['subtitle','formDesc','directNote','lead','whySubtitle','vacSubtitle']) ? 2 : 1;
            ?>
            <div class="field">
              <label class="lbl"><?php echo $label; ?></label>
              <?php if ($rows > 1): ?>
                <textarea class="inp" name="h_ar_<?php echo $fld; ?>" rows="<?php echo $rows; ?>"><?php echo e($hdr['ar'][$fld] ?? ''); ?></textarea>
              <?php else: ?>
                <input class="inp" name="h_ar_<?php echo $fld; ?>" value="<?php echo e($hdr['ar'][$fld] ?? ''); ?>">
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800" dir="ltr">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">English Content</h4>
            <?php
            $enFields = [
              'badge'       => 'Hero Badge Label',
              'title'       => 'Hero Main Title',
              'subtitle'    => 'Hero Subtitle / Short Description',
              'lead'        => 'Hero CTA sentence (e.g. Submit within 5 days...)',
              'whyBadge'    => '"Why Join Us" Section Badge',
              'whyTitle'    => '"Why Join Us" Section Title',
              'whySubtitle' => '"Why Join Us" Section Description',
              'vacBadge'    => '"Open Vacancies" Section Badge',
              'vacTitle'    => '"Open Vacancies" Section Title',
              'vacSubtitle' => '"Open Vacancies" Section Description',
              'formTitle'   => 'Application Form Section Title',
              'formDesc'    => 'Application Form Section Description',
              'directTitle' => '"Or send CV directly" Card Title',
              'directNote'  => 'Left card body text'
            ];
            foreach ($enFields as $fld => $label):
              $rows = in_array($fld, ['subtitle','formDesc','directNote','lead','whySubtitle','vacSubtitle']) ? 2 : 1;
            ?>
            <div class="field">
              <label class="lbl"><?php echo $label; ?></label>
              <?php if ($rows > 1): ?>
                <textarea class="inp" name="h_en_<?php echo $fld; ?>" rows="<?php echo $rows; ?>"><?php echo e($hdr['en'][$fld] ?? ''); ?></textarea>
              <?php else: ?>
                <input class="inp" name="h_en_<?php echo $fld; ?>" value="<?php echo e($hdr['en'][$fld] ?? ''); ?>">
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <button class="px-6 py-3 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg"><i class="fa-solid fa-floppy-disk"></i> حفظ جميع العناوين (Hero + النموذج)</button>
      </form>

      <!-- ========== الجزء الثاني: بطاقات "لماذا تنضم إلينا" Why Join ========== -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-[#C9A227] border-b border-slate-700 pb-2 flex items-center gap-2"><i class="fa-solid fa-hand-holding-heart"></i> 2. بطاقات "لماذا تنضم إلى ديلتون / Why Join Delton" (<?php echo count($whyCards); ?>)</h3>
          <button onclick="openCareersWhyModal()" class="px-4 py-2 rounded-xl gold-gradient text-[#0B132B] font-bold text-xs shadow-lg">+ إضافة بطاقة جديدة</button>
        </div>
        <?php if (empty($whyCards)): ?>
          <div class="p-8 rounded-2xl bg-[#111C38] border border-dashed border-slate-700 text-center">
            <i class="fa-solid fa-layer-group text-3xl text-slate-600 mb-2"></i>
            <p class="text-xs text-slate-400">لا توجد بطاقات الآن. اضغط "إضافة بطاقة جديدة" لبدء إنشاء القسم.</p>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($whyCards as $idx => $c): ?>
            <div class="p-5 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition">
              <div class="w-11 h-11 rounded-xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center text-[#C9A227] text-lg mb-3">
                <i class="fa-solid <?php echo e($c['icon'] ?? 'fa-award'); ?>"></i>
              </div>
              <h4 class="text-sm font-bold text-white mb-1"><?php echo e($c['ar']['title'] ?? ''); ?></h4>
              <p class="text-[11px] text-slate-400 italic mb-2">EN: <?php echo e($c['en']['title'] ?? ''); ?></p>
              <p class="text-xs text-slate-300 mb-3" style="white-space:pre-line"><?php echo e($c['ar']['desc'] ?? ''); ?></p>
              <div class="flex items-center justify-between border-t border-slate-700/60 pt-3">
                <button onclick='editCareersWhy(<?php echo (int)$idx; ?>)'
                        class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold border border-slate-700"><i class="fa-solid fa-pen-to-square text-[#C9A227]"></i> تعديل</button>
                <form method="POST" onsubmit="return confirm('حذف هذه البطاقة؟');">
                  <input type="hidden" name="action" value="delete_careers_why">
                  <input type="hidden" name="delete_id" value="<?php echo $idx; ?>">
                  <button class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-[11px] font-semibold border border-red-500/30"><i class="fa-solid fa-trash"></i> حذف</button>
                </form>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ========== الجزء الثالث: الوظائف متاحةة (Open Vacancies) ========== -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-[#C9A227] border-b border-slate-700 pb-2 flex items-center gap-2"><i class="fa-solid fa-briefcase"></i> 3. الوظائف متاحةة المعروضة على الصفحة / Open Vacancies (<?php echo count($vacs); ?>)</h3>
          <button onclick="openCareersVacancyModal()" class="px-4 py-2 rounded-xl gold-gradient text-[#0B132B] font-bold text-xs shadow-lg">+ إضافة وظيفة شاغرة</button>
        </div>
        <?php if (empty($vacs)): ?>
          <div class="p-8 rounded-2xl bg-[#111C38] border border-dashed border-slate-700 text-center">
            <i class="fa-solid fa-clipboard-list text-3xl text-slate-600 mb-2"></i>
            <p class="text-xs text-slate-400">لا توجد وظائف شاغرة الآن. الوظائف التي تضيفها هنا ستظهر مباشرة في صفحة careers.html.</p>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($vacs as $idx => $v): ?>
            <div class="p-5 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg shrink-0">
                    <i class="fa-solid <?php echo e($v['icon'] ?? 'fa-briefcase'); ?>"></i>
                  </div>
                  <div class="min-w-0">
                    <h4 class="text-sm font-bold text-white truncate"><?php echo e($v['ar']['title'] ?? ''); ?></h4>
                    <p class="text-[11px] text-slate-400 italic truncate">EN: <?php echo e($v['en']['title'] ?? ''); ?></p>
                  </div>
                </div>
                <div class="flex flex-wrap gap-1 shrink-0">
                  <?php if (!empty($v['type'])): ?>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-500/20 border border-blue-500/30 text-blue-200 font-bold"><?php echo e($v['type']); ?></span>
                  <?php endif; ?>
                  <?php if (!empty($v['location'])): ?>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-500/20 border border-purple-500/30 text-purple-200 font-bold"><i class="fa-solid fa-location-dot"></i> <?php echo e($v['location']); ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <?php if (!empty($v['salary'])): ?>
                <div class="mb-2 text-[11px] text-yellow-300 font-bold"><i class="fa-solid fa-money-bill-wave"></i> <?php echo e($v['salary']); ?></div>
              <?php endif; ?>
              <?php if (!empty($v['ar']['tagline']) || !empty($v['en']['tagline'])): ?>
                <p class="text-xs text-slate-300 mb-3">
                  <span class="text-slate-400">AR:</span> <?php echo e($v['ar']['tagline'] ?? ''); ?><br>
                  <span class="text-slate-400">EN:</span> <span dir="ltr"><?php echo e($v['en']['tagline'] ?? ''); ?></span>
                </p>
              <?php endif; ?>
              <?php if (!empty($v['ar']['reqs']) || !empty($v['en']['reqs'])): ?>
                <div class="mb-3 text-xs">
                  <?php if (!empty($v['ar']['reqs']) && is_array($v['ar']['reqs'])): ?>
                    <p class="text-slate-400 font-bold mb-1">المتطلبات:</p>
                    <ul class="list-disc pr-4 space-y-0.5 text-slate-300">
                      <?php foreach (array_slice($v['ar']['reqs'],0,3) as $r): ?>
                        <li><?php echo e($r); ?></li>
                      <?php endforeach; ?>
                      <?php if (count($v['ar']['reqs']) > 3): ?><li class="text-slate-500 italic">+<?php echo count($v['ar']['reqs']) - 3; ?> متطلبات أخرى...</li><?php endif; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <div class="flex items-center justify-between border-t border-slate-700/60 pt-3">
                <a href="#application" onclick="document.getElementById('appPosition').value='<?php echo e(addslashes($v['ar']['title'] ?? '')); ?>';"
                   class="px-3 py-1.5 rounded-lg bg-[#C9A227]/20 hover:bg-[#C9A227]/30 text-[#F0CB86] text-[11px] font-bold border border-[#C9A227]/40">قدّم الآن</a>
                <div class="flex gap-2">
                  <button onclick='editCareersVacancy(<?php echo (int)$idx; ?>)' class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold border border-slate-700"><i class="fa-solid fa-pen-to-square text-[#C9A227]"></i> تعديل</button>
                  <form method="POST" onsubmit="return confirm('حذف هذه الوظيفة نهائياً؟');">
                    <input type="hidden" name="action" value="delete_careers_vacancy">
                    <input type="hidden" name="delete_id" value="<?php echo $idx; ?>">
                    <button class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-[11px] font-semibold border border-red-500/30"><i class="fa-solid fa-trash"></i> حذف</button>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ========== الجزء الرابع: قوائم النموذج ========== -->
      <form method="POST" class="space-y-6 p-6 rounded-2xl bg-[#111C38] border border-slate-700 shadow-xl">
        <input type="hidden" name="action" value="save_careers_lists">
        <h3 class="text-sm font-bold text-[#C9A227] border-b border-slate-700 pb-2 mb-2 flex items-center gap-2"><i class="fa-solid fa-list-check"></i> 4. قوائم خانات نموذج التقديم (Job Roles / الخبرة / المحافظات)</h3>
        <p class="text-[11px] text-slate-400 -mt-4">كُل سطر = عنصر واحد في القائمة. (تظهر في الـ dropdown و datalist داخل نموذج التوظيف)</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="field">
            <label class="lbl">قائمة الوظائف المقترحة (job roles)</label>
            <textarea class="inp" name="list_jobs" rows="12" placeholder="عامل نظافة&#10;فني صيانة&#10;محاسب..."><?php echo e(implode("\n", $lists['jobRoles'] ?? [])); ?></textarea>
          </div>
          <div class="field">
            <label class="lbl">خيارات سنوات الخبرة (dropdown)</label>
            <textarea class="inp" name="list_exp" rows="12" placeholder="بدون خبرة&#10;أقل من سنة&#10;1 - 3 سنوات..."><?php echo e(implode("\n", $lists['experiences'] ?? [])); ?></textarea>
          </div>
          <div class="field">
            <label class="lbl">قائمة المحافظات / المدن (datalist)</label>
            <textarea class="inp" name="list_gov" rows="12" placeholder="القاهرة&#10;الجيزة&#10;الإسكندرية..."><?php echo e(implode("\n", $lists['governorates'] ?? [])); ?></textarea>
          </div>
        </div>
        <button class="px-6 py-3 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg"><i class="fa-solid fa-list"></i> حفظ القوائم</button>
      </form>

    <!-- ================================================================
         TAB: SETTINGS (Contact + Footer)
    ================================================================= -->
    <?php elseif ($tab === 'settings'):
          $ct = $content['contact'];
          $ft = $content['footer'];
          $phones = $ct['phones'] ?? [];
    ?>
      <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-white"><i class="fa-solid fa-gear text-[#C9A227]"></i> بيانات الاتصال والفوتر</h2></div>
      <form method="POST" class="space-y-6 p-6 rounded-2xl bg-[#111C38] border border-slate-700 shadow-xl">
        <input type="hidden" name="action" value="save_settings">

        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
          <h4 class="text-xs font-bold text-yellow-400 mb-3">أرقام الهاتف والبريد (تظهر في الموقع كاملاً)</h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="field"><label class="lbl">هاتف <?php echo $i; ?></label><input class="inp" name="phone<?php echo $i; ?>" dir="ltr" value="<?php echo e($phones[$i-1] ?? ''); ?>"></div>
            <?php endfor; ?>
          </div>
          <div class="field md:w-1/3"><label class="lbl">البريد الإلكتروني</label><input class="inp" name="email" value="<?php echo e($ct['email'] ?? ''); ?>"></div>
          <div class="field md:w-1/3"><label class="lbl">أرقام الهاتف في الفوتر (نص واحد)</label><input class="inp" name="footer_phones" dir="ltr" value="<?php echo e($ft['phones'] ?? ''); ?>"></div>
        </div>

        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
          <h4 class="text-xs font-bold text-yellow-400 mb-3">شعار الموقع في navbar و footer</h4>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="space-y-3">
              <div class="field"><label class="lbl">شعار الـ Navbar</label><input class="inp" name="navbar_logo_url" id="navbarLogoUrl" value="<?php echo e($content['branding']['navbar']['image'] ?? 'newlogo.jpeg'); ?>" placeholder="newlogo.jpeg أو assets/images/brand/..." ></div>
              <input type="file" name="navbar_logo_file" accept="image/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-yellow-500/20 file:text-yellow-400 file:text-xs">
              <div class="grid grid-cols-2 gap-3">
                <div class="field"><label class="lbl">العرض</label><input class="inp" name="navbar_logo_width" id="navbarLogoWidth" value="<?php echo e($content['branding']['navbar']['width'] ?? '110'); ?>"></div>
                <div class="field"><label class="lbl">الارتفاع</label><input class="inp" name="navbar_logo_height" id="navbarLogoHeight" value="<?php echo e($content['branding']['navbar']['height'] ?? '52'); ?>"></div>
              </div>
              <div class="rounded-xl border border-slate-700 bg-[#0B132B] p-3">
                <div class="text-[10px] text-slate-400 mb-2">معاينة Navbar</div>
                <div class="flex items-center gap-3 min-h-[70px]">
                  <img id="navbarLogoPreview" src="<?php echo e(thumb_src($content['branding']['navbar']['image'] ?? 'newlogo.jpeg')); ?>" alt="Navbar logo preview" class="object-contain" style="width: <?php echo e($content['branding']['navbar']['width'] ?? '110'); ?>px; height: <?php echo e($content['branding']['navbar']['height'] ?? '52'); ?>px; max-width: 100%;">
                </div>
              </div>
            </div>
            <div class="space-y-3">
              <div class="field"><label class="lbl">شعار الـ Footer</label><input class="inp" name="footer_logo_url" id="footerLogoUrl" value="<?php echo e($content['branding']['footer']['image'] ?? 'assets/images/logoFooter.png'); ?>" placeholder="assets/images/logoFooter.png أو assets/images/brand/..." ></div>
              <input type="file" name="footer_logo_file" accept="image/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-yellow-500/20 file:text-yellow-400 file:text-xs">
              <div class="grid grid-cols-2 gap-3">
                <div class="field"><label class="lbl">العرض</label><input class="inp" name="footer_logo_width" id="footerLogoWidth" value="<?php echo e($content['branding']['footer']['width'] ?? '150'); ?>"></div>
                <div class="field"><label class="lbl">الارتفاع</label><input class="inp" name="footer_logo_height" id="footerLogoHeight" value="<?php echo e($content['branding']['footer']['height'] ?? '56'); ?>"></div>
              </div>
              <div class="rounded-xl border border-slate-700 bg-[#0B132B] p-3">
                <div class="text-[10px] text-slate-400 mb-2">معاينة Footer</div>
                <div class="flex items-center justify-center min-h-[90px] bg-slate-950/60 rounded-lg">
                  <img id="footerLogoPreview" src="<?php echo e(thumb_src($content['branding']['footer']['image'] ?? 'assets/images/logoFooter.png')); ?>" alt="Footer logo preview" class="object-contain" style="width: <?php echo e($content['branding']['footer']['width'] ?? '150'); ?>px; height: <?php echo e($content['branding']['footer']['height'] ?? '56'); ?>px; max-width: 100%;">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">قسم الاتصال - عربي</h4>
            <?php foreach ([
              'badge'=>'الوسم','title'=>'العنوان','subtitle'=>'الوصف','addressTitle'=>'عنوان الحقل','address'=>'العنوان الفعلي',
              'phoneTitle'=>'عنوان الهواتف','emailTitle'=>'عنوان البريد','hoursTitle'=>'عنوان الميعاد','hours'=>'أوقات العمل'
            ] as $fld=>$label): ?>
            <div class="field"><label class="lbl"><?php echo $label; ?></label><textarea class="inp" name="c_ar_<?php echo $fld; ?>" rows="<?php echo $fld==='subtitle' ? 3 : 1; ?>"><?php echo e($ct['ar'][$fld] ?? ''); ?></textarea></div>
            <?php endforeach; ?>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">Contact Section - EN</h4>
            <?php foreach ([
              'badge'=>'Badge','title'=>'Title','subtitle'=>'Subtitle','addressTitle'=>'Address Title','address'=>'Address',
              'phoneTitle'=>'Phone Title','emailTitle'=>'Email Title','hoursTitle'=>'Hours Title','hours'=>'Business Hours'
            ] as $fld=>$label): ?>
            <div class="field"><label class="lbl"><?php echo $label; ?></label><textarea class="inp" name="c_en_<?php echo $fld; ?>" rows="<?php echo $fld==='subtitle' ? 3 : 1; ?>"><?php echo e($ct['en'][$fld] ?? ''); ?></textarea></div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">الفوتر - عربي</h4>
            <?php foreach ([
              'about'=>'نبذة عن الشركة','quickLinks'=>'عنوان الروابط السريعة','ourServices'=>'عنوان خدماتنا الأساسية',
              'contactInfo'=>'عنوان بيانات التواصل','copyright'=>'حقوق النشر','address'=>'عنوان الشركة'
            ] as $fld=>$label): ?>
            <div class="field"><label class="lbl"><?php echo $label; ?></label><textarea class="inp" name="f_ar_<?php echo $fld; ?>" rows="<?php echo $fld==='about' ? 3 : 1; ?>"><?php echo e($ft['ar'][$fld] ?? ''); ?></textarea></div>
            <?php endforeach; ?>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">Footer - EN</h4>
            <?php foreach ([
              'about'=>'Company About','quickLinks'=>'Quick Links Title','ourServices'=>'Core Services Title',
              'contactInfo'=>'Contact Info Title','copyright'=>'Copyright','address'=>'Company Address'
            ] as $fld=>$label): ?>
            <div class="field"><label class="lbl"><?php echo $label; ?></label><textarea class="inp" name="f_en_<?php echo $fld; ?>" rows="<?php echo $fld==='about' ? 3 : 1; ?>"><?php echo e($ft['en'][$fld] ?? ''); ?></textarea></div>
            <?php endforeach; ?>
          </div>
        </div>

        <button class="px-6 py-3 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg">حفظ الإعدادات</button>
      </form>

    <!-- ================================================================
         TAB: JOB APPLICATIONS
    ================================================================= -->
    <?php elseif ($tab === 'applications'):
          $apps = json_load($APPLICATIONS_JSON) ?: [];
          $apps = array_reverse($apps);
    ?>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-briefcase text-[#C9A227]"></i> طلبات التوظيف (<?php echo count($apps); ?>)</h2>
        <a href="?tab=applications" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700">
          <i class="fa-solid fa-rotate text-[#C9A227]"></i> تحديث
        </a>
      </div>

      <?php if (empty($apps)): ?>
        <div class="p-10 rounded-2xl bg-[#111C38] border border-dashed border-slate-700 text-center">
          <i class="fa-solid fa-inbox text-4xl text-slate-600 mb-3"></i>
          <p class="text-sm text-slate-400">لا توجد طلبات توظيف حتى الآن. ستظهر الطلبات هنا فور تقديم المتقدمين عبر صفحة "الوظائف" على الموقع، وتصلك نسخة بريدياً على info@delton-eg.com.</p>
        </div>
      <?php else: ?>
        <div class="space-y-5">
          <?php foreach ($apps as $a):
            $created = !empty($a['created_at']) ? date('d/m/Y h:i A', strtotime($a['created_at'])) : '';
            $hasCv = !empty($a['cv']);
          ?>
          <div class="p-5 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition shadow-xl">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center text-[#C9A227] text-lg shrink-0">
                  <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="min-w-0">
                  <h3 class="text-sm font-bold text-white truncate"><?php echo e($a['name'] ?? ''); ?></h3>
                  <p class="text-[11px] text-[#C9A227] font-semibold"><?php echo e($a['position'] ?? ''); ?></p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <?php if ($hasCv): ?>
                <a href="../<?php echo e($a['cv']); ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-200 text-[11px] font-bold border border-emerald-500/30">
                  <i class="fa-solid fa-file-arrow-down"></i> السيرة الذاتية
                </a>
                <?php endif; ?>
                <form method="POST" onsubmit="return confirm('حذف هذا الطلب؟');">
                  <input type="hidden" name="action" value="delete_application">
                  <input type="hidden" name="delete_id" value="<?php echo e($a['id'] ?? ''); ?>">
                  <button class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-[11px] font-semibold border border-red-500/30">
                    <i class="fa-solid fa-trash"></i> حذف
                  </button>
                </form>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-[11px]">
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-phone text-[#C9A227]"></i> الهاتف / الواتساب</p>
                <p class="text-slate-200 font-semibold" dir="ltr"><?php echo e($a['phone'] ?? ''); ?></p>
              </div>
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-envelope text-[#C9A227]"></i> البريد الإلكتروني</p>
                <p class="text-slate-200 font-semibold truncate" dir="ltr"><?php echo e($a['email'] ?? ''); ?></p>
              </div>
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-map-location-dot text-[#C9A227]"></i> المحافظة</p>
                <p class="text-slate-200 font-semibold"><?php echo e($a['city'] ?? ''); ?></p>
              </div>
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-calendar-days text-[#C9A227]"></i> تاريخ التقديم</p>
                <p class="text-slate-200 font-semibold"><?php echo e($created); ?></p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 text-[11px]">
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-user-graduate text-[#C9A227]"></i> المؤهل</p>
                <p class="text-slate-200 font-semibold"><?php echo e($a['qualification'] ?? ''); ?></p>
              </div>
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-award text-[#C9A227]"></i> سنوات الخبرة</p>
                <p class="text-slate-200 font-semibold"><?php echo e($a['experience'] ?? ''); ?></p>
              </div>
            </div>

            <?php if (!empty($a['message'])): ?>
            <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 mt-3">
              <p class="text-slate-500 mb-1 text-[11px]"><i class="fa-solid fa-message text-[#C9A227]"></i> نبذة عن المتقدم</p>
              <p class="text-slate-300 leading-relaxed" style="white-space:pre-line"><?php echo e($a['message']); ?></p>
            </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <!-- ================================================================
         TAB: CONTACT INQUIRIES
    ================================================================= -->
    <?php elseif ($tab === 'inquiries'):
          $inqs = json_load($INQUIRIES_JSON) ?: [];
          $inqs = array_reverse($inqs);

          $serviceLabels = [
              'all'         => 'حلول متكاملة',
              'cleaning'    => 'خدمات النظافة والتعقيم',
              'maintenance' => 'الصيانة الفنية والمقاولات',
              'landscaping' => 'تنسيق المسطحات الخضراء',
              'supplies'    => 'التوريدات وتوظيف العمالة',
              'hospitality' => 'خدمات الضيافة والبوفيه',
              'renovation'  => 'المقاولات والتجديدات',
              'other'       => 'أخرى',
          ];
    ?>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-envelope-open-text text-[#C9A227]"></i> رسائل عملاء الاستفسارات (<?php echo count($inqs); ?>)</h2>
        <a href="?tab=inquiries" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700">
          <i class="fa-solid fa-rotate text-[#C9A227]"></i> تحديث الآن
        </a>
      </div>

      <?php if (empty($inqs)): ?>
        <div class="p-10 rounded-2xl bg-[#111C38] border border-dashed border-slate-700 text-center">
          <i class="fa-solid fa-inbox text-4xl text-slate-600 mb-3"></i>
          <p class="text-sm text-slate-400">لا توجد رسائل استفسارات حتى الآن. ستظهر الرسائل هنا فور ملء نموذج "تواصل معنا" على الصفحة الرئيسية، وتصلك نسخة بريدياً على البريد الرسمي.</p>
        </div>
      <?php else: ?>
        <div class="space-y-5">
          <?php foreach ($inqs as $i):
            $created = !empty($i['created_at']) ? date('d/m/Y h:i A', strtotime($i['created_at'])) : '';
            $svc = !empty($i['service']) ? ($serviceLabels[$i['service']] ?? $i['service']) : '';
          ?>
          <div class="p-5 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition shadow-xl">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg shrink-0">
                  <i class="fa-solid fa-building-user"></i>
                </div>
                <div class="min-w-0">
                  <h3 class="text-sm font-bold text-white truncate"><?php echo e($i['name'] ?? ''); ?></h3>
                  <p class="text-[11px] text-[#C9A227] font-semibold truncate"><?php echo e($i['company'] ?? ''); ?></p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <?php if (!empty($i['phone'])): ?>
                <a href="https://wa.me/20<?php echo e(ltrim($i['phone'], '0')); ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#25D366]/20 hover:bg-[#25D366]/30 text-white text-[11px] font-bold border border-[#25D366]/40">
                  <i class="fa-brands fa-whatsapp"></i> واتساب
                </a>
                <?php endif; ?>
                <?php if (!empty($i['email'])): ?>
                <a href="mailto:<?php echo e($i['email']); ?>" class="px-3 py-1.5 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-200 text-[11px] font-bold border border-blue-500/30">
                  <i class="fa-solid fa-reply"></i> رد بالبريد
                </a>
                <?php endif; ?>
                <form method="POST" onsubmit="return confirm('حذف هذه الرسالة نهائياً؟');">
                  <input type="hidden" name="action" value="delete_inquiry">
                  <input type="hidden" name="delete_id" value="<?php echo e($i['id'] ?? ''); ?>">
                  <button class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-[11px] font-semibold border border-red-500/30">
                    <i class="fa-solid fa-trash"></i> حذف
                  </button>
                </form>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-[11px]">
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-phone text-[#C9A227]"></i> الهاتف / الواتساب</p>
                <p class="text-slate-200 font-semibold" dir="ltr"><?php echo e($i['phone'] ?? ''); ?></p>
              </div>
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-envelope text-[#C9A227]"></i> البريد الإلكتروني</p>
                <p class="text-slate-200 font-semibold truncate" dir="ltr"><?php echo e($i['email'] ?? ''); ?></p>
              </div>
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-briefcase text-[#C9A227]"></i> الخدمة المطلوبة</p>
                <p class="text-slate-200 font-semibold"><?php echo e($svc); ?></p>
              </div>
              <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                <p class="text-slate-500 mb-1"><i class="fa-solid fa-calendar-days text-[#C9A227]"></i> تاريخ الاستفسار</p>
                <p class="text-slate-200 font-semibold"><?php echo e($created); ?></p>
              </div>
            </div>

            <?php if (!empty($i['ip'])): ?>
            <div class="mt-3 text-[10px] text-slate-500">
              <i class="fa-solid fa-location-dot text-slate-600"></i> عنوان IP المرسل: <span dir="ltr" class="font-mono"><?php echo e($i['ip']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($i['message'])): ?>
            <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 mt-3">
              <p class="text-slate-500 mb-2 text-[11px]"><i class="fa-solid fa-message text-[#C9A227]"></i> تفاصيل الرسالة / الاستفسار</p>
              <p class="text-slate-200 leading-relaxed text-sm" style="white-space:pre-line"><?php echo e($i['message']); ?></p>
            </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </main>

  <!-- ==================================================================
       MODALS
  =================================================================== -->

  <!-- Service Modal -->
  <div id="serviceModal" class="admin-modal fixed hidden inset-0 flex items-center justify-center p-4 overflow-y-auto">
    <div class="admin-modal-panel bg-[#0D1733] px-5 py-6 sm:px-6 sm:py-7">
      <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-4">
        <h3 id="serviceModalTitle" class="text-lg font-bold text-white">إضافة / تعديل خدمة</h3>
        <button onclick="closeServiceModal()" class="text-slate-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" id="serviceId">
        <input type="hidden" name="action" value="save_service">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field"><label class="lbl">المعرّف (slug) - يظهر في الرابط</label><input class="inp" id="serviceSlug" name="service_id" readonly dir="ltr"></div>
          <div class="field"><label class="lbl">أيقونة FontAwesome (مثال: fa-gear, fa-broom)</label><input class="inp" id="serviceIcon" name="icon" list="iconsList"><datalist id="iconsList">
            <option>fa-gear</option><option>fa-broom</option><option>fa-tree</option><option>fa-boxes-packing</option>
            <option>fa-mug-hot</option><option>fa-building</option><option>fa-screwdriver-wrench</option><option>fa-shield-halved</option>
          </datalist></div>
        </div>

        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
          <label class="lbl">صورة الخدمة: ارفع صورة <span class="text-slate-500">أو</span> اكتب مسار/رابط</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-yellow-500/20 file:text-yellow-400 file:text-xs">
            <input type="text" class="inp" name="image_url" id="serviceImage" placeholder="assets/images/services/...">
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
            <div class="field"><label class="lbl">صورة إضافية في المعرض 1</label><input class="inp" name="gallery_1" id="serviceGallery1"></div>
            <div class="field"><label class="lbl">صورة إضافية في المعرض 2</label><input class="inp" name="gallery_2" id="serviceGallery2"></div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">عربي</h4>
            <div class="field"><label class="lbl">العنوان *</label><input class="inp" name="ar_title" id="s-ar-title" required></div>
            <div class="field"><label class="lbl">الوصف المختصر *</label><input class="inp" name="ar_shortDesc" id="s-ar-shortDesc" required></div>
            <div class="field"><label class="lbl">عنوان النافذة المنبثقة</label><input class="inp" name="ar_modalTitle" id="s-ar-modalTitle"></div>
            <div class="field"><label class="lbl">نطاق الخدمة (overview)</label><textarea class="inp" name="ar_overview" id="s-ar-overview" rows="3"></textarea></div>
            <div class="field"><label class="lbl">المهام (كل سطر = مهمة)</label><textarea class="inp" name="ar_tasks" id="s-ar-tasks" rows="5"></textarea></div>
            <div class="field"><label class="lbl">المعدات والخامات</label><input class="inp" name="ar_equipment" id="s-ar-equipment"></div>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800" dir="ltr">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">English</h4>
            <div class="field"><label class="lbl">Title *</label><input class="inp" name="en_title" id="s-en-title" required></div>
            <div class="field"><label class="lbl">Short Description *</label><input class="inp" name="en_shortDesc" id="s-en-shortDesc" required></div>
            <div class="field"><label class="lbl">Modal Title</label><input class="inp" name="en_modalTitle" id="s-en-modalTitle"></div>
            <div class="field"><label class="lbl">Overview</label><textarea class="inp" name="en_overview" id="s-en-overview" rows="3"></textarea></div>
            <div class="field"><label class="lbl">Tasks (one per line)</label><textarea class="inp" name="en_tasks" id="s-en-tasks" rows="5"></textarea></div>
            <div class="field"><label class="lbl">Equipment</label><input class="inp" name="en_equipment" id="s-en-equipment"></div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
          <button type="button" onclick="closeServiceModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">إلغاء</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg">حفظ الخدمة</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Why Us Modal -->
  <div id="whyModal" class="admin-modal fixed hidden inset-0 flex items-center justify-center p-4 overflow-y-auto">
    <div class="admin-modal-panel bg-[#0D1733] px-5 py-6 sm:px-6 sm:py-7 max-w-2xl">
      <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-4">
        <h3 id="whyModalTitle" class="text-lg font-bold text-white">إضافة / تعديل بطاقة</h3>
        <button onclick="closeWhyModal()" class="text-slate-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="save_why">
        <input type="hidden" name="why_id" id="whyId">
        <div class="field"><label class="lbl">أيقونة FontAwesome</label><input class="inp" id="whyIcon" name="icon" list="iconsList"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">عربي</h4>
            <div class="field"><label class="lbl">العنوان</label><input class="inp" name="ar_title" id="w-ar-title" required></div>
            <div class="field"><label class="lbl">الوصف</label><textarea class="inp" name="ar_desc" id="w-ar-desc" rows="3"></textarea></div>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800" dir="ltr">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">English</h4>
            <div class="field"><label class="lbl">Title</label><input class="inp" name="en_title" id="w-en-title" required></div>
            <div class="field"><label class="lbl">Description</label><textarea class="inp" name="en_desc" id="w-en-desc" rows="3"></textarea></div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
          <button type="button" onclick="closeWhyModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">إلغاء</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg">حفظ البطاقة</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Client Modal -->
  <div id="clientModal" class="admin-modal fixed hidden inset-0 flex items-center justify-center p-4 overflow-y-auto">
    <div class="admin-modal-panel bg-[#0D1733] px-5 py-6 sm:px-6 sm:py-7 max-w-2xl">
      <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-4">
        <h3 class="text-lg font-bold text-white">إضافة / تعديل عميل</h3>
        <button onclick="closeClientModal()" class="text-slate-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="action" value="save_client">
        <input type="hidden" name="client_id" id="clientId">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field"><label class="lbl">اسم العميل (عربي) *</label><input class="inp" name="ar_name" id="c-ar-name" required></div>
          <div class="field" dir="ltr"><label class="lbl">Client Name (EN) *</label><input class="inp" name="en_name" id="c-en-name" required></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field">
            <label class="lbl">القطاع</label>
            <select class="inp" name="sector" id="c-sector">
              <option value="banking">القطاع المصرفي والمالي</option>
              <option value="corporate">المؤسسات الكبرى والصناعية</option>
            </select>
          </div>
          <div class="field"><label class="lbl">شعار العميل: رفع أو رابط/مسار</label>
            <div class="flex gap-2">
              <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-yellow-500/20 file:text-yellow-400 file:text-xs">
            </div>
            <input type="text" class="inp mt-2" name="image_url" id="c-image" placeholder="assets/images/clientLogo/...">
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
          <button type="button" onclick="closeClientModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">إلغاء</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg">حفظ العميل</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Section Header Modal (services / whyus / clients) -->
  <div id="headerModal" class="admin-modal fixed hidden inset-0 flex items-center justify-center p-4 overflow-y-auto">
    <div class="admin-modal-panel bg-[#0D1733] px-5 py-6 sm:px-6 sm:py-7 max-w-3xl">
      <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-4">
        <h3 class="text-lg font-bold text-white">تحرير عنوان القسم</h3>
        <button onclick="closeHeaderModal()" class="text-slate-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" id="headerAction">
        <input type="hidden" name="h_section" id="headerSection">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">عربي</h4>
            <div class="field"><label class="lbl">الوسم (Badge)</label><input class="inp" name="h_ar_badge" id="h-ar-badge"></div>
            <div class="field"><label class="lbl">العنوان</label><input class="inp" name="h_ar_title" id="h-ar-title"></div>
            <div class="field"><label class="lbl">الوصف</label><textarea class="inp" name="h_ar_subtitle" id="h-ar-subtitle" rows="2"></textarea></div>
            <div class="field" id="h-ar-extra1-wrap" style="display:none"><label class="lbl">نص زر "عرض التفاصيل"</label><input class="inp" name="h_ar_view" id="h-ar-extra1"></div>
            <div class="field" id="h-ar-extra2-wrap" style="display:none"><label class="lbl">نص زر "إغلاق"</label><input class="inp" name="h_ar_close" id="h-ar-extra2"></div>
            <div class="field" id="h-ar-extra3-wrap" style="display:none"><label class="lbl">نص زر "طلب هذه الخدمة"</label><input class="inp" name="h_ar_request" id="h-ar-extra3"></div>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800" dir="ltr">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">English</h4>
            <div class="field"><label class="lbl">Badge</label><input class="inp" name="h_en_badge" id="h-en-badge"></div>
            <div class="field"><label class="lbl">Title</label><input class="inp" name="h_en_title" id="h-en-title"></div>
            <div class="field"><label class="lbl">Subtitle</label><textarea class="inp" name="h_en_subtitle" id="h-en-subtitle" rows="2"></textarea></div>
            <div class="field" id="h-en-extra1-wrap" style="display:none"><label class="lbl">"View Details" Label</label><input class="inp" name="h_en_view" id="h-en-extra1"></div>
            <div class="field" id="h-en-extra2-wrap" style="display:none"><label class="lbl">"Close" Label</label><input class="inp" name="h_en_close" id="h-en-extra2"></div>
            <div class="field" id="h-en-extra3-wrap" style="display:none"><label class="lbl">"Request Service" Label</label><input class="inp" name="h_en_request" id="h-en-extra3"></div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
          <button type="button" onclick="closeHeaderModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">إلغاء</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg">حفظ العنوان</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Slide Modal (from legacy slider editor) -->
  <div id="slideModal" class="admin-modal fixed hidden inset-0 flex items-center justify-center p-4 overflow-y-auto">
    <div class="admin-modal-panel bg-[#0D1733] px-5 py-6 sm:px-6 sm:py-7 max-w-2xl">
      <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-6">
        <h3 id="slideModalTitle" class="text-lg font-bold text-white">شريحة السلايدر</h3>
        <button onclick="closeSlideModal()" class="text-slate-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="action" value="save_slide">
        <input type="hidden" name="slide_id" id="slideId" value="">
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
          <label class="lbl">صورة الخلفية: ارفع صورة أو اكتب مسار/رابط</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-yellow-500/20 file:text-yellow-400 file:text-xs">
            <input type="text" name="image_url" id="slideImage" class="inp" placeholder="assets/images/hero/hero-1.svg">
          </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">عربي</h4>
            <div class="field"><label class="lbl">الوسم</label><input class="inp" name="ar_badge" id="sl-ar-badge"></div>
            <div class="field"><label class="lbl">العنوان *</label><input class="inp" name="ar_title" id="sl-ar-title" required></div>
            <div class="field"><label class="lbl">الوصف</label><textarea class="inp" name="ar_description" id="sl-ar-desc" rows="2"></textarea></div>
            <div class="field"><label class="lbl">نص الزر الرئيسي</label><input class="inp" name="ar_cta_primary" id="sl-ar-cta"></div>
            <div class="field"><label class="lbl">رابط الزر الرئيسي</label><input class="inp" name="ar_cta_primary_link" id="sl-ar-cta-link"></div>
            <div class="field"><label class="lbl">نص الزر الثانوي</label><input class="inp" name="ar_cta_secondary" id="sl-ar-cta2"></div>
            <div class="field"><label class="lbl">رابط الزر الثانوي</label><input class="inp" name="ar_cta_secondary_link" id="sl-ar-cta2-link"></div>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800" dir="ltr">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">English</h4>
            <div class="field"><label class="lbl">Badge</label><input class="inp" name="en_badge" id="sl-en-badge"></div>
            <div class="field"><label class="lbl">Title *</label><input class="inp" name="en_title" id="sl-en-title" required></div>
            <div class="field"><label class="lbl">Description</label><textarea class="inp" name="en_description" id="sl-en-desc" rows="2"></textarea></div>
            <div class="field"><label class="lbl">Primary CTA Label</label><input class="inp" name="en_cta_primary" id="sl-en-cta"></div>
            <div class="field"><label class="lbl">Primary CTA Link</label><input class="inp" name="en_cta_primary_link" id="sl-en-cta-link"></div>
            <div class="field"><label class="lbl">Secondary CTA Label</label><input class="inp" name="en_cta_secondary" id="sl-en-cta2"></div>
            <div class="field"><label class="lbl">Secondary CTA Link</label><input class="inp" name="en_cta_secondary_link" id="sl-en-cta2-link"></div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
          <button type="button" onclick="closeSlideModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">إلغاء</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg">حفظ الشريحة</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Careers Why Join Modal -->
  <div id="careersWhyModal" class="admin-modal fixed hidden inset-0 flex items-center justify-center p-4 overflow-y-auto">
    <div class="admin-modal-panel bg-[#0D1733] px-5 py-6 sm:px-6 sm:py-7 max-w-2xl">
      <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-4">
        <h3 id="careersWhyModalTitle" class="text-lg font-bold text-white">إضافة / تعديل بطاقة لماذا تنضم</h3>
        <button onclick="closeCareersWhyModal()" class="text-slate-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="save_careers_why">
        <input type="hidden" name="why_id" id="cWhyId">
        <div class="field"><label class="lbl">أيقونة FontAwesome</label><input class="inp" id="cWhyIcon" name="icon" list="iconsList"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">عربي</h4>
            <div class="field"><label class="lbl">العنوان</label><input class="inp" name="ar_title" id="cw-ar-title" required></div>
            <div class="field"><label class="lbl">الوصف</label><textarea class="inp" name="ar_desc" id="cw-ar-desc" rows="3"></textarea></div>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800" dir="ltr">
            <h4 class="text-xs font-bold text-yellow-400 mb-2">English</h4>
            <div class="field"><label class="lbl">Title</label><input class="inp" name="en_title" id="cw-en-title" required></div>
            <div class="field"><label class="lbl">Description</label><textarea class="inp" name="en_desc" id="cw-en-desc" rows="3"></textarea></div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
          <button type="button" onclick="closeCareersWhyModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">إلغاء</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg">حفظ البطاقة</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Careers Vacancy Modal -->
  <div id="careersVacancyModal" class="admin-modal fixed hidden inset-0 flex items-center justify-center p-4 overflow-y-auto">
    <div class="admin-modal-panel bg-[#0D1733] px-5 py-6 sm:px-6 sm:py-7 max-w-4xl w-full">
      <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-4">
        <h3 id="careersVacancyModalTitle" class="text-lg font-bold text-white">إضافة / تعديل وظيفة شاغرة</h3>
        <button onclick="closeCareersVacancyModal()" class="text-slate-400 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="save_careers_vacancy">
        <input type="hidden" name="vac_id" id="cVacId">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="field"><label class="lbl">أيقونة FontAwesome</label><input class="inp" id="cVacIcon" name="icon" list="iconsList" value="fa-briefcase"></div>
          <div class="field"><label class="lbl">الموقع (المدينة)</label><input class="inp" id="cVacLocation" name="location" placeholder="القاهرة - نزهة"></div>
          <div class="field"><label class="lbl">نوع الوظيفة</label>
            <select class="inp" id="cVacType" name="type">
              <option value="full-time">دوام كامل</option>
              <option value="part-time">دوام جزئي</option>
              <option value="contract">عقد</option>
              <option value="internship">تدريب</option>
              <option value="remote">عن بعد</option>
            </select>
          </div>
          <div class="field"><label class="lbl">الراتب (اختياري)</label><input class="inp" id="cVacSalary" name="salary" placeholder="7,000 - 10,000 جنيه"></div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">عربي</h4>
            <div class="field"><label class="lbl">المسمى الوظيفي *</label><input class="inp" name="ar_title" id="cv-ar-title" required></div>
            <div class="field"><label class="lbl">الوصف المختصر / الشعار</label><input class="inp" name="ar_tagline" id="cv-ar-tagline"></div>
            <div class="field"><label class="lbl">المتطلبات (كل سطر = متطلب)</label><textarea class="inp" name="ar_reqs" id="cv-ar-reqs" rows="7" placeholder="بكالوريوس إدارة أعمال&#10;3 سنوات خبرة على الأقل&#10;إجادة برامج Office"></textarea></div>
          </div>
          <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800" dir="ltr">
            <h4 class="text-xs font-bold text-yellow-400 mb-3">English</h4>
            <div class="field"><label class="lbl">Job Title *</label><input class="inp" name="en_title" id="cv-en-title" required></div>
            <div class="field"><label class="lbl">Short Tagline</label><input class="inp" name="en_tagline" id="cv-en-tagline"></div>
            <div class="field"><label class="lbl">Requirements (one per line)</label><textarea class="inp" name="en_reqs" id="cv-en-reqs" rows="7" placeholder="Bachelor in Business Admin&#10;Minimum 3 years experience&#10;Proficient in MS Office"></textarea></div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
          <button type="button" onclick="closeCareersVacancyModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">إلغاء</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg">حفظ الوظيفة</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    window.CMS = <?php echo json_encode([
        'services' => $content['services']['items'],
        'servicesHeader' => $content['services']['header'],
        'whyUs' => $content['whyUs']['cards'],
        'whyHeader' => $content['whyUs']['header'],
        'clients' => $content['clients'],
        'clientsHeader' => $content['clientsHeader'],
        'careers' => $content['careers'] ?? [
            'header'    => ['ar'=>[], 'en'=>[]],
            'whyJoin'  => [],
            'vacancies'=> [],
            'lists'    => ['jobRoles'=>[], 'experiences'=>[], 'governorates'=>[]]
        ]
      ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;

    /* ---------- generic helpers ---------- */
    function sv(id, v) { var el = document.getElementById(id); if (el) el.value = (v === undefined || v === null) ? '' : v; }

    /* ---------- SERVICES ---------- */
    function openAddService() {
      document.getElementById('serviceModalTitle').textContent = 'إضافة خدمة جديدة';
      sv('serviceId', ''); sv('serviceSlug', '');
      sv('serviceIcon', 'fa-gear');
      sv('serviceImage', ''); sv('serviceGallery1', ''); sv('serviceGallery2', '');
      ['s-ar-title','s-ar-shortDesc','s-ar-modalTitle','s-ar-overview','s-ar-tasks','s-ar-equipment'].forEach(i => sv(i,''));
      ['s-en-title','s-en-shortDesc','s-en-modalTitle','s-en-overview','s-en-tasks','s-en-equipment'].forEach(i => sv(i,''));
      document.getElementById('serviceSlug').readOnly = false;
      document.getElementById('serviceModal').classList.remove('hidden');
    }
    function editService(it) {
      document.getElementById('serviceModalTitle').textContent = 'تعديل خدمة';
      sv('serviceId', it.id); sv('serviceSlug', it.id);
      sv('serviceIcon', it.icon || 'fa-gear'); sv('serviceImage', it.image || '');
      sv('serviceGallery1', (it.gallery && it.gallery[1]) || ''); sv('serviceGallery2', (it.gallery && it.gallery[2]) || '');
      var a = it.ar || {}, en = it.en || {};
      sv('s-ar-title', a.title); sv('s-ar-shortDesc', a.shortDesc); sv('s-ar-modalTitle', a.modalTitle);
      sv('s-ar-overview', a.overview); sv('s-ar-tasks', (a.tasks || []).join('\n')); sv('s-ar-equipment', a.equipment);
      sv('s-en-title', en.title); sv('s-en-shortDesc', en.shortDesc); sv('s-en-modalTitle', en.modalTitle);
      sv('s-en-overview', en.overview); sv('s-en-tasks', (en.tasks || []).join('\n')); sv('s-en-equipment', en.equipment);
      document.getElementById('serviceSlug').readOnly = true;
      document.getElementById('serviceModal').classList.remove('hidden');
    }
    function closeServiceModal() { document.getElementById('serviceModal').classList.add('hidden'); }

    /* ---------- WHY US ---------- */
    function openAddWhy() {
      document.getElementById('whyModalTitle').textContent = 'إضافة بطاقة جديدة';
      sv('whyId', ''); sv('whyIcon', 'fa-award');
      ['w-ar-title','w-ar-desc','w-en-title','w-en-desc'].forEach(i => sv(i,''));
      document.getElementById('whyModal').classList.remove('hidden');
    }
    function editWhy(idx) {
      var c = window.CMS.whyUs[idx];
      if (!c) return;
      document.getElementById('whyModalTitle').textContent = 'تعديل بطاقة';
      sv('whyId', String(idx + 1)); sv('whyIcon', c.icon || 'fa-award');
      sv('w-ar-title', c.ar && c.ar.title); sv('w-ar-desc', c.ar && c.ar.desc);
      sv('w-en-title', c.en && c.en.title); sv('w-en-desc', c.en && c.en.desc);
      document.getElementById('whyModal').classList.remove('hidden');
    }
    function closeWhyModal() { document.getElementById('whyModal').classList.add('hidden'); }

    /* ---------- CLIENTS ---------- */
    function openAddClient() {
      sv('clientId', ''); sv('c-ar-name', ''); sv('c-en-name', ''); sv('c-image', ''); sv('c-sector', 'corporate');
      document.getElementById('clientModal').classList.remove('hidden');
    }
    function editClient(c) {
      sv('clientId', c.id || ''); sv('c-ar-name', c.ar && c.ar.name); sv('c-en-name', c.en && c.en.name);
      sv('c-image', c.image || ''); sv('c-sector', c.sector || 'corporate');
      document.getElementById('clientModal').classList.remove('hidden');
    }
    function closeClientModal() { document.getElementById('clientModal').classList.add('hidden'); }

    /* ---------- SECTION HEADERS ---------- */
    function openHeaderModal(sectionFor) {
      var h;
      var extraShow = false;
      if (sectionFor === 'why') {
        document.getElementById('headerAction').value = 'save_why_header';
        h = window.CMS.whyHeader || {};
      } else if (sectionFor === 'clients') {
        document.getElementById('headerAction').value = 'save_clients_header';
        h = window.CMS.clientsHeader || {};
      } else {
        document.getElementById('headerAction').value = 'save_services_header';
        h = window.CMS.servicesHeader || {};
        extraShow = true;
      }
      var a = h.ar || {}, en = h.en || {};
      sv('h-ar-badge', a.badge); sv('h-ar-title', a.title); sv('h-ar-subtitle', a.subtitle);
      sv('h-en-badge', en.badge); sv('h-en-title', en.title); sv('h-en-subtitle', en.subtitle);
      sv('h-ar-extra1', a.viewDetails); sv('h-ar-extra2', a.closeModal); sv('h-ar-extra3', a.requestService);
      sv('h-en-extra1', en.viewDetails); sv('h-en-extra2', en.closeModal); sv('h-en-extra3', en.requestService);
      ['h-ar-extra1','h-ar-extra2','h-ar-extra3','h-en-extra1','h-en-extra2','h-en-extra3'].forEach(id => {
        document.getElementById(id + '-wrap').style.display = (id.indexOf('ar-extra') !== -1 || id.indexOf('en-extra') !== -1) && extraShow ? 'block' : 'none';
      });
      document.getElementById('headerModal').classList.remove('hidden');
    }
    function closeHeaderModal() { document.getElementById('headerModal').classList.add('hidden'); }

    /* ---------- SLIDE ---------- */
    function openSlideModal() {
      document.getElementById('slideModalTitle').textContent = 'إضافة شريحة جديدة';
      sv('slideId', ''); sv('slideImage', '');
      ['sl-ar-badge','sl-ar-title','sl-ar-desc','sl-ar-cta','sl-ar-cta-link','sl-ar-cta2','sl-ar-cta2-link'].forEach(i => sv(i,''));
      ['sl-en-badge','sl-en-title','sl-en-desc','sl-en-cta','sl-en-cta-link','sl-en-cta2','sl-en-cta2-link'].forEach(i => sv(i,''));
      document.getElementById('slideModal').classList.remove('hidden');
    }
    function editSlide(s) {
      document.getElementById('slideModalTitle').textContent = 'تعديل الشريحة';
      sv('slideId', s.id || ''); sv('slideImage', s.image || '');
      var a = s.ar || {}, en = s.en || {};
      sv('sl-ar-badge', a.badge); sv('sl-ar-title', a.title); sv('sl-ar-desc', a.description);
      sv('sl-ar-cta', a.ctaPrimary); sv('sl-ar-cta-link', a.ctaPrimaryLink); sv('sl-ar-cta2', a.ctaSecondary); sv('sl-ar-cta2-link', a.ctaSecondaryLink);
      sv('sl-en-badge', en.badge); sv('sl-en-title', en.title); sv('sl-en-desc', en.description);
      sv('sl-en-cta', en.ctaPrimary); sv('sl-en-cta-link', en.ctaPrimaryLink); sv('sl-en-cta2', en.ctaSecondary); sv('sl-en-cta2-link', en.ctaSecondaryLink);
      document.getElementById('slideModal').classList.remove('hidden');
    }
    function closeSlideModal() { document.getElementById('slideModal').classList.add('hidden'); }

    /* ---------- CAREERS WHY JOIN ---------- */
    function openCareersWhyModal() {
      document.getElementById('careersWhyModalTitle').textContent = 'إضافة بطاقة جديدة';
      sv('cWhyId', ''); sv('cWhyIcon', 'fa-award');
      ['cw-ar-title','cw-ar-desc','cw-en-title','cw-en-desc'].forEach(i => sv(i,''));
      document.getElementById('careersWhyModal').classList.remove('hidden');
    }
    function editCareersWhy(idx) {
      var c = (window.CMS.careers && window.CMS.careers.whyJoin) ? window.CMS.careers.whyJoin[idx] : null;
      if (!c) return;
      document.getElementById('careersWhyModalTitle').textContent = 'تعديل بطاقة';
      sv('cWhyId', String(idx + 1)); sv('cWhyIcon', c.icon || 'fa-award');
      sv('cw-ar-title', c.ar && c.ar.title); sv('cw-ar-desc', c.ar && c.ar.desc);
      sv('cw-en-title', c.en && c.en.title); sv('cw-en-desc', c.en && c.en.desc);
      document.getElementById('careersWhyModal').classList.remove('hidden');
    }
    function closeCareersWhyModal() { document.getElementById('careersWhyModal').classList.add('hidden'); }

    /* ---------- CAREERS VACANCY ---------- */
    function openCareersVacancyModal() {
      document.getElementById('careersVacancyModalTitle').textContent = 'إضافة وظيفة شاغرة جديدة';
      sv('cVacId', ''); sv('cVacIcon', 'fa-briefcase');
      sv('cVacLocation', ''); sv('cVacType', 'full-time'); sv('cVacSalary', '');
      ['cv-ar-title','cv-ar-tagline','cv-ar-reqs','cv-en-title','cv-en-tagline','cv-en-reqs'].forEach(i => sv(i,''));
      document.getElementById('careersVacancyModal').classList.remove('hidden');
    }
    function editCareersVacancy(idx) {
      var v = (window.CMS.careers && window.CMS.careers.vacancies) ? window.CMS.careers.vacancies[idx] : null;
      if (!v) return;
      document.getElementById('careersVacancyModalTitle').textContent = 'تعديل الوظيفة متاحةة';
      sv('cVacId', v.id || String(idx + 1));
      sv('cVacIcon', v.icon || 'fa-briefcase');
      sv('cVacLocation', v.location || '');
      sv('cVacType', v.type || 'full-time');
      sv('cVacSalary', v.salary || '');
      var a = v.ar || {}, en = v.en || {};
      sv('cv-ar-title', a.title); sv('cv-ar-tagline', a.tagline);
      sv('cv-ar-reqs', (a.reqs || []).join('\n'));
      sv('cv-en-title', en.title); sv('cv-en-tagline', en.tagline);
      sv('cv-en-reqs', (en.reqs || []).join('\n'));
      document.getElementById('careersVacancyModal').classList.remove('hidden');
    }
    function closeCareersVacancyModal() { document.getElementById('careersVacancyModal').classList.add('hidden'); }

    /* ---------- close modals on Escape ---------- */
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape') {
        ['serviceModal','whyModal','clientModal','headerModal','slideModal','careersWhyModal','careersVacancyModal'].forEach(function (id) {
          var m = document.getElementById(id); if (m) m.classList.add('hidden');
        });
      }
    });
  </script>

<?php endif; ?>

</body>
</html>