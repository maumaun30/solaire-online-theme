import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { Repeater } from '../_shared/controls';

export default function Edit({ attributes, setAttributes }) {
  const { overline, heading, subheading, cards, columns, columnsTablet, columnsMobile } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Intro', 'solaire')} initialOpen>
          <TextControl label={__('Overline', 'solaire')} value={overline} onChange={(v) => setAttributes({ overline: v })} />
          <TextareaControl label={__('Heading', 'solaire')} value={heading} onChange={(v) => setAttributes({ heading: v })} />
          <TextareaControl label={__('Subheading', 'solaire')} value={subheading} onChange={(v) => setAttributes({ subheading: v })} />
        </PanelBody>
        <PanelBody title={__('Layout', 'solaire')} initialOpen={false}>
          <RangeControl
            label={__('Columns (desktop)', 'solaire')}
            value={columns}
            onChange={(v) => setAttributes({ columns: v || 1 })}
            min={1}
            max={6}
          />
          <RangeControl
            label={__('Columns (tablet)', 'solaire')}
            value={columnsTablet}
            onChange={(v) => setAttributes({ columnsTablet: v || 1 })}
            min={1}
            max={6}
          />
          <RangeControl
            label={__('Columns (mobile)', 'solaire')}
            value={columnsMobile}
            onChange={(v) => setAttributes({ columnsMobile: v || 1 })}
            min={1}
            max={4}
          />
        </PanelBody>
        <PanelBody title={__('Cards', 'solaire')} initialOpen={false}>
          <Repeater
            items={cards}
            onChange={(v) => setAttributes({ cards: v })}
            addLabel={__('Add card', 'solaire')}
            newItem={{ icon: { id: 0, url: '' }, title: '', text: '', linkText: 'Explore', linkUrl: '', image: { id: 0, url: '' } }}
            fields={[
              { name: 'image', label: __('Image', 'solaire'), type: 'media' },
              { name: 'icon', label: __('Icon', 'solaire'), type: 'media' },
              { name: 'title', label: __('Title', 'solaire'), type: 'text' },
              { name: 'text', label: __('Text', 'solaire'), type: 'textarea' },
              { name: 'linkText', label: __('Link text', 'solaire'), type: 'text' },
              { name: 'linkUrl', label: __('Link URL', 'solaire'), type: 'text' },
            ]}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/feature-cards" attributes={attributes} />
      </div>
    </>
  );
}
