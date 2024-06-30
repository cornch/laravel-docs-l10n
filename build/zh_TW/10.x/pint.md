---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/178/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
---

# Laravel Pint

- [簡介](#introduction)
- [安裝](#installation)
- [執行 Pint](#running-pint)
- [設定 Pint](#configuring-pint)
   - [預設](#presets)
   - [規則](#rules)
   - [排除檔案或資料夾](#excluding-files-or-folders)

<a name="introduction"></a>

## 簡介

[Laravel Pint](https://github.com/laravel/pint) 是一款專為極簡主義者涉及的主導性 (Opinionated) PHP ^[Code Style Fixer](程式碼風格修正程式)。Pint 以 PHP-CS-Fixer 為基礎，並讓其保持簡單，以確保你的 Code Style 保持乾淨與統一。

在所有新建立的 Laravel 專案中，會自動安裝 Pint，因此你可以馬上開始使用。預設情況下，Pint 並不需要任何設定，會自動使用 Laravel 的主導性 Coding Style 來修正程式碼中的 Coding Style 問題。

<a name="installation"></a>

## 安裝

Pint 已包含在最近釋出的 Laravel 框架中，因此通常不需要進行安裝。不過，在舊的專案中，可以使用 Composer 來安裝 Laravel Pint：

```shell
composer require laravel/pint --dev
```

<a name="running-pint"></a>

## 執行 Pint

只要呼叫專案 `vendor/bin` 目錄中的 `pint` 二進位檔案，就可以讓 Pint 修正 Coding Style 問題：

```shell
./vendor/bin/pint
```

也可以針對特定檔案或目錄來執行 Pint：

```shell
./vendor/bin/pint app/Models

./vendor/bin/pint app/Models/User.php
```

Pint 會列出其更新的檔案列表。只要在呼叫 Pint 時提供 `-v` 選項，就可以檢視更多關於 Pint 所做出更改的詳情：

```shell
./vendor/bin/pint -v
```

若只想讓 Pint 偵測程式碼中的 Coding Style 錯誤而不實際更改檔案，可以使用 `--test` 選項：

```shell
./vendor/bin/pint --test
```

若要根據 Git 來讓 Pint 只修改包含未 Commit 更改的檔案，可使用 `--dirty` 選項：

```shell
./vendor/bin/pint --dirty
```

<a name="configuring-pint"></a>

## 設定 Pint

剛才也提到過，Pint 不需要任何設定檔。不過，若有需要更改預設、規則、或是要檢查的資料夾，可在專案根目錄建立一個 `pint.json` 檔：

```json
{
    "preset": "laravel"
}
```

此外，若要使用特定資料夾中的 `pint.json` 檔，可在呼叫 Pint 時提供 `--config` 選項：

```shell
pint --config vendor/my-company/coding-style/pint.json
```

<a name="presets"></a>

### 預設

「預設 (Preset)」定義了一組可用來修正程式碼中 Coding Style 的規則。預設情況下，Pint 使用了 `laravel` 預設，這個預設遵守 Laravel 的主導性 Coding Style。不過，也可與提供 `--preset` 選項給 Pint 來指定不同的預設：

```shell
pint --preset psr12
```

若有需要，也可以在專案的 `pint.json` 檔案中設定預設：

```json
{
    "preset": "psr12"
}
```

Pint 目前支援的預設有：`laravel`、`per`、`psr12`、`symfony`。

<a name="rules"></a>

### 規則

「規則 (Rule)」是 Pint 要用來修正程式碼 Coding Style 問題的樣式準則。剛才提到過，「預設」預先定義了一組規則，這些規則應該適用於大多數的 PHP 專案，因此你通常不需要擔心個別的規則。

不過，若有需要，也可以在 `pint.json` 檔中啟用或禁用特定的規則：

```json
{
    "preset": "laravel",
    "rules": {
        "simplified_null_return": true,
        "braces": false,
        "new_with_braces": {
            "anonymous_class": false,
            "named_class": false
        }
    }
}
```

Pint 以 [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) 為基礎製作，因此，你可以使用 PHP-CS-Fixer 的規則來修正專案中的 Coding Style 問題： [PHP-CS-Fixer Configurator](https://mlocati.github.io/php-cs-fixer-configurator)。

<a name="excluding-files-or-folders"></a>

### 排除檔案或資料夾

預設情況下，Pint 會檢查專案中除了 `vendor` 目錄以外的所有 `.php` 檔案。若有需要排除其他目錄，可使用 `exclude` 設定選項：

```json
{
    "exclude": [
        "my-specific/folder"
    ]
}
```

若有需要排除所有符合特定檔名規則的檔案，可使用 `notName` 選項：

```json
{
    "notName": [
        "*-my-file.php"
    ]
}
```

若有需要排除特定路徑的檔案，可使用 `notPath` 設定選項：

```json
{
    "notPath": [
        "path/to/excluded-file.php"
    ]
}
```
