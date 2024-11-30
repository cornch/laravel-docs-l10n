---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/135/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 50.7
---

# 版本資訊

- [版本策略](#versioning-scheme)
- [支援政策](#support-policy)
- [Laravel 12](#laravel-12)

<a name="versioning-scheme"></a>

## 版本策略

Laravel 及其第一方套件都遵守 [語義化版本](https://semver.org/lang/zh-Tw/)。框架的主要更新會每年釋出 (約在第一季)，而次版本與修訂版則可能頻繁到每週更新。此版本與修訂版 **絕對不會** 包含中斷性變更 (Breaking Change)。

由於 Laravel 的主要更新會包含中斷性變更，因此在專案或套件中參照 Laravel 框架或其組件時，應使用如 `^11.0` 這樣的版本限制式。不過，我們也會不斷努力確保每次進行主要版本更新時，都可於一天之內升級完成。

<a name="named-arguments"></a>

#### 帶名稱的引數

[帶名稱引數](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments)功能尚未包含在 Laravel 的向下相容性方針內。我們可能會在有必要的時候更改函式的參數名稱以改進 Laravel 的程式碼。因此，在使用帶名稱引數呼叫 Laravel 方法時應格外注意，並瞭解到引數名稱未來可能會有所更改。

<a name="support-policy"></a>

## 支援政策

所有的 Laravel 版本都提供 18 個月的 Bug 修正，以及 2 年的安全性修正。對於其他的函式庫，如 Lumen，則只有最新的主要版本會收到 Bug 修正。此外，也請參考 [Laravel 支援的](/docs/{{version}}/database#introduction)資料庫版本。

<div class="overflow-auto">
| 版本 | PHP (*) | 釋出日期 | Bug 修正期限 | 安全性修正期限 |
| --- | --- | --- | --- | --- |
| 9 | 8.0 - 8.2 | 2022 年 2 月 8 日 | 2023 年 8 月 8 日 | 2024 年 2 月 6 日 |
| 10 | 8.1 - 8.3 | 2023 年 2 月 14 日 | 2024 年 8 月 6 日 | 2025 年 2 月 4 日 |
| 11 | 8.2 - 8.3 | March 12th, 2024 | September 3rd, 2025 | March 12th, 2026 |
| 12 | 8.2 - 8.3 | Q1 2025 | Q3, 2026 | Q1, 2027 |

</div>
<div class="version-colors">
    <div class="end-of-life">
        <div class="color-box"></div>
        <div>End of life</div>
    </div>
    <div class="security-fixes">
        <div class="color-box"></div>
        <div>Security fixes only</div>
    </div>
</div>
(*) 支援的 PHP 版本

<a name="laravel-12"></a>

## Laravel 12

TBA...
