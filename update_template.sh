#!/bin/zsh

echo "Remove old pot files..."
rm -Rf templates/*

for file in `ls docs/**/*.md | sed -E 's#docs/[^/]+/(.+\.md)#\1#g' | sort | uniq`; do
  if [[ $file == "readme.md" || $file == "license.md" ]]; then
    continue
  fi

  echo "Processing $file"

  pot_path=templates/${file%.md}.pot

  mkdir -p `dirname $pot_path`

  md2po \
    --quiet \
    --save \
    --wrapwidth 0 \
    --po-filepath $pot_path \
    --include-codeblocks \
    docs/**/$file
done
