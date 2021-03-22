# Laravel Documentation Translations

## Translate

1. Clone the repository.
2. Copy from `po/docs.pot` to `po/{lang}.po` if not yet presented.
3. Open `po/{lang}.po` and start translate.

## Maintenance

### Update Source Strings

1. Update git submodule.
```
git submodule foreach git pull
```
2. Run `update_template.sh` to extract strings into pot files.

## License

Source files are released under MIT license.

Translation files are released under MIT as well.

