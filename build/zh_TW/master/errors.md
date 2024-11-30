---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/67/en-zhtw'
updatedAt: '2024-06-30T08:26:00Z'
contributors: {  }
progress: 41.24
---

# 錯誤處理

- [簡介](#introduction)
- [設定](#configuration)
- [Handling Exceptions](#handling-exceptions)
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

When you start a new Laravel project, error and exception handling is already configured for you; however, at any point, you may use the `withExceptions` method in your application's `bootstrap/app.php` to manage how exceptions are reported and rendered by your application.

The `$exceptions` object provided to the `withExceptions` closure is an instance of `Illuminate\Foundation\Configuration\Exceptions` and is responsible for managing exception handling in your application. We'll dive deeper into this object throughout this documentation.

<a name="configuration"></a>

## 設定

`config/app.php` 設定檔中的 `debug` 選項用來判斷錯誤在實際顯示給使用者時要包含多少資訊。預設情況下，這個選項被設為依照 `APP_DEBUG` 環境變數值，該環境變數儲存於 `.env` 檔內。

在本機上開發時，應將 `APP_DEBUG` 環境變數設為 `true`。 **在正式環境上，這個值一定要是 `false`。若在正式環境上將該值設為 `true`，則會有將機敏設定值暴露給應用程式終端使用者的風險。**

<a name="handling-exceptions"></a>

## Handling Exceptions

<a name="reporting-exceptions"></a>

### 回報 Exception

In Laravel, exception reporting is used to log exceptions or send them to an external service [Sentry](https://github.com/getsentry/sentry-laravel) or [Flare](https://flareapp.io). By default, exceptions will be logged based on your [logging](/docs/{{version}}/logging) configuration. However, you are free to log exceptions however you wish.

If you need to report different types of exceptions in different ways, you may use the `report` exception method in your application's `bootstrap/app.php` to register a closure that should be executed when an exception of a given type needs to be reported. Laravel will determine what type of exception the closure reports by examining the type-hint of the closure:

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (InvalidOrderException $e) {
            // ...
        });
    })
When you register a custom exception reporting callback using the `report` method, Laravel will still log the exception using the default logging configuration for the application. If you wish to stop the propagation of the exception to the default logging stack, you may use the `stop` method when defining your reporting callback or return `false` from the callback:

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (InvalidOrderException $e) {
            // ...
        })->stop();
    
        $exceptions->report(function (InvalidOrderException $e) {
            return false;
        });
    })
> [!NOTE]  
> 若要為給定的例外自訂 Exception 回報，可使用 [Reportable 的例外](/docs/{{version}}/errors#renderable-exceptions)。

<a name="global-log-context"></a>

#### 全域 Log 上下文

If available, Laravel automatically adds the current user's ID to every exception's log message as contextual data. You may define your own global contextual data using the `context` exception method in your application's `bootstrap/app.php` file. This information will be included in every exception's log message written by your application:

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->context(fn () => [
            'foo' => 'bar',
        ]);
    })
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

Sometimes you may need to report an exception but continue handling the current request. The `report` helper function allows you to quickly report an exception without rendering an error page to the user:

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

If you would like to ensure that a single instance of an exception is only ever reported once, you may invoke the `dontReportDuplicates` exception method in your application's `bootstrap/app.php` file:

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReportDuplicates();
    })
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

As noted above, even when you register a custom exception reporting callback using the `report` method, Laravel will still log the exception using the default logging configuration for the application; however, since the log level can sometimes influence the channels on which a message is logged, you may wish to configure the log level that certain exceptions are logged at.

To accomplish this, you may use the `level` exception method in your application's `bootstrap/app.php` file. This method receives the exception type as its first argument and the log level as its second argument:

    use PDOException;
    use Psr\Log\LogLevel;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->level(PDOException::class, LogLevel::CRITICAL);
    })
<a name="ignoring-exceptions-by-type"></a>

### Ignoring Exceptions by Type

When building your application, there will be some types of exceptions you never want to report. To ignore these exceptions, you may use the `dontReport` exception method in your application's `boostrap/app.php` file. Any class provided to this method will never be reported; however, they may still have custom rendering logic:

    use App\Exceptions\InvalidOrderException;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport([
            InvalidOrderException::class,
        ]);
    })
Internally, Laravel already ignores some types of errors for you, such as exceptions resulting from 404 HTTP errors or 419 HTTP responses generated by invalid CSRF tokens. If you would like to instruct Laravel to stop ignoring a given type of exception, you may use the `stopIgnoring` exception method in your application's `boostrap/app.php` file:

    use Symfony\Component\HttpKernel\Exception\HttpException;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->stopIgnoring(HttpException::class);
    })
<a name="rendering-exceptions"></a>

### 轉譯 Exception

By default, the Laravel exception handler will convert exceptions into an HTTP response for you. However, you are free to register a custom rendering closure for exceptions of a given type. You may accomplish this by using the `render` exception method in your application's `boostrap/app.php` file.

The closure passed to the `render` method should return an instance of `Illuminate\Http\Response`, which may be generated via the `response` helper. Laravel will determine what type of exception the closure renders by examining the type-hint of the closure:

    use App\Exceptions\InvalidOrderException;
    use Illuminate\Http\Request;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (InvalidOrderException $e, Request $request) {
            return response()->view('errors.invalid-order', [], 500);
        });
    })
You may also use the `render` method to override the rendering behavior for built-in Laravel or Symfony exceptions such as `NotFoundHttpException`. If the closure given to the `render` method does not return a value, Laravel's default exception rendering will be utilized:

    use Illuminate\Http\Request;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found.'
                ], 404);
            }
        });
    })
<a name="rendering-exceptions-as-json"></a>

#### Rendering Exceptions as JSON

When rendering an exception, Laravel will automatically determine if the exception should be rendered as an HTML or JSON response based on the `Content-Type` header of the request. If you would like to customize how Laravel determines whether to render HTML or JSON exception responses, you may utilize the `shouldRenderJsonWhen` method:

    use Illuminate\Http\Request;
    use Throwable;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('admin/*')) {
                return true;
            }
    
            return $request->expectsJson();
        });
    })
<a name="customizing-the-exception-response"></a>

#### Customizing the Exception Response

Rarely, you may need to customize the entire HTTP response rendered by Laravel's exception handler. To accomplish this, you may register a response customization closure using the `respond` method:

    use Symfony\Component\HttpFoundation\Response;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }
    
            return $response;
        });
    })
<a name="renderable-exceptions"></a>

### Reportable and Renderable Exceptions

Instead of defining custom reporting and rendering behavior in your application's `boostrap/app.php` file, you may define `report` and `render` methods directly on your application's exceptions. When these methods exist, they will automatically be called by the framework:

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

To take a random sample rate of exceptions, you may use the `throttle` exception method in your application's `bootstrap/app.php` file. The `throttle` method receives a closure that should return a `Lottery` instance:

    use Illuminate\Support\Lottery;
    use Throwable;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable Throwable) {
            return Lottery::odds(1, 1000);
        });
    })
也可以根據 Exception 的型別來有條件地採樣。若只想採樣特定 Exception 類別的實體，只需要針對該類別回傳 `Lottery` 實體即可：

    use App\Exceptions\ApiMonitoringException;
    use Illuminate\Support\Lottery;
    use Throwable;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable Throwable) {
            if ($e instanceof ApiMonitoringException) {
                return Lottery::odds(1, 1000);
            }
        });
    })
若不回傳 `Lottery` 而回傳 `Limit` 實體的話，就可以針對 Exception 的 Log 或傳送到外部錯誤追蹤服務進行頻率限制。這麼做可以避免突然增加的 Exception 使 Log 暴增，例如當網站使用的第三方服務突然離線的情況：

    use Illuminate\Broadcasting\BroadcastException;
    use Illuminate\Cache\RateLimiting\Limit;
    use Throwable;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable Throwable) {
            if ($e instanceof BroadcastException) {
                return Limit::perMinute(300);
            }
        });
    })
預設情況下，會使用 Exception 的類別名稱來作為頻率限制的索引鍵。可以在 `Limit` 上使用 `by` 方法來指定自定的索引鍵：

    use Illuminate\Broadcasting\BroadcastException;
    use Illuminate\Cache\RateLimiting\Limit;
    use Throwable;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable Throwable) {
            if ($e instanceof BroadcastException) {
                return Limit::perMinute(300)->by($e->getMessage());
            }
        });
    })
當然，可以在不同的 Exception 間混合使用 `Lottery` 與 `Limit` 實體：

    use App\Exceptions\ApiMonitoringException;
    use Illuminate\Broadcasting\BroadcastException;
    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Support\Lottery;
    use Throwable;
    
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->throttle(function (Throwable Throwable) {
            return match (true) {
                $e instanceof BroadcastException => Limit::perMinute(300),
                $e instanceof ApiMonitoringException => Lottery::odds(1, 1000),
                default => Limit::none(),
            };
        });
    })
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
