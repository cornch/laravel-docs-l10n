---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/165/en-zhtw
progress: 92
updatedAt: '2024-06-30T08:27:00Z'
---

# 升級指南

- [從 9.x 版升級至 10.0 版](#upgrade-10.0)

<a name="high-impact-changes"></a>

## 高度影響的更改

<div class="content-list" markdown="1">

- [更新相依性套件](#updating-dependencies)
- [更新 Minimum Stability](#updating-minimum-stability)

</div>

<a name="medium-impact-changes"></a>

## 中度影響的更改

<div class="content-list" markdown="1">

- [資料庫運算式](#database-expressions)
- [Model 的「Dates」屬性](#model-dates-property)
- [Monolog 3](#monolog-3)
- [Redis 的快取 Tag](#redis-cache-tags)
- [Service Mock](#service-mocking)
- [語系檔目錄](#language-directory)

</div>

<a name="low-impact-changes"></a>

## 低影響的更改

<div class="content-list" markdown="1">

- [閉包的 Validation Rule 訊息](#closure-validation-rule-messages)
- [Form Request `after` Method](#form-request-after-method)
- [Public Path Binding](#public-path-binding)
- [Query Exception 的 Constructor](#query-exception-constructor)
- [Rate Limiter 的回傳值](#rate-limiter-return-values)
- [`Redirect::home` 方法](#redirect-home)
- [`Bus::dispatchNow` 方法](#dispatch-now)
- [The `registerPolicies` Method](#register-policies)
- [ULID 欄位](#ulid-columns)

</div>

<a name="upgrade-10.0"></a>

## 從 9.x 版升級至 10.0 版

<a name="estimated-upgrade-time-??-minutes"></a>

#### 預計升級所需時間：10 分鐘

> **Note** 雖然我們已經儘可能地在本說明文件中涵蓋所有^[中斷性變更](Breaking Change)。不過，在 Laravel 中，有些中斷性變更存在一些比較不明顯的地方，且這些更改中幾乎不太會影響到你的專案。 想節省時間嗎？可以使用 [Laravel Shift](https://laravelshift.com/) 來協助你快速升級你的專案。

<a name="updating-dependencies"></a>

### 相依性套件更新

**受影響的可能：高**

#### 最低版本要求為 PHP 8.1.0

Laravel 現在要求的 PHP 最低版本為 8.1.0 版。

#### Composer 2.2.0 Required

Laravel now requires [Composer](https://getcomposer.org) 2.2.0 or greater.

#### Composer 相依性套件

請在專案的 `composer.json` 檔案中更新下列相依性套件：

<div class="content-list" markdown="1">

- `laravel/framework` 升級為 `^10.0`
- `laravel/sanctum` to `^3.2`
- `doctrine/dbal` to `^3.0`
- `spatie/laravel-ignition` 升級為 `^2.0`
- `laravel/passport` to `^11.0` ([Upgrade Guide](https://github.com/laravel/passport/blob/11.x/UPGRADE.md))

</div>

If you are upgrading to Sanctum 3.x from the 2.x release series, please consult the [Sanctum upgrade guide](https://github.com/laravel/sanctum/blob/3.x/UPGRADE.md).

Furthermore, if you wish to use [PHPUnit 10](https://phpunit.de/announcements/phpunit-10.html), you should delete the `processUncoveredFiles` attribute from the `<coverage>` section of your application's `phpunit.xml` configuration file. Then, update the following dependencies in your application's `composer.json` file:

<div class="content-list" markdown="1">

- `nunomaduro/collision` to `^7.0`
- `phpunit/phpunit` to `^10.0`

</div>

最後，請檢視你的專案使用的其他第三方套件，確認一下是否有使用支援 Laravel 10 的版本。

<a name="updating-minimum-stability"></a>

#### Minimum Stability

You should update the `minimum-stability` setting in your application's `composer.json` file to `stable`. Or, since the default value of `minimum-stability` is `stable`, you may delete this setting from your application's `composer.json` file:

```json
"minimum-stability": "stable",
```

### Application

<a name="public-path-binding"></a>

#### Public Path Binding

**受影響的可能：低**

If your application is customizing its "public path" by binding `path.public` into the container, you should instead update your code to invoke the `usePublicPath` method offered by the `Illuminate\Foundation\Application` object:

```php
app()->usePublicPath(__DIR__.'/public');
```

### Authorization

<a name="register-policies"></a>

### The `registerPolicies` Method

**受影響的可能：低**

The `registerPolicies` method of the `AuthServiceProvider` is now invoked automatically by the framework. Therefore, you may remove the call to this method from the `boot` method of your application's `AuthServiceProvider`.

### 快取

<a name="redis-cache-tags"></a>

#### Redis 的快取 Tag

**受影響的可能性：中等**

Redis [cache tag](/docs/{{version}}/cache#cache-tags) support has been rewritten for better performance and storage efficiency. In previous releases of Laravel, stale cache tags would accumulate in the cache when using Redis as your application's cache driver.

不過，為了正確地修建過時的快取 Tag，請在專案的 `App\Console\Kernel` 類別內[排程](/docs/{{version}}/scheduling)呼叫 Laravel 中全新的 `cache:prune-stale-tags` Artisan 指令：

    $schedule->command('cache:prune-stale-tags')->hourly();

### 資料庫

<a name="database-expressions"></a>

#### 資料庫運算式

**受影響的可能性：中等**

資料庫的「^[Expression](運算式)」(通常由 `DB::raw` 產生) 在 Laravel 10.x 中已被重寫，以在未來能提供更多功能。特別是，^[Grammer](語法) 的原始字串值現在已改用 Expression 的 `getValue(Grammar $grammar)` 方法來取得。現在已不再支援通過 `(string)` 來將 Expression 型別轉換為字串。

**一般來說，這項更改應該不會影響到終端使用者的專案**；不過，如果你的專案有手動使用 `(string)` 來將資料庫 Expression 型別轉換為字串，或是有直接在 Expression 上呼叫 `__toString` 方法，則請將這些程式碼更新為呼叫 `getValue` 方法：

```php
use Illuminate\Support\Facades\DB;

$expression = DB::raw('select 1');

$string = $expression->getValue(DB::connection()->getQueryGrammar());
```

<a name="query-exception-constructor"></a>

#### Query Exception 的 Constructor

**受影響的可能：非常低**

The `Illuminate\Database\QueryException` constructor now accepts a string connection name as its first argument. If your application is manually throwing this exception, you should adjust your code accordingly.

<a name="ulid-columns"></a>

#### ULID 欄位

**受影響的可能：低**

在 Migration 中，若在不提供任何引數的情況下執行 `ulid` 方法時，該欄位的名稱現在會是 `ulid`。在先前版本的 Laravel 中，在不提供引數的情況下呼叫該方法時，所建立的欄位被錯誤地命名為 `uuid`：

    $table->ulid();

若要在呼叫 `ulid` 方法時明確指定欄位名稱，可傳入欄位名稱給該方法：

    $table->ulid('ulid');

### Eloquent

<a name="model-dates-property"></a>

#### Model 的「Dates」屬性

**受影響的可能性：中等**

Eloquent Model 中停止支援 (Deprecated) 的 `$dates` 屬性已被移除。請使用 `$casts` 屬性代替：

```php
protected $casts = [
    'deployed_at' => 'datetime',
];
```

### 本土化

<a name="language-directory"></a>

#### 語系檔目錄

**受影響的可能：無**

雖然與現有專案無關，不過 Laravel 的專案 Skeleton 現在預設不包含 `lang` 目錄。在撰寫新的 Laravel 專案時，可以使用 `lang:publish` Artisan 指令來安裝該目錄：

```shell
php artisan lang:publish
```

### 日誌

<a name="monolog-3"></a>

#### Monolog 3

**受影響的可能性：中等**

Laravel 的 Monologo 相依性套件已升級為 Monolog 3.x 版。若你有在專案中直接用到 Monologo，請檢視 Monolog 的[升級指南](https://github.com/Seldaek/monolog/blob/main/UPGRADE.md)。

If you are using third-party logging services such as BugSnag or Rollbar, you may need to upgrade those third-party packages to a version that supports Monolog 3.x and Laravel 10.x.

### 佇列 - Queue

<a name="dispatch-now"></a>

#### `Bus::dispatchNow` 方法

**受影響的可能：低**

停止支援 (Deprecated) 的 `Bus::dispatchNow` 與 `dispatch_now` 方法現已移除。請分別改用 `Bus::dispatchSync` 與 `dispatch_sync` 方法。

### 路由

<a name="middleware-aliases"></a>

#### Middleware 的別名

**受影響的可能性：可選**

在新的 Laravel 專案中，`App\Http\Kernel` 的 `$routeMiddleware` 屬性已被重新命名為 `$middlewareAliases` 以更好地反應其目的。你可以在現有的專案中重新命名此屬性。不過，重新命名這個屬性並不是必要的。

<a name="rate-limiter-return-values"></a>

#### Rate Limiter 的回傳值

**受影響的可能：低**

在呼叫 `RateLimiter::attempt` 方法時，該方法現在會回傳提供給該方法閉包的回傳值。若該閉包沒有回傳值，或是回傳了 `null`，則 `attempt` 方法會回傳 `true`：

```php
$value = RateLimiter::attempt('key', 10, fn () => ['example'], 1);

$value; // ['example']
```

<a name="redirect-home"></a>

#### `Redirect::home` 方法

**受影響的可能：非常低**

停止支援 (Deprecated) 的 `Redirect::home` 方法現已被移除。請改為直接使用命名 Route 來做重新導向：

```php
return Redirect::route('home');
```

### 測試

<a name="service-mocking"></a>

#### Service Mock

**受影響的可能性：中等**

停止支援 (Deprecated) 的 `MocksApplicationServices` Trait 已從 Laravel Framework 中移除。這個 Trait 提供了如 `expectsEvents`、`expectesJobs`、與 `expectesNotifications` 等的測試方法。

If your application uses these methods, we recommend you transition to `Event::fake`, `Bus::fake`, and `Notification::fake`, respectively. You can learn more about mocking via fakes in the corresponding documentation for the component you are attempting to fake.

### 表單驗證

<a name="closure-validation-rule-messages"></a>

#### 閉包的 Validation Rule 訊息

**受影響的可能：非常低**

在撰寫基於閉包的自定 Validation Rule 時，若呼叫 `$fail` 回呼超過一次時，原本訊息會被取代，現在改為會將訊息加到陣列的尾端。一般來說，這應該不會影響到你的專案。

此外，`$fail` 回呼現在會回傳一個物件。如果原本有在 Validation 閉包中 ^[Type-Hint](型別提示) 回傳型別，則可能有需要更新：

```php
public function rules()
{
    'name' => [
        function ($attribute, $value, $fail) {
            $fail('validation.translation.key')->translate();
        },
    ],
}
```

<a name="form-request-after-method"></a>

#### Form Request After Method

**受影響的可能：非常低**

Within form requests, the `after` method is now [reserved by Laravel](https://github.com/laravel/framework/pull/46757). If your form requests define an `after` method, the method should be renamed or modified to utilize the new "after validation" feature of Laravel's form requests.

<a name="miscellaneous"></a>

### 其他

我們也建議你檢視 `laravel/laravel` [GitHub 存放庫](https://github.com/laravel/laravel)上的更改。雖然這些更改中大多數都不是必須要進行的，但你可能也會想讓專案中的這些檔案保持同步。其中一些修改有在本升級指南中提到，但有些其他的更改 (如設定檔的更改或註解等) 就沒有提到。

你可以輕鬆地通過 [GitHub 的比較工具](https://github.com/laravel/laravel/compare/9.x...10.x)來檢視這些更改，並自行判斷哪些修改對你來說是重要的。不過，GitHub 比較工具上顯示的許多更改都是因為 Laravel 選用了 PHP 原生型別導致的。這些更改是能向下相容的，而在升級到 Laravel 10 時，可選擇是否要加上這些原生型別提示。
