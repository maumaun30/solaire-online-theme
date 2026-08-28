import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  useInnerBlocksProps,
  RichText,
  InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';

/* Same typography classes render.php puts on the body. The editor canvas loads
   main.min.css (add_editor_style in functions.php), so the Tailwind utilities
   resolve here too and the preview matches the front end. */
const BODY_CLASSES = [
  'text-sm leading-relaxed text-slatey sm:text-base',
  '[&_a]:text-orange hover:[&_a]:underline',
  '[&_h2]:mb-3 [&_h2]:mt-8 [&_h2:first-child]:mt-0 [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-extrabold [&_h2]:text-white sm:[&_h2]:text-2xl',
  '[&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:font-display [&_h3]:font-semibold [&_h3]:text-white',
  '[&_h4]:mb-2 [&_h4]:mt-4 [&_h4]:font-display [&_h4]:font-semibold [&_h4]:text-white',
  '[&_li]:mb-1 [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-5',
  '[&_p]:mb-4 [&_p:last-child]:mb-0',
  '[&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-5 [&_ul_li]:marker:text-orange',
].join(' ');

export default function Edit({ attributes, setAttributes }) {
  const { heading, showIcon, collapsible, collapsedHeight } = attributes;

  const blockProps = useBlockProps({
    className: 'title-bar bg-white/[0.02] p-6 sm:p-8',
  });

  // Inner blocks rather than one RichText so each line can be turned into a
  // Heading or a list from the normal block toolbar.
  const innerBlocksProps = useInnerBlocksProps(
    { className: BODY_CLASSES },
    {
      allowedBlocks: [
        'core/paragraph',
        'core/heading',
        'core/list',
        'core/separator',
      ],
      template: [
        ['core/paragraph', { placeholder: __('Write the description…', 'solaire') }],
      ],
      templateLock: false,
    }
  );

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Panel', 'solaire')} initialOpen>
          <ToggleControl
            label={__('Show heading icon', 'solaire')}
            checked={!!showIcon}
            onChange={(value) => setAttributes({ showIcon: value })}
          />
          <ToggleControl
            label={__('Collapse long text', 'solaire')}
            help={__(
              'Shows a Read More button when the copy is taller than the collapsed height.',
              'solaire'
            )}
            checked={!!collapsible}
            onChange={(value) => setAttributes({ collapsible: value })}
          />
          {collapsible && (
            <RangeControl
              label={__('Collapsed height (px)', 'solaire')}
              value={collapsedHeight}
              onChange={(value) => setAttributes({ collapsedHeight: value })}
              min={80}
              max={600}
              step={20}
            />
          )}
        </PanelBody>
      </InspectorControls>

      {/* The editor shows the panel at full height — collapsing is a front-end
          concern and would only get in the way of editing. */}
      <div {...blockProps}>
        <div className="mb-6 flex items-start gap-3 border-b border-white/10 pb-4 md:items-center">
          {showIcon && (
            <svg
              className="h-6 w-6 shrink-0 text-orange"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              aria-hidden="true"
            >
              <circle cx="12" cy="12" r="9" />
              <path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.7.4-1 .9-1 1.7M12 17h.01" />
            </svg>
          )}
          <RichText
            tagName="h2"
            className="font-display text-xl font-extrabold text-gold sm:text-2xl"
            value={heading}
            allowedFormats={[]}
            onChange={(value) => setAttributes({ heading: value })}
            placeholder={__('About the Game', 'solaire')}
          />
        </div>

        <div {...innerBlocksProps} />

        {collapsible && (
          <span className="mt-4 block font-display text-sm font-semibold text-orange">
            {__('Read More', 'solaire')}
          </span>
        )}
      </div>
    </>
  );
}
