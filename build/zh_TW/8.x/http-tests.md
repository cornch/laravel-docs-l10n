---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/87/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 47.28
---

# HTTP 測試

- [簡介](#introduction)
- [建立 Request](#making-requests)
  - [自訂 Request Header](#customizing-request-headers)
  - [Cookie](#cookies)
  - [Session 與身份驗證](#session-and-authentication)
  - [為 Response 進行偵錯](#debugging-responses)
  - [處理 Exception](#exception-handling)
  
- [測試 JSON API](#testing-json-apis)
  - [Fluent 的 JSON 測試](#fluent-json-testing)
  
- [測試檔案上傳](#testing-file-uploads)
- [測試 View](#testing-views)
  - [轉譯 Blade 與原件](#rendering-blade-and-components)
  
- [可用的 Assertion](#available-assertions)
  - [Response 上的 Assertion](#response-assertions)
  - [身份驗證的 Assertion](#authentication-assertions)
  

<a name="introduction"></a>

## 簡介

Laravel 提供了一個語義化的 API，這個 API 可以建立連到我們專案的 HTTP ^[Request](%E8%AB%8B%E6%B1%82)，並讓我們能加以檢查 ^[Response](%E5%9B%9E%E8%A6%86)。舉例來說，我們來看看下面定義的這個 Feature Test：

    <?php
    
    namespace Tests\Feature;
    
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function test_a_basic_request()
        {
            $response = $this->get('/');
    
            $response->assertStatus(200);
        }
    }
`get` 方法會建立連到專案的 `GET` Request，而 `assertStatus` 方法則會判斷回傳的 Response 是否為給定的 HTTP 狀態碼。出了這種簡單的 ^[Assertion](%E5%88%A4%E6%96%B7%E6%8F%90%E7%A4%BA) 外，Laravel 也提供了各種不同的 Assertion，可用來檢查 Response 的 ^[Header](%E6%A8%99%E9%A0%AD)、內容、JSON 結構…等。

<a name="making-requests"></a>

## 建立 Request

若要建立連到專案的 Request，可以在測試中叫用 `get`、`post`、`put`、`patch`、`delete` 方法。這些方法不會真的建立「真正的」HTTP Request，而是在程式內部模擬一段網路連線。

這些測試 Request 方法不是回傳 `Illuminate\Http\Response` 實體，而是回傳 `Illuminate\Testing\TestResponse` 的實體。`TestResponse` 實體提供了[各種實用的 Assertion](#available-assertions)，可讓我們檢查專案的 Response：

    <?php
    
    namespace Tests\Feature;
    
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function test_a_basic_request()
        {
            $response = $this->get('/');
    
            $response->assertStatus(200);
        }
    }
一般來說，每個測試都應只向專案建立一個 Request。若在單一測試方法內建立多個 Request，可能會發生未預期的行為。

> [!TIP]  
> 為了方便期間，在執行測試期間會自動禁用 CSRF Middleware。

<a name="customizing-request-headers"></a>

### 自訂 Request 的 Header

可使用 `withHeaders` 方法來在 Request 傳送到專案前先自訂 Request 的 Header。使用這個方法，我們就可以自行加上任何需要的自定 Request：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_interacting_with_headers()
        {
            $response = $this->withHeaders([
                'X-Header' => 'Value',
            ])->post('/user', ['name' => 'Sally']);
    
            $response->assertStatus(201);
        }
    }
<a name="cookies"></a>

### Cookie

我們可以使用 `withCookie` 或 `withCookies` 方法來在建立 Request 前設定 Cookie 值。`withCookie` 方法有兩個引數：Cookie 名稱與 Cookie 值。`withCookies` 方法則接受一組名稱／值配對的陣列：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_interacting_with_cookies()
        {
            $response = $this->withCookie('color', 'blue')->get('/');
    
            $response = $this->withCookies([
                'color' => 'blue',
                'name' => 'Taylor',
            ])->get('/');
        }
    }
<a name="session-and-authentication"></a>

### Session 與身份驗證

Laravel 提供了各種在 HTTP 測試期間處理 Session 的輔助函式。首先，我們可以使用給定 `withSession` 方法來以給定的陣列設定 Session 資料。若要在向專案傳送 Request 前先在 Session 內載入資料，就適合使用這個方法：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_interacting_with_the_session()
        {
            $response = $this->withSession(['banned' => false])->get('/');
        }
    }
由於 Laravel 的 Session 通常是用來保存目前登入使用者的狀態，因此，也有一個 `actingAs` 輔助函式方法，可更簡單地讓我們將給定的使用者登入為目前使用者。舉例來說，我們可以使用 [Model Factory](/docs/{{version}}/database-testing#writing-factories) 來產生並登入使用者：

    <?php
    
    namespace Tests\Feature;
    
    use App\Models\User;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_an_action_that_requires_authentication()
        {
            $user = User::factory()->create();
    
            $response = $this->actingAs($user)
                             ->withSession(['banned' => false])
                             ->get('/');
        }
    }
我們可以使用 `actingAs` 方法的第二個引數來指定要使用哪個 Guard 來驗證給定使用者：

    $this->actingAs($user, 'web')
<a name="debugging-responses"></a>

### 為 Response 進行偵錯

向專案建立測試 Request 後，可使用 `dump`、`dumpHeaders`、`dumpSession` 方法來取得 Response 的內容或對其偵錯：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function test_basic_test()
        {
            $response = $this->get('/');
    
            $response->dumpHeaders();
    
            $response->dumpSession();
    
            $response->dump();
        }
    }
或者，我們可以使用 `dd`、`ddHeaders`、`ddSession` 方法來將該 Response 相關的資料傾印出來，並停止執行：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function test_basic_test()
        {
            $response = $this->get('/');
    
            $response->ddHeaders();
    
            $response->ddSession();
    
            $response->dd();
        }
    }
<a name="exception-handling"></a>

### 處理 Exception

有時候，我們可能會想測試專案是否有擲回特定的 ^[Exception](%E4%BE%8B%E5%A4%96)。為了避免該 Exception 被 Laravel 的 Exception ^[Handler](%E8%99%95%E7%90%86%E5%B8%B8%E5%BC%8F)攔截並轉為 HTTP Response，請在建立 Request 前先叫用 `withoutExceptionHandling` 方法：

    $response = $this->withoutExceptionHandling()->get('/');
此外，若想確保專案中未使用到 PHP 或其他專案使用套件中宣告 ^[Deprecated](%E5%B7%B2%E9%81%8E%E6%99%82) 的功能，我們可以在建立 Request 前叫用 `withoutDeprecationHandling` 方法。停用 Deprecation Handling 後，Deprecation ^[Warning](%E8%AD%A6%E5%91%8A) 會被轉換為 Exception，並導致測試執行失敗：

    $response = $this->withoutDeprecationHandling()->get('/');
<a name="testing-json-apis"></a>

## 測試 JSON API

Laravel 中也提供了數種可用來測試 JSON API 與起 Response 的輔助函式。舉例來說，`json`、`getJson`、`postJson`、`putJson`、`patchJson`、`deleteJson`、`optionsJson` 等方法可用來以各種 HTTP ^[Verb](%E6%8C%87%E4%BB%A4%E5%8B%95%E8%A9%9E) 來建立 JSON Request。我們也可以輕鬆地將資料與 Header 傳給這些方法。若要開始測試 JSON API，我們先來撰寫一個建立連到  `/api/user` 的 `POST` Request，並撰寫預期回傳 JSON 資料的 Assertion：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_making_an_api_request()
        {
            $response = $this->postJson('/api/user', ['name' => 'Sally']);
    
            $response
                ->assertStatus(201)
                ->assertJson([
                    'created' => true,
                ]);
        }
    }
此外，在 Response 上，我們可以用陣列變數的形式來存取 JSON Response 的資料。這麼一來我們就能方便地檢查 JSON Response 中回傳的各個值：

    $this->assertTrue($response['created']);
> [!TIP]  
> `assertJson` 方法會將 Response 轉換為陣列，並使用 `PHPUnit::assertArraySubset` 來驗證給定陣列是否有包含在專案回傳的 JSON Response 中。所以，如果在 JSON Response 中有包含其他屬性，只要給定的部分有包含在 JSON 裡，測試就會通過。

<a name="verifying-exact-match"></a>

#### 判斷 JSON 是否完全符合

剛才也提到過，`assertJson` 方法可用來判斷給定的部分 JSON 是否有包含在 JSON Response 中。若想檢查給定的陣列是否與專案回傳的 JSON **完全符合**，請使用 `assertExactJson` 方法：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_asserting_an_exact_json_match()
        {
            $response = $this->postJson('/user', ['name' => 'Sally']);
    
            $response
                ->assertStatus(201)
                ->assertExactJson([
                    'created' => true,
                ]);
        }
    }
<a name="verifying-json-paths"></a>

#### 判斷 JSON 路徑

若想檢查 JSON Response 中，特定路徑上是否有包含給定資料，可使用 `assertJsonPath` 方法：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_asserting_a_json_paths_value()
        {
            $response = $this->postJson('/user', ['name' => 'Sally']);
    
            $response
                ->assertStatus(201)
                ->assertJsonPath('team.owner.name', 'Darian');
        }
    }
<a name="fluent-json-testing"></a>

### Fluent JSON 測試

Laravel 也提供了另一種較好看、語義化的方法來讓我們測試專案的 JSON Response。若要用這種方法來測試 JSON Response，只需要傳入一個閉包給 `assertJson`。在叫用這個閉包時，該閉包會收到一個 `Illuminate\Testing\Fluent\AssertableJson` 實體。`AssertableJson` 可用來對專案回傳的 JSON 做 Assertion。`where` 方法可用來對 JSON 中特定屬性做 Assertion。而 `missing` 方法可用來判斷 JSON 中是否不含特定屬性：

    use Illuminate\Testing\Fluent\AssertableJson;
    
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function test_fluent_json()
    {
        $response = $this->getJson('/users/1');
    
        $response
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', 1)
                     ->where('name', 'Victoria Faith')
                     ->missing('password')
                     ->etc()
            );
    }
#### 瞭解 `etc` 方法

在上述的範例中，讀者可能有注意到我們在 Assersion 串列的最後面叫用了 `etc` 方法。叫用該方法可讓 Laravel 知道在 JSON 物件中可能還有其他屬性。在沒有使用 `etc` 方法的情況下，若 JSON 物件中還有其他屬性存在，而我們未對這些屬性進行 Assersion 時，測試會執行失敗。

這種在沒有呼叫 `etc` 方法的情況下會使測試失敗的行為，是為了避免讓我們在 JSON Response 中不小心暴露出機敏資訊，所以才強制我們要針對所有屬性做 Assersion，或是使用 `etc` 方法來顯式允許其他額外的屬性。

<a name="asserting-json-attribute-presence-and-absence"></a>

#### 判斷屬性存在／不存在

若要判斷某個屬性存在或不存在，可使用 `has` 或 `missing` 方法：

    $response->assertJson(fn (AssertableJson $json) =>
        $json->has('data')
             ->missing('message')
    );
此外，使用 `hasAll` 或 `missingAll` 方法，就可以同時針對多個屬性判斷存在或不存在：

    $response->assertJson(fn (AssertableJson $json) =>
        $json->hasAll('status', 'data')
             ->missingAll('message', 'code')
    );
我們可以使用 `hasAny` 方法來判斷給定屬性列表中是否至少有一個屬性存在：

    $response->assertJson(fn (AssertableJson $json) =>
        $json->has('status')
             ->hasAny('data', 'message', 'code')
    );
<a name="asserting-against-json-collections"></a>

#### 判斷 JSON Collection

通常來說，在 Route 中回傳的 Json Response 會包含多個項目，如多位使用者：

    Route::get('/users', function () {
        return User::all();
    });
在這些情況下，我們可以使用 Fluent JSON 物件的 `has` 方法來針對該 Response 中的使用者進行 Assertion。舉例來說，我們來判斷 JSON Response 中是否有包含三位使用者。接著，我們再使用 `first` 方法來對該 Collection 中的使用者做 Assertion。`first` 方法接受一個閉包，該閉包會收到另一個可 Assert 的 JSON 字串，我們可以使用這個 JSON 字串來針對該 JSON Collection 中的第一個物件進行 Assertion：

    $response
        ->assertJson(fn (AssertableJson $json) =>
            $json->has(3)
                 ->first(fn ($json) =>
                    $json->where('id', 1)
                         ->where('name', 'Victoria Faith')
                         ->missing('password')
                         ->etc()
                 )
        );
<a name="scoping-json-collection-assertions"></a>

#### 限定範圍的 JSON Collection Assertion

有時候，Route 可能會回傳被指派為命名索引鍵的 JSON Collection：

    Route::get('/users', function () {
        return [
            'meta' => [...],
            'users' => User::all(),
        ];
    })
在測試這類 Route 時，可以使用 `has` 方法來判斷該 Collection 中的項目數。此外，也可以使用 `has` 方法來在一連串的 Assertion 間限制判斷的範圍：

    $response
        ->assertJson(fn (AssertableJson $json) =>
            $json->has('meta')
                 ->has('users', 3)
                 ->has('users.0', fn ($json) =>
                    $json->where('id', 1)
                         ->where('name', 'Victoria Faith')
                         ->missing('password')
                         ->etc()
                 )
        );
不過，除了一次對 `users` Collection 呼叫兩次 `has` 方法以外，我們也可以只呼叫一次，並提供一個閉包作為該方法的第三個引數。傳入閉包時，Laravel 會自動叫用該閉包，並將作用範圍限定在該 Collection 的第一個項目：

    $response
        ->assertJson(fn (AssertableJson $json) =>
            $json->has('meta')
                 ->has('users', 3, fn ($json) =>
                    $json->where('id', 1)
                         ->where('name', 'Victoria Faith')
                         ->missing('password')
                         ->etc()
                 )
        );
<a name="asserting-json-types"></a>

#### 判斷 JSON 型別

我們可能會想檢查 JSON Response 中的某些屬性是否為特定的型別。`Illuminate\Testing\Fluent\AssertableJson` 類別中，提供了 `whereType` 與 `whereAllType` 方法可讓我們檢查 JSON 屬性中的型別：

    $response->assertJson(fn (AssertableJson $json) =>
        $json->whereType('id', 'integer')
             ->whereAllType([
                'users.0.name' => 'string',
                'meta' => 'array'
            ])
    );
我們也可以使用 `|` 字元來指定多個型別，或者，也可以傳入一組型別陣列作為 `whereType` 的第二個引數。若 Response 值符合任意列出的型別，則該 Assertion 會執行成功：

    $response->assertJson(fn (AssertableJson $json) =>
        $json->whereType('name', 'string|null')
             ->whereType('id', ['string', 'integer'])
    );
`whereType` 與 `whereAllType` 方法可支援下列型別： `string`、`integer`、`double`、`boolean`、`array`、`null`。

<a name="testing-file-uploads"></a>

## 測試檔案上傳

`Illuminate\Http\UploadedFile` 類別提供了一個 `fake` 方法，可用來產生用於測試的假檔案或圖片。只要將 `UploadedFile` 的 `fake` 方法與 `Storage` Facade 的 `fake` 方法一起使用，我們就能大幅簡化測試檔案上傳的過程。舉例來說，我們可以將這兩個功能搭配使用，來測試某個上傳使用者大頭照的表單：

    <?php
    
    namespace Tests\Feature;
    
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\Storage;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_avatars_can_be_uploaded()
        {
            Storage::fake('avatars');
    
            $file = UploadedFile::fake()->image('avatar.jpg');
    
            $response = $this->post('/avatar', [
                'avatar' => $file,
            ]);
    
            Storage::disk('avatars')->assertExists($file->hashName());
        }
    }
若想檢查給定的檔案是否不存在，可使用 `Storage` Facade 的 `assertMissing` 方法：

    Storage::fake('avatars');
    
    // ...
    
    Storage::disk('avatars')->assertMissing('missing.jpg');
<a name="fake-file-customization"></a>

#### 自訂 Fake 檔案

在使用 `UploadedFile` 類別的 `fake` 方法來建立檔案時，我們可以指定圖片的長寬與檔案大小 (單位為 kB)，以更好地測試程式中的表單驗證規則：

    UploadedFile::fake()->image('avatar.jpg', $width, $height)->size(100);
除了建立圖片外，還可以使用 `create` 方法來建立任何其他類型的檔案：

    UploadedFile::fake()->create('document.pdf', $sizeInKilobytes);
若有需要，可傳入 `$mimeType` 引數，以明顯定義 File 要回傳的 MIME 型別：

    UploadedFile::fake()->create(
        'document.pdf', $sizeInKilobytes, 'application/pdf'
    );
<a name="testing-views"></a>

## 測試 View

在 Laravel 中，我們也可以在不模擬 HTTP Request 的情況轉譯 View。若要在不模擬 HTTP Request 的情況下轉譯 View，我們可以在測試中呼叫 `view` 方法。`view` 方法的參數為 View 的名稱，以及一組可選的資料陣列。該方法會回傳 `Illuminate\Testing\TestView` 的實體，使用 `TestView`，我們就能方便地針對 View 的內容進行 Assertion：

    <?php
    
    namespace Tests\Feature;
    
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_a_welcome_view_can_be_rendered()
        {
            $view = $this->view('welcome', ['name' => 'Taylor']);
    
            $view->assertSee('Taylor');
        }
    }
`TestView` 類別還提供了下列 Assertion 方法：`assertSee`、`assertSeeInOrder`、`assertSeeText`、`assertSeeTextInOrder`、`assertDontSee`、`assertDontSeeText`。

若有需要，我們可以將 `TestView` 實體轉換型別為字串來取得原始的 View 內容轉譯結果：

    $contents = (string) $this->view('welcome');
<a name="sharing-errors"></a>

#### 共用錯誤訊息

有的 View 會使用到 [Laravel 所提供的全域 Error Bag](/docs/{{version}}/validation#quick-displaying-the-validation-errors)中共享的錯誤。若要在全域 Error Bag 中填充錯誤訊息，可使用 `withViewErrors` 方法：

    $view = $this->withViewErrors([
        'name' => ['Please provide a valid name.']
    ])->view('form');
    
    $view->assertSee('Please provide a valid name.');
<a name="rendering-blade-and-components"></a>

### 轉譯 Blade 與元件

若有需要，可以使用 `blade` 方法來取值並轉譯原始的 [Blade](/docs/{{version}}/blade) 字串。與 `view` 方法類似，`blade` 方法也會回傳 `Illuminate\Testing\TestView` 的實體：

    $view = $this->blade(
        '<x-component :name="$name" />',
        ['name' => 'Taylor']
    );
    
    $view->assertSee('Taylor');
可以使用 `component` 方法來取值並轉譯 [Blade 元件](/docs/{{version}}/blade#components)。與 `view` 方法類似，`component` 元件也會回傳 `Illuminate\Testing\TestView` 的實體：

    $view = $this->component(Profile::class, ['name' => 'Taylor']);
    
    $view->assertSee('Taylor');
<a name="available-assertions"></a>

## 可用的 Assertion

<a name="response-assertions"></a>

### Response 的 Assertion

Laravel 的 `Illuminate\Testing\TestResponse` 類別提供了各種自訂 Assertion 方法供我們在測試程式時使用。這些 Assertion 可在 `json`、`get`、`post`、`put`、`delete` 測試方法回傳的 Response 上存取：

<style>
    .collection-method-list > p {
        column-count: 2; -moz-column-count: 2; -webkit-column-count: 2;
        column-gap: 2em; -moz-column-gap: 2em; -webkit-column-gap: 2em;
    }

    .collection-method-list a {
        display: block;
    }
</style>
<div class="collection-method-list" markdown="1">
[assertCookie](#assert-cookie)
[assertCookieExpired](#assert-cookie-expired)
[assertCookieNotExpired](#assert-cookie-not-expired)
[assertCookieMissing](#assert-cookie-missing)
[assertCreated](#assert-created)
[assertDontSee](#assert-dont-see)
[assertDontSeeText](#assert-dont-see-text)
[assertDownload](#assert-download)
[assertExactJson](#assert-exact-json)
[assertForbidden](#assert-forbidden)
[assertHeader](#assert-header)
[assertHeaderMissing](#assert-header-missing)
[assertJson](#assert-json)
[assertJsonCount](#assert-json-count)
[assertJsonFragment](#assert-json-fragment)
[assertJsonMissing](#assert-json-missing)
[assertJsonMissingExact](#assert-json-missing-exact)
[assertJsonMissingValidationErrors](#assert-json-missing-validation-errors)
[assertJsonPath](#assert-json-path)
[assertJsonStructure](#assert-json-structure)
[assertJsonValidationErrors](#assert-json-validation-errors)
[assertJsonValidationErrorFor](#assert-json-validation-error-for)
[assertLocation](#assert-location)
[assertNoContent](#assert-no-content)
[assertNotFound](#assert-not-found)
[assertOk](#assert-ok)
[assertPlainCookie](#assert-plain-cookie)
[assertRedirect](#assert-redirect)
[assertRedirectContains](#assert-redirect-contains)
[assertRedirectToSignedRoute](#assert-redirect-to-signed-route)
[assertSee](#assert-see)
[assertSeeInOrder](#assert-see-in-order)
[assertSeeText](#assert-see-text)
[assertSeeTextInOrder](#assert-see-text-in-order)
[assertSessionHas](#assert-session-has)
[assertSessionHasInput](#assert-session-has-input)
[assertSessionHasAll](#assert-session-has-all)
[assertSessionHasErrors](#assert-session-has-errors)
[assertSessionHasErrorsIn](#assert-session-has-errors-in)
[assertSessionHasNoErrors](#assert-session-has-no-errors)
[assertSessionDoesntHaveErrors](#assert-session-doesnt-have-errors)
[assertSessionMissing](#assert-session-missing)
[assertSimilarJson](#assert-similar-json)
[assertStatus](#assert-status)
[assertSuccessful](#assert-successful)
[assertUnauthorized](#assert-unauthorized)
[assertUnprocessable](#assert-unprocessable)
[assertValid](#assert-valid)
[assertInvalid](#assert-invalid)
[assertViewHas](#assert-view-has)
[assertViewHasAll](#assert-view-has-all)
[assertViewIs](#assert-view-is)
[assertViewMissing](#assert-view-missing)

</div>
<a name="assert-cookie"></a>

#### assertCookie

判斷 Response 包含給定 Cookie：

    $response->assertCookie($cookieName, $value = null);
<a name="assert-cookie-expired"></a>

#### assertCookieExpired

判斷 Response 包含給定 Cookie，且該 Cookie 已逾期：

    $response->assertCookieExpired($cookieName);
<a name="assert-cookie-not-expired"></a>

#### assertCookieNotExpired

判斷 Response 包含給定 Cookie，且該 Cookie 未逾期：

    $response->assertCookieNotExpired($cookieName);
<a name="assert-cookie-missing"></a>

#### assertCookieMissing

判斷 Response 不包含給定 Cookie：

    $response->assertCookieMissing($cookieName);
<a name="assert-created"></a>

#### assertCreated

判斷 Response 是否為 201 HTTP 狀態碼：

    $response->assertCreated();
<a name="assert-dont-see"></a>

#### assertDontSee

判斷程式回傳的 Response 中是否不包含給定字串。除非將第二個引數設為 `false`，否則該 Assertion 會自動逸出給定的字串：

    $response->assertDontSee($value, $escaped = true);
<a name="assert-dont-see-text"></a>

#### assertDontSeeText

判斷 Response 的文字中是否不包含給定字串。除非將第二個引數設為 `false`，否則該 Assertion 會自動逸出給定的字串。該方法會將 Response 的內容傳給 `strip_tags` PHP 函式，然後再進行判斷：

    $response->assertDontSeeText($value, $escaped = true);
<a name="assert-download"></a>

#### assertDownload

判斷 Response 是否為「檔案下載」。一般來說，就表示所叫用的 Route 回傳了 `Response::download` Response、`BinaryFileResponse`、 或是 `Storage::download` Response：

    $response->assertDownload();
若有需要，可判斷該下載檔案是否為給定的檔名：

    $response->assertDownload('image.jpg');
<a name="assert-exact-json"></a>

#### assertExactJson

判斷 Response 是否包含與給定的 JSON 資料完全相符的內容：

    $response->assertExactJson(array $data);
<a name="assert-forbidden"></a>

#### assertForbidden

判斷 Response 是否為 ^[Forbidden](%E7%A6%81%E6%AD%A2%E5%AD%98%E5%8F%96) (403) HTTP 狀態碼：

    $response->assertForbidden();
<a name="assert-header"></a>

#### assertHeader

判斷 Response 中是否有給定的 Header 與值：

    $response->assertHeader($headerName, $value = null);
<a name="assert-header-missing"></a>

#### assertHeaderMissing

判斷 Response 中是否不含給定的 Header：

    $response->assertHeaderMissing($headerName);
<a name="assert-json"></a>

#### assertJson

判斷 Response 是否包含給定的 JSON 資料：

    $response->assertJson(array $data, $strict = false);
`assertJson` 方法會將 Response 轉換為陣列，並使用 `PHPUnit::assertArraySubset` 來驗證給定陣列是否有包含在專案回傳的 JSON Response 中。所以，如果在 JSON Response 中有包含其他屬性，只要給定的部分有包含在 JSON 裡，測試就會通過。

<a name="assert-json-count"></a>

#### assertJsonCount

判斷 Response JSON 是否為一組陣列，且該陣列在給定的索引鍵上有包含預期數量的項目：

    $response->assertJsonCount($count, $key = null);
<a name="assert-json-fragment"></a>

#### assertJsonFragment

判斷 Response 中，是否有在任意位置上包含給定的 JSON 資料：

    Route::get('/users', function () {
        return [
            'users' => [
                [
                    'name' => 'Taylor Otwell',
                ],
            ],
        ];
    });
    
    $response->assertJsonFragment(['name' => 'Taylor Otwell']);
<a name="assert-json-missing"></a>

#### assertJsonMissing

判斷 Response 中是否不包含給定的 JSON 資料：

    $response->assertJsonMissing(array $data);
<a name="assert-json-missing-exact"></a>

#### assertJsonMissingExact

判斷 Response 是否不包含完全相符的 JSON 資料：

    $response->assertJsonMissingExact(array $data);
<a name="assert-json-missing-validation-errors"></a>

#### assertJsonMissingValidationErrors

判斷 Response 中，給定的索引鍵上是否不含 JSON 驗證錯誤：

    $response->assertJsonMissingValidationErrors($keys);
> [!TIP]  
> 還有一個更泛用的 [assertValid](#assert-valid) 方法，可用來檢查 Response 是否不含以 JSON 格式回傳的驗證錯誤，**並檢查** Session Storage 上是否未有快閃存入錯誤訊息。

<a name="assert-json-path"></a>

#### assertJsonPath

判斷 Response 中在特定路徑上是否包含給定的資料：

    $response->assertJsonPath($path, $expectedValue);
舉例來說，若程式回傳了包含下列資料的 JSON Response：

```js
{
    "user": {
        "name": "Steve Schoger"
    }
}
```
則我們可以像這樣判斷 `user` 物件的 `name` 屬性是否符合給定的值：

    $response->assertJsonPath('user.name', 'Steve Schoger');
<a name="assert-json-structure"></a>

#### assertJsonStructure

判斷 Response 是否含有給定的 JSON 結構：

    $response->assertJsonStructure(array $structure);
舉例來說，若程式回傳了包含下列資料的 JSON Response：

```js
{
    "user": {
        "name": "Steve Schoger"
    }
}
```
也可以像這樣檢查 JSON 結構是否符合預期：

    $response->assertJsonStructure([
        'user' => [
            'name',
        ]
    ]);
有時候，程式會回傳的 JSON Response 會包含一組由物件組成的陣列：

```js
{
    "user": [
        {
            "name": "Steve Schoger",
            "age": 55,
            "location": "Earth"
        },
        {
            "name": "Mary Schoger",
            "age": 60,
            "location": "Earth"
        }
    ]
}
```
這時，我們可以使用 `*` 字元來對該陣列中的所有物件進行結構檢查：

    $response->assertJsonStructure([
        'user' => [
            '*' => [
                 'name',
                 'age',
                 'location'
            ]
        ]
    ]);
<a name="assert-json-validation-errors"></a>

#### assertJsonValidationErrors

判斷 Response 中，給定的索引鍵上是否有給定的 JSON 驗證錯誤。該方法應用於檢查以 JSON 格式回傳的表單驗證錯誤 Response，而不應用於檢查快閃存入 Session 中的表單驗證錯誤：

    $response->assertJsonValidationErrors(array $data, $responseKey = 'errors');
> [!TIP]  
> 還有一個更泛用的 [assertInvalid](#assert-invalid) 方法，可用來檢查 Response 是否包含以 JSON 格式回傳的驗證錯誤，**或是** Session Storage 上是否有快閃存入錯誤訊息。

<a name="assert-json-validation-error-for"></a>

#### assertJsonValidationErrorFor

判斷 Response 中，給定的索引鍵上是否有任何的 JSON 驗證規則：

    $response->assertJsonValidationErrorFor(string $key, $responseKey = 'errors');
<a name="assert-location"></a>

#### assertLocation

判斷 Response 中，`Location` Header 上是否有給定的 URI 值：

    $response->assertLocation($uri);
<a name="assert-no-content"></a>

#### assertNoContent

判斷 Response 是否為給定的 HTTP 狀態碼，且不含內容：

    $response->assertNoContent($status = 204);
<a name="assert-not-found"></a>

#### assertNotFound

判斷 Response 是否為 ^[Not Found](%E6%89%BE%E4%B8%8D%E5%88%B0) (404) HTTP 狀態碼：

    $response->assertNotFound();
<a name="assert-ok"></a>

#### assertOk

判斷 Response 是否為 200 HTTP 狀態碼：

    $response->assertOk();
<a name="assert-plain-cookie"></a>

#### assertPlainCookie

判斷 Response 是否包含給定未加密的 Cookie：

    $response->assertPlainCookie($cookieName, $value = null);
<a name="assert-redirect"></a>

#### assertRedirect

判斷 Response 是否為指向給定 URI 的重新導向：

    $response->assertRedirect($uri);
<a name="assert-redirect-contains"></a>

#### assertRedirectContains

判斷 Response 是否在重新導向至包含給定字串的 URI：

    $response->assertRedirectContains($string);
<a name="assert-redirect-to-signed-route"></a>

#### assertRedirectToSignedRoute

判斷 Response 是否為指向給定簽名 Route 的重新導向：

    $response->assertRedirectToSignedRoute($name = null, $parameters = []);
<a name="assert-see"></a>

#### assertSee

判斷 Response 中是否包含給定字串。除非將第二個引數設為 `false`，否則該 Assertion 會自動逸出給定的字串：

    $response->assertSee($value, $escaped = true);
<a name="assert-see-in-order"></a>

#### assertSeeInOrder

判斷 Response 中，是否有依照順序包含給定字串。除非將第二個引數設為 `false`，否則該 Assertion 會自動逸出給定的字串：

    $response->assertSeeInOrder(array $values, $escaped = true);
<a name="assert-see-text"></a>

#### assertSeeText

判斷 Response 的文字中是否包含給定字串。除非將第二個引數設為 `false`，否則該 Assertion 會自動逸出給定的字串。該方法會將 Response 的內容傳給 `strip_tags` PHP 函式，然後再進行判斷：

    $response->assertSeeText($value, $escaped = true);
<a name="assert-see-text-in-order"></a>

#### assertSeeTextInOrder

判斷 Response 的文字中，是否有依照順序出現給定的字串。除非將第二個引數設為 `false`，否則該 Assertion 會自動逸出給定的字串。該方法會將 Response 的內容傳給 `strip_tags` PHP 函式，然後再進行判斷：

    $response->assertSeeTextInOrder(array $values, $escaped = true);
<a name="assert-session-has"></a>

#### assertSessionHas

判斷 Session 是否包含給定的資料：

    $response->assertSessionHas($key, $value = null);
若有需要，`assertSessionHas` 方法的第二個引數也可以傳入閉包。若閉包回傳 `true`，則會視為 Assertion 通過：

    $response->assertSessionHas($key, function ($value) {
        return $value->name === 'Taylor Otwell';
    });
<a name="assert-session-has-input"></a>

#### assertSessionHasInput

判斷 Session 中[快閃的輸入陣列](/docs/{{version}}/responses#redirecting-with-flashed-session-data)內是否包含給定值：

    $response->assertSessionHasInput($key, $value = null);
若有需要若有需要，`assertSessionHasInput` 方法的第二個引數也可以傳入閉包。若閉包回傳 `true`，則會視為 Assertion 通過：

    $response->assertSessionHasInput($key, function ($value) {
        return Crypt::decryptString($value) === 'secret';
    });
<a name="assert-session-has-all"></a>

#### assertSessionHasAll

判斷 Session 內是否包含給定的索引鍵／值配對陣列：

    $response->assertSessionHasAll(array $data);
舉例來說，若網站的 Session 內有 `name` 與 `status` 索引鍵，則可以像這樣來判斷這兩個欄位是否都存在且為特定的值：

    $response->assertSessionHasAll([
        'name' => 'Taylor Otwell',
        'status' => 'active',
    ]);
<a name="assert-session-has-errors"></a>

#### assertSessionHasErrors

判斷 Session 內給定的 `$keys` 中是否有錯誤。若``$keys` 為關聯式陣列，則會判斷 Session 中，各個欄位 (陣列索引鍵) 是否有特定的錯誤訊息 (陣列值)。請勿使用該方法來測試會回傳 JSON 結構的 Route，請使用該方法來測試將驗證錯誤訊息快閃存入 Session 的 Route：

    $response->assertSessionHasErrors(
        array $keys, $format = null, $errorBag = 'default'
    );
舉例來說，若要判斷 `name` 與 `email` 欄位中是否有快閃存入 Session 的驗證錯誤訊息，可像這樣叫用 `assertSessionHasErrors` 方法：

    $response->assertSessionHasErrors(['name', 'email']);
或者，也可以判斷給定欄位是否有特定的驗證錯誤訊息：

    $response->assertSessionHasErrors([
        'name' => 'The given name was invalid.'
    ]);
<a name="assert-session-has-errors-in"></a>

#### assertSessionHasErrorsIn

判斷 Session 中，在特定的 [Error Bag](/docs/{{version}}/validation#named-error-bags) 中，給定的 `$keys` 內是否有錯誤訊息。若 `$keys` 為關聯式陣列，則會判斷 Session 中各個欄位 (陣列索引鍵) 是否有特定的錯誤訊息 (陣列值)：

    $response->assertSessionHasErrorsIn($errorBag, $keys = [], $format = null);
<a name="assert-session-has-no-errors"></a>

#### assertSessionHasNoErrors

判斷 Session 中是否無驗證錯誤：

    $response->assertSessionHasNoErrors();
<a name="assert-session-doesnt-have-errors"></a>

#### assertSessionDoesntHaveErrors

判斷 Session 中給定的索引鍵是否無驗證錯誤：

    $response->assertSessionDoesntHaveErrors($keys = [], $format = null, $errorBag = 'default');
<a name="assert-session-missing"></a>

#### assertSessionMissing

判斷 Session 中是否不包含給定的索引鍵：

    $response->assertSessionMissing($key);
<a name="assert-status"></a>

#### assertStatus

判斷 Response 是否回傳給定的 HTTP 狀態碼：

    $response->assertStatus($code);
<a name="assert-successful"></a>

#### assertSuccessful

判斷 Response 是否有成功的 HTTP 狀態碼 (>= 200 且 < 300)：

    $response->assertSuccessful();
<a name="assert-unauthorized"></a>

#### assertUnauthorized

判斷 Response 是否為禁止存取 (401) HTTP 狀態碼：

    $response->assertUnauthorized();
<a name="assert-unprocessable"></a>

#### assertUnprocessable

判斷 Response 是否為無法處理 (422) HTTP 狀態碼：

    $response->assertUnprocessable();
<a name="assert-valid"></a>

#### assertValid

判斷 Response 中，不包含給定索引鍵的驗證錯誤。該方法可用來判斷以 JSON 結構回傳驗證錯誤，或是將驗證錯誤快閃存入 Session 的 Response：

    // Assert that no validation errors are present...
    $response->assertValid();
    
    // Assert that the given keys do not have validation errors...
    $response->assertValid(['name', 'email']);
<a name="assert-invalid"></a>

#### assertInvalid

判斷 Response 中，給定的索引鍵是否有驗證錯誤訊息。該方法可用於檢查以 JSON 格式回傳錯誤訊息，或是將驗證錯誤訊息快閃存入 Session 的 Response：

    $response->assertInvalid(['name', 'email']);
也可以判斷給定的索引鍵是否有特定的錯誤訊息。在判斷是否有特定的錯誤訊息時，可提供完整的訊息，或是其中一段的錯誤訊息：

    $response->assertInvalid([
        'name' => 'The name field is required.',
        'email' => 'valid email address',
    ]);
<a name="assert-view-has"></a>

#### assertViewHas

判斷 Response View 中是否包含給定的一部分資料：

    $response->assertViewHas($key, $value = null);
若在 `assertViewHas` 方法中的第二個引數傳入閉包，則可檢查並針對一部分的資料做 Assertion：

    $response->assertViewHas('user', function (User $user) {
        return $user->name === 'Taylor';
    });
此外，也可以在 Response 上以陣列變數的形式來存取 View Data，讓我們可以方便地檢查這些值：

    $this->assertEquals('Taylor', $response['name']);
<a name="assert-view-has-all"></a>

#### assertViewHasAll

判斷 Response View 中是否包含一組資料：

    $response->assertViewHasAll(array $data);
該方法可用於檢查 View 中是否含有符合給定索引鍵的資料：

    $response->assertViewHasAll([
        'name',
        'email',
    ]);
或者，也可以判斷是否包含特定的 View Data，且這些資料是否為指定的值：

    $response->assertViewHasAll([
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com,',
    ]);
<a name="assert-view-is"></a>

#### assertViewIs

判斷 Route 是否回傳給定的 View：

    $response->assertViewIs($value);
<a name="assert-view-missing"></a>

#### assertViewMissing

判斷程式回傳的 Reponse 中，是否不含給定的資料索引鍵：

    $response->assertViewMissing($key);
<a name="authentication-assertions"></a>

### 身份驗證 Assertion

Laravel 也提供了各種與身份驗證相關的 Assertion，讓我們能在專案的 Feature Test 中使用。請注意，這些方法需要在 Test Class 上呼叫，而不是在 `get` 或 `post` 方法回傳的  `Illuminate\Testing\TestResponse`實體上呼叫。

<a name="assert-authenticated"></a>

#### assertAuthenticated

判斷使用者已登入：

    $this->assertAuthenticated($guard = null);
<a name="assert-guest"></a>

#### assertGuest

判斷使用者是否未登入：

    $this->assertGuest($guard = null);
<a name="assert-authenticated-as"></a>

#### assertAuthenticatedAs

判斷是否已登入特定的使用者：

    $this->assertAuthenticatedAs($user, $guard = null);