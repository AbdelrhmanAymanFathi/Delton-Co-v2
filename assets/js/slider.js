/**
 * Delton Facility Management - Swiper Hero Slider Controller
 * Dynamic loading from admin/slider.json with built-in robust fallback
 */

let heroSwiperInstance = null;
let cachedSlidesData = null;

// Built-in fallback slides data in case admin/slider.json cannot be fetched
const defaultFallbackSlides = [
  {
    id: 1,
    image: "https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1600&q=80",
    ar: {
      badge: "ديلتون لإدارة المرافق المتكاملة",
      title: "شريكك الاستراتيجي في إدارة وتشغيل المنشآت",
      description: "نضمن لكم بيئة عمل فعالة ومستدامة تدعم أهداف استثماراتكم وتساهم في نجاح مؤسساتكم بأعلى معايير الجودة في جمهورية مصر العربية.",
      ctaPrimary: "طلب عرض سعر",
      ctaPrimaryLink: "#contact",
      ctaSecondary: "استكشف خدماتنا",
      ctaSecondaryLink: "#services"
    },
    en: {
      badge: "Delton Integrated FM Solutions",
      title: "Your Strategic Partner in Integrated Facility Management",
      description: "Ensuring an efficient, sustainable work environment that preserves your investment objectives and accelerates corporate success across Egypt with world-class standards.",
      ctaPrimary: "Request a Quote",
      ctaPrimaryLink: "#contact",
      ctaSecondary: "Explore Services",
      ctaSecondaryLink: "#services"
    }
  },
  {
    id: 2,
    image: "https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1600&q=80",
    ar: {
      badge: "الإدارة والصيانة الفنية الشاملة",
      title: "حلول هندسية وصيانة متكاملة لكافة المباني والفروع",
      description: "صيانة دورية وطارئة للتكييفات، الكهرباء، والسباكة عبر فنيين مقيمين أو حسب الطلب، بالإضافة لأعمال المقاولات والتجديدات للمباني الإدارية.",
      ctaPrimary: "تفاصيل الصيانة",
      ctaPrimaryLink: "#services",
      ctaSecondary: "تواصل معنا",
      ctaSecondaryLink: "#contact"
    },
    en: {
      badge: "Comprehensive Technical Maintenance",
      title: "Engineering & Facility Maintenance for Corporate Buildings",
      description: "24/7 preventive & reactive maintenance for HVAC, electrical, and plumbing systems with resident or on-demand certified technicians, plus turnkey renovations.",
      ctaPrimary: "Maintenance Details",
      ctaPrimaryLink: "#services",
      ctaSecondary: "Contact Us",
      ctaSecondaryLink: "#contact"
    }
  },
  {
    id: 3,
    image: "https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=1600&q=80",
    ar: {
      badge: "خدمات النظافة والتعقيم الفندقي",
      title: "أعلى معايير النظافة للواجهات وماكينات الصراف الآلي",
      description: "فرق عمل متخصصة وإشراف دقيق لنظافة المباني الداخلية والواجهات الخارجية، وخدمة تطهير ماكينات الـ ATM للبنوك الكبرى بأحدث المواد المعتمدة.",
      ctaPrimary: "خدمات النظافة",
      ctaPrimaryLink: "#services",
      ctaSecondary: "طلب خدمة",
      ctaSecondaryLink: "#contact"
    },
    en: {
      badge: "Commercial Cleaning & Facade Sanitization",
      title: "Pristine Facade Care & ATM Sanitization Services",
      description: "Dedicated teams and supervisors delivering superior internal/exterior hygiene, high-rise facade cleaning, and specialized ATM care across bank branches.",
      ctaPrimary: "Cleaning Services",
      ctaPrimaryLink: "#services",
      ctaSecondary: "Request Service",
      ctaSecondaryLink: "#contact"
    }
  },
  {
    id: 4,
    image: "https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=1600&q=80",
    ar: {
      badge: "التوريدات، التوظيف والضيافة",
      title: "إسناد وتشغيل متكامل يغطي كافة احتياجات منشأتك",
      description: "توريد الأثاث المكتبي ومستلزمات التشغيل، توظيف وتوريد العمالة المتخصصة (Outsourcing)، وإدارة متكاملة لخدمات الضيافة والكافيتريا وتنسيق المسطحات الخضراء.",
      ctaPrimary: "حلول التشغيل",
      ctaPrimaryLink: "#services",
      ctaSecondary: "تواصل مع المبيعات",
      ctaSecondaryLink: "#contact"
    },
    en: {
      badge: "Supplies, Outsourcing & Hospitality",
      title: "Comprehensive Operations & Specialized Manpower Outsourcing",
      description: "Turnkey operational supplies, office furnishings, specialized personnel outsourcing, professional cafeteria management, and landscape architecture.",
      ctaPrimary: "Operational Solutions",
      ctaPrimaryLink: "#services",
      ctaSecondary: "Contact Sales",
      ctaSecondaryLink: "#contact"
    }
  }
];

/**
 * Fetch slides from admin/slider.json with fallback
 */
async function loadSlidesData() {
  try {
    const res = await fetch('admin/slider.json?t=' + new Date().getTime());
    if (res.ok) {
      const data = await res.json();
      if (Array.isArray(data) && data.length > 0) {
        cachedSlidesData = data;
        return data;
      }
    }
  } catch (err) {
    console.info('Using embedded fallback slides for Delton Hero Slider');
  }
  cachedSlidesData = defaultFallbackSlides;
  return defaultFallbackSlides;
}

/**
 * Render slides HTML inside swiper-wrapper
 */
function renderSlides(slides, currentLang) {
  const wrapper = document.getElementById('heroSwiperWrapper');
  if (!wrapper) return;

  const isRtl = currentLang === 'ar';
  
  wrapper.innerHTML = slides.map(slide => {
    const content = slide[currentLang] || slide.ar || slide.en;
    return `
      <div class="swiper-slide flex items-center">
        <!-- Background Image with Zoom & Dark Gradient -->
        <div class="slide-bg" style="background-image: url('${slide.image}');"></div>
        <div class="slide-overlay"></div>
        
        <!-- Slide Content Container -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-12 relative z-10 py-16">
          <div class="max-w-3xl ${isRtl ? 'text-right' : 'text-left'} space-y-6">
            
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-yellow-500/10 border border-yellow-500/30 text-[#C9A227] text-sm font-semibold tracking-wide">
              <span class="w-2 h-2 rounded-full bg-[#C9A227] animate-ping"></span>
              <span>${content.badge}</span>
            </div>
            
            <!-- Title -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
              ${content.title}
            </h1>
            
            <!-- Description -->
            <p class="text-base sm:text-lg md:text-xl text-slate-300 font-normal leading-relaxed">
              ${content.description}
            </p>
            
            <!-- Dual CTA Buttons -->
            <div class="pt-4 flex flex-wrap items-center gap-4 ${isRtl ? 'justify-start' : 'justify-start'}">
              <a href="${content.ctaPrimaryLink || '#contact'}" 
                 class="px-8 py-3.5 rounded-xl font-bold bg-gradient-gold text-[#0B132B] hover:shadow-lg hover:shadow-yellow-500/30 transition duration-300 transform hover:-translate-y-0.5 flex items-center gap-2">
                <span>${content.ctaPrimary}</span>
                <i class="fa-solid ${isRtl ? 'fa-arrow-left' : 'fa-arrow-right'} text-sm"></i>
              </a>
              
              <a href="${content.ctaSecondaryLink || '#services'}" 
                 class="px-8 py-3.5 rounded-xl font-bold bg-slate-900/80 hover:bg-slate-800 text-white border border-slate-700 hover:border-[#C9A227] transition duration-300 transform hover:-translate-y-0.5 flex items-center gap-2">
                <span>${content.ctaSecondary}</span>
                <i class="fa-solid fa-layer-group text-[#C9A227] text-sm"></i>
              </a>
            </div>
            
          </div>
        </div>
      </div>
    `;
  }).join('');
}

/**
 * Initialize or rebuild Swiper
 */
async function initHeroSlider(lang = 'ar') {
  const slides = cachedSlidesData || await loadSlidesData();
  renderSlides(slides, lang);

  if (heroSwiperInstance) {
    heroSwiperInstance.destroy(true, true);
  }

  // Initialize Swiper
  if (typeof Swiper !== 'undefined') {
    heroSwiperInstance = new Swiper('.hero-slider', {
      loop: true,
      speed: 1100,
      effect: 'fade',
      fadeEffect: {
        crossFade: true
      },
      autoplay: {
        delay: 5500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true
      },
      pagination: {
        el: '.hero-pagination',
        clickable: true
      },
      navigation: {
        nextEl: '.hero-btn-next',
        prevEl: '.hero-btn-prev'
      },
      keyboard: {
        enabled: true
      }
    });
  }
}

/**
 * Called when switching languages
 */
window.updateSliderLanguage = function(newLang) {
  initHeroSlider(newLang);
};

document.addEventListener('DOMContentLoaded', () => {
  const currentLang = localStorage.getItem('delton_lang') || 'ar';
  initHeroSlider(currentLang);
});
