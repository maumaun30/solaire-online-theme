import { useBlockProps, RichText, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  TextareaControl,
  Button,
  Card,
  CardBody,
  CardHeader,
  Flex,
  FlexItem,
  FlexBlock,
} from '@wordpress/components';
import { plus, chevronUp, chevronDown, trash } from '@wordpress/icons';

function ImgPlaceholder() {
  return (
    <svg
      viewBox="0 0 64 64"
      xmlns="http://www.w3.org/2000/svg"
      style={{ width: '32px', height: '32px', opacity: 0.25 }}
    >
      <rect x="8" y="8" width="48" height="48" rx="4" fill="none" stroke="currentColor" strokeWidth="3" strokeDasharray="6 3" />
      <text x="32" y="38" textAnchor="middle" fontSize="14" fill="currentColor">IMG</text>
    </svg>
  );
}

export default function Edit({ attributes, setAttributes }) {
  const { sectionTitle, sectionSubtitle, sectionTagline, features } = attributes;

  const updateFeature = (index, field, value) => {
    const updated = features.map((f, i) => i === index ? { ...f, [field]: value } : f);
    setAttributes({ features: updated });
  };

  const updateFeatureMedia = (index, media) => {
    const updated = features.map((f, i) => i === index ? { ...f, svgId: media.id, svgUrl: media.url } : f);
    setAttributes({ features: updated });
  };

  const clearFeatureMedia = (index) => {
    const updated = features.map((f, i) => i === index ? { ...f, svgId: 0, svgUrl: '' } : f);
    setAttributes({ features: updated });
  };

  const addFeature = () => {
    setAttributes({ features: [...features, { svgId: 0, svgUrl: '', title: 'NEW ITEM', description: '' }] });
  };

  const removeFeature = (index) => {
    setAttributes({ features: features.filter((_, i) => i !== index) });
  };

  const moveFeature = (index, direction) => {
    const updated = [...features];
    const target = index + direction;
    if (target < 0 || target >= updated.length) return;
    [updated[index], updated[target]] = [updated[target], updated[index]];
    setAttributes({ features: updated });
  };

  const blockProps = useBlockProps({
    className: 'mytheme-payments-editor',
    style: {
      backgroundColor: 'var(--bg-dark-3, #060d1a)',
      padding: '30px 0 0',
      fontFamily: "'Montserrat', sans-serif",
    },
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title="Section Heading" initialOpen={true}>
          <TextControl
            label="Title"
            value={sectionTitle}
            onChange={(value) => setAttributes({ sectionTitle: value })}
          />
          <TextareaControl
            label="Subtitle"
            value={sectionSubtitle}
            onChange={(value) => setAttributes({ sectionSubtitle: value })}
            rows={3}
          />
          <TextControl
            label="Tagline (below cards)"
            value={sectionTagline}
            onChange={(value) => setAttributes({ sectionTagline: value })}
          />
        </PanelBody>

        <PanelBody title={`Items (${features.length})`} initialOpen={true}>
          {features.map((feature, index) => (
            <Card key={index} style={{ marginBottom: '12px', border: '1px solid #444' }}>
              <CardHeader>
                <Flex align="center">
                  <FlexItem>
                    <strong style={{ color: '#aaa', fontSize: '12px' }}>Item {index + 1}</strong>
                  </FlexItem>
                  <FlexBlock />
                  <FlexItem>
                    <Button icon={chevronUp} isSmall disabled={index === 0} onClick={() => moveFeature(index, -1)} label="Move up" />
                  </FlexItem>
                  <FlexItem>
                    <Button icon={chevronDown} isSmall disabled={index === features.length - 1} onClick={() => moveFeature(index, 1)} label="Move down" />
                  </FlexItem>
                  <FlexItem>
                    <Button icon={trash} isSmall isDestructive disabled={features.length <= 1} onClick={() => removeFeature(index)} label="Remove" />
                  </FlexItem>
                </Flex>
              </CardHeader>
              <CardBody>
                <p style={{ fontSize: '11px', color: '#aaa', margin: '0 0 8px', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                  Logo / Icon
                </p>
                <MediaUploadCheck>
                  <MediaUpload
                    onSelect={(media) => updateFeatureMedia(index, media)}
                    allowedTypes={['image']}
                    value={feature.svgId}
                    render={({ open }) => (
                      <Flex align="center" gap={2} style={{ marginBottom: '12px' }}>
                        <FlexItem>
                          <div style={{
                            width: '56px', height: '40px', borderRadius: '6px',
                            backgroundColor: '#0e1a2e', border: '1px solid #2a3a55',
                            display: 'flex', alignItems: 'center', justifyContent: 'center', overflow: 'hidden',
                          }}>
                            {feature.svgUrl
                              ? <img src={feature.svgUrl} alt="" style={{ maxWidth: '40px', maxHeight: '28px', objectFit: 'contain' }} />
                              : <ImgPlaceholder />
                            }
                          </div>
                        </FlexItem>
                        <FlexBlock>
                          <Button variant="secondary" isSmall onClick={open} style={{ marginBottom: '4px', display: 'block', width: '100%' }}>
                            {feature.svgUrl ? 'Replace Logo' : 'Upload Logo'}
                          </Button>
                          {feature.svgUrl && (
                            <Button variant="tertiary" isSmall isDestructive onClick={() => clearFeatureMedia(index)} style={{ display: 'block', width: '100%' }}>
                              Remove
                            </Button>
                          )}
                        </FlexBlock>
                      </Flex>
                    )}
                  />
                </MediaUploadCheck>
                <TextControl
                  label="Label"
                  value={feature.title}
                  onChange={(value) => updateFeature(index, 'title', value)}
                />
              </CardBody>
            </Card>
          ))}

          <Button
            icon={plus}
            variant="secondary"
            onClick={addFeature}
            style={{ width: '100%', justifyContent: 'center', marginTop: '4px' }}
          >
            Add Item
          </Button>
        </PanelBody>
      </InspectorControls>

      {/* ── Editor canvas preview ── */}
      <div {...blockProps}>
        <style>{`@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap');`}</style>

        <div style={{ maxWidth: '1280px', margin: '0 auto', padding: '0 24px' }}>

        {/* Heading */}
        <div style={{ textAlign: 'center', marginBottom: '48px' }}>
          <RichText
            tagName="h2"
            value={sectionTitle}
            onChange={(value) => setAttributes({ sectionTitle: value })}
            placeholder="Section title…"
            style={{
              fontFamily: "'Montserrat', sans-serif",
              fontSize: 'clamp(1.35rem, 2.4vw, 1.9rem)',
              fontWeight: 900,
              color: '#ffffff',
              textTransform: 'uppercase',
              margin: '0 0 18px',
              lineHeight: 1.25,
            }}
            allowedFormats={[]}
          />
          <RichText
            tagName="p"
            value={sectionSubtitle}
            onChange={(value) => setAttributes({ sectionSubtitle: value })}
            placeholder="Section subtitle…"
            style={{
              fontFamily: "'Montserrat', sans-serif",
              fontSize: '0.975rem',
              fontWeight: 400,
              color: 'rgba(255,255,255,0.65)',
              margin: '0 auto',
              maxWidth: '826px',
              lineHeight: 1.75,
            }}
            allowedFormats={['core/bold', 'core/italic']}
          />
        </div>

        {/* Carousel preview — static scrollable row (animates on the front end) */}
        <div style={{
          display: 'flex',
          marginBottom: '32px',
          overflowX: 'auto',
          paddingBottom: '8px',
        }}>
          {features.map((feature, index) => (
            <div
              key={index}
              style={{
                flexShrink: 0,
                width: '200px',
                marginRight: '16px',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '16px',
                padding: '10px',
                borderRadius: '6px',
                backgroundColor: '#1E1E1E',
                border: '1px solid rgba(255,255,255,0.08)',
              }}
            >
              {/* Icon area — flat container, logo centered */}
              <div style={{
                width: '100%',
                height: '40px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                overflow: 'hidden',
                flexShrink: 0,
              }}>
                {feature.svgUrl
                  ? <img src={feature.svgUrl} alt={feature.title} style={{ display: 'block', maxWidth: '70px', maxHeight: '36px', objectFit: 'contain' }} />
                  : <ImgPlaceholder />
                }
              </div>

              {/* Label inside card */}
              <span style={{
                fontFamily: "'Montserrat', sans-serif",
                fontWeight: 800,
                fontSize: '0.78rem',
                color: '#ffffff',
                letterSpacing: '0.14em',
                textTransform: 'uppercase',
                textAlign: 'center',
                lineHeight: 1,
              }}>
                {feature.title}
              </span>
            </div>
          ))}
        </div>

        {/* Tagline — always rendered so it's clickable/editable on canvas */}
        <RichText
          tagName="p"
          value={sectionTagline}
          onChange={(value) => setAttributes({ sectionTagline: value })}
          placeholder="Tagline below cards…"
          style={{
            fontFamily: "'Montserrat', sans-serif",
            textAlign: 'center',
            fontSize: '0.875rem',
            color: 'rgba(255,255,255,0.45)',
            margin: 0,
            paddingTop: '4px',
          }}
          allowedFormats={['core/bold', 'core/italic']}
        />

        </div>
      </div>
    </>
  );
}