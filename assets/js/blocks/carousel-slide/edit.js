import {
  useBlockProps,
  RichText,
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
} from '@wordpress/block-editor';
import {
  PanelBody,
  Button,
  TextControl,
  ToggleControl,
  RangeControl,
  ResponsiveWrapper,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

export default function Edit({ attributes, setAttributes, clientId }) {
  const {
    imageUrl,
    imageId,
    imageAlt,
    title,
    titleHighlight,
    titleEnd,
    subtitle,
    primaryButtonText,
    showPrimaryButton,
    secondaryButtonText,
    secondaryButtonUrl,
    overlayOpacity,
  } = attributes;

  // SEO: the first slide is an <h1>, every following slide an <h2>.
  // Capitalized so JSX treats it as the element type.
  const isFirstSlide = useSelect(
    (select) => select('core/block-editor').getBlockIndex(clientId) === 0,
    [clientId]
  );
  const TitleTag = isFirstSlide ? 'h1' : 'h2';

  const overlayStyle = {
    background: `linear-gradient(to right, rgba(10, 10, 11, ${overlayOpacity / 100}), rgba(10, 10, 11, ${(overlayOpacity / 100) * 0.6}))`,
  };

  const blockProps = useBlockProps({ className: 'carousel-slide-editor' });

  return (
    <>
      <InspectorControls>
        <PanelBody title="Background Image" initialOpen={true}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={(media) =>
                setAttributes({
                  imageUrl: media.url,
                  imageId: media.id,
                  imageAlt: media.alt || '',
                })
              }
              allowedTypes={['image']}
              value={imageId}
              render={({ open }) => (
                <div>
                  {imageUrl ? (
                    <>
                      <ResponsiveWrapper naturalWidth={1920} naturalHeight={1080}>
                        <img src={imageUrl} alt={imageAlt} style={{ width: '100%', display: 'block' }} />
                      </ResponsiveWrapper>
                      <div style={{ marginTop: '8px', display: 'flex', gap: '8px' }}>
                        <Button variant="secondary" onClick={open} isSmall>
                          Replace Image
                        </Button>
                        <Button
                          variant="tertiary"
                          isDestructive
                          isSmall
                          onClick={() => setAttributes({ imageUrl: '', imageId: 0, imageAlt: '' })}
                        >
                          Remove
                        </Button>
                      </div>
                    </>
                  ) : (
                    <Button variant="primary" onClick={open}>
                      Select Image
                    </Button>
                  )}
                </div>
              )}
            />
          </MediaUploadCheck>

          <RangeControl
            label="Overlay Opacity (%)"
            value={overlayOpacity}
            onChange={(value) => setAttributes({ overlayOpacity: value })}
            min={0}
            max={100}
            style={{ marginTop: '16px' }}
          />
        </PanelBody>

        <PanelBody title="Primary Button" initialOpen={false}>
          <ToggleControl
            label="Show Primary Button"
            help="When on, the primary button is shown and opens the Login / Register modal."
            checked={!!showPrimaryButton}
            onChange={(value) => setAttributes({ showPrimaryButton: value })}
          />
          {showPrimaryButton && (
            <TextControl
              label="Button Text"
              value={primaryButtonText}
              onChange={(value) => setAttributes({ primaryButtonText: value })}
            />
          )}
        </PanelBody>

        <PanelBody title="Secondary Button" initialOpen={false}>
          <TextControl
            label="Button Text"
            value={secondaryButtonText}
            onChange={(value) => setAttributes({ secondaryButtonText: value })}
          />
          <TextControl
            label="Button URL"
            value={secondaryButtonUrl}
            onChange={(value) => setAttributes({ secondaryButtonUrl: value })}
            type="url"
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {/* Background */}
        <div
          className="carousel-slide-editor__bg"
          style={{
            backgroundImage: imageUrl ? `url(${imageUrl})` : 'none',
            backgroundColor: !imageUrl ? '#15171a' : undefined,
          }}
        />

        {/* Overlay */}
        <div className="carousel-slide-editor__overlay" style={overlayStyle} />

        {/* Content */}
        <div className="carousel-slide-editor__content">
          <div className="carousel-slide-editor__inner">
            <TitleTag className="carousel-slide-editor__title">
  <RichText
    tagName="span"
    value={title}
    onChange={(value) => setAttributes({ title: value })}
    placeholder="Enter slide title…"
    allowedFormats={[]}
  />

  {' '}

  <span className="highlight-text">
    <RichText
      tagName="span"
      value={titleHighlight}
      onChange={(value) => setAttributes({ titleHighlight: value })}
      placeholder="Enter highlighted text…"
      allowedFormats={[]}
    />
  </span>

  {' '}

  <span className="mytheme-carousel-slide__title-end">
    <RichText
      tagName="span"
      value={titleEnd}
      onChange={(value) => setAttributes({ titleEnd: value })}
      placeholder="Enter end text…"
      allowedFormats={[]}
    />
  </span>
</TitleTag>
            <RichText
              tagName="p"
              className="carousel-slide-editor__subtitle"
              value={subtitle}
              onChange={(value) => setAttributes({ subtitle: value })}
              placeholder="Enter subtitle…"
              allowedFormats={['core/bold', 'core/italic']}
            />

            {/* Only render buttons wrapper when at least one button has text */}
            {((showPrimaryButton && primaryButtonText) || secondaryButtonText) && (
              <div className="carousel-slide-editor__buttons">
                {showPrimaryButton && primaryButtonText && (
                  <span className="carousel-slide-editor__btn carousel-slide-editor__btn--primary">
                    {primaryButtonText}
                  </span>
                )}
                {secondaryButtonText && (
                  <span className="carousel-slide-editor__btn carousel-slide-editor__btn--secondary">
                    {secondaryButtonText}
                  </span>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}