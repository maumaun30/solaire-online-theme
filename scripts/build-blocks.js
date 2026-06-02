const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const blocksDir = path.join(__dirname, '../assets/js/blocks');

if (!fs.existsSync(blocksDir)) {
  console.log('No blocks directory found.');
  process.exit(0);
}

const blocks = fs
  .readdirSync(blocksDir)
  .filter(name => {
    const fullPath = path.join(blocksDir, name);
    return (
      fs.statSync(fullPath).isDirectory() &&
      fs.existsSync(path.join(fullPath, 'index.js'))
    );
  });

if (!blocks.length) {
  console.log('No blocks found to build.');
  process.exit(0);
}

for (const block of blocks) {
  console.log(`🔨 Building block: ${block}`);
  execSync(
    `npx wp-scripts build ./assets/js/blocks/${block}/index.js --output-path=./assets/js/blocks/${block}/build`,
    { stdio: 'inherit' }
  );
}

console.log('✅ All blocks built successfully.');