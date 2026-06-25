<?php
/**
 * Site popups — cookie policy + responsible gaming guidelines.
 *
 * Flow (handled by assets/js/solaire.js):
 *   1. Cookie Policy modal shows on first visit. "I Accept" (or close) →
 *      remembered in localStorage, then the RG modal opens.
 *   2. Responsible Gaming Guidelines modal gates entry: "Accept" →
 *      remembered, proceed to the site. "I Do Not Accept" → redirected away.
 *
 * Content is editable on the "Site Popups" ACF options page.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Highlight *asterisk-wrapped* text in orange, escaping the rest.
 */
function solaire_popup_emphasis($text)
{
    $parts = preg_split('/(\*[^*]+\*)/', (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out   = '';
    foreach ($parts as $part) {
        if (strlen($part) > 1 && $part[0] === '*' && substr($part, -1) === '*') {
            $out .= '<span class="font-bold text-orange">' . esc_html(substr($part, 1, -1)) . '</span>';
        } else {
            $out .= esc_html($part);
        }
    }
    return $out;
}

$cookie_on = get_field('so_cookie_enabled', 'option');
$rg_on     = get_field('so_rg_enabled', 'option');
$cookie_on = ($cookie_on === null || $cookie_on === '') ? true : (bool) $cookie_on;
$rg_on     = ($rg_on === null || $rg_on === '') ? true : (bool) $rg_on;

/* ---- Cookie content ---- */
$cookie_title = get_field('so_cookie_title', 'option') ?: 'Cookie Policy';
$cookie_msg   = get_field('so_cookie_message', 'option') ?: 'Our website uses cookies to ensure you get the best experience. By continuing to browse this website, you are agreeing to our use of cookies. Please read our updated';
$cookie_l1    = get_field('so_cookie_link_text', 'option') ?: 'Privacy Policy';
$cookie_u1    = get_field('so_cookie_link_url', 'option');
$cookie_l2    = get_field('so_cookie_link2_text', 'option');
$cookie_u2    = get_field('so_cookie_link2_url', 'option');
$cookie_btn   = get_field('so_cookie_button_text', 'option') ?: 'I Accept';

/* ---- Responsible gaming content ---- */
$rg_heading = get_field('so_rg_heading', 'option') ?: 'Responsible Gaming Guidelines';
$rg_intro   = get_field('so_rg_intro', 'option') ?: 'The following persons are *NOT ALLOWED* to register and/or play in the online gaming website:';
$rg_footer  = get_field('so_rg_footer', 'option');
$rg_pagcor  = get_field('so_rg_pagcor_image', 'option');
$rg_age     = get_field('so_rg_age_image', 'option');
$rg_terms_t = get_field('so_rg_terms_text', 'option') ?: 'Terms & Conditions';
$rg_terms_u = get_field('so_rg_terms_url', 'option');
$rg_priv_t  = get_field('so_rg_privacy_text', 'option') ?: 'Privacy Policy';
$rg_priv_u  = get_field('so_rg_privacy_url', 'option');
$rg_accept  = get_field('so_rg_accept_text', 'option') ?: 'Accept';
$rg_decline = get_field('so_rg_decline_text', 'option') ?: 'I Do Not Accept';
$rg_decline_url = get_field('so_rg_decline_url', 'option') ?: 'https://www.google.com';

$rg_items = get_field('so_rg_items', 'option');
if (!$rg_items) {
    // PAGCOR responsible-gaming defaults when none are configured.
    $rg_items = [
        ['text' => 'Government official or employee connected directly with the operation of the Government or any of its agencies', 'url' => ''],
        ['text' => 'Member of the Armed Forces of the Philippines, including the Army, Navy, Air Force or the Philippine National Police', 'url' => ''],
        ['text' => 'Person under 21 years of age', 'url' => ''],
        ['text' => 'Persons included in the National Database of Restricted Persons (NDRP)', 'url' => ''],
        ['text' => 'Gaming employees, unless on official duty', 'url' => ''],
    ];
}
?>

<?php if ($cookie_on) : ?>
  <!-- ===================== COOKIE POLICY ===================== -->
  <div data-cookie-modal class="fixed inset-0 z-[9985] hidden items-end justify-center bg-black/70 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="cookie-modal-title" aria-hidden="true">
    <div class="relative w-full max-w-lg overflow-hidden rounded-t-2xl bg-deep ring-1 ring-orange/40 shadow-2xl">
      <div class="pointer-events-none absolute -top-16 left-1/2 h-40 w-40 -translate-x-1/2 rounded-full bg-orange/30 blur-[80px]"></div>
      <div class="relative z-10 p-6 sm:p-7">
        <button type="button" data-cookie-accept aria-label="<?php esc_attr_e('Close', 'solaire'); ?>" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-md text-white/60 transition hover:bg-white/10 hover:text-white"><?php echo solaire_icon('close', 'h-5 w-5', '2.5'); // phpcs:ignore ?></button>
        <h2 id="cookie-modal-title" class="text-center font-display text-lg font-bold text-orange sm:text-xl"><?php echo esc_html($cookie_title); ?></h2>
        <p class="mt-4 text-center text-xs leading-relaxed text-white/70 sm:text-sm">
          <?php echo esc_html($cookie_msg); ?>
          <?php if ($cookie_u1) : ?>
            <a href="<?php echo esc_url($cookie_u1); ?>" class="font-semibold text-orange transition hover:text-white"><?php echo esc_html($cookie_l1); ?></a><?php endif; ?><?php if ($cookie_u2) : ?> <?php esc_html_e('and', 'solaire'); ?> <a href="<?php echo esc_url($cookie_u2); ?>" class="font-semibold text-orange transition hover:text-white"><?php echo esc_html($cookie_l2); ?></a><?php endif; ?> <?php esc_html_e('to understand how we treat your Personal Data.', 'solaire'); ?>
        </p>
        <button type="button" data-cookie-accept class="btn-press bg-brand-orange mt-5 inline-flex w-full items-center justify-center rounded-xl px-8 py-3 font-display text-sm font-bold text-white sm:text-base"><?php echo esc_html($cookie_btn); ?></button>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($rg_on) : ?>
  <!-- ===================== RESPONSIBLE GAMING ===================== -->
  <div data-rg-modal data-decline-url="<?php echo esc_url($rg_decline_url); ?>" class="fixed inset-0 z-[9990] hidden items-end justify-center bg-black/80 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="rg-modal-title" aria-hidden="true">
    <div class="relative flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-t-2xl bg-[#2a1715] ring-1 ring-orange/40 shadow-2xl">
      <div class="pointer-events-none absolute -top-20 left-1/2 h-48 w-48 -translate-x-1/2 rounded-full bg-brandred/40 blur-[90px]"></div>
      <div class="relative z-10 overflow-y-auto px-6 py-7 sm:px-8">
        <div class="text-center">
          <span class="mx-auto flex h-12 w-12 items-center justify-center text-orange">
            <svg viewBox="0 0 24 24" class="h-9 w-9" fill="currentColor" aria-hidden="true"><path d="M12 2c.5 3-1.5 4.5-2.8 6C7.7 9.7 7 11.3 7 13a5 5 0 0 0 10 0c0-1.7-.8-3.2-2-4.5C13.2 6.7 14 4 12 2Zm0 16a2.5 2.5 0 0 1-2.5-2.5c0-1.2.8-2 1.5-2.8.4 1 1.2 1.4 1.8 2 .5.5.7 1 .7 1.8A2 2 0 0 1 12 18Z"/></svg>
          </span>
          <h2 id="rg-modal-title" class="mt-2 font-display text-xl font-extrabold text-orange sm:text-2xl"><?php echo esc_html($rg_heading); ?></h2>
        </div>

        <p class="mt-5 text-xs leading-relaxed text-white/80 sm:text-sm"><?php echo solaire_popup_emphasis($rg_intro); // phpcs:ignore ?></p>

        <ul class="mt-3 space-y-2 text-xs leading-relaxed text-white/75 sm:text-sm">
          <?php foreach ($rg_items as $item) :
              $txt = $item['text'] ?? '';
              if ($txt === '') {
                  continue;
              }
              $url = $item['url'] ?? '';
          ?>
            <li class="flex gap-2">
              <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-orange"></span>
              <?php if ($url) : ?>
                <a href="<?php echo esc_url($url); ?>" class="font-semibold text-orange transition hover:text-white"><?php echo esc_html($txt); ?></a>
              <?php else : ?>
                <span><?php echo esc_html($txt); ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <?php if ($rg_pagcor || $rg_age) : ?>
          <div class="mt-6 flex flex-wrap items-center justify-center gap-8 border-y border-white/10 py-5 sm:justify-between">
            <?php if ($rg_pagcor) : ?>
              <img src="<?php echo esc_url($rg_pagcor['url']); ?>" alt="<?php echo esc_attr($rg_pagcor['alt'] ?: 'PAGCOR'); ?>" class="h-10 w-auto max-w-[180px] object-contain" loading="lazy" />
            <?php endif; ?>
            <?php if ($rg_age) : ?>
              <img src="<?php echo esc_url($rg_age['url']); ?>" alt="<?php echo esc_attr($rg_age['alt'] ?: '21+'); ?>" class="h-12 w-auto max-w-[200px] object-contain" loading="lazy" />
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($rg_footer) : ?>
          <p class="mt-5 text-[11px] leading-relaxed text-white/55 sm:text-xs"><?php echo esc_html($rg_footer); ?></p>
        <?php endif; ?>

        <?php if ($rg_terms_u || $rg_priv_u) : ?>
          <p class="mt-2 text-[11px] text-white/55 sm:text-xs">
            <?php esc_html_e('Learn more about', 'solaire'); ?>
            <?php if ($rg_terms_u) : ?><a href="<?php echo esc_url($rg_terms_u); ?>" class="font-semibold text-orange transition hover:text-white"><?php echo esc_html($rg_terms_t); ?></a><?php endif; ?>
            <?php if ($rg_terms_u && $rg_priv_u) : ?><?php esc_html_e('and', 'solaire'); ?><?php endif; ?>
            <?php if ($rg_priv_u) : ?> <a href="<?php echo esc_url($rg_priv_u); ?>" class="font-semibold text-orange transition hover:text-white"><?php echo esc_html($rg_priv_t); ?></a><?php endif; ?>.
          </p>
        <?php endif; ?>
      </div>

      <div class="relative z-10 flex flex-col items-center gap-3 border-t border-white/10 bg-black/20 px-6 py-4 sm:flex-row sm:gap-4 sm:px-8">
        <button type="button" data-rg-accept class="btn-press bg-brand-orange inline-flex w-full items-center justify-center rounded-xl px-8 py-3 font-display text-sm font-bold text-white sm:flex-1 sm:text-base"><?php echo esc_html($rg_accept); ?></button>
        <button type="button" data-rg-decline class="font-display text-sm font-semibold text-white/55 underline-offset-2 transition hover:text-white hover:underline sm:shrink-0"><?php echo esc_html($rg_decline); ?></button>
      </div>
    </div>
  </div>
<?php endif; ?>
