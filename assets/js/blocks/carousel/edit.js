import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import {
  PanelBody,
  ToggleControl,
  RangeControl,
  SelectControl,
} from '@wordpress/components';

const ALLOWED_BLOCKS = ['mytheme/carousel-slide'];

const TEMPLATE = [
  ['mytheme/carousel-slide'],
  ['mytheme/carousel-slide'],
];

export default function Edit({ attributes, setAttributes }) {
  const { autoplay, autoplayDelay, loop, showIndicators, effect } = attributes;

  const blockProps = useBlockProps({
    className: 'carousel-editor-wrapper',
    style: {
      position: 'relative',
      border: '2px dashed #6c757d',
      borderRadius: '8px',
      padding: '16px',
      minHeight: '200px',
    },
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title="Carousel Settings" initialOpen={true}>
          <SelectControl
            label="Transition Effect"
            value={effect}
            options={[
              { label: 'Fade', value: 'fade' },
              { label: 'Slide', value: 'slide' },
              { label: 'Cube', value: 'cube' },
              { label: 'Coverflow', value: 'coverflow' },
            ]}
            onChange={(value) => setAttributes({ effect: value })}
          />
          <ToggleControl
            label="Autoplay"
            checked={autoplay}
            onChange={(value) => setAttributes({ autoplay: value })}
          />
          {autoplay && (
            <RangeControl
              label="Autoplay Delay (ms)"
              value={autoplayDelay}
              onChange={(value) => setAttributes({ autoplayDelay: value })}
              min={1000}
              max={10000}
              step={500}
            />
          )}
          <ToggleControl
            label="Loop"
            checked={loop}
            onChange={(value) => setAttributes({ loop: value })}
          />
          <ToggleControl
            label="Show Dot Indicators"
            checked={showIndicators}
            onChange={(value) => setAttributes({ showIndicators: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div
          style={{
            background: '#1a1a1a',
            color: '#aaa',
            fontSize: '11px',
            fontFamily: 'monospace',
            padding: '6px 12px',
            borderRadius: '4px',
            marginBottom: '12px',
            display: 'inline-block',
          }}
        >
          🎠 Carousel — {effect} · {autoplay ? `autoplay ${autoplayDelay}ms` : 'no autoplay'} ·{' '}
          {loop ? 'loop' : 'no loop'}
        </div>
        <InnerBlocks
          allowedBlocks={ALLOWED_BLOCKS}
          template={TEMPLATE}
          orientation="horizontal"
        />
      </div>
    </>
  );
}