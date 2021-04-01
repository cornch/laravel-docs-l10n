#!/bin/zsh

for file in docs/**/*.md; do
  pot_path=`echo $file | sed -E 's#docs/(.+)\.md#translations/template/\1.pot#g'`

  mkdir -p `dirname $pot_path`

  po4a-gettextize \
          --package-name "Laravel Documentation" \
          --copyright-holder "Taylor Otwell" \
          --format text --option markdown \
          --master $file \
          --po $pot_path 
done

for po in translations/**/*.po; do
  
done

msgcat --strict -o po/docs.pot translations/**/*.pot

