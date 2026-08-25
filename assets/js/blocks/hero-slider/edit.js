import { useEffect, useState } from '@wordpress/element';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  SelectControl,
  RangeControl,
  Spinner,
  Notice,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes }) {
  const {
    selectedPostType,
    slideCount,
    breadcrumbLabel,
    autoplayDelay,
    sliderHeight,
  } = attributes;

  const [postTypes, setPostTypes] = useState([]);
  const [slides, setSlides] = useState([]);
  const [totalPosts, setTotalPosts] = useState(0);
  const [loading, setLoading] = useState(false);
  const [activeSlide, setActiveSlide] = useState(0);
  const [siteLogo, setSiteLogo] = useState('');

  // Site logo — the stand-in for slides with no featured image, mirroring
  // single.php. Falls through to the wordmark when none is set.
  useEffect(() => {
    apiFetch({ path: '/wp/v2/settings' })
      .then((settings) => {
        if (!settings?.site_logo) return;
        return apiFetch({ path: `/wp/v2/media/${settings.site_logo}` }).then((media) => {
          setSiteLogo(media?.source_url || '');
        });
      })
      .catch(() => {});
  }, []);

  // Fetch all registered public post types
  useEffect(() => {
    apiFetch({ path: '/wp/v2/types?context=edit' }).then((types) => {
      const excluded = [
        'attachment', 'wp_block', 'wp_template', 'wp_template_part',
        'wp_navigation', 'wp_font_family', 'wp_font_face',
      ];
      setPostTypes(
        Object.entries(types)
          .filter(([key]) => !excluded.includes(key))
          .map(([key, val]) => ({ label: val.name, value: key }))
      );
    });
  }, []);

  // Fetch featured images for the editor preview
  useEffect(() => {
    if (!selectedPostType) return;
    setLoading(true);
    setActiveSlide(0);
    const endpoint = selectedPostType === 'post' ? 'posts' : selectedPostType;

    apiFetch({
      path: `/wp/v2/${endpoint}?per_page=${slideCount}&_fields=id,title,featured_media,_links&_embed`,
    })
      .then((posts) => {
        setSlides(
          posts.map((p) => ({
            id: p.id,
            title: p.title?.rendered || '',
            img: p._embedded?.['wp:featuredmedia']?.[0]?.source_url || '',
          }))
        );
        setLoading(false);
      })
      .catch(() => { setSlides([]); setLoading(false); });

    apiFetch({ path: `/wp/v2/${endpoint}?per_page=1`, parse: false })
      .then((res) => setTotalPosts(parseInt(res.headers.get('X-WP-Total'), 10) || 0))
      .catch(() => setTotalPosts(0));
  }, [selectedPostType, slideCount]);

  const blockProps = useBlockProps({
    className: 'wp-block-solaire-hero-slider solaire-hero-slider',
    style: {
      position: 'relative',
      height: sliderHeight,
      overflow: 'hidden',
      background: '#15171a',
      display: 'block',
    },
  });

  const currentSlide = slides[activeSlide];
  const breadcrumbDisplay = breadcrumbLabel || 'Page Title';

  return (
    <>
      <InspectorControls>
        <PanelBody title="Post Type" initialOpen={true}>
          <SelectControl
            label="Select Post Type"
            value={selectedPostType}
            options={postTypes.length ? postTypes : [{ label: 'Loading…', value: 'post' }]}
            onChange={(val) => setAttributes({ selectedPostType: val })}
            help={totalPosts ? `${totalPosts} total posts found` : ''}
          />
          <RangeControl
            label="Number of Slides"
            value={slideCount}
            onChange={(val) => setAttributes({ slideCount: val })}
            min={1}
            max={10}
          />
        </PanelBody>

        <PanelBody title="Breadcrumb" initialOpen={true}>
          <TextControl
            label="Override Breadcrumb Label"
            value={breadcrumbLabel}
            onChange={(val) => setAttributes({ breadcrumbLabel: val })}
            help="Leave blank — auto-resolves to the current page title on the front-end."
          />
        </PanelBody>

        <PanelBody title="Slider Settings" initialOpen={false}>
          <TextControl
            label="Slider Height (CSS value)"
            value={sliderHeight}
            onChange={(val) => setAttributes({ sliderHeight: val })}
            help="e.g. 420px · 50vh · 480px"
          />
          <RangeControl
            label="Autoplay Delay (ms)"
            value={autoplayDelay}
            onChange={(val) => setAttributes({ autoplayDelay: val })}
            min={2000}
            max={10000}
            step={500}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {loading ? (
          <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 2 }}>
            <Spinner />
          </div>
        ) : currentSlide ? (
          <>
            {currentSlide.img ? (
              <img
                src={currentSlide.img}
                alt={currentSlide.title}
                style={{
                  position: 'absolute', inset: 0,
                  width: '100%', height: '100%',
                  objectFit: 'cover', objectPosition: 'center',
                  display: 'block',
                }}
              />
            ) : (
              <div className="solaire-hero-slider__fallback">
                {siteLogo ? (
                  <img className="solaire-hero-slider__fallback-logo" src={siteLogo} alt="" />
                ) : (
                  <span className="solaire-hero-slider__wordmark">
                    <span className="solaire-hero-slider__wordmark-main">SOLAIRE</span>
                    <span className="solaire-hero-slider__wordmark-sub">ONLINE</span>
                  </span>
                )}
              </div>
            )}
          </>
        ) : (
          <div style={{ position: 'absolute', inset: 0, zIndex: 2, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Notice status="warning" isDismissible={false}>
              No published posts found for <strong>{selectedPostType}</strong>.
            </Notice>
          </div>
        )}

        {/* Readability scrim — matches the ::before in style.css */}
        <div style={{
          position: 'absolute', inset: 0, zIndex: 2, pointerEvents: 'none',
          background:
            'linear-gradient(to bottom, rgba(21,23,26,0.60) 0%, rgba(21,23,26,0.28) 18%, rgba(21,23,26,0) 38%),'
            + 'linear-gradient(to top, rgba(21,23,26,0.45) 0%, rgba(21,23,26,0) 22%)',
        }} />

        {/* Breadcrumb */}
        <div style={{
          position: 'absolute', top: 0, left: 0, right: 0, zIndex: 10,
          display: 'flex', alignItems: 'center', width: '100%', paddingTop: '1.25rem',
        }}>
          <nav style={{
            display: 'flex', alignItems: 'center', gap: '0.55rem',
            width: '100%', maxWidth: '1440px', margin: '0 auto', padding: '0 1.5rem',
            fontFamily: "'Montserrat', system-ui, sans-serif",
            fontSize: '0.78rem', fontWeight: 600, letterSpacing: '0.04em',
          }}>
            <span style={{ color: '#fff', fontWeight: 400 }}>Home</span>
            <svg viewBox="0 0 6 10" width="6" height="10" aria-hidden="true" style={{ color: '#fff', flexShrink: 0 }}>
              <path d="M3.818 5L0 1.111 1.091 0 6 5l-4.909 5L0 8.889 3.818 5z" fill="currentColor" />
            </svg>
            <span style={{ color: '#df6a2e' }}>{breadcrumbDisplay}</span>
          </nav>
        </div>

        {/* Gold hairline along the bottom edge */}
        <div style={{
          position: 'absolute', left: 0, right: 0, bottom: 0, height: '3px', zIndex: 9,
          background: 'linear-gradient(90deg, #c42b29 0%, #df6a2e 45%, #f5b335 100%)',
        }} />

        {/* Dot navigation — click to preview in editor */}
        {slides.length > 1 && (
          <div style={{
            position: 'absolute', bottom: 20, left: '50%',
            transform: 'translateX(-50%)', zIndex: 10,
            display: 'flex', alignItems: 'center', gap: '8px',
          }}>
            {slides.map((_, i) => (
              <button
                key={i}
                onClick={() => setActiveSlide(i)}
                aria-label={`Go to slide ${i + 1}`}
                style={{
                  width: i === activeSlide ? 40 : 10,
                  height: 10,
                  borderRadius: '9999px',
                  border: 'none',
                  cursor: 'pointer',
                  padding: 0,
                  flexShrink: 0,
                  background: i === activeSlide
                    ? 'linear-gradient(90deg, #df6a2e 0%, #f5b335 100%)'
                    : 'rgba(255,255,255,0.28)',
                  transition: 'all 0.25s ease',
                }}
              />
            ))}
          </div>
        )}
      </div>
    </>
  );
}
