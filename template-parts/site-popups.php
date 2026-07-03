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

/* ---- Cookie content ----
   Body is the WYSIWYG "Cookie Policy Content" field (Gaming Guide options
   page). Title and button text stay on the Site Popups options page. */
$cookie_title   = get_field('so_cookie_title', 'option') ?: 'Cookie Policy';
$cookie_btn     = get_field('so_cookie_button_text', 'option') ?: 'I Accept';
$cookie_content = get_field('so_cookie_policy_content', 'option')
    ?: '<p>Our website uses cookies to ensure you get the best experience. By continuing to browse this website, you are agreeing to our use of cookies.</p>';

/* ---- Responsible gaming content ---- */
$rg_heading = get_field('so_rg_heading', 'option') ?: 'Responsible Gaming Guidelines';
$rg_terms_t = get_field('so_rg_terms_text', 'option') ?: 'Terms & conditions';
$rg_terms_u = get_field('so_rg_terms_url', 'option');
$rg_priv_t  = get_field('so_rg_privacy_text', 'option') ?: 'Privacy Policy';
$rg_priv_u  = get_field('so_rg_privacy_url', 'option');
$rg_accept  = get_field('so_rg_accept_text', 'option') ?: 'Accept';
$rg_decline = get_field('so_rg_decline_text', 'option') ?: 'I Do Not Accept';
$rg_decline_url = get_field('so_rg_decline_url', 'option') ?: 'https://www.google.com';
$rg_logo    = get_field('so_solaire_logo', 'option'); // Image array return

/* ---- Gaming Guide: dynamic RG body (Gaming Guide ACF options page) ---- */
$gg_contents     = get_field('so_gaming_guidelines_contents', 'option');
$gg_logo_info    = get_field('so_gaming_logo_info', 'option'); // "Funds or credits..." / "Keep online gaming private..." — sits inside the PAGCOR/21+ box
$gg_subparagraph = get_field('so_gaming_guidelines_subparagraph', 'option') ?: '<p>Solaire Online Terms &amp; conditions And Privacy Policy</p>';
?>

<?php if ($cookie_on) : ?>
  <!-- ===================== COOKIE POLICY ===================== -->
  <div data-cookie-modal class="fixed inset-0 z-[9985] hidden items-end justify-center bg-black/70 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="cookie-modal-title" aria-hidden="true">
    <div class="relative w-full max-w-4xl overflow-hidden rounded-t-2xl ring-1 ring-white/10 shadow-2xl" style="background: radial-gradient(100% 80% at center top, rgb(83, 29, 30) 0%, rgba(35, 38, 41, 0.46) 100%), rgb(35, 38, 41);">
      <div class="relative z-10 p-6 sm:px-10 sm:py-8">
        <button type="button" data-cookie-close aria-label="<?php esc_attr_e('Close', 'solaire'); ?>" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 text-white/70 transition hover:bg-white/20 hover:text-white"><?php echo solaire_icon('close', 'h-5 w-5', '2.5'); // phpcs:ignore ?></button>
        <h2 id="cookie-modal-title" class="text-center font-display text-lg font-bold text-modal-highlight sm:text-xl"><?php echo esc_html($cookie_title); ?></h2>
        <div class="mt-4 text-xs font-light text-[#FCE5CF] [&_a]:font-light [&_a]:text-modal-highlight [&_a]:underline [&_p]:mb-3 [&_p:last-child]:mb-0"><?php echo wp_kses_post($cookie_content); ?></div>
        <button type="button" data-cookie-accept class="btn-press bg-cta-orange mt-6 inline-flex w-full items-center justify-center rounded-xl px-8 py-3 font-display text-sm font-bold text-white sm:text-base"><?php echo esc_html($cookie_btn); ?></button>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($rg_on) : ?>
  <!-- ===================== RESPONSIBLE GAMING ===================== -->
  <!-- Anchored bottom-center on all breakpoints (items-end + justify-center). -->
  <div data-rg-modal data-decline-url="<?php echo esc_url($rg_decline_url); ?>" class="fixed inset-0 z-[9990] hidden items-end justify-center bg-black/80 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="rg-modal-title" aria-hidden="true">
    <!-- Bottom sheet: height follows the content, capped at the viewport. Short
         content leaves the empty space at the top (above the logo); tall content
         grows to the full height (no top gap) and scrolls internally only then. -->
    <div class="relative flex max-h-[100dvh] w-full max-w-3xl flex-col overflow-hidden rounded-t-2xl ring-1 ring-white/10 shadow-2xl sm:mx-0 sm:max-h-[90dvh] sm:rounded-2xl" style="background: radial-gradient(100% 80% at center top, rgb(83, 29, 30) 0%, rgba(35, 38, 41, 0.46) 100%), rgb(35, 38, 41);">
      <div class="relative z-10 min-h-0 flex-1 overflow-y-auto px-5 py-3 sm:px-8 sm:py-7">
        <div class="flex items-center justify-center gap-2.5 sm:gap-4">
          <?php if (is_array($rg_logo) && !empty($rg_logo['url'])) : ?>
            <img src="<?php echo esc_url($rg_logo['url']); ?>" alt="<?php echo esc_attr($rg_logo['alt'] ?: ''); ?>" class="h-10 w-auto shrink-0 sm:h-16" loading="lazy" />
          <?php endif; ?>
          <h2 id="rg-modal-title" class="font-display text-m font-medium leading-tight text-center text-modal-highlight sm:text-2xl"><?php echo esc_html($rg_heading); ?></h2>
        </div>

        <?php if ($gg_contents) : ?>
          <div class="mt-2 text-xs leading-snug text-white sm:mt-5 sm:text-sm sm:leading-relaxed [&_a]:font-light [&_a]:text-modal-highlight [&_a]:underline [&_strong]:text-[color:var(--modal-highlighted-text)] [&_b]:text-[color:var(--modal-highlighted-text)] [&_ul]:mt-1.5 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5 sm:[&_ul]:mt-3 sm:[&_ul]:space-y-2 [&_li]:marker:text-modal-highlight [&_li]:text-[#FCE5CF] [&_li]:font-light [&_p]:mb-1.5 sm:[&_p]:mb-3 [&_p:last-child]:mb-0"><?php echo wp_kses_post($gg_contents); ?></div>
        <?php endif; ?>

        <?php if (have_rows('so_gaming_guidelines_img_wrapper', 'option') || $gg_logo_info) : ?>
          <!-- PAGCOR / 21+ box: logos on top (#552324), "Funds or credits..." +
               "Keep online gaming private..." paragraphs below (#732626/40%) -->
          <div class="mt-3 overflow-hidden rounded-xl ring-1 ring-white/10 sm:mt-6">
            <?php if (have_rows('so_gaming_guidelines_img_wrapper', 'option')) : ?>
              <div class="flex items-center justify-around divide-x divide-white/15 bg-[#552324] px-2 py-2.5 sm:px-4 sm:py-5">
                <?php while (have_rows('so_gaming_guidelines_img_wrapper', 'option')) : the_row();
                    $img = get_sub_field('so_gaming_guidelines_img');
                    $src = '';
                    $alt = '';
                    if (is_array($img)) {
                        $src = $img['url'] ?? '';
                        $alt = $img['alt'] ?? '';
                    } elseif (is_numeric($img)) {
                        $src = wp_get_attachment_image_url($img, 'medium');
                        $alt = get_post_meta($img, '_wp_attachment_image_alt', true);
                    } elseif (is_string($img)) {
                        $src = $img;
                    }
                    if (!$src) {
                        continue;
                    }
                ?>
                  <div class="flex flex-1 items-center justify-center px-3 sm:px-6">
                    <img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr($alt); ?>" class="h-9 w-auto max-w-full object-contain sm:h-14" loading="lazy" />
                  </div>
                <?php endwhile; ?>
              </div>
            <?php endif; ?>

            <?php if ($gg_logo_info) : ?>
              <div class="space-y-1 bg-[#732626]/40 px-4 py-2.5 text-xs leading-snug text-white/70 sm:space-y-2 sm:px-6 sm:py-4 sm:text-sm sm:leading-relaxed [&_p]:mb-1 sm:[&_p]:mb-2 [&_p:last-child]:mb-0">
                <?php echo wp_kses_post($gg_logo_info); ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($gg_subparagraph) : ?>
          <!-- so_gaming_guidelines_subparagraph is now a WYSIWYG field, so the
               "Terms & conditions" / "Privacy Policy" links are expected to be
               typed/linked directly inside its content. -->
          <div class="mt-2 text-[11px] leading-snug text-white/55 sm:mt-4 sm:text-xs sm:leading-relaxed [&_a]:font-light [&_a]:text-modal-highlight [&_a]:underline [&_p]:mb-1 sm:[&_p]:mb-2 [&_p:last-child]:mb-0">
            <?php echo wp_kses_post($gg_subparagraph); ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="relative z-10 flex flex-col items-center gap-1.5 border-t border-white/10 px-5 py-2.5 sm:flex-row sm:gap-4 sm:px-8 sm:py-4">
        <button type="button" data-rg-accept class="btn-press bg-cta-orange inline-flex w-full items-center justify-center rounded-xl px-8 py-2.5 font-display text-sm font-bold text-white sm:flex-1 sm:py-3 sm:text-base"><?php echo esc_html($rg_accept); ?></button>
        <button type="button" data-rg-decline class="font-display text-sm font-semibold text-white/55 underline-offset-2 transition hover:text-white hover:underline sm:flex-1 sm:text-center"><?php echo esc_html($rg_decline); ?></button>
      </div>
    </div>
  </div>
<?php endif; ?>