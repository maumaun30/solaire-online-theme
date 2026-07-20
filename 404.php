<?php
/**
 * The template for displaying 404 (not found) pages.
 *
 * Same structure as the reference 404 — headline, subtitle, description and a
 * "back home" button — re-skinned in the Solaire Online dark + orange/gold theme.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="site-main">
  <section class="relative overflow-hidden">

    <!-- Ambient brand glow behind the numerals. 
    <div class="pointer-events-none absolute left-1/2 top-1/3 h-96 w-96 -translate-x-1/2 rounded-full bg-orange/25 blur-[120px]" aria-hidden="true"></div>-->

    <div class="relative mx-auto flex min-h-[70vh] max-w-shell flex-col items-center justify-center px-4 py-24 text-center">

      <span class="block bg-gradient-to-b from-gold via-orange to-brandred bg-clip-text font-display text-[110px] font-extrabold leading-none tracking-tight text-transparent sm:text-[160px]">404</span>

      <h1 class="mt-4 font-display text-3xl font-bold uppercase tracking-wide text-white sm:text-4xl">
        <?php esc_html_e('Oops!', 'solaire'); ?>
      </h1>

      <h2 class="mt-3 font-display text-sm font-semibold uppercase tracking-[0.2em] text-orange">
        <?php esc_html_e('404 - Page Not Found', 'solaire'); ?>
      </h2>

      <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed sm:text-base">
        <?php esc_html_e('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'solaire'); ?>
      </p>

      <a href="<?php echo esc_url(home_url('/')); ?>"
         class="btn-press mt-8 inline-flex items-center justify-center rounded-lg bg-brand-orange px-8 py-3.5 font-display text-sm font-bold uppercase tracking-wide text-white shadow-lg shadow-orange/30 ring-1 ring-white/10">
        <?php esc_html_e('Go back to homepage', 'solaire'); ?>
      </a>

    </div>
  </section>
</main>
<?php
get_footer();
