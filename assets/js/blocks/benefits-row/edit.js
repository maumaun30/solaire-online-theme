import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { Repeater } from '../_shared/controls';

const ICONS = [
  { label: 'Bolt', value: 'bolt' },
  { label: 'Shield', value: 'shield' },
  { label: 'Hybrid', value: 'hybrid' },
];

export default function Edit({ attributes, setAttributes }) {
  const { heading, benefits } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Benefits', 'solaire')} initialOpen>
          <TextControl label={__('Heading', 'solaire')} value={heading} onChange={(v) => setAttributes({ heading: v })} />
          <Repeater
            items={benefits}
            onChange={(v) => setAttributes({ benefits: v })}
            addLabel={__('Add benefit', 'solaire')}
            newItem={{ icon: 'bolt', title: '', text: '' }}
            fields={[
              { name: 'icon', label: __('Icon', 'solaire'), type: 'select', options: ICONS },
              { name: 'title', label: __('Title', 'solaire'), type: 'text' },
              { name: 'text', label: __('Text', 'solaire'), type: 'textarea' },
            ]}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/benefits-row" attributes={attributes} />
      </div>
    </>
  );
}
