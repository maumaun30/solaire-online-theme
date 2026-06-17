import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { Repeater } from '../_shared/controls';

export default function Edit({ attributes, setAttributes }) {
  const { tiles } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Category Tiles', 'solaire')} initialOpen>
          <Repeater
            items={tiles}
            onChange={(v) => setAttributes({ tiles: v })}
            addLabel={__('Add tile', 'solaire')}
            newItem={{ label: '', slug: '', icon: 'live-slots', image: { id: 0, url: '' } }}
            fields={[
              { name: 'label', label: __('Label', 'solaire'), type: 'text' },
              { name: 'slug', label: __('Category slug', 'solaire'), type: 'text' },
              {
                name: 'icon',
                label: __('Icon', 'solaire'),
                type: 'select',
                options: [
                  { label: 'Live Slots', value: 'live-slots' },
                  { label: 'Live Casino', value: 'live-casino' },
                  { label: 'E-Games', value: 'e-games' },
                  { label: 'Sportsbook', value: 'sportsbook' },
                ],
              },
              { name: 'image', label: __('Image', 'solaire'), type: 'media' },
            ]}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/category-tiles" attributes={attributes} />
      </div>
    </>
  );
}
