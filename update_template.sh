#!/bin/zsh

for file in docs/**/*.md; do
  echo "Processing $file"

  pot_path=`echo $file | sed -E 's#docs/(.+)\.md#translations/template/\1.pot#g'`

  mkdir -p `dirname $pot_path`

  po4a-gettextize \
          --package-name "Laravel Documentation" \
          --copyright-holder "Taylor Otwell" \
          --format text --option markdown \
          --master-charset utf-8 --localized-charset utf-8 \
          --master $file \
          --po $pot_path
done

msgcat --strict -o po/docs.pot translations/**/*.pot

