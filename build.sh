#!/bin/zsh

locale=$1

if [[ -z "$locale" ]]; then
  echo "Usage: zsh build.sh <locale>"
  exit
fi

if [[ ! -d "po/$locale" ]]; then
  echo "Error: po/$locale does not exist"
  exit
fi

for source_dir in docs/*; do
  version=`basename "$source_dir"`
  target_dir="build/$locale/$version"
  po_dir="po/$locale"

  mkdir -p "build/$locale/$version"

  cp -Rf $source_dir/* "$target_dir"

  for md in $target_dir/**/*.md; do
    echo "Building $target_dir/$md_filename.md..."

    md_filename=`echo "$md" | sed -E "s#$target_dir/(.+)\.md#\1#g"`

    po2md \
      --quiet \
      --wrapwidth 0 \
      --po-files "$po_dir/$md_filename.po" \
      --save "$target_dir/$md_filename.md" \
      "$source_dir/$md_filename.md"
  done
done

