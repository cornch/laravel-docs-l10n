---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/113/en-zhtw'
updatedAt: '2023-02-11T12:59:00Z'
contributors: {  }
progress: 52.02
---

# 套件開發

- [簡介](#introduction)
  - [A Note on Facades](#a-note-on-facades)
  
- [Package Discovery](#package-discovery)
- [Service Provider](#service-providers)
- [資源](#resources)
  - [設定](#configuration)
  - [Migration](#migrations)
  - [Route](#routes)
  - [語系檔](#language-files)
  - [View](#views)
  - [View 元件](#view-components)
  - [「About」Artisan 指令](#about-artisan-command)
  
- [指令](#commands)
  - [Optimize Commands](#optimize-commands)
  
- [公用素材](#public-assets)
- [安裝檔案群組](#publishing-file-groups)

<a name="introduction"></a>

## 簡介

套件是用來給 Laravel 新增功能的主要方法。套件可以是任何東西，有像 [Carbon](https://github.com/briannesbitt/Carbon) 這樣可以方便處理日期的套件、或者是像 Spatie 的 [Laravel Media Library](https://github.com/spatie/laravel-medialibrary) 這樣用來處理與 Eloquent Model 關聯檔案的套件。

There are different types of packages. Some packages are stand-alone, meaning they work with any PHP framework. Carbon and Pest are examples of stand-alone packages. Any of these packages may be used with Laravel by requiring them in your `composer.json` file.

另一方面，有的套件是特別為了供 Laravel 使用而設計的。這些套件可能會有 Route、Controller、View、設定檔等等用來增強 Laravel 程式的功能。本篇指南主要就是在討論有關開發這些專為 Laravel 設計的套件。

<a name="a-note-on-facades"></a>

### A Note on Facades

在撰寫 Laravel 專案時，要使用 Contract 還是 Facade，一般來說沒什麼差別，因為兩者的可測試性都是相同的。不過，在開發套件的時候，我們要開發的套件可能無法存取所有的 Laravel 測試輔助函式。若想在測試套件是能像在一般的 Laravel 專案一樣測試，可使用 [Orchestral Testbench](https://github.com/orchestral/testbench) 套件。

<a name="package-discovery"></a>

## Package Discovery

A Laravel application's `bootstrap/providers.php` file contains the list of service providers that should be loaded by Laravel. However, instead of requiring users to manually add your service provider to the list, you may define the provider in the `extra` section of your package's `composer.json` file so that it is automatically loaded by Laravel. In addition to service providers, you may also list any [facades](/docs/{{version}}/facades) you would like to be registered:

```json
"extra": {
    "laravel": {
        "providers": [
            "Barryvdh\\Debugbar\\ServiceProvider"
        ],
        "aliases": {
            "Debugbar": "Barryvdh\\Debugbar\\Facade"
        }
    }
},
```
設定好 Discovery 後，Larave 就會在套件安裝時自動註冊套件的 Service Provider 與 Facade，帶給套件使用者一個方便的體驗。

<a name="opting-out-of-package-discovery"></a>

#### Opting Out of Package Discovery

若有使用到某個套件且想為該套件禁用 Package Discovery 的話，可以將該套件名稱列在專案 `composer.json` 檔中的 `extra` 段落內：

```json
"extra": {
    "laravel": {
        "dont-discover": [
            "barryvdh/laravel-debugbar"
        ]
    }
},
```
可以在 `dont-discover` 指示詞內使用 `*` 字元來禁用所有套件的 Package Discovery：

```json
"extra": {
    "laravel": {
        "dont-discover": [
            "*"
        ]
    }
},
```
<a name="service-providers"></a>

## Service Provider

[Service Provider](/docs/{{version}}/providers) 是套件與 Laravel 間的連結點。Service Provider 負責將各種東西繫結到 Laravel 的 [Service Container](/docs/{{version}}/container) 上，並告訴 Laravel 要在哪裡載入套件的資源，如 View、設定檔、語系檔等。

Service Provider 應繼承 `Illuminate\Support\ServiceProvider` 類別，並包含兩個方法：`register` 與 `boot`。`ServiceProvider` 基礎類別放在 `illuminate/support` Composer 套件中，請在你的套件中將其加為相依性套件。若要瞭解更多有關 Service Provider 的架構與功能，請參見 [Service Provider 的說明文件](/docs/{{version}}/providers)。

<a name="resources"></a>

## 資源

<a name="configuration"></a>

### 設定

一般來說，在製作套件的時候我們會想將套件的設定檔安裝到專案的 `config` 目錄內。這樣一來套件使用者就能輕鬆地覆寫預設設定。若要讓設定檔能安裝到專案內，請在 Service Provider 的 `boot` 方法內呼叫 `publishes` 方法：

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/courier.php' => config_path('courier.php'),
        ]);
    }
然後，當套件使用者執行 Laravel 的 `vendor:publish` 指令時，這些檔案就會被複製到指定的^[安裝](Publish)地點。安裝好設定檔後，就可以像其他設定檔樣存取這些設定值：

    $value = config('courier.option');
> [!WARNING]  
> You should not define closures in your configuration files. They cannot be serialized correctly when users execute the `config:cache` Artisan command.

<a name="default-package-configuration"></a>

#### 預設套件設定

可以將套件自己的設定檔跟安裝到專案裡的設定檔合併。這樣一來，就能讓使用者在設定檔中只定義要覆寫的值。若要合併設定檔，請在 Service Provider 的 `register` 方法中使用 `mergeConfigFrom` 方法。

`mergeConfigFrom` 方法接受套件設定檔的路徑作為其第一個引數，而專案中的設定檔名稱則為其第二個引數：

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/courier.php', 'courier'
        );
    }
> [!WARNING]  
> 該方法只會合併設定陣列中的第一層。若套件使用者只定義了多為陣列中的一部分，則未定義的部分將不會被合併。

<a name="routes"></a>

### Route

若套件中包含 Route，可使用 `loadRoutesFrom` 方法。該方法會自動判斷專案的 Route 是否有被快取，當有快取時將不會載入這些 Route 檔：

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
<a name="migrations"></a>

### Migration

If your package contains [database migrations](/docs/{{version}}/migrations), you may use the `publishesMigrations` method to inform Laravel that the given directory or file contains migrations. When Laravel publishes the migrations, it will automatically update the timestamp within their filename to reflect the current date and time:

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);
    }
<a name="language-files"></a>

### 語系檔

若套件有包含[語系檔](/docs/{{version}}/localization)，可使用 `loadTranslationsFrom` 方法來讓 Laravel 載入這些檔案。舉例來說，若套件名稱為 `courier`，則可在 Service Provider 的 `boot` 方法中這樣寫：

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'courier');
    }
套件的語系檔使用 `package::file.line` (`套件::檔名.行`) 語法慣例來參照。所以，`courier` 套件的 `messages` 檔案中，`welcome` 行可以這樣載入：

    echo trans('courier::messages.welcome');
You can register JSON translation files for your package using the `loadJsonTranslationsFrom` method. This method accepts the path to the directory that contains your package's JSON translation files:

```php
/**
 * Bootstrap any package services.
 */
public function boot(): void
{
    $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
}
```
<a name="publishing-language-files"></a>

#### 安裝語系檔

若想將套件的語系檔安裝到專案的 `lang/vendor` 目錄下，可使用 Service Provider 的 `publishes` 方法。`publishes` 方法接受一組套件路徑與欲安裝位置的陣列。舉例來說，若要為 `courier` 套件安裝語系檔，可以這樣寫：

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'courier');
    
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/courier'),
        ]);
    }
接著，當套件使用者執行 Laravel 的 `vendor:publish` Artisan 指令後，套件的翻譯語系檔就會被安裝到指定的安裝位置內。

<a name="views"></a>

### View

若要向 Laravel 註冊套件的 [View]，我們需要告訴 Laravel 這些 View 存在哪裡。可以使用 Service Provider 的 `loadViewsFrom` 方法。`loadViewsFrom` 方法接受兩個引數：View 樣板的路徑，以及套件的名稱。舉例來說，若套件名稱為 `courier`，則可在 Service Provider 的 `boot` 方法中加入下列程式：

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
    }
套件的 View 使用 `package::view` (`套件::View`) 語法慣例來參照。所以，在 Service Provider 內註冊好 View 的路徑後，就可以在 `courier` 套件中像這樣載入 `dashboard` View：

    Route::get('/dashboard', function () {
        return view('courier::dashboard');
    });
<a name="overriding-package-views"></a>

#### 覆寫套件的 View

使用 `loadViewsFrom` 方法時，Laravel 實際上在兩個地方上都註冊為這個套件的 View 存放位置：專案的 `resources/views/vendor` 目錄，以及你所指定的目錄。所以，若以 `courier` 套件為例，Laravel 會先檢查 `resources/views/vendor/courier` 目錄下是否有開發人員覆寫的自訂版本。若沒找到自訂版本，Laravel 接著才會在呼叫 `loadViewsFrom` 時提供的路徑下搜尋套件的 View。這樣一來，套件使用者就能輕鬆地客製化 / 覆寫套件的 View。

<a name="publishing-views"></a>

#### 安裝 View

若想讓 View 可被安裝到專案的 `resources/views/vendor` 目錄下，可使用 Service Provider 的 `publishes` 方法。`publishes` 方法接受一組套件 View 路徑與欲安裝路徑的陣列：

    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
    
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/courier'),
        ]);
    }
接著，當套件使用者執行 Laravel 的 `vendor:publish` Artisan 指令後，套件的 View 就會被複製到指定的安裝位置內。

<a name="view-components"></a>

### View 元件

若想製作使用 Blade 元件的套件或將元件放在不符合慣例的目錄內，則需要手動註冊元件類別與其 HTML 標籤別名，以讓 Laravel 知道要在哪裡尋找元件。通常，應在套件的 Service Provider 內的 `boot` 方法中註冊你的元件：

    use Illuminate\Support\Facades\Blade;
    use VendorPackage\View\Components\AlertComponent;
    
    /**
     * Bootstrap your package's services.
     */
    public function boot(): void
    {
        Blade::component('package-alert', AlertComponent::class);
    }
註冊好元件後，便可使用其標籤別名來轉譯：

```blade
<x-package-alert/>
```
<a name="autoloading-package-components"></a>

#### 自動載入套件元件

或者，也可以使用 `componentNamespace` 方法來依照慣例自動載入元件類別。舉例來說，`Nightshade` 套件可能包含了放在 `Nightshade\Views\Components` Namespace 下的 `Calendar` 與 `ColorPicker` 元件：

    use Illuminate\Support\Facades\Blade;
    
    /**
     * Bootstrap your package's services.
     */
    public function boot(): void
    {
        Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade');
    }
這樣一來，就可以讓套件元件通過其 Vendor Namespace 來使用 `package-name::` 語法：

```blade
<x-nightshade::calendar />
<x-nightshade::color-picker />
```
Blade 會通過將元件名稱轉為 Pascal 命名法來自動偵測與這個元件關連的類別。也可以使用「點」語法來支援子目錄。

<a name="anonymous-components"></a>

#### 匿名元件

若套件中有匿名元件，則這些套件必須放在套件「view」目錄 (即 [`loadViewsFrom` 方法](#views) 所指定的目錄) 下的 `components` 目錄內。接著，就可以使用套件 View 命名空間作為前置詞來轉譯套件：

```blade
<x-courier::alert />
```
<a name="about-artisan-command"></a>

### 「About」Artisan 指令

Laravel 的內建 `about` Artisan  指令提供了有關專案環境與設定的一覽。套件也可以使用 `AboutCommand` 類別來將額外資訊推入該指令的輸出中。一般來說，可在套件 Service Provider 的 `boot` 方法內加上該資訊：

    use Illuminate\Foundation\Console\AboutCommand;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AboutCommand::add('My Package', fn () => ['Version' => '1.0.0']);
    }
<a name="commands"></a>

## 指令

若要向 Laravel 註冊 Artisan 指令，可使用 `commands` 方法。這個方法接受一組指令類別名稱的陣列。註冊好指令後，就可以使用 [Artisan CLI](/docs/{{version}}/artisan) 來執行這些指令：

    use Courier\Console\Commands\InstallCommand;
    use Courier\Console\Commands\NetworkCommand;
    
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                NetworkCommand::class,
            ]);
        }
    }
<a name="optimize-commands"></a>

### Optimize Commands

Laravel's [`optimize` command](/docs/{{version}}/deployment#optimization) caches the application's configuration, events, routes, and views. Using the `optimizes` method, you may register your package's own Artisan commands that should be invoked when the `optimize` and `optimize:clear` commands are executed:

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->optimizes(
                optimize: 'package:optimize',
                clear: 'package:clear-optimizations',
            );
        }
    }
<a name="public-assets"></a>

## 公用素材

套件也可以有像 JavaScript、CSS、圖片等的素材。若要將這些素材安裝到 `public` 目錄內，可使用 Service Provider 的 `publishes` 方法。在這個範例中，我們也給這些素材嫁了一個 `public` 素材群組標籤，這樣我們就能使用該標籤來輕鬆將一組相關的素材安裝到專案內：

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/courier'),
        ], 'public');
    }
接著，當專案使用者執行 `vendor:publish` 指令後，素材就會被複製到指定的位置。由於使用者通常會需要在每次套件更新後都覆寫這些素材，因此可以使用 `--force` 旗標：

```shell
php artisan vendor:publish --tag=public --force
```
<a name="publishing-file-groups"></a>

## 安裝檔案群組

有時候我們可能會想分別安裝套件素材與資源。舉例來說，我們可以讓使用者安裝套件的設定檔，但不強制使用者安裝套件素材。我們可以通過在 Service Provider 內呼叫 `publishes` 方法時為這些檔案指定「標籤」。舉例來說，我們來在 Service Provider 內 `boot` 方法中為 `courier` 套件定義兩個安裝群組 (`courier-config` 與 `courier-migrations`)：

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/package.php' => config_path('package.php')
        ], 'courier-config');
    
        $this->publishesMigrations([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'courier-migrations');
    }
接著，使用者在執行 `vendor:publish` 指令時就可以使用標籤來分別安裝這些群組：

```shell
php artisan vendor:publish --tag=courier-config
```