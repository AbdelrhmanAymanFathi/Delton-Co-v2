<?php
/**
 * Delton Facility Management - Slider Administration Panel
 * Standalone PHP single-file dashboard for HostGator shared hosting.
 * No SQL database needed; reads & writes to slider.json.
 */

session_start();

// Configuration
$ADMIN_PIN = "delton2026"; // Change your admin password here
$JSON_FILE = __DIR__ . "/slider.json";
$UPLOAD_DIR = __DIR__ . "/../assets/images/hero/";

// Load existing slides
$slides = [];
if (file_exists($JSON_FILE)) {
    $content = file_get_contents($JSON_FILE);
    $slides = json_decode($content, true) ?: [];
}

// Authentication handling
$message = "";
$messageType = "";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Handle Login
    if ($_POST['action'] === 'login') {
        if (trim($_POST['pin'] ?? '') === $ADMIN_PIN) {
            $_SESSION['delton_admin_logged'] = true;
            header("Location: index.php");
            exit;
        } else {
            $message = "كلمة المرور غير صحيحة / Invalid Admin Password";
            $messageType = "error";
        }
    }

    // Handle Slide Save / Update (Requires Auth)
    if (!empty($_SESSION['delton_admin_logged'])) {
        if ($_POST['action'] === 'save_slide') {
            $slide_id = isset($_POST['slide_id']) && $_POST['slide_id'] !== '' ? intval($_POST['slide_id']) : null;
            
            // Image handling (Upload or URL)
            $image_path = trim($_POST['image_url'] ?? '');
            
            if (!empty($_FILES['image_file']['name'])) {
                $file = $_FILES['image_file'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
                
                if (in_array($ext, $allowed) && $file['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($UPLOAD_DIR)) {
                        mkdir($UPLOAD_DIR, 0755, true);
                    }
                    $filename = "hero-" . time() . "." . $ext;
                    $target = $UPLOAD_DIR . $filename;
                    if (move_uploaded_file($file['tmp_name'], $target)) {
                        $image_path = "assets/images/hero/" . $filename;
                    }
                }
            }

            if (empty($image_path)) {
                $image_path = "assets/images/hero/hero-1.svg";
            }

            $new_slide = [
                "id" => $slide_id ?: (count($slides) ? max(array_column($slides, 'id')) + 1 : 1),
                "image" => $image_path,
                "ar" => [
                    "badge" => trim($_POST['ar_badge'] ?? ''),
                    "title" => trim($_POST['ar_title'] ?? ''),
                    "description" => trim($_POST['ar_description'] ?? ''),
                    "ctaPrimary" => trim($_POST['ar_cta_primary'] ?? 'طلب عرض سعر'),
                    "ctaPrimaryLink" => trim($_POST['ar_cta_primary_link'] ?? '#contact'),
                    "ctaSecondary" => trim($_POST['ar_cta_secondary'] ?? 'استكشف خدماتنا'),
                    "ctaSecondaryLink" => trim($_POST['ar_cta_secondary_link'] ?? '#services'),
                ],
                "en" => [
                    "badge" => trim($_POST['en_badge'] ?? ''),
                    "title" => trim($_POST['en_title'] ?? ''),
                    "description" => trim($_POST['en_description'] ?? ''),
                    "ctaPrimary" => trim($_POST['en_cta_primary'] ?? 'Request a Quote'),
                    "ctaPrimaryLink" => trim($_POST['en_cta_primary_link'] ?? '#contact'),
                    "ctaSecondary" => trim($_POST['en_cta_secondary'] ?? 'Explore Services'),
                    "ctaSecondaryLink" => trim($_POST['en_cta_secondary_link'] ?? '#services'),
                ]
            ];

            $found = false;
            if ($slide_id) {
                foreach ($slides as $k => $s) {
                    if ($s['id'] === $slide_id) {
                        $slides[$k] = $new_slide;
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $slides[] = $new_slide;
            }

            // Write back to slider.json
            if (file_put_contents($JSON_FILE, json_encode($slides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $message = "تم حفظ بيانات السلايدر بنجاح وتحديث الموقع مباشرة!";
                $messageType = "success";
            } else {
                $message = "خطأ في أذونات الملف slider.json. تأكد من إعطاء تصريح 666 أو 777 عبر cPanel.";
                $messageType = "error";
            }
        }

        // Handle Delete Slide
        if ($_POST['action'] === 'delete_slide') {
            $del_id = intval($_POST['delete_id'] ?? 0);
            $slides = array_filter($slides, function($s) use ($del_id) {
                return $s['id'] !== $del_id;
            });
            $slides = array_values($slides); // Reindex
            file_put_contents($JSON_FILE, json_encode($slides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message = "تم حذف الشريحة بنجاح!";
            $messageType = "success";
        }
    }
}

$isLoggedIn = !empty($_SESSION['delton_admin_logged']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة تحكم السلايدر | Delton Slider Manager</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Cairo', sans-serif; background-color: #0B132B; color: #F8FAFC; }
    .gold-gradient { background: linear-gradient(135deg, #DFB739 0%, #C9A227 60%, #A88118 100%); }
    .gold-border { border-color: #C9A227; }
  </style>
</head>
<body class="min-h-screen py-10 px-4">

  <!-- Header -->
  <header class="max-w-5xl mx-auto mb-8 flex flex-wrap items-center justify-between gap-4 border-b border-slate-700/80 pb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-yellow-500/10 border border-yellow-500/40 flex items-center justify-center text-[#C9A227] text-2xl">
        <i class="fa-solid fa-sliders"></i>
      </div>
      <div>
        <h1 class="text-2xl font-extrabold text-white">لوحة تحكم السلايدر الرئيسي</h1>
        <p class="text-xs text-[#C9A227] font-semibold">Delton Facility Management - Slider CMS</p>
      </div>
    </div>
    
    <div class="flex items-center gap-3">
      <a href="../index.html" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold border border-slate-700 transition flex items-center gap-2">
        <i class="fa-solid fa-arrow-up-right-from-square text-[#C9A227]"></i>
        <span>معاينة الموقع</span>
      </a>
      <?php if ($isLoggedIn): ?>
      <a href="?logout=1" class="px-4 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-300 text-sm font-semibold border border-red-500/40 transition flex items-center gap-2">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        <span>تسجيل الخروج</span>
      </a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Notification Message -->
  <?php if ($message): ?>
  <div class="max-w-5xl mx-auto mb-6 p-4 rounded-xl border text-sm font-semibold flex items-center gap-3 <?php echo $messageType === 'success' ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-200' : 'bg-red-500/20 border-red-500/40 text-red-200'; ?>">
    <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> text-lg"></i>
    <span><?php echo htmlspecialchars($message); ?></span>
  </div>
  <?php endif; ?>

  <?php if (!$isLoggedIn): ?>
  <!-- Login Form -->
  <div class="max-w-md mx-auto my-12 p-8 rounded-2xl bg-[#111C38] border border-slate-700 shadow-2xl">
    <div class="text-center mb-6">
      <div class="w-16 h-16 rounded-2xl bg-yellow-500/15 border border-yellow-500/30 flex items-center justify-center text-[#C9A227] text-3xl mx-auto mb-4">
        <i class="fa-solid fa-lock"></i>
      </div>
      <h2 class="text-xl font-bold text-white mb-1">تسجيل دخول المدير</h2>
      <p class="text-xs text-slate-400">يرجى إدخال كلمة المرور لتعديل شرائح العرض</p>
    </div>

    <form method="POST" class="space-y-4">
      <input type="hidden" name="action" value="login">
      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-2">كلمة المرور (Admin PIN):</label>
        <input type="password" name="pin" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 focus:border-[#C9A227] text-white outline-none">
      </div>
      <button type="submit" class="w-full py-3 rounded-xl gold-gradient text-[#0B132B] font-bold shadow-lg transition transform hover:-translate-y-0.5">
        دخول لوحة التحكم
      </button>
      <p class="text-[11px] text-center text-slate-500">كلمة المرور الافتراضية: <code class="text-yellow-400">delton2026</code></p>
    </form>
  </div>

  <?php else: ?>

  <!-- Dashboard Main Content -->
  <main class="max-w-5xl mx-auto space-y-8">
    
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold text-white flex items-center gap-2">
        <i class="fa-solid fa-layer-group text-[#C9A227]"></i>
        <span>شرائح السلايدر الحالية (<?php echo count($slides); ?>)</span>
      </h2>
      <button onclick="openSlideModal()" class="px-5 py-2.5 rounded-xl gold-gradient text-[#0B132B] font-bold text-sm shadow-lg flex items-center gap-2 transition hover:-translate-y-0.5">
        <i class="fa-solid fa-plus"></i>
        <span>إضافة شريحة جديدة</span>
      </button>
    </div>

    <!-- Slides Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($slides as $idx => $s): ?>
      <div class="p-5 rounded-2xl bg-[#111C38] border border-slate-700/80 hover:border-[#C9A227] transition flex flex-col justify-between shadow-xl">
        <div>
          <!-- Thumbnail & Actions -->
          <div class="w-full h-40 rounded-xl overflow-hidden mb-4 border border-slate-800 relative group bg-slate-900">
            <img src="../<?php echo htmlspecialchars($s['image']); ?>" alt="Slide" class="w-full h-full object-cover">
            <div class="absolute top-2 right-2 bg-slate-900/80 backdrop-blur px-2.5 py-1 rounded-lg text-xs font-bold text-[#C9A227] border border-yellow-500/30">
              شريحة رقم <?php echo $idx + 1; ?>
            </div>
          </div>

          <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-md bg-yellow-500/10 text-[#C9A227] border border-yellow-500/20 mb-2">
            <?php echo htmlspecialchars($s['ar']['badge'] ?? 'بدون وسم'); ?>
          </span>

          <h3 class="text-base font-bold text-white mb-2 leading-snug">
            <?php echo htmlspecialchars($s['ar']['title'] ?? ''); ?>
          </h3>
          <p class="text-xs text-slate-400 line-clamp-2 mb-2">
            <?php echo htmlspecialchars($s['ar']['description'] ?? ''); ?>
          </p>
          <p class="text-[11px] text-slate-500 italic">
            EN: <?php echo htmlspecialchars($s['en']['title'] ?? ''); ?>
          </p>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-700/60 flex items-center justify-between">
          <button onclick='editSlide(<?php echo json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-pen-to-square text-[#C9A227]"></i>
            <span>تعديل الشريحة</span>
          </button>

          <form method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذه الشريحة؟');">
            <input type="hidden" name="action" value="delete_slide">
            <input type="hidden" name="delete_id" value="<?php echo $s['id']; ?>">
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs font-semibold border border-red-500/30 flex items-center gap-1 transition">
              <i class="fa-solid fa-trash"></i>
              <span>حذف</span>
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </main>

  <!-- Slide Edit / Add Modal -->
  <div id="slideModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-[#0D1733] border border-slate-700 w-full max-w-2xl rounded-2xl p-6 sm:p-8 shadow-2xl my-8">
      
      <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-6">
        <h3 id="modalTitle" class="text-lg font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-pen-fancy text-[#C9A227]"></i>
          <span>تعديل الشريحة</span>
        </h3>
        <button onclick="closeSlideModal()" class="text-slate-400 hover:text-white text-xl">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="action" value="save_slide">
        <input type="hidden" name="slide_id" id="formSlideId" value="">

        <!-- Image source -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
          <label class="block text-xs font-bold text-[#C9A227]">صورة خلفية الشريحة:</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <span class="block text-[11px] text-slate-400 mb-1">مسار أو رابط الصورة (Image URL / Path):</span>
              <input type="text" name="image_url" id="formImageUrl" placeholder="assets/images/hero/hero-1.svg" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs outline-none focus:border-[#C9A227]">
            </div>
            <div>
              <span class="block text-[11px] text-slate-400 mb-1">أو رفع صورة جديدة من جهازك:</span>
              <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-yellow-500/20 file:text-yellow-400 file:text-xs">
            </div>
          </div>
        </div>

        <!-- Arabic Details -->
        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
          <h4 class="text-xs font-bold text-yellow-400 flex items-center gap-1.5">
            <i class="fa-solid fa-language"></i>
            <span>المحتوى باللغة العربية (Arabic)</span>
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">الوسم العلوي (Badge):</label>
              <input type="text" name="ar_badge" id="formArBadge" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs outline-none focus:border-[#C9A227]">
            </div>
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">العنوان الرئيسي (Headline):</label>
              <input type="text" name="ar_title" id="formArTitle" required class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs outline-none focus:border-[#C9A227]">
            </div>
          </div>
          <div>
            <label class="block text-[11px] text-slate-400 mb-1">الوصف التفصيلي (Description):</label>
            <textarea name="ar_description" id="formArDesc" rows="2" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs outline-none focus:border-[#C9A227]"></textarea>
          </div>
        </div>

        <!-- English Details -->
        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3" dir="ltr">
          <h4 class="text-xs font-bold text-yellow-400 flex items-center gap-1.5">
            <i class="fa-solid fa-globe"></i>
            <span>English Content</span>
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Top Badge:</label>
              <input type="text" name="en_badge" id="formEnBadge" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs outline-none focus:border-[#C9A227]">
            </div>
            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Main Headline:</label>
              <input type="text" name="en_title" id="formEnTitle" required class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs outline-none focus:border-[#C9A227]">
            </div>
          </div>
          <div>
            <label class="block text-[11px] text-slate-400 mb-1">Description:</label>
            <textarea name="en_description" id="formEnDesc" rows="2" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs outline-none focus:border-[#C9A227]"></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
          <button type="button" onclick="closeSlideModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700 transition">
            إلغاء
          </button>
          <button type="submit" class="px-6 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg transition hover:-translate-y-0.5">
            حفظ الشريحة في slider.json
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openSlideModal() {
      document.getElementById('modalTitle').innerHTML = '<i class=\"fa-solid fa-plus text-[#C9A227]\"></i> <span>إضافة شريحة جديدة</span>';
      document.getElementById('formSlideId').value = '';
      document.getElementById('formImageUrl').value = 'assets/images/hero/hero-1.svg';
      document.getElementById('formArBadge').value = '';
      document.getElementById('formArTitle').value = '';
      document.getElementById('formArDesc').value = '';
      document.getElementById('formEnBadge').value = '';
      document.getElementById('formEnTitle').value = '';
      document.getElementById('formEnDesc').value = '';
      document.getElementById('slideModal').classList.remove('hidden');
    }

    function editSlide(slide) {
      document.getElementById('modalTitle').innerHTML = '<i class=\"fa-solid fa-pen-fancy text-[#C9A227]\"></i> <span>تعديل الشريحة</span>';
      document.getElementById('formSlideId').value = slide.id || '';
      document.getElementById('formImageUrl').value = slide.image || '';
      document.getElementById('formArBadge').value = slide.ar ? slide.ar.badge : '';
      document.getElementById('formArTitle').value = slide.ar ? slide.ar.title : '';
      document.getElementById('formArDesc').value = slide.ar ? slide.ar.description : '';
      document.getElementById('formEnBadge').value = slide.en ? slide.en.badge : '';
      document.getElementById('formEnTitle').value = slide.en ? slide.en.title : '';
      document.getElementById('formEnDesc').value = slide.en ? slide.en.description : '';
      document.getElementById('slideModal').classList.remove('hidden');
    }

    function closeSlideModal() {
      document.getElementById('slideModal').classList.add('hidden');
    }
  </script>
  <?php endif; ?>

</body>
</html>
