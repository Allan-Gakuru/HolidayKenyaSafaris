#!/usr/bin/env node
/** Export the approved SVG artwork; never redraw or typeset the logo. Requires sharp. */
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const sharp = require('sharp');

const root = path.resolve(__dirname, '../..');
const masters = path.join(root, 'brand/masters');
const exportsDir = path.join(root, 'brand/exports');
const theme = path.join(root, 'wp-content/themes/hks-wayfinder/assets/images/brand');
const prefix = 'holiday-kenya-safaris-';
const source = fs.readFileSync(path.join(masters, `${prefix}logo.svg`), 'utf8');

// The approved master contains closed M/L/Z paths. Extract the complete compass
// subpaths at their original coordinates, preserving holes and every point.
function iconSvg() {
  if (!source.includes('viewBox="0 0 895 342"')) throw new Error('Review changed master geometry before exporting icons.');
  return source.replace('0 0 895 342', '0 0 342 342')
    .replace('Holiday Kenya Safaris logo', 'Holiday Kenya Safaris icon')
    .replace('Compass monogram HKS with Holiday Kenya Safaris wordmark.', 'The official Holiday Kenya Safaris compass monogram.')
    .replace(/d="([^"]+)"/g, (attribute, d) => {
      if (!d.startsWith('M ')) return attribute;
      if (/[^MLZ\d.\s-]/.test(d)) throw new Error('Unexpected SVG path command.');
      const subpaths = d.match(/M[^M]+/g).filter(segment => {
        const points = segment.match(/-?\d+(?:\.\d+)?/g).map(Number);
        const x = points.filter((_, index) => index % 2 === 0);
        if (Math.min(...x) < 342 && Math.max(...x) >= 342) throw new Error('A path crosses the icon boundary.');
        return Math.max(...x) < 342;
      });
      return `d="${subpaths.join(' ').trim()}"`;
    });
}

async function main() {
  fs.mkdirSync(exportsDir, { recursive: true });
  const icon = iconSvg();
  fs.writeFileSync(path.join(masters, `${prefix}icon.svg`), icon);
  const rasterFiles = [];
  async function png(name, input, width, height, margin = 0) {
    const artwork = await sharp(Buffer.from(input), { density: 288 })
      .resize(width - margin * 2, height - margin * 2, { fit: 'inside' }).png().toBuffer();
    const output = path.join(exportsDir, `${prefix}${name}.png`);
    await sharp({ create: { width, height, channels: 4, background: '#ffffff' } })
      .composite([{ input: artwork, gravity: 'centre' }]).png().toFile(output);
    rasterFiles.push(output);
  }
  await png('favicon-32', icon, 32, 32);
  await png('apple-touch-icon-180', icon, 180, 180, 8);
  await png('site-icon-512', icon, 512, 512, 24);
  await png('social-1200x630', source, 1200, 630, 80);
  await png('logo-1200', source, 1200, 459);

  const svgFiles = ['logo.svg', 'logo-reversed.svg', 'icon.svg'].map(name => path.join(masters, prefix + name));
  const deploy = [...svgFiles, ...rasterFiles.filter(file => !file.endsWith('logo-1200.png'))];
  for (const file of deploy) fs.copyFileSync(file, path.join(theme, path.basename(file)));
  const files = [...svgFiles, ...rasterFiles].sort().map(file => {
    const bytes = fs.readFileSync(file);
    return { path: path.relative(root, file).split(path.sep).join('/'), bytes: bytes.length, sha256: crypto.createHash('sha256').update(bytes).digest('hex') };
  });
  fs.writeFileSync(path.join(root, 'brand/manifest.json'), JSON.stringify({
    source: 'brand/masters/holiday-kenya-safaris-logo.svg',
    generated_by: 'tools/brand/build_logo.cjs',
    files,
  }, null, 2) + '\n');
  console.log(`Exported ${files.length} official brand assets; copied ${deploy.length} to the theme.`);
}
main().catch(error => { console.error(error); process.exitCode = 1; });
