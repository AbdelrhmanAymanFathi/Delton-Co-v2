/**
 * Delton Facility Management - Careers Page Controller
 * Bilingual controller, sticky navbar, mobile drawer, and job application form handling.
 */

let currentLanguage = localStorage.getItem('delton_lang') || 'ar';

function getNestedTranslation(obj, path) {
  return path.split('.').reduce((prev, curr) => prev ? prev[curr] : undefined, obj);
}

function isRtlLang() {
  return currentLanguage === 'ar';
}

function applyTranslations() {
  const t = translations[currentLanguage];
  if (!t) return;

  const isRtl = isRtlLang();
  document.documentElement.dir = isRtl ? 'rtl' : 'ltr';
  document.documentElement.lang = currentLanguage;

  if (isRtl) {
    document.body.classList.remove('font-en');
    document.body.classList.add('font-ar');
  } else {
    document.body.classList.remove('font-ar');
    document.body.classList.add('font-en');
  }

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    const val = getNestedTranslation(t, key);
    if (val !== undefined) el.textContent = val;
  });

  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    const val = getNestedTranslation(t, key);
    if (val !== undefined) el.setAttribute('placeholder', val);
  });

  // Brand text
  const brandName = isRtl ? 'ديلتون' : 'DELTON';
  const brandSubtitle = isRtl ? 'إدارة المرافق' : 'Facility Management';
  document.querySelectorAll('[data-brand-primary]').forEach(el => el.textContent = brandName);
  document.querySelectorAll('[data-brand-secondary]').forEach(el => el.textContent = brandSubtitle);

  // Language toggle buttons
  ['langToggleBtn', 'langToggleBtnMobile'].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) btn.innerHTML = `<i class="fa-solid fa-globe text-yellow-500 mr-1.5 ml-1.5"></i> ${t.nav.langToggle}`;
  });

  // Dynamic contact info
  const email = window.__ContactEmail || 'info@delton-eg.com';
  document.querySelectorAll('[data-dyn-email]').forEach(el => {
    if (el.tagName === 'A') el.setAttribute('href', 'mailto:' + email);
    const innerText = el.querySelector('[data-dyn-email-text]');
    if (innerText) innerText.textContent = email;
    else el.textContent = email;
  });
  document.querySelectorAll('[data-dyn-email-text]').forEach(el => {
    el.textContent = email;
  });

  const phones = (window.__ContactPhones && window.__ContactPhones.length)
    ? window.__ContactPhones : ['01102668966', '01123544717', '01123545516'];
  document.querySelectorAll('[data-dyn-phone]').forEach(el => {
    const idx = parseInt(el.getAttribute('data-dyn-phone'), 10) || 0;
    const p = phones[idx] || phones[0] || '';
    el.textContent = p;
    if (el.tagName === 'A') el.setAttribute('href', 'tel:' + p);
  });

  const footerAddress = document.querySelector('[data-dyn-footer-address]');
  const langKey = isRtl ? 'ar' : 'en';
  const addrText = (window.__FooterAddress && window.__FooterAddress[langKey])
    ? window.__FooterAddress[langKey]
    : (t.contact && t.contact.address);
  if (footerAddress && addrText) {
    footerAddress.textContent = addrText;
  }
  document.querySelectorAll('[data-dyn-footer-address]').forEach(el => {
    if (addrText) el.textContent = addrText;
  });

  const footerPhones = document.querySelector('[data-dyn-footer-phones]');
  if (footerPhones && window.__FooterPhones) footerPhones.textContent = window.__FooterPhones;

  // Branding logos
  const branding = window.__Branding || {
    navbar: { image: 'assets/images/logo-nav.png', width: '124', height: '50' },
    footer: { image: 'assets/images/logo-nav.png', width: '160', height: '64' }
  };
  document.querySelectorAll('[data-brand-logo]').forEach(el => {
    const role = el.getAttribute('data-brand-logo');
    const cfg = branding[role] || branding.navbar || {};
    el.src = cfg.image || 'assets/images/logo-nav.png';
    let w = String(cfg.width || '').trim();
    let h = String(cfg.height || '').trim();
    el.style.width = /^\d+(\.\d+)?$/.test(w) ? w + 'px' : w;
    el.style.height = /^\d+(\.\d+)?$/.test(h) ? h + 'px' : h;
    el.style.maxWidth = '100%';
    el.style.objectFit = 'contain';
  });

  // Profile download link
  const profileLink = document.getElementById('profileDownloadLink');
  if (profileLink) {
    profileLink.href = isRtl ? 'Delton-profile ar 2026.pdf' : 'Delton-profile EN 2026.pdf';
    profileLink.setAttribute('download', isRtl ? 'Delton-profile ar 2026.pdf' : 'Delton-profile EN 2026.pdf');
  }

  // Job suggestion datalists + experience select
  const tCareers = t.careers || {};
  const cLists = window.__CareersLists || null;
  const jobRoles = (cLists && cLists.jobRoles && cLists.jobRoles.length) ? cLists.jobRoles : (tCareers.jobRoles || []);
  const governorates = (cLists && cLists.governorates && cLists.governorates.length) ? cLists.governorates : (tCareers.governorates || []);
  const experiences = (cLists && cLists.experiences && cLists.experiences.length) ? cLists.experiences : (tCareers.experiences || []);

  const rolesDatalist = document.getElementById('jobRolesList');
  if (rolesDatalist && Array.isArray(jobRoles)) {
    rolesDatalist.innerHTML = jobRoles
      .map(r => `<option value="${r.replace(/"/g, '&quot;')}"></option>`)
      .join('');
  }
  const govsDatalist = document.getElementById('governoratesList');
  if (govsDatalist && Array.isArray(governorates)) {
    govsDatalist.innerHTML = governorates
      .map(g => `<option value="${g.replace(/"/g, '&quot;')}"></option>`)
      .join('');
  }
  const expSelect = document.getElementById('expSelect');
  if (expSelect && Array.isArray(experiences)) {
    expSelect.innerHTML = experiences
      .map((e, i) => `<option value="${e}">${e}</option>`)
      .join('');
  }
}

/**
 * Render Why Join Delton cards from CMS data
 */
function renderWhyJoinGrid() {
  const grid = document.getElementById('whyJoinGrid');
  if (!grid) return;
  const t = translations[currentLanguage] || {};
  const list = (t.careers && Array.isArray(t.careers.whyJoin) && t.careers.whyJoin.length)
    ? t.careers.whyJoin
    : [];
  if (!list.length) {
    grid.innerHTML = '';
    grid.parentElement.style.display = 'none';
    return;
  }
  grid.parentElement.style.display = '';
  grid.innerHTML = list.map((c, i) => `
    <div class="group relative p-6 sm:p-7 rounded-2xl bg-[#18141A] border border-slate-800 hover:border-yellow-500/40 shadow-xl hover:shadow-[0_10px_40px_-12px_rgba(228,177,91,0.25)] transition-all duration-300" data-aos="fade-up" data-aos-delay="${Math.min(i * 80, 400)}">
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-yellow-500/20 to-yellow-500/5 border border-yellow-500/30 flex items-center justify-center text-[#E4B15B] text-2xl mb-5 group-hover:scale-110 transition-transform duration-300">
        <i class="fa-solid ${c.icon || 'fa-award'}"></i>
      </div>
      <h3 class="text-lg font-bold text-white mb-2.5 leading-snug">${c.title || ''}</h3>
      <p class="text-sm text-slate-400 leading-relaxed">${c.desc || ''}</p>
    </div>
  `).join('');
}

/**
 * Render Open Vacancies cards from CMS data
 */
function renderVacanciesGrid() {
  const grid = document.getElementById('vacanciesGrid');
  if (!grid) return;
  const t = translations[currentLanguage] || {};
  const isRtl = isRtlLang();
  const list = (t.careers && Array.isArray(t.careers.vacancies) && t.careers.vacancies.length)
    ? t.careers.vacancies
    : [];
  if (!list.length) {
    grid.innerHTML = '';
    grid.parentElement.style.display = 'none';
    return;
  }
  grid.parentElement.style.display = '';

  const typeLabels = {
    ar: { 'full-time': 'دوام كامل', 'part-time': 'دوام جزئي', 'contract': 'عقد', 'internship': 'تدريب', 'remote': 'عن بعد' },
    en: { 'full-time': 'Full-Time', 'part-time': 'Part-Time', 'contract': 'Contract', 'internship': 'Internship', 'remote': 'Remote' }
  };
  const applyBtn = isRtl ? 'قدّم الآن' : 'Apply Now';
  const reqsTitle = isRtl ? 'المتطلبات' : 'Requirements';
  const locLabel = isRtl ? 'الموقع' : 'Location';
  const typeLabel = isRtl ? 'النوع' : 'Type';

  grid.innerHTML = list.map((v, i) => {
    const tLabel = (typeLabels[currentLanguage] && typeLabels[currentLanguage][v.type]) ? typeLabels[currentLanguage][v.type] : (v.type || '');
    const reqsHtml = Array.isArray(v.reqs) && v.reqs.length
      ? `<div class="pt-4 border-t border-slate-800/70 space-y-2">
          <p class="text-xs font-bold text-yellow-400/90 mb-2"><i class="fa-solid fa-list-check ml-1.5"></i>${reqsTitle}</p>
          <ul class="space-y-1.5">
            ${v.reqs.slice(0, 5).map(r => `<li class="text-xs text-slate-400 leading-relaxed flex items-start gap-2"><i class="fa-solid fa-check text-emerald-400 mt-0.5 text-[10px] shrink-0"></i><span>${r}</span></li>`).join('')}
            ${v.reqs.length > 5 ? `<li class="text-xs text-slate-500 italic">${isRtl ? '... وغيرها' : '... and more'}</li>` : ''}
          </ul>
        </div>` : '';
    return `
      <div class="group relative p-6 sm:p-7 rounded-2xl bg-[#18141A] border border-slate-800 hover:border-yellow-500/40 shadow-xl hover:shadow-[0_10px_40px_-12px_rgba(228,177,91,0.25)] transition-all duration-300 flex flex-col" data-aos="fade-up" data-aos-delay="${Math.min(i * 100, 400)}">
        <div class="flex items-start justify-between gap-3 mb-4">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500/20 to-yellow-500/5 border border-yellow-500/30 flex items-center justify-center text-[#E4B15B] text-xl shrink-0">
            <i class="fa-solid ${v.icon || 'fa-briefcase'}"></i>
          </div>
          ${v.salary ? `<span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-2.5 py-1 shrink-0">${v.salary}</span>` : ''}
        </div>
        <h3 class="text-lg font-bold text-white mb-1 leading-snug">${v.title || ''}</h3>
        ${v.tagline ? `<p class="text-sm text-yellow-400/80 mb-4">${v.tagline}</p>` : ''}
        <div class="flex flex-wrap gap-2 mb-4">
          ${v.location ? `<span class="text-[11px] text-slate-300 bg-slate-800/70 border border-slate-700 rounded-lg px-2.5 py-1 flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-[#E4B15B] text-[10px]"></i>${v.location}</span>` : ''}
          ${tLabel ? `<span class="text-[11px] text-slate-300 bg-slate-800/70 border border-slate-700 rounded-lg px-2.5 py-1 flex items-center gap-1.5"><i class="fa-solid fa-clock text-[#E4B15B] text-[10px]"></i>${tLabel}</span>` : ''}
        </div>
        ${reqsHtml}
        <div class="mt-5 pt-4 border-t border-slate-800/70">
          <button onclick="applyForJob('${(v.title || '').replace(/'/g, "\\'")}')" class="w-full px-5 py-2.5 rounded-xl gold-gradient text-[#0B132B] text-xs font-bold shadow-lg hover:shadow-yellow-500/25 transition-all flex items-center justify-center gap-2 group-hover:scale-[1.02]">
            <i class="fa-solid fa-paper-plane text-[11px]"></i>
            <span>${applyBtn}</span>
          </button>
        </div>
      </div>
    `;
  }).join('');
}

/**
 * Scroll to form and prefill the position field
 */
function applyForJob(title) {
  const posInput = document.getElementById('appPosition');
  if (posInput && title) posInput.value = title;
  const appSection = document.getElementById('application');
  if (appSection) {
    appSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(() => { if (posInput) posInput.focus(); }, 600);
  }
}

/**
 * Main careers dynamic renderer (called from content.js after load)
 */
window.renderCareersDynamic = function () {
  applyTranslations();
  renderWhyJoinGrid();
  renderVacanciesGrid();
};

/**
 * Switch language between 'ar' and 'en'
 */
function setCareersLanguage(lang) {
  if (!translations[lang]) return;
  currentLanguage = lang;
  localStorage.setItem('delton_lang', lang);
  applyTranslations();
  renderWhyJoinGrid();
  renderVacanciesGrid();
}

/**
 * Fill phone / email / footer from admin-managed data (mirror of main.js)
 */
function renderCareersDynamicContact() {
  applyTranslations();
}

/**
 * Sticky navbar + back-to-top
 */
function setupNavbarScroll() {
  const navbar = document.getElementById('mainNavbar');
  const backToTop = document.getElementById('backToTopBtn');

  window.addEventListener('scroll', () => {
    if (navbar) {
      navbar.classList.toggle('navbar-scrolled', window.scrollY > 60);
    }
    if (backToTop) {
      backToTop.classList.toggle('show', window.scrollY > 450);
    }
  }, { passive: true });

  if (backToTop) {
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }
}

/**
 * Mobile navigation drawer
 */
function setupMobileMenu() {
  const toggleBtn = document.getElementById('mobileMenuToggle');
  const closeBtn = document.getElementById('mobileMenuClose');
  const drawer = document.getElementById('mobileDrawer');
  const backdrop = document.getElementById('mobileDrawerBackdrop');
  const links = document.querySelectorAll('.mobile-nav-link');

  const openDrawer = () => {
    if (drawer) drawer.classList.add('active');
    if (backdrop) backdrop.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  };

  const closeDrawer = () => {
    if (drawer) drawer.classList.remove('active');
    if (backdrop) backdrop.classList.add('hidden');
    document.body.style.overflow = '';
  };

  if (toggleBtn) toggleBtn.addEventListener('click', openDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
  if (backdrop) backdrop.addEventListener('click', closeDrawer);
  links.forEach(l => l.addEventListener('click', closeDrawer));
}

/**
 * Toast helper
 */
function showToast(type, title, message) {
  const toast = document.getElementById('formToast');
  const titleEl = document.getElementById('formToastTitle');
  const msgEl = document.getElementById('formToastMsg');
  if (!toast) return;

  const isSuccess = type === 'success';
  titleEl.textContent = title;
  msgEl.textContent = message;
  toast.className = isSuccess
    ? 'flex items-start gap-3 p-5 rounded-2xl bg-emerald-500/15 border border-emerald-500/40'
    : 'flex items-start gap-3 p-5 rounded-2xl bg-red-500/15 border border-red-500/40';
  toast.classList.remove('hidden');
  toast.querySelector('i.fa-solid')?.classList.remove('fa-circle-check', 'fa-triangle-exclamation');
  toast.querySelector('i.fa-solid')?.classList.add(isSuccess ? 'fa-circle-check' : 'fa-triangle-exclamation');
  toast.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  setTimeout(() => toast.classList.add('hidden'), 12000);
}

function validateEgyptianPhone(phone) {
  return /^01[0125][0-9]{8}$/.test(String(phone || '').trim());
}

function validateEmailStrict(email) {
  const em = String(email || '').trim().toLowerCase();
  if (em.length > 254) return false;
  if (/\s/.test(em)) return false;
  return /^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/.test(em);
}

function isPdfFile(file) {
  if (!file) return true;
  const name = String(file.name || '').toLowerCase();
  const mime = String(file.type || '').toLowerCase();
  return name.endsWith('.pdf') && (mime === '' || mime === 'application/pdf');
}

function setupCvUploadUI() {
  const fileInput = document.getElementById('appCv');
  const trigger = document.getElementById('cvUploadTrigger');
  const nameEl = document.getElementById('cvFileName');
  const clearBtn = document.getElementById('cvClearBtn');
  const errEl = document.getElementById('cvError');
  if (!fileInput || !trigger) return;

  const t = translations[currentLanguage] || {};
  const cForm = (t.careers && t.careers.form) ? t.careers.form : {};
  const defaultPlace = cForm.cvPlaceholderText || 'انقر لاختيار ملف CV.pdf';
  const errPdf = cForm.cvPdfError || 'يجب رفع ملف بصيغة PDF فقط. غير ذلك لن يتم قبول السيرة الذاتية.';
  const errSize = cForm.cvSizeError || 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت فقط.';

  if (nameEl && !nameEl.textContent.trim()) nameEl.textContent = defaultPlace;

  function showErr(msg) {
    if (!errEl) return;
    const s = errEl.querySelector('span');
    if (s) s.textContent = msg;
    errEl.classList.remove('hidden');
  }
  function clearErr() { if (errEl) errEl.classList.add('hidden'); }

  trigger.addEventListener('click', (e) => {
    if (e.target.closest('#cvClearBtn')) return;
    fileInput.click();
  });
  if (clearBtn) {
    clearBtn.addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation();
      fileInput.value = '';
      if (nameEl) nameEl.textContent = defaultPlace;
      clearBtn.classList.add('hidden');
      clearErr();
    });
  }
  fileInput.addEventListener('change', () => {
    clearErr();
    const f = fileInput.files && fileInput.files[0];
    if (!f) {
      if (nameEl) nameEl.textContent = defaultPlace;
      if (clearBtn) clearBtn.classList.add('hidden');
      return;
    }
    if (!isPdfFile(f)) {
      fileInput.value = '';
      if (nameEl) nameEl.textContent = defaultPlace;
      if (clearBtn) clearBtn.classList.add('hidden');
      showErr(errPdf);
      return;
    }
    if (f.size > 5 * 1024 * 1024) {
      fileInput.value = '';
      if (nameEl) nameEl.textContent = defaultPlace;
      if (clearBtn) clearBtn.classList.add('hidden');
      showErr(errSize);
      return;
    }
    if (nameEl) nameEl.textContent = f.name;
    if (clearBtn) clearBtn.classList.remove('hidden');
  });
}

/**
 * Handle the job application form submit
 */
function setupApplicationForm() {
  const form = document.getElementById('deltonApplicationForm');
  if (!form) return;

  setupCvUploadUI();

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const t = translations[currentLanguage] || {};
    const cf = (t.careers && t.careers.form) || {};
    const name = document.getElementById('appName')?.value.trim();
    const phone = document.getElementById('appPhone')?.value.trim();
    const email = document.getElementById('appEmail')?.value.trim();
    const position = document.getElementById('appPosition')?.value.trim();
    const city = document.getElementById('appCity')?.value.trim();
    const cv = document.getElementById('appCv')?.files[0];

    if (!name || !phone || !email || !position || !city) {
      showToast('error', (cf.errorTitle || ''), (cf.requiredError || 'الرجاء تعبئة جميع الحقول المطلوبة.'));
      return;
    }

    if (!validateEgyptianPhone(phone)) {
      showToast('error', (cf.errorTitle || 'خطأ في البيانات'),
        (currentLanguage === 'ar')
          ? 'رقم التليفون غير صحيح. يجب أن يكون 11 رقماً مصرياً ويبدأ بـ 010 / 011 / 012 / 015.'
          : 'Invalid Egyptian phone. Must be 11 digits starting with 010/011/012/015.');
      document.getElementById('appPhone')?.focus();
      return;
    }

    if (!validateEmailStrict(email)) {
      showToast('error', (cf.errorTitle || 'خطأ في البيانات'),
        (currentLanguage === 'ar')
          ? 'البريد الإلكتروني غير صحيح. يرجى التأكد من وجود علامة @ والنطاق (مثال: name@company.com).'
          : 'Invalid email format. Must contain @ and a valid domain.');
      document.getElementById('appEmail')?.focus();
      return;
    }

    if (cv) {
      if (!isPdfFile(cv)) {
        showToast('error', (cf.errorTitle || ''),
          (currentLanguage === 'ar')
            ? 'صيغة الملف غير مسموحة. السيرة الذاتية يجب أن تكون بصيغة PDF فقط.'
            : 'Only PDF files are accepted for the CV.');
        return;
      }
      if (cv.size > 5 * 1024 * 1024) {
        showToast('error', (cf.errorTitle || ''), t.careers.fileError || '');
        return;
      }
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin ml-2"></i> ${cf.submitting || ''}`;
    }

    const fd = new FormData(form);
    const lang = currentLanguage;

    fetch('assets/php/apply.php', { method: 'POST', body: fd })
      .then(res => res.json())
      .then(data => {
        if (data && data.ok) {
          form.reset();
          const expReset = document.getElementById('expSelect');
          if (expReset) expReset.selectedIndex = 0;
          const clearCv = document.getElementById('cvClearBtn');
          const cvName = document.getElementById('cvFileName');
          if (clearCv) clearCv.classList.add('hidden');
          if (cvName) cvName.textContent = 'انقر لاختيار ملف CV.pdf';
          showToast('success', cf.successTitle || '', cf.successMsg || '');
        } else {
          let msg = cf.errorMsg || '';
          if (data && data.code) {
            if (data.code === 'phone') {
              msg = (currentLanguage === 'ar')
                ? 'رقم التليفون غير صحيح (11 رقماً مصرياً - 010/011/012/015).'
                : 'Invalid Egyptian phone (11 digits, 010/011/012/015 prefix).';
            } else if (data.code === 'email') {
              msg = (currentLanguage === 'ar')
                ? 'البريد الإلكتروني غير صحيح.'
                : 'Invalid email address.';
            } else if (data.code === 'cv_pdf') {
              msg = (currentLanguage === 'ar')
                ? 'السيرة الذاتية يجب أن تكون بصيغة PDF فقط وبحد أقصى 5 ميجابايت.'
                : 'CV must be a valid PDF file (max 5 MB).';
            } else if (data.code === 'rate_limited') {
              msg = (currentLanguage === 'ar')
                ? 'لقد تجاوزت الحد المسموح به من الطلبات. حاول مرة أخرى بعد دقيقة.'
                : 'Too many submissions. Please try again in a minute.';
            }
          }
          showToast('error', cf.errorTitle || '', msg);
        }
      })
      .catch(() => {
        showToast('error', cf.errorTitle || '', cf.errorMsg || 'حدث خطأ أثناء الإرسال. حاول مرة أخرى بعد قليل.');
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>${cf.submitBtn || ''}</span> <i class="fa-solid fa-paper-plane text-sm"></i>`;
        }
      });
  });
}

/**
 * Initialization
 */
document.addEventListener('DOMContentLoaded', async () => {
  if (window.ContentManager && window.ContentManager.ready) {
    await window.ContentManager.ready;
  }

  applyTranslations();
  renderWhyJoinGrid();
  renderVacanciesGrid();
  setupNavbarScroll();
  setupMobileMenu();
  setupApplicationForm();

  // Language toggle triggers
  const toggleLangHandler = () => {
    const next = currentLanguage === 'ar' ? 'en' : 'ar';
    setCareersLanguage(next);
  };
  const langToggleBtn = document.getElementById('langToggleBtn');
  const langToggleBtnMobile = document.getElementById('langToggleBtnMobile');
  if (langToggleBtn) langToggleBtn.addEventListener('click', toggleLangHandler);
  if (langToggleBtnMobile) langToggleBtnMobile.addEventListener('click', toggleLangHandler);
});