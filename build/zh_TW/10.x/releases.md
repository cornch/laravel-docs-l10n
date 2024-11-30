---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/135/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 50.7
---

# 版本資訊

- [版本策略](#versioning-scheme)
- [支援政策](#support-policy)
- [Laravel 10](#laravel-10)

<a name="versioning-scheme"></a>

## 版本策略

Laravel 及其第一方套件都遵守 [語義化版本](https://semver.org/lang/zh-Tw/)。框架的主要更新會每年釋出 (約在第一季)，而次版本與修訂版則可能頻繁到每週更新。此版本與修訂版 **絕對不會** 包含中斷性變更 (Breaking Change)。

由於 Laravel 的主要更新會包含中斷性變更，因此在專案或套件中參照 Laravel 框架或其組件時，應使用如 `^10.0` 這樣的版本限制式。不過，我們也會不斷努力確保每次進行主要版本更新時，都可於一天之內升級完成。

<a name="named-arguments"></a>

#### 帶名稱的引數

[帶名稱引數](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments)功能尚未包含在 Laravel 的向下相容性方針內。我們可能會在有必要的時候更改函式的參數名稱以改進 Laravel 的程式碼。因此，在使用帶名稱引數呼叫 Laravel 方法時應格外注意，並瞭解到引數名稱未來可能會有所更改。

<a name="support-policy"></a>

## 支援政策

所有的 Laravel 版本都提供 18 個月的 Bug 修正，以及 2 年的安全性修正。對於其他的函式庫，如 Lumen，則只有最新的主要版本會收到 Bug 修正。此外，也請參考 [Laravel 支援的](/docs/{{version}}/database#introduction)資料庫版本。

<div class="overflow-auto">
| 版本 | PHP (*) | 釋出日期 | Bug 修正期限 | 安全性修正期限 |
| --- | --- | --- | --- | --- |
| 8 | 7.3 - 8.1 | 2020 年 9 月 8 日 | 2022 年 7 月 26 日 | 2023 年 1 月 24 日 |
| 9 | 8.0 - 8.2 | 2022 年 2 月 8 日 | 2023 年 8 月 8 日 | 2024 年 2 月 6 日 |
| 10 | 8.1 - 8.3 | 2023 年 2 月 14 日 | 2024 年 8 月 6 日 | 2025 年 2 月 4 日 |
| 11 | 8.2 - 8.3 | March 12th, 2024 | 2025 年 8 月 5 日 | 2026 年 2 月 3 日 |

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

<a name="laravel-10"></a>

## Laravel 10

讀者可能已經知道，從 Laravel 8 開始，Laravel 改為每年釋出新的主要版本。在此之前，每 6 個月都會釋出主要版本。這個改變是為了降低社群維護的負擔，並讓我們的開發團隊能想辦法在不包含中斷性更改 (Breaking Change) 的情況下繼續提供驚艷且強大的新功能。因此，我們在 Laravel 9 中，以不破壞向下相容性的前提下推出了許多強健的功能。

因此，我們對於在目前版本中釋出新功能的承諾也將導致未來的「主要 (Major)」版本將著重於一些「維護性」的任務，如更新上游套件等，讀者稍後可以在本版本資訊內讀到。

Laravel 10 在 Laravel 9.x 的基礎上繼續進行了諸多改進，包含在專案 Skeleton 中以及 Laravel 用來產生類別的 Stub 檔案中加上了回傳型別，並為所有引數加上型別。此外，我們還新增了一個對開發者友善的抽象層，可用來啟動與使用外部處理程序。而且，我們還推出了 Laravel Pennant，為你提供管理專案「^[Feature Flag](%E5%8A%9F%E8%83%BD%E6%97%97%E6%A8%99)」的優質方案。

<a name="php-8"></a>

### PHP 8.1

Laravel 10.x 所要求的最小 PHP 版本為 8.1。

<a name="types"></a>

### 型別

*專案 Skeleton 與 Stub 的型別提示由 [Nuno Maduro](https://github.com/nunomaduro) 參與貢獻*。

在 Laravel 最初的版本中，我們使用了當時 PHP 內能用的所有型別提示 (Type-hint) 功能。不過，在接下來的幾年中，PHP 不斷推出新功能，包含原生型別的型別提示、回傳型別、等位型別 (Union Type) 等。

在 Laravel 10.x 中，我們完全更新了專案的 Skeleton 與 Laravel 所使用的所有 Stub 檔案，以在這些檔案中為所有的方法簽章 (Method Signature) 上加上引數的型別提示與回傳型別。此外，還刪除了不必要的「Doc Block」型別提示：

對於現有專案來說，這項更改是完全向下相容的。因此，現有專案若沒有型別提示，也能繼續正常運作：

<a name="laravel-pennant"></a>

### Laravel Pennant

*Laravel Pennant 由 [Tim MacDonald](https://github.com/timacdonald) 開發*。

第一方套件，Laravel Pennant，現已推出。Laravel Pennant 提供了輕量、簡化的方法，能讓你管理專案的 Feature Flag。在 Pennant 中包含了現成的 `array` Driver 與 `database` Driver 可用來保存 Feature。

使用 `Feature::define` 方法就能輕鬆定義 Feature：

```php
use Laravel\Pennant\Feature;
use Illuminate\Support\Lottery;

Feature::define('new-onboarding-flow', function () {
    return Lottery::odds(1, 10);
});
```
定義好 Feature 後，可以輕鬆判斷目前使用者是否能存取該功能：

```php
if (Feature::active('new-onboarding-flow')) {
    // ...
}
```
當然，為了讓開發起來更方便，我們也提供了 Blade 指示詞：

```blade
@feature('new-onboarding-flow')
    <div>
        <!-- ... -->
    </div>
@endfeature
```
Pennant 還提供了更多進階的功能與 API。更多資訊請參考[完整的 Pennant 說明文件](/docs/{{version}}/pennant)。

<a name="process"></a>

### 使用 Process

*Process 的抽象層由 [Nuno Maduro](https://github.com/nunomaduro) 與 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

Laravel 10.x 中推出了一個新的 `Process` Facade，這個出色的抽象層可用來啟動與操縱外部處理程序 (Process)：

```php
use Illuminate\Support\Facades\Process;

$result = Process::run('ls -la');

return $result->output();
```
也可以使用集區 (Pool) 的方式啟動處理程序，以更方便的執行與管理平行執行的處理程序：

```php
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

[$first, $second, $third] = Process::concurrently(function (Pool $pool) {
    $pool->command('cat first.txt');
    $pool->command('cat second.txt');
    $pool->command('cat third.txt');
});

return $first->output();
```
此外，也可以模擬 Process 以方便測試：

```php
Process::fake();

// ...

Process::assertRan('ls -la');
```
更多有關使用 Process 的資訊，請參考[完整的 Process 說明文件](/docs/{{version}}/processes)。

<a name="test-profiling"></a>

### 測試分析

*測試分析由 [Nuno Maduro](https://github.com/nunomaduro) 參與貢獻*。

Artisan 的 `test` 指令有了一個全新的 `--profile` 選項，能讓你輕鬆的找到專案中最慢的測試：

```shell
php artisan test --profile
```
為了方便起見，最慢的測試會直接在 CLI 的輸出中顯示出來：

<p align="center">
    <img width="100%" src="https://user-images.githubusercontent.com/5457236/217328439-d8d983ec-d0fc-4cde-93d9-ae5bccf5df14.png"/>
</p>
<a name="pest-scaffolding"></a>

### Pest 的 Scaffold

現在，新建立的 Laravel 專案中可以使用 Pest 測試來 Scaffold。若要使用這個功能，請在使用 Laravel 安裝程式建立新專案時提供 `--pest` 旗標：

```shell
laravel new example-application --pest
```
<a name="generator-cli-prompts"></a>

### 產生 CLI 提示字元

*產生 CLI 提示字元由 [Jess Archer](https://github.com/jessarcher) 參與貢獻*。

為了改進 Laravel 所提供的開發者經驗，Laravel 內所有內建的 `make` 指令現在已不再需要任何輸入。在執行這些指令時，若未提供輸入，則會被提示輸入必要的引數：

```shell
php artisan make:controller
```
<a name="horizon-telescope-facelift"></a>

### Horizon / Telescope 的全新面貌

[Horizon](/docs/{{version}}/horizon) 與 [Telescope](/docs/{{version}}/telescope) 的外觀已更新為更現代的樣子，包含改進的字體、間距，以及外觀設計：

<img src="https://laravel.com/img/docs/horizon-example.png">