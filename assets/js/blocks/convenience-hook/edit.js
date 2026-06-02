import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { Repeater } from '../_shared/controls';

const FEATURE_ICONS = [
  { label: 'Phone', value: 'phone' },
  { label: 'Clock', value: 'clock' },
  { label: 'Shield', value: 'shield-plain' },
  { label: 'Peso', value: 'peso' },
];
const ITEM_ICONS = [
  { label: 'Phone', value: 'phone-plain' },
  { label: 'Card', value: 'card' },
  { label: 'Hybrid', value: 'dual' },
];

export default function Edit({ attributes, setAttributes }) {
  const { overline, heading, text, features, items } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Content', 'solaire')} initialOpen>
          <TextControl label={__('Overline', 'solaire')} value={overline} onChange={(v) => setAttributes({ overline: v })} />
          <TextareaControl label={__('Heading (line breaks honoured)', 'solaire')} value={heading} onChange={(v) => setAttributes({ heading: v })} />
          <TextareaControl label={__('Text', 'solaire')} value={text} onChange={(v) => setAttributes({ text: v })} />
        </PanelBody>
        <PanelBody title={__('Feature icons', 'solaire')} initialOpen={false}>
          <Repeater
            items={features}
            onChange={(v) => setAttributes({ features: v })}
            addLabel={__('Add feature', 'solaire')}
            newItem={{ icon: 'phone', label: '' }}
            fields={[
              { name: 'icon', label: __('Icon', 'solaire'), type: 'select', options: FEATURE_ICONS },
              { name: 'label', label: __('Label', 'solaire'), type: 'text' },
            ]}
          />
        </PanelBody>
        <PanelBody title={__('Accordion', 'solaire')} initialOpen={false}>
          <Repeater
            items={items}
            onChange={(v) => setAttributes({ items: v })}
            addLabel={__('Add accordion item', 'solaire')}
            newItem={{ icon: 'phone-plain', title: '', text: '' }}
            fields={[
              { name: 'icon', label: __('Icon', 'solaire'), type: 'select', options: ITEM_ICONS },
              { name: 'title', label: __('Title', 'solaire'), type: 'text' },
              { name: 'text', label: __('Text', 'solaire'), type: 'textarea' },
            ]}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/convenience-hook" attributes={attributes} />
      </div>
    </>
  );
}
