---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/67/en-zhtw'
updatedAt: '2024-06-30T08:26:00Z'
contributors: {  }
progress: 41.24
---

# 錯誤處理

- [簡介](#introduction)
- [設定](#configuration)
- [Exception Handler](#the-exception-handler)
  - [回報 Exception](#reporting-exceptions)
  - [Exception 的 Log 等級](#exception-log-levels)
  - [Ignoring Exceptions by Type](#ignoring-exceptions-by-type)
  - [轉譯 Exception](#rendering-exceptions)
  - [Reportable and Renderable Exceptions](#renderable-exceptions)
  
- [頻率限制 Exception 的回報](#throttling-exceptions)
- [HTTP Exception](#http-exceptions)
  - [自訂 HTTP 錯誤的頁面](#custom-http-error-pages)
  

<a name="introduction"></a>

## 簡介

在開始新的 Laravel 專案時，Laravel 已經先幫你設定好錯誤與 ^[Exception Handler](%E4%BE%8B%E5%A4%96%E8%99%95%E7%90%86%E5%B8%B8%E5%BC%8F)。在你的專案中^[擲回](Throw)的所有 Exception 都會由 `App\Exceptions\Handler` 負責紀錄 ^[Log](%E6%97%A5%E8%AA%8C) 並轉譯給使用者。我們會在這篇說明文件中深入瞭解這個類別。

<a name="configuration"></a>

## 設定

`config/app.php` 設定檔中的 `debug` 選項用來判斷錯誤在實際顯示給使用者時要包含多少資訊。預設情況下，這個選項被設為依照 `APP_DEBUG` 環境變數值，該環境變數儲存於 `.env` 檔內。

在本機上開發時，應將 `APP_DEBUG` 環境變數設為 `true`。 **在正式環境上，這個值一定要是 `false`。若在正式環境上將該值設為 `true`，則會有將機敏設定值暴露給應用程式終端使用者的風險。**

<a name="the-exception-handler"></a>

## Exception Handler

<a name="reporting-exceptions"></a>

### 回報 Exception

所有的 Exception 都由 `App\Exceptions\Handler` 類別負責處理。該類別中包含了一個 `register` 方法，可用來註冊所有自訂的 Exception 回報與轉譯回呼。我們來詳細看看其中各個概念。「回報 Exception」就是指將例外紀錄到 ^[Log](%E6%97%A5%E8%AA%8C)，或是傳送到如 [Flare](https://flareapp.io)、[Bugsnag](https://bugsnag.com)、[Sentry](https://github.com/getsentry/sentry-laravel) 等外部服務。預設情況下，Laravel 會使用專案的[Log](/docs/{{version}}/logging) 設定來紀錄 Exception。不過，我們也可以隨意調整 Exception 要如何紀錄。

若想以不同的方式回報不同類型的 Exception，可以使用 `reportable` 方法來註冊一個閉包。這個閉包會在給定類型的 Exception 需要回報時被呼叫。Laravel 會自動使用該閉包的^[型別提示](Type-Hint)來判斷該閉包接受什麼類型的 Exception：

    use App\Exceptions\InvalidOrderException;
    
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (InvalidOrderException $e) {
            // ...
        });
    }
使用 `reportable` 方法定義自訂的 Exception 回報回呼時，Laravel 還是會使用專案的預設 Log 設定來紀錄例外。若想停止將 Exception ^[傳播](Propagation)給預設的日誌 Stack，請在定義回報回呼時使用 `stop` 方法，或是在該回呼內回傳 `false`：

    $this->reportable(function (InvalidOrderException $e) {
        // ...
    })->stop();
    
    $this->reportable(function (InvalidOrderException $e) {
        return false;
    });
> [!NOTE]  
> 若要為給定的例外自訂 Exception 回報，可使用 [Reportable 的例外](/docs/{{version}}/errors#renderable-exceptions)。

<a name="global-log-context"></a>

#### 全域 Log 上下文

當有目前使用者 ID 的時候，Laravel 會自動將使用者 ID 加到所有的例外 Log 訊息，以作為^[上下文](Context)資料。可以在專案中 `App\Exceptions\Handler` 類別內定義一個 `context` 方法來定義你自己的全域上下文資料。這個資料會被包含在專案輸出的所有例外 Log 訊息中：

    /**
     * Get the default context variables for logging.
     *
     * @return array<string, mixed>
     */
    protected function context(): array
    {
        return array_merge(parent::context(), [
            'foo' => 'bar',
        ]);
    }
<a name="exception-log-context"></a>

#### Exception Log 的上下文

為所有 Log 訊息都新增額外的上下文可能會很實用，但有些特別的 Exception 可能會有一些獨特的上下文，而我們也想將這類上下文加到 Log 上。只要在 Exception 中定義一個 `context` 方法，就可以指定與該 Exception 相關的資料，將這些資料包含到例外的 Log 中：

    <?php
    
    namespace App\Exceptions;
    
    use Exception;
    
    class InvalidOrderException extends Exception
    {
        // ...
    
        /**
         * Get the exception's context information.
         *
         * @return array<string, mixed>
         */
        public function context(): array
        {
            return ['order_id' => $this->orderId];
        }
    }
<a name="the-report-helper"></a>

#### `report` 輔助函式

有時候，我們可能會想回報某個 Exception，但又想繼續執行目前的 Request。使用 `report` 輔助函式，就能輕鬆地在不轉譯出錯誤頁面的情況下使用 Exception Handler 來回報這個 Exception：

    public function isValid(string $value): bool
    {
        try {
            // Validate the value...
        } catch (Throwable $e) {
            report($e);
    
            return false;
        }
    }
<a name="deduplicating-reported-exceptions"></a>

#### 避免重複回報的 Exception

若你在專案中使用 `report` 函式，偶爾可能會發生同一個 Exception 被回報多次的情況，並在 Log 中造成重複的項目。

若想避免同一個 Exception 實體被回報多次，可以在 `App\Exceptions\Handler` 類別中將 `$withoutDuplicates` 屬性設為 `true`：

```php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Indicates that an exception instance should only be reported once.
     *
     * @var bool
     */
    protected $withoutDuplicates = true;

    // ...
}
```
現在，當使用相同 Exception 實體來呼叫 `report` 輔助函式時，就只有第一次呼叫會被回報：

```php
$original = new RuntimeException('Whoops!');

report($original); // reported

try {
    throw $original;
} catch (Throwable $caught) {
    report($caught); // ignored
}

report($original); // ignored
report($caught); // ignored
```
<a name="exception-log-levels"></a>

### Exception 的 Log 等級

在將訊息寫入專案的 [Log](/docs/{{version}}/logging) 時，這些訊息會以特定的 [Log 等級](/docs/{{version}}/logging#log-levels)寫入。這個等級即代表該日誌訊息的嚴重程度。

上面也提過，即使使用了 `reportable` 方法註冊自定的 Exception 回報回呼，Laravel 也還是會使用專案預設的 Log 設定來記錄該 Exception。不過，由於 Log 等級有時候會影響訊息會被記錄在哪些通道內，因此有時候我們可能會想設定某個特定的 Exception 要被記錄在哪個 Log 等級上。

若要設定 Exception 的等級，請在專案的 Exception Handler 內定義一個 `$levels` 屬性。該屬性應包含一組陣列，包含 Exception 型別與其關聯的 Log 等級：

    use PDOException;
    use Psr\Log\LogLevel;
    
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        PDOException::class => LogLevel::CRITICAL,
    ];
<a name="ignoring-exceptions-by-type"></a>

### Ignoring Exceptions by Type

在製作專案時，有一些類型的 Exception 可能是我們想永遠忽略不回報的。若要忽略這類 Exception，請在專案的 Exception Handler 中定義一個 `$dontReport` 屬性。加入到此屬性的任何類別都不會被回報。不過，這些 Exception 還是可以有其自定轉譯邏輯：

    use App\Exceptions\InvalidOrderException;
    
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        InvalidOrderException::class,
    ];
在 Laravel 內部，已經有一些型別的錯誤是會預設被忽略的，例如 404 HTTP 錯誤與無效 CSRF Token 產生的 419 HTTP Response 所產生的 Exception 等。若想讓 Laravel 不要忽略特定類型的 Exception，可以在 Exception Handler 的 `register` 方法中呼叫 `stopIgnoring` 方法：

    use Symfony\Component\HttpKernel\Exception\HttpException;
    
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->stopIgnoring(HttpException::class);
    
        // ...
    }
<a name="rendering-exceptions"></a>

### 轉譯 Exception

預設情況下，Laravel 的 Exception Handler 會幫你把 Exception 轉成 HTTP Response。不過，我們也可以自由地為某個類型的 Exception 註冊自訂^[轉譯閉包](Rendering Closure)。只要在 Exception Handler 內呼叫 `renderable` 方法，就可以註冊轉譯閉包。

傳給 `renderable` 方法的閉包應回傳一個 `Illuminate\Http\Response` 的實體。可以使用 `response` 輔助函式來產生該實體。Laravel 會依照該閉包的型別提示來判斷這個閉包能轉移哪種類型的 Exception：

    use App\Exceptions\InvalidOrderException;
    use Illuminate\Http\Request;
    
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (InvalidOrderException $e, Request $request) {
            return response()->view('errors.invalid-order', [], 500);
        });
    }
也可以使用 `renderable` 方法來複寫 Laravel 或 Symfony 內建 Exception 的轉移行外。如：`NotFoundHttpException`。若傳給 `renderable` 方法的閉包未回傳任何值，則會使用 Laravel 的預設 Exception 轉譯：

    use Illuminate\Http\Request;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found.'
                ], 404);
            }
        });
    }
<a name="renderable-exceptions"></a>

### Reportable and Renderable Exceptions

除了在 Exception Handler 的 `register` 方法中定義回報與轉譯行為，也可以直接在專案的 Exception 中定義 `report` 與 `render` 方法。當 Exception 中有這些方法時，Laravel 會自動呼叫該方法：

    <?php
    
    namespace App\Exceptions;
    
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    
    class InvalidOrderException extends Exception
    {
        /**
         * Report the exception.
         */
        public function report(): void
        {
            // ...
        }
    
        /**
         * Render the exception into an HTTP response.
         */
        public function render(Request $request): Response
        {
            return response(/* ... */);
        }
    }
若你的 Exception 繼承的 Exception 已經是^[可轉譯的](Renderable)了 (如 Laravel 或 Symfony 內建的 Exception)，可在該 Exception 的 `render` 方法內回傳 `false` 來轉譯某個 Exception 的預設 HTTP Response：

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): Response|bool
    {
        if (/** Determine if the exception needs custom rendering */) {
    
            return response(/* ... */);
        }
    
        return false;
    }
若你的 Exception 中包含了只有在特定情況下才會使用的自訂回報邏輯，則可讓 Laravel 在某些時候使用預設的 Exception 處理設定來回報這個 Exception。若要這麼做，請在該 Exception 的 `report` 方法內回傳 `false`：

    /**
     * Report the exception.
     */
    public function report(): bool
    {
        if (/** Determine if the exception needs custom reporting */) {
    
            // ...
    
            return true;
        }
    
        return false;
    }
> [!NOTE]  
> 可以在 `report` 方法中型別提示任何的^[相依性](Dependency)。Laravel 的 [Service Container](/docs/{{version}}/container) 會自動插入這些相依性。

<a name="throttling-reported-exceptions"></a>

### 頻率限制回報的 Exception

若你的專案會回報大量的 Exception，則你可能會想針對實際要被 Log 與傳送到專案外部錯誤追蹤服務的 Exception 進行頻率限制。

若要對 Exception 採用隨機的採樣率，可以在 Exception Handler 的 `throttle` 方法中回傳 Lottery 實體。若 `App\Exceptions\Handler` 類別中沒有此方法，只需要將其加入類別即可：

```php
use Illuminate\Support\Lottery;
use Throwable;

/**
 * Throttle incoming exceptions.
 */
protected function throttle(Throwable $e): mixed
{
    return Lottery::odds(1, 1000);
}
```
也可以根據 Exception 的型別來有條件地採樣。若只想採樣特定 Exception 類別的實體，只需要針對該類別回傳 `Lottery` 實體即可：

```php
use App\Exceptions\ApiMonitoringException;
use Illuminate\Support\Lottery;
use Throwable;

/**
 * Throttle incoming exceptions.
 */
protected function throttle(Throwable $e): mixed
{
    if ($e instanceof ApiMonitoringException) {
        return Lottery::odds(1, 1000);
    }
}
```
若不回傳 `Lottery` 而回傳 `Limit` 實體的話，就可以針對 Exception 的 Log 或傳送到外部錯誤追蹤服務進行頻率限制。這麼做可以避免突然增加的 Exception 使 Log 暴增，例如當網站使用的第三方服務突然離線的情況：

```php
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Cache\RateLimiting\Limit;
use Throwable;

/**
 * Throttle incoming exceptions.
 */
protected function throttle(Throwable $e): mixed
{
    if ($e instanceof BroadcastException) {
        return Limit::perMinute(300);
    }
}
```
預設情況下，會使用 Exception 的類別名稱來作為頻率限制的索引鍵。可以在 `Limit` 上使用 `by` 方法來指定自定的索引鍵：

```php
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Cache\RateLimiting\Limit;
use Throwable;

/**
 * Throttle incoming exceptions.
 */
protected function throttle(Throwable $e): mixed
{
    if ($e instanceof BroadcastException) {
        return Limit::perMinute(300)->by($e->getMessage());
    }
}
```
當然，可以在不同的 Exception 間混合使用 `Lottery` 與 `Limit` 實體：

```php
use App\Exceptions\ApiMonitoringException;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Lottery;
use Throwable;

/**
 * Throttle incoming exceptions.
 */
protected function throttle(Throwable $e): mixed
{
    return match (true) {
        $e instanceof BroadcastException => Limit::perMinute(300),
        $e instanceof ApiMonitoringException => Lottery::odds(1, 1000),
        default => Limit::none(),
    };
}
```
<a name="http-exceptions"></a>

## HTTP Exception

有的 Exception 是用來描述伺服器的 HTTP 錯誤代碼。例如，這些 Exception 可能是：「找不到頁面」錯誤 (404)、「未經授權」錯誤 (401) 等，甚至是開發人員造成的 500 錯誤。在你的程式中的任何地點內，若要產生這種 Response，可使用 `abort` ^[輔助函式](Helper)：

    abort(404);
<a name="custom-http-error-pages"></a>

### 自訂 HTTP 錯誤頁面

在 Laravel 中，要給各種 HTTP 狀態碼顯示自訂錯誤頁非常容易。舉例來說，若要自訂 404 HTTP 狀態碼的錯誤頁面，請建立 `resources/views/errors/404.blade.php` View 樣板。程式中只要產生 404 錯誤，就會轉譯這個 View。在該目錄中的 View 應以對應的 HTTP 狀態碼來命名。由 `abort` 函式產生的 `Symfony\Component\HttpKernel\Exception\HttpException` 實體會以 `$exception` 變數傳給該 View：

    <h2>{{ $exception->getMessage() }}</h2>
可以使用 `vendor:publish` Artisan 指令來將 Laravel 的預設錯誤頁樣板^[安裝](Publish)到專案內。安裝好樣板後，就可以隨意自訂這些樣板：

```shell
php artisan vendor:publish --tag=laravel-errors
```
<a name="fallback-http-error-pages"></a>

#### ^[遞補](Fallback)的 HTTP 錯誤頁

可以為給定的一系列 HTTP 狀態碼定義一個「遞補的」錯誤頁面。當發生的 HTTP 狀態碼沒有對應頁面時，就會轉譯這個遞補的頁面。若要使用遞補頁面，請在專案的 `resources/views/errors` 目錄下定義一個 `4xx.blade.php` 樣板與 `5xx.blade.php` 樣板。
