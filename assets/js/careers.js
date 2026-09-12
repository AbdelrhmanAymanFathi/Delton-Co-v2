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
    el.textContent = email;
    if (el.tagName === 'A') el.setAttribute('href', 'mailto:' + email);
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
  if (footerAddress && window.__FooterAddress && window.__FooterAddress[isRtl ? 'ar' : 'en']) {
    footerAddress.textContent = window.__FooterAddress[isRtl ? 'ar' : 'en'];
  }

  const footerPhones = document.querySelector('[data-dyn-footer-phones]');
  if (footerPhones && window.__FooterPhones) footerPhones.textContent = window.__FooterPhones;

  // Branding logos
  const branding = window.__Branding || {
    navbar: { image: 'newlogo.jpeg', width: '110', height: '52' },
    footer: { image: 'assets/images/logoFooter.png', width: '150', height: '56' }
  };
  document.querySelectorAll('[data-brand-logo]').forEach(el => {
    const role = el.getAttribute('data-brand-logo');
    const cfg = branding[role] || branding.navbar || {};
    el.src = cfg.image || 'newlogo.jpeg';
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
  const rolesDatalist = document.getElementById('jobRolesList');
  if (rolesDatalist && Array.isArray(tCareers.jobRoles)) {
    rolesDatalist.innerHTML = tCareers.jobRoles
      .map(r => `<option value="${r.replace(/"/g, '&quot;')}"></option>`)
      .join('');
  }
  const govsDatalist = document.getElementById('governoratesList');
  if (govsDatalist && Array.isArray(tCareers.governorates)) {
    govsDatalist.innerHTML = tCareers.governorates
      .map(g => `<option value="${g.replace(/"/g, '&quot;')}"></option>`)
      .join('');
  }
  const expSelect = document.getElementById('expSelect');
  if (expSelect && Array.isArray(tCareers.experiences)) {
    expSelect.innerHTML = tCareers.experiences
      .map((e, i) => `<option value="${e}">${e}</option>`)
      .join('');
  }
}

/**
 * Switch language between 'ar' and 'en'
 */
function setCareersLanguage(lang) {
  if (!translations[lang]) return;
  currentLanguage = lang;
  localStorage.setItem('delton_lang', lang);
  applyTranslations();
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

/**
 * Handle the job application form submit
 */
function setupApplicationForm() {
  const form = document.getElementById('deltonApplicationForm');
  if (!form) return;

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
      showToast('error', (cf.errorTitle || ''), (cf.requiredError || ''));
      return;
    }

    if (cv && cv.size > 5 * 1024 * 1024) {
      showToast('error', (cf.errorTitle || ''), t.careers.fileError || '');
      return;
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
          showToast('success', cf.successTitle || '', cf.successMsg || '');
        } else {
          const msg = (data && data.code === 'validation') ? (cf.requiredError || '') : (cf.errorMsg || '');
          showToast('error', cf.errorTitle || '', msg);
        }
      })
      .catch(() => {
        showToast('error', cf.errorTitle || '', cf.errorMsg || '');
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