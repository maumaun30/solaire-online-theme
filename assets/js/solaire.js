/* ============================================================
   Solaire Online — shared front-end interactions
   Mobile drawer · carousel arrows · accordion · entrance anim
   · category filter chips · grid "load more"
   (ported from the design source of truth: solaire/js/main.js)
   ============================================================ */
(function () {
  "use strict";

  /* ---- Mobile nav drawer ---------------------------------- */
  function initDrawer() {
    var btn = document.getElementById("nav-toggle");
    var drawer = document.getElementById("nav-drawer");
    var overlay = document.getElementById("nav-overlay");
    var closeBtn = document.getElementById("nav-close");
    if (!btn || !drawer) return;

    function open() {
      drawer.classList.add("open");
      overlay.classList.remove("hidden");
      requestAnimationFrame(function () { overlay.style.opacity = "1"; });
      document.body.style.overflow = "hidden";
    }
    function close() {
      drawer.classList.remove("open");
      overlay.style.opacity = "0";
      document.body.style.overflow = "";
      setTimeout(function () { overlay.classList.add("hidden"); }, 350);
    }
    btn.addEventListener("click", open);
    closeBtn && closeBtn.addEventListener("click", close);
    overlay && overlay.addEventListener("click", close);
    drawer.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", close);
    });
  }

  /* ---- Game-row carousels --------------------------------- */
  function initCarousels() {
    document.querySelectorAll("[data-carousel]").forEach(function (root) {
      var track = root.querySelector("[data-track]");
      var prev = root.querySelector("[data-prev]");
      var next = root.querySelector("[data-next]");
      var dotsWrap = root.querySelector("[data-dots]");
      if (!track) return;

      // Dots imply full-page snapping (one viewport per slide).
      var paged = !!dotsWrap;
      var dots = [];

      function pageCount() {
        // A hidden carousel (e.g. the mobile/desktop variant not shown at the
        // current breakpoint) has clientWidth 0 — guard against dividing by it.
        if (!track.clientWidth) return 1;
        return Math.max(1, Math.round(track.scrollWidth / track.clientWidth));
      }
      function currentPage() {
        return Math.round(track.scrollLeft / track.clientWidth);
      }
      function step() {
        if (paged) return track.clientWidth;
        // Move exactly one card per click (card width + gap).
        var kids = track.children;
        if (kids.length > 1) return kids[1].offsetLeft - kids[0].offsetLeft;
        if (kids.length === 1) return kids[0].offsetWidth;
        return Math.max(track.clientWidth * 0.8, 240);
      }

      function buildDots() {
        if (!dotsWrap) return;
        dotsWrap.innerHTML = "";
        dots = [];
        var n = pageCount();
        dotsWrap.classList.toggle("hidden", n <= 1);
        for (var i = 0; i < n; i++) {
          (function (idx) {
            var b = document.createElement("button");
            b.type = "button";
            b.className = "h-2 rounded-full transition-all";
            b.setAttribute("aria-label", "Go to slide " + (idx + 1));
            b.addEventListener("click", function () {
              track.scrollTo({ left: idx * track.clientWidth, behavior: "smooth" });
            });
            dotsWrap.appendChild(b);
            dots.push(b);
          })(i);
        }
      }

      // Disable prev/next when there's nothing more to scroll to; sync dots.
      function update() {
        var maxScroll = track.scrollWidth - track.clientWidth;
        if (prev) prev.disabled = track.scrollLeft <= 1;
        if (next) next.disabled = track.scrollLeft >= maxScroll - 1;
        if (dots.length) {
          var cur = currentPage();
          dots.forEach(function (d, i) {
            var active = i === cur;
            d.classList.toggle("w-5", active);
            d.classList.toggle("bg-brand-orange", active);
            d.classList.toggle("w-2", !active);
            d.classList.toggle("bg-white/20", !active);
          });
        }
      }

      prev && prev.addEventListener("click", function () {
        track.scrollBy({ left: -step(), behavior: "smooth" });
      });
      next && next.addEventListener("click", function () {
        track.scrollBy({ left: step(), behavior: "smooth" });
      });
      track.addEventListener("scroll", update, { passive: true });
      window.addEventListener("resize", function () {
        buildDots();
        update();
      });
      buildDots();
      update();
    });
  }

  /* ---- Site popups: cookie policy + responsible gaming ------
     First visit shows the Cookie Policy modal; accepting it opens the
     Responsible Gaming gate. Accepting RG proceeds to the site; "I Do
     Not Accept" redirects the visitor away. Each acceptance is stored
     in localStorage so the popups don't reappear. */
  function initSitePopups() {
    var COOKIE_KEY = "solaire_cookie_ok";
    var RG_KEY = "solaire_rg_ok";

    function remembered(key) {
      try { return localStorage.getItem(key) === "1"; } catch (e) { return false; }
    }
    function remember(key) {
      try { localStorage.setItem(key, "1"); } catch (e) {}
    }
    function lock() { document.body.style.overflow = "hidden"; }
    function unlock() { document.body.style.overflow = ""; }
    function show(el) {
      if (!el) return;
      el.classList.remove("hidden");
      el.classList.add("flex");
      el.setAttribute("aria-hidden", "false");
      lock();
    }
    function hide(el) {
      if (!el) return;
      el.classList.add("hidden");
      el.classList.remove("flex");
      el.setAttribute("aria-hidden", "true");
    }

    var cookie = document.querySelector("[data-cookie-modal]");
    var rg = document.querySelector("[data-rg-modal]");
    var needCookie = cookie && !remembered(COOKIE_KEY);
    var needRg = rg && !remembered(RG_KEY);

    function showRg() {
      if (needRg) { show(rg); } else { unlock(); }
    }

    // Cookie: accept and close both dismiss, then open the RG gate.
    if (cookie) {
      cookie.querySelectorAll("[data-cookie-accept]").forEach(function (btn) {
        btn.addEventListener("click", function () {
          remember(COOKIE_KEY);
          hide(cookie);
          showRg();
        });
      });
    }

    // RG: accept → proceed; decline → redirect away.
    if (rg) {
      var accept = rg.querySelector("[data-rg-accept]");
      accept && accept.addEventListener("click", function () {
        remember(RG_KEY);
        hide(rg);
        unlock();
      });
      var decline = rg.querySelector("[data-rg-decline]");
      decline && decline.addEventListener("click", function () {
        window.location.href = rg.getAttribute("data-decline-url") || "https://www.google.com";
      });
    }

    // Cookie first, then responsible gaming.
    if (needCookie) { show(cookie); } else { showRg(); }
  }

  /* ---- Accordions ----------------------------------------- */
  function initAccordions() {
    function setOpen(item, open) {
      var panel = item.querySelector(".acc-panel");
      item.classList.toggle("open", open);
      // Move the orange outline to whichever item is active.
      item.classList.toggle("ring-orange/40", open);
      item.classList.toggle("ring-white/10", !open);
      if (!panel) return;
      panel.style.maxHeight = open ? panel.scrollHeight + "px" : "0px";
    }
    document.querySelectorAll("[data-accordion]").forEach(function (group) {
      var single = group.hasAttribute("data-single");
      group.querySelectorAll(".acc-item").forEach(function (item) {
        var head = item.querySelector("[data-acc-head]");
        if (!head) return;
        // Sync each panel with its initial open state on load.
        setOpen(item, item.classList.contains("open"));
        head.addEventListener("click", function () {
          var willOpen = !item.classList.contains("open");
          if (single && willOpen) {
            group.querySelectorAll(".acc-item.open").forEach(function (o) {
              if (o !== item) setOpen(o, false);
            });
          }
          setOpen(item, willOpen);
        });
      });
    });
    // Keep an open panel's height correct if the viewport reflows.
    window.addEventListener("resize", function () {
      document.querySelectorAll(".acc-item.open .acc-panel").forEach(function (p) {
        p.style.maxHeight = p.scrollHeight + "px";
      });
    });
  }

  /* ---- Category filter chips + grid "Load more" (AJAX) -----
     The grid is server-rendered with page 1; the filter chips and the
     "Load more" button fetch additional pages from admin-ajax so every
     game is reachable (there can be thousands, far more than the DOM
     holds). Filtering by child category re-queries the server rather
     than hiding already-rendered cards. Each `[data-grid]` owns its
     own state. */
  function gridState(grid) {
    if (!grid.__solaire) {
      grid.__solaire = {
        parent:  grid.getAttribute("data-parent") || "",
        filter:  "all",
        paged:   parseInt(grid.getAttribute("data-page") || "1", 10),
        step:    parseInt(grid.getAttribute("data-step") || "12", 10),
        hasMore: grid.getAttribute("data-has-more") === "1",
        loading: false
      };
    }
    return grid.__solaire;
  }

  function loadMoreBtnFor(grid) {
    return document.querySelector('[data-load-more][data-load-target="#' + grid.id + '"]')
      || (grid.parentElement && grid.parentElement.querySelector("[data-load-more]"));
  }

  /* Fetch a page of cards. When `replace` is true the grid is cleared
     first (filter switch); otherwise the results are appended (load more). */
  function fetchGames(grid, replace) {
    if (typeof window.SolaireAjax === "undefined") return;
    var st = gridState(grid);
    if (st.loading) return;
    st.loading = true;

    var btn = loadMoreBtnFor(grid);
    if (btn) { btn.setAttribute("aria-busy", "true"); btn.disabled = true; }
    if (replace) grid.setAttribute("aria-busy", "true");

    var body = new URLSearchParams({
      action:   "solaire_load_games",
      nonce:    window.SolaireAjax.nonce,
      parent:   st.parent,
      filter:   st.filter,
      paged:    String(st.paged),
      per_page: String(st.step)
    });

    fetch(window.SolaireAjax.url, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: body.toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        st.loading = false;
        if (btn) { btn.removeAttribute("aria-busy"); btn.disabled = false; }
        grid.removeAttribute("aria-busy");
        if (!res || !res.success) return;

        var html = res.data.html || "";
        if (replace) {
          grid.innerHTML = html
            || '<p class="col-span-full rounded-xl bg-white/[0.03] p-6 text-sm text-slatey ring-1 ring-white/5">No games found.</p>';
        } else if (html) {
          grid.insertAdjacentHTML("beforeend", html);
        }

        st.hasMore = !!res.data.hasMore;
        if (btn) btn.classList.toggle("hidden", !st.hasMore);
      })
      .catch(function () {
        st.loading = false;
        if (btn) { btn.removeAttribute("aria-busy"); btn.disabled = false; }
        grid.removeAttribute("aria-busy");
      });
  }

  function initFilters() {
    document.querySelectorAll("[data-filter-group]").forEach(function (group) {
      var grid = document.querySelector(group.getAttribute("data-filter-target") || "[data-grid]");
      if (!grid) return;
      var chips = group.querySelectorAll("[data-filter]");
      chips.forEach(function (chip) {
        chip.addEventListener("click", function () {
          chips.forEach(function (c) { c.classList.toggle("is-active", c === chip); });
          var st = gridState(grid);
          st.filter = chip.getAttribute("data-filter") || "all";
          st.paged  = 1;
          fetchGames(grid, true); // reset + replace with page 1 of the filter
        });
      });
    });
  }

  function initLoadMore() {
    document.querySelectorAll("[data-load-more]").forEach(function (btn) {
      var grid = document.querySelector(btn.getAttribute("data-load-target") || "[data-grid]");
      if (!grid) return;
      var st = gridState(grid);
      btn.classList.toggle("hidden", !st.hasMore);
      btn.addEventListener("click", function () {
        var s = gridState(grid);
        if (s.loading || !s.hasMore) return;
        s.paged += 1;
        fetchGames(grid, false); // append next page
      });
    });
  }

  /* ---- Entrance / scroll-in animations -------------------- */
  function initAnim() {
    var els = document.querySelectorAll("[data-anim]");
    if (!els.length) return;
    // Only now hide the elements — if this script never runs, content stays visible.
    document.documentElement.classList.add("anim-on");

    function reveal(el) {
      var delay = parseInt(el.getAttribute("data-anim-delay") || "0", 10);
      setTimeout(function () { el.classList.add("in"); }, delay);
    }

    if (!("IntersectionObserver" in window)) {
      els.forEach(reveal);
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { reveal(e.target); io.unobserve(e.target); }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
    els.forEach(function (el) { io.observe(el); });

    // Failsafe: reveal anything left hidden so content is never stuck invisible.
    setTimeout(function () {
      els.forEach(function (el) {
        if (!el.classList.contains("in")) el.classList.add("in");
      });
    }, 1100);
  }

  /* ---- Collapsible "Read More" blocks --------------------- */
  function initReadMore() {
    document.querySelectorAll("[data-readmore]").forEach(function (root) {
      var body = root.querySelector("[data-readmore-body]");
      var btn = root.querySelector("[data-readmore-toggle]");
      var fade = root.querySelector("[data-readmore-fade]");
      if (!body || !btn) return;

      // Collapsed height: either show the first N block elements
      // (data-readmore-blocks) or a fixed pixel height fallback.
      var blocks = parseInt(root.getAttribute("data-readmore-blocks") || "0", 10);
      var collapsed;
      if (blocks > 0 && body.children.length > blocks) {
        var lastShown = body.children[blocks - 1];
        collapsed = lastShown.offsetTop + lastShown.offsetHeight;
      } else {
        collapsed = parseInt(root.getAttribute("data-readmore-collapsed-height") || "220", 10);
      }

      // Short enough to fit — no toggle or fade needed.
      if (body.scrollHeight <= collapsed + 4) {
        btn.style.display = "none";
        if (fade) fade.style.display = "none";
        return;
      }

      var open = false;
      body.style.overflow = "hidden";
      body.style.maxHeight = collapsed + "px";
      body.style.transition = "max-height 0.4s ease";

      btn.addEventListener("click", function () {
        open = !open;
        if (open) {
          body.style.maxHeight = body.scrollHeight + "px";
          btn.textContent = btn.getAttribute("data-less") || "Read Less";
          if (fade) fade.style.opacity = "0";
        } else {
          // Pin to the current height first so the collapse transition fires.
          body.style.maxHeight = body.scrollHeight + "px";
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              body.style.maxHeight = collapsed + "px";
            });
          });
          btn.textContent = btn.getAttribute("data-more") || "Read More";
          if (fade) fade.style.opacity = "1";
        }
      });
    });
  }

  /* ---- Back to top ---------------------------------------- */
  function initBackToTop() {
    var btn = document.getElementById("back-to-top");
    if (!btn) return;

    var hidden = ["pointer-events-none", "opacity-0", "translate-y-3"];

    function toggle() {
      if (window.scrollY > 400) {
        btn.classList.remove.apply(btn.classList, hidden);
      } else {
        btn.classList.add.apply(btn.classList, hidden);
      }
    }

    toggle();
    window.addEventListener("scroll", toggle, { passive: true });
    btn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initDrawer();
    initCarousels();
    initSitePopups();
    initAccordions();
    initFilters();
    initLoadMore();
    initReadMore();
    initAnim();
    initBackToTop();
  });
})();
