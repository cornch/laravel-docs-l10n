#!/bin/zsh

for file in docs/**/*.md; do
  echo "Processing $file"

  pot_path=`echo $file | sed -E 's#docs/(.+)\.md#_tmp/\1.pot#g'`

  mkdir -p `dirname $pot_path`

  po4a-gettextize \
          --package-name "Laravel Documentation" \
          --copyright-holder "Taylor Otwell" \
          --format text --option markdown \
          --master-charset utf-8 --localized-charset utf-8 \
          --master $file \
          --po $pot_path
done

echo "Merging different versions..."

for file in `ls _tmp/**/*.pot | sed -E 's#_tmp/.+/(.+)\.pot#\1#g' | uniq`; do
  echo "Merging $file..."
  mkdir -p `dirname "templates/$file"`
  msgcat --strict --no-wrap -o templates/$file.pot _tmp/*/$file.pot
done

echo "Cleaning up..."
rm -Rf _tmp
