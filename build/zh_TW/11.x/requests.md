---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/137/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 45.79
---

# HTTP Request

- [簡介](#introduction)
- [使用 Request](#interacting-with-the-request)
  - [Accessing the Request](#accessing-the-request)
  - [Request Path, Host, and Method](#request-path-and-method)
  - [Request 標頭](#request-headers)
  - [Request 的 IP 位址](#request-ip-address)
  - [判斷適當的內容](#content-negotiation)
  - [PSR-7 Request](#psr7-requests)
  
- [輸入](#input)
  - [取得輸入](#retrieving-input)
  - [Input Presence](#input-presence)
  - [合併額外的輸入](#merging-additional-input)
  - [舊輸入](#old-input)
  - [Cookie](#cookies)
  - [Input Trimming and Normalization](#input-trimming-and-normalization)
  
- [檔案](#files)
  - [取得上傳的檔案](#retrieving-uploaded-files)
  - [保存上傳的檔案](#storing-uploaded-files)
  
- [設定 Trusted Proxies](#configuring-trusted-proxies)
- [設定 Trusted Hosts](#configuring-trusted-hosts)

<a name="introduction"></a>

## 簡介

Laravel 的 `Illuminate\Http\Request` 類別提供了一種物件導向的方法來讓你存取目前程式在處理的 HTTP Request，包含 Request 的輸入、Cookie、上傳的檔案⋯⋯等。

<a name="interacting-with-the-request"></a>

## 使用 Request

<a name="accessing-the-request"></a>

### Accessing the Request

若要通過相依性插入 (Dependency Injection) 來取得目前的 HTTP Request，可在 Route 閉包或 Controller 方法上型別提示 `Illuminate\Http\Request` 類別。連入的 Request 實體會自動被插入到 Laravel 的 [Service Container](/docs/{{version}}/container)：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    
    class UserController extends Controller
    {
        /**
         * Store a new user.
         */
        public function store(Request $request): RedirectResponse
        {
            $name = $request->input('name');
    
            // Store the user...
    
            return redirect('/users');
        }
    }
剛才也提到過，我們也可以在 Route 閉包上型別提示 `Illuminate\Http\Request` 類別。Service Container 會自動在閉包執行時將連入的 Request 插入進去：

    use Illuminate\Http\Request;
    
    Route::get('/', function (Request $request) {
        // ...
    });
<a name="dependency-injection-route-parameters"></a>

#### Dependency Injection and Route Parameters

若 Controller 方法中還會從 Route 引數中收到輸入，請將 Route 引數列在其他相依性之後。舉例來說，若 Route 定義長這樣：

    use App\Http\Controllers\UserController;
    
    Route::put('/user/{id}', [UserController::class, 'update']);
則我們還是可以像這樣定義 Controller 方法來型別提示 `Illuminate\Http\Request` 並取得 `id` Route 參數：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    
    class UserController extends Controller
    {
        /**
         * Update the specified user.
         */
        public function update(Request $request, string $id): RedirectResponse
        {
            // Update the user...
    
            return redirect('/users');
        }
    }
<a name="request-path-and-method"></a>

### Request Path, Host, and Method

`Illuminate\Http\Request` 提供了多種可檢查連入 HTTP Request 的方法。這個方法也繼承了 `Symfony\Component\HttpFoundation\Request` 類別。我們稍後會討論其中幾個最重要的方法。

<a name="retrieving-the-request-path"></a>

#### Retrieving the Request Path

`path` 方法會回傳 Request 的路徑資訊。因此，若連入 Request 是在瀏覽 `http://example.com/foo/bar`，則 `path` 方法會回傳 `foo/bar`：

    $uri = $request->path();
<a name="inspecting-the-request-path"></a>

#### Inspecting the Request Path / Route

可以使用 `is` 方法來驗證連入 Request 的路徑是否符合給定的格式。使用這個方法的時候，可以使用 `*` 字元作為萬用字元：

    if ($request->is('admin/*')) {
        // ...
    }
使用 `routeIs` 方法可以判斷連入的 Request 是否為某個[命名 Route](/docs/{{version}}/routing#named-routes)：

    if ($request->routeIs('admin.*')) {
        // ...
    }
<a name="retrieving-the-request-url"></a>

#### Retrieving the Request URL

若要取得連入 Request 的完整 URL，可以使用 `url` 或 `fullUrl` 方法。`url` 方法會回傳不含查詢字串 (Query String) 的 URL，而 `fullUrl` 則包含查詢字串：

    $url = $request->url();
    
    $urlWithQueryString = $request->fullUrl();
若想將查詢字串資料附加到目前的 URL，可以使用 `fullUrlWithQuery` 方法。傳入一個包含查詢字串變數的陣列，然後這個方法會將給定的陣列與目前的查詢字串合併：

    $request->fullUrlWithQuery(['type' => 'phone']);
若想取得不含給定 Query String 參數的目前 URL，可使用 `fullUrlWithoutQuery` 方法：

```php
$request->fullUrlWithoutQuery(['type']);
```
<a name="retrieving-the-request-host"></a>

#### Retrieving the Request Host

可以使用 `host`、`httpHost`、與 `schemeAndHttpHost` 來取得連入 Request 的「主機」：

    $request->host();
    $request->httpHost();
    $request->schemeAndHttpHost();
<a name="retrieving-the-request-method"></a>

#### Retrieving the Request Method

`method` 方法會回傳該 Request 的 HTTP 動詞 (Verb)。可以使用 `isMethod` 方法來判斷目前的 HTTP 動詞是否符合給定字串：

    $method = $request->method();
    
    if ($request->isMethod('post')) {
        // ...
    }
<a name="request-headers"></a>

### Request 標頭

可以使用 `header` 方法來從 `Illuminate\Http\Request` 內取得 Request 的標頭 (Header)。若該 Request 未包含指定的標頭，則會回傳 `null`。不過，`header` 方法也接受第三個可選的引數，會在標頭不存在時回傳該值：

    $value = $request->header('X-Header-Name');
    
    $value = $request->header('X-Header-Name', 'default');
`hasHeader` 方法可用來判斷 Request 是否包含給定的標頭：

    if ($request->hasHeader('X-Header-Name')) {
        // ...
    }
為了方便起見，可以使用 `bearerToken` 方法來從 `Authorization` 標頭中取得 Bearer Token。若該標頭不存在，會回傳空字串：

    $token = $request->bearerToken();
<a name="request-ip-address"></a>

### Request 的 IP 位址

可以使用 `ip` 方法來取得用戶端發起 Request 使用的 IP 位址：

    $ipAddress = $request->ip();
If you would like to retrieve an array of IP addresses, including all of the client IP addresses that were forwarded by proxies, you may use the `ips` method. The "original" client IP address will be at the end of the array:

    $ipAddresses = $request->ips();
In general, IP addresses should be considered untrusted, user-controlled input and be used for informational purposes only.

<a name="content-negotiation"></a>

### 判斷適當的內容

Laravel 提供了數種方法來通過 `Accept` 標頭判斷連入 Request 所要求的 Content Type (內容類型)。首先，`getAcceptableContentTypes` 會回傳一個陣列，其中包含該 Request 所接受的所有 Content Type：

    $contentTypes = $request->getAcceptableContentTypes();
`accepts` 方法接受一個包含 Content Type 的陣列，當陣列中有任何一個 Content Type 是 Request 接受的，就會回傳 `true`。否則，會回傳 `false`：

    if ($request->accepts(['text/html', 'application/json'])) {
        // ...
    }
可以使用 `prefers` 方法來判斷給定陣列中的哪個 Content Type 是該 Request 最優先選擇的。若所提供的 Content Type 都不為 Request 接受，則會回傳 `null`：

    $preferred = $request->prefers(['text/html', 'application/json']);
因為大部分專案都只提供 HTML 或 JSON，所以我們可以通過 `expectsJson` 方法來快速判斷連入的 Request 是否預期 Response 應為 JSON：

    if ($request->expectsJson()) {
        // ...
    }
<a name="psr7-requests"></a>

### PSR-7 Request

[PSR-7 標準](https://www.php-fig.org/psr/psr-7/) 指定了用於 HTTP 訊息通訊的介面，其中包含 Request 與 Response。若你想取得 PSR-7 Request 的實體而不是 Laravel Request，首先你會需要安裝幾個函式庫。Laravel 使用 *Symfony 的 HTTP Message Bridge* 元件來將一般的 Laravel Request 與 Response 轉換為相容於 PSR-7 的實作：

```shell
composer require symfony/psr-http-message-bridge
composer require nyholm/psr7
```
安裝好這些函式庫後，就可以在 Route 閉包或 Controller 方法上型別提示 PSR-7 Request 介面來取得 PSR-7 Request 的實體：

    use Psr\Http\Message\ServerRequestInterface;
    
    Route::get('/', function (ServerRequestInterface $request) {
        // ...
    });
> [!NOTE]  
> 若從 Route 或 Controller 中回傳 PSR-7 Response，這個 Response 會先被轉回到 Laravel 的 Response 實體，然後才會由 Laravel 顯示出來。

<a name="input"></a>

## 輸入

<a name="retrieving-input"></a>

### 取得輸入

<a name="retrieving-all-input-data"></a>

#### 取得所有輸入的資料

可以使用 `all` 方法來將所有連入 Request 的輸入資料取得為 `array`。無論連入的 Request 是來自 HTML 表單還是 XHR Request，都可以使用這個方法：

    $input = $request->all();
使用 `collect` 方法就可以將連入 Request 的輸入資料作為 [Collection](/docs/{{version}}/collections) 取得：

    $input = $request->collect();
使用 `collect` 方法也可以用來將連入 Request 輸入中的一部分取得為 Collection：

    $request->collect('users')->each(function (string $user) {
        // ...
    });
<a name="retrieving-an-input-value"></a>

#### Retrieving an Input Value

使用幾個簡單的方法，不需要擔心 Request 使用了哪個 HTTP 動詞，都可以存取 `Illuminate\Http\Request` 實體中所有的使用者輸入。無論 HTTP 動詞是什麼，都可以用 `input` 方法來取得使用者輸入：

    $name = $request->input('name');
也可以傳入第二個引數給 `input` 方法來取得預設值。若 Request 中沒有要求的輸入值時，就會回傳這個預設值：

    $name = $request->input('name', 'Sally');
在處理包含陣列輸入的表單時，可以使用「點 (.)」標記法來存取陣列：

    $name = $request->input('products.0.name');
    
    $names = $request->input('products.*.name');
呼叫 `input` 方法時若不傳入任何引數，則可以用關聯式陣列的方式取得所有輸入資料：

    $input = $request->input();
<a name="retrieving-input-from-the-query-string"></a>

#### Retrieving Input From the Query String

雖然 `input` 方法可以從所有的 Request 承載 (Payload) 上取得資料 (其中也包含查詢字串)，若使用 `query` 方法，則可以只從查詢字串中取得資料：

    $name = $request->query('name');
若要求的查詢字串值不存在，則會回傳第二個傳入該方法的值：

    $name = $request->query('name', 'Helen');
呼叫 `query` 方法時若不傳入任何引數，則可以用關聯式陣列的方式取得所有查詢字串的資料：

    $query = $request->query();
<a name="retrieving-json-input-values"></a>

#### 取得 JSON 輸入值

傳送 JSON 的 Request 時，只要 Request 的 `Content-Type` 由正確設定為 `application/json`，就可以使用 `input` 方法來存取 JSON 資料。也可以使用「點 (.)」標記法來存取 JSON 陣列／物件中的巢狀資料：

    $name = $request->input('user.name');
<a name="retrieving-stringable-input-values"></a>

#### 取得 Stringable 的輸入值

Instead of retrieving the request's input data as a primitive `string`, you may use the `string` method to retrieve the request data as an instance of [`Illuminate\Support\Stringable`](/docs/{{version}}/strings):

    $name = $request->string('name')->trim();
<a name="retrieving-integer-input-values"></a>

#### Retrieving Integer Input Values

To retrieve input values as integers, you may use the `integer` method. This method will attempt to cast the input value to an integer. If the input is not present or the cast fails, it will return the default value you specify. This is particularly useful for pagination or other numeric inputs:

    $perPage = $request->integer('per_page');
<a name="retrieving-boolean-input-values"></a>

#### 取得布林輸入值

在處理如勾選框 (Checkbox) 等 HTML 元素時，我們的程式可能會收到以字串形式呈現的「真假」值。舉例來說，這個值可能是「true」或「on」。為了方便起見，我們可以使用 `boolean` 方法來將這些值以布林方式取得。值為 1、"1"、true、"true"、"on"、"yes" 時，`boolean` 方法回傳 `true`。其他任何的值則會回傳 `false`：

    $archived = $request->boolean('archived');
<a name="retrieving-date-input-values"></a>

#### 取得日期的輸入值

為了方便起見，我們可以使用 `date` 方法來將包含日期 / 時間的輸入值以 Carbon 實體來存取。若 Request 中為包含給定名稱的輸入值，則會回傳 `null`：

    $birthday = $request->date('birthday');
可以使用 `date` 的第二與第三個引數來分別指定日期的格式與時區：

    $elapsed = $request->date('elapsed', '!H:i', 'Europe/Madrid');
若輸入中有值，但格式不正確時，會擲回 `InvalidArgumentException`。因此，建議你在叫用 `date` 方法前先驗證輸入。

<a name="retrieving-enum-input-values"></a>

#### 取得 Enum 輸入值

也可以從 Request 中取得對應到 [PHP Enum](https://www.php.net/manual/en/language.types.enumerations.php) 的輸入值。若 Request 中沒有輸入值，或是給定的名稱或 Enum 中沒有符合該輸入值的後端值 (Backing Value)，則會回傳 `null`。`enum` 方法的第一個引數為輸入值的名稱、第二個引數為 Enum 類別：

    use App\Enums\Status;
    
    $status = $request->enum('status', Status::class);
If the input value is an array of values that correspond to a PHP enum, you may use the `enums` method to retrieve the array of values as enum instances:

    use App\Enums\Product;
    
    $products = $request->enums('products', Product::class);
<a name="retrieving-input-via-dynamic-properties"></a>

#### Retrieving Input via Dynamic Properties

可以在 `Illuminate\Http\Request` 實體上通過動態屬性來存取使用者輸入。舉例來說，若其中一個程式的表單包含了 `name` 欄位，則可以像這樣存取該欄位的值：

    $name = $request->name;
使用動態方法時，Laravel 會先在 Request 的 Payload (承載) 上尋找參數值。若 Payload 上沒有該值，Laravel 會接著在 Route 參數中尋找符合名稱的欄位：

<a name="retrieving-a-portion-of-the-input-data"></a>

#### Retrieving a Portion of the Input Data

若只想取得一部分的輸入資料，可以使用 `only` 或 `except` 方法。這兩個方法都接受一個 `array` 值、或是一組引數的動態列表：

    $input = $request->only(['username', 'password']);
    
    $input = $request->only('username', 'password');
    
    $input = $request->except(['credit_card']);
    
    $input = $request->except('credit_card');
> [!WARNING]  
> `only` 方法會回傳所要求的所有索引鍵 / 值配對組。不過，若要求的索引鍵 / 值配對未出現在 Request 中，將不會回傳。

<a name="input-presence"></a>

### Input Presence

可以使用 `has` 方法來判斷某個值是否存在 Request 中。若給定的輸入值存在於 Request 中，`has` 方法會回傳 `true`：

    if ($request->has('name')) {
        // ...
    }
傳入陣列時，`has` 方法判斷其中所有的值是否都存在：

    if ($request->has(['name', 'email'])) {
        // ...
    }
`hasAny` 方法會給定的值有其中一個存在時回傳 `true`：

    if ($request->hasAny(['name', 'email'])) {
        // ...
    }
`whenHas` 方法會執行給定的閉包來判斷某個值是否存在於 Request 中：

    $request->whenHas('name', function (string $input) {
        // ...
    });
可以傳入第二個閉包給 `whenHas` 方法，當指定的值未存在於 Request 中，則會執行這個閉包：

    $request->whenHas('name', function (string $input) {
        // The "name" value is present...
    }, function () {
        // The "name" value is not present...
    });
若想判斷某個值是否有出現在 Request 中，且該值不是空字串時，可使用 `filled` 方法：

    if ($request->filled('name')) {
        // ...
    }
If you would like to determine if a value is missing from the request or is an empty string, you may use the `isNotFilled` method:

    if ($request->isNotFilled('name')) {
        // ...
    }
When given an array, the `isNotFilled` method will determine if all of the specified values are missing or empty:

    if ($request->isNotFilled(['name', 'email'])) {
        // ...
    }
`anyFilled` 方法會在給定的值中有其中一個值不為空字串時回傳 `true`：

    if ($request->anyFilled(['name', 'email'])) {
        // ...
    }
`whenFilled` 方法會執行給定的閉包來判斷 Request 中某個值是否為空字串：

    $request->whenFilled('name', function (string $input) {
        // ...
    });
可以傳入第二個閉包給 `whenFilled` 方法，當 Request 中指定的值為空時會執行這個閉包：

    $request->whenFilled('name', function (string $input) {
        // The "name" value is filled...
    }, function () {
        // The "name" value is not filled...
    });
若要判斷 Request 中是否不存在給定的索引鍵，可使用 `missing` 方法與 `whenMissing` 方法：

    if ($request->missing('name')) {
        // ...
    }
    
    $request->whenMissing('name', function () {
        // The "name" value is missing...
    }, function () {
        // The "name" value is present...
    });
<a name="merging-additional-input"></a>

### 合併額外的輸入

有時候，我們需要手動合併額外的輸入到 Request 中現有的輸入資料。這種情況下，可使用 `merge` 方法。若 Request 中已存在給定的輸入索引鍵，則會使用提供給 `merge` 方法的資料來複寫：

    $request->merge(['votes' => 0]);
使用 `mergeIfMissing` 方法就可以只在 Request 的輸入資料中缺少特定索引鍵時才合併進 Request：

    $request->mergeIfMissing(['votes' => 0]);
<a name="old-input"></a>

### 舊輸入

Laravel 提供了將輸入資料從一個 Request 帶到下一個 Request 的功能。這個功能特別適合用在表單驗證失敗後要重新填充表單時。不過，若你使用 Laravel 提供的[表單驗證功能](/docs/{{version}}/validation)，那麼你應該不需要直接手動進行這些 Session 的 Input 快閃方法，因為 Laravel 的內建表單驗證功能已經自動處理好了。

<a name="flashing-input-to-the-session"></a>

#### Flashing Input to the Session

使用 `Illuminate\Http\Request` 類別的 `flash` 方法，就可以將目前的輸入快閃 (Flash) 進 [Session](/docs/{{version}}/session)。這樣一來，使用者的下個 Request 中就有這些輸入值可用：

    $request->flash();
也可以使用 `flashOnly` 與 `flashExcept` 方法來只將一部分的 Request 資料刷入 Session。這些方法特別適用於想讓一些機密資料（如密碼）不要被刷入 Session 時：

    $request->flashOnly(['username', 'email']);
    
    $request->flashExcept('password');
<a name="flashing-input-then-redirecting"></a>

#### 快閃存入輸入後再重新導向

由於我們很常會需要再將輸入資料快閃存入 Session 後再重新導向回上一頁，因此我們只要把 `withInput` 方法串到重新導向後，就可以輕鬆地快閃存入輸入值：

    return redirect('/form')->withInput();
    
    return redirect()->route('user.create')->withInput();
    
    return redirect('/form')->withInput(
        $request->except('password')
    );
<a name="retrieving-old-input"></a>

#### 取得舊輸入

若要取得前一個 Request 中的快閃輸入，可叫用 `Illuminate\Http\Request` 上的 `old` 方法。`old` 方法從 [Session](/docs/{{version}}/session) 中拉取前次快閃存入輸入資料：

    $username = $request->old('username');
Laravel 也提供了一個全域 `old` 輔助函式。若想在 [Blade 樣板](/docs/{{version}}/blade)中顯示舊輸入，那麼使用 `old` 輔助函式來將其填回表單回比較方便。若給定欄位沒有舊輸入的話，會回傳 `null`：

    <input type="text" name="username" value="{{ old('username') }}">
<a name="cookies"></a>

### Cookie

<a name="retrieving-cookies-from-requests"></a>

#### 從 Request 中取得 Cookie

所有由 Laravel 框架所建立的 Cookie 都是經過加密且使用驗證碼簽名過的，這代表若用戶端有修改這些值，就會讓 Cookie 變成無效。若要從 Request 中取得 Cookie，請使用 `Illuminate\Http\Request` 實體上的 `cookie` 方法：

    $value = $request->cookie('name');
<a name="input-trimming-and-normalization"></a>

## Input Trimming and Normalization

By default, Laravel includes the `Illuminate\Foundation\Http\Middleware\TrimStrings` and `Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull` middleware in your application's global middleware stack. These middleware will automatically trim all incoming string fields on the request, as well as convert any empty string fields to `null`. This allows you to not have to worry about these normalization concerns in your routes and controllers.

#### 禁用輸入正規化

If you would like to disable this behavior for all requests, you may remove the two middleware from your application's middleware stack by invoking the `$middleware->remove` method in your application's `bootstrap/app.php` file:

    use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
    use Illuminate\Foundation\Http\Middleware\TrimStrings;
    
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->remove([
            ConvertEmptyStringsToNull::class,
            TrimStrings::class,
        ]);
    })
If you would like to disable string trimming and empty string conversion for a subset of requests to your application, you may use the `trimStrings` and `convertEmptyStringsToNull` middleware methods within your application's `bootstrap/app.php` file. Both methods accept an array of closures, which should return `true` or `false` to indicate whether input normalization should be skipped:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->convertEmptyStringsToNull(except: [
            fn (Request $request) => $request->is('admin/*'),
        ]);
    
        $middleware->trimStrings(except: [
            fn (Request $request) => $request->is('admin/*'),
        ]);
    })
<a name="files"></a>

## 檔案

<a name="retrieving-uploaded-files"></a>

### 取得上傳的檔案

可以使用 `file` 方法或動態屬性來從 `Illuminate\Http\Request` 實體上取得上傳的檔案。`file` 方法會回傳 `Illuminate\Http\UploadedFile` 類別的實體，該實體繼承了 PHP 的 `SplFileInfo` 類別，並提供各種能處理使用該檔案的方法：

    $file = $request->file('photo');
    
    $file = $request->photo;
可以使用 `hasFile` 方法來判斷某個檔案是否存在於 Request 中：

    if ($request->hasFile('photo')) {
        // ...
    }
<a name="validating-successful-uploads"></a>

#### 驗證成功上傳

除了檢查檔案是否存在外，還可以使用 `isValid` 方法來確認上傳檔案的過程中是否無問題：

    if ($request->file('photo')->isValid()) {
        // ...
    }
<a name="file-paths-extensions"></a>

#### File Paths and Extensions

`UploadedFile` 類別也包含了能存取檔案完整路徑與副檔名的方法。`extension` 方法可以使用檔案的內容來推測檔案的副檔名。這個副檔名克呢功能會與用戶端提供的副檔名有所不同：

    $path = $request->photo->path();
    
    $extension = $request->photo->extension();
<a name="other-file-methods"></a>

#### 其他檔案方法

`UploadedFile` 實體還提供了其他各種方法。請參考[該類別的 API 說明文件](https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpFoundation/File/UploadedFile.php)來瞭解有關這些方法的更多資訊。

<a name="storing-uploaded-files"></a>

### 儲存上傳的檔案

若要儲存已上傳的檔案，通常我們需要先設定好[檔案系統](/docs/{{version}}/filesystem)。`UploadedFile` 類別中有個 `store` 方法，該方法可以將已上傳的檔案移到其中一個磁碟裡。這個磁碟可以是本機檔案系統，也可以是像 Amazon S3 之類的雲端儲存空間。

`store` 方法接受一個路徑，該路徑就是相對於檔案系統設定中根目錄的位置。路徑不包含檔案名稱，Laravel 會自動產生獨立的 ID 來當作檔案名稱。

`store` 方法也接受可選的第二個引數，該引數是要用來儲存檔案的磁碟名稱。 `store` 方法會回傳相對於磁碟根目錄的檔案路徑：

    $path = $request->photo->store('images');
    
    $path = $request->photo->store('images', 's3');
若不想要自動產生的檔案名稱，可以使用 `storeAs` 方法，該方法的引數是路徑、檔案名稱、磁碟名稱：

    $path = $request->photo->storeAs('images', 'filename.jpg');
    
    $path = $request->photo->storeAs('images', 'filename.jpg', 's3');
> [!NOTE]  
> 更多有關 Laravel 中檔案儲存的資訊，請參考完整的[檔案儲存說明文件](/docs/{{version}}/filesystem)。

<a name="configuring-trusted-proxies"></a>

## 設定信任的代理 (Trusted Proxy)

在負責處理 TLS / SSL 證書的 Load Balancer (負載平衡器) 後方執行應用程式時，有時候由 `url` 輔助函式產生的連結可能不會使用 HTTPS。者通常是因為，Load Balancer 把流量傳過來時使用的是 80 Port，因為 Laravel 不知道是否要產生 HTTPS 的連結。

To solve this, you may enable the `Illuminate\Http\Middleware\TrustProxies` middleware that is included in your Laravel application, which allows you to quickly customize the load balancers or proxies that should be trusted by your application. Your trusted proxies should be specified using the `trustProxies` middleware method in your application's `bootstrap/app.php` file:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: [
            '192.168.1.1',
            '10.0.0.0/8',
        ]);
    })
In addition to configuring the trusted proxies, you may also configure the proxy headers that should be trusted:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );
    })
> [!NOTE]  
> If you are using AWS Elastic Load Balancing, the `headers` value should be `Request::HEADER_X_FORWARDED_AWS_ELB`. If your load balancer uses the standard `Forwarded` header from [RFC 7239](https://www.rfc-editor.org/rfc/rfc7239#section-4), the `headers` value should be `Request::HEADER_FORWARDED`. For more information on the constants that may be used in the `headers` value, check out Symfony's documentation on [trusting proxies](https://symfony.com/doc/7.0/deployment/proxies.html).

<a name="trusting-all-proxies"></a>

#### 信任所有代理

若使用 Amazon AWS 或其他的「雲端」Load Balancer 提供者，則我們可能不知道 Load Balancer 實際的 IP 位置。這時，可以使用 `*` 來信任所有代理：

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
<a name="configuring-trusted-hosts"></a>

## 設定信任的主機 (Trusted Hosts)

預設情況下，無論收到的 HTTP Request 中 `Host` 標頭內容為何，Laravel 都會回應所有收到的 Request。此外，Laravel 還會使用 `Host` 標頭的值來在 Request 中為你的程式產生絕對路徑的網址。

Typically, you should configure your web server, such as Nginx or Apache, to only send requests to your application that match a given hostname. However, if you do not have the ability to customize your web server directly and need to instruct Laravel to only respond to certain hostnames, you may do so by enabling the `Illuminate\Http\Middleware\TrustHosts` middleware for your application.

To enable the `TrustHosts` middleware, you should invoke the `trustHosts` middleware method in your application's `bootstrap/app.php` file. Using the `at` argument of this method, you may specify the hostnames that your application should respond to. Incoming requests with other `Host` headers will be rejected:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustHosts(at: ['laravel.test']);
    })
By default, requests coming from subdomains of the application's URL are also automatically trusted. If you would like to disable this behavior, you may use the `subdomains` argument:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustHosts(at: ['laravel.test'], subdomains: false);
    })
If you need to access your application's configuration files or database to determine your trusted hosts, you may provide a closure to the `at` argument:

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustHosts(at: fn () => config('app.trusted_hosts'));
    })