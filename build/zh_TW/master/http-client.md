---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/85/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 47.95
---

# HTTP 用戶端

- [簡介](#introduction)
- [建立 Request](#making-requests)
  - [Request 資料](#request-data)
  - [標頭 (Header)](#headers)
  - [身份認證](#authentication)
  - [逾時](#timeout)
  - [重試](#retries)
  - [錯誤處理](#error-handling)
  - [Guzzle Middleware](#guzzle-middleware)
  - [Guzzle 選項](#guzzle-options)
  
- [同時進行的 Request](#concurrent-requests)
- [Macro](#macros)
- [測試](#testing)
  - [模擬 Response](#faking-responses)
  - [攔截 Request](#inspecting-requests)
  - [避免漏掉的 Request](#preventing-stray-requests)
  
- [事件](#events)

<a name="introduction"></a>

## 簡介

Laravel 為 [Guzzle HTTP 用戶端](http://docs.guzzlephp.org/en/stable/)提供了一個語意化的極簡 API，能讓我們快速建立外連 HTTP Request 來與其他 Web App 通訊。Laravel 的 Guzzle 包裝著重於各個常見的使用情境，並提供優秀的^[開發人員經驗](Developer Experience)。

<a name="making-requests"></a>

## 建立 Request

若要建立 Request，可以使用 `Http` Facade 提供的 `head`、`get`、`post`、`put`、`patch`、`delete` 等方法。首先，我們先看看要查詢另一個 URL 的基礎 `GET` Request 怎麼建立：

    use Illuminate\Support\Facades\Http;
    
    $response = Http::get('http://example.com');
`get` 方法會回傳 `Illuminate\Http\Client\Response` 的實體，該實體提供了許多用來取得 Response 資訊的方法：

    $response->body() : string;
    $response->json($key = null, $default = null) : array|mixed;
    $response->object() : object;
    $response->collect($key = null) : Illuminate\Support\Collection;
    $response->status() : int;
    $response->successful() : bool;
    $response->redirect(): bool;
    $response->failed() : bool;
    $response->clientError() : bool;
    $response->header($header) : string;
    $response->headers() : array;
`Illuminate\Http\Client\Response` 物件也實作了 PHP 的 `ArrayAccess` 實體，能讓我們直接在 Response 上存取 JSON Response 資料：

    return Http::get('http://example.com/users/1')['name'];
除了上述所列的 Response 方法外，也可以使用下列方法來判斷 Response 是否有給定的狀態碼：

    $response->ok() : bool;                  // 200 OK
    $response->created() : bool;             // 201 Created
    $response->accepted() : bool;            // 202 Accepted
    $response->noContent() : bool;           // 204 No Content
    $response->movedPermanently() : bool;    // 301 Moved Permanently
    $response->found() : bool;               // 302 Found
    $response->badRequest() : bool;          // 400 Bad Request
    $response->unauthorized() : bool;        // 401 Unauthorized
    $response->paymentRequired() : bool;     // 402 Payment Required
    $response->forbidden() : bool;           // 403 Forbidden
    $response->notFound() : bool;            // 404 Not Found
    $response->requestTimeout() : bool;      // 408 Request Timeout
    $response->conflict() : bool;            // 409 Conflict
    $response->unprocessableEntity() : bool; // 422 Unprocessable Entity
    $response->tooManyRequests() : bool;     // 429 Too Many Requests
    $response->serverError() : bool;         // 500 Internal Server Error
<a name="uri-templates"></a>

#### URI 樣板

在 HTTP Client 中，也可使用 [URI 樣板規格 (URI Template Specification)](https://www.rfc-editor.org/rfc/rfc6570)來建立 Request URL。若要定義可由 URI 樣板展開的 URL 參數，請使用 `withUrlParameters` 方法：

```php
Http::withUrlParameters([
    'endpoint' => 'https://laravel.com',
    'page' => 'docs',
    'version' => '11.x',
    'topic' => 'validation',
])->get('{+endpoint}/{page}/{version}/{topic}');
```
<a name="dumping-requests"></a>

#### 傾印 Request

若想在送出 Request 前傾印連外 Request 並終止指令碼執行，可在 Request 定義的最前方加上 `dd` 方法：

    return Http::dd()->get('http://example.com');
<a name="request-data"></a>

### Request 資料

當然，我們也很常使用 `POST`、`PUT`、`PATCH` 等 Request 來在 Request 上傳送額外資料，所以這些方法接受資料陣列作為第二個引數。預設情況下，資料會使用 `application/json` ^[Content Type](%E5%85%A7%E5%AE%B9%E5%9E%8B%E5%88%A5) 來傳送：

    use Illuminate\Support\Facades\Http;
    
    $response = Http::post('http://example.com/users', [
        'name' => 'Steve',
        'role' => 'Network Administrator',
    ]);
<a name="get-request-query-parameters"></a>

#### GET Request 查詢參數

在產生 `GET` Request 時，可以直接將^[查詢字串](Query String)加到 URL 上，或是傳入一組索引鍵 / 值配對的陣列作為 `get` 方法的第二個引數：

    $response = Http::get('http://example.com/users', [
        'name' => 'Taylor',
        'page' => 1,
    ]);
或者，可使用 `withQueryParameters` 方法：

    Http::retry(3, 100)->withQueryParameters([
        'name' => 'Taylor',
        'page' => 1,
    ])->get('http://example.com/users')
<a name="sending-form-url-encoded-requests"></a>

#### 傳送 Form URL Encoded 的 Request

若想使用 `application/x-www-form-urlencoded` Content Type 來傳送資料的話，請在建立 Request 前呼叫 `asForm` 方法：

    $response = Http::asForm()->post('http://example.com/users', [
        'name' => 'Sara',
        'role' => 'Privacy Consultant',
    ]);
<a name="sending-a-raw-request-body"></a>

#### Sending a Raw Request Body

在建立 Request 時，若想提供^[原始 Request 內文](Raw Request Body)，可使用 `withBody` 方法。可以在該方法的第二個引數上提供 Content Type：

    $response = Http::withBody(
        base64_encode($photo), 'image/jpeg'
    )->post('http://example.com/photo');
<a name="multi-part-requests"></a>

#### Multi-Part 的 Request

If you would like to send files as multi-part requests, you should call the `attach` method before making your request. This method accepts the name of the file and its contents. If needed, you may provide a third argument which will be considered the file's filename, while a fourth argument may be used to provide headers associated with the file:

    $response = Http::attach(
        'attachment', file_get_contents('photo.jpg'), 'photo.jpg', ['Content-Type' => 'image/jpeg']
    )->post('http://example.com/attachments');
除了直接傳入檔案的原始內容外，也可以傳入一個 ^[Stream Resource](%E4%B8%B2%E6%B5%81%E8%B3%87%E6%BA%90)：

    $photo = fopen('photo.jpg', 'r');
    
    $response = Http::attach(
        'attachment', $photo, 'photo.jpg'
    )->post('http://example.com/attachments');
<a name="headers"></a>

### 標頭

可以使用 `withHeaders` 方法來將^[標頭](Header)加到 Request 上。`withHeaders` 方法接受一組索引鍵 / 值配對的陣列：

    $response = Http::withHeaders([
        'X-First' => 'foo',
        'X-Second' => 'bar'
    ])->post('http://example.com/users', [
        'name' => 'Taylor',
    ]);
可以使用 `accept` 方法來指定你的程式預期所預期 Response 的 Content Type：

    $response = Http::accept('application/json')->get('http://example.com/users');
為了方便起見，可以使用 `acceptJson` 方法來快速指定要預期 Response 的 Content Type 是 `application/json`：

    $response = Http::acceptJson()->get('http://example.com/users');
`withHeaders` 方法會將新的 Header 合併到該 Request 中已存在的 Header。若有需要，可使用 `replaceHeaders` 方法來講整個 Header 取代掉：

```php
$response = Http::withHeaders([
    'X-Original' => 'foo',
])->replaceHeaders([
    'X-Replacement' => 'bar',
])->post('http://example.com/users', [
    'name' => 'Taylor',
]);
```
<a name="authentication"></a>

### 身分驗證

可以使用 `withBasicAuth` 方法來指定使用 Basic 身分驗證的^[認證](Credential)，或是使用 `withDigestAuth` 方法來指定 Digest 身分驗證的認證：

    // Basic authentication...
    $response = Http::withBasicAuth('taylor@laravel.com', 'secret')->post(/* ... */);
    
    // Digest authentication...
    $response = Http::withDigestAuth('taylor@laravel.com', 'secret')->post(/* ... */);
<a name="bearer-tokens"></a>

#### Bearer 權杖

若想快速在 Request 的 `Authorization` 標頭中加上 Bearer ^[權杖](Token)，可使用 `withToken` 方法：

    $response = Http::withToken('token')->post(/* ... */);
<a name="timeout"></a>

### 逾時

`timeout` 方法可用來指定該 Response 最長應等待多久。預設情況下，HTTP Client 會在 30 秒後逾時：

    $response = Http::timeout(3)->get(/* ... */);
If the given timeout is exceeded, an instance of `Illuminate\Http\Client\ConnectionException` will  be thrown.

可以使用 `connectTimeout` 方法來指定嘗試連線到伺服器時要等待的最大秒數：

    $response = Http::connectTimeout(3)->get(/* ... */);
<a name="retries"></a>

### 重試

若想讓 HTTP 用戶端在發生用戶端錯誤或伺服器端錯誤時自動重試，可以使用 `retry` 方法。`retry` 方法接受該 Request 要重試的最大次數，以及每次重試間要等待多少毫秒：

    $response = Http::retry(3, 100)->post(/* ... */);
If you would like to manually calculate the number of milliseconds to sleep between attempts, you may pass a closure as the second argument to the `retry` method:

    use Exception;
    
    $response = Http::retry(3, function (int $attempt, Exception $exception) {
        return $attempt * 100;
    })->post(/* ... */);
For convenience, you may also provide an array as the first argument to the `retry` method. This array will be used to determine how many milliseconds to sleep between subsequent attempts:

    $response = Http::retry([100, 200])->post(/* ... */);
若有需要，可以傳入第三個引數給 `retry` 方法。第三個引數應為一個 Callable，用來判斷是否要重試。舉例來說，我們可以判斷只在 Request 遇到 `ConnectionException` 時才重試：

    use Exception;
    use Illuminate\Http\Client\PendingRequest;
    
    $response = Http::retry(3, 100, function (Exception $exception, PendingRequest $request) {
        return $exception instanceof ConnectionException;
    })->post(/* ... */);
若 Request 查詢失敗，我們可能會想在進行新嘗試前對 Request 做點修改。若要在重新嘗試前對 Request 做修改，我們只需要將提供 `retry` 方法的 Request 引數更改為 Callable 即可。舉例來說，在第一次嘗試回傳身份驗證錯誤時，我們可能會想以新的 Authorization Token 來重試該 Request：

    use Exception;
    use Illuminate\Http\Client\PendingRequest;
    use Illuminate\Http\Client\RequestException;
    
    $response = Http::withToken($this->getToken())->retry(2, 0, function (Exception $exception, PendingRequest $request) {
        if (! $exception instanceof RequestException || $exception->response->status() !== 401) {
            return false;
        }
    
        $request->withToken($this->getNewToken());
    
        return true;
    })->post(/* ... */);
若 Request 執行失敗，會擲回一個 `Illuminate\Http\Client\RequestException` 實體。若想禁用這個行為，可傳入 `false` 給 `throw` 引數。當禁用擲回 Exception 時，會回傳所有重試中用戶端收到的最後一個 Response：

    $response = Http::retry(3, 100, throw: false)->post(/* ... */);
> [!WARNING]  
> 若所有的 Request 都因為連線問題而失敗，即使 `throw` 引數設為 `false`，還是會擲回 `Illuminate\Http\Client\ConnectionException`。

<a name="error-handling"></a>

### 錯誤處理

與 Guzzle 預設的行為不同，Laravel 的 HTTP 用戶端在遇到用戶端錯誤或伺服器端錯誤時 (即，伺服器回傳 `4XX` 與 `5XX` 等級的錯誤)，不會擲回 Exception。我們可以使用 `successful`、`clientError`、`serverError` 等方法來判斷是否遇到這類錯誤：

    // Determine if the status code is >= 200 and < 300...
    $response->successful();
    
    // Determine if the status code is >= 400...
    $response->failed();
    
    // Determine if the response has a 400 level status code...
    $response->clientError();
    
    // Determine if the response has a 500 level status code...
    $response->serverError();
    
    // Immediately execute the given callback if there was a client or server error...
    $response->onError(callable $callback);
<a name="throwing-exceptions"></a>

#### 擲回 Exception

假設有個 Response 實體，而我們想在該 Response 的狀態碼為伺服器端或用戶端錯誤時擲回 `Illuminate\Http\Client\RequestException`，則可以使用 `throw` 或 `throwIf` 方法：

    use Illuminate\Http\Client\Response;
    
    $response = Http::post(/* ... */);
    
    // Throw an exception if a client or server error occurred...
    $response->throw();
    
    // Throw an exception if an error occurred and the given condition is true...
    $response->throwIf($condition);
    
    // Throw an exception if an error occurred and the given closure resolves to true...
    $response->throwIf(fn (Response $response) => true);
    
    // Throw an exception if an error occurred and the given condition is false...
    $response->throwUnless($condition);
    
    // Throw an exception if an error occurred and the given closure resolves to false...
    $response->throwUnless(fn (Response $response) => false);
    
    // Throw an exception if the response has a specific status code...
    $response->throwIfStatus(403);
    
    // Throw an exception unless the response has a specific status code...
    $response->throwUnlessStatus(200);
    
    return $response['user']['id'];
`Illuminate\Http\Client\RequestException` 實體有個 `$response` 公用屬性，我們可以使用該屬性來取得回傳的 Response。

如果沒有發生錯誤，`throw` 方法會回傳 Response 實體，能讓我們在 `throw` 方法後繼續串上其他操作：

    return Http::post(/* ... */)->throw()->json();
若想在 Exception 被擲回前加上其他額外的邏輯，可傳入一個閉包給 `throw` 方法。叫用閉包後，就會自動擲回 Exception，因此我們不需要在閉包內重新擲回 Exception：

    use Illuminate\Http\Client\Response;
    use Illuminate\Http\Client\RequestException;
    
    return Http::post(/* ... */)->throw(function (Response $response, RequestException $e) {
        // ...
    })->json();
<a name="guzzle-middleware"></a>

### Guzzle Middleware

由於 Laravel 的 HTTP 用戶端使用 Guzzle，因此我們也可以使用 [Guzzle 的 Middleware 功能](https://docs.guzzlephp.org/en/stable/handlers-and-middleware.html)來對修改連外 Request，或是檢查連入的 Response。若要修改連外的 Request，可使用 `withRequestMiddleware ` 方法來註冊 Guzzle Middleware：

    use Illuminate\Support\Facades\Http;
    use Psr\Http\Message\RequestInterface;
    
    $response = Http::withRequestMiddleware(
        function (RequestInterface $request) {
            return $request->withHeader('X-Example', 'Value');
        }
    )->get('http://example.com');
類似地，我們也可以使用 `withResponseMiddleware` 方法來註冊 Middleware 以檢查連入的 HTTP Response：

    use Illuminate\Support\Facades\Http;
    use Psr\Http\Message\ResponseInterface;
    
    $response = Http::withResponseMiddleware(
        function (ResponseInterface $response) {
            $header = $response->getHeader('X-Example');
    
            // ...
    
            return $response;
        }
    )->get('http://example.com');
<a name="global-middleware"></a>

#### 全域 Middleware

有時候，我們會想註冊套用到每個連外 Request 與連入 Response 的 Middleware。這時候，我們可以使用 `globalRequestMiddleware` 與 `globalResponseMiddleware` 方法。一般來說，這些方法應在專案的 `AppServiceProvider` 中 `boot` 方法內呼叫：

```php
use Illuminate\Support\Facades\Http;

Http::globalRequestMiddleware(fn ($request) => $request->withHeader(
    'User-Agent', 'Example Application/1.0'
));

Http::globalResponseMiddleware(fn ($response) => $response->withHeader(
    'X-Finished-At', now()->toDateTimeString()
));
```
<a name="guzzle-options"></a>

### Guzzle 選項

You may specify additional [Guzzle request options](http://docs.guzzlephp.org/en/stable/request-options.html) for an outgoing request using the `withOptions` method. The `withOptions` method accepts an array of key / value pairs:

    $response = Http::withOptions([
        'debug' => true,
    ])->get('http://example.com/users');
<a name="global-options"></a>

#### Global Options

To configure default options for every outgoing request, you may utilize the `globalOptions` method. Typically, this method should be invoked from the `boot` method of your application's `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Http;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Http::globalOptions([
        'allow_redirects' => false,
    ]);
}
```
<a name="concurrent-requests"></a>

## 同時進行的 Request

有時候，我們可能會想同時進行多個 HTTP Request。換句話說，不是依序執行 Request，而是同時分派多個 Request。同時執行多個 Request 的話，在處理速度慢的 HTTP API 時就可以大幅提升效能。

所幸，我們只要使用 `pool` 方法就能達成。`pool` 方法接受一個閉包，該閉包會收到 `Illuminate\Http\Client\Pool` 實體，能讓我們輕鬆地將 Request 加到 ^[Request Pool](%E8%AB%8B%E6%B1%82%E9%9B%86%E5%8D%80) 以作分派：

    use Illuminate\Http\Client\Pool;
    use Illuminate\Support\Facades\Http;
    
    $responses = Http::pool(fn (Pool $pool) => [
        $pool->get('http://localhost/first'),
        $pool->get('http://localhost/second'),
        $pool->get('http://localhost/third'),
    ]);
    
    return $responses[0]->ok() &&
           $responses[1]->ok() &&
           $responses[2]->ok();
就像這樣，我們可以依據加入 Pool 的順序來存取每個 Response 實體。若有需要的話，也可以使用 `as` 方法來為 Request 命名，好讓我們能使用名稱來存取對應的 Response：

    use Illuminate\Http\Client\Pool;
    use Illuminate\Support\Facades\Http;
    
    $responses = Http::pool(fn (Pool $pool) => [
        $pool->as('first')->get('http://localhost/first'),
        $pool->as('second')->get('http://localhost/second'),
        $pool->as('third')->get('http://localhost/third'),
    ]);
    
    return $responses['first']->ok();
<a name="customizing-concurrent-requests"></a>

#### 自定同時進行的 Request

`pool` 方法不能與其他如 `withHeaders` 或 `middleware` 等 HTTP Client 方法一起串連使用。若想講自定 Header 或 Middleware 套用到放在 Pool 中的 Request，則必須將這些選項設定到 Pool 中的各個 Request：

```php
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

$headers = [
    'X-Example' => 'example',
];

$responses = Http::pool(fn (Pool $pool) => [
    $pool->withHeaders($headers)->get('http://laravel.test/test'),
    $pool->withHeaders($headers)->get('http://laravel.test/test'),
    $pool->withHeaders($headers)->get('http://laravel.test/test'),
]);
```
<a name="macros"></a>

## Macro

Laravel HTTP 用戶端支援定義「^[Macro](%E5%B7%A8%E9%9B%86)」。通過 Macro，我們就能通過一些流暢且語義化的機制來在專案中為一些服務設定常用的 Request 路徑與標頭。若要開始使用 Macro，我們可以在專案的 `App\Providers\AppServiceProvider` 內 `boot` 方法中定義 Macro：

```php
use Illuminate\Support\Facades\Http;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Http::macro('github', function () {
        return Http::withHeaders([
            'X-Example' => 'example',
        ])->baseUrl('https://github.com');
    });
}
```
設定好 Macro 後，就可以在任何地方叫用這個 Macro，以使用指定的設定來建立 Request：

```php
$response = Http::github()->get('/');
```
<a name="testing"></a>

## 測試

許多 Laravel 的服務都提供了能讓我們輕鬆撰寫測試的功能，而 Laravel 的 HTTP 用戶端也不例外。`Http` Facade 的 `fake` 方法能讓我們指定 HTTP 用戶端在建立 Request 後回傳一組虛擬的 Response。

<a name="faking-responses"></a>

### ^[模擬](Fake) Response

舉例來說，若要讓 HTTP 用戶端為每個 Request 回傳 `200` 狀態碼的空 Response，可呼叫 `fake` 方法，然後不傳入任何引數：

    use Illuminate\Support\Facades\Http;
    
    Http::fake();
    
    $response = Http::post(/* ... */);
<a name="faking-specific-urls"></a>

#### 模擬執行 URL

或者，我們也可以傳入一組陣列給 `fake` 方法。該陣列的索引鍵代表要模擬的 URL，對應的值則為 Response。可使用 `*` 字元來當作萬用字元。當 Request 的 URL 不在模擬列表內時，就會被實際執行。可以使用 `Http` Facade 的 `response` 方法來為這些^[Endpoint](%E7%AB%AF%E9%BB%9E)建立虛擬的 Response：

    Http::fake([
        // Stub a JSON response for GitHub endpoints...
        'github.com/*' => Http::response(['foo' => 'bar'], 200, $headers),
    
        // Stub a string response for Google endpoints...
        'google.com/*' => Http::response('Hello World', 200, $headers),
    ]);
若想為所有不符合的 URL 建立一個遞補用 URL 規則，只要使用單一 `*` 字元即可：

    Http::fake([
        // Stub a JSON response for GitHub endpoints...
        'github.com/*' => Http::response(['foo' => 'bar'], 200, ['Headers']),
    
        // Stub a string response for all other endpoints...
        '*' => Http::response('Hello World', 200, ['Headers']),
    ]);
<a name="faking-response-sequences"></a>

#### 模擬 Response 序列

有時候我們需要讓單一 URL 以固定的順序回傳一系列模擬的 Response。我們可以使用 `Http::sequence`  方法來建立 Request：

    Http::fake([
        // Stub a series of responses for GitHub endpoints...
        'github.com/*' => Http::sequence()
                                ->push('Hello World', 200)
                                ->push(['foo' => 'bar'], 200)
                                ->pushStatus(404),
    ]);
用完 Response 序列內的所有 Response 後，若之後又建立新的 Request，就會導致 Response 序列擲回 Exception。若想指定當序列為空時要回傳的預設 Response，可使用 `whenEmpty` 方法：

    Http::fake([
        // Stub a series of responses for GitHub endpoints...
        'github.com/*' => Http::sequence()
                                ->push('Hello World', 200)
                                ->push(['foo' => 'bar'], 200)
                                ->whenEmpty(Http::response()),
    ]);
若想模擬一系列的 Response，但又不想指定要模擬的特定 URL 格式，可使用 `Http::fakeSequence`  方法：

    Http::fakeSequence()
            ->push('Hello World', 200)
            ->whenEmpty(Http::response());
<a name="fake-callback"></a>

#### 模擬回呼

若某些 Endpoint 需要使用比較複雜的邏輯來判斷要回傳什麼 Response 的話，可傳入一個閉包給 `fake` 方法。該閉包會收到一組 `Illuminate\Http\Client\Request` 的實體，而該閉包必須回傳 Response 實體。在這個閉包內，我們就可以任意加上邏輯來判斷要回傳什麼類型的 Response：

    use Illuminate\Http\Client\Request;
    
    Http::fake(function (Request $request) {
        return Http::response('Hello World', 200);
    });
<a name="preventing-stray-requests"></a>

### 避免漏掉的 Request

若要確保在個別測試或整個測試套件中，所有使用 HTTP 用戶端的 Request 都有被 Fake 到，則可以使用 `preventStrayRequests` 方法。呼叫該方法後，若有任何找不到對應 Fake Response 的 Request，就不會產生實際的 HTTP Request，而會擲回 Exception：

    use Illuminate\Support\Facades\Http;
    
    Http::preventStrayRequests();
    
    Http::fake([
        'github.com/*' => Http::response('ok'),
    ]);
    
    // An "ok" response is returned...
    Http::get('https://github.com/laravel/framework');
    
    // An exception is thrown...
    Http::get('https://laravel.com');
<a name="inspecting-requests"></a>

### 檢查 Request

在模擬 Response 時，有時候我們會需要檢查用戶端收到的 Request，以確保程式有傳送正確的資料。可以在呼叫 `Http::fake` 之前先呼叫 `Http::assertSent` 方法來檢查。

`assertSent` 方法接受一組閉包，該閉包會收到 `Illuminate\Http\Client\Request` 的實體，而該閉包應回傳用來表示 Request 是否符合預期的布林值。若要讓測試通過，提供的 Request 中就必須至少有一個是符合給定預期條件的：

    use Illuminate\Http\Client\Request;
    use Illuminate\Support\Facades\Http;
    
    Http::fake();
    
    Http::withHeaders([
        'X-First' => 'foo',
    ])->post('http://example.com/users', [
        'name' => 'Taylor',
        'role' => 'Developer',
    ]);
    
    Http::assertSent(function (Request $request) {
        return $request->hasHeader('X-First', 'foo') &&
               $request->url() == 'http://example.com/users' &&
               $request['name'] == 'Taylor' &&
               $request['role'] == 'Developer';
    });
若有需要，可以使用 `assertNotSent` 方法來判斷特定 Request 是否未被送出：

    use Illuminate\Http\Client\Request;
    use Illuminate\Support\Facades\Http;
    
    Http::fake();
    
    Http::post('http://example.com/users', [
        'name' => 'Taylor',
        'role' => 'Developer',
    ]);
    
    Http::assertNotSent(function (Request $request) {
        return $request->url() === 'http://example.com/posts';
    });
可以使用 `assertSentCount` 方法來判斷在測試時「送出」了多少個 Request：

    Http::fake();
    
    Http::assertSentCount(5);
或者，也可以使用 `assertNothingSent` 方法來判斷在測試時是否未送出任何 Request：

    Http::fake();
    
    Http::assertNothingSent();
<a name="recording-requests-and-responses"></a>

#### 記錄 Request 或 Response

可以使用 `recorded` 方法來取得所有的 Request 與其對應的 Response。`recorded` 方法會回傳一組陣列的 Collection，其內容為 `Illuminate\Http\Client\Request` 與 `Illuminate\Http\Client\Response` 的實體：

```php
Http::fake([
    'https://laravel.com' => Http::response(status: 500),
    'https://nova.laravel.com/' => Http::response(),
]);

Http::get('https://laravel.com');
Http::get('https://nova.laravel.com/');

$recorded = Http::recorded();

[$request, $response] = $recorded[0];
```
此外，也可傳入閉包給 `recorded` 方法，該閉包會收到 `Illuminate\Http\Client\Request` 與 `Illuminate\Http\Client\Response` 的實體。可以傳入閉包來依據需求過濾 Request／Response 配對：

```php
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;

Http::fake([
    'https://laravel.com' => Http::response(status: 500),
    'https://nova.laravel.com/' => Http::response(),
]);

Http::get('https://laravel.com');
Http::get('https://nova.laravel.com/');

$recorded = Http::recorded(function (Request $request, Response $response) {
    return $request->url() !== 'https://laravel.com' &&
           $response->successful();
});
```
<a name="events"></a>

## 事件

在傳送 HTTP Request 的過程中，Laravel 會觸發三個事件。在送出 Request 前會觸發 `RequestSending` 事件，而給定 Request 收到 Response 後會觸發 `ResponseReceived` 事件。若給定的 Request 未收到 Response，會觸發 `ConnectionFailed` 事件。

The `RequestSending` and `ConnectionFailed` events both contain a public `$request` property that you may use to inspect the `Illuminate\Http\Client\Request` instance. Likewise, the `ResponseReceived` event contains a `$request` property as well as a `$response` property which may be used to inspect the `Illuminate\Http\Client\Response` instance. You may create [event listeners](/docs/{{version}}/events) for these events within your application:

    use Illuminate\Http\Client\Events\RequestSending;
    
    class LogRequest
    {
        /**
         * Handle the given event.
         */
        public function handle(RequestSending $event): void
        {
            // $event->request ...
        }
    }