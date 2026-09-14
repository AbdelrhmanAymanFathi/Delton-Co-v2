/**
 * Delton Facility Management - Core Application Script
 * Bilingual Controller, Sticky Navbar, Services Modal, Client Filtering, Form Handling
 */

let currentLanguage = localStorage.getItem('delton_lang') || 'ar';

// Official Client Roster
let clientData = [
  // Banking Sector
  { id: 'cbe', sector: 'banking', code: 'cbe', ar: 'البنك المركزي المصري', en: 'Central Bank of Egypt' },
  { id: 'aaib', sector: 'banking', code: 'aaib', ar: 'البنك العربي الأفريقي الدولي', en: 'Arab African Int. Bank' },
  { id: 'albaraka', sector: 'banking', code: 'albaraka', ar: 'بنك البركة مصر', en: 'Al Baraka Bank Egypt' },
  { id: 'wu', sector: 'banking', code: 'wu', ar: 'ويسترن يونيون (IPAG)', en: 'Western Union (IPAG)' },
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
  { id: 'trust-petroleum', sector: 'corporate', code: 'trust-petroleum', ar: 'شركة تراست بتروليوم سيرفيس', en: 'Trust Petroleum Services' },
  { id: 'pioneer', sector: 'corporate', code: 'pioneer', ar: 'شركة بايونير (Pioneer)', en: 'Pioneer Holding' },
  { id: 'hbs', sector: 'corporate', code: 'hbs', ar: 'هادي بوشمان للخدمات البترولية (HBS)', en: 'Hadi Bushman Petroleum (HBS)' },
  { id: 'cairo3', sector: 'corporate', code: 'cairo3', ar: 'كايرو ثري إيه (Cairo 3A)', en: 'Cairo 3A Group' },
  { id: 'wally-auto', sector: 'corporate', code: 'wally-auto', ar: 'شركة والي أوتو (Wally Auto)', en: 'Wally Auto Group' },
  { id: 'marakez', sector: 'corporate', code: 'marakez', ar: 'مراكز العقارية (Marakez)', en: 'Marakez Developments' },
  { id: 'auto-samir-rayan', sector: 'corporate', code: 'auto-samir-rayan', ar: 'أوتو سمير ريان (Auto Samir Rayan)', en: 'Auto Samir Rayan' },
  { id: 'lasirena', sector: 'corporate', code: 'lasirena', ar: 'مجموعة لاسيرينا (La Sirena)', en: 'La Sirena Group' },
  { id: 'vezeeta', sector: 'corporate', code: 'vezeeta', ar: 'فيزيتا (Vezeeta)', en: 'Vezeeta' },
  { id: 'pharmacare', sector: 'corporate', code: 'pharmacare', ar: 'فارماكير (Pharmacare)', en: 'Pharmacare' },
  { id: 'ezzeldeen', sector: 'corporate', code: 'ezzeldeen', ar: 'صيدليات عز الدين', en: 'Ezzedeen Pharmacies' },
  { id: 'fas', sector: 'corporate', code: 'fas', ar: 'شركة إف إيه إس (FAS)', en: 'FAS Group' }
];

const clientImageMap = {
  // Banking Sector
  cbe: 'assets/images/clientLogo/cbe.svg',
  aaib: 'assets/images/clientLogo/arab african international bank.png',
  albaraka: 'assets/images/clientLogo/bank Elbarka.svg',
  wu: 'assets/images/clientLogo/wu.svg',
  'cib-brokerage': 'assets/images/clients/ci capital.jpeg',
  'cib-underwriting': 'assets/images/clients/التجاري الدولي لترويج وتغطية الاكتتاب.jpeg',
  'misr-capital': 'assets/images/clientLogo/misr-capital.png',

  // Corporate & Institutions Sector
  'supreme-court': 'assets/images/clientLogo/supreme-court.png',
  ncmp: 'assets/images/clientLogo/NCMP.jpg',
  'nasr-auto': 'assets/images/clientLogo/نصر.jpg',
  elsewedy: 'assets/images/clientLogo/elsewedy.svg',
  metallurgical: 'assets/images/clientLogo/metallurgical.png',
  waterway: 'assets/images/clientLogo/waterway.png',
  'trust-petroleum': 'assets/images/clientLogo/trust-petroleum.png',
  pioneer: 'assets/images/clientLogo/pioneer.png',
  hbs: 'assets/images/clientLogo/hbs.jpg',
  cairo3: 'assets/images/clientLogo/CAIRO 3A.jpg',
  'wally-auto': 'assets/images/clientLogo/wallyauto.jpg',
  marakez: 'assets/images/clientLogo/Marakez.jpg',
  'auto-samir-rayan': 'assets/images/clientLogo/Auto Samir Rayan.jpg',
  lasirena: 'assets/images/clientLogo/lasirena.jpg',
  vezeeta: 'assets/images/clientLogo/vezeeta.jpg',
  pharmacare: 'assets/images/clientLogo/pharmacare.jpg',
  ezzeldeen: 'assets/images/clientLogo/صيدليات عز الدين.jpg',
  fas: 'assets/images/clientLogo/FAS.jpg'
};

function resolveClientImage(client) {
  if (client.image) return client.image;

  const preferred = clientImageMap[client.id];
  if (preferred) return preferred;

  return 'assets/images/clientLogo/client-fallback.svg';
}

/**
 * Apply client list overrides loaded from admin/content.json
 */
function applyClientOverrides(overrides) {
  if (!Array.isArray(overrides) || !overrides.length) return;

  clientData = overrides.map(c => ({
    id: c.id || ('client-' + Math.random().toString(36).slice(2, 7)),
    sector: c.sector || 'corporate',
    image: c.image || '',
    ar: (c.ar && c.ar.name) ? c.ar.name : '',
    en: (c.en && c.en.name) ? c.en.name : ''
  }));
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

  // Update title/tooltip attributes
  document.querySelectorAll('[data-i18n-title]').forEach(el => {
    const key = el.getAttribute('data-i18n-title');
    const val = getNestedTranslation(translations[lang], key);
    if (val !== undefined) {
      el.setAttribute('title', val);
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

  // Update dynamic contact info + branding + service dropdown options
  renderDynamicContact();
  renderBranding();
  renderServiceSelectOptions();

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
 * Fill phone / email / footer contact elements from admin-managed data
 */
function normalizeBrandSize(value, fallback) {
  if (value === undefined || value === null || value === '') return fallback + 'px';
  const text = String(value).trim();
  if (/^\d+(\.\d+)?$/.test(text)) return text + 'px';
  return text;
}

function renderBranding() {
  const branding = window.__Branding || {
    navbar: { image: 'assets/images/logo2-removebg-preview.png', width: '124', height: '50' },
    footer: { image: 'assets/images/logo2-removebg-preview.png', width: '160', height: '64' }
  };

  document.querySelectorAll('[data-brand-logo]').forEach(el => {
    const role = el.getAttribute('data-brand-logo');
    const config = branding[role] || branding.navbar || {};
    const src = config.image || 'assets/images/logo2-removebg-preview.png';
    const width = normalizeBrandSize(config.width, role === 'footer' ? 150 : 140);
    const height = normalizeBrandSize(config.height, role === 'footer' ? 56 : 66);

    el.src = src;
    el.style.width = width;
    el.style.height = height;
    el.style.maxWidth = '100%';
    el.style.objectFit = 'contain';
  });
}

function renderDynamicContact() {
  const phones = (window.__ContactPhones && window.__ContactPhones.length)
    ? window.__ContactPhones
    : ['01102668966', '01123544717', '01123545516'];
  const email = window.__ContactEmail || 'info@delton-eg.com';
  const isRtl = currentLanguage === 'ar';

  document.querySelectorAll('[data-dyn-phone]').forEach(el => {
    const idx = parseInt(el.getAttribute('data-dyn-phone'), 10) || 0;
    const p = phones[idx] || phones[0] || '';
    el.textContent = p;
    if (el.tagName === 'A') el.setAttribute('href', 'tel:' + p);
  });

  document.querySelectorAll('[data-dyn-email]').forEach(el => {
    el.textContent = email;
    if (el.tagName === 'A') el.setAttribute('href', 'mailto:' + email);
  });

  const defaultFooterAddress = {
    ar: '2120 شارع الأرقم، المعراج، زهراء المعادي، القاهرة',
    en: '2120 St Elarqam, El Meraag, Zahraa Elmaadi, Cairo, Egypt'
  };
  const footerAddressData = (window.__FooterAddress && (window.__FooterAddress.ar || window.__FooterAddress.en))
    ? window.__FooterAddress
    : defaultFooterAddress;
  const footerAddress = document.querySelector('[data-dyn-footer-address]');
  if (footerAddress) {
    footerAddress.textContent = footerAddressData[isRtl ? 'ar' : 'en'] || footerAddressData.ar || footerAddressData.en || '';
  }

  const footerPhones = document.querySelector('[data-dyn-footer-phones]');
  if (footerPhones && window.__FooterPhones) footerPhones.textContent = window.__FooterPhones;
}

/**
 * Build the contact form service dropdown dynamically from active services
 */
function renderServiceSelectOptions() {
  const sel = document.getElementById('contactServiceSelect');
  if (!sel) return;

  const contact = translations[currentLanguage].contact.form;
  const sData = translations[currentLanguage].services;

  const options = [
    `<option value="" disabled selected>${contact.selectServicePlaceholder}</option>`,
    `<option value="all">${contact.allServicesOpt}</option>`
  ];

  (sData.items || []).forEach(srv => {
    options.push(`<option value="${srv.id}">${srv.title}</option>`);
  });

  sel.innerHTML = options.join('');
}

/**
 * Ash & Ember icon "temperature" system — each service gets its own
 * light/dark ember tint instead of one flat gold, without leaving the
 * gold/ash/ember family. Falls back to the maintenance tone if a service
 * id isn't recognized.
 */
const SERVICE_ACCENTS = {
  maintenance: { light: '#F2C572', dark: '#C23B2E' },  // hot ember
  cleaning:    { light: '#F7ECD8', dark: '#9C99A6' },  // cool steam
  landscaping: { light: '#E4B15B', dark: '#6B5A4A' },  // ember cooling to ash-bronze
  supplies:    { light: '#C7C4CC', dark: '#6E6B76' },  // pure ash-silver
  hospitality: { light: '#F3C9A6', dark: '#C2503B' },  // warm rose-ember
  renovation:  { light: '#D9C9A6', dark: '#6B6258' }   // warm stone/concrete
};

function hexToRgb(hex) {
  const h = hex.replace('#', '');
  return [0, 2, 4].map(i => parseInt(h.slice(i, i + 2), 16));
}

function serviceAccentStyle(serviceId, { size = '2xl' } = {}) {
  const acc = SERVICE_ACCENTS[serviceId] || SERVICE_ACCENTS.maintenance;
  const [lr, lg, lb] = hexToRgb(acc.light);
  const [dr, dg, db] = hexToRgb(acc.dark);
  return `background:linear-gradient(135deg, rgba(${lr},${lg},${lb},.18), rgba(${dr},${dg},${db},.10)); border:1px solid rgba(${dr},${dg},${db},.4); color:${acc.light}; box-shadow:0 8px 20px rgba(${dr},${dg},${db},.18);`;
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
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl transition duration-300" style="${serviceAccentStyle(srv.id)}">
            <i class="fa-solid ${srv.icon}"></i>
          </div>
          <span class="text-xs font-bold text-slate-400 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">
            0${idx + 1}
          </span>
        </div>

        <!-- Service Image Thumbnail -->
        <div class="w-full h-44 rounded-xl overflow-hidden mb-6 border border-slate-800 relative group">
          <img src="${srv.image}" alt="${srv.title}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-[#0E0C10] via-transparent to-transparent opacity-60"></div>
        </div>

        <!-- Service Title -->
        <h3 class="text-xl font-bold text-white mb-3 hover:text-[#E4B15B] transition">
          ${srv.title}
        </h3>

        <!-- Short Description -->
        <p class="text-slate-300 text-sm leading-relaxed mb-6">
          ${srv.shortDesc}
        </p>
      </div>

      <!-- Action Button -->
      <button onclick="openServiceModal('${srv.id}')" 
              class="w-full py-3 px-4 rounded-xl font-semibold bg-slate-800 hover:bg-[#E4B15B] text-slate-200 hover:text-[#0E0C10] border border-slate-700 hover:border-[#E4B15B] transition duration-300 flex items-center justify-center gap-2 group">
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
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl" style="${serviceAccentStyle(service.id)}">
          <i class="fa-solid ${service.icon}"></i>
        </div>
        <div>
          <span class="text-xs uppercase tracking-wider text-[#E4B15B] font-bold">DELTON FM SOLUTION</span>
          <h2 class="text-xl sm:text-2xl font-bold text-white">${service.modalTitle || service.title}</h2>
        </div>
      </div>
      <button onclick="closeServiceModal()" class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 flex items-center justify-center transition">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Image Gallery -->
    <div class="grid grid-cols-1 ${service.gallery && service.gallery.length > 1 ? 'sm:grid-cols-2' : ''} gap-3 mt-6">
      ${(service.gallery || [service.image]).map(img => `
        <div class="h-44 sm:h-52 rounded-xl overflow-hidden border border-slate-700 relative group">
          <img src="${img}" alt="${service.title}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        </div>
      `).join('')}
    </div>

    <!-- Overview -->
    <div class="mt-6">
      <h4 class="text-base font-bold text-[#E4B15B] mb-2 flex items-center gap-2">
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
        <i class="fa-solid fa-list-check text-[#E4B15B]"></i>
        <span>${isRtl ? 'أبرز المهام والخدمات الفرعية التي نغطيها:' : 'Key Sub-Tasks & Operations Covered:'}</span>
      </h4>
      <ul class="space-y-2.5">
        ${service.tasks.map(t => `
          <li class="flex items-start gap-3 text-slate-300 text-sm sm:text-base">
            <span class="w-5 h-5 rounded-full bg-yellow-500/20 text-[#E4B15B] flex items-center justify-center text-xs mt-0.5 shrink-0">
              <i class="fa-solid fa-check"></i>
            </span>
            <span>${t}</span>
          </li>
        `).join('')}
      </ul>
    </div>

    <!-- Specialized Equipment Badge -->
    <div class="mt-6 p-4 rounded-xl bg-slate-800/60 border border-slate-700/80 flex items-start gap-3">
      <i class="fa-solid fa-shield-halved text-[#E4B15B] text-xl mt-0.5"></i>
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

      <button onclick="selectServiceAndScroll('${service.id}')" class="px-7 py-2.5 rounded-xl bg-gradient-gold text-[#0E0C10] font-bold text-sm hover:shadow-lg hover:shadow-yellow-500/20 transition flex items-center gap-2">
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
    <div class="whyus-card p-6 sm:p-8 rounded-2xl bg-[#18141A] border border-[#2E262C] hover:border-[#E4B15B] transition duration-300 hover:-translate-y-1.5 shadow-xl group" data-aos="fade-up" data-aos-delay="${idx * 80}">
      <div class="w-14 h-14 rounded-2xl bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center text-[#E4B15B] text-2xl mb-6 group-hover:scale-110 transition duration-300 shadow-lg shadow-yellow-500/10">
        <i class="fa-solid ${card.icon}"></i>
      </div>
      <h3 class="text-lg sm:text-xl font-bold text-white mb-3 group-hover:text-[#E4B15B] transition">
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
      <div class="w-full h-24 mb-3 p-3 bg-white rounded-xl shadow-sm flex items-center justify-center overflow-hidden group-hover:scale-[1.02] transition duration-300">
        <img src="${resolveClientImage(c)}" alt="${isRtl ? c.ar : c.en}" class="max-h-full max-w-full object-contain filter transition duration-300" loading="lazy" onerror="this.onerror=null;this.src='assets/images/clientLogo/client-fallback.svg';">
      </div>
      <h4 class="text-sm font-bold text-white group-hover:text-[#E4B15B] transition leading-snug">
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
      btn.classList.add('bg-gradient-gold', 'text-[#0E0C10]', 'font-bold', 'shadow-lg');
      btn.classList.remove('bg-slate-800', 'text-slate-300');
    } else {
      btn.classList.remove('bg-gradient-gold', 'text-[#0E0C10]', 'font-bold', 'shadow-lg');
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

function validateEgyptianPhone(phone) {
  return /^01[0125][0-9]{8}$/.test(String(phone || '').trim());
}

function validateEmailStrict(email) {
  const em = String(email || '').trim().toLowerCase();
  if (em.length > 254) return false;
  if (/\s/.test(em)) return false;
  return /^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/.test(em);
}

/**
 * Handle Contact Form Submit
 */
function setupContactForm() {
  const form = document.getElementById('deltonContactForm');
  if (!form) return;

  // Live-sanitize the phone field: digits only, capped at 11 (Egyptian mobile length)
  const phoneInput = document.getElementById('contactPhone');
  if (phoneInput) {
    phoneInput.addEventListener('input', () => {
      phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '').slice(0, 11);
    });
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const t = translations[currentLanguage] || {};
    const cf = (t.contact && t.contact.form) || {};

    const name = document.getElementById('contactName')?.value.trim();
    const company = document.getElementById('contactCompany')?.value.trim();
    const phone = document.getElementById('contactPhone')?.value.trim();
    const email = document.getElementById('contactEmail')?.value.trim();
    const service = document.getElementById('contactServiceSelect')?.value;
    const message = document.getElementById('contactMessage')?.value.trim();

    const toast = document.getElementById('formToast');
    const toastMsg = document.getElementById('formToastMsg');

    function showErrorToast(msg) {
      if (!toast || !toastMsg) return;
      toastMsg.textContent = msg || (cf.errorMsg || '');
      toast.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-200 text-sm block animate-bounce';
      toast.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function showSuccessToast(msg) {
      if (!toast || !toastMsg) return;
      toastMsg.textContent = msg || (cf.successMsg || '');
      toast.className = 'p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 text-sm block';
      toast.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    if (!name || !company || !phone || !email || !service || !message) {
      showErrorToast(cf.errorMsg || '');
      return;
    }

    if (!validateEgyptianPhone(phone)) {
      showErrorToast(currentLanguage === 'ar'
        ? 'رقم التليفون غير صحيح. يجب أن يكون 11 رقماً مصرياً ويبدأ بـ 010 / 011 / 012 / 015.'
        : 'Invalid Egyptian phone. Must be 11 digits starting with 010/011/012/015.');
      document.getElementById('contactPhone')?.focus();
      return;
    }

    if (!validateEmailStrict(email)) {
      showErrorToast(currentLanguage === 'ar'
        ? 'البريد الإلكتروني غير صحيح. يرجى التأكد من وجود علامة @ والنطاق.'
        : 'Invalid email format. Must contain @ and a valid domain.');
      document.getElementById('contactEmail')?.focus();
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      const originalBtnLabel = submitBtn.innerHTML;
      submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i> ${cf.submitting || ''}`;
    }

    const fd = new FormData(form);
    fd.append('lang', currentLanguage);

    // Some hosting security layers (e.g. bot/DDoS "human check" on shared hosting)
    // answer a POST with a tiny HTML page that sets a verification cookie and
    // reloads - fine for a normal page load, but fetch() never executes that
    // script. Detect that shape, set the cookie ourselves, and retry once.
    function extractChallengeCookie(text) {
      const m = text.match(/document\.cookie\s*=\s*["']([^"']+)["']/);
      return m ? m[1] : null;
    }

    function submitContact(isRetry) {
      return fetch('assets/php/contact.php', { method: 'POST', body: fd })
        .then(res => res.text().then((text) => {
          let data = null;
          try { data = JSON.parse(text); } catch (e) { /* not JSON */ }

          if (!data) {
            const cookie = !isRetry ? extractChallengeCookie(text) : null;
            if (cookie) {
              document.cookie = cookie;
              return submitContact(true);
            }
            throw new Error('non_json_response');
          }
          return data;
        }));
    }

    submitContact(false)
      .then(data => {
        if (data && data.ok) {
          form.reset();
          showSuccessToast(cf.successMsg || '');
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
            } else if (data.code === 'rate_limited') {
              msg = (currentLanguage === 'ar')
                ? 'لقد تجاوزت الحد المسموح به من الطلبات. حاول مرة أخرى بعد دقيقة.'
                : 'Too many submissions. Please try again in a minute.';
            } else if (data.code === 'server') {
              msg = (currentLanguage === 'ar')
                ? 'حدث خطأ أثناء المعالجة. حاول مرة أخرى لاحقاً.'
                : 'Server error. Please try again later.';
            }
          }
          showErrorToast(msg);
        }
      })
      .catch(() => {
        showErrorToast(currentLanguage === 'ar'
          ? 'تعذر إرسال الطلب حالياً بسبب فحص أمني من مزود الاستضافة. برجاء المحاولة مرة أخرى خلال لحظات، أو تواصل معنا مباشرة عبر واتساب.'
          : 'We could not submit your request right now due to a security check on our hosting. Please try again shortly, or reach us directly via WhatsApp.');
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
document.addEventListener('DOMContentLoaded', async () => {
  // Wait for admin content (services/whyUs/clients/stats/settings) to merge
  if (window.ContentManager && window.ContentManager.ready) {
    await window.ContentManager.ready;
  }

  if (window.__ContentClients) {
    applyClientOverrides(window.__ContentClients);
  }

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
