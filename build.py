import os
import shutil
import sys
from glob import glob
from mdpo.po2md import pofile_to_markdown

if len(sys.argv) < 2 or not sys.argv[1]:
  print('Usage: ./build.py <locale>')
  sys.exit(1)

locale = sys.argv[1]

if os.path.exists(f'po/{locale}.po'):
  print(f'Error: po/{locale} does not exist')
  sys.exit(1)

for source_dir in glob('docs/*'):
  version = os.path.basename(source_dir)
  target_dir = f'build/{locale}/{version}'
  po_dir = f'po/{locale}'

  os.makedirs(target_dir, exist_ok=True)

  for file in glob(f'{source_dir}/**/*.md', recursive=True):
    path = os.path.dirname(file).replace(source_dir, '')
    shutil.copy(file, f'{target_dir}/{path}')

  for md in glob(f'{target_dir}/**/*.md', recursive=True):
    md_filename = md.replace(f'{target_dir}/', '').replace('.md', '')

    print(f'Building {target_dir}/{md_filename}.md...')

    pofile_to_markdown(
        f'{source_dir}/{md_filename}.md',
        f'{po_dir}/{md_filename}.po',
        save=f'{target_dir}/{md_filename}.md',
        wrapwidth=0,
    )
