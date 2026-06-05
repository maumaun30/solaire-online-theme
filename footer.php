<?php
/**
 * Site footer — Solaire Online chrome.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- ============================ FOOTER ============================ -->
<footer class="footer-glow relative bg-deep">
  <div class="relative z-10 mx-auto max-w-shell px-4 py-14">
    <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
      <div class="lg:col-span-2 lg:max-w-sm">
        <div class="flex flex-col leading-none">
          <span class="font-logo text-2xl font-semibold tracking-[0.3em]">SOLAIRE</span>
          <span class="font-logo text-[10px] tracking-[0.5em] text-white/60">ONLINE</span>
        </div>
        <p class="mt-5 text-sm leading-relaxed text-slatey">Spin the Reels and Try Your Luck Playing Live Slots on Solaire Online Philippines!</p>
        <p class="mt-6 text-sm font-bold">Solaire Online Communities</p>
        <div class="mt-3 flex gap-3">
          <a href="#" aria-label="Help" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-orange hover:text-white"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.7.4-1 .9-1 1.7M12 17h.01"/></svg></a>
          <a href="#" aria-label="Support" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-orange hover:text-white"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 13a8 8 0 0 1 16 0v4a2 2 0 0 1-2 2h-1v-6h3M4 13v4a2 2 0 0 0 2 2h1v-6H4"/></svg></a>
          <a href="#" aria-label="Facebook" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-orange hover:text-white"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M14 9h3V5h-3c-2.2 0-4 1.8-4 4v2H7v4h3v6h4v-6h3l1-4h-4V9c0-.6.4-1 1-1z"/></svg></a>
          <a href="#" aria-label="Instagram" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-orange hover:text-white"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17 7h.01"/></svg></a>
          <a href="#" aria-label="X" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-orange hover:text-white"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M17 3h3l-7 8 8 10h-6l-5-6-5 6H2l8-9L2 3h6l4 5z"/></svg></a>
        </div>
      </div>
      <div>
        <h3 class="inline-block border-b-2 border-orange pb-1 font-display text-base font-bold">Legal</h3>
        <ul class="mt-4 flex flex-col gap-3 text-sm text-slatey">
          <li><a href="#" class="transition hover:text-white">Responsible Gaming</a></li>
          <li><a href="#" class="transition hover:text-white">Privacy Policy</a></li>
          <li><a href="#" class="transition hover:text-white">Terms &amp; Conditions</a></li>
          <li><a href="#" class="font-bold text-white transition hover:text-orange">Bonus Terms</a></li>
        </ul>
      </div>
      <div>
        <h3 class="inline-block border-b-2 border-orange pb-1 font-display text-base font-bold">Support</h3>
        <ul class="mt-4 flex flex-col gap-3 text-sm text-slatey">
          <li><a href="#" class="transition hover:text-white">About us</a></li>
          <li><a href="#" class="transition hover:text-white">Help</a></li>
          <li><a href="#" class="transition hover:text-white">Contact Us</a></li>
          <li><a href="#" class="transition hover:text-white">FAQ</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="mx-auto flex max-w-shell flex-col items-center justify-between gap-4 px-4 py-6 sm:flex-row">
      <div class="flex items-center gap-2 font-display text-lg font-bold tracking-wide text-white/90"><span class="text-orange">&#10148;</span> PAGCOR</div>
      <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-red-600 text-xs font-bold text-red-500">21+</span>
        <span class="text-[11px] font-semibold uppercase leading-tight tracking-wide text-slatey">Gambling can be addictive<br />Know when to stop</span>
      </div>
    </div>
  </div>
  <div class="border-t border-white/10 py-5 text-center text-xs text-white/40">Copyright &copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. All Rights Reserved.</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
