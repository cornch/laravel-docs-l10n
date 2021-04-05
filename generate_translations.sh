#!/bin/zsh

for monopo in po/*.po; do
  locale=`echo $(basename $monopo) | sed 's/\.po//'`

  mkdir -p "translations/$locale"
  for pot in translations/template/**/*.pot; do
    file=`echo $pot | sed -E 's#translations/template/(.+)\.pot#\1#g'`

    mkdir -p `dirname "translations/$locale/$file"`

    if [[ -f $file ]]; then
      po4a-updatepo \
        -f text -o markdown \
        --master-charset utf-8 --localized-charset utf-8 \
        -m $pot \
        -p $file
    fi

    msgmerge "$monopo" "$pot" > "translations/$locale/$file.po"
  done
done

