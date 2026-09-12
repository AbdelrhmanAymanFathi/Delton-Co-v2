/**
 * Delton Facility Management - Content Loader & Overrides
 * Fetches admin/content.json and deep-merges it over translations + client defaults.
 * Falls back to built-in defaults when the JSON cannot be fetched (e.g. local file:// usage).
 */

(function () {
  var CONTENT_URL = 'admin/content.json?t=' + Date.now();

  function applyContent(content) {
    if (!content || typeof content !== 'object') return;
    var langs = ['ar', 'en'];
    var i, l, t;

    // ---- Services ----
    if (content.services) {
      var srv = content.services;
      for (i = 0; i < langs.length; i++) {
        l = langs[i];
        t = translations[l];
        if (srv.header && srv.header[l]) {
          Object.assign(t.services, srv.header[l]);
        }
        if (Array.isArray(srv.items)) {
          t.services.items = srv.items.map(function (it) {
            var langData = it[l] || {};
            return {
              id: it.id,
              icon: it.icon,
              image: it.image,
              gallery: (it.gallery && it.gallery.length) ? it.gallery : [it.image],
              title: langData.title || '',
              shortDesc: langData.shortDesc || '',
              modalTitle: langData.modalTitle || '',
              overview: langData.overview || '',
              tasks: langData.tasks || [],
              equipment: langData.equipment || ''
            };
          });
        }
      }
    }

    // ---- Why Us ----
    if (content.whyUs) {
      var why = content.whyUs;
      for (i = 0; i < langs.length; i++) {
        l = langs[i];
        t = translations[l];
        if (why.header && why.header[l]) {
          Object.assign(t.whyUs, why.header[l]);
        }
        if (Array.isArray(why.cards)) {
          t.whyUs.cards = why.cards.map(function (c) {
            var cl = c[l] || {};
            return { icon: c.icon, title: cl.title || '', desc: cl.desc || '' };
          });
        }
      }
    }

    // ---- Stats (4 fixed slots) ----
    if (Array.isArray(content.stats)) {
      for (i = 0; i < langs.length; i++) {
        l = langs[i];
        t = translations[l];
        content.stats.forEach(function (st, idx) {
          var key = 'item' + (idx + 1);
          if (t.stats[key] && st[l]) {
            t.stats[key] = {
              num: st[l].num !== undefined ? st[l].num : (st[l].label || ''),
              label: st[l].label || '',
              desc: st[l].desc || ''
            };
          }
        });
      }
    }

    // ---- Clients (exposed for main.js render) ----
    if (Array.isArray(content.clients)) {
      window.__ContentClients = content.clients;
    }

    // ---- Clients Section Header ----
    if (content.clientsHeader) {
      for (i = 0; i < langs.length; i++) {
        l = langs[i];
        t = translations[l];
        if (content.clientsHeader[l]) {
          Object.assign(t.clients, content.clientsHeader[l]);
        }
      }
    }

    // ---- About ----
    if (content.about) {
      for (i = 0; i < langs.length; i++) {
        l = langs[i];
        t = translations[l];
        var a = content.about[l];
        if (!a) continue;
        t.about.badge = a.badge || t.about.badge;
        t.about.title = a.title || t.about.title;
        t.about.lead = a.lead || t.about.lead;
        t.about.p1 = a.p1 || t.about.p1;
        t.about.p2 = a.p2 || t.about.p2;
        t.about.pillarsTitle = a.pillarsTitle || t.about.pillarsTitle;
        if (Array.isArray(a.pillars)) {
          a.pillars.forEach(function (p, pi) {
            if (p) t.about['pillar' + (pi + 1)] = p;
          });
        }
        t.about.downloadAr = a.downloadAr || t.about.downloadAr;
        t.about.downloadEn = a.downloadEn || t.about.downloadEn;
        if (a.companyInfo) {
          t.about.companyInfo = Object.assign({}, t.about.companyInfo, a.companyInfo);
        }
      }
    }

    // ---- Contact ----
    if (content.contact) {
      window.__ContactPhones = Array.isArray(content.contact.phones) ? content.contact.phones : [];
      window.__ContactEmail = content.contact.email || '';
      window.__FooterAddress = {};
      for (i = 0; i < langs.length; i++) {
        l = langs[i];
        t = translations[l];
        var c = content.contact[l];
        if (!c) continue;
        Object.assign(t.contact, c);
        if (c.hours) t.topbar.hours = c.hours;
        if (c.address) window.__FooterAddress[l] = c.address;
      }
    }

    // ---- Footer ----
    if (content.footer) {
      window.__FooterPhones = content.footer.phones || '';
      for (i = 0; i < langs.length; i++) {
        l = langs[i];
        t = translations[l];
        var f = content.footer[l];
        if (!f) continue;
        Object.assign(t.footer, f);
        if (f.address) window.__FooterAddress[l] = f.address;
      }
    }

    // ---- Branding (navbar + footer logos) ----
    if (content.branding) {
      window.__Branding = {
        navbar: Object.assign({ image: 'newlogo.jpeg', width: '110', height: '52' }, content.branding.navbar || {}),
        footer: Object.assign({ image: 'assets/images/logoFooter.png', width: '150', height: '56' }, content.branding.footer || {})
      };
      if (window.renderBranding) window.renderBranding();
    }
  }

  window.ContentManager = {
    loaded: false,
    ready: (async function () {
      try {
        var res = await fetch(CONTENT_URL);
        if (res.ok) {
          var data = await res.json();
          applyContent(data);
          window.ContentManager.loaded = true;
        }
      } catch (err) {
        // Keep built-in defaults (offline / file:// usage)
      }
    })()
  };
})();