import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { Repeater } from '../_shared/controls';

export default function Edit({ attributes, setAttributes }) {
  const { heading, subheading, footerText, steps, columns, columnsMobile } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Intro', 'solaire')} initialOpen>
          <TextareaControl label={__('Heading', 'solaire')} value={heading} onChange={(v) => setAttributes({ heading: v })} />
          <TextareaControl label={__('Subheading', 'solaire')} value={subheading} onChange={(v) => setAttributes({ subheading: v })} />
          <TextareaControl label={__('Footer text', 'solaire')} value={footerText} onChange={(v) => setAttributes({ footerText: v })} />
        </PanelBody>
        <PanelBody title={__('Layout', 'solaire')} initialOpen={false}>
          <RangeControl
            label={__('Columns (desktop)', 'solaire')}
            help={__('0 = auto: match the number of steps, up to 4.', 'solaire')}
            value={columns}
            onChange={(v) => setAttributes({ columns: v ?? 0 })}
            min={0}
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
        <PanelBody title={__('Steps', 'solaire')} initialOpen={false}>
          <Repeater
            items={steps}
            onChange={(v) => setAttributes({ steps: v })}
            addLabel={__('Add step', 'solaire')}
            newItem={{ number: '', icon: { id: 0, url: '' }, title: '', text: '' }}
            fields={[
              { name: 'number', label: __('Number (blank = auto)', 'solaire'), type: 'text' },
              { name: 'icon', label: __('Icon', 'solaire'), type: 'media' },
              { name: 'title', label: __('Title', 'solaire'), type: 'text' },
              { name: 'text', label: __('Description', 'solaire'), type: 'textarea' },
            ]}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/steps" attributes={attributes} />
      </div>
    </>
  );
}
