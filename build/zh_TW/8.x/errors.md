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
  - [依照型別忽略 Exception](#ignoring-exceptions-by-type)
  - [轉譯 Exception](#rendering-exceptions)
  - [Reportable 與 Renderable 的 Exception](#renderable-exceptions)
  - [依型別映射 Exception](#mapping-exceptions-by-type)
  
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

所有的 Exception 都由 `App\Exceptions\Handler` 類別負責處理。該類別中包含了一個 `register` 方法，可用來註冊所有自訂的 Exception 回報與轉譯回呼。我們來詳細看看其中各個概念。「回報 Exception」就是指將例外紀錄到 ^[Log](%E6%97%A5%E8%AA%8C)，或是傳送到如 [Flare](https://flareapp.io)、[Bugsnag](https://bugsnag.com)、[Sentry](https://github.com/getsentry/sentry-laravel)⋯⋯等外部服務。預設情況下，Laravel 會使用專案的[Log](/docs/{{version}}/logging) 設定來紀錄 Exception。不過，我們也可以隨意調整 Exception 要如何紀錄。

舉例來說，如果想以不同的方式回報不同類型的 Exception，可以使用 `reportable` 方法來註冊一個閉包。這個閉包會在給定類型的 Exception 需要回報時被呼叫。Laravel 會自動使用該閉包的^[型別提示](Type-Hint)來推導該閉包接受什麼類型的 Exception：

    use App\Exceptions\InvalidOrderException;
    
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (InvalidOrderException $e) {
            //
        });
    }
使用 `reportable` 方法定義自訂的 Exception 回報回呼時，Laravel 還是會使用專案的預設 Log 設定來紀錄例外。若想停止將 Exception ^[傳播](Propagation)給預設的日誌 Stack，請在定義回報回呼時使用 `stop` 方法，或是在該回呼內回傳 `false`：

    $this->reportable(function (InvalidOrderException $e) {
        //
    })->stop();
    
    $this->reportable(function (InvalidOrderException $e) {
        return false;
    });
> [!TIP]  
> 若要為給定的例外自訂 Exception 回報，可使用 [Reportable 的例外](/docs/{{version}}/errors#renderable-exceptions)。

<a name="global-log-context"></a>

#### 全域 Log 上下文

當有目前使用者 ID 的時候，Laravel 會自動將使用者 ID 加到所有的例外 Log 訊息，以作為^[上下文](Context)資料。可以複寫專案中 `App\Exceptions\Handler` 類別的 `context` 來定義你自己的全域上下文資料。這個資料會被包含在專案輸出的所有例外 Log 訊息中：

    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    protected function context()
    {
        return array_merge(parent::context(), [
            'foo' => 'bar',
        ]);
    }
<a name="exception-log-context"></a>

#### Exception Log 的上下文

為所有 Log 訊息都新增額外的上下文可能會很實用，但有些特別的 Exception 可能會有一些獨特的上下文，而我們也想將這類上下文加到 Log 上。只要在我們的其中一個自訂 Exception 中定義一個 `context` 方法，就可以指定與該 Exception 相關的資料，將這些資料包含到例外的 Log 中：

    <?php
    
    namespace App\Exceptions;
    
    use Exception;
    
    class InvalidOrderException extends Exception
    {
        // ...
    
        /**
         * Get the exception's context information.
         *
         * @return array
         */
        public function context()
        {
            return ['order_id' => $this->orderId];
        }
    }
<a name="the-report-helper"></a>

#### `report` 輔助函式

有時候，我們可能會想回報某個 Exception，但又想繼續執行目前的 Request。使用 `report` 輔助函式，就能輕鬆地在不轉譯出錯誤頁面的情況下使用 Exception Handler 來回報這個 Exception：

    public function isValid($value)
    {
        try {
            // Validate the value...
        } catch (Throwable $e) {
            report($e);
    
            return false;
        }
    }
<a name="ignoring-exceptions-by-type"></a>

### 以類型忽略例外

在製作專案時，我們可能會想忽略一些類型的 Exception，讓這些 Exception 永遠不要被回報。在專案中的 Exception Handler 中包含了一個 `$dontReport` 屬性，該屬性被初始化為空陣列。只要將任何類別加到該屬性中，這些類別就不會被回報。不過，還是可以為這些類別定義自訂的轉譯邏輯：

    use App\Exceptions\InvalidOrderException;
    
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        InvalidOrderException::class,
    ];
> [!TIP]  
> 在 Laravel 內部，Laravel 已經預先幫你忽略了一些類型的錯誤。如：產生 404 HTTP「找不到」錯誤的 Exception、還有因為無效 CSRF Token 產生的 419 HTTP Response。

<a name="rendering-exceptions"></a>

### 轉譯 Exception

預設情況下，Laravel 的 Exception Handler 會幫你把 Exception 轉成 HTTP Response。不過，我們也可以自由地為某個類型的 Exception 註冊自訂^[轉譯閉包](Rendering Closure)。只要使用 Exception Handler 的 `renderable` 方法，就註冊轉譯閉包。

傳給 `renderable` 方法的閉包應回傳一個 `Illuminate\Http\Response` 的實體。可以使用 `response` 輔助函式來產生該實體。Laravel 會依照該閉包的型別提示來判斷這個閉包能轉移哪種類型的 Exception：

    use App\Exceptions\InvalidOrderException;
    
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (InvalidOrderException $e, $request) {
            return response()->view('errors.invalid-order', [], 500);
        });
    }
也可以使用 `renderable` 方法來複寫 Laravel 或 Symfony 內建 Exception 的轉移行外。如：`NotFoundHttpException`。若傳給 `renderable` 方法的閉包未回傳任何值，則會使用 Laravel 的預設 Exception 轉譯：

    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found.'
                ], 404);
            }
        });
    }
<a name="renderable-exceptions"></a>

### ^[可回報](Reportable)與^[可轉譯](Renderable)的 Exception

除了在 Exception Handler 的 `register` 方法上設定 Exception 的類型外，我們還可以直接在我們的自訂 Exception 上定義 `report` 與 `render` 方法。當這些方法存在時，Laravel 會自動呼叫這些方法：

    <?php
    
    namespace App\Exceptions;
    
    use Exception;
    
    class InvalidOrderException extends Exception
    {
        /**
         * Report the exception.
         *
         * @return bool|null
         */
        public function report()
        {
            //
        }
    
        /**
         * Render the exception into an HTTP response.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function render($request)
        {
            return response(...);
        }
    }
若你的 Exception 繼承的 Exception 已經是^[可轉譯的](Renderable)了 (如 Laravel 或 Symfony 內建的 Exception)，可在該 Exception 的 `render` 方法內回傳 `false` 來轉譯某個 Exception 的預設 HTTP Response：

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        // Determine if the exception needs custom rendering...
    
        return false;
    }
若你的 Exception 中包含了只有在特定情況下才會使用的自訂回報邏輯，則可讓 Laravel 在某些時候使用預設的 Exception 處理設定來回報這個 Exception。若要這麼做，請在該 Exception 的 `report` 方法內回傳 `false`：

    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        // Determine if the exception needs custom reporting...
    
        return false;
    }
> [!TIP]  
> 可以在 `report` 方法中型別提示任何的^[相依性](Dependency)。Laravel 的 [Service Container](/docs/{{version}}/container) 會自動插入這些相依性。

<a name="mapping-exceptions-by-type"></a>

### 依型別映射 Exception

專案中使用的第三方函式庫可能會擲回 Exception，而有時候我們會想讓這些 Exception 變成是[可被轉譯](#renderable-exceptions)的，但因為我們無法控制第三方的 Exception，因此無法做到。

幸好，在 Laravel 中，我們可以將這些 Exception 映射為其他由專案所管理的 Exception 型別。若要映射這些 Exception，可以在 Exception Handler 的 `register` 方法內呼叫 `map` 方法：

    use League\Flysystem\Exception;
    use App\Exceptions\FilesystemException;
    
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->map(Exception::class, FilesystemException::class);
    }
若想進一步控制目標 Exception，可以傳入一個閉包給 `map` 方法：

    use League\Flysystem\Exception;
    use App\Exceptions\FilesystemException;
    
    $this->map(fn (Exception $e) => new FilesystemException($e));
<a name="http-exceptions"></a>

## HTTP Exception

有的 Exception 是用來描述伺服器的 HTTP 錯誤代碼。例如，這些 Exception 可能是：「找不到頁面」錯誤 (404)、「未經授權」錯誤 (401)⋯⋯等，甚至是開發人員造成的 500 錯誤。在你的程式中的任何地點內，若要產生這種 Response，可使用 `abort` ^[輔助函式](Helper)：

    abort(404);
<a name="custom-http-error-pages"></a>

### 自訂 HTTP 錯誤頁面

在 Laravel 中，要給各種 HTTP 狀態碼顯示自訂錯誤頁非常容易。舉例來說，若要自訂 404 HTTP 狀態碼的錯誤頁面，請建立 `resources/views/errors/404.blade.php` View 樣板。程式中只要產生 404 錯誤，就會轉譯這個 View。在該目錄中的 View 應以對應的 HTTP 狀態碼來命名。由 `abort` 函式產生的 `Symfony\Component\HttpKernel\Exception\HttpException` 實體會以 `$exception` 變數傳給該 View：

    <h2>{{ $exception->getMessage() }}</h2>
可以使用 `vendor:publish` Artisan 指令來將 Laravel 的預設錯誤頁樣板^[安裝](Publish)到專案內。安裝好樣板後，就可以隨意自訂這些樣板：

    php artisan vendor:publish --tag=laravel-errors