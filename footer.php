<?php
/**
 * Site footer — Solaire Online chrome.
 */

if (!defined('ABSPATH')) {
    exit;
}

/* Footer Data — ACF fields on the "Site Settings" options page. */
$footer_description = get_field('so_footer_description', 'option');
$footer_social      = get_field('so_footer_social', 'option');
$footer_responsible = get_field('so_footer_responsible_gaming', 'option');
?>
<!-- ============================ FOOTER ============================ -->
<footer class="footer-glow relative bg-deep">
  <div class="relative z-10 mx-auto max-w-shell px-4 py-14">
    <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
      <div class="lg:col-span-2 lg:max-w-sm">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex flex-col leading-none">
          <?php if (has_custom_logo()) : ?>
            <?php the_custom_logo(); ?>
          <?php else : ?>
            <span class="font-logo text-2xl font-semibold tracking-[0.3em]">SOLAIRE</span>
            <span class="font-logo text-[10px] tracking-[0.5em] text-white/60">ONLINE</span>
          <?php endif; ?>
        </a>
        <?php if ($footer_description) : ?>
          <p class="mt-5 text-sm leading-relaxed"><?php echo esc_html($footer_description); ?></p>
        <?php endif; ?>
        <?php if ($footer_social) : ?>
          <p class="mt-6 text-sm font-bold">Solaire Online Communities</p>
          <div class="mt-3 flex flex-wrap gap-3">
            <?php foreach ($footer_social as $social) :
                $icon = $social['so_social_media_icon'] ?? null;
                $url  = $social['so_social_media_url'] ?? '';
                if (!$icon) {
                    continue;
                }
            ?>
              <a href="<?php echo esc_url($url ?: '#'); ?>"<?php echo $url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-white/10 text-white/80 transition hover:bg-orange hover:text-white">
                <img src="<?php echo esc_url($icon['url']); ?>" alt="<?php echo esc_attr($icon['alt'] ?: ''); ?>" class="h-4 w-4 object-contain" loading="lazy" />
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php if (has_nav_menu('footer-legal')) : ?>
        <div>
          <h3 class="inline-block border-b-2 border-orange pb-1 font-display text-base font-bold">Legal</h3>
          <?php wp_nav_menu([
              'theme_location' => 'footer-legal',
              'container'      => false,
              'menu_class'     => 'footer-menu mt-4 flex flex-col gap-3 text-sm',
              'depth'          => 1,
              'fallback_cb'    => false,
          ]); ?>
        </div>
      <?php endif; ?>
      <?php if (has_nav_menu('footer-support')) : ?>
        <div>
          <h3 class="inline-block border-b-2 border-orange pb-1 font-display text-base font-bold">Support</h3>
          <?php wp_nav_menu([
              'theme_location' => 'footer-support',
              'container'      => false,
              'menu_class'     => 'footer-menu mt-4 flex flex-col gap-3 text-sm',
              'depth'          => 1,
              'fallback_cb'    => false,
          ]); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php if ($footer_responsible) : ?>
    <div class="border-t border-white/10 bg-white/[0.02]">
      <div class="mx-auto flex max-w-3xl flex-wrap items-center justify-center gap-10 px-4 py-5 sm:justify-between">
        <?php foreach ($footer_responsible as $row) :
            $img = $row['so_responsible_gaming_image'] ?? null;
            if (!$img) {
                continue;
            }
        ?>
          <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt'] ?: ''); ?>" class="h-9 w-auto max-w-[200px] object-contain" loading="lazy" />
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
  <div class="border-t border-white/10 py-5 text-center text-xs text-white/40">Copyright &copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. All Rights Reserved.</div>
</footer>

<?php get_template_part('template-parts/site-popups'); ?>

<?php wp_footer(); ?>
</body>
</html>
