<?php
/**
 * Solaire Feature Cards ("Explore the Best") — render.
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) {
    exit;
}

$overline   = $attributes['overline'] ?? '';
$heading    = $attributes['heading'] ?? '';
$subheading = $attributes['subheading'] ?? '';
$cards      = $attributes['cards'] ?? [];
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'relative mt-14 overflow-hidden bg-gradient-to-b from-[#2a1410] via-deep to-deep py-8 sm:py-16']); ?>>
  <div class="mx-auto max-w-shell px-4 text-center">
    <?php if ($overline) : ?>
      <p data-anim class="text-xs font-bold uppercase tracking-[0.28em] text-orange"><?php echo esc_html($overline); ?></p>
    <?php endif; ?>
    <?php if ($heading) : ?>
      <h2 data-anim data-anim-delay="80" class="mx-auto mt-3 max-w-2xl font-display text-2xl font-extrabold leading-tight sm:text-4xl"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>
    <?php if ($subheading) : ?>
      <p data-anim data-anim-delay="160" class="mx-auto mt-4 max-w-2xl text-sm text-slatey sm:text-base"><?php echo esc_html($subheading); ?></p>
    <?php endif; ?>

    <div class="mt-10 grid gap-5 text-left md:grid-cols-3">
      <?php foreach ($cards as $i => $card) :
          // Prefer an image picked in the editor (stored as { id, url }),
          // otherwise fall back to the default filename in imageUrl.
          if (!empty($card['image']['url'])) {
              $img = $card['image']['url'];
          } else {
              $img = solaire_img_src($card['imageUrl'] ?? '');
          }
          $iconImg = is_array($card['icon'] ?? null) ? ($card['icon']['url'] ?? '') : '';
          $title   = $card['title'] ?? '';
          $text    = $card['text'] ?? '';
          $ltext   = $card['linkText'] ?? '';
          $lurl    = $card['linkUrl'] ?? '';
          if (!$lurl) {
              $lurl = '#';
          }
          $delay = $i * 100;
      ?>
        <article data-anim <?php echo $delay ? 'data-anim-delay="' . esc_attr($delay) . '"' : ''; ?> class="overflow-hidden rounded-2xl bg-white/[0.03] ring-1 ring-white/10">
          <div class="relative aspect-[16/10] overflow-hidden">
            <?php if ($img) : ?>
              <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" class="h-full w-full object-cover" loading="lazy" />
            <?php else : ?>
              <div class="ph absolute inset-0"><?php echo esc_html($title); ?></div>
            <?php endif; ?>
            <?php if ($iconImg) : ?>
              <span class="absolute left-3 top-3 flex h-8 w-8 items-center justify-center rounded-md bg-black/55"><img src="<?php echo esc_url($iconImg); ?>" alt="" class="h-4 w-4 object-contain" /></span>
            <?php endif; ?>
          </div>
          <div class="p-5">
            <h3 class="font-display text-lg font-bold"><?php echo esc_html($title); ?></h3>
            <p class="mt-2 text-sm leading-relaxed text-slatey"><?php echo esc_html($text); ?></p>
            <?php if ($ltext) : ?>
              <a href="<?php echo esc_url($lurl); ?>" class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-orange transition hover:gap-2 hover:text-orange-bright"><?php echo esc_html($ltext); ?> <span>&rarr;</span></a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
