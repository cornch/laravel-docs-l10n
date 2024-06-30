---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/171/en-zhtw
progress: 94
updatedAt: '2024-06-30T08:27:00Z'
---

# 表單驗證 - Validation

- [簡介](#introduction)
- [「表單驗證」快速入門](#validation-quickstart)
   - [定義 Route](#quick-defining-the-routes)
   - [建立 Controller](#quick-creating-the-controller)
   - [撰寫表單驗證邏輯](#quick-writing-the-validation-logic)
   - [顯示驗證錯誤](#quick-displaying-the-validation-errors)
   - [重新回填表單](#repopulating-forms)
   - [有關可選欄位的注意事項](#a-note-on-optional-fields)
   - [驗證錯誤的 Response 格式](#validation-error-response-format)
- [Form Request 的驗證](#form-request-validation)
   - [建立 Form Request](#creating-form-requests)
   - [授權 Form Request](#authorizing-form-requests)
   - [自訂錯誤訊息](#customizing-the-error-messages)
   - [為表單驗證準備輸入](#preparing-input-for-validation)
- [手動建立 Validator](#manually-creating-validators)
   - [自動重新導向](#automatic-redirection)
   - [命名的 Error Bag](#named-error-bags)
   - [自訂錯誤訊息](#manual-customizing-the-error-messages)
   - [After Validation Hook](#after-validation-hook)
- [處理已驗證的輸入](#working-with-validated-input)
- [處理錯誤訊息](#working-with-error-messages)
   - [在語系檔中指定自訂訊息](#specifying-custom-messages-in-language-files)
   - [在語系檔中指定屬性](#specifying-attribute-in-language-files)
   - [在語系檔中指定值](#specifying-values-in-language-files)
- [可用的表單驗證規則](#available-validation-rules)
- [條件式新增規則](#conditionally-adding-rules)
- [驗證陣列](#validating-arrays)
   - [驗證巢狀陣列輸入](#validating-nested-array-input)
   - [錯誤訊息的索引與位置](#error-message-indexes-and-positions)
- [驗證](#validating-files)
- [驗證密碼](#validating-passwords)
- [自訂驗證規則](#custom-validation-rules)
   - [使用 Rule 物件](#using-rule-objects)
   - [使用閉包](#using-closures)
   - [隱式規則](#implicit-rules)

<a name="introduction"></a>

## 簡介

Laravel 中提供了多種不同方式能讓我們對連入資料做驗證 (Validate)。最常見的方法就是使用所有連入 HTTP Request 上都有的 `validata` 方法來驗證。不過，我們稍後也會討論其他驗證方法。

Laravel 中包含了多種方便的驗證規則可讓你套用到資料上，甚至，Laravel 中也有辦法驗證某個值在給定資料表上是否是不重複的。稍後我們會詳細說明各個驗證規則，讓你熟悉 Laravel 中所有的驗證功能。

<a name="validation-quickstart"></a>

## 「Validation」快速開始

要瞭解有關 Laravel 中強大的驗證功能，我們先來看看一個驗證表單並將錯誤訊息顯示給使用者看的完整範例。閱讀這個高階的範例，讀者將能對如何使用 Laravel 驗證連入的 Request 資料有個基本的理解：

<a name="quick-defining-the-routes"></a>

### 定義 Route

首先，我們假設 `routes/web.php` 檔案中有下列 Route 定義：

    use App\Http\Controllers\PostController;
    
    Route::get('/post/create', [PostController::class, 'create']);
    Route::post('/post', [PostController::class, 'store']);

這個 `GET` Route 會向使用者顯示一個用來建立新部落格貼文的表單，而 `POST` Route 則用來將新部落格貼文儲存到資料庫中。

<a name="quick-creating-the-controller"></a>

### 建立 Controller

接著，我們來看看一個簡單的 Controller，這個 Controller 用來處理這些 Route 的連入 Request。我們現在先把 `store` 方法留空：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    
    class PostController extends Controller
    {
        /**
         * Show the form to create a new blog post.
         *
         * @return \Illuminate\View\View
         */
        public function create()
        {
            return view('post.create');
        }
    
        /**
         * Store a new blog post.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            // Validate and store the blog post...
        }
    }

<a name="quick-writing-the-validation-logic"></a>

### 撰寫驗證邏輯

現在，我們已經準備好可以在 `store` 方法內撰寫驗證新部落格貼文的驗證邏輯了。要撰寫驗證邏輯，我們會使用 `Illuminate\Http\Request` 物件所提供的 `validate` 方法。若驗證規則通過，則程式碼就可以繼續正常執行。不過，若驗證失敗，則會擲回 `Illuminate\Validation\ValidationException` 例外，然後 Laravel 會自動回傳適當的錯誤 Response 給使用者。

若驗證失敗時使用的是傳統 HTTP Request，則會產生一個回到上一頁網址的 Redirect Response。若連入的 Request 是 XHR Request，則會回傳一個[包含驗證錯誤訊息的 JSON Response]](#validation-error-response-format)。

為了更好瞭解 `validate`，我們先回來看看 `store` 方法：

    /**
     * Store a new blog post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ]);
    
        // The blog post is valid...
    }

就像我們可以看到的一樣，我們將驗證規則傳入 `validate` 方法。別擔心 —— 所有可用的規則都有[說明文件](#available-validation-rules)。一樣，若驗證失敗，會自動產生適當的 Response。若驗證成功，我們的 Controller 就會繼續正常執行。

或者，我們也可以不使用以 `|` 分隔的單一字串來指定驗證規則，而是使用一組規則陣列：

    $validatedData = $request->validate([
        'title' => ['required', 'unique:posts', 'max:255'],
        'body' => ['required'],
    ]);

此外，也可以使用 `validateWithBag` 方法來驗證 Request 並將錯誤訊息保存在一個[命名的 Error Bag](#named-error-bags)：

    $validatedData = $request->validateWithBag('post', [
        'title' => ['required', 'unique:posts', 'max:255'],
        'body' => ['required'],
    ]);

<a name="stopping-on-first-validation-failure"></a>

#### 在第一個驗證失敗後就停止

有時候，我們會想在驗證某個屬性時，當遇到第一個驗證失敗就停止執行接下來的驗證規則。為此，可以在該屬性上加上 `bail` 規則：

    $request->validate([
        'title' => 'bail|required|unique:posts|max:255',
        'body' => 'required',
    ]);

在這個例子中，若 `title` 屬性上的 `unique` 規則執行失敗，將不會檢查 `max` 規則。會依照所指派的順序來執行驗證規則。

<a name="a-note-on-nested-attributes"></a>

#### 有關巢狀屬性的注意事項

若連入的 HTTP Request 中包含「巢狀」的欄位資料，請使用「點 (.)」語法來在驗證規則中指定這些欄位：

    $request->validate([
        'title' => 'required|unique:posts|max:255',
        'author.name' => 'required',
        'author.description' => 'required',
    ]);

另一方面，若欄位名稱包含 `.` 字元，則我們可以使用反斜線來逸出句點，以顯式避免被解析成「點 (.)」語法：

    $request->validate([
        'title' => 'required|unique:posts|max:255',
        'v1\.0' => 'required',
    ]);

<a name="quick-displaying-the-validation-errors"></a>

### 顯示驗證錯誤

那麼，如果連入 Request 的欄位沒通過給定的驗證規則呢？就像剛才提到過的，Laravel 會自動將使用者重新導向回到上一個位置。此外，所有的驗證規則與 [Request 輸入](/docs/{{version}}/requests#retrieving-old-input)都會自動被[快閃存入 Session](/docs/{{version}}/session#flash-data)。

`Illuminate\View\Middleware\ShareErrorsFromSession` Middleware 幫我們在專案中所有的 View 間共享了一個 `$errors` 變數。這個 Middleware 在 `web` Middleware 群組中提供。當有套用這個 Middleware 時，所有的 View 中都會有 `$errors` 變數，因此我們能方便地假設 `$errors` 變數擁有都已定義好且可安全地使用。`$errors` 變數是 `Illuminate\Support\MessageBag` 的實體。更多有關該物件的資訊，[請參考 Message Bag 的說明文件](#working-with-error-messages)。

所有，在我們的範例中，當驗證失敗時，使用者會被重新導向到 Controller 的 `create` 方法，讓我們能在 View 中顯示錯誤訊息：

```blade
<!-- /resources/views/post/create.blade.php -->

<h1>Create Post</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Create Post Form -->
```

<a name="quick-customizing-the-error-messages"></a>

#### 自訂錯誤訊息

在專案的 `lang/en/validation.php` 檔案中，有所有 Laravel 內建驗證規則的錯誤訊息。在這個檔案中，我們可以看到每個驗證規則的翻譯欄位。可以依照需求修改這些訊息。

此外，也可以把這個檔案複製到另一個翻譯語系目錄中，以將其翻成你專案的語言。要瞭解 Laravel 中有關本土化 (Localization) 的更多資訊，請參考完整的[本土化說明文件](/docs/{{version}}/localization)。

<a name="quick-xhr-requests-and-validation"></a>

#### XHR Request 與驗證

在這個例子中，我們使用傳統的表單來將資料傳給程式。不過，有許多程式是接受來自 JavaScript 前端的 XHR Request。在 XHR Request 中使用 `validate` 方法時，Laravel 不會產生 Redirect Response，而是產生一個[包含所有驗證錯誤的 JSON Response](#validation-error-response-format)。JSON Response 會以 422 HTTP 狀態碼傳送。

<a name="the-at-error-directive"></a>

#### `@error` 指示詞

可以使用 `@error` Blade 指示詞來快速判斷給定的屬性是否有驗證錯誤訊息。在 `@error` 指示詞內，可以輸出 `$message` 變數來顯示錯誤訊息：

```blade
<!-- /resources/views/post/create.blade.php -->

<label for="title">Post Title</label>

<input id="title"
    type="text"
    name="title"
    class="@error('title') is-invalid @enderror">

@error('title')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
```

若使用[命名的 Error Bag](#named-error-bags)，則可將 Error Bag 的名稱作為第二個引數傳給 `@error` 指示詞：

```blade
<input ... class="@error('title', 'post') is-invalid @enderror">
```

<a name="repopulating-forms"></a>

### 重新回填表單

當 Laravel 因為驗證錯誤而產生 Redirect Response 時，Laravel 會自動[將目前的 Request 輸入快閃存入 Session](/docs/{{version}}/session#flash-data)。這樣一來我們就能在下一個 Request 中方便地存取這些輸入，並將資料重新回填到使用者嘗試送出的表單上。

若要取得前一個 Request 中的快閃輸入，可叫用 `Illuminate\Http\Request` 上的 `old` 方法。`old` 方法從 [Session](/docs/{{version}}/session) 中拉取前次快閃存入輸入資料：

    $title = $request->old('title');

Laravel 也提供了一個全域 `old` 輔助函式。若想在 [Blade 樣板](/docs/{{version}}/blade)中顯示舊輸入，那麼使用 `old` 輔助函式來將其填回表單回比較方便。若給定欄位沒有舊輸入的話，會回傳 `null`：

```blade
<input type="text" name="title" value="{{ old('title') }}">
```

<a name="a-note-on-optional-fields"></a>

### 有關可選欄位的注意事項

預設情況下，Laravel 的全域 Middleware Stack 中包含了 `TrimStrings` 與 `ConvertEmptyStringsToNull` Middleware。這兩個 Middleware 由 `App\Http\Kernel` 類別列在一個 Stack 中。因此，如果不希望 Validator (驗證程式) 把 `null` 值當作無效資料的話，我們常常需要將「可選填」的 Request 欄位標為 `nullable`。舉例來說：

    $request->validate([
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
        'publish_at' => 'nullable|date',
    ]);

在這個範例中，我們指定讓 `publish_at` 欄位可以是 `null` 或是有效的日期呈現。若沒有在規則定義中加上 `nullabale` 修飾詞 (Modifier)，則 Validator 會把 `null` 當作無效的日期。

<a name="validation-error-response-format"></a>

### 驗證錯誤的 Response 格式

當專案擲回 `Illuminate\Validation\ValidationException` Exception 且連入 HTTP Request 預期要回傳 JSON Response 時，Laravel 會自動格式化錯誤訊息，並回傳 `422 Unprocessable Entity` HTTP Response。

下方是一個範例的驗證錯誤 JSON Response 格式。請注意，巢狀的錯誤索引鍵會被扁平化為「點 (.)」表示法：

```json
{
    "message": "The team name must be a string. (and 4 more errors)",
    "errors": {
        "team_name": [
            "The team name must be a string.",
            "The team name must be at least 1 characters."
        ],
        "authorization.role": [
            "The selected authorization.role is invalid."
        ],
        "users.0.email": [
            "The users.0.email field is required."
        ],
        "users.2.email": [
            "The users.2.email must be a valid email address."
        ]
    }
}
```

<a name="form-request-validation"></a>

## Form Request 的驗證

<a name="creating-form-requests"></a>

### 建立 Form Request

在更複雜的驗證情境中，我們可能會想建立一個「Form Request (表單請求)」。Form Request 就是自訂的 Request 類別，其中封裝了該 Request 自己的驗證與認證邏輯。若要建立 Form Request 類別，可使用 `make:request` Artisan CLI 指令：

```shell
php artisan make:request StorePostRequest
```

產生的 Form Request 會被放在 `app/Http/Requests` 目錄中。若該目錄不存在，則執行 `make:request` 指令是會自動建立。Laravel 產生的每個 Form Request 都有兩個方法：`authorize` 與 `rules`。

讀者可能已經猜到，`authorize` 方法是用來判斷目前已登入使用者是否能進行該 Request 所代表的動作。`rules` 方法則回傳要套用到 Request 資料的驗證規則：

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ];
    }

> **Note** 在 `rules` 方法的^[簽章](Signature) 中可以對任何需要的相依性進行^[型別提示](Type-Hint)。型別提示的相依性會由 Laravel 的 [Service Container](/docs/{{version}}/container) 自動解析。

那麼，要怎麼執行驗證規則呢？我們只需要在 Controller 方法中型別提示這個 Request 即可。連入的 Form Request 會在呼叫 Controller 方法前驗證。這表示，我們就不需要在 Controller 中放一些凌亂的驗證邏輯：

    /**
     * Store a new blog post.
     *
     * @param  \App\Http\Requests\StorePostRequest  $request
     * @return Illuminate\Http\Response
     */
    public function store(StorePostRequest $request)
    {
        // 連入的 Request 是有效的...
    
        // 取得已驗證的輸入資料...
        $validated = $request->validated();
    
        // 取得一部分的已驗證輸入資料...
        $validated = $request->safe()->only(['name', 'email']);
        $validated = $request->safe()->except(['name', 'email']);
    }

若驗證失敗，會產生一個 Redirect Response，並將使用者傳送回前一個位置。錯誤訊息也會被快閃存入 Session 中以便顯示。若目前的 Request 是 XHR Request，則會回傳一個 422 狀態碼的 HTTP Response 給使用者，其中包含了[以 JSON 呈現的驗證錯誤訊息](#validation-error-response-format)：

<a name="adding-after-hooks-to-form-requests"></a>

#### 新增 After Hook 到 Form Request

若想將「After」驗證 Hook 加到 Form Request 上，則需要使用 `withValidator` 方法。該方法接收完整建構好的 Validator，能讓你在實際執行驗證規則前呼叫 Validator 上的任何方法：

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        });
    }

<a name="request-stopping-on-first-validation-rule-failure"></a>

#### 在第一個屬性驗證失敗後就停止

在 Request 類別上新增 `stopOnFirstFailure` 屬性後，就可以讓 Validator 在發生一個驗證失敗後就停止驗證所有的屬性：

    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

<a name="customizing-the-redirect-location"></a>

#### 自訂重新導向位置

前面也提到過，Form Request 驗證失敗時會產生一個 Redirect Response 來將使用者傳送到前一個位置。不過，我們可以自訂這個行為。為此，請在 Form Request 中定義一個 `$redirect` 屬性：

    /**
     * The URI that users should be redirected to if validation fails.
     *
     * @var string
     */
    protected $redirect = '/dashboard';

或者，若想將使用者重新導向到命名 Route，請改定義 `$redirectRoute` 屬性：

    /**
     * The route that users should be redirected to if validation fails.
     *
     * @var string
     */
    protected $redirectRoute = 'dashboard';

<a name="authorizing-form-requests"></a>

### 授權 Form Request

Form Request 類別中也包含了一個 `authorize` 方法。在這個方法中，我們可以判斷已登入使用者是否有授權能更新給定資源。舉例來說，我們可以判斷使用者是否真的擁有正在編輯的部落格留言。大多數情況下，在這個方法中我們應該都是使用[授權的 Gate 與 Policy](/docs/{{version}}/authorization)：

    use App\Models\Comment;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $comment = Comment::find($this->route('comment'));
    
        return $comment && $this->user()->can('update', $comment);
    }

由於所有 Form Request 都繼承自 Laravel 的基礎 Request 類別，因此我們可以使用 `user` 方法來存取目前已登入的使用者。此外，也請注意上方範例中呼叫的 `route` 方法。這個方法能讓我們存取目前呼叫的 Route 上的 URI 參數，如上述例子中為 `{comment}` 參數：

    Route::post('/comment/{comment}');

因此，若我們的專案有使用 [Route Model 繫結](/docs/{{version}}/routing#route-model-binding)，則這裡的程式碼還能存取 Request 上已解析的 Model 屬性來進一步簡化：

    return $this->user()->can('update', $this->comment);

若 `authorize` 方法回傳 `false`，則會自動回傳一個 403 狀態碼的 HTTP Respnose，而 Controller 則不會被執行。

若想在程式中的其他部分處理授權邏輯，只要在 `authorize` 方法中回傳 `true` 即可：

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

> **Note** 在 `authorize` 方法的^[簽章](Signature) 中可以對任何需要的相依性進行^[型別提示](Type-Hint)。型別提示中的相依性會由 Laravel 的 [Service Container](/docs/{{version}}/container) 自動解析。

<a name="customizing-the-error-messages"></a>

### 自訂錯誤訊息

可以複寫 `messages` 方法來自訂 Form Request 使用的錯誤訊息。這個方法應回傳一組包含屬性/ 規則配對的陣列與其對應的錯誤訊息：

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'A title is required',
            'body.required' => 'A message is required',
        ];
    }

<a name="customizing-the-validation-attributes"></a>

#### 自訂驗證屬性

Laravel 中許多的內建驗證規則錯誤訊息都包含了一個 `:attribute` 預留位置 (Placeholder)。若想將驗證訊息中 `:attribute` 預留位置該為自訂屬性名稱，可以複寫 `attributes` 方法來指定自訂的名稱。這個方法應回傳一組包含屬性 / 名稱配對的陣列：

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'email' => 'email address',
        ];
    }

<a name="preparing-input-for-validation"></a>

### 為驗證準備輸入

若有需要在套用驗證規則前準備或消毒 (Sanitize) 任何 Request 中的資料，可使用 `prepareForValidation` 方法：

    use Illuminate\Support\Str;
    
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'slug' => Str::slug($this->slug),
        ]);
    }

類似地，若有需要在驗證完成後正常化任何 Request 資料，可使用 `passedValidation` 方法：

    use Illuminate\Support\Str;
    
    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        $this->replace(['name' => 'Taylor']);
    }

<a name="manually-creating-validators"></a>

## 手動建立 Validator

若不想使用 Request 上的 `validate` 方法，也可以使用 `Validator` [Facade](/docs/{{version}}/facades) 來手動建立 Validator 實體。Facade 方法上的 `make` 方法會產生新的 Validator 實體：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    
    class PostController extends Controller
    {
        /**
         * Store a new blog post.
         *
         * @param  Request  $request
         * @return Response
         */
        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|unique:posts|max:255',
                'body' => 'required',
            ]);
    
            if ($validator->fails()) {
                return redirect('post/create')
                            ->withErrors($validator)
                            ->withInput();
            }
    
            // Retrieve the validated input...
            $validated = $validator->validated();
    
            // Retrieve a portion of the validated input...
            $validated = $validator->safe()->only(['name', 'email']);
            $validated = $validator->safe()->except(['name', 'email']);
    
            // Store the blog post...
        }
    }

傳入 `make` 方法的第一個屬性是要驗證的資料。第二個引述則是一組要套用到給定資料上的驗證規則陣列。

在判斷 Request 是否驗證失敗後，可以使用 `withErrors` 方法來將錯誤訊息快閃存入 Session 中。使用這個方法時，重新導向後會自動共享 `$errors` 變數，讓我們能輕鬆將其顯示給使用者。`withErrors` 方法接受一個 Validator、`MessageBag`、或 PHP `array`。

#### 在第一個驗證失敗後就停止

`stopOnFirstFailure` 方法可以讓 Validator 在發生一個驗證失敗後就停止驗證所有的屬性：

    if ($validator->stopOnFirstFailure()->fails()) {
        // ...
    }

<a name="automatic-redirection"></a>

### 自動重新導向

若想手動建立 Validator 實體，但也想要使用 HTTP Request 的 `validate` 方法提供的自動重新導向功能，可以在現有 Validator 實體上呼叫 `validate` 方法。若驗證失敗，使用者會被重新導向。XHR Request 的情況下，則會[回傳 JSON Response](#validation-error-response-format)：

    Validator::make($request->all(), [
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
    ])->validate();

可以使用 `validateWithBag` 方法來在驗證失敗時將錯誤訊息保存在[命名的 Error Bag](#named-error-bags) 中：

    Validator::make($request->all(), [
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
    ])->validateWithBag('post');

<a name="named-error-bags"></a>

### 命名的 Error Bag

若單一頁面中有多個表單，則我們可能會想為保存錯誤訊息的 `MessageBag` 命名。這樣一來，我們就可以為特定的表單取得錯誤訊息。為此，請傳入名稱作為第二個引數給 `withErrors`：

    return redirect('register')->withErrors($validator, 'login');

接著我們就可以在 `$errors` 變數中存取命名的 `MessageBag` 實體：

```blade
{{ $errors->login->first('email') }}
```

<a name="manual-customizing-the-error-messages"></a>

### 自訂錯誤訊息

當然，除了 Laravel 提供的預設錯誤訊息外，我們還可以提供自訂的錯誤訊息給 Validator 實體使用。有許多方法可以指定自訂訊息。第一個方法是，將自訂訊息作為第三個引數傳給 `Validator::make` 方法：

    $validator = Validator::make($input, $rules, $messages = [
        'required' => 'The :attribute field is required.',
    ]);

在這個例子中，`:attribute` 預留位置 (Placeholder) 會被替換成驗證中的實際欄位名稱。我們也可以在驗證訊息中使用其他的預留位置，如：

    $messages = [
        'same' => 'The :attribute and :other must match.',
        'size' => 'The :attribute must be exactly :size.',
        'between' => 'The :attribute value :input is not between :min - :max.',
        'in' => 'The :attribute must be one of the following types: :values',
    ];

<a name="specifying-a-custom-message-for-a-given-attribute"></a>

#### 為給定屬性指定自訂訊息

有時候我們可能會想指為特定的屬性指定錯誤訊息。為此，我們可以使用「點 (.)」標記法。先指定屬性的名稱，然後再加上規則名稱：

    $messages = [
        'email.required' => 'We need to know your email address!',
    ];

<a name="specifying-custom-attribute-values"></a>

#### 指定自訂屬性值

Laravel 中許多內建的錯誤訊息都包含了一個 `:attribute` 預留位置，會被取代成正在驗證的欄位名稱或屬性名稱。若想在指定的欄位上自使用自訂值來取代這些預留位置，可將一組自訂屬性的陣列作為第四個引數傳給 `Validator::make` 方法：

    $validator = Validator::make($input, $rules, $messages, [
        'email' => 'email address',
    ]);

<a name="after-validation-hook"></a>

### 驗證的「After」Hook

我們可以附加一個要在驗證完成後才執行的回呼。這樣一來，我們就可以輕鬆地做進一步的驗證、甚至是將更多的錯誤訊息加到 Message Collection 上。要開始加上 After Hook，請在 Validator 實體上呼叫 `after` 方法：

    $validator = Validator::make(/* ... */);
    
    $validator->after(function ($validator) {
        if ($this->somethingElseIsInvalid()) {
            $validator->errors()->add(
                'field', 'Something is wrong with this field!'
            );
        }
    });
    
    if ($validator->fails()) {
        //
    }

<a name="working-with-validated-input"></a>

## 處理已驗證的輸入

使用 Form Request 或手動建立的 Validator 實體驗證好連入的 Request 資料後，我們可能會想取得連入 Request 中實際被驗證過的資料。有許多種方法可以取得這些資料。第一種方法是在 Form Request 或 Validator 實體上呼叫 `validated` 方法。這個方法會回傳一組驗證過的資料陣列：

    $validated = $request->validated();
    
    $validated = $validator->validated();

或者，也可以在 Form Request 或 Validator 實體上呼叫 `safe` 方法。這個方法會回傳一個 `Illuminate\Support\ValidatedInput` 實體。該物件提供了 `only`、`except`、`all` 等方法，可用來取得一部分已驗證的資料或是整個已驗證資料的陣列：

    $validated = $request->safe()->only(['name', 'email']);
    
    $validated = $request->safe()->except(['name', 'email']);
    
    $validated = $request->safe()->all();

此外，也可迭代 `Illuminate\Support\ValidatedInput` 或像陣列一樣存取：

    // 可以迭代已驗證的資料...
    foreach ($request->safe() as $key => $value) {
        //
    }
    
    // 可用陣列形式存取已驗證的資料...
    $validated = $request->safe();
    
    $email = $validated['email'];

若想在已驗證資料上加上額外的欄位，可呼叫 `merge` 方法：

    $validated = $request->safe()->merge(['name' => 'Taylor Otwell']);

若想將已驗證資料作為 [Collection](/docs/{{version}}/collections) 實體取得，可呼叫 `collect` 方法：

    $collection = $request->safe()->collect();

<a name="working-with-error-messages"></a>

## 處理錯誤訊息

在 `Validator` 實體上呼叫 `errors` 方法後，會收到 `Illuminate\Support\MessageBag` 實體。該實體提供了多種方便的方法能讓我們處理錯誤訊息。自動提供給所有 View 的 `$errors` 變數也是一個 `MessageBag` 類別的實體。

<a name="retrieving-the-first-error-message-for-a-field"></a>

#### 取得某個欄位的第一筆錯誤訊息

若要取得給定欄位的第一筆錯誤訊息，請使用 `first` 方法：

    $errors = $validator->errors();
    
    echo $errors->first('email');

<a name="retrieving-all-error-messages-for-a-field"></a>

#### 取得某個欄位的所有錯誤訊息

若需要取得給定欄位的所有訊息陣列，請使用 `get` 方法：

    foreach ($errors->get('email') as $message) {
        //
    }

在驗證某個陣列格式的表單欄位時，可使用 `*` 字元來取得各個陣列元素的所有錯誤訊息：

    foreach ($errors->get('attachments.*') as $message) {
        //
    }

<a name="retrieving-all-error-messages-for-all-fields"></a>

#### 取得全部欄位的所有訊息

若要取得所有欄位的所有訊息陣列，請使用 `all` 方法：

    foreach ($errors->all() as $message) {
        //
    }

<a name="determining-if-messages-exist-for-a-field"></a>

#### 判斷某個欄位是否有錯誤訊息

`has` 方法可用來判斷給定的欄位是否有錯誤訊息：

    if ($errors->has('email')) {
        //
    }

<a name="specifying-custom-messages-in-language-files"></a>

### 在語系檔中指定自訂訊息

在專案的 `lang/en/validation.php` 檔案中，有所有 Laravel 內建驗證規則的錯誤訊息。在這個檔案中，我們可以看到每個驗證規則的翻譯欄位。可以依照需求修改這些訊息。

此外，也可以把這個檔案複製到另一個翻譯語系目錄中，以將其翻成你專案的語言。要瞭解 Laravel 中有關本土化 (Localization) 的更多資訊，請參考完整的[本土化說明文件](/docs/{{version}}/localization)。

<a name="custom-messages-for-specific-attributes"></a>

#### 為特定屬性指定自訂訊息

我們可能會想在程式的驗證語系檔中為特定的屬性與規則組合自訂錯誤訊息。為此，請在專案的 `lang/xx/validation.php` 語系檔中 `custom` 陣列內新增你的自訂訊息：

    'custom' => [
        'email' => [
            'required' => 'We need to know your email address!',
            'max' => 'Your email address is too long!'
        ],
    ],

<a name="specifying-attribute-in-language-files"></a>

### 在語系檔中指定屬性

Laravel 中內建的許多錯誤訊息都包含了一個 `:attribute` 預留位置 (Placeholder)，該預留位置會被取代為被驗證的欄位名稱或屬性名稱。若想讓驗證訊息的 `:attribute` 部分被取代為自訂的值，可在 `lang/xx/validation.php` 語系檔中 `attributes` 陣列內指定自訂的屬性名稱：

    'attributes' => [
        'email' => 'email address',
    ],

<a name="specifying-values-in-language-files"></a>

### 在語系檔中指定值

Laravel 中有些內建的驗證規則錯誤訊息中包含了一個 `:value` 預留位置 (Placeholder)，這個預留位置會被取代為目前 Request 中的屬性值。不過，有時候我們會像讓驗證訊息中的 `:value` 部分被取代為用於該值的自訂呈現方式。舉例來說，假設我們套用了下列規則來讓 `payment_type` 值為 `cc` 時，信用卡卡號為必填：

    Validator::make($request->all(), [
        'credit_card_number' => 'required_if:payment_type,cc'
    ]);

若驗證規則執行失敗，會產生下列錯誤訊息：

```none
The credit card number field is required when payment type is cc.
```

我們可以在 `lang/xx/validation.php` 語系檔中定義一個 `values` 陣列來為付款方式的值指定一個對使用者更友好的呈現，而不是顯示 `cc`：

    'values' => [
        'payment_type' => [
            'cc' => 'credit card'
        ],
    ],

定義好這個值之後，剛才的驗證規則會產生下列錯誤訊息：

```none
The credit card number field is required when payment type is credit card.
```

<a name="available-validation-rules"></a>

## 可用的驗證規則

下面列出了所有可用的驗證規則與其函式：

<style>
    .collection-method-list > p {
        columns: 10.8em 3; -moz-columns: 10.8em 3; -webkit-columns: 10.8em 3;
    }

    .collection-method-list a {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

<div class="collection-method-list" markdown="1">

[Accepted](#rule-accepted) [Accepted If](#rule-accepted-if) [Active URL](#rule-active-url) [After (Date)](#rule-after) [After Or Equal (Date)](#rule-after-or-equal) [Alpha](#rule-alpha) [Alpha Dash](#rule-alpha-dash) [Alpha Numeric](#rule-alpha-num) [Array](#rule-array) [Ascii](#rule-ascii) [Bail](#rule-bail) [Before (Date)](#rule-before) [Before Or Equal (Date)](#rule-before-or-equal) [Between](#rule-between) [Boolean](#rule-boolean) [Confirmed](#rule-confirmed) [Current Password](#rule-current-password) [Date](#rule-date) [Date Equals](#rule-date-equals) [Date Format](#rule-date-format) [Decimal](#rule-decimal) [Declined](#rule-declined) [Declined If](#rule-declined-if) [Different](#rule-different) [Digits](#rule-digits) [Digits Between](#rule-digits-between) [Dimensions (Image Files)](#rule-dimensions) [Distinct](#rule-distinct) [Doesnt Start With](#rule-doesnt-start-with) [Doesnt End With](#rule-doesnt-end-with) [Email](#rule-email) [Ends With](#rule-ends-with) [Enum](#rule-enum) [Exclude](#rule-exclude) [Exclude If](#rule-exclude-if) [Exclude Unless](#rule-exclude-unless) [Exclude With](#rule-exclude-with) [Exclude Without](#rule-exclude-without) [Exists (Database)](#rule-exists) [File](#rule-file) [Filled](#rule-filled) [Greater Than](#rule-gt) [Greater Than Or Equal](#rule-gte) [Image (File)](#rule-image) [In](#rule-in) [In Array](#rule-in-array) [Integer](#rule-integer) [IP Address](#rule-ip) [JSON](#rule-json) [Less Than](#rule-lt) [Less Than Or Equal](#rule-lte) [Lowercase](#rule-lowercase) [MAC Address](#rule-mac) [Max](#rule-max) [Max Digits](#rule-max-digits) [MIME Types](#rule-mimetypes) [MIME Type By File Extension](#rule-mimes) [Min](#rule-min) [Min Digits](#rule-min-digits) [Missing](#rule-missing) [Missing If](#rule-missing-if) [Missing Unless](#rule-missing-unless) [Missing With](#rule-missing-with) [Missing With All](#rule-missing-with-all) [Multiple Of](#rule-multiple-of) [Not In](#rule-not-in) [Not Regex](#rule-not-regex) [Nullable](#rule-nullable) [Numeric](#rule-numeric) [Password](#rule-password) [Present](#rule-present) [Prohibited](#rule-prohibited) [Prohibited If](#rule-prohibited-if) [Prohibited Unless](#rule-prohibited-unless) [Prohibits](#rule-prohibits) [Regular Expression](#rule-regex) [Required](#rule-required) [Required If](#rule-required-if) [Required Unless](#rule-required-unless) [Required With](#rule-required-with) [Required With All](#rule-required-with-all) [Required Without](#rule-required-without) [Required Without All](#rule-required-without-all) [Required Array Keys](#rule-required-array-keys) [Same](#rule-same) [Size](#rule-size) [Sometimes](#validating-when-present) [Starts With](#rule-starts-with) [String](#rule-string) [Timezone](#rule-timezone) [Unique (Database)](#rule-unique) [Uppercase](#rule-uppercase) [URL](#rule-url) [ULID](#rule-ulid) [UUID](#rule-uuid)

</div>

<a name="rule-accepted"></a>

#### accepted

驗證欄位必須為 `"yes"`、`"on"`、`1`、`true` 等。適用於驗證類似是否已接受「服務條款」等欄位。

<a name="rule-accepted-if"></a>

#### accepted_if:anotherfield,value,...

若另一個驗證欄位符合給定的值，則該驗證欄位必須為 `"yes"`、`"on"`、`1`、`true`。適用於驗證類似是否接受「服務條款」等欄位。

<a name="rule-active-url"></a>

#### active_url

該驗證欄位在使用 `dns_get_record` PHP 函式時必須有有效的 A 紀錄或 AAAA 紀錄。在傳送給 `dns_get_record` 前，主機名稱是從提供的 URL 中使用 `parse_url` PHP 方法取出的。

<a name="rule-after"></a>

#### after:*日期*

該驗證欄位必須為給定日期後的日期。日期會使用 PHP 的 `strtotime` 函式來轉換為有效的 `DataTime` 實體：

    'start_date' => 'required|date|after:tomorrow'

除了將日期字串直接傳入 `strtotime` 取值外，也可以指定另一個欄位來比較日期：

    'finish_date' => 'required|date|after:start_date'

<a name="rule-after-or-equal"></a>

#### after_or_equal:*日期*

該驗證欄位的值必須在給定日期之後或等於給定日期。更多資訊請參考 [after](#rule-after) 規則。

<a name="rule-alpha"></a>

#### alpha

該驗證欄位必須只由 [`\p{L}`](https://util.unicode.org/UnicodeJsps/list-unicodeset.jsp?a=%5B%3AL%3A%5D&g=&i=) 與 [`\p{M}`](https://util.unicode.org/UnicodeJsps/list-unicodeset.jsp?a=%5B%3AM%3A%5D&g=&i=) 內的 Unicode 的字母字元組成。

若要進一步限制該驗證規則為只允許 ASCII 範圍 (`a-z` 與 `A-Z`)，可提供 `ascii` 選項給該驗證規則：

```php
'username' => 'alpha:ascii',
```

<a name="rule-alpha-dash"></a>

#### alpha_dash

該驗證欄位必須完全由 `\p{L}`](https://util.unicode.org/UnicodeJsps/list-unicodeset.jsp?a=%5B%3AL%3A%5D&g=&i=)、[`\p{M}`](https://util.unicode.org/UnicodeJsps/list-unicodeset.jsp?a=%5B%3AM%3A%5D&g=&i=)、[`\p{N}`](https://util.unicode.org/UnicodeJsps/list-unicodeset.jsp?a=%5B%3AN%3A%5D&g=&i=) 內所包含的 Unicode 字母數字字元、以及 ASCII 的減號 (`-`) 與 ASCII 的底線 (`_`) 所組成。

若要進一步限制該驗證規則為只允許 ASCII 範圍 (`a-z` 與 `A-Z`)，可提供 `ascii` 選項給該驗證規則：

```php
'username' => 'alpha_dash:ascii',
```

<a name="rule-alpha-num"></a>

#### alpha_num

該驗證欄位必須完全由 `\p{L}`](https://util.unicode.org/UnicodeJsps/list-unicodeset.jsp?a=%5B%3AL%3A%5D&g=&i=)、[`\p{M}`](https://util.unicode.org/UnicodeJsps/list-unicodeset.jsp?a=%5B%3AM%3A%5D&g=&i=) 與 [`\p{N}`](https://util.unicode.org/UnicodeJsps/list-unicodeset.jsp?a=%5B%3AN%3A%5D&g=&i=) 內所包含的 Unicode 字母數字字元所組成。

若要進一步限制該驗證規則為只允許 ASCII 範圍 (`a-z` 與 `A-Z`)，可提供 `ascii` 選項給該驗證規則：

```php
'username' => 'alpha_num:ascii',
```

<a name="rule-array"></a>

#### array

該欄位必須為一 PHP `array`。

若有提供額外的值給 `array` 規則，則輸入陣列中的每個索引鍵都必須要在提供給該規則的列表值中。在下列的例子中，`admin` 索引鍵是無效的，因為 `admin` 不包含在我們提供給 `array` 規則的數值列表中：

    use Illuminate\Support\Facades\Validator;
    
    $input = [
        'user' => [
            'name' => 'Taylor Otwell',
            'username' => 'taylorotwell',
            'admin' => true,
        ],
    ];
    
    Validator::make($input, [
        'user' => 'array:name,username',
    ]);

一般來說，請總是指定允許出現在陣列中的索引鍵：

<a name="rule-ascii"></a>

#### ascii

該驗證欄位只能由 7 位元的 ASCII 字元組成。

<a name="rule-bail"></a>

#### bail

該欄位中某項驗證規則失敗後，停止驗證該欄位的其他規則。

`bail` 規則會在遇到驗證失敗時停止驗證該欄位，而 `stopOnFirstFailure` 方法則會讓 Validator 在遇到一個驗證失敗的時候就停止所有屬性的驗證：

    if ($validator->stopOnFirstFailure()->fails()) {
        // ...
    }

<a name="rule-before"></a>

#### before:*日期*

驗證欄位必須為給定日期之前的日期。該日期會被傳給 PHP 的 `strtotime` 函式，以轉換為有效的 `DateTime` 實體。此外，與 [`after`](#rule-after) 規則一樣，我們也可以提供驗證欄位中的另一個欄位來作為 `日期` 的值。

<a name="rule-before-or-equal"></a>

#### before_or_equal:*日期*

驗證欄位必須為給定日期或給定日期之前的日期。該日期會被傳給 PHP 的 `strtotime` 函式，以轉換為有效的 `DateTime` 實體。此外，與 [`after`](#rule-after) 規則一樣，我們也可以提供驗證欄位中的另一個欄位來作為 `日期` 的值。

<a name="rule-between"></a>

#### between:*最小值*,*最大值*

該驗證欄位的大小必須介於給定的 *最小值* 與 *最大值* 之間 (含)。字串、數字、陣列、與檔案會使用與 [`size`](#rule-size) 規則相同的方法計算大小。

<a name="rule-boolean"></a>

#### boolean

該驗證欄位必須能被轉為布林值。可接受的輸入為 `true`, `false`, `1`, `0`, `"1"`, 與 `"0"`。

<a name="rule-confirmed"></a>

#### confirmed

該驗證欄位必須與 `{欄位}_confirmation` 相符合。舉例來說，若正在驗證的欄位是 `password`，則輸入中必須有相符的 `password_confirmation` 欄位。

<a name="rule-current-password"></a>

#### current_password

驗證欄位必須符合目前登入使用者的密碼。可以使用規則的第一個參數來指定[認證 Guard](/docs/{{version}}/authentication)。

    'password' => 'current_password:api'

<a name="rule-date"></a>

#### date

驗證欄位在依照 `strtotime` PHP 函式時，必須是有效且非相對的日期。

<a name="rule-date-equals"></a>

#### date_equals:*日期*

該驗證欄位必須為給定日期或給定日期後的日期。日期會使用 PHP 的 `strtotime` 函式來轉換為有效的 `DataTime` 實體：

<a name="rule-date-format"></a>

#### date_format:*格式*,...

驗證欄位必須符合其中一個給定的 *格式*。驗證欄位時只能使用 `date` 或 `date_format` **擇一**，不可同時使用。該驗證規則支援 PHP [DateTime](https://www.php.net/manual/en/class.datetime.php) 類別支援的所有格式。

<a name="rule-decimal"></a>

#### decimal:*最小值*,*最大值*

該驗證欄位必須為數字 (Numeric)，且必須包含特定位數的小數點：

    // 必須正好有兩位小數點 (9.99)...
    'price' => 'decimal:2'
    
    // 必須有介於 2 到 4 位小數點位數...
    'price' => 'decimal:2,4'

<a name="rule-declined"></a>

#### declined

該驗證欄位必須為 `"no"`, `"off"`, `0`, 或 `false`。

<a name="rule-declined-if"></a>

#### declined_if:另一個欄位,值,...

若驗證中另一個欄位符合給定的值時，該驗證欄位必須為 `"no"`, `"off"`, `0`, 或 `false`。

<a name="rule-different"></a>

#### different:*欄位*

該驗證欄位必須與 *欄位* 的值不同。

<a name="rule-digits"></a>

#### digits:*值*

要驗證的整數的長度必須完全符合 **值**。

<a name="rule-digits-between"></a>

#### digits_between:*最小值*,*最大值*

要驗證的整數長度必須介於給定的 **最小值** 與 **最大值**。

<a name="rule-dimensions"></a>

#### dimensions

該驗證欄位必須為一張圖片，且必須符合規則參數所指定的長寬限制：

    'avatar' => 'dimensions:min_width=100,min_height=200'

可用的條件限制為：最小寬度 `min_width`、最大寬度 `max_width`、最小高度 `min_height`、最大高度 `max_height`、寬度 `width`、高度 `height`、長寬比 `ratio`。

長寬比 `ratio` 以寬除以高來呈現。可以使用如 `3/2` 這樣的分數，或是如 `1.5` 這樣的浮點數來表示：

    'avatar' => 'dimensions:ratio=3/2'

由於這個規則要求多個引數，所以也可以使用 `Rule::dimensions` 方法來流暢地建立規則：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    Validator::make($data, [
        'avatar' => [
            'required',
            Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2),
        ],
    ]);

<a name="rule-distinct"></a>

#### distinct

在驗證陣列時，該驗證欄位必須不含重複的值：

    'foo.*.id' => 'distinct'

Distinct 預設使用鬆散的 (Loose) 變數比較。若要使用嚴格 (Strict) 比較，可在驗證規則定義中加上 `strict` 參數：

    'foo.*.id' => 'distinct:strict'

可以將 `ignore_case` 加到驗證規則的參數內來讓該規則忽略大小寫差異：

    'foo.*.id' => 'distinct:ignore_case'

<a name="rule-doesnt-start-with"></a>

#### doesnt_start_with:*foo*,*bar*,...

該驗證欄位不可以任何給定的值開頭。

<a name="rule-doesnt-end-with"></a>

#### doesnt_end_with:*foo*,*bar*,...

該驗證欄位不可以任何給定的值結尾。

<a name="rule-email"></a>

#### email

驗證欄位必須為 E-Mail 位址格式。該驗證規則使用 [`egulias/email-validator`](https://github.com/egulias/EmailValidator) 套件來驗證 E-Mail位址。預設情況下，使用 `RFCValidation` Validator，不過，也可以自訂套用其他驗證風格：

    'email' => 'email:rfc,dns'

上方的例子會套用 `RFCValidation` 與 `DNSCheckValidation` 驗證。此處列出了所有可套用的驗證風格：

<div class="content-list" markdown="1">

- `rfc`: `RFCValidation`
- `strict`: `NoRFCWarningsValidation`
- `dns`: `DNSCheckValidation`
- `spoof`: `SpoofCheckValidation`
- `filter`: `FilterEmailValidation`
- `filter_unicode`: `FilterEmailValidation::unicode()`

</div>

`filter` Validator 使用 PHP 的 `filter_var` 函式，是隨 Laravel 提供的 Validator。在 Laravel 5.8 以前是 Laravel 的預設 E-Mail 驗證行為。

> **Warning** `dns` 與 `spoof` Validator 需要有 PHP 的 `intl` 擴充程式。

<a name="rule-ends-with"></a>

#### ends_with:*foo*,*bar*,...

該驗證欄位必須以其中一個給定的值結尾。

<a name="rule-enum"></a>

#### enum

`Enum` 規則是一個基於類別的規則，會驗證該驗證欄位是否包含有效的 Enum 值。`Enum` 規則接受一個 Enum 的名稱作為其唯一的 Constructor (建構函式) 引數：

    use App\Enums\ServerStatus;
    use Illuminate\Validation\Rules\Enum;
    
    $request->validate([
        'status' => [new Enum(ServerStatus::class)],
    ]);

> **Warning** Enum 只在 PHP 8.1 以上提供。

<a name="rule-exclude"></a>

#### exclude

`validate` 或 `validated` 方法回傳的 Request 資料中會排除此驗證欄位。

<a name="rule-exclude-if"></a>

#### exclude_if:*另一欄位*,*值*

若 *另一欄位* 欄位的值是 *值*，則 `validate` 或 `validated` 方法回傳的 Request 資料中會排除此驗證欄位。

若有需要使用複雜的邏輯條件來排除欄位，可使用 `Rule::excludeIf` 方法。該方法接受一個布林值或閉包。傳入閉包時，該閉包應回傳 `true` 或 `false`，來判斷該驗證欄位是否要被排除：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    Validator::make($request->all(), [
        'role_id' => Rule::excludeIf($request->user()->is_admin),
    ]);
    
    Validator::make($request->all(), [
        'role_id' => Rule::excludeIf(fn () => $request->user()->is_admin),
    ]);

<a name="rule-exclude-unless"></a>

#### exclude_unless:*另一欄位*,*值*

除非 *另一欄位* 為 *值*，否則 `validate` 或 `validated` 方法回傳的 Request 資料中將不會排除該驗證欄位。若 *值* 為 `null` (`exclude_unless:name,null`)，則除非要比較的欄位為 `null` 或 Request 資料中沒有要比較的欄位，否則該驗證欄位將不會被排除。

<a name="rule-exclude-with"></a>

#### exclude_with:*anotherfield*

若 *另一欄位* 存在，則 `validate` 或 `validated` 方法回傳的 Request 資料中將排除該驗證欄位。

<a name="rule-exclude-without"></a>

#### exclude_without:*另一欄位*

若 *另一欄位* 不存在，則 `validate` 或 `validated` 方法回傳的 Request 資料中將排除該驗證欄位。

<a name="rule-exists"></a>

#### exists:*資料表*,*欄位*

該驗證欄位必須在給定資料庫資料表中存在。

<a name="basic-usage-of-exists-rule"></a>

#### Exists 規則的基本用法

    'state' => 'exists:states'

若未指定 `column` 欄位，則會該驗證欄位的名稱。因此，在這個例子中，本規則會驗證 `states` 資料表中是否包含有一筆 `state` 欄位值符合 Request 中 `state` 屬性值的紀錄。

<a name="specifying-a-custom-column-name"></a>

#### 指定自訂欄位名稱

也可以顯式指定本驗證規則要使用的資料庫欄位名稱。只需要將欄位名稱放在資料表名稱後即可：

    'state' => 'exists:states,abbreviation'

有時候，我們可能會需要指定 `exists` 查詢使用的資料庫連線。為此，我們只要在資料表名稱前方加上連線名稱即可：

    'email' => 'exists:connection.staff,email'

除了直接指定資料表名稱外，也可以指定要用來判斷資料表名稱的 Eloquent Model：

    'user_id' => 'exists:App\Models\User,id'

若想自訂該驗證規則執行的查詢，可以使用 `Rule` 類別來流暢地定義該規則。在這個範例中，我們還會使用陣列來指定驗證規則，而不是使用 `|` 字元來區分各個規則：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    Validator::make($data, [
        'email' => [
            'required',
            Rule::exists('staff')->where(function ($query) {
                return $query->where('account_id', 1);
            }),
        ],
    ]);

只要在 `exists` 方法的第二個引數上提供欄位名稱，就可以明顯指定 `Rule::exists` 方法所產生的 `exists` 規則要使用的資料庫欄位名稱：

    'state' => Rule::exists('states', 'abbreviation'),

<a name="rule-file"></a>

#### file

該驗證欄位必須為一成功上傳的檔案。

<a name="rule-filled"></a>

#### filled

當該驗證欄位存在時，不可為空。

<a name="rule-gt"></a>

#### gt:*欄位*

該驗證欄位必須大於給定的 *欄位*。這兩個欄位必須為相同型別。字串、數字、陣列、檔案等，都使用與 [`size`](#rule-size) 規則相同的方式計算大小。

<a name="rule-gte"></a>

#### gte:*欄位*

該驗證欄位必須大於或等於給定的 *欄位*。這兩個欄位必須為相同型別。字串、數字、陣列、檔案等，都使用與 [`size`](#rule-size) 規則相同的方式計算大小。

<a name="rule-image"></a>

#### image

該驗證欄位必須為一圖片 (jpg, jpeg, png, bmp, gif, svg, 或 webp)。

<a name="rule-in"></a>

#### in:*foo*,*bar*,...

該驗證欄位必須要包含在給定的列表值中。使用這個規則時，我們常常需要對陣列 `implode`，所以我們還能使用 `Rule::in` 方法來流暢地建立該規則：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    Validator::make($data, [
        'zones' => [
            'required',
            Rule::in(['first-zone', 'second-zone']),
        ],
    ]);

若與 `array` 規則一起使用 `in` 規則，則輸入陣列中的每個值都必須要包含在提供給 `in` 規則的列表值中。在下面的例子中，輸入陣列內的 `LAS` 機場代碼是無效的，因為提供給 `in` 規則的機場列表中未包含 `LAS`：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    $input = [
        'airports' => ['NYC', 'LAS'],
    ];
    
    Validator::make($input, [
        'airports' => [
            'required',
            'array',
        ],
        'airports.*' => Rule::in(['NYC', 'LIT']),
    ]);

<a name="rule-in-array"></a>

#### in_array:*另一欄位*.*

該驗證欄位的值必須存在於 *另一欄位* 的值中。

<a name="rule-integer"></a>

#### integer

該驗證欄位必須為整數。

> **Warning** 這個驗證規則並不會驗證輸入是否為「^[整數](Integer)」變數型別，只會驗證該輸入值是否為 PHP 的 `FILTER_VALIDATE_INT` 規則接受的類型。若想驗證輸入是否為一數字，請搭配 [`numeric` 驗證規則](#rule-numeric)一起使用此規則。

<a name="rule-ip"></a>

#### ip

該驗證欄位必須為一 IP 位址。

<a name="ipv4"></a>

#### ipv4

該驗證欄位必須為一 IPv4 位址。

<a name="ipv6"></a>

#### ipv6

該驗證欄位必須為一 IPv6 位址。

<a name="rule-json"></a>

#### json

該驗證欄位必須為有效的 JSON 字串。

<a name="rule-lt"></a>

#### lt:*欄位*

該驗證欄位必須小於給定的 *欄位*。這兩個欄位必須為相同型別。字串、數字、陣列、檔案等，將使用與 [`size`](#rule-size) 規則相同的方式計算長度。

<a name="rule-lte"></a>

#### lte:*欄位*

該驗證欄位必須小於或等於給定的 *欄位*。這兩個欄位必須為相同型別。字串、數字、陣列、檔案等，將使用與 [`size`](#rule-size) 規則相同的方式計算長度。

<a name="rule-lowercase"></a>

#### lowercase

該驗證欄位必須為小寫字母。

<a name="rule-mac"></a>

#### mac_address

該驗證欄位必須為一 MAC 位址。

<a name="rule-max"></a>

#### max:*值*

該驗證欄位必須小於或等於最大值 *值*。字串、數字、陣列、檔案等會使用與 [`size`](#rule-size) 規則相同的方法計算大小。

<a name="rule-max-digits"></a>

#### max_digits:*值*

要驗證的整數位數必須小於 *值*。

<a name="rule-mimetypes"></a>

#### mimetypes:*text/plain*,...

該驗證欄位的檔案必須為其中一個給定的 MIME 型別：

    'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'

若要判斷上傳檔案的 MIME 類型，Laravel 會讀取該檔案的內容，並嘗試推測 MIME 類型。推測的 MIME 類型可能會與用戶端提供的 MIME 類型不同。

<a name="rule-mimes"></a>

#### mimes:*foo*,*bar*,...

該驗證欄位的檔案必須為列出的副檔名中其中一個對應的 MIME 類型。

<a name="basic-usage-of-mime-rule"></a>

#### MIME 規則的基礎用法

    'photo' => 'mimes:jpg,bmp,png'

雖然我們只需要指定副檔名，不過這個規則會讀取該檔案的內容並判斷 MIME 類型，再實際去驗證 MIME 類型。可以在下列位置找到一組 MIME 類型與其對應副檔名的列表：

 <https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types>

<a name="rule-min"></a>

#### min:*值*

該驗證欄位必須有最小值 *最小值*。字串、數字、陣列、與檔案會使用與 [`size`](#rule-size) 規則相同的方法計算大小。

<a name="rule-min-digits"></a>

#### min_digits:*值*

要驗證的整數位數必須大於 *值*。

<a name="rule-multiple-of"></a>

#### multiple_of:*值*

該驗證欄位必須為 *值* 的倍數。

<a name="rule-missing"></a>

#### missing

要驗證的欄位不可存在於輸入資料中。

<a name="rule-missing-if"></a>

#### missing_if:*另一欄位*,*值*,...

若 *另一欄位* 欄位為給定的其中一個 *值* 時，該驗證欄位不可存在。

<a name="rule-missing-unless"></a>

#### missing_unless:*另一欄位*,*值*

除非 *另一欄位* 欄位為給定的其中一個 *值* 時，否則該驗證欄位不可存在。

<a name="rule-missing-with"></a>

#### missing_with:*foo*,*bar*,...

**只有在** 任一指定的其他欄位存在時，該驗證欄位不可存在。

<a name="rule-missing-with-all"></a>

#### missing_with_all:*foo*,*bar*,...

**只有在** 所有指定的其他欄位都存在時，該驗證欄位不可存在。

<a name="rule-not-in"></a>

#### not_in:*foo*,*bar*,...

該驗證欄位不可包含在給定的列表值中。可使用 `Rule::notIn` 方法來流暢地建立此規則：

    use Illuminate\Validation\Rule;
    
    Validator::make($data, [
        'toppings' => [
            'required',
            Rule::notIn(['sprinkles', 'cherries']),
        ],
    ]);

<a name="rule-not-regex"></a>

#### not_regex:*格式*

該驗證欄位不可符合給定的正規表示式 (Regular Expression)。

在這個規則內部，使用了 PHP 的 `preg_match` 函式。指定的規則必須符合 `preg_match` 所要求的格式，因此也必須包含有效的^[分隔字元](Delimiter)。例如：`'email' => 'not_regex:/^.+$/i'`。

> **Warning** 在使用 `regex` / `not_regex` 格式時，可能會需要以變數方式來指定驗證規則，而不是使用 `|` 分隔符號。尤其是當正規表示式包含 `|` 字元時。

<a name="rule-nullable"></a>

#### nullable

該驗證欄位可為 `null`。。

<a name="rule-numeric"></a>

#### numeric

該驗證欄位必須為[數字 (Numeric)](https://www.php.net/manual/en/function.is-numeric.php)。

<a name="rule-password"></a>

#### password

該驗證欄位必須符合已登入使用者的密碼。

> **Warning** 該驗證欄位已改名為 `current_password`，並將於 Laravel 9 中移除。請改用 [current_password](#rule-current-password) 規則代替。

<a name="rule-present"></a>

#### present

要驗證的欄位必須存在於輸入資料中。

<a name="rule-prohibited"></a>

#### prohibited

要驗證的欄位必須不存在或為空。當欄位符合下列條件時，將視該欄位為空：

<div class="content-list" markdown="1">

- 該值為 `null`。
- 該值為空字串。
- 該值為空陣列或空的 `Countable` 物件。
- 該值為已上傳的檔案，並且路徑為空。

</div>

<a name="rule-prohibited-if"></a>

#### prohibited_if:*另一欄位*,*值*,...

若 *另一欄位* 相符與任意的 *值*，則要驗證的欄位必須不存在或為空。當欄位滿足下列條件時，將視該欄位為空：

<div class="content-list" markdown="1">

- 該值為 `null`。
- 該值為空字串。
- 該值為空陣列或空的 `Countable` 物件。
- 該值為已上傳的檔案，並且路徑為空。

</div>

若有需要使用複雜的邏輯條件來禁止欄位，可使用 `Rule::prohibitedIf` 方法。該方法接受一個布林值或閉包。傳入閉包時，該閉包應回傳 `true` 或 `false`，來判斷該驗證欄位是否要被禁止：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    Validator::make($request->all(), [
        'role_id' => Rule::prohibitedIf($request->user()->is_admin),
    ]);
    
    Validator::make($request->all(), [
        'role_id' => Rule::prohibitedIf(fn () => $request->user()->is_admin),
    ]);

<a name="rule-prohibited-unless"></a>

#### prohibited_unless:*另一欄位*,*值*,...

除非 *另一欄位* 相符與任意的 *值*，否則要驗證的欄位必須不存在或為空。當欄位滿足下列條件時，將視該欄位為空：

<div class="content-list" markdown="1">

- 該值為 `null`。
- 該值為空字串。
- 該值為空陣列或空的 `Countable` 物件。
- 該值為已上傳的檔案，並且路徑為空。

</div>

<a name="rule-prohibits"></a>

#### prohibits:*另一欄位*,...

若該驗證欄位不存在或為空，則所有 *另一欄位* 的欄位都必須不存在或為空。當欄位滿足下列條件時，將視該欄位為「空」：

<div class="content-list" markdown="1">

- 該值為 `null`。
- 該值為空字串。
- 該值為空陣列或空的 `Countable` 物件。
- 該值為已上傳的檔案，並且路徑為空。

</div>

<a name="rule-regex"></a>

#### regex:*格式*

該驗證欄位必須符合給定的正規表示式 (Regular Expression)。

在這個規則內部，使用了 PHP 的 `preg_match` 函式。指定的格式必須符合 `preg_match` 所要求的格式，因此必須包含^[分隔字元](Delimiter)。如：`'email' => 'regex:/^.+@.+$/i'`。

> **Warning** 使用 `regex` / `not_regex` 格式時，可能有需要使用陣列方式制定規則，而不是使用 `|` 分隔字元。特別是當正規式中有包含 `|` 字元時。

<a name="rule-required"></a>

#### required

該驗證欄位必須存在於數字資料中且不為空。當欄位滿足下列條件時，將視為「空」：

<div class="content-list" markdown="1">

- 該值為 `null`。
- 該值為空字串。
- 該值為空陣列或空的 `Countable` 物件。
- 該值為一無路徑的已上傳檔案。

</div>

<a name="rule-required-if"></a>

#### required_if:*另一欄位*,*值*,...

若 *另一欄位* 符合其中一個 *值* 時，該驗證欄位必須存在且不可為空。

若想為 `required_if` 規則建立更複雜的條件，可使用 `Rule::requiredIf` 方法。該方法接受一個布林或閉包。傳入閉包時，該閉包應回傳 `true` 或 `false` 欄判斷該驗證欄位是否為必填 (Required)：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    Validator::make($request->all(), [
        'role_id' => Rule::requiredIf($request->user()->is_admin),
    ]);
    
    Validator::make($request->all(), [
        'role_id' => Rule::requiredIf(fn () => $request->user()->is_admin),
    ]);

<a name="rule-required-unless"></a>

#### required_unless:*另一欄位*,*值*,...

除非 *另一欄位* 符合其中一個 *值*，否則該驗證欄位必須存在且不可為空。這也表示，除非 *值* 為 `null`，否則 *另一欄位* 必須存在於 Request 資料中。若 *值* 為 `null` (`required_unless:name,null`)，則除非比較的欄位為 `null` 或比較的欄位不存在於 Request 資料中，否則該驗證欄位為^[必填](Required)。

<a name="rule-required-with"></a>

#### required_with:*foo*,*bar*,...

**只有在** 任意指定的其他欄位存在且不為空時，該驗證欄位必須存在且不為空。

<a name="rule-required-with-all"></a>

#### required_with_all:*foo*,*bar*,...

**只有在** 所有指定的其他欄位都存在且都不為空時，該驗證欄位必須存在且不為空。

<a name="rule-required-without"></a>

#### required_without:*foo*,*bar*,...

**只有在** 任意指定的其他欄位為空或不存在時，該驗證欄位必須存在且不為空。

<a name="rule-required-without-all"></a>

#### required_without_all:*foo*,*bar*,...

**只有在** 所有指定的其他欄位都為空或不存在時，該驗證欄位必須存在且不為空。

<a name="rule-required-array-keys"></a>

#### required_array_keys:*foo*,*bar*,...

The field under validation must be an array and must contain at least the specified keys.

<a name="rule-same"></a>

#### same:*欄位*

給定的 *欄位* 必須符合該驗證欄位。

<a name="rule-size"></a>

#### size:*值*

該驗證值必須符合給定 *值* 的大小。若為字串資料，則 *值* 代表字元數。若為^[數字](Numeric)資料，*值* 則對應給定的整數值 (該屬性必須同時使用 `numeric` 或 `integer` 規則)。若為陣列，*值* 對應到陣列的 `count` 結果。若為檔案，則 *size* 對應到單位為 ^[KB](Kilobytes) 的檔案大小。來看看下列範例：

    // 驗證字串為恰好 12 字元長...
    'title' => 'size:12';
    
    // 驗證提供的整數等於 10...
    'seats' => 'integer|size:10';
    
    // 驗證陣列恰好有 5 個元素...
    'tags' => 'array|size:5';
    
    // 驗證上傳檔案的大小為 512 KB...
    'image' => 'file|size:512';

<a name="rule-starts-with"></a>

#### starts_with:*foo*,*bar*,...

該驗證欄位必須以其中一個給定的值開頭。

<a name="rule-string"></a>

#### string

該驗證欄位必須為一字串。若想允許該欄位為 `null`，請為該欄位指定 `nullable` 規則。

<a name="rule-timezone"></a>

#### timezone

該欄位必須為 `timezone_identifiers_list` PHP 函式中的有效^[時區識別子](Timezone Identifier)。

<a name="rule-unique"></a>

#### unique:*資料表*,*欄位*

該驗證欄位必須不存在於給定資料庫資料表中。

**指定自訂的資料表 / 欄位名稱：**

除了直接指定資料表名稱外，也可以指定要用來判斷資料表名稱的 Eloquent Model：

    'email' => 'unique:App\Models\User,email_address'

可使用 `欄位` 選項來指定該欄位對應的資料庫欄位。若未指定 `欄位` 選項，則會使用該驗證欄位的名稱。

    'email' => 'unique:users,email_address'

**指定自訂資料庫連線**

有時候，我們可能需要讓 Validator 在做資料庫查詢時使用自訂的資料庫連線。為此，只需再資料表名稱前方加上連線名稱即可：

    'email' => 'unique:connection.users,email_address'

**強制 Unique 規則忽略給定的 ID：**

有時候我們可能會想在做 Unique 驗證時忽略給定的 ID。舉例來說，假設我們在「更新個人檔案」頁面，其中包含使用者名稱、電子郵件、位置。我們可能會想驗證這個 E-Mail 是否不重複。不過，若使用者只更改姓名欄位而未更改 E-Mail 欄位，這時因為該使用者已經是這個 E-Mail 位址的擁有者了，所以我們就不會想讓再讓 Validator 跑出驗證錯誤。

若想讓 Validator 忽略該使用者的 ID，我們會需要使用 `Rule` 類別來流暢地定義該規則。在這個例子中，我們還會使用陣列來定義驗證規則，而不是使用 `|` 字元來區分各個規則：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    Validator::make($data, [
        'email' => [
            'required',
            Rule::unique('users')->ignore($user->id),
        ],
    ]);

> **Warning** 絕對不要傳入任何由使用者控制的 Request 輸入給 `ignore` 方法。請只傳入 Eloquent Model 實體中由系統產生的不重複 ID，如^[自動遞增 ID](Auto-Incrementing ID) 或 UUID。若傳入了使用者控制的資料，可能會讓你的程式發生如 ^[SQL 注入](SQL Injection)等弱點。

除了直接將 Model 的索引鍵值傳給 `ignore` 方法外，還可以傳入整個 Model 實體。Laravel 會自動從 Model 中取出索引鍵：

    Rule::unique('users')->ignore($user)

若你的資料表使用 `id` 以外的欄位名稱作為主索引鍵，可在呼叫 `ignore` 方法時指定欄位名稱：

    Rule::unique('users')->ignore($user->id, 'user_id')

預設情況下，`unique` 規則會檢查欄位名稱符合欲驗證屬性名稱是否不重複。不過，也可以傳入不同的欄位名稱作為第二個引數給 `unique` 方法：

    Rule::unique('users', 'email_address')->ignore($user->id),

**新增額外的 Where 子句：**

可以使用 `where` 方法來自訂查詢，以指定額外的查詢條件。舉例來說，我們來新增一個查詢條件，將該查詢限制在只搜尋 `account_id` 為 `1` 的紀錄：

    'email' => Rule::unique('users')->where(fn ($query) => $query->where('account_id', 1))

<a name="rule-uppercase"></a>

#### uppercase

該驗證欄位必須為大寫字母。

<a name="rule-url"></a>

#### url

該驗證欄位必須為一有效的網址。

<a name="rule-ulid"></a>

#### ulid

驗證的欄位必須為有效的 [ULID](https://github.com/ulid/spec) (Universally Unique Lexicographically Sortable Identifier)。

<a name="rule-uuid"></a>

#### uuid

該驗證欄位必須為有效的 RFC 4122 (Version 1, 3, 4, 或 5) 之^[通用唯一識別碼](Universally Unique Identifier) (UUID)。

<a name="conditionally-adding-rules"></a>

## 有條件地新增規則

<a name="skipping-validation-when-fields-have-certain-values"></a>

#### 當欄位符合特定值時，略過驗證

有時候我們可能會想只在某個欄位為特定值時，才驗證另一個欄位。為此，可以使用 `exclude_if` 驗證規則。在這個例子中，除非 `has_appointment` 欄位為 `false`，否則將不會驗證 `appointment_date` 與 `doctor_name` 欄位：

    use Illuminate\Support\Facades\Validator;
    
    $validator = Validator::make($data, [
        'has_appointment' => 'required|boolean',
        'appointment_date' => 'exclude_if:has_appointment,false|required|date',
        'doctor_name' => 'exclude_if:has_appointment,false|required|string',
    ]);

或者，也可以使用 `exclude_unless` 規則來在另一個欄位不符合給定值時驗證給定欄位：

    $validator = Validator::make($data, [
        'has_appointment' => 'required|boolean',
        'appointment_date' => 'exclude_unless:has_appointment,true|required|date',
        'doctor_name' => 'exclude_unless:has_appointment,true|required|string',
    ]);

<a name="validating-when-present"></a>

#### 存在時驗證

在某些情況下，我們會需要 **只在** 某個欄位存在於資料中，才去驗證該欄位。要快速搞定這個狀況，只需要在規則列表中加上 `sometimes` 即可：

    $v = Validator::make($data, [
        'email' => 'sometimes|required|email',
    ]);

在上述例子中，只有在 `$data` 陣列中有 `email` 欄位時，才會驗證該欄位。

> **Note** 若想驗證某個欄位必須存在，但可為空，請參考[這個關於可選欄位的備註](#a-note-on-optional-fields)。

<a name="complex-conditional-validation"></a>

#### 複雜的條件式驗證

有時候，我們可能會想以更複雜的條件邏輯來新增驗證規則。舉例來說，我們可能會想在另一個欄位大於 100 時，才要求給定欄位為必填。或者，我們可能需要在某個欄位存在時才驗證某兩個欄位是否有給定的值。要新增這類規則不會很難。首先，先使用不會變動的 **靜態規則** 來建立 `Validator` 實體：

    use Illuminate\Support\Facades\Validator;
    
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'games' => 'required|numeric',
    ]);

先假設我們在做一個給遊戲收藏家用的網站。假設某個遊戲收藏家註冊了這個網站，且該收藏家擁有超過 100 款遊戲，我們就想問問這個收藏家位什麼擁有這麼多遊戲。舉例來說，這個收藏家可能在經營二手遊戲店、或者這個收藏家只是很喜歡收藏遊戲而已。若要有條件地新增這個要求，可以在 `Validator` 實體上使用 `sometimes` 方法。

    $validator->sometimes('reason', 'required|max:500', function ($input) {
        return $input->games >= 100;
    });

傳入 `sometimes` 方法的引數是我們要條件式驗證的欄位名稱。第二個引數是我們要新增的規則列表。若第三個引數的閉包回傳 `true`，就會新增這些規則。這麼一來，我們就能建立更複雜的條件式驗證了。我們還能一次位多個欄位新增條件式驗證：

    $validator->sometimes(['reason', 'cost'], 'required', function ($input) {
        return $input->games >= 100;
    });

> **Note** 傳給閉包的 `$input` 引數會是 `Illuminate\Support\Fluent` 的實體。且可用來存取所有正在驗證的輸入與檔案。

<a name="complex-conditional-array-validation"></a>

#### 複雜的條件式陣列驗證

有時候，我們可能會想依據同一個巢狀陣列中的另一個欄位來驗證某個欄位，但同時我們又不知道這個巢狀陣列的索引鍵。在這種情況下，我們可以在閉包中接收第二個引數，該引數位為目前在驗證的陣列中目前的項目：

    $input = [
        'channels' => [
            [
                'type' => 'email',
                'address' => 'abigail@example.com',
            ],
            [
                'type' => 'url',
                'address' => 'https://example.com',
            ],
        ],
    ];
    
    $validator->sometimes('channels.*.address', 'email', function ($input, $item) {
        return $item->type === 'email';
    });
    
    $validator->sometimes('channels.*.address', 'url', function ($input, $item) {
        return $item->type !== 'email';
    });

與傳給閉包的 `$input` 類似，當屬性資料是陣列時，`$item` 參數也會是 `Illuminate\Support\Fluent` 的實體。若非陣列，則會是字串。

<a name="validating-arrays"></a>

## 驗證陣列

與 [`array` 驗證規則說明文件](#rule-array)中討論過的類似，`array` 規則接收一個允許的陣列索引鍵列表。若該陣列中有出現其他的索引鍵，會驗證失敗：

    use Illuminate\Support\Facades\Validator;
    
    $input = [
        'user' => [
            'name' => 'Taylor Otwell',
            'username' => 'taylorotwell',
            'admin' => true,
        ],
    ];
    
    Validator::make($input, [
        'user' => 'array:username,locale',
    ]);

一般來說，請總是指定陣列中可出現的索引鍵。如未指定可出現的索引鍵，即使這些索引鍵未經過其他巢狀陣列驗證規則驗證，Validator 的 `validate` 方法與 `validated` 方法回傳的所有已驗證中資料，還是會包含該陣列與其所有的索引鍵。

<a name="validating-nested-array-input"></a>

### 驗證巢狀的陣列輸入

依據表單輸入欄位來驗證巢狀的陣列並不會很難。我們可以使用「^[『點』標記法](Dot Natation)」來在陣列中驗證屬性。舉例來說，若連入的 HTTP Request 包含了 `photos[profile]` 欄位，我們可以像這樣驗證該欄位：

    use Illuminate\Support\Facades\Validator;
    
    $validator = Validator::make($request->all(), [
        'photos.profile' => 'required|image',
    ]);

也可以驗證陣列中的各個元素。舉例來說，若要驗證給定陣列輸入欄位中的各個 E-Mail 是否不重複，可以這麼做：

    $validator = Validator::make($request->all(), [
        'person.*.email' => 'email|unique:users',
        'person.*.first_name' => 'required_with:person.*.last_name',
    ]);

類似的，[在語系檔中自訂驗證訊息](#custom-messages-for-specific-attributes)時，也可以使用 `*` 字元，讓我們只需要單一驗證訊息就能輕鬆地在陣列欄位上使用：

    'custom' => [
        'person.*.email' => [
            'unique' => 'Each person must have a unique email address',
        ]
    ],

<a name="accessing-nested-array-data"></a>

#### 存取巢狀陣列資料

有時候，在為屬性指派認證規則時，我們可能會想存取給定巢狀陣列項目的值。為此，我們可以使用 `Rule::forEach` 方法來達成。`forEach` 方法接受一個閉包。在認證時，每次迭代陣列屬性都會叫用一次這個閉包，且該閉包會收到屬性值與完整展開的屬性名稱。該閉包應回傳一個陣列，其中包含要指派給陣列元素的認證規則：

    use App\Rules\HasPermission;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    
    $validator = Validator::make($request->all(), [
        'companies.*.id' => Rule::forEach(function ($value, $attribute) {
            return [
                Rule::exists(Company::class, 'id'),
                new HasPermission('manage-company', $value),
            ];
        }),
    ]);

<a name="error-message-indexes-and-positions"></a>

### 錯誤訊息的索引與位置

在驗證陣列時，有時候我們可能會想在顯示錯誤訊息時參照特定項目的索引或位置。若要參照驗證失敗項目的索引或位置，可在[自定驗證訊息](#manual-customizing-the-error-messages)中使用 `:index` (從 0 開始) 與 `:position` (從 1 開始) 預留位置：

    use Illuminate\Support\Facades\Validator;
    
    $input = [
        'photos' => [
            [
                'name' => 'BeachVacation.jpg',
                'description' => 'A photo of my beach vacation!',
            ],
            [
                'name' => 'GrandCanyon.jpg',
                'description' => '',
            ],
        ],
    ];
    
    Validator::validate($input, [
        'photos.*.description' => 'required',
    ], [
        'photos.*.description.required' => 'Please describe photo #:position.',
    ]);

在上述的範例中，會驗證失敗，而使用者會看到這個錯誤訊息：「**Please describe photo #2.**」

<a name="validating-files"></a>

## 驗證檔案

Laravel 提供了多種驗證規則，可用來驗證已上傳的檔案，如 `mimes`、`image`、`min`、`max`。雖然我們也可以自行個別指定這些規則，但 Laravel 還提供了一種能流暢建立檔案驗證規則的建構程式：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rules\File;
    
    Validator::validate($input, [
        'attachment' => [
            'required',
            File::types(['mp3', 'wav'])
                ->min(1024)
                ->max(12 * 1024),
        ],
    ]);

若專案接受使用者上傳圖片，則可使用 `File` 規則的 `image` Constructor 方法來指定這個上傳的檔案應為圖片。此外，使用 `dimensions` 規則可用來限制圖片的長寬：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rules\File;
    
    Validator::validate($input, [
        'photo' => [
            'required',
            File::image()
                ->min(1024)
                ->max(12 * 1024)
                ->dimensions(Rule::dimensions()->maxWidth(1000)->maxHeight(500)),
        ],
    ]);

> **Note** 更多有關驗證圖片長寬的資訊，請參考 [dimension 規則的說明文件](#rule-dimensions)。

<a name="validating-files-file-types"></a>

#### 檔案類型

雖然在叫用 `types` 方法時只需要指定副檔名，但該方法其實會實際讀取檔案的內容名推測其 MIME 型別，然後再驗證該檔案實際的 MIME 型別。完整的 MIME 型別列表，以及這些 MIME 對應的副檔名可在下列位置中找到：

 <https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types>

<a name="validating-passwords"></a>

## 驗證密碼

若要確定輸入的密碼有足夠的複雜度，可使用 Laravel 的 `Password` 規則物件：

    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rules\Password;
    
    $validator = Validator::make($request->all(), [
        'password' => ['required', 'confirmed', Password::min(8)],
    ]);

`Password` 規則物件能讓我們輕鬆地為我們的專案自訂密碼複雜度的要求。例如：我們可以指定密碼必須至少要有一個字母、一個數字、一個符號、或是有大小寫混合的字元：

    // 至少要有 8 字元...
    Password::min(8)
    
    // 至少要有 1 個英文字母...
    Password::min(8)->letters()
    
    // 至少要有一個大寫與一個小寫字母...
    Password::min(8)->mixedCase()
    
    // 至少要有 1 個數字...
    Password::min(8)->numbers()
    
    // 至少要有 1 個符號...
    Password::min(8)->symbols()

此外，還可以使用 `uncompromised` 方法來確保該密碼在公開的密碼^[資料外洩](Data Breach)中未曾被入侵：

    Password::min(8)->uncompromised()

在這個方法內部，`Password` 規則物件會使用 [k-Anonymity](https://en.wikipedia.org/wiki/K-anonymity) 模型來在 [haveibeenpwned.com](https://haveibeenpwned.com) 上以不犧牲使用者隱私或安全性的前提判斷密碼是否有被外洩。

預設情況下，若密碼出現在只少一個資料外洩中，就會被當作^[已被入侵](Compromised)。我們可以使用 `uncompromised` 方法的第一個引述來修改這個門檻：

    // 確認密碼在同一個資料外洩中只出現少於 3 次...
    Password::min(8)->uncompromised(3);

當然，我們還可以將上述的例子中所有的方法都串在一起：

    Password::min(8)
        ->letters()
        ->mixedCase()
        ->numbers()
        ->symbols()
        ->uncompromised()

<a name="defining-default-password-rules"></a>

#### 定義預設的密碼規則

對一些專案來說，在程式中的單一位置內指定預設的密碼驗證規則可能會比較方便。只要使用 `Password::defaults` 方法就可以輕鬆達成。該方法接受一個閉包，該閉包應回傳預設的 Password 規則設定。一般來說，應在專案內其中一個 Service Provider 中 `boot` 方法內呼叫這個 `defaults` 方法：

```php
use Illuminate\Validation\Rules\Password;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Password::defaults(function () {
        $rule = Password::min(8);

        return $this->app->isProduction()
                    ? $rule->mixedCase()->uncompromised()
                    : $rule;
    });
}
```

接著，若要在某個密碼驗證中套用預設規則，只需要呼叫 `defaults` 方法即可。不需帶任何參數：

    'password' => ['required', Password::defaults()],

有時候，除了預設的密碼驗證規則外，我們可能會想附加一些額外的規則上去。為此，可以使用 `rules` 方法：

    use App\Rules\ZxcvbnRule;
    
    Password::defaults(function () {
        $rule = Password::min(8)->rules([new ZxcvbnRule]);
    
        // ...
    });

<a name="custom-validation-rules"></a>

## 自訂驗證規則

<a name="using-rule-objects"></a>

### 使用規則物件

Laravel 提供了多種實用的驗證規則。不過，有時候我們可能會想自訂一個規則。要註冊自訂驗證規則的其中一個方法就是使用 Rule 物件。若要產生新的 Rule 物件，可使用 `make:rule` Artisan 指令。讓我們來使用這個指令產生一個檢查字串是否為大寫的規則。Laravel 會將該規則放在 `app/Rules` 目錄內。若該目錄不存在，執行這個 Artisan 指令時，Laravel 會自動幫你建立：

```shell
php artisan make:rule Uppercase --invokable
```

建立好規則後，就可以來定義其行為了。Rule 物件只包含了單一方法：`__invoke`。該方法會收到屬性的名稱、屬性值、以及一個應在驗證失敗時以錯誤訊息叫用的回呼：

    <?php
    
    namespace App\Rules;
    
    use Illuminate\Contracts\Validation\InvokableRule;
    
    class Uppercase implements InvokableRule
    {
        /**
         * Run the validation rule.
         *
         * @param  string  $attribute
         * @param  mixed  $value
         * @param  \Closure  $fail
         * @return void
         */
        public function __invoke($attribute, $value, $fail)
        {
            if (strtoupper($value) !== $value) {
                $fail('The :attribute must be uppercase.');
            }
        }
    }

定義好規則後，就可以與其他驗證規則一起，將 Rule 物件的實體傳給 Validator，以使用該規則：

    use App\Rules\Uppercase;
    
    $request->validate([
        'name' => ['required', 'string', new Uppercase],
    ]);

#### 翻譯驗證訊息

除了提供字面錯誤訊息給 `$fail` 閉包外，也可以提供[翻譯字串的索引鍵](/docs/{{version}}/localization)，並告訴 Laravel 要翻譯這個錯誤訊息：

    if (strtoupper($value) !== $value) {
        $fail('validation.uppercase')->translate();
    }

若有需要，`translate` 方法的第一個引數可以設定預留位置 (Placeholder) 的取代值，第二個引數可以設定偏好的語言：

    $fail('validation.location')->translate([
        'value' => $this->value,
    ], 'fr')

#### 存取額外資料

若這個自訂驗證 Rule 類別需要存取正在驗證的所有其他資料，則可以讓 Rule 類別實作 `Illuminate\Contracts\Validation\DataAwareRule` 介面。該介面會要求類別要定義 `setData` 方法。這個方法會由 Laravel (在驗證開始前) 自動叫用，並會傳入所有要驗證的資料：

    <?php
    
    namespace App\Rules;
    
    use Illuminate\Contracts\Validation\DataAwareRule;
    use Illuminate\Contracts\Validation\InvokableRule;
    
    class Uppercase implements DataAwareRule, InvokableRule
    {
        /**
         * All of the data under validation.
         *
         * @var array
         */
        protected $data = [];
    
        // ...
    
        /**
         * Set the data under validation.
         *
         * @param  array  $data
         * @return $this
         */
        public function setData($data)
        {
            $this->data = $data;
    
            return $this;
        }
    }

或者，若這個驗證規則需要存取正在進行驗證的 Validator 實體，則可以實作 `ValidatorAwareRule` 介面：

    <?php
    
    namespace App\Rules;
    
    use Illuminate\Contracts\Validation\InvokableRule;
    use Illuminate\Contracts\Validation\ValidatorAwareRule;
    
    class Uppercase implements InvokableRule, ValidatorAwareRule
    {
        /**
         * The validator instance.
         *
         * @var \Illuminate\Validation\Validator
         */
        protected $validator;
    
        // ...
    
        /**
         * Set the current validator.
         *
         * @param  \Illuminate\Validation\Validator  $validator
         * @return $this
         */
        public function setValidator($validator)
        {
            $this->validator = $validator;
    
            return $this;
        }
    }

<a name="using-closures"></a>

### 使用閉包

若在專案中只有一個地方會需要某個自訂驗證規則，除了使用 Rule 物件外，我們可以使用閉包。這個閉包會收到屬性名稱、屬性值、以及一個要在驗證失敗時呼叫的 `$fail` 回呼：

    use Illuminate\Support\Facades\Validator;
    
    $validator = Validator::make($request->all(), [
        'title' => [
            'required',
            'max:255',
            function ($attribute, $value, $fail) {
                if ($value === 'foo') {
                    $fail('The '.$attribute.' is invalid.');
                }
            },
        ],
    ]);

<a name="implicit-rules"></a>

### 隱式規則

預設情況下，若正在驗證的屬性不存在或包含空字串時，就不會執行包含自訂規則在內的一般驗證規則。舉例來說，遇到空字串時 [`unique`](#rule-unique) 規則將不會執行：

    use Illuminate\Support\Facades\Validator;
    
    $rules = ['name' => 'unique:users,name'];
    
    $input = ['name' => ''];
    
    Validator::make($input, $rules)->passes(); // true

如果要在屬性為空時也執行自定規則，則該規則必須暗示該屬性為 `required`。若要產生新的隱式規則物件，可在呼叫 `make:rule` Artisan 指令時提供 `--implicit` 選項：

```shell
php artisan make:rule Uppercase --invokable --implicit
```

> **Warning** 「隱式」規則只 **暗示** 該屬性為必填欄位。至於當屬性不存在或屬性為空時是否要視為驗證失敗，則取決於你。
