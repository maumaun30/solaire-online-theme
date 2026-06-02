import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { MediaField } from '../_shared/controls';

export default function Edit({ attributes, setAttributes }) {
  const { title, subtitle, buttonText, buttonUrl, imageId, imageUrl } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Hero Settings', 'solaire')} initialOpen>
          <MediaField
            label={__('Background image', 'solaire')}
            value={{ id: imageId, url: imageUrl }}
            onChange={(m) => setAttributes({ imageId: m.id, imageUrl: m.url })}
          />
          <TextControl label={__('Title', 'solaire')} value={title} onChange={(v) => setAttributes({ title: v })} />
          <TextControl label={__('Subtitle', 'solaire')} value={subtitle} onChange={(v) => setAttributes({ subtitle: v })} />
          <TextControl label={__('Button text', 'solaire')} value={buttonText} onChange={(v) => setAttributes({ buttonText: v })} />
          <TextControl label={__('Button URL', 'solaire')} value={buttonUrl} onChange={(v) => setAttributes({ buttonUrl: v })} />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/hero-banner" attributes={attributes} />
      </div>
    </>
  );
}
