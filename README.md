# Laravel Documentation Translations

[![Crowdin](https://badges.crowdin.net/laravel-docs/localized.svg)](https://crowdin.com/project/laravel-docs)

## Status

| Language | Status |
|----------|--------|
| Mandarin (Taiwan) | ![zh-TW translation](https://img.shields.io/badge/dynamic/json?color=blue&label=zh-TW&style=flat&query=%24.progress.0.data.translationProgress&url=https%3A%2F%2Fbadges.awesome-crowdin.com%2Fstats-14684796-447952.json)  |


## Translate

### Use Crowdin

Crowdin is the prefered way of translation.  

1. Open https://crowdin.com/project/laravel-docs
2. Select your language and start translate.

If you don't see your language on Crowdin. Open a Pull Request on this repository and the admin will open it for you.

### Use GitHub

While using GitHub for doing translation is not recommended, because manualy managing translation files may cause them to out of sync. But we still accept you to submit your translations this way.

1. Fork this repository.
2. Check if `po/{lang}` exists. If not, copy files from `templates` and rename the extensions to `.po`.
3. Open po/{lang}/{file}.po to translation.
4. While your translation is in progress, please open a **Draft Pull Request** so others know that someone is doing translation for the perticular language / file.

## Maintenance

### Update Source Strings

1. Update git submodule.
```
git submodule foreach git pull
```
2. Run `update_template.py` to extract strings into pot files.
z
### Build Translated Markdown

Run `build.sh`:

```bash
zsh build.sh zh_TW
```

## License

Source files are released under MIT license.

Translation files are released under MIT as well.

