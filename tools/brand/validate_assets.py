#!/usr/bin/env python3
"""Verify official logo provenance, safe vectors, raster dimensions and inventory."""
import hashlib
import json
import re
from pathlib import Path
from xml.etree import ElementTree as ET

from PIL import Image

ROOT = Path(__file__).resolve().parents[2]
BRAND = ROOT / 'brand'
THEME = ROOT / 'wp-content/themes/hks-wayfinder/assets/images/brand'
PREFIX = 'holiday-kenya-safaris-'
SVG = '{http://www.w3.org/2000/svg}'
EXPECTED_PNG = {
    'favicon-32': (32, 32),
    'apple-touch-icon-180': (180, 180),
    'site-icon-512': (512, 512),
    'social-1200x630': (1200, 630),
    'logo-1200': (1200, 459),
}


def check(condition, message):
    if not condition:
        raise AssertionError(message)


def main():
    manifest = json.loads((BRAND / 'manifest.json').read_text(encoding='utf-8'))
    expected = {f'brand/masters/{PREFIX}{name}.svg' for name in ('logo', 'logo-reversed', 'icon')}
    expected |= {f'brand/exports/{PREFIX}{name}.png' for name in EXPECTED_PNG}
    check({item['path'] for item in manifest['files']} == expected, 'Unexpected manifest inventory')
    actual = {p.relative_to(ROOT).as_posix() for folder in ('masters', 'exports') for p in (BRAND / folder).iterdir() if p.is_file()}
    check(actual == expected, 'Old or unregistered artwork remains in the production brand directories')
    for item in manifest['files']:
        file = ROOT / item['path']
        data = file.read_bytes()
        check(len(data) == item['bytes'], f'Byte count differs: {file.name}')
        check(hashlib.sha256(data).hexdigest() == item['sha256'], f'Hash differs: {file.name}')
        if not file.name.endswith('logo-1200.png'):
            check((THEME / file.name).read_bytes() == data, f'Theme asset differs: {file.name}')

    parsed = {}
    for name in ('logo', 'logo-reversed', 'icon'):
        tree = ET.parse(BRAND / 'masters' / f'{PREFIX}{name}.svg').getroot()
        parsed[name] = tree
        check(tree.get('viewBox') == ('0 0 342 342' if name == 'icon' else '0 0 895 342'), f'Wrong viewBox: {name}')
        check(tree.find(SVG + 'title') is not None, f'Missing accessible title: {name}')
        for element in tree.iter():
            check(element.tag.removeprefix(SVG) in {'svg', 'title', 'desc', 'g', 'path'}, f'Unsafe or unexpected SVG element: {element.tag}')
            check(not any(key.startswith('on') or 'href' in key for key in element.attrib), 'Active SVG attribute')
    # The icon contains exactly the monogram paths from the approved master.
    for original, icon in zip(parsed['logo'].iter(SVG + 'path'), parsed['icon'].iter(SVG + 'path')):
        segments = []
        for segment in re.findall(r'M[^M]+', original.get('d')):
            numbers = [float(n) for n in re.findall(r'-?\d+(?:\.\d+)?', segment)]
            if max(numbers[::2]) < 342:
                segments.append(segment.strip())
        check(re.findall(r'\S+', icon.get('d')) == re.findall(r'\S+', ' '.join(segments)), 'Icon geometry differs from approved artwork')
        check(original.get('fill') == icon.get('fill'), 'Icon colors differ from approved artwork')
    for name, size in EXPECTED_PNG.items():
        with Image.open(BRAND / 'exports' / f'{PREFIX}{name}.png') as image:
            image.load()
            check(image.size == size and image.format == 'PNG', f'Invalid raster: {name}')
            check(image.convert('RGBA').getextrema()[3] == (255, 255), f'Preview background is not opaque: {name}')
    deployed = {p.name for p in THEME.iterdir() if p.is_file()}
    expected_deployed = {Path(p).name for p in expected if not p.endswith('logo-1200.png')} | {'Holiday-Kenya-Safaris-Logo-Approved.png'}
    check(deployed == expected_deployed, 'Old or unregistered artwork remains in theme assets')
    print('Official brand validation passed (source geometry, inventory, hashes, raster dimensions and deployed copies).')


if __name__ == '__main__':
    main()
