<?php
/**
 * Register / login modal — mobile number capture.
 *
 * Opened by any element with the `.so-open-register` class (or
 * #so-register-trigger). Behaviour — attribution capture, phone
 * normalisation, redirect — lives in assets/js/register-modal.js.
 */

if (!defined('ABSPATH')) {
    exit;
}

/* Content comes from the "Login Modal Data" field group on the Gaming Guide
   options page. Images are ACF image arrays. */
if (!solaire_login_modal_enabled()) {
    return;
}

$reg_logo       = get_field('so_upper_logo', 'option') ?: get_field('so_solaire_logo', 'option');
$reg_title      = get_field('so_login_modal_title', 'option') ?: __('Login', 'solaire');
$reg_desc       = get_field('so_login_modal_description', 'option')
    ?: '<p>' . esc_html__('Solaire Rewards members can log in using their registered mobile number.', 'solaire') . '</p>';
// Unformatted (no wpautop) so the text sits inline beside the checkbox.
$reg_check      = get_field('so_login_modal_check_description', 'option', false)
    ?: 'I agree to <a href="https://games.solaireonline.com/terms-conditions/">The Terms Of Use</a> and <a href="https://games.solaireonline.com/privacy-policy/">Privacy Policy.</a>';
$reg_bg_desktop = get_field('so_login_modal_desktop_bg', 'option');
$reg_bg_mobile  = get_field('so_login_modal_mobile_bg', 'option');

$reg_bg_desktop_url = is_array($reg_bg_desktop) ? ($reg_bg_desktop['url'] ?? '') : '';
$reg_bg_mobile_url  = is_array($reg_bg_mobile) ? ($reg_bg_mobile['url'] ?? '') : '';
// Each breakpoint falls back to the other image when only one is set.
$reg_bg_desktop_url = $reg_bg_desktop_url ?: $reg_bg_mobile_url;
$reg_bg_mobile_url  = $reg_bg_mobile_url ?: $reg_bg_desktop_url;
?>

<!-- ===================== REGISTER / LOGIN ===================== -->
<!-- Mobile: full-screen sheet (terms pinned to the bottom). sm+: centered
     480x640 card (solaireonline.com's 1440px layout); from 1024px up,
     register-modal.js scales it with `zoom` so it stays ~1/3 of the viewport
     width, as on solaireonline.com. -->
<div id="so-reg-modal" class="fixed inset-0 z-[9995] hidden items-start justify-center overflow-y-auto bg-black/70 backdrop-blur-sm sm:p-4" role="dialog" aria-modal="true" aria-labelledby="so-reg-title" aria-hidden="true">
  <div class="<?php echo $reg_bg_mobile_url ? 'bg-black' : 'bg-modal-surface'; ?> relative flex min-h-dvh w-full flex-col shadow-2xl sm:my-auto sm:min-h-[640px] sm:w-[480px] sm:shrink-0 sm:rounded-2xl sm:ring-1 sm:ring-white/10" data-reg-panel>
    <?php if ($reg_bg_mobile_url) : ?>
      <!-- Background art: pinned to the top at full width; it fades into the
           panel's black base below. Mobile / desktop art swap at 640px. -->
      <div class="pointer-events-none absolute inset-0 overflow-hidden sm:rounded-2xl" aria-hidden="true">
        <picture>
          <source media="(min-width: 640px)" srcset="<?php echo esc_url($reg_bg_desktop_url); ?>" />
          <!-- The desktop art has the card's frame drawn into it, so from sm up it
               is stretched to the full card height (object-fill keeps the
               frame's side edges, which cover would crop). -->
          <img src="<?php echo esc_url($reg_bg_mobile_url); ?>" alt="" class="absolute inset-x-0 top-0 h-auto w-full select-none sm:h-full sm:object-fill" loading="lazy" />
        </picture>
      </div>
    <?php endif; ?>

    <div class="relative z-10 flex flex-1 flex-col px-3 pb-[max(2.5rem,env(safe-area-inset-bottom))] pt-[88px] sm:px-4 sm:pb-7 sm:pt-[85px]">
      <button type="button" id="so-reg-close" aria-label="<?php esc_attr_e('Close', 'solaire'); ?>" class="absolute right-3 top-3 flex h-6 w-6 items-center justify-center rounded-md bg-white/15 text-white transition hover:bg-white/25 sm:right-4 sm:top-4 sm:h-7 sm:w-7"><?php echo solaire_icon('close', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>

      <div class="flex justify-center">
        <?php if (is_array($reg_logo) && !empty($reg_logo['url'])) : ?>
          <img src="<?php echo esc_url($reg_logo['url']); ?>" alt="<?php echo esc_attr(($reg_logo['alt'] ?? '') ?: 'Solaire Online'); ?>" class="h-[108px] w-auto sm:h-[110px]" loading="lazy" />
        <?php else : ?>
          <span class="flex flex-col items-center leading-none">
            <span class="font-logo text-3xl font-semibold tracking-[0.2em] text-modal-highlight">SOLAIRE</span>
            <span class="mt-1 font-logo text-xs tracking-[0.4em] text-modal-highlight">ONLINE</span>
          </span>
        <?php endif; ?>
      </div>

      <h2 id="so-reg-title" class="mt-24 text-center font-display sm:mt-11 text-lg font-bold text-white"><?php echo esc_html($reg_title); ?></h2>
      <div class="mt-3 text-center text-[15px] leading-snug text-white/85 [&_a]:text-modal-highlight [&_a]:underline [&_p]:mb-2 [&_p:last-child]:mb-0"><?php echo wp_kses_post($reg_desc); ?></div>

      <!-- Phone row. The country picker (rendered by register-modal.js from
           assets/js/country-codes.js) drops down under the field on sm+, and
           on mobile becomes a "Country / Region" sheet over the lower panel —
           positioned against the content wrapper, since this row is only
           `relative` from sm up. -->
      <div class="mt-8 flex items-start gap-2.5 sm:relative sm:gap-2">
        <button type="button" id="so-reg-country" aria-haspopup="listbox" aria-expanded="false" aria-controls="so-reg-country-list" class="flex h-12 w-[130px] shrink-0 sm:h-11 items-center gap-2 rounded-lg bg-[#1c1c1e] px-2.5 text-base font-medium text-white ring-1 ring-white/20 transition hover:ring-white/40 aria-expanded:ring-white/60">
          <img data-cc-flag src="https://flagcdn.com/ph.svg" alt="" class="h-[18px] w-[26px] shrink-0 object-cover" />
          <span data-cc-dial>+63</span>
          <?php echo solaire_icon('chevron', 'ml-auto h-4 w-4 text-white/80 transition-transform', '2.5'); // phpcs:ignore ?>
        </button>

        <div data-cc-backdrop class="absolute inset-0 z-20 hidden bg-black/75 sm:!hidden"></div>
        <div id="so-reg-country-picker" class="absolute inset-x-0 bottom-0 top-[36%] z-30 hidden flex-col bg-black shadow-[0_-24px_40px_rgba(0,0,0,0.85)] sm:inset-x-auto sm:bottom-auto sm:left-0 sm:top-full sm:mt-1.5 sm:w-[390px] sm:max-w-[calc(100vw-4rem)] sm:rounded-xl sm:shadow-2xl sm:ring-1 sm:ring-white/15">
          <div class="relative flex items-center justify-center px-4 pb-3 pt-5 sm:hidden">
            <p class="font-display text-lg font-bold text-white"><?php esc_html_e('Country / Region', 'solaire'); ?></p>
            <button type="button" data-cc-close aria-label="<?php esc_attr_e('Close', 'solaire'); ?>" class="absolute right-3 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md bg-white/15 text-white"><?php echo solaire_icon('close', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
          </div>
          <ul id="so-reg-country-list" role="listbox" aria-label="<?php esc_attr_e('Country / Region', 'solaire'); ?>" class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:max-h-[264px] sm:px-2 sm:py-2"></ul>
        </div>
        <!-- Number column: input (with clear button) + inline error, which
             lines up under the input like solaireonline.com. -->
        <div class="min-w-0 flex-1">
          <div class="relative">
            <label for="so-reg-phone" class="sr-only"><?php esc_html_e('Mobile number', 'solaire'); ?></label>
            <input type="tel" id="so-reg-phone" class="phoneInput h-12 w-full rounded-lg bg-[#1c1c1e] pl-3 pr-10 text-base text-white ring-1 ring-white/20 placeholder:text-white/40 focus:outline-none focus:ring-orange data-[invalid]:!ring-[#f04438] sm:h-11" placeholder="9XXX XXX XXX" inputmode="numeric" autocomplete="tel" aria-describedby="so-reg-error" />
            <button type="button" id="so-reg-clear" aria-label="<?php esc_attr_e('Clear number', 'solaire'); ?>" class="absolute right-2 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-white/80 transition hover:text-white"><?php echo solaire_icon('close', 'h-4 w-4', '2.5'); // phpcs:ignore ?></button>
          </div>
          <p id="so-reg-error" role="alert" class="mt-1.5 hidden items-start gap-1.5 text-[13px] leading-snug text-[#f04438] sm:text-sm">
            <svg class="mt-px h-4 w-4 shrink-0" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="8" fill="currentColor"/><path d="M8 4v5" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/><circle cx="8" cy="11.75" r="1.05" fill="#fff"/></svg>
            <span data-reg-error-text></span>
          </p>
        </div>
      </div>

      <button type="button" id="so-reg-submit" class="btn-press mb-10 mt-8 inline-flex h-12 w-full items-center justify-center rounded-lg bg-[#3a3a3a] px-8 font-display sm:mb-0 text-base font-bold text-white/40 transition disabled:cursor-not-allowed" disabled><?php esc_html_e('Submit', 'solaire'); ?></button>

      <div class="mt-auto border-t border-white/10 pt-6">
        <label class="flex cursor-pointer items-center gap-2 text-[13px] text-white/80">
          <input type="checkbox" id="so-reg-terms" class="h-4 w-4 shrink-0 cursor-pointer rounded accent-orange" checked />
          <span class="[&_a]:text-gold-soft [&_a]:underline [&_a]:underline-offset-2"><?php echo wp_kses_post($reg_check); ?></span>
        </label>
      </div>
    </div>
  </div>
</div>
