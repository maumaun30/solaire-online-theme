import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { title, category, count, viewAllText, viewAllUrl } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Game Row', 'solaire')} initialOpen>
          <TextControl label={__('Title', 'solaire')} value={title} onChange={(v) => setAttributes({ title: v })} />
          <TextControl
            label={__('Category slug', 'solaire')}
            help={__('e.g. live-slots, live-casino, e-games. Leave blank for latest games.', 'solaire')}
            value={category}
            onChange={(v) => setAttributes({ category: v })}
          />
          <RangeControl label={__('Number of games', 'solaire')} min={2} max={12} value={count} onChange={(v) => setAttributes({ count: v })} />
          <TextControl label={__('"View all" text', 'solaire')} value={viewAllText} onChange={(v) => setAttributes({ viewAllText: v })} />
          <TextControl label={__('"View all" URL', 'solaire')} value={viewAllUrl} onChange={(v) => setAttributes({ viewAllUrl: v })} />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/game-row" attributes={attributes} />
      </div>
    </>
  );
}
