const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

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
  console.log('No blocks found to watch.');
  process.exit(0);
}

const children = [];

for (const block of blocks) {
  console.log(`👀 Watching block: ${block}`);

  const child = spawn(
    'npx',
    [
      'wp-scripts',
      'start',
      `./assets/js/blocks/${block}/index.js`,
      `--output-path=./assets/js/blocks/${block}/build`
    ],
    {
      stdio: 'inherit',
      shell: true
    }
  );

  children.push(child);
}

process.on('SIGINT', () => {
  for (const child of children) {
    child.kill('SIGINT');
  }
  process.exit();
});