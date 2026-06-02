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
      if (!track) return;
      function step() {
        // scroll by roughly one viewport of the track
        return Math.max(track.clientWidth * 0.8, 240);
      }
      prev && prev.addEventListener("click", function () {
        track.scrollBy({ left: -step(), behavior: "smooth" });
      });
      next && next.addEventListener("click", function () {
        track.scrollBy({ left: step(), behavior: "smooth" });
      });
    });
  }

  /* ---- Accordions ----------------------------------------- */
  function initAccordions() {
    document.querySelectorAll("[data-accordion]").forEach(function (group) {
      var single = group.hasAttribute("data-single");
      group.querySelectorAll(".acc-item").forEach(function (item) {
        var head = item.querySelector("[data-acc-head]");
        if (!head) return;
        head.addEventListener("click", function () {
          var isOpen = item.classList.contains("open");
          if (single) {
            group.querySelectorAll(".acc-item.open").forEach(function (o) {
              if (o !== item) o.classList.remove("open");
            });
          }
          item.classList.toggle("open", !isOpen);
        });
      });
    });
  }

  /* ---- Category filter chips + grid "Load more" -----------
     A single visibility model drives both: an item is shown when it
     matches the active category filter AND falls within the current
     "shown" cap. Each grid (`[data-grid]`) owns its own state. */
  function gridState(grid) {
    if (!grid.__solaire) {
      grid.__solaire = {
        filter: "all",
        step: parseInt(grid.getAttribute("data-step") || "12", 10)
      };
      grid.__solaire.shown = grid.__solaire.step;
    }
    return grid.__solaire;
  }

  function renderGrid(grid) {
    var st = gridState(grid);
    var loadMore = document.querySelector('[data-load-more][data-load-target="#' + grid.id + '"]')
      || (grid.parentElement && grid.parentElement.querySelector("[data-load-more]"));
    var shownCount = 0, matchCount = 0;
    grid.querySelectorAll("[data-grid-item]").forEach(function (item) {
      var cats = (item.getAttribute("data-category") || "").split(/\s+/);
      var matches = st.filter === "all" || cats.indexOf(st.filter) !== -1;
      if (matches) {
        matchCount++;
        var withinCap = shownCount < st.shown;
        item.classList.toggle("hidden", !withinCap);
        if (withinCap) shownCount++;
      } else {
        item.classList.add("hidden");
      }
    });
    if (loadMore) loadMore.classList.toggle("hidden", matchCount <= st.shown);
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
          st.filter = chip.getAttribute("data-filter");
          st.shown = st.step; // reset cap when the filter changes
          renderGrid(grid);
        });
      });
    });
  }

  function initLoadMore() {
    document.querySelectorAll("[data-grid]").forEach(function (grid) { renderGrid(grid); });
    document.querySelectorAll("[data-load-more]").forEach(function (btn) {
      var grid = document.querySelector(btn.getAttribute("data-load-target") || "[data-grid]");
      if (!grid) return;
      btn.addEventListener("click", function () {
        var st = gridState(grid);
        st.shown += st.step;
        renderGrid(grid);
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

  document.addEventListener("DOMContentLoaded", function () {
    initDrawer();
    initCarousels();
    initAccordions();
    initFilters();
    initLoadMore();
    initAnim();
  });
})();
