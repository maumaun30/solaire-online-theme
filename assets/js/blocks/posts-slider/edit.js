import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import {
  PanelBody,
  SelectControl,
  CheckboxControl,
  Spinner,
  Button,
  Notice,
  ToggleControl,
  TextControl,
  RangeControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

// Post types we never want to offer in the picker.
const EXCLUDED_TYPES = [
  'attachment',
  'wp_block',
  'wp_template',
  'wp_template_part',
  'wp_navigation',
  'wp_font_face',
  'wp_font_family',
];

// Hard ceiling on cards per view. The slide maths and the card proportions are
// only defined for 1–3; anything past that squashes the cards, so the value is
// clamped on the way in rather than trusted from the control.
const MAX_PER_VIEW = 3;

const clampPerView = (value) => {
  const number = parseInt(value, 10);
  if (isNaN(number)) return 1;
  return Math.min(MAX_PER_VIEW, Math.max(1, number));
};

export default function Edit({ attributes, setAttributes }) {
  const {
    heading,
    postType,
    postIds,
    slidesPerView,
    slidesPerViewTablet,
    slidesPerViewMobile,
    slideGap,
    showViewAll,
    viewAllText,
    viewAllUrl,
  } = attributes;
  const [activeIndex, setActiveIndex] = useState(0);
  const [perViewWarning, setPerViewWarning] = useState('');

  /* RangeControl's number input accepts typed values beyond max, so clamp here
     and tell the editor why the number snapped back. */
  const setPerView = (key, label) => (value) => {
    const clamped = clampPerView(value);
    const requested = parseInt(value, 10);
    setPerViewWarning(
      !isNaN(requested) && requested > MAX_PER_VIEW
        ? `${label}: ${requested} is more than the maximum of ${MAX_PER_VIEW} cards — set to ${MAX_PER_VIEW}.`
        : ''
    );
    setAttributes({ [key]: clamped });
  };

  // 1. Available post types: default post/page plus any public + REST-enabled
  //    CPT (ACF or code registered). getPostTypes only returns REST-visible types.
  const postTypes = useSelect((select) => {
    const types = select(coreStore).getPostTypes({ per_page: -1 });
    if (!types) return null;
    return types.filter((t) => t.viewable && !EXCLUDED_TYPES.includes(t.slug));
  }, []);

  // 2. Published posts of the chosen type — id + title + featured image id.
  const posts = useSelect(
    (select) =>
      postType
        ? select(coreStore).getEntityRecords('postType', postType, {
            per_page: 100,
            status: 'publish',
            orderby: 'date',
            order: 'desc',
            _fields: 'id,title,featured_media,link',
          })
        : null,
    [postType]
  );

  // Keep selection in the chosen order for the preview + reorder controls.
  const selectedPosts = (postIds || [])
    .map((id) => (posts || []).find((p) => p.id === id))
    .filter(Boolean);

  // 3. Featured-image URLs for the currently selected posts.
  const mediaIds = selectedPosts
    .map((p) => p.featured_media)
    .filter((id) => id > 0);

  const mediaMap = useSelect(
    (select) => {
      if (!mediaIds.length) return {};
      const records = select(coreStore).getEntityRecords('postType', 'attachment', {
        include: mediaIds,
        per_page: mediaIds.length,
        _fields: 'id,source_url,media_details',
      });
      const map = {};
      (records || []).forEach((m) => {
        const sizes = m.media_details?.sizes || {};
        map[m.id] =
          sizes.large?.source_url ||
          sizes.medium_large?.source_url ||
          m.source_url;
      });
      return map;
    },
    [mediaIds.join(',')]
  );

  const togglePost = (id, checked) => {
    if (checked) {
      setAttributes({ postIds: [...postIds, id] });
    } else {
      setAttributes({ postIds: postIds.filter((pid) => pid !== id) });
    }
  };

  const movePost = (index, direction) => {
    const next = [...postIds];
    const target = index + direction;
    if (target < 0 || target >= next.length) return;
    [next[index], next[target]] = [next[target], next[index]];
    setAttributes({ postIds: next });
  };

  const slides = selectedPosts.map((p) => ({
    id: p.id,
    title: p.title?.rendered || `#${p.id}`,
    img: p.featured_media ? mediaMap[p.featured_media] : '',
  }));

  /* The editor canvas is a fixed width, so it previews the desktop count. The
     tablet/mobile values still ship to the front end via render.php. */
  const perView = clampPerView(slidesPerView);
  const maxIndex = Math.max(0, slides.length - perView);
  const safeIndex = Math.min(activeIndex, maxIndex);
  const visible = slides.slice(safeIndex, safeIndex + perView);

  const blockProps = useBlockProps({
    className: 'posts-slider posts-slider--editor',
    style: {
      '--ps-per': perView,
      '--ps-per-tablet': slidesPerViewTablet,
      '--ps-per-mobile': slidesPerViewMobile,
      '--ps-cur': perView,
      '--ps-gap': `${slideGap}px`,
      // Same count-to-proportion mapping render.php uses.
      '--ps-ratio': { 1: '16 / 5', 2: '16 / 7', 3: '16 / 9' }[perView],
    },
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Source', 'solaire')} initialOpen={true}>
          <SelectControl
            label={__('Post type', 'solaire')}
            value={postType}
            options={
              postTypes
                ? postTypes.map((t) => ({ label: t.name, value: t.slug }))
                : [{ label: __('Loading…', 'solaire'), value: postType }]
            }
            onChange={(value) =>
              // Reset the selection when the source type changes.
              setAttributes({ postType: value, postIds: [] })
            }
          />
        </PanelBody>

        <PanelBody
          title={__('Posts in slider', 'solaire') + ` (${postIds.length})`}
          initialOpen={true}
        >
          {/* Reorder controls for the already-selected posts */}
          {selectedPosts.length > 0 && (
            <div style={{ marginBottom: '16px' }}>
              <p style={{ fontSize: '11px', color: '#757575', textTransform: 'uppercase', letterSpacing: '0.04em', margin: '0 0 6px' }}>
                {__('Slide order', 'solaire')}
              </p>
              {selectedPosts.map((p, i) => (
                <div
                  key={p.id}
                  style={{ display: 'flex', alignItems: 'center', gap: '4px', marginBottom: '4px' }}
                >
                  <span style={{ flex: 1, fontSize: '13px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {i + 1}. {p.title?.rendered || `#${p.id}`}
                  </span>
                  <Button isSmall disabled={i === 0} onClick={() => movePost(i, -1)} label={__('Move up', 'solaire')}>▲</Button>
                  <Button isSmall disabled={i === selectedPosts.length - 1} onClick={() => movePost(i, 1)} label={__('Move down', 'solaire')}>▼</Button>
                </div>
              ))}
            </div>
          )}

          {posts === null && <Spinner />}
          {posts && posts.length === 0 && (
            <Notice status="warning" isDismissible={false}>
              {__('No published posts found for this type.', 'solaire')}
            </Notice>
          )}
          {posts &&
            posts.map((p) => (
              <CheckboxControl
                key={p.id}
                label={p.title?.rendered || `#${p.id}`}
                checked={postIds.includes(p.id)}
                onChange={(checked) => togglePost(p.id, checked)}
              />
            ))}
        </PanelBody>

        <PanelBody title={__('Cards per view', 'solaire')} initialOpen={false}>
          {perViewWarning && (
            <Notice status="warning" onRemove={() => setPerViewWarning('')}>
              {perViewWarning}
            </Notice>
          )}
          <RangeControl
            label={__('Desktop (1024px and up)', 'solaire')}
            value={slidesPerView}
            onChange={setPerView('slidesPerView', __('Desktop', 'solaire'))}
            min={1}
            max={MAX_PER_VIEW}
          />
          <RangeControl
            label={__('Tablet (768px and up)', 'solaire')}
            value={slidesPerViewTablet}
            onChange={setPerView('slidesPerViewTablet', __('Tablet', 'solaire'))}
            min={1}
            max={MAX_PER_VIEW}
          />
          <RangeControl
            label={__('Mobile (under 768px)', 'solaire')}
            value={slidesPerViewMobile}
            onChange={setPerView('slidesPerViewMobile', __('Mobile', 'solaire'))}
            min={1}
            max={MAX_PER_VIEW}
          />
          <RangeControl
            label={__('Gap between cards (px)', 'solaire')}
            value={slideGap}
            onChange={(value) => setAttributes({ slideGap: value })}
            min={0}
            max={48}
            step={4}
          />
        </PanelBody>

        <PanelBody title={__('"View All" button', 'solaire')} initialOpen={false}>
          <ToggleControl
            label={__('Show "View All" button', 'solaire')}
            checked={!!showViewAll}
            onChange={(value) => setAttributes({ showViewAll: value })}
          />
          {showViewAll && (
            <>
              <TextControl
                label={__('Button text', 'solaire')}
                value={viewAllText}
                onChange={(value) => setAttributes({ viewAllText: value })}
              />
              <TextControl
                label={__('Button URL', 'solaire')}
                value={viewAllUrl}
                placeholder="https://"
                onChange={(value) => setAttributes({ viewAllUrl: value })}
              />
              {!viewAllUrl && (
                <Notice status="warning" isDismissible={false}>
                  {__('Add a URL — the button stays hidden on the front end until it has one.', 'solaire')}
                </Notice>
              )}
            </>
          )}
        </PanelBody>

      </InspectorControls>

      <div {...blockProps}>
        {/* Header row: heading left, nav arrows right */}
        <div className="posts-slider__header">
          <RichText
            tagName="h2"
            className="posts-slider__heading"
            value={heading}
            onChange={(value) => setAttributes({ heading: value })}
            placeholder={__('Heading…', 'solaire')}
            allowedFormats={[]}
          />
          <div className="posts-slider__nav" aria-hidden="true">
            {showViewAll && !!viewAllUrl && (
              <span className="posts-slider__viewall">
                {viewAllText || __('View All', 'solaire')}
              </span>
            )}
            <button
              type="button"
              className="posts-slider__arrow"
              onClick={() => setActiveIndex((i) => Math.max(0, i - perView))}
              disabled={safeIndex <= 0}
            >
              <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
                <path fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" d="m15 5-7 7 7 7" />
              </svg>
            </button>
            <button
              type="button"
              className="posts-slider__arrow"
              onClick={() => setActiveIndex((i) => Math.min(maxIndex, i + perView))}
              disabled={safeIndex >= maxIndex}
            >
              <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
                <path fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" d="m9 5 7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        {/* Preview viewport */}
        <div className="posts-slider__viewport">
          {slides.length === 0 && (
            <div className="posts-slider__empty">
              {__('Select one or more posts in the block settings to build the slider.', 'solaire')}
            </div>
          )}

          {visible.length > 0 && (
            <div className="posts-slider__track">
              {visible.map((slide) => (
                <div className="posts-slider__slide" key={slide.id}>
                  {slide.img ? (
                    <img src={slide.img} alt={slide.title} />
                  ) : (
                    <div className="posts-slider__no-image">
                      {__('No featured image set for', 'solaire')} “{slide.title}”
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}

        </div>
      </div>
    </>
  );
}
