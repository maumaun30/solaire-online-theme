import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { title, category, count, viewAllUrl } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Ranking List', 'solaire')} initialOpen>
          <TextControl label={__('Title', 'solaire')} value={title} onChange={(v) => setAttributes({ title: v })} />
          <TextControl
            label={__('Category slug (optional)', 'solaire')}
            value={category}
            onChange={(v) => setAttributes({ category: v })}
          />
          <RangeControl label={__('Number of rows', 'solaire')} min={3} max={12} value={count} onChange={(v) => setAttributes({ count: v })} />
          <TextControl label={__('"View all" URL', 'solaire')} value={viewAllUrl} onChange={(v) => setAttributes({ viewAllUrl: v })} />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/ranking-list" attributes={attributes} />
      </div>
    </>
  );
}
