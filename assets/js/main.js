/**
 * Delton Facility Management - Core Application Script
 * Bilingual Controller, Sticky Navbar, Services Modal, Client Filtering, Form Handling
 */

let currentLanguage = localStorage.getItem('delton_lang') || 'ar';

// Official Client Roster
const clientData = [
  // Banking Sector
  { id: 'cbe', sector: 'banking', code: 'cbe', ar: 'البنك المركزي المصري', en: 'Central Bank of Egypt' },
  { id: 'aaib', sector: 'banking', code: 'aaib', ar: 'البنك العربي الأفريقي الدولي', en: 'Arab African Int. Bank' },
  { id: 'albaraka', sector: 'banking', code: 'albaraka', ar: 'بنك البركة مصر', en: 'Al Baraka Bank Egypt' },
  { id: 'wu', sector: 'banking', code: 'wu', ar: 'ويسترن يونيون (IPAG)', en: 'Western Union IPAG' },
  { id: 'cib-brokerage', sector: 'banking', code: 'cib-brokerage', ar: 'شركة التجاري الدولي للسمسرة', en: 'CIB Brokerage' },
  { id: 'cib-underwriting', sector: 'banking', code: 'cib-underwriting', ar: 'التجاري الدولي لترويج وتغطية الاكتتاب', en: 'CIB Promotion & Underwriting' },
  { id: 'misr-capital', sector: 'banking', code: 'misr-capital', ar: 'شركة مصر كابيتال للوساطة', en: 'Misr Capital for Brokerage' },
  
  // Corporate & Institutions Sector
  { id: 'supreme-court', sector: 'corporate', code: 'supreme-court', ar: 'المحكمة الدستورية العليا', en: 'Supreme Constitutional Court' },
  { id: 'ncmp', sector: 'corporate', code: 'ncmp', ar: 'الشركة الوطنية لمنتجات الذرة (NCMP)', en: 'National Maize Products (NCMP)' },
  { id: 'nasr-auto', sector: 'corporate', code: 'nasr-auto', ar: 'شركة النصر لصناعة السيارات', en: 'El Nasr Automotive Manufacturing' },
  { id: 'elsewedy', sector: 'corporate', code: 'elsewedy', ar: 'شركة السويدي إنرجيا للكابلات', en: 'Elsewedy Energia Cables' },
  { id: 'metallurgical', sector: 'corporate', code: 'metallurgical', ar: 'الشركة القابضة للصناعات المعدنية', en: 'Holding Co. for Metallurgical Ind.' },
  { id: 'waterway', sector: 'corporate', code: 'waterway', ar: 'مجموعة واتر واي (The Waterway)', en: 'The Waterway Developments' },
  { id: 'trust-petroleum', sector: 'corporate', code: 'trust-petroleum', ar: 'شركة تراست بتروليوم سيرفيس', en: 'Trust Petroleum Service' },
  { id: 'pioneer', sector: 'corporate', code: 'pioneer', ar: 'شركة بايونير (Pioneer)', en: 'Pioneer Company' },
  { id: 'hbs', sector: 'corporate', code: 'hbs', ar: 'هادي بوشمان للخدمات البترولية (HBS)', en: 'Hadi Bushman Petroleum (HBS)' },
  { id: 'cairo3', sector: 'corporate', code: 'cairo3', ar: 'شركة كايرو 3 (Cairo 3)', en: 'Cairo 3 Company' },
  { id: 'wally-auto', sector: 'corporate', code: 'wally-auto', ar: 'شركة والي أوتو (Wally Auto)', en: 'Wally Auto Group' }
];

const clientImageMap = {
  aaib: 'assets/images/clientLogo/arab african international bank.png',
  albaraka: 'assets/images/clientLogo/bank Elbarka.svg',
  cairo3: 'assets/images/clientLogo/CAIRO 3A.jpg',
  ncmp: 'assets/images/clientLogo/NCMP.jpg',
  'wally-auto': 'assets/images/clientLogo/wallyauto.jpg',
  'nasr-auto': 'assets/images/clientLogo/نصر.jpg'
};

function resolveClientImage(client) {
  const preferred = clientImageMap[client.id];
  if (preferred) return preferred;

  const primary = `assets/images/clients/${client.code}.svg`;
  const fallback = 'assets/images/clients/client-fallback.svg';
  return `${primary}?v=${Date.now()}`;
}

/**
 * Switch Language between 'ar' and 'en'
 */
function setLanguage(lang) {
  if (!translations[lang]) return;
  currentLanguage = lang;
  localStorage.setItem('delton_lang', lang);

  const isRtl = lang === 'ar';
  document.documentElement.dir = isRtl ? 'rtl' : 'ltr';
  document.documentElement.lang = lang;

  // Toggle body font classes
  if (isRtl) {
    document.body.classList.remove('font-en');
    document.body.classList.add('font-ar');
  } else {
    document.body.classList.remove('font-ar');
    document.body.classList.add('font-en');
  }

  // Update Page Title and Meta
  document.title = translations[lang].meta.title;
  const metaDesc = document.querySelector('meta[name="description"]');
  if (metaDesc) metaDesc.setAttribute('content', translations[lang].meta.desc);

  // Update text for all data-i18n elements
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    const val = getNestedTranslation(translations[lang], key);
    if (val !== undefined) {
      el.textContent = val;
    }
  });

  // Update placeholders for inputs
  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    const val = getNestedTranslation(translations[lang], key);
    if (val !== undefined) {
      el.setAttribute('placeholder', val);
    }
  });

  // Update Profile Download Links
  const profileDownloadBtn = document.getElementById('profileDownloadLink');
  if (profileDownloadBtn) {
    profileDownloadBtn.href = isRtl ? 'Delton-profile ar 2026.pdf' : 'Delton-profile EN 2026.pdf';
    profileDownloadBtn.setAttribute('download', isRtl ? 'Delton-profile ar 2026.pdf' : 'Delton-profile EN 2026.pdf');
  }

  // Re-render Services Cards
  renderServicesSection(lang);

  // Re-render Why Us Cards
  renderWhyUsSection(lang);

  // Re-render Clients Cards
  renderClientsSection(lang, currentClientFilter);

  // Update Slider
  if (window.updateSliderLanguage) {
    window.updateSliderLanguage(lang);
  }

  // Update Lang toggle button text
  const langToggleBtn = document.getElementById('langToggleBtn');
  const langToggleBtnMobile = document.getElementById('langToggleBtnMobile');
  const toggleText = translations[lang].nav.langToggle;
  if (langToggleBtn) langToggleBtn.innerHTML = `<i class="fa-solid fa-globe text-yellow-500 mr-1.5 ml-1.5"></i> ${toggleText}`;
  if (langToggleBtnMobile) langToggleBtnMobile.innerHTML = `<i class="fa-solid fa-globe text-yellow-500 mr-1.5 ml-1.5"></i> ${toggleText}`;

  // Refresh AOS if available
  if (typeof AOS !== 'undefined') {
    AOS.refresh();
  }
}

/**
 * Helper to fetch nested keys like "nav.home"
 */
function getNestedTranslation(obj, path) {
  return path.split('.').reduce((prev, curr) => prev ? prev[curr] : undefined, obj);
}

/**
 * Render Services Grid
 */
function renderServicesSection(lang) {
  const container = document.getElementById('servicesGrid');
  if (!container) return;

  const sData = translations[lang].services;
  const isRtl = lang === 'ar';

  container.innerHTML = sData.items.map((srv, idx) => `
    <div class="service-card p-6 sm:p-8 flex flex-col justify-between" data-aos="fade-up" data-aos-delay="${idx * 100}">
      <div>
        <!-- Service Header & Icon -->
        <div class="flex items-center justify-between mb-6">
          <div class="w-14 h-14 rounded-2xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center text-[#C9A227] text-2xl shadow-lg shadow-yellow-500/10">
            <i class="fa-solid ${srv.icon}"></i>
          </div>
          <span class="text-xs font-bold text-slate-400 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">
            0${idx + 1}
          </span>
        </div>

        <!-- Service Image Thumbnail -->
        <div class="w-full h-44 rounded-xl overflow-hidden mb-6 border border-slate-800 relative group">
          <img src="${srv.image}" alt="${srv.title}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-[#0B132B] via-transparent to-transparent opacity-60"></div>
        </div>

        <!-- Service Title -->
        <h3 class="text-xl font-bold text-white mb-3 hover:text-[#C9A227] transition">
          ${srv.title}
        </h3>

        <!-- Short Description -->
        <p class="text-slate-300 text-sm leading-relaxed mb-6">
          ${srv.shortDesc}
        </p>
      </div>

      <!-- Action Button -->
      <button onclick="openServiceModal('${srv.id}')" 
              class="w-full py-3 px-4 rounded-xl font-semibold bg-slate-800 hover:bg-[#C9A227] text-slate-200 hover:text-[#0B132B] border border-slate-700 hover:border-[#C9A227] transition duration-300 flex items-center justify-center gap-2 group">
        <span>${sData.viewDetails}</span>
        <i class="fa-solid ${isRtl ? 'fa-arrow-left' : 'fa-arrow-right'} text-xs transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition"></i>
      </button>
    </div>
  `).join('');
}

/**
 * Open Service Interactive Modal
 */
function openServiceModal(serviceId) {
  const sData = translations[currentLanguage].services;
  const service = sData.items.find(s => s.id === serviceId);
  if (!service) return;

  const isRtl = currentLanguage === 'ar';
  const modal = document.getElementById('serviceModal');
  const modalBody = document.getElementById('serviceModalContent');

  modalBody.innerHTML = `
    <!-- Header -->
    <div class="flex items-start justify-between pb-4 border-b border-slate-700/60">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-yellow-500/15 border border-yellow-500/40 flex items-center justify-center text-[#C9A227] text-2xl">
          <i class="fa-solid ${service.icon}"></i>
        </div>
        <div>
          <span class="text-xs uppercase tracking-wider text-[#C9A227] font-bold">DELTON FM SOLUTION</span>
          <h2 class="text-xl sm:text-2xl font-bold text-white">${service.modalTitle || service.title}</h2>
        </div>
      </div>
      <button onclick="closeServiceModal()" class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 flex items-center justify-center transition">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Image banner -->
    <div class="w-full h-52 sm:h-64 rounded-xl overflow-hidden mt-6 border border-slate-700 relative">
      <img src="${service.image}" alt="${service.title}" class="w-full h-full object-cover">
    </div>

    <!-- Overview -->
    <div class="mt-6">
      <h4 class="text-base font-bold text-[#C9A227] mb-2 flex items-center gap-2">
        <i class="fa-solid fa-circle-info"></i>
        <span>${isRtl ? 'نطاق الخدمة والمعايير التشغيلية:' : 'Operational Scope & Standards:'}</span>
      </h4>
      <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
        ${service.overview}
      </p>
    </div>

    <!-- Key Tasks -->
    <div class="mt-6">
      <h4 class="text-base font-bold text-white mb-3 flex items-center gap-2">
        <i class="fa-solid fa-list-check text-[#C9A227]"></i>
        <span>${isRtl ? 'أبرز المهام والخدمات الفرعية التي نغطيها:' : 'Key Sub-Tasks & Operations Covered:'}</span>
      </h4>
      <ul class="space-y-2.5">
        ${service.tasks.map(t => `
          <li class="flex items-start gap-3 text-slate-300 text-sm sm:text-base">
            <span class="w-5 h-5 rounded-full bg-yellow-500/20 text-[#C9A227] flex items-center justify-center text-xs mt-0.5 shrink-0">
              <i class="fa-solid fa-check"></i>
            </span>
            <span>${t}</span>
          </li>
        `).join('')}
      </ul>
    </div>

    <!-- Specialized Equipment Badge -->
    <div class="mt-6 p-4 rounded-xl bg-slate-800/60 border border-slate-700/80 flex items-start gap-3">
      <i class="fa-solid fa-shield-halved text-[#C9A227] text-xl mt-0.5"></i>
      <div>
        <h5 class="text-sm font-bold text-white mb-1">
          ${isRtl ? 'المعدات والخامات المعتمدة:' : 'Accredited Equipment & Materials:'}
        </h5>
        <p class="text-xs sm:text-sm text-slate-300">${service.equipment}</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="mt-8 pt-4 border-t border-slate-700/60 flex flex-wrap items-center justify-between gap-4">
      <button onclick="closeServiceModal()" class="px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition">
        ${sData.closeModal}
      </button>

      <button onclick="selectServiceAndScroll('${service.id}')" class="px-7 py-2.5 rounded-xl bg-gradient-gold text-[#0B132B] font-bold text-sm hover:shadow-lg hover:shadow-yellow-500/20 transition flex items-center gap-2">
        <span>${sData.requestService}</span>
        <i class="fa-solid ${isRtl ? 'fa-arrow-left' : 'fa-arrow-right'} text-xs"></i>
      </button>
    </div>
  `;

  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

/**
 * Close Service Modal
 */
function closeServiceModal() {
  const modal = document.getElementById('serviceModal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }
}

/**
 * Select service from modal and scroll to contact form
 */
function selectServiceAndScroll(serviceId) {
  closeServiceModal();
  const selectElem = document.getElementById('contactServiceSelect');
  if (selectElem) {
    // Map serviceId to select option value
    selectElem.value = serviceId;
  }
  const contactSection = document.getElementById('contact');
  if (contactSection) {
    contactSection.scrollIntoView({ behavior: 'smooth' });
    setTimeout(() => {
      const msgInput = document.getElementById('contactMessage');
      if (msgInput) msgInput.focus();
    }, 600);
  }
}

/**
 * Render Why Us Section
 */
function renderWhyUsSection(lang) {
  const container = document.getElementById('whyUsGrid');
  if (!container) return;

  const wData = translations[lang].whyUs;
  container.innerHTML = wData.cards.map((card, idx) => `
    <div class="p-6 sm:p-8 rounded-2xl bg-[#111C38] border border-[#1F3059] hover:border-[#C9A227] transition duration-300 hover:-translate-y-1.5 shadow-xl group" data-aos="fade-up" data-aos-delay="${idx * 80}">
      <div class="w-14 h-14 rounded-2xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center text-[#C9A227] text-2xl mb-6 group-hover:scale-110 transition duration-300 shadow-lg shadow-yellow-500/10">
        <i class="fa-solid ${card.icon}"></i>
      </div>
      <h3 class="text-lg sm:text-xl font-bold text-white mb-3 group-hover:text-[#C9A227] transition">
        ${card.title}
      </h3>
      <p class="text-slate-300 text-sm leading-relaxed">
        ${card.desc}
      </p>
    </div>
  `).join('');
}

/**
 * Render Clients Section with Filter
 */
let currentClientFilter = 'all';

function renderClientsSection(lang, filter = 'all') {
  currentClientFilter = filter;
  const container = document.getElementById('clientsGrid');
  if (!container) return;

  const isRtl = lang === 'ar';
  const filtered = clientData.filter(c => filter === 'all' || c.sector === filter);

  container.innerHTML = filtered.map((c, idx) => `
    <div class="client-card p-4 sm:p-5 flex flex-col justify-between items-center text-center group cursor-pointer" data-aos="fade-up" data-aos-delay="${(idx % 6) * 60}">
      <div class="w-full h-24 mb-3 flex items-center justify-center">
        <img src="${resolveClientImage(c)}" alt="${isRtl ? c.ar : c.en}" class="w-full h-full object-contain filter group-hover:brightness-110 transition duration-300" loading="lazy" onerror="this.onerror=null;this.src='assets/images/clients/client-fallback.svg';">
      </div>
      <h4 class="text-sm font-bold text-white group-hover:text-[#C9A227] transition leading-snug">
        ${isRtl ? c.ar : c.en}
      </h4>
      <span class="text-[11px] text-slate-400 mt-1 uppercase tracking-wider font-semibold">
        ${c.sector === 'banking' ? (isRtl ? 'قطاع مصرفي ومالي' : 'Banking & Finance') : (isRtl ? 'مؤسسات كبرى وصناعية' : 'Corporate & Industrial')}
      </span>
    </div>
  `).join('');

  // Update filter buttons styling
  document.querySelectorAll('.client-filter-btn').forEach(btn => {
    const f = btn.getAttribute('data-filter');
    if (f === filter) {
      btn.classList.add('bg-gradient-gold', 'text-[#0B132B]', 'font-bold', 'shadow-lg');
      btn.classList.remove('bg-slate-800', 'text-slate-300');
    } else {
      btn.classList.remove('bg-gradient-gold', 'text-[#0B132B]', 'font-bold', 'shadow-lg');
      btn.classList.add('bg-slate-800', 'text-slate-300');
    }
  });
}

/**
 * Filter client button click handler
 */
function setClientFilter(filter) {
  renderClientsSection(currentLanguage, filter);
}

/**
 * Handle Contact Form Submit
 */
function setupContactForm() {
  const form = document.getElementById('deltonContactForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const name = document.getElementById('contactName')?.value.trim();
    const company = document.getElementById('contactCompany')?.value.trim();
    const phone = document.getElementById('contactPhone')?.value.trim();
    const email = document.getElementById('contactEmail')?.value.trim();
    const service = document.getElementById('contactServiceSelect')?.value;
    const message = document.getElementById('contactMessage')?.value.trim();

    const toast = document.getElementById('formToast');
    const toastMsg = document.getElementById('formToastMsg');

    if (!name || !phone || !message) {
      if (toast && toastMsg) {
        toastMsg.textContent = translations[currentLanguage].contact.form.errorMsg;
        toast.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-200 text-sm block animate-bounce';
        toast.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i> ${translations[currentLanguage].contact.form.submitting}`;
    }

    // Simulate sending
    setTimeout(() => {
      if (toast && toastMsg) {
        toastMsg.textContent = translations[currentLanguage].contact.form.successMsg;
        toast.className = 'p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 text-sm block';
        toast.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }

      form.reset();
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span>${translations[currentLanguage].contact.form.submitBtn}</span> <i class="fa-solid fa-paper-plane text-sm"></i>`;
      }
    }, 1000);
  });
}

/**
 * Sticky Navbar & Back-to-Top Setup
 */
function setupNavbarScroll() {
  const navbar = document.getElementById('mainNavbar');
  const backToTop = document.getElementById('backToTopBtn');

  window.addEventListener('scroll', () => {
    if (window.scrollY > 60) {
      if (navbar) navbar.classList.add('navbar-scrolled');
    } else {
      if (navbar) navbar.classList.remove('navbar-scrolled');
    }

    if (window.scrollY > 450) {
      if (backToTop) backToTop.classList.add('show');
    } else {
      if (backToTop) backToTop.classList.remove('show');
    }
  });

  if (backToTop) {
    backToTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
}

/**
 * Mobile Navigation Drawer Toggle
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
 * Global Keyboard & Modal Listeners
 */
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeServiceModal();
  }
});

/**
 * App Initialization
 */
document.addEventListener('DOMContentLoaded', () => {
  setLanguage(currentLanguage);
  setupNavbarScroll();
  setupMobileMenu();
  setupContactForm();

  // Language switch triggers
  const langToggleBtn = document.getElementById('langToggleBtn');
  const langToggleBtnMobile = document.getElementById('langToggleBtnMobile');

  const toggleLangHandler = () => {
    const nextLang = currentLanguage === 'ar' ? 'en' : 'ar';
    setLanguage(nextLang);
  };

  if (langToggleBtn) langToggleBtn.addEventListener('click', toggleLangHandler);
  if (langToggleBtnMobile) langToggleBtnMobile.addEventListener('click', toggleLangHandler);

  // Initialize AOS
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 800,
      once: true,
      offset: 60,
      easing: 'ease-out-cubic'
    });
  }
});
