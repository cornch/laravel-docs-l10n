import os
import re
from glob import glob
from mdpo.md2po import markdown_to_pofile

def filter_anchors(self, msgid, *args):
  if re.match(r'<a name="(.+?)"></a>', msgid) is not None:
    self.disable_next_block = True

print('Remove old pot files...')
for file in glob('templates/*', recursive=True):
  if not os.path.isdir(file):
    os.remove(file)

mdFiles = set([
  re.match(r'docs/[^/]+?/(.+\.md)', file).group(1)
  for file in glob('docs/**/*.md')
])

for file in mdFiles:
  if file == 'readme.md' or file == 'license.md':
    continue

  print(f'Processing {file}')

  pot_path = f'templates/{file[:-3]}.pot'

  os.makedirs(os.path.dirname(pot_path), exist_ok=True)

  markdown_to_pofile(
    f'docs/**/{file}',
    po_filepath=pot_path,
    wrapwidth=0,
    save=True,
    include_codeblocks=True,
    events={ 'msgid': filter_anchors }
  )
