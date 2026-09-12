/**
 * Delton Facility Management - Complete Bilingual Dictionary (Arabic & English)
 */
const translations = {
  ar: {
    meta: {
      title: "شركة ديلتون لخدمات وإدارة المنشآت ش.م.م | Delton FM Solutions",
      desc: "ديلتون: شريكك الاستراتيجي في إدارة المنشآت المتكاملة في مصر. خدمات النظافة والتعقيم، الصيانة الفنية، المسطحات الخضراء، التوريدات والتوظيف، وإدارة الضيافة."
    },
    topbar: {
      tagline: "شريكك الموثوق في إدارة المنشآت المتكاملة بمصر",
      phone: "01102668966 / 01123544717 / 01123545516",
      email: "info@delton-eg.com",
      hours: "الأحد - الخميس: 8:30 ص - 5:00 م (دعم الطوارئ 24/7)",
      cta: "طلب استشارة سريعة"
    },
    nav: {
      home: "الرئيسية",
      about: "عن ديلتون",
      services: "خدماتنا",
      whyUs: "لماذا تختارنا",
      clients: "شركاء النجاح",
      contact: "اتصل بنا",
      careers: "الوظائف",
      quoteBtn: "طلب عرض سعر",
      profileDownload: "تحميل البروفايل (PDF)",
      langToggle: "English"
    },
    stats: {
      item1: { num: "2019", label: "سنة التأسيس", desc: "شركة مساهمة مصرية رائدة" },
      item2: { num: "+18", label: "شراكات استراتيجية", desc: "بنوك ومؤسسات سيادية وكبرى" },
      item3: { num: "24/7", label: "جاهزية ودعم فني", desc: "فرق مقيمة واستجابة فورية" },
      item4: { num: "100%", label: "تغطية جغرافية", desc: "كافة أنحاء جمهورية مصر العربية" }
    },
    about: {
      badge: "نبذة عن ديلتون",
      title: "ريادة في تقديم حلول إدارة المنشآت المتكاملة",
      lead: "تأسست شركة ديلتون عام 2019 كشركة مساهمة مصرية متخصصة في تقديم خدمات الإدارة المتكاملة للمنشآت، مستهدفةً كافة المؤسسات والشركات الكبرى في جمهورية مصر العربية.",
      p1: "نحن ندرك بعمق متطلبات وتحديات تشغيل المباني الإدارية والفروع الحيوية، لذا نضمن لشركائنا بيئة عمل فعالة، آمنة ومستدامة تدعم وتحافظ على أهداف استثماراتهم وتساهم مباشرة في تعزيز إنتاجية ونجاح مؤسساتهم.",
      p2: "سواء كنتم بحاجة إلى إدارة تشغيلية شاملة لكامل المنشأة أو حلول تخصصية محددة، فإننا نمتلك الكفاءة الفنية والكوادر المؤهلة لتصميم وتنفيذ الحل الأمثل وفق اتفاقيات مستوى الخدمة (SLA) الصارمة.",
      pillarsTitle: "أبرز ركائز تميزنا التشغيلي:",
      pillar1: "فريق هندسي وفني معتمد مقيم أو عند الطلب",
      pillar2: "التزام كامل بمعايير السلامة المهنية والصحة والبيئة (HSE)",
      pillar3: "تقنيات ومعدات أوروبية حديثة لضمان أعلى جودة",
      pillar4: "إشراف دوري وتقارير تشغيلية رقمية شفافة",
      downloadAr: "تحميل البروفايل بالعربية (PDF)",
      downloadEn: "تحميل البروفايل بالإنجليزية (PDF)",
      registeredInfo: "شركة مساهمة مصرية خاضعة لأحكام قانون الشركات المصري",
      companyInfo: {
        legalLabel: "الشكل القانوني:",
        legalValue: "شركة مساهمة مصرية (ش.م.م)",
        foundedLabel: "تاريخ التأسيس:",
        foundedValue: "2019",
        headquartersLabel: "المقر الرئيسي:",
        headquartersValue: "زهراء المعادي، القاهرة",
        scopeLabel: "نطاق العمليات:",
        scopeValue: "كافة محافظات جمهورية مصر العربية",
        quote: "\"نضمن لكم بيئة عمل فعالة تدعم وتحافظ على أهداف استثماراتكم وتساهم في نجاح مؤسساتكم.\""
      }
    },
    services: {
      badge: "محفظة الخدمات",
      title: "حلول شاملة ومتكاملة لإدارة المنشآت (FM Solutions)",
      subtitle: "نقدم حلولاً متكاملة لإدارة المرافق، يمكننا توفيرها بشكل جزئي أو كلي بحسب احتياجات مؤسستكم وطبيعة عملياتكم.",
      viewDetails: "عرض التفاصيل والمهام",
      closeModal: "إغلاق",
      requestService: "طلب هذه الخدمة الآن",
      items: [
        {
          id: "maintenance",
          icon: "fa-screwdriver-wrench",
          image: "assets/images/services/service-maintenance.jpg",
          gallery: [
            "assets/images/services/service-maintenance.jpg",
            "assets/images/services/service-maintenance-2.jpg"
          ],
          title: "الإدارة والصيانة الفنية",
          shortDesc: "صيانة شاملة للتكييفات، الكهرباء، والسباكة عبر فنيين مقيمين أو حسب الطلب لضمان استمرارية التشغيل.",
          modalTitle: "الإدارة والتشغيل وحلول الصيانة الهندسية المتكاملة",
          overview: "نضمن استمرارية أعمالكم دون انقطاع عبر برامج الصيانة الوقائية والتصحيحية لكافة الأنظمة الكهروميكانيكية، مدعومة بفرق هندسية وفنية معتمدة تلبي أعلى معايير الجودة والسلامة.",
          tasks: [
            "صيانة منظومات التكييف بكافة أنواعها (Chillers, VRV/VRF, Package, Split).",
            "صيانة اللوحات الكهربائية، المولدات، شبكات الإنارة، وأنظمة التيار الخفيف (Low Current).",
            "إصلاح وصيانة شبكات السباكة، مضخات المياه، خطوط الصرف، ومصائد الشحوم.",
            "توفير أطقم فنية مقيمة (Resident Technicians) في الموقع أو فرق دعم سريع حسب الطلب 24/7.",
            "إجراء الفحوصات الدورية والاختبارات الوقائية المجدولة للأجهزة والمعدات الحيوية."
          ],
          equipment: "أجهزة فحص حراري دقيقة، قطع غيار أصلية، فرق صيانة متنقلة ومجهزة بالكامل."
        },
        {
          id: "cleaning",
          icon: "fa-broom",
          image: "assets/images/services/service-cleaning.jpg",
          gallery: [
            "assets/images/services/service-cleaning.jpg",
            "assets/images/services/service-cleaning-2.jpg"
          ],
          title: "خدمات النظافة والتعقيم المتكاملة",
          shortDesc: "نظافة داخلية وخارجية شاملة للمباني والواجهات، وتنظيف متخصص لماكينات الصراف الآلي (ATM).",
          modalTitle: "خدمات النظافة الشاملة، الواجهات وماكينات الصراف الآلي",
          overview: "نقدم خدمات النظافة والتعقيم وفق بروتوكولات فندقية صارمة تشمل المباني الإدارية، المراكز التجارية، الفروع المصرفية، والصروح الطبية، بإشراف أطقم مدربة وخامات معتمدة دولياً.",
          tasks: [
            "النظافة الداخلية اليومية والشاملة للمكاتب، القاعات، والممرات ومناطق الاستقبال.",
            "تنظيف وجلي وتلميع الأرضيات (رخام، جرانيت، باركيه) بأحدث الماكينات الدوارة.",
            "نظافة الواجهات الخارجية (زجاج، كلادينج، واجهات حجرية) باستخدام أوناش ومعدات سلامة متطورة.",
            "تنظيف وتطهير ماكينات الصراف الآلي (ATM) داخل وخارج الفروع وتلميع الشاشات واللوحات بإتقان.",
            "التعقيم المستمر لنقاط التلامس ودورات المياه بمطهرات معتمدة وصديقة للبيئة."
          ],
          equipment: "ماكينات Kärcher ألمانية، رافعات هيدروليكية، مواد تعقيم ومطهرات معتمدة صحياً."
        },
        {
          id: "landscaping",
          icon: "fa-tree",
          image: "assets/images/services/service-landscaping.jpg",
          gallery: [
            "assets/images/services/service-landscaping.jpg",
            "assets/images/services/service-landscaping-2.jpg"
          ],
          title: "تنسيق وعناية المسطحات الخضراء",
          shortDesc: "إنشاء وتصميم المسطحات الخضراء والعناية والمتابعة الدورية بها داخل وخارج المنشأة.",
          modalTitle: "تصميم، إنشاء والعناية الدورية بالمسطحات الخضراء",
          overview: "فريق زراعي متخصص مسؤول عن إضفاء الطابع الجمالي والبيئي المستدام لمنشآتكم من خلال تصميم وتنسيق الحدائق والمساحات الخضراء ومتابعتها بصورة دورية دقيقة.",
          tasks: [
            "تصميم وتنفيذ الحدائق الخارجية واللاندسكيب للمقرات الإدارية والمجمعات.",
            "تركيب وصيانة شبكات الري الحديثة (الري بالتنقيط والرشاشات الأوتوماتيكية).",
            "القص والتهذيب الدوري للنجيل والشجيرات والأسيجة النباتية بجدول زمني منتظم.",
            "التسميد العضوي ومكافحة الآفات الزراعية بمبيدات مرخصة وآمنة بيئياً.",
            "توريد ورعاية نباتات الزينة الداخلية للمكاتب وصالات الاستقبال والاجتماعات."
          ],
          equipment: "أجهزة ومقصات هيدروليكية، شبكات ري ذكية، بيوت محمية ونباتات فاخرة."
        },
        {
          id: "supplies",
          icon: "fa-boxes-packing",
          image: "assets/images/services/service-supplies.jpg",
          gallery: [
            "assets/images/services/service-supplies.jpg",
            "assets/images/services/service-supplies-2.jpg"
          ],
          title: "التوريدات التشغيلية والتوظيف (Outsourcing)",
          shortDesc: "توريد الأثاث المكتبي ومستلزمات النظافة والبوفيه، وتوظيف وتوريد العمالة المتخصصة للشركات.",
          modalTitle: "التوريدات التشغيلية الشاملة وإسناد الكوادر البشرية",
          overview: "نلبي كافة الاحتياجات اللوجستية والتشغيلية لمؤسستكم من التوريدات المكتبية والخامات، إلى جانب حلول التوظيف وتوريد الكوادر البشرية المتخصصة بأعلى معايير الانضباط والكفاءة.",
          tasks: [
            "توريد الأثاث المكتبي العصري، المكاتب التنفيذية، وحدات العمل وقاعات المؤتمرات.",
            "توفير خامات وأدوات النظافة عالية الجودة وموزعات المناديل والمطهرات بالجملة.",
            "توريد مستلزمات البوفيه اليومية وخامات الضيافة بجودة ممتازة وأسعار تنافسية.",
            "توظيف وتوريد العمالة المتخصصة والمدربة (Outsourcing) لصالح كبرى المؤسسات.",
            "إدارة ملفات التأمينات، الرواتب، والتدريب المهني للعمالة المسندة بالكامل."
          ],
          equipment: "سلاسل إمداد موثوقة، مستودعات تخزين مجهزة، أسطول نقل لوجستي سريع."
        },
        {
          id: "hospitality",
          icon: "fa-mug-hot",
          image: "assets/images/services/service-hospitality.jpg",
          gallery: [
            "assets/images/services/service-hospitality.jpg",
            "assets/images/services/service-hospitality-2.jpg"
          ],
          title: "إدارة خدمات الضيافة والبوفيه",
          shortDesc: "إدارة متكاملة للمطابخ والكافيتريات وتوفير عمال المطبخ والخامات وفق أعلى معايير الجودة.",
          modalTitle: "إدارة المطابخ والكافيتريات وخدمات الضيافة المؤسسية",
          overview: "نقدم تجربة ضيافة راقية تعكس وقار مؤسستكم أمام ضيوفكم وموظفيكم، عبر إدارة احترافية للمطابخ والكافيتريات بأطقم مدربة على أصول الضيافة الفندقية.",
          tasks: [
            "إدارة وتشغيل مطابخ وكافيتريات الشركات والمقرات الإدارية بالكامل.",
            "توفير شيفات، باريستا، وعمال بوفيه مدربين على اللياقة والمظهر المهني واللباقة.",
            "توفير الخامات الغذائية الطازجة والمشروبات الساخنة والباردة بعقود دورية منتظمة.",
            "تجهيز بوفيهات الاجتماعات الوزارية والتنفيذية والفعاليات والمؤتمرات الرسمية.",
            "تطبيق معايير السلامة الغذائية العالمية (HACCP) والنظافة الفائقة لكافة الأدوات."
          ],
          equipment: "معدات طهي وإعداد ستانلس ستيل معتمدة، ماكينات قهوة إيطالية احترافية."
        },
        {
          id: "renovation",
          icon: "fa-building",
          image: "assets/images/services/service-renovation.jpg",
          gallery: [
            "assets/images/services/service-renovation.jpg",
            "assets/images/services/service-renovation-2.jpg"
          ],
          title: "أعمال المقاولات والتجديدات",
          shortDesc: "تنفيذ أعمال المقاولات المتكاملة والتجديدات للمباني الإدارية والفروع التجارية وفق أعلى المواصفات.",
          modalTitle: "أعمال المقاولات والتجديدات والتشطيبات المعمارية",
          overview: "فريق هندسي متخصص في تنفيذ أعمال المقاولات الشاملة والتجديدات وإعادة تهيئة المقرات الإدارية والفروع المصرفية والتجارية، مع الالتزام التام بالجداول الزمنية والمواصفات القياسية.",
          tasks: [
            "تنفيذ أعمال المقاولات المتكاملة والإنشاءات التكميلية لمنشأتكم.",
            "التجديدات المعمارية والتشطيبات الداخلية المخططة وفقاً لمتطلبات التشغيل.",
            "تعديل وتجهيز المساحات المكتبية والقواطع والأسقف المعلقة والأرضيات.",
            "أعمال العزل المائي والحراري والدهانات الديكورية المقاومة للعوامل الجوية.",
            "إدارة مراحل المشروع والتسليم بأعلى معايير الجودة وضمان الأعمال."
          ],
          equipment: "معدات بناء وتشطيب متطورة، خامات معتمدة طبقاً للمواصفات القياسية المصرية."
        }
      ]
    },
    whyUs: {
      badge: "لماذا ديلتون؟",
      title: "معايير استثنائية تجعلنا خيار كبرى البنوك والمؤسسات",
      subtitle: "نحن لا نقدم مجرد خدمات اعتيادية، بل نبني شراكات نجاح طويلة الأمد ترتكز على الشفافية والالتزام.",
      cards: [
        {
          icon: "fa-award",
          title: "خبرة وسجل إنجازات موثوق",
          desc: "منذ عام 2019 نجحنا في كسب ثقة كبرى الهيئات القضائية والبنوك والمؤسسات الصناعية الرائدة في مصر."
        },
        {
          icon: "fa-user-gear",
          title: "كوادر فنية متخصصة ومقيمة",
          desc: "أطقم عمل مدربة تخضع لإشراف فني مستمر، مع إمكانية تواجد فنيين مقيمين بالمبنى لضمان عدم توقف الأعمال."
        },
        {
          icon: "fa-map-location-dot",
          title: "تغطية شاملة لجمهورية مصر",
          desc: "قدرة لوجستية على خدمة فروعكم ومقراتكم عبر كافة المحافظات والمدن الجديدة بذات الجودة والجاهزية."
        },
        {
          icon: "fa-shield-halved",
          title: "اتفاقيات أداء ومستوى خدمة (SLA)",
          desc: "عقود واضحة ومحددة تتضمن مؤشرات أداء دقيقة (KPIs) تضمن حقوقكم واستجابة فورية لأي طوارئ."
        },
        {
          icon: "fa-hand-holding-dollar",
          title: "كفاءة اقتصادية وترشيد تكاليف",
          desc: "حلول تشغيلية مدروسة تمنع الهدر وتقلل تكاليف الاستهلاك والصيانة على المدى الطويل دون المساس بالجودة."
        },
        {
          icon: "fa-headset",
          title: "متابعة وإشراف على مدار الساعة",
          desc: "غرفة عمليات وخدمة عملاء نشطة على مدار الساعة لتقديم الدعم الفوري ومتابعة خطط التشغيل."
        }
      ]
    },
    clients: {
      badge: "شركاء المسيرة",
      title: "شركاء النجاح وقائمة الشراكات الاستراتيجية",
      subtitle: "تعتبر قائمة عملائنا شهادة حية على جودة خدماتنا وثقة كبرى الكيانات في إمكانياتنا التشغيلية.",
      filterAll: "جميع القطاعات",
      filterBanking: "القطاع المصرفي والمالي",
      filterCorporate: "المؤسسات الكبرى والصناعية",
      sectors: {
        banking: "القطاع المصرفي والمالي",
        gov: "المؤسسات السيادية والقضائية",
        industrial: "القطاع الصناعي والتصنيع",
        energy: "قطاع الطاقة والكابلات",
        realestate: "التطوير العقاري والاستثمار",
        petroleum: "قطاع البترول والخدمات البترولية",
        agri: "الصناعات الغذائية والزراعية",
        auto: "قطاع السيارات والمحركات",
        brokerage: "الوساطة المالية والاستثمار"
      }
    },
    contact: {
      badge: "تواصل معنا",
      title: "جاهزون لخدمتكم ومناقشة احتياجات منشأتكم",
      subtitle: "يسعدنا الرد على استفساراتكم وترتيب زيارة معاينة ميدانية لمقر مؤسستكم لتقديم عرض فني ومالي مفصل.",
      addressTitle: "العنوان الرئيسي:",
      address: "2120 شارع الأرقم، المعراج، زهراء المعادي، القاهرة، مصر",
      phoneTitle: "أرقام الهاتف المباشرة:",
      emailTitle: "البريد الإلكتروني:",
      hoursTitle: "أوقات العمل الرسمية:",
      form: {
        title: "طلب عرض سعر أو استشارة",
        nameLabel: "الاسم بالكامل *",
        namePlaceholder: "مثال: م. أحمد عبد الرحمن",
        companyLabel: "اسم الشركة / المؤسسة *",
        companyPlaceholder: "مثال: البنك الأهلي / شركة أرامكو",
        phoneLabel: "رقم الهاتف / الواتساب *",
        phonePlaceholder: "010xxxxxxxx",
        emailLabel: "البريد الإلكتروني المهني *",
        emailPlaceholder: "name@company.com",
        serviceLabel: "الخدمة المطلوبة *",
        selectServicePlaceholder: "-- اختر الخدمة المطلوبة --",
        allServicesOpt: "حلول متكاملة لكامل المنشأة (Comprehensive FM)",
        cleaningOpt: "خدمات النظافة والتعقيم والواجهات",
        maintenanceOpt: "الإدارة والصيانة الفنية والمقاولات",
        landscapingOpt: "تنسيق وعناية المسطحات الخضراء",
        suppliesOpt: "التوريدات التشغيلية وتوظيف العمالة",
        hospitalityOpt: "إدارة خدمات الضيافة والبوفيه",
        renovationOpt: "أعمال المقاولات والتجديدات",
        otherOpt: "أخرى (تُحدد في الرسالة)",
        messageLabel: "تفاصيل الاحتياج أو المشروع *",
        messagePlaceholder: "يرجى توضيح حجم المقر، طبيعة المنشأة، الخدمات المطلوبة والموقع الجغرافي...",
        submitBtn: "إرسال طلب العرض الفني",
        submitting: "جاري الإرسال...",
        successMsg: "شكراً لتواصلكم مع شركة ديلتون! تم استلام طلبكم بنجاح، وسيتواصل معكم قطاع التسويق والمبيعات خلال 24 ساعة.",
        errorMsg: "يرجى التأكد من ملء جميع الحقول الإلزامية المطلوبة."
      }
    },
    careers: {
      badge: "فرص العمل",
      title: "انضم إلى فريق ديلتون",
      subtitle: "نبحث دائماً عن كوادر متميزة تشاركنا الشغف بالتميز التشغيلي وخدمة المنشآت الكبرى.",
      lead: "قدّم سيرتك الذاتية الآن وسيتواصل معك فريق الموارد البشرية خلال 5 أيام عمل كحد أقصى.",
      formTitle: "نموذج التقديم على وظيفة",
      formDesc: "أكمل الحقول التالية وجميع البيانات سيتم التعامل معها بسرية تامة وفق سياسة الخصوصية الخاصة بالشركة.",
      directTitle: "أو أرسل سيرتك الذاتية مباشرة",
      directNote: "يمكنك أيضاً إرسال السيرة الذاتية على البريد الإلكتروني الرسمي:",
      successTitle: "تم استلام طلبك بنجاح!",
      successMsg: "شكراً لاهتمامك بالانضمام إلينا، سيقوم فريق الموارد البشرية بمراجعة بياناتك والتواصل معك قريباً.",
      errorTitle: "فشل إرسال الطلب",
      errorMsg: "حدث خطأ غير متوقع أثناء الإرسال. يرجى المحاولة مرة أخرى أو التواصل معنا مباشرة عبر البريد الإلكتروني.",
      fileError: "الملف المرفق غير صالح أو يتجاوز الحجم المسموح به (5 ميجابايت).",
      form: {
        nameLabel: "الاسم بالكامل *",
        namePlaceholder: "مثال: أحمد محمد علي",
        phoneLabel: "رقم الهاتف / الواتساب *",
        phonePlaceholder: "010xxxxxxxx",
        emailLabel: "البريد الإلكتروني *",
        emailPlaceholder: "name@example.com",
        positionLabel: "الوظيفة المتقدم لها *",
        positionPlaceholder: "اكتب الوظيفة أو اختر من الاقتراحات",
        cityLabel: "المحافظة / المدينة *",
        cityPlaceholder: "مثال: القاهرة - المعادي",
        experienceLabel: "سنوات الخبرة",
        qualificationLabel: "المؤهل الدراسي",
        qualificationPlaceholder: "مثال: بكالوريوس تجارة",
        cvLabel: "إرفاق السيرة الذاتية (CV) - اختياري",
        cvNote: "صيغ مسموحة: PDF, DOC, DOCX, JPG, PNG - بحد أقصى 5 ميجابايت",
        messageLabel: "نبذة عن خبراتك (اختياري)",
        messagePlaceholder: "اكتب نبذة مختصرة عن خبراتك السابقة وأي مهارات تميزك...",
        submitBtn: "إرسال طلب التقديم",
        submitting: "جاري الإرسال...",
        requiredError: "يرجى التأكد من ملء جميع الحقول الإلزامية بشكل صحيح."
      },
      experiences: ["بدون خبرة", "أقل من سنة", "1 - 3 سنوات", "3 - 5 سنوات", "أكثر من 5 سنوات"],
      jobRoles: [
        "عامل نظافة", "عامل تعقيم", "عامل نظافة واجهات", "فني صيانة عامة", "فني تكييف",
        "فني كهرباء", "فني سباكة", "عامل مسطحات خضراء", "بستاني", "شيف", "باريستا",
        "عامل بوفيه", "سائق", "أعمال أمن", "محاسب", "موظف موارد بشرية", "موظف إداري",
        "مهندس ميكانيكا", "مهندس كهرباء", "منسق مشاريع", "مشرف موقع"
      ],
      governorates: [
        "القاهرة", "الجيزة", "الإسكندرية", "الدقهلية", "الشرقية", "الغربية", "المنوفية",
        "القليوبية", "الفيوم", "بني سويف", "المنيا", "أسيوط", "سوهاج", "قنا", "الأقصر",
        "أسوان", "البحر الأحمر", "بورسعيد", "الإسماعيلية", "السويس", "دمياط", "كفر الشيخ",
        "مطروح", "شمال سيناء", "جنوب سيناء", "الوادي الجديد"
      ]
    },
    footer: {
      quickLinks: "روابط سريعة",
      ourServices: "خدماتنا الأساسية",
      srvMaintenance: "الإدارة والصيانة الفنية",
      srvCleaning: "خدمات النظافة والتعقيم والواجهات",
      srvLandscaping: "تنسيق وعناية المسطحات الخضراء",
      srvSupplies: "التوريدات التشغيلية والتوظيف (Outsourcing)",
      srvHospitality: "إدارة خدمات الضيافة والبوفيه",
      srvRenovation: "أعمال المقاولات والتجديدات",
      contactInfo: "بيانات التواصل",
      copyright: "© 2026 شركة ديلتون لخدمات وإدارة المنشآت ش.م.م. جميع الحقوق محفوظة.",
      adminPortal: "بوابة الإدارة",
      profileDownloadNotice: "البروفايل التعريفي الرسمي (PDF)"
    },
    whatsappTooltip: "تواصل معنا مباشرة عبر واتساب"
  },
  en: {
    meta: {
      title: "Delton for Services & Facility Management S.A.E | Integrated FM Solutions",
      desc: "Delton: Your strategic partner in integrated facility management in Egypt. Commercial cleaning, technical maintenance, landscaping, logistics & staffing, and corporate catering."
    },
    topbar: {
      tagline: "Your Trusted Partner in Integrated Facility Management in Egypt",
      phone: "+20 1102668966 / +20 1123544717 / +20 1123545516",
      email: "info@delton-eg.com",
      hours: "Sunday - Thursday: 8:30 AM - 5:00 PM (24/7 Emergency Support)",
      cta: "Quick Consultation"
    },
    nav: {
      home: "Home",
      about: "About Us",
      services: "Services",
      whyUs: "Why Delton",
      clients: "Our Clients",
      contact: "Contact Us",
      careers: "Careers",
      quoteBtn: "Request a Quote",
      profileDownload: "Download Profile (PDF)",
      langToggle: "العربية"
    },
    stats: {
      item1: { num: "2019", label: "Year Founded", desc: "Leading Egyptian Joint-Stock Company" },
      item2: { num: "+18", label: "Strategic Partnerships", desc: "Major Banks & Sovereign Entities" },
      item3: { num: "24/7", label: "Technical Readiness", desc: "Resident Technicians & Rapid Response" },
      item4: { num: "100%", label: "National Coverage", desc: "Across All Governorates of Egypt" }
    },
    about: {
      badge: "About Delton",
      title: "Pioneering Integrated Facility Management Solutions",
      lead: "Delton Company was established in 2019 as an Egyptian joint-stock company, dedicated to providing integrated facility management services to all leading institutions and corporations operating across Egypt.",
      p1: "We understand our clients' profound requirements and operational challenges in administrative headquarters and mission-critical facilities. We are honored to ensure a productive, safe, and sustainable work environment that safeguards your investments and directly drives organizational success.",
      p2: "Whether you need full turnkey facility management or tailored specialized services, our accredited technical workforce and engineering leadership deliver optimal solutions aligned with rigorous Service Level Agreements (SLAs).",
      pillarsTitle: "Key Pillars of Operational Excellence:",
      pillar1: "Certified resident or on-demand engineering & technical teams",
      pillar2: "Strict adherence to Health, Safety & Environment (HSE) standards",
      pillar3: "Advanced European equipment and eco-certified materials",
      pillar4: "Periodic oversight and transparent digital operational reports",
      downloadAr: "Download Profile in Arabic (PDF)",
      downloadEn: "Download Profile in English (PDF)",
      registeredInfo: "Egyptian Joint Stock Company subject to Egyptian Companies Law",
      companyInfo: {
        legalLabel: "Legal form:",
        legalValue: "Egyptian Joint Stock Company (S.A.E)",
        foundedLabel: "Founded:",
        foundedValue: "2019",
        headquartersLabel: "Headquarters:",
        headquartersValue: "Zahraa El Maadi, Cairo",
        scopeLabel: "Operations scope:",
        scopeValue: "Across all governorates of the Arab Republic of Egypt",
        quote: "\"We deliver an efficient work environment that protects your investment goals and contributes to the success of your organization.\""
      }
    },
    services: {
      badge: "Services Portfolio",
      title: "Comprehensive Integrated Facility Management (FM Solutions)",
      subtitle: "We deliver complete facility management solutions, providable partially or turn-key according to your organizational requirements.",
      viewDetails: "View Details & Scope",
      closeModal: "Close",
      requestService: "Request This Service Now",
      items: [
        {
          id: "maintenance",
          icon: "fa-screwdriver-wrench",
          image: "assets/images/services/service-maintenance.jpg",
          gallery: [
            "assets/images/services/service-maintenance.jpg",
            "assets/images/services/service-maintenance-2.jpg"
          ],
          title: "Technical Management & Maintenance",
          shortDesc: "Air conditioning, electrical, and plumbing maintenance with resident or on-demand certified technicians.",
          modalTitle: "Integrated Engineering Maintenance & Technical Management",
          overview: "We ensure uninterrupted business continuity through proactive preventive and reactive corrective maintenance programs across all electromechanical infrastructure, backed by certified engineering teams.",
          tasks: [
            "Preventive and emergency servicing of HVAC systems (Chillers, VRV/VRF, Package, Split units).",
            "Electrical distribution boards, backup diesel generators, low current networks, and lighting systems.",
            "Water supply networks, booster pumps, drainage lines, grease traps, and plumbing systems.",
            "Dedicated on-site resident technicians or mobile rapid-response teams available 24/7.",
            "Scheduled preventive diagnostic tests for critical electromechanical assets."
          ],
          equipment: "High-precision thermal imaging cameras, OEM replacement parts, fully equipped mobile workshop units."
        },
        {
          id: "cleaning",
          icon: "fa-broom",
          image: "assets/images/services/service-cleaning.jpg",
          gallery: [
            "assets/images/services/service-cleaning.jpg",
            "assets/images/services/service-cleaning-2.jpg"
          ],
          title: "Integrated Cleaning & Sanitation",
          shortDesc: "Interior, exterior, and high-rise facade cleaning plus specialized ATM sanitation under Delton supervision.",
          modalTitle: "Commercial Cleaning, Facade Maintenance & ATM Sanitization",
          overview: "We execute commercial cleaning and sanitization following rigorous hospitality standards across corporate headquarters, shopping malls, banking branches, and medical facilities, led by qualified supervisors.",
          tasks: [
            "Daily deep cleaning of executive offices, workstations, conference rooms, and reception lobbies.",
            "Rotary mechanical polishing, crystallization, and sealing of marble, granite, and parquet floors.",
            "Exterior glass and alucobond facade cleaning utilizing modern scaffolding, cradles, and safety rigging.",
            "Specialized sanitization of Automatic Teller Machines (ATMs) inside and outside branches.",
            "Continuous touch-point sanitization and restroom hygiene using hospital-grade certified disinfectants."
          ],
          equipment: "German Kärcher industrial scrubbers, hydraulic cherry pickers, certified eco-friendly disinfectants."
        },
        {
          id: "landscaping",
          icon: "fa-tree",
          image: "assets/images/services/service-landscaping.jpg",
          gallery: [
            "assets/images/services/service-landscaping.jpg",
            "assets/images/services/service-landscaping-2.jpg"
          ],
          title: "Landscaping & Green Area Care",
          shortDesc: "Landscape design, establishment, and ongoing care for every green space around and within your facility.",
          modalTitle: "Design, Construction & Sustained Landscape Care",
          overview: "Our specialized agricultural engineering team enriches your corporate identity by designing, creating, and meticulously maintaining aesthetic, sustainable green environments indoors and outdoors.",
          tasks: [
            "Architectural landscape design and softscape construction for corporate complexes.",
            "Smart irrigation system installation and routine maintenance (automated drip and pop-up sprinklers).",
            "Scheduled mowing, topiary hedge trimming, pruning, and seasonal floral renewal.",
            "Organic fertilization and soil treatment using environmentally authorized pest control agents.",
            "Interior plant cultivation, botanical styling for VIP boardrooms, executive corridors, and entrance lobbies."
          ],
          equipment: "Commercial hydraulic mowers, automated irrigation timers, pest management sprayers."
        },
        {
          id: "supplies",
          icon: "fa-boxes-packing",
          image: "assets/images/services/service-supplies.jpg",
          gallery: [
            "assets/images/services/service-supplies.jpg",
            "assets/images/services/service-supplies-2.jpg"
          ],
          title: "Supplies & Manpower Outsourcing",
          shortDesc: "Office furniture, cleaning tools, pantry provisions, and specialized workforce outsourcing for major companies.",
          modalTitle: "Turnkey Operational Logistics & Human Capital Outsourcing",
          overview: "We streamline your operational supply chain and human capital requirements, providing high-standard supplies alongside certified personnel outsourcing with full legal and administrative compliance.",
          tasks: [
            "Executive office furniture, ergonomic workstations, boardroom setups, and modular furnishings.",
            "Commercial janitorial equipment, paper dispensers, certified detergents, and PPE supplies.",
            "Pantry provisions, specialty coffees, hospitality consumables, and pantry machinery.",
            "Sourcing, vetting, and outsourcing specialized labor and technical staff for corporate clients.",
            "Complete payroll, social insurance, labor law compliance, and vocational training management."
          ],
          equipment: "Verified procurement pipelines, central temperature-controlled storage, express distribution fleet."
        },
        {
          id: "hospitality",
          icon: "fa-mug-hot",
          image: "assets/images/services/service-hospitality.jpg",
          gallery: [
            "assets/images/services/service-hospitality.jpg",
            "assets/images/services/service-hospitality-2.jpg"
          ],
          title: "Corporate Hospitality Services",
          shortDesc: "Integrated management of corporate kitchens, cafeterias, hospitality personnel, and complete provisions.",
          modalTitle: "Corporate Cafeteria Operations & Executive Hospitality Services",
          overview: "Delivering an upscale hospitality experience that represents your corporate prestige before clients and staff, managing corporate kitchens with hotel-grade etiquette and food hygiene.",
          tasks: [
            "Complete management and day-to-day operation of corporate cafeterias and executive dining halls.",
            "Supplying certified executive chefs, professional baristas, and courteous hospitality staff.",
            "Sourcing fresh culinary ingredients, gourmet hot and cold beverages under scheduled agreements.",
            "Executive catering for board meetings, ministerial delegations, summits, and VIP events.",
            "Rigorous HACCP food safety protocols and sterile kitchen sanitation practices."
          ],
          equipment: "Commercial stainless steel culinary gear, professional Italian espresso machines, warming units."
        },
        {
          id: "renovation",
          icon: "fa-building",
          image: "assets/images/services/service-renovation.jpg",
          gallery: [
            "assets/images/services/service-renovation.jpg",
            "assets/images/services/service-renovation-2.jpg"
          ],
          title: "Renovation & Contracting Works",
          shortDesc: "Integrated contracting and renovation for administrative buildings, corporate premises, and commercial branches.",
          modalTitle: "Turnkey General Contracting, Renovation & Architectural Fit-Out",
          overview: "Specialized engineering and construction solutions for turnkey fit-out, architectural renovations, and spatial restructuring of administrative facilities and branches, strictly aligned with operational schedules.",
          tasks: [
            "Complete contracting works and auxiliary constructions tailored for your facility.",
            "Architectural renovations and interior remodeling planned around operational needs.",
            "Spatial reconfiguration, drywall partitions, acoustic ceilings, and flooring solutions.",
            "Thermal and moisture waterproofing, exterior facade coating, and durable finishes.",
            "End-to-end project management, quality assurance, and formal handover protocols."
          ],
          equipment: "Modern precision construction machinery, certified construction materials meeting Egyptian standards."
        }
      ]
    },
    whyUs: {
      badge: "Why Choose Delton",
      title: "Exceptional Standards Trusted by Premier Institutions",
      subtitle: "We don't merely provide services; we cultivate enduring partnerships founded on transparency, safety, and operational excellence.",
      cards: [
        {
          icon: "fa-award",
          title: "Proven Track Record",
          desc: "Since 2019, we have earned the trust of supreme judicial courts, central banks, and multinational industrial giants."
        },
        {
          icon: "fa-user-gear",
          title: "Certified Resident Technicians",
          desc: "Vetted, continually supervised technical workforce with options for full-time resident teams on your premises."
        },
        {
          icon: "fa-map-location-dot",
          title: "Nationwide Coverage",
          desc: "Full operational capability to serve headquarters and branch networks across all Egyptian governorates."
        },
        {
          icon: "fa-shield-halved",
          title: "Strict SLAs & Measurable KPIs",
          desc: "Clear contractual frameworks with measurable performance indicators guaranteeing fast emergency resolution."
        },
        {
          icon: "fa-hand-holding-dollar",
          title: "Cost Efficiency & Asset Longevity",
          desc: "Systematic preventive maintenance schedules that prevent asset degradation and trim unnecessary operational expenditures."
        },
        {
          icon: "fa-headset",
          title: "24/7 Command Center",
          desc: "Always-on dispatch center and customer service monitoring technical operations and emergency dispatches round the clock."
        }
      ]
    },
    clients: {
      badge: "Strategic Partnerships",
      title: "Delton's Clients & Strategic Success Partners",
      subtitle: "Our client roster reflects our unwavering commitment to quality and the trust major Egyptian institutions place in us.",
      filterAll: "All Sectors",
      filterBanking: "Banking & Financial Sector",
      filterCorporate: "Corporate & Industrial Entities",
      sectors: {
        banking: "Banking & Financial Services",
        gov: "Sovereign & Judicial Institutions",
        industrial: "Industrial & Manufacturing",
        energy: "Energy & Cabling Sector",
        realestate: "Real Estate & Commercial Developments",
        petroleum: "Petroleum & Energy Services",
        agri: "Agro-Industries & Commodities",
        auto: "Automotive Industry",
        brokerage: "Securities & Investment Banking"
      }
    },
    contact: {
      badge: "Contact Us",
      title: "Ready to Discuss Your Facility Requirements",
      subtitle: "We look forward to addressing your inquiries and scheduling a complimentary technical survey to deliver a comprehensive proposal.",
      addressTitle: "Headquarters Address:",
      address: "2120 St Elarqam, El Meraag, Zahraa Elmaadi, Cairo, Egypt",
      phoneTitle: "Direct Hotlines:",
      emailTitle: "Official Email:",
      hoursTitle: "Business Hours:",
      form: {
        title: "Request a Technical Consultation or Quote",
        nameLabel: "Full Name *",
        namePlaceholder: "e.g. Eng. Ahmed Abdelrahman",
        companyLabel: "Company / Institution *",
        companyPlaceholder: "e.g. National Bank / Multinational Corp",
        phoneLabel: "Phone / WhatsApp *",
        phonePlaceholder: "010xxxxxxxx",
        emailLabel: "Corporate Email *",
        emailPlaceholder: "name@company.com",
        serviceLabel: "Required Service *",
        selectServicePlaceholder: "-- Select Required Service --",
        allServicesOpt: "Turnkey Facility Management (Full FM)",
        cleaningOpt: "Commercial Cleaning & Facade Sanitization",
        maintenanceOpt: "Technical Maintenance & Contracting",
        landscapingOpt: "Landscaping & Green Area Care",
        suppliesOpt: "Operational Supplies & Manpower Outsourcing",
        hospitalityOpt: "Corporate Hospitality & Catering",
        renovationOpt: "Renovation & Contracting Works",
        otherOpt: "Other (Specified in Message)",
        messageLabel: "Project Details & Scope *",
        messagePlaceholder: "Please share premises size, facility type, requested scope, and location...",
        submitBtn: "Submit Request for Proposal",
        submitting: "Submitting...",
        successMsg: "Thank you for reaching out to Delton! Your request has been received, and our Commercial Sector team will contact you within 24 hours.",
        errorMsg: "Please ensure all required fields are correctly completed."
      }
    },
    careers: {
      badge: "Career Opportunities",
      title: "Join the Delton Team",
      subtitle: "We are always looking for talented professionals who share our passion for operational excellence.",
      lead: "Submit your application now and our HR team will get back to you within a maximum of 5 working days.",
      formTitle: "Job Application Form",
      formDesc: "Complete the fields below. All data is treated with strict confidentiality in line with our privacy policy.",
      directTitle: "Or send your CV directly",
      directNote: "You can also email your resume to our official inbox:",
      successTitle: "Application Received!",
      successMsg: "Thank you for applying. Our HR team will review your details and contact you soon.",
      errorTitle: "Submission Failed",
      errorMsg: "An unexpected error occurred. Please try again or contact us directly by email.",
      fileError: "The attached file is invalid or exceeds the allowed size (5 MB).",
      form: {
        nameLabel: "Full Name *",
        namePlaceholder: "e.g. Ahmed Mohamed Ali",
        phoneLabel: "Phone / WhatsApp *",
        phonePlaceholder: "010xxxxxxxx",
        emailLabel: "Email Address *",
        emailPlaceholder: "name@example.com",
        positionLabel: "Position Applied For *",
        positionPlaceholder: "Type the role or choose from suggestions",
        cityLabel: "Governorate / City *",
        cityPlaceholder: "e.g. Cairo - Maadi",
        experienceLabel: "Years of Experience",
        qualificationLabel: "Academic Qualification",
        qualificationPlaceholder: "e.g. B.Sc. Commerce",
        cvLabel: "Attach Resume (CV) - Optional",
        cvNote: "Allowed formats: PDF, DOC, DOCX, JPG, PNG - max 5 MB",
        messageLabel: "About Your Experience (Optional)",
        messagePlaceholder: "Write a short summary of your experience and key skills...",
        submitBtn: "Submit Application",
        submitting: "Submitting...",
        requiredError: "Please make sure all required fields are filled in correctly."
      },
      experiences: ["No experience", "Less than 1 year", "1 - 3 years", "3 - 5 years", "More than 5 years"],
      jobRoles: [
        "Cleaner", "Sanitization Worker", "Facade Cleaner", "General Maintenance Technician", "HVAC Technician",
        "Electrician", "Plumber", "Landscape Worker", "Gardener", "Chef", "Barista",
        "Pantry Worker", "Driver", "Security", "Accountant", "HR Officer", "Administrative Officer",
        "Mechanical Engineer", "Electrical Engineer", "Project Coordinator", "Site Supervisor"
      ],
      governorates: [
        "Cairo", "Giza", "Alexandria", "Dakahlia", "Sharqia", "Gharbia", "Monufia",
        "Qalyubia", "Faiyum", "Beni Suef", "Minya", "Asyut", "Sohag", "Qena", "Luxor",
        "Aswan", "Red Sea", "Port Said", "Ismailia", "Suez", "Damietta", "Kafr El Sheikh",
        "Matrouh", "North Sinai", "South Sinai", "New Valley"
      ]
    },
    footer: {
      aboutText: "An Egyptian Joint Stock Company specialized in delivering integrated facility management services to leading organizations across Egypt.",
      quickLinks: "Quick Links",
      ourServices: "Core Services",
      srvMaintenance: "Technical Management & Maintenance",
      srvCleaning: "Commercial Cleaning & Facade Sanitization",
      srvLandscaping: "Landscaping & Green Area Care",
      srvSupplies: "Supplies & Manpower Outsourcing",
      srvHospitality: "Corporate Hospitality & Catering",
      srvRenovation: "Renovation & Contracting Works",
      contactInfo: "Contact Information",
      copyright: "© 2026 Delton for Services & Facility Management S.A.E. All Rights Reserved.",
      adminPortal: "Admin Portal",
      profileDownloadNotice: "Official Corporate Profile (PDF)"
    },
    whatsappTooltip: "Chat with us directly on WhatsApp"
  }
};

if (typeof module !== 'undefined' && module.exports) {
  module.exports = translations;
}
