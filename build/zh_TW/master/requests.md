---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/137/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
---

# HTTP Request

- [簡介](#introduction)
- [使用 Request](#interacting-with-the-request)
   - [存取 Request](#accessing-the-request)
   - [Request 路徑、主機、與方法](#request-path-and-method)
   - [Request 標頭](#request-headers)
   - [Request 的 IP 位址](#request-ip-address)
   - [判斷適當的內容](#content-negotiation)
   - [PSR-7 Request](#psr7-requests)
- [輸入](#input)
   - [取得輸入](#retrieving-input)
   - [判斷輸入是否存在](#determining-if-input-is-present)
   - [合併額外的輸入](#merging-additional-input)
   - [舊輸入](#old-input)
   - [Cookie](#cookies)
   - [修剪輸入與正常化 (Normalization)](#input-trimming-and-normalization)
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

### 存取 Request

若要通過相依性插入 (Dependency Injection) 來取得目前的 HTTP Request，可在 Route 閉包或 Controller 方法上型別提示 `Illuminate\Http\Request` 類別。連入的 Request 實體會自動被插入到 Laravel 的 [Service Container](/docs/{{version}}/container)：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    
    class UserController extends Controller
    {
        /**
         * Store a new user.
         */
        public function store(Request $request): Response
        {
            $name = $request->input('name');
    
            // ...
    
            return response()->noContent();
        }
    }

剛才也提到過，我們也可以在 Route 閉包上型別提示 `Illuminate\Http\Request` 類別。Service Container 會自動在閉包執行時將連入的 Request 插入進去：

    use Illuminate\Http\Request;
    
    Route::get('/', function (Request $request) {
        // ...
    });

<a name="dependency-injection-route-parameters"></a>

#### 相依性插入與 Route 參數

若 Controller 方法中還會從 Route 引數中收到輸入，請將 Route 引數列在其他相依性之後。舉例來說，若 Route 定義長這樣：

    use App\Http\Controllers\UserController;
    
    Route::put('/user/{id}', [UserController::class, 'update']);

則我們還是可以像這樣定義 Controller 方法來型別提示 `Illuminate\Http\Request` 並取得 `id` Route 參數：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    
    class UserController extends Controller
    {
        /**
         * Update the specified user.
         */
        public function update(Request $request, string $id): Response
        {
            // ...
    
            return response()->noContent();
        }
    }

<a name="request-path-and-method"></a>

### Request 路徑、主機、與方法

`Illuminate\Http\Request` 提供了多種可檢查連入 HTTP Request 的方法。這個方法也繼承了 `Symfony\Component\HttpFoundation\Request` 類別。我們稍後會討論其中幾個最重要的方法。

<a name="retrieving-the-request-path"></a>

#### 取得 Request 的路徑

`path` 方法會回傳 Request 的路徑資訊。因此，若連入 Request 是在瀏覽 `http://example.com/foo/bar`，則 `path` 方法會回傳 `foo/bar`：

    $uri = $request->path();

<a name="inspecting-the-request-path"></a>

#### 偵測 Request 路徑與 Route

可以使用 `is` 方法來驗證連入 Request 的路徑是否符合給定的格式。使用這個方法的時候，可以使用 `*` 字元作為萬用字元：

    if ($request->is('admin/*')) {
        // ...
    }

使用 `routeIs` 方法可以判斷連入的 Request 是否為某個[命名 Route](/docs/{{version}}/routing#named-routes)：

    if ($request->routeIs('admin.*')) {
        // ...
    }

<a name="retrieving-the-request-url"></a>

#### 取得 Request 的 URL

若要取得連入 Request 的完整 URL，可以使用 `url` 或 `fullUrl` 方法。`url` 方法會回傳不含查詢字串 (Query String) 的 URL，而 `fullUrl` 則包含查詢字串：

    $url = $request->url();
    
    $urlWithQueryString = $request->fullUrl();

若想將查詢字串資料附加到目前的 URL，可以使用 `fullUrlWithQuery` 方法。傳入一個包含查詢字串變數的陣列，然後這個方法會將給定的陣列與目前的查詢字串合併：

    $request->fullUrlWithQuery(['type' => 'phone']);

<a name="retrieving-the-request-host"></a>

#### 取得 Request 主機

可以使用 `host`、`httpHost`、與 `schemeAndHttpHost` 來取得連入 Request 的「主機」：

    $request->host();
    $request->httpHost();
    $request->schemeAndHttpHost();

<a name="retrieving-the-request-method"></a>

#### 取得 Request 的方法

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

> **Note** 若從 Route 或 Controller 中回傳 PSR-7 Response，這個 Response 會先被轉回到 Laravel 的 Response 實體，然後才會由 Laravel 顯示出來。

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

#### 取得輸入值

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

#### 取得查詢字串上的輸入

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

除了將輸入值以原生型別的 `string` 取得，還可以使用 `string` 方法來將 Request 資料以 [`Illuminate\Support\Stringable`](/docs/{{version}}/helpers#fluent-strings) 實體的形式取得：

    $name = $request->string('name')->trim();

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

<a name="retrieving-input-via-dynamic-properties"></a>

#### 通過動態屬性來取得輸入

可以在 `Illuminate\Http\Request` 實體上通過動態屬性來存取使用者輸入。舉例來說，若其中一個程式的表單包含了 `name` 欄位，則可以像這樣存取該欄位的值：

    $name = $request->name;

使用動態方法時，Laravel 會先在 Request 的 Payload (承載) 上尋找參數值。若 Payload 上沒有該值，Laravel 會接著在 Route 參數中尋找符合名稱的欄位：

<a name="retrieving-a-portion-of-the-input-data"></a>

#### 取得部分輸入資料

若只想取得一部分的輸入資料，可以使用 `only` 或 `except` 方法。這兩個方法都接受一個 `array` 值、或是一組引數的動態列表：

    $input = $request->only(['username', 'password']);
    
    $input = $request->only('username', 'password');
    
    $input = $request->except(['credit_card']);
    
    $input = $request->except('credit_card');

> **Warning** `only` 方法會回傳所要求的所有索引鍵 / 值配對組。不過，若要求的索引鍵 / 值配對未出現在 Request 中，將不會回傳。

<a name="determining-if-input-is-present"></a>

### 判斷輸入是否存在

可以使用 `has` 方法來判斷某個值是否存在 Request 中。若給定的輸入值存在於 Request 中，`has` 方法會回傳 `true`：

    if ($request->has('name')) {
        // ...
    }

傳入陣列時，`has` 方法判斷其中所有的值是否都存在：

    if ($request->has(['name', 'email'])) {
        // ...
    }

`whenHas` 方法會執行給定的閉包來判斷某個值是否存在於 Request 中：

    $request->whenHas('name', function (string $input) {
        // ...
    });

可以傳入第二個閉包給 `whenHas` 方法，當指定的值未存在於 Request 中，則會執行這個閉包：

    $request->whenHas('name', function (string $input) {
        // 有「name」值...
    }, function () {
        // 沒有「name」值...
    });

`hasAny` 方法會給定的值有其中一個存在時回傳 `true`：

    if ($request->hasAny(['name', 'email'])) {
        // ...
    }

若想判斷某個值是否有出現在 Request 中，且該值不是空字串時，可使用 `filled` 方法：

    if ($request->filled('name')) {
        // ...
    }

`whenFilled` 方法會執行給定的閉包來判斷 Request 中某個值是否為空字串：

    $request->whenFilled('name', function (string $input) {
        // ...
    });

可以傳入第二個閉包給 `whenFilled` 方法，當 Request 中指定的值為空時會執行這個閉包：

    $request->whenFilled('name', function (string $input) {
        // 已填寫「name」...
    }, function () {
        // 未填寫「name」...
    });

若要判斷 Request 中是否不存在給定的索引鍵，可使用 `missing` 方法與 `whenMissing` 方法：

    if ($request->missing('name')) {
        // ...
    }
    
    $request->whenMissing('name', function (array $input) {
        // 沒有「name」值...
    }, function () {
        // 有「name」值...
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

#### 將輸入資料快閃進 Session

使用 `Illuminate\Http\Request` 類別的 `flash` 方法，就可以將目前的輸入快閃 (Flash) 進 [Session](/docs/{{version}}/session)。這樣一來，使用者的下個 Request 中就有這些輸入值可用：

    $request->flash();

也可以使用 `flashOnly` 與 `flashExcept` 方法來只將一部分的 Request 資料刷入 Session。這些方法特別適用於想讓一些機密資料（如密碼）不要被刷入 Session 時：

    $request->flashOnly(['username', 'email']);
    
    $request->flashExcept('password');

<a name="flashing-input-then-redirecting"></a>

#### 快閃存入輸入後再重新導向

由於我們很常會需要再將輸入資料快閃存入 Session 後再重新導向回上一頁，因此我們只要把 `withInput` 方法串到重新導向後，就可以輕鬆地快閃存入輸入值：

    return redirect('form')->withInput();
    
    return redirect()->route('user.create')->withInput();
    
    return redirect('form')->withInput(
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

## 輸入修剪與正常化

預設情況下，Laravel 中包含了 `App\Http\Middleware\TrimStrings` 與 `Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull` 這兩個 Middleware，且放在程式的全域 Middleware Stack 中。這些 Middleware 被列在 `App\Http\Kernel` 類別的全域 Middleware Stack 中。這些 Middleware 會自動修剪 Request 中的所有連入子船，並將空白的字串欄位轉為 `null`。這樣，我們就不需要在 Route 或 Controller 中去費心正常化這些資料。

#### 禁用輸入正規化

若想在所有 Request 上禁用這些行為，可在 `App\Http\Kernel` 類別的 `$middleware` 屬性中將其移除：

若只想在專案中一部分的 Request 上禁用字串修剪與空字串轉換，可使用這兩個 Middleware 提供的 `skipWhen` 方法。請傳入一個回傳 `true` 或 `false` 的閉包給該方法，用來判斷是否應跳過字串的正規化。一般來說，應在專案的 `AppServiceProvider` 中 `boot` 方法內叫用這個 `skipWhen` 方法。

```php
use App\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    TrimStrings::skipWhen(function (Request $request) {
        return $request->is('admin/*');
    });

    ConvertEmptyStringsToNull::skipWhen(function (Request $request) {
        // ...
    });
}
```

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

#### 檔案路徑與副檔名

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

> **Note** 更多有關 Laravel 中檔案儲存的資訊，請參考完整的[檔案儲存說明文件](/docs/{{version}}/filesystem)。

<a name="configuring-trusted-proxies"></a>

## 設定信任的代理 (Trusted Proxy)

在負責處理 TLS / SSL 證書的 Load Balancer (負載平衡器) 後方執行應用程式時，有時候由 `url` 輔助函式產生的連結可能不會使用 HTTPS。者通常是因為，Load Balancer 把流量傳過來時使用的是 80 Port，因為 Laravel 不知道是否要產生 HTTPS 的連結。

為此，我們可以使用 Laravel 專案中有包含的 `App\Http\Middleware\TrustProxies` Middleware。該 Middleware 能讓我們快速自訂程式要信任的 Load Balancer 或代理伺服器 (Proxy)。應在該 Middleware 內的 `$proxies` 屬性內列出信任的代理伺服器。除了設定信任的代理外，也可以設定信任代理的 `$headers`：

    <?php
    
    namespace App\Http\Middleware;
    
    use Illuminate\Http\Middleware\TrustProxies as Middleware;
    use Illuminate\Http\Request;
    
    class TrustProxies extends Middleware
    {
        /**
         * The trusted proxies for this application.
         *
         * @var string|array
         */
        protected $proxies = [
            '192.168.1.1',
            '192.168.1.2',
        ];
    
        /**
         * The headers that should be used to detect proxies.
         *
         * @var int
         */
        protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO;
    }

> **Note** 若要使用 AWS Elastic Load Balancing，則 `$headers` 的值應為 `Request::HEADER_X_FORWARDED_AWS_ELB`。更多有關能用在 `$headers` 屬性的常數資訊，請參考 Symfony 說明文件中的 [Trusting Proxies](https://symfony.com/doc/current/deployment/proxies.html)。

<a name="trusting-all-proxies"></a>

#### 信任所有代理

若使用 Amazon AWS 或其他的「雲端」Load Balancer 提供者，則我們可能不知道 Load Balancer 實際的 IP 位置。這時，可以使用 `*` 來信任所有代理：

    /**
     * The trusted proxies for this application.
     *
     * @var string|array
     */
    protected $proxies = '*';

<a name="configuring-trusted-hosts"></a>

## 設定信任的主機 (Trusted Hosts)

預設情況下，無論收到的 HTTP Request 中 `Host` 標頭內容為何，Laravel 都會回應所有收到的 Request。此外，Laravel 還會使用 `Host` 標頭的值來在 Request 中為你的程式產生絕對路徑的網址。

一般來說，應在 Web Server 上 (如 Nginx 或 Apache) 設定只有特定的主機名稱時才將 Request 送往你的程式中。不過，若沒機會能自訂 Web Server，則需要讓 Laravel 只對特定主機名稱作回應。為此，可以啟用專案中的 `App\Http\Middleware\TrustHosts` Middleware。

`TrustHosts` Middleware 以預先包含在專案中的 `$middlware` Stack 裡的。不過，需要先取消註解這個 Middleware，才能啟用它。在這個 Middleware 中有個 `hosts` 方法，我們可以在其中指定我們的程式要回應的主機名稱。有其他 `Host` 標頭值的連入 Request 將會被拒絕：

    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string>
     */
    public function hosts(): array
    {
        return [
            'laravel.test',
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }

`allSubdomainsOfApplicationUrl` 輔助函式會回傳一個可配對應用程式中 `app.url` 設定值子網域的正規表示式。使用這個輔助函式，就可以方便地在使用萬用子網域的程式中允許所有的子網域。
