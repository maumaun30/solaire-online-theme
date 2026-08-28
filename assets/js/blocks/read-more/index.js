import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import metadata from './block.json';
import Edit from './edit';
import './style.css';

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  // Dynamic block, but the inner blocks still have to be serialized so
  // render.php receives them as $content.
  save: () => <InnerBlocks.Content />,
});
