import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { Repeater } from '../_shared/controls';

const ICONS = [
  { label: 'Help', value: 'help' },
  { label: 'Shield', value: 'shield-plain' },
  { label: 'Card', value: 'card' },
  { label: 'Phone', value: 'phone-plain' },
];

export default function Edit({ attributes, setAttributes }) {
  const { heading, items } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('FAQ Guide', 'solaire')} initialOpen>
          <TextControl label={__('Heading', 'solaire')} value={heading} onChange={(v) => setAttributes({ heading: v })} />
          <Repeater
            items={items}
            onChange={(v) => setAttributes({ items: v })}
            addLabel={__('Add question', 'solaire')}
            newItem={{ icon: 'help', question: '', answer: '' }}
            fields={[
              { name: 'icon', label: __('Icon', 'solaire'), type: 'select', options: ICONS },
              { name: 'question', label: __('Question', 'solaire'), type: 'text' },
              { name: 'answer', label: __('Answer', 'solaire'), type: 'textarea' },
            ]}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/faq-guide" attributes={attributes} />
      </div>
    </>
  );
}
