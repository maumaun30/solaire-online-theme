# Solaire Online — WordPress Theme

Premium live casino & luxury slots theme. Classic PHP templates + dynamic
Gutenberg blocks + a Games custom post type, styled with Tailwind CSS v3.
Ported 1:1 from the Claude Design handoff (`solaire/` HTML prototypes).

---

## Stack

- Classic PHP templates (`header.php`, `footer.php`, `front-page.php`,
  `archive-game.php`, `single-game.php`, `index.php`, `page.php`)
- Dynamic, server-rendered Gutenberg blocks (React `edit` + PHP `render.php`)
- Games CPT (`game`, archive `/games/`) + `game_category` taxonomy
- Advanced Custom Fields (bundled) for game metadata
- Tailwind CSS v3 with the Solaire token set + ported utilities

---

## Install / build

```bash
npm install
npm run build      # builds every block + compiles main.min.css & critical.min.css
npm run dev        # watch mode (blocks + css)
```

Then activate **Solaire Online** in WordPress. On first admin load the theme:

- registers the `game` CPT + `game_category` terms,
- seeds sample games (using the bundled artwork) so every page is populated,
- creates a **Home** page from the homepage blocks and sets it as the front page.

> Built assets (`assets/**/build/`, `*.min.css`) are gitignored — run
> `npm run build` after cloning.

---

## Pages → templates

| Design page          | Template            | Notes |
|----------------------|---------------------|-------|
| `solaire/index.html` | `front-page.php`    | Composed from Solaire blocks (see below) |
| `solaire/live-slots.html` | `archive-game.php` | CPT archive + category filter chips + load-more + FAQ |
| `solaire/coin-combo.html` | `single-game.php` | Single game: hero, stats, gold CTA, more games, about, rules |

## Homepage blocks (`solaire/` namespace)

`hero-banner`, `category-tiles`, `game-row` (×3, queries the CPT by category),
`ranking-list`, `feature-cards`, `benefits-row`, `convenience-hook`,
plus `faq-guide` (used on the archive). All are editable in the block editor
and previewed via `ServerSideRender`. The full homepage is also available as the
**Solaire Homepage** block pattern.

## Interactions (`assets/js/solaire.js`)

Mobile drawer, carousel arrows, single-open accordions, IntersectionObserver
entrance animation (resting state stays visible — degrades gracefully without JS /
under reduced-motion / in print), category filter chips and grid load-more.

## Creating a block

```bash
npm run create-block my-block
```

---

## Notes

- **Gotham** is a licensed font — drop `Gotham-{Book,Medium,Bold}.woff2` into
  `assets/fonts/` to enable it; Montserrat is the fallback.
- Block namespace is `solaire/`; auto-registered from `assets/js/blocks/*`.

## License

GPL-2.0-or-later
