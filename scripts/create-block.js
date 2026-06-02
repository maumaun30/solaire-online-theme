const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const blockName = process.argv[2];

if (!blockName) {
  console.error('❌ Please provide a block name.');
  console.error('Example: npm run create-block hero');
  process.exit(1);
}

const slug = blockName.toLowerCase().trim().replace(/\s+/g, '-');
const titleCase = slug
  .split('-')
  .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
  .join(' ');

const blockDir = path.join(__dirname, '../assets/js/blocks', slug);

if (fs.existsSync(blockDir)) {
  console.error(`❌ Block "${slug}" already exists.`);
  process.exit(1);
}

fs.mkdirSync(blockDir, { recursive: true });

const blockJson = `{
  "apiVersion": 3,
  "name": "mytheme/${slug}",
  "title": "${titleCase}",
  "category": "design",
  "icon": "block-default",
  "description": "${titleCase} block.",
  "editorScript": "file:./build/index.js",
  "style": "file:./style.css",
  "render": "file:./render.php",
  "attributes": {
    "title": {
      "type": "string",
      "default": "${titleCase} Title"
    },
    "description": {
      "type": "string",
      "default": "Add your description here."
    }
  }
}
`;

const indexJs = `import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import './style.css';

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save: () => null,
});
`;

const editJs = `import {
  RichText,
  InspectorControls,
  useBlockProps
} from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  TextareaControl
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
  const { title, description } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title="${titleCase} Settings" initialOpen={true}>
          <TextControl
            label="Title"
            value={title}
            onChange={(value) => setAttributes({ title: value })}
          />
          <TextareaControl
            label="Description"
            value={description}
            onChange={(value) => setAttributes({ description: value })}
          />
        </PanelBody>
      </InspectorControls>

      <section {...useBlockProps({ className: 'card' })}>
        <RichText
          tagName="h2"
          value={title}
          onChange={(value) => setAttributes({ title: value })}
          placeholder="Enter title..."
        />
        <RichText
          tagName="p"
          value={description}
          onChange={(value) => setAttributes({ description: value })}
          placeholder="Enter description..."
        />
      </section>
    </>
  );
}
`;

const renderPhp = `<?php
$title = $attributes['title'] ?? '';
$description = $attributes['description'] ?? '';
?>

<section <?php echo get_block_wrapper_attributes(['class' => 'card']); ?>>
    <?php if ($title) : ?>
        <h2><?php echo esc_html($title); ?></h2>
    <?php endif; ?>

    <?php if ($description) : ?>
        <p><?php echo esc_html($description); ?></p>
    <?php endif; ?>
</section>
`;

const styleCss = `.wp-block-mytheme-${slug} {}
`;

fs.writeFileSync(path.join(blockDir, 'block.json'), blockJson);
fs.writeFileSync(path.join(blockDir, 'index.js'), indexJs);
fs.writeFileSync(path.join(blockDir, 'edit.js'), editJs);
fs.writeFileSync(path.join(blockDir, 'render.php'), renderPhp);
fs.writeFileSync(path.join(blockDir, 'style.css'), styleCss);

console.log(`✅ Block "${slug}" created.`);

try {
  console.log(`🔨 Building "${slug}"...`);
  execSync(
    `npx wp-scripts build ./assets/js/blocks/${slug}/index.js --output-path=./assets/js/blocks/${slug}/build`,
    { stdio: 'inherit' }
  );
  console.log(`✅ Block "${slug}" built successfully.`);
} catch (error) {
  console.error(`❌ Block "${slug}" was created, but build failed.`);
  process.exit(1);
}