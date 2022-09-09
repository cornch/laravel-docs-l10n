import os
import shutil
import sys
import requests
import yaml
import polib
import md4c
from dotenv import load_dotenv
from glob import glob
from mdpo.po2md import pofile_to_markdown

load_dotenv()

if len(sys.argv) < 2 or not sys.argv[1]:
  print('Usage: ./build.py <locale>')
  sys.exit(1)

locale = sys.argv[1]

crowdinToken = os.getenv('CROWDIN_TOKEN')

if crowdinToken is None:
  print('Error: CROWDIN_TOKEN environment variable is not set')
  sys.exit(1)

if os.path.exists(f'po/{locale}.po'):
  print(f'Error: po/{locale} does not exist')
  sys.exit(1)

file_translators = {}
def list_file_translators(project_id, file_id):
    if file_id in file_translators:
        return file_translators[file_id]

    translators = {}
    has_more = True
    per_page = 500
    offset = 0
    while has_more:
        response = requests.get(f'https://api.crowdin.com/api/v2/projects/{project_id}/languages/{locale.replace("_", "-")}/translations?fileId={file_id}&offset={offset}&limit={per_page}', headers={ 'Authorization': f'Bearer {crowdinToken}' }).json()
        for translation in response['data']:
            translators[translation['data']['user']['id']] = {
                'name': translation['data']['user']['fullName'],
                'avatarUrl': translation['data']['user']['avatarUrl']
            } 
        has_more = len(response['data']) >= per_page
        offset += per_page

    file_translators[file_id] = translators
    return translators

def enables_html_block(self, block, details):
  if block is md4c.BlockType.HTML:
    return False

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

    pofile = polib.pofile(f'{po_dir}/{md_filename}.po')
    file_id = pofile.metadata['X-Crowdin-File-ID']
    project_id = pofile.metadata['X-Crowdin-Project-ID']
    updated_at = pofile.metadata['PO-Revision-Date']

    pofile_to_markdown(
        f'{source_dir}/{md_filename}.md',
        f'{po_dir}/{md_filename}.po',
        save=f'{target_dir}/{md_filename}.md',
        wrapwidth=0,
        events={
          'enter_block': enables_html_block,
        },
      )

    # write front matter    
    front_matters = {}
    if file_id is not None:
      front_matters['crowdinUrl'] = f'https://crowdin.com/translate/laravel-docs/{file_id}/en-{locale.replace("_", "").lower()}' 
      front_matters['updatedAt'] = f'{updated_at.replace(" ", "T")}:00Z'
      front_matters['contributors'] = list_file_translators(project_id, file_id)
      front_matters['progress'] = pofile.percent_translated()

    with open(f'{target_dir}/{md_filename}.md', 'r') as f:
        markdown_data = f.read()
    
    with open(f'{target_dir}/{md_filename}.md', 'w') as f:
        f.write(f'---\n{yaml.dump(front_matters)}---\n\n{markdown_data}')
