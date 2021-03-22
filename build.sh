#!/bin/zsh

locale=$1

if [[ -z "$locale" || ! -d "translations/$locale" ]]; then
  echo "Usage: zsh build.sh <locale>"
  exit
fi

for source_dir in docs/*; do
  version=`basename "$source_dir"`
  target_dir="build/$locale/$version"
  po_dir="translations/$locale/$version"

  mkdir -p "build/$locale/$version"

  cp -Rf $source_dir/* "$target_dir"

  for md in $target_dir/**/*.md; do
    md_filename=`echo "$md" | sed -E "s#$target_dir/(.+)\.md#\1#g"`

    po4a-translate --format text --option markdown \
      --keep 0 \
      --master "$source_dir/$md_filename.md" \
      --po "$po_dir/$md_filename.po" \
      --localized "$target_dir/$md_filename.md"
  done
done

