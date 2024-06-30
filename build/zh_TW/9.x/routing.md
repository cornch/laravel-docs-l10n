---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/141/en-zhtw
progress: 99
updatedAt: '2024-06-30T08:27:00Z'
---

# 路由

- [基礎路由](#basic-routing)
   - [重新導向的 Route](#redirect-routes)
   - [View Route](#view-routes)
   - [Route 列表](#the-route-list)
- [Route 參數](#route-parameters)
   - [必填參數](#required-parameters)
   - [可選參數](#parameters-optional-parameters)
   - [正規表示式的條件限制](#parameters-regular-expression-constraints)
- [命名 Route](#named-routes)
- [Route 群組](#route-groups)
   - [Middleware](#route-group-middleware)
   - [Controller](#route-group-controllers)
   - [子網域的路由](#route-group-subdomain-routing)
   - [Route 前置詞](#route-group-prefixes)
   - [Route 名稱的前置詞](#route-group-name-prefixes)
- [Route 的 Model 繫結](#route-model-binding)
   - [隱式繫結](#implicit-binding)
   - [隱式 Enum 繫結](#implicit-enum-binding)
   - [顯式繫結](#explicit-binding)
- [遞補 Route](#fallback-routes)
- [頻率限制](#rate-limiting)
   - [定義 Rate Limiter](#defining-rate-limiters)
   - [在 Route 上附加 Rate Limiter](#attaching-rate-limiters-to-routes)
- [Form Method 的模擬](#form-method-spoofing)
- [存取目前 Route](#accessing-the-current-route)
- [跨原始來源資源共用 (CORS, Cross-Origin Resource Sharing)(#cors)
- [Route 快取](#route-caching)

<a name="basic-routing"></a>

## 基礎路由

最基礎的 Laravel Route (路由) 就是接受一個 URI 與一個閉包，我們可以使用簡單直觀的方法來定義 Route 與其行為，而不需複雜 Route 設定檔：

    use Illuminate\Support\Facades\Route;
    
    Route::get('/greeting', function () {
        return 'Hello World';
    });

<a name="the-default-route-files"></a>

#### 預設的 Route 檔案

Laravel 中所有的 Route 都在 Route 檔案中定義，這些檔案位在 `routes` 目錄下。這些檔案會由專案中的 `App\Providers\RouteServiceProvider` 自動載入。`routes/web.php` 檔案中定義了網頁介面的 Route。這些 Route 被指派到 `web` Middleware 群組中，該 Middleware 群組提供了一些如 Session 狀態與 CSRF 保護等功能。`routes/api.php` 是無狀態的 (Stateless)，裡面的 Route 會被指派給 `api` Middleware 群組。

對於大多數的程式來說，我們會在 `routes/web.php` 檔案中定義 Route。我們可以在瀏覽器中打開 Route 定義的 URL 來存取 `routes/web.php` 中定義的路由。舉例來說，我們可以在瀏覽器中打開 `http://example.com/user` 來存取下來路由：

    use App\Http\Controllers\UserController;
    
    Route::get('/user', [UserController::class, 'index']);

`routes/api.php` 檔案中定義的 Route 放在巢狀放置在 `RouteServiceProvider` 中的 Route 群組內。在這個群組中，Laravel 會自動加上 `/api` URI 前置詞 (Prefix)，因此在這個檔案中，我們不需手動在所有 Route 前方加上 `/api`。我們也可以修改 `RouteServiceProvider` 類別來修改這個前置詞以及其他一些 Route 群組的選項。

<a name="available-router-methods"></a>

#### 可用的 Router 方法

使用 Router 就能讓我們註冊能回應任何 HTTP 動詞的 Route：

    Route::get($uri, $callback);
    Route::post($uri, $callback);
    Route::put($uri, $callback);
    Route::patch($uri, $callback);
    Route::delete($uri, $callback);
    Route::options($uri, $callback);

有時候，我們可能需要註冊一個能回應多個 HTTP 動詞的 Route。這時可以使用 `match` 方法。或者，我們甚至可以使用 `any` 方法來註冊一個回應所有 HTTP 動詞的 Route：

    Route::match(['get', 'post'], '/', function () {
        //
    });
    
    Route::any('/', function () {
        //
    });

> **Note** 註冊多個共享同 URI 的 Route 時，應將這些 `any`, `match`, 與 `redirect` 方法的 Route 定義在 `get`, `post`, `put`, `patch`, `delete`, 與 `options` 方法定義之前。這樣一來可以確保連入的 Request 被配對到正確的 Route 上。

<a name="dependency-injection"></a>

#### 相依性插入

可以在 Route 的回呼簽章 (Signature) 上型別提示 (Type-Hint) 任何 Route 所需的相依性。Laravel 的 [Service Container](/docs/{{version}}/container) 會自動解析並插入所定義的相依性。舉例來說，我們可以型別提示 `Illuminate\Http\Request` 並自動插入到 Route 回呼中，該類別代表目前的 HTTP Request：

    use Illuminate\Http\Request;
    
    Route::get('/users', function (Request $request) {
        // ...
    });

<a name="csrf-protection"></a>

#### CSRF 保護

請記得，當 HTML 表單指向 `web` Route 檔的 `POST`, `PUT`, `PATCH`, 與 `DELETE` Route 時，都應包含一個 CSRF 權杖欄位。若未包含權杖欄位，則該 Request 會被拒絕。更多有關 CSRF 保護的資訊可以參考 [CSRF 說明文件](/docs/{{version}}/csrf)：

    <form method="POST" action="/profile">
        @csrf
        ...
    </form>

<a name="redirect-routes"></a>

### 重新導向的 Route

若想定義可以重新導向到另一個 URI 的 Route，可以使用 `Route::redirect` 方法。這個方法提供了一個方便的捷徑，讓你不需要為了簡單的重新導向定義完整的 Route 或 Controller：

    Route::redirect('/here', '/there');

預設情況下，`Route::redirect` 回傳 `302` 狀態碼。我們可以使用可選的第三個參數來自訂狀態碼：

    Route::redirect('/here', '/there', 301);

或者，我們也可以使用 `Route::permanentRedirect` 方法來回傳 `301` 狀態碼：

    Route::permanentRedirect('/here', '/there');

> **Warning** 在重新導向 Route 中使用 Route 參數時，有幾個參數名稱是 Laravel 的保留字，無法使用：`destination` 與 `status`。

<a name="view-routes"></a>

### View 的 Route

若某個 Route 只需要回傳一個 [View](/docs/{{version}}/views)，則可以使用 `Route::view` 方法。與 `redirect` 方法類似，這個方法提供了一個簡單的捷徑，能讓我們不需定義完整的 Route 或 Controller。`view` 方法接受一個 URI 作為其第一個引數，而第二個引數則是 View 的名稱。此外，也可以提供一組陣列，其中包含要傳給 View 的資料，並作為可選的第三個引數傳入：

    Route::view('/welcome', 'welcome');
    
    Route::view('/welcome', 'welcome', ['name' => 'Taylor']);

> **Warning** 在 View 的 Route 中使用 Route 參數時，有幾個參數名稱是 Laravel 的保留字，無法使用：`view`、`data`、`status`、`header`。

<a name="the-route-list"></a>

### Route 列表

使用 `route:list` Artisan 指令就可輕鬆檢視專案中定義的所有 Route 一覽：

```shell
php artisan route:list
```

預設情況下，指派給各個 Route 的 Middleware 不會顯示在 `route:list` 輸出中。不過，我們可以在該指令後加上 `-v` 選項來讓 Laravel 顯示 Route Middleware：

```shell
php artisan route:list -v
```

也可以讓 Laravel 值顯示以給定 URI 開頭的 Route：

```shell
php artisan route:list --path=api
```

此外，也可以在執行 `route:list` 指令時提供 `--except-vendor` 選項來讓 Laravel 隱藏由第三方套件所定義的 Route：

```shell
php artisan route:list --except-vendor
```

類似地，執行 `route:list` 指令時，也可以提供 `--only-vendor` 選項來讓 Laravel 只顯示第三方套件定義的 Route：

```shell
php artisan route:list --only-vendor
```

<a name="route-parameters"></a>

## Route 參數

<a name="required-parameters"></a>

### 必填參數

在 Route 中，有時候我們會想從 URI 中擷取一個片段。舉例來說，我們可能會需要從 URI 中擷取出使用者的 ID。為此，我們可以定義 Route 參數：

    Route::get('/user/{id}', function ($id) {
        return 'User '.$id;
    });

根據 Route 的需求，我們可以定義不限數量的 Route 參數：

    Route::get('/posts/{post}/comments/{comment}', function ($postId, $commentId) {
        //
    });

Route 參數必須要包裝在 `{}` 大括號中，且只能使用字母。在 Route 參數名稱中也可以使用 (`_`)。Route 參數會依照順序插入到 Route 的回呼或 Controller 上 —— Route 的回呼或 Controller 中的名稱並不影響。

<a name="parameters-and-dependency-injection"></a>

#### 參數與相依性插入

若你的 Route 有使用讓 Laravel Service Container 自動插入到 Route 回呼的相依性的話，請將 Route 參數列在相依性之後：

    use Illuminate\Http\Request;
    
    Route::get('/user/{id}', function (Request $request, $id) {
        return 'User '.$id;
    });

<a name="parameters-optional-parameters"></a>

### 可選的參數

有時候，我們可能會讓某個 Route 參數不需要出現在每個 URI 上。為此，我們可以在參數名稱後方放置一個 `?` 符號。請先確定這個 Route 中對應的變數有預設值：

    Route::get('/user/{name?}', function ($name = null) {
        return $name;
    });
    
    Route::get('/user/{name?}', function ($name = 'John') {
        return $name;
    });

<a name="parameters-regular-expression-constraints"></a>

### 正規表示式條件

可以在 Route 實體上使用 `where` 方法來規定 Route 參數的格式。`where` 方法接受一個參數名稱、以及一個用來規範參數格式的正規表示式：

    Route::get('/user/{name}', function ($name) {
        //
    })->where('name', '[A-Za-z]+');
    
    Route::get('/user/{id}', function ($id) {
        //
    })->where('id', '[0-9]+');
    
    Route::get('/user/{id}/{name}', function ($id, $name) {
        //
    })->where(['id' => '[0-9]+', 'name' => '[a-z]+']);

為了方便起見，一些常用的正規式都有輔助方法，可以讓你快速將這些格式套用到 Route 上：

    Route::get('/user/{id}/{name}', function ($id, $name) {
        //
    })->whereNumber('id')->whereAlpha('name');
    
    Route::get('/user/{name}', function ($name) {
        //
    })->whereAlphaNumeric('name');
    
    Route::get('/user/{id}', function ($id) {
        //
    })->whereUuid('id');
    
    Route::get('/user/{id}', function ($id) {
        //
    })->whereUlid('id');
    
    Route::get('/category/{category}', function ($category) {
        //
    })->whereIn('category', ['movie', 'song', 'painting']);

若連入 Request 不符合 Route 的格式限制，則會回傳 404 HTTP Response。

<a name="parameters-global-constraints"></a>

#### 全域條件限制

若想以某個正規式規範所有相同的 Route 參數，可以使用 `pattern` 方法。可以在專案的 `App\Providers\RouteServiceProvider` 類別中 `boot` 方法內定義這些格式：

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('id', '[0-9]+');
    }

定義好之後，這個規則會自動套用到有使用這個參數名稱的 Route：

    Route::get('/user/{id}', function ($id) {
        // 只會在 {id} 為數字時執行...
    });

<a name="parameters-encoded-forward-slashes"></a>

#### 編碼斜線

Laravel 的路由元件能接受除了 `/` 外的所有字元出現在 Route 的參數值內。請使用 `where` 正規表示式條件來顯式允許 `/` 出現在預留位置中：

    Route::get('/search/{search}', function ($search) {
        return $search;
    })->where('search', '.*');

> **Warning** 只有最後一個 Route 片段才支援編碼斜線。

<a name="named-routes"></a>

## 命名的 Route

命名 Route 可以方便地未特定 Route 產生 URL 或重新導向。我們可以通過在 Route 定義後方串上 `name` 方法來為 Route 指定名稱：

    Route::get('/user/profile', function () {
        //
    })->name('profile');

也可以為 Controller 動作指定 Route 名稱：

    Route::get(
        '/user/profile',
        [UserProfileController::class, 'show']
    )->name('profile');

> **Warning** Route 名稱不可重複。

<a name="generating-urls-to-named-routes"></a>

#### 產生命名 Route 的 URL

給某個 Route 指定好名稱後，我們就可以使用 Laravel 的 `route` 與 `redirect` 輔助函式來在產生 URL 或重新導向時使用 Route 的名稱：

    // 產生 URL...
    $url = route('profile');
    
    // 產生重新導向...
    return redirect()->route('profile');
    
    return to_route('profile');

若命名 Route 有定義參數，則可以將這些參數作為第二個引數傳給 `route` 函式。傳入的參數會自動依照正確位置插入到產生的 URL 裡：

    Route::get('/user/{id}/profile', function ($id) {
        //
    })->name('profile');
    
    $url = route('profile', ['id' => 1]);

若該陣列中有傳入額外的參數，則這些額外的索引鍵 / 值配對會自動被插入到產生的 URL 中之查詢字串 (Query String) 上：

    Route::get('/user/{id}/profile', function ($id) {
        //
    })->name('profile');
    
    $url = route('profile', ['id' => 1, 'photos' => 'yes']);
    
    // /user/1/profile?photos=yes

> **Note** 有時候，我們可能會想為 URL 引數指定 Request 層級的預設值，例如目前使用的語系等。為此，可以使用 [`URL::defaults` 方法](/docs/{{version}}/urls#default-values)。

<a name="inspecting-the-current-route"></a>

#### 檢查目前 Route

若想判斷目前的 Request 是否有被路由到給定的命名 Route 上，可以使用 Route 實體上的 `named` 方法。舉例來說，我們可以從某個 Route 的
Middleware 上檢查目前的 Route 名稱：

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->route()->named('profile')) {
            //
        }
    
        return $next($request);
    }

<a name="route-groups"></a>

## Route 群組

使用 Route 群組，我們就可以在多個 Route 間共享相同的 Route 參數（如：使用相同的 Middleware），而不需要手動在個別 Route 上定義這些參數。

巢狀群組會嘗試智慧地將屬性「合併」到上層群組中。Middleware 與 `where` 條件會被合併，而命名 Route 的名稱則會被作為前置詞放到前面。Laravel 會自動在適當的時候往 URI 前方插入 Namespace 分隔符號或斜線。

<a name="route-group-middleware"></a>

### Middleware

若要將 [Middleware](/docs/{{version}}/middleware) 設定給群組中的所有 Route，可以在定義群組前使用 `middleware` 方法。Middleware 會以陣列中列出的順序執行：

    Route::middleware(['first', 'second'])->group(function () {
        Route::get('/', function () {
            // 使用 first 與 second Middleware...
        });
    
        Route::get('/user/profile', function () {
            // 使用 first 與 second Middleware...
        });
    });

<a name="route-group-controllers"></a>

### Controller

若有一組 Route 全部都使用了相同的 [Controller](/docs/{{version}}/controllers)，則我們可以使用 `controller` 方法來在路由群組中為所有的路由定義通用的 Controller。定義好之後，當定義路由時，就只需要提供要叫用的 Controller 方法即可：

    use App\Http\Controllers\OrderController;
    
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders/{id}', 'show');
        Route::post('/orders', 'store');
    });

<a name="route-group-subdomain-routing"></a>

### 子網域路由

Route 群組也可以用來處理子網域路由。我們可以像在設定 Route URI 一樣，在Route 參數內指派子網域。這樣一來我們就可以在 Route 或 Controller 內取得子網域的部分。可以通過在定義群組前呼叫 `domain` 來指定子網域：

    Route::domain('{account}.example.com')->group(function () {
        Route::get('user/{id}', function ($account, $id) {
            //
        });
    });

> **Warning** 為了確保子網域 Route 有效，請在註冊任何根網域 Route 前先註冊子網域 Route。這樣可以避免根網域的 Route 去複寫到子網域 Route 中有相同 URI 路徑的 Route。

<a name="route-group-prefixes"></a>

### Route 前置詞

可以使用 `prefix` 方法來為群組中的每個 Route 都加上給定 URI 的前置詞。舉例來說，我們可能會想把某個群組中的所有 Route URI 都加上 `admin` 前置詞：

    Route::prefix('admin')->group(function () {
        Route::get('/users', function () {
            // 配對到「/admin/users」URL
        });
    });

<a name="route-group-name-prefixes"></a>

### 命名 Route 的名稱前置詞

可以使用 `name` 方法來給群組中的每個 Route 名稱都加上給定字串的前置詞。舉例來說，我們可能會想給群組 Route 中的所有 Route 名稱都加上 `admin` 前置詞。給定字串會直接以指定時的樣子被加到 Route 名稱上。因此，請確保有在前置詞後方加上 `.` 字元：

    Route::name('admin.')->group(function () {
        Route::get('/users', function () {
            // Route 被指派名稱為「admin.users」...
        })->name('users');
    });

<a name="route-model-binding"></a>

## Route 的 Model 繫結

在將 Model ID 插入到 Route 或 Controller 動作時，我們常常會需要查詢資料庫來取得相應於該 ID 的 Model。Laravel 的 Route Model 繫結提供了能自動將 Model 實體插入到 Route 中的方便方法。舉例來說，我們可以插入符合給定 ID 的整個 `User` Model 實體，而不是插入使用者的 ID。

<a name="implicit-binding"></a>

### 隱式繫結

當 Route 或 Controller 動作中定義的變數名稱符合某個 Route 片段名稱，且該變數有型別提示時，Laravel 會自動解析 Eloquent Model。舉例來說：

    use App\Models\User;
    
    Route::get('/users/{user}', function (User $user) {
        return $user->email;
    });

由於 `$user` 變數有型別提示為 `App\Models\User` Eloquent Model，且該變數名稱符合 `{user}` URI 片段，因此 Laravel 會自動將 ID 符合 Request URI 中相應值的 Model 實體插入進去。若資料庫中找不到對應的 Model 實體，則會自動產生 404 HTTP Response。

當然，在使用 Controller 方法時也能使用隱式繫結。再強調一次，必須注意 `{user}` URI 片段要符合 Controller 中有 `App\Models\User` 型別提示的 `$user` 變數：

    use App\Http\Controllers\UserController;
    use App\Models\User;
    
    // Route 定義...
    Route::get('/users/{user}', [UserController::class, 'show']);
    
    // Controller 方法定義...
    public function show(User $user)
    {
        return view('user.profile', ['user' => $user]);
    }

<a name="implicit-soft-deleted-models"></a>

#### 軟刪除的 Model

一般來說，隱式型別細節不會去的被[軟刪除](/docs/{{version}}/eloquent#soft-deleting)的 Model。不過，我們也可以在 Route 的定義後方串上 `withTrashed` 方法來讓隱式型別綁定取得這些 Model：

    use App\Models\User;
    
    Route::get('/users/{user}', function (User $user) {
        return $user->email;
    })->withTrashed();

<a name="customizing-the-key"></a> <a name="customizing-the-default-key-name"></a>

#### 自訂索引鍵

有時候，我們可能會像讓 Eloquent 解析 `id` 以外的其他欄位。為此，可以在 Route 的參數定義中指定這個欄位：

    use App\Models\Post;
    
    Route::get('/posts/{post:slug}', function (Post $post) {
        return $post;
    });

若想讓 Model 繫結在給定 Model 類別上總是使用 `id` 以外的其他欄位，可以在 Eloquent Model 上複寫 `getRouteKeyName` 方法：

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

<a name="implicit-model-binding-scoping"></a>

#### 自訂索引鍵與作用範圍

當我們在單一 Route 定義中隱式繫結多個 Eloquent Model 時，我們可以限定第二個 Eloquent Model 一定要是前一個 Eloquent Model 的子 Model。舉例來說，假設有下列這樣通過 Slug 取得特定使用者的部落格貼文的 Route 定義：

    use App\Models\Post;
    use App\Models\User;
    
    Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
        return $post;
    });

當使用自訂鍵值的隱式繫結作為巢狀路由參數時，Laravel 會自動以慣例推測其上層 Model 上的關聯名稱來將限制巢狀 Model 的查詢範圍。在這個例子中，Laravel 會假設 `User` Model 有個名為 `posts` 的關聯 (即路由參數名稱的複數形)，該關聯將用於取得 `Post` Model。

若有需要的話，就算沒有提供自訂索引鍵，我們還是可以告訴 Laravel 要如何限定「子」繫結的限定。為此，我們可以在定義 Route 時叫用 `scopeBindings` 方法：

    use App\Models\Post;
    use App\Models\User;
    
    Route::get('/users/{user}/posts/{post}', function (User $user, Post $post) {
        return $post;
    })->scopeBindings();

或者，也可以讓整個 Route 定義群組使用限定範圍的繫結：

    Route::scopeBindings()->group(function () {
        Route::get('/users/{user}/posts/{post}', function (User $user, Post $post) {
            return $post;
        });
    });

類似地，也可以通過呼叫 `withoutScopedBindings` 方法來明顯讓 Laravel 不使用限定範圍的繫結：

    Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
        return $post;
    })->withoutScopedBindings();

<a name="customizing-missing-model-behavior"></a>

#### 自訂找不到 Model 的行為

通常來說，若找不到隱式繫結的 Model 時會產生一個 404 HTTP 回應。不過，可以在定義 Route 時呼叫 `missing` 方法來自訂這個行為。`missing` 方法接受一個閉包，該閉包會在找不到隱式繫結的 Model 時被叫用：

    use App\Http\Controllers\LocationsController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Redirect;
    
    Route::get('/locations/{location:slug}', [LocationsController::class, 'show'])
            ->name('locations.view')
            ->missing(function (Request $request) {
                return Redirect::route('locations.index');
            });

<a name="implicit-enum-binding"></a>

### 隱式 Enum 繫結

PHP 8.1 新增了對 [Enum](https://www.php.net/manual/en/language.enumerations.backed.php) 的支援。為了配合這個功能，Laravel 中提供了能在 Route 定義中對 [String-Backed Enum](https://www.php.net/manual/en/language.enumerations.backed.php) 進行型別提示的功能。加上型別提示後，只有當網址中的相應的 Route 片段為有效的 Enum 時，Laravel 才會叫用該 Route。若不是有效的 Enum 值，則會自動回傳 404 HTTP Response。舉例來說，假設有下列 Enum：

```php
<?php

namespace App\Enums;

enum Category: string
{
    case Fruits = 'fruits';
    case People = 'people';
}
```

我們可以定義一個只有當 `{category}` 路由片段為 `fruits` 或 `people` 時才會被叫用的路由。若為其他值，Laravel 會回傳 HTTP 404 Response：

```php
use App\Enums\Category;
use Illuminate\Support\Facades\Route;

Route::get('/categories/{category}', function (Category $category) {
    return $category->value;
});
```

<a name="explicit-binding"></a>

### 顯式繫結

Model 繫結不一定要使用 Laravel 的隱式的，隱式繫結是基於慣例的 Model 解析。我們也可以顯式定義 Route 參數要怎麼對應到 Model。若要註冊顯式細節，請使用 Router 的 `model` 方法來為給定參數指定類別。應在 `RouteServiceProvider` 類別中 `boot` 方法內的開頭定義顯式 Model 繫結：

    use App\Models\User;
    use Illuminate\Support\Facades\Route;
    
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::model('user', User::class);
    
        // ...
    }

接著，請定義含有 `{user}` 參數的 Route：

    use App\Models\User;
    
    Route::get('/users/{user}', function (User $user) {
        //
    });

我們已經將所有 `{user}` 參數繫結到 `App\Models\User` Model 上了。`User` Model 的實體會被插入到這個 Route 中。因此，舉例來說，對 `users/1` 的 Request 將會插入一個資料庫中 ID 為 `1` 的 `User` 實體。

若資料庫中找不到相符合的 Model 實體，則會自動產生 404 HTTP Response。

<a name="customizing-the-resolution-logic"></a>

#### 自訂解析邏輯

若想定義你自己的 Model 繫結解析邏輯，則可以使用 `Route::bind` 方法。我們可以傳入一個閉包給 `bind` 方法，用來接受 URI 片段中的值，並回應要插入到 Route 中的類別實體。同樣的，這個自訂邏輯應放在專案的 `RouteServiceProvider` 中 `boot` 方法內：

    use App\Models\User;
    use Illuminate\Support\Facades\Route;
    
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::bind('user', function ($value) {
            return User::where('name', $value)->firstOrFail();
        });
    
        // ...
    }

或者，我們也可以在 Eloquent Model 上複寫 `resolveRouteBinding` 方法。這個方法會接收 URI 片段中的值，並應回傳要插入到 Route 中的類別實體：

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('name', $value)->firstOrFail();
    }

如有 Route 是使用[限定範圍的隱式細節](#implicit-model-binding-scoping)，則在解析上層 Model 的子繫結時會使用 `resolveChildRouteBinding` 方法：

    /**
     * Retrieve the child model for a bound value.
     *
     * @param  string  $childType
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return parent::resolveChildRouteBinding($childType, $value, $field);
    }

<a name="fallback-routes"></a>

## 遞補的 Route

使用 `Route::fallback` 方法，就可以定義當沒有其他 Route 符合連入 Request 時要執行的 Route。一般來說，專案中的例外處理常式會自動幫未處理的 Request 會轉譯出「404」頁面。不過，因為我們通常會在 `routes/web.php` 檔案中定義 `fallback` Route，因此在 `web` Middleware 群組中的所有 Middleware 也會被套用到該 Route 中。有需要的話也可以為這個 Route 定義額外的 Middleware：

    Route::fallback(function () {
        //
    });

> **Warning** 遞補的 Route 應該要保持為專案中最後一個註冊的 Route。

<a name="rate-limiting"></a>

## 頻率限制

<a name="defining-rate-limiters"></a>

### 定義 Rate Limiter (頻率限制程式)

Laravel 中包含了強大且可客製化的頻率限制服務，可以用來為給定的 Route 或 Route 群組限制流量。要開始使用頻率限制，我們需要先依照專案需求定義 Rate Limiter (頻率限制程式) 的設定。一般來說，應在 `App\Providers\RouteServiceProvider` 類別的 `configureRateLimiting` 方法中定義。在 `configureRateLimiting` 方法中，已預先包含了一個會套用到專案 `routes/api.php` 中 Route 的頻率限制：

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Configure the rate limiters for the application.
 */
protected function configureRateLimiting(): void
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
}
```

使用 `RateLimiter` Facade 的 `for` 方法來定義 Rate Limiter。`for` 方法接受 Rate Limiter 的名稱以及一個閉包。該閉包應回傳用來套用到指派了這個 Rate Limiter 上的 Route 所需要的頻率限制設定。頻率限制的設定使用 `Illuminate\Cache\RateLimiting\Limit` 類別的實體。這個實體中包含了實用的「建構程式 (Builder)」，可讓你快速定義限制。Rate Limiter 的名稱可以為任意字串：

    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\RateLimiter;
    
    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(1000);
        });
    }

若連入的 Request 超過了指定的頻率限制，Laravel 會自動回傳一個 429 HTTP 狀態碼。若想自訂頻率限制回傳的 Response，可使用 `response` 方法：

    RateLimiter::for('global', function (Request $request) {
        return Limit::perMinute(1000)->response(function (Request $request, array $headers) {
            return response('Custom response...', 429, $headers);
        });
    });

由於頻率限制程式的回呼會接收連入 HTTP Request 實體，因此我們可以依據連入 Request 或登入使用者來動態調整適當的頻率限制：

    RateLimiter::for('uploads', function (Request $request) {
        return $request->user()->vipCustomer()
                    ? Limit::none()
                    : Limit::perMinute(100);
    });

<a name="segmenting-rate-limits"></a>

#### 區塊化的頻率限制

有時候，我們可能會像依照某個值來做分區的頻率限制。舉例來說，我們可能會想讓某個使用者在每個 IP 位址上每分鐘只能存取某個 Route 100 次。為此，可以在設定頻率限制時使用 `by` 方法：

    RateLimiter::for('uploads', function (Request $request) {
        return $request->user()->vipCustomer()
                    ? Limit::none()
                    : Limit::perMinute(100)->by($request->ip());
    });

我們來看看另一個使用這個功能的例子。我們可以像這樣限制某個 Route 對已登入使用者的限制時 100 次/分鐘，而未登入使用者則是 10 次/分鐘：

    RateLimiter::for('uploads', function (Request $request) {
        return $request->user()
                    ? Limit::perMinute(100)->by($request->user()->id)
                    : Limit::perMinute(10)->by($request->ip());
    });

<a name="multiple-rate-limits"></a>

#### 多個頻率限制

當然，對於某個 Rate Limiter 的設定，我們也可以回傳一組包含頻率限制的陣列。每個頻率限制會依據陣列中的順序被套用在 Route 上：

    RateLimiter::for('login', function (Request $request) {
        return [
            Limit::perMinute(500),
            Limit::perMinute(3)->by($request->input('email')),
        ];
    });

<a name="attaching-rate-limiters-to-routes"></a>

### 將 Rate Limiter 附加到 Route 上

可以使用 `throttle` [Middleware](/docs/{{version}}/middleware) 來將 Rate Limiter 附加到 Route 或 Route 群組上。這個 Throttle Middleware 接受欲指派給 Route 的 Rate Limiter 名稱：

    Route::middleware(['throttle:uploads'])->group(function () {
        Route::post('/audio', function () {
            //
        });
    
        Route::post('/video', function () {
            //
        });
    });

<a name="throttling-with-redis"></a>

#### 使用 Redis 來做頻率限制

一般來說，`throttle` Middleware 被映射到 `Illuminate\Routing\Middleware\ThrottleRequests` 類別。這個映射定義在程式的 HTTP Kernel (`App\Http\Kernel`) 中。不過，如果你使用 Redis 來作為快取的 Driver，則可以將這個映射改為使用 `Illuminate\Routing\Middleware\ThrottleRequestsWithRedis` 類別。這個類別能更有效率地使用 Redis 來管理頻率限制：

    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,

<a name="form-method-spoofing"></a>

## 表單方法的變更

HTML 表單不支援 `PUT`, `PATCH`, 與 `DELETE` 動作，因此，當我們在定義會由 HTML 表單呼叫的 `PUT`, `PATCH`, 或 `DELETE` Route 時，我們需要在表單內加上一個隱藏的 `_method` 欄位。包含在 `_method` 欄位裡的值會被當作 HTTP Request 方法使用：

    <form action="/example" method="POST">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>

為了方便起見，也可以使用 `@method` [Blade 指示詞](/docs/{{version}}/blade)來產生 `_method` 輸入欄位：

    <form action="/example" method="POST">
        @method('PUT')
        @csrf
    </form>

<a name="accessing-the-current-route"></a>

## 存取目前的 Route

可以使用 `Route` Facade 上的 `current`, `currentRouteName` 與 `currentRouteAction` 方法來存取有關處理本次連入 Request 的 Route 資訊：

    use Illuminate\Support\Facades\Route;
    
    $route = Route::current(); // Illuminate\Routing\Route
    $name = Route::currentRouteName(); // string
    $action = Route::currentRouteAction(); // string

請參考 [Route Facade 底層的類別](https://laravel.com/api/{{version}}/Illuminate/Routing/Router.html)與 [Route 實體](https://laravel.com/api/{{version}}/Illuminate/Routing/Route.html)的 API 說明文件以瞭解 Router 與 Route 類別提供的全部方法。

<a name="cors"></a>

## 跨原始來源資源共用 (CORS, Cross-Origin Resource Sharing)

Laravel 會自動依照你設定的值來回應 CORS 的 `OPTIONS` HTTP Request。可以在專案的 `config/cors.php` 設定檔中設定所有的 CORS 設定。 `HandleCors` [Middleware](/docs/{{version}}/middleware) 會自動處理 `OPTIONS` Request，該 Middleware 預設包含在全域的 Middleware Stack 中。全域的 Middleware Stack 存在 HTTP Kernel (`App\Http\Kernel`) 中。

> **Note** 更多有關 CORS 與 CORS 標頭的資訊，請參考 [MDN 網頁說明文件上的 CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#The_HTTP_response_headers)。

<a name="route-caching"></a>

## Route 的快取

在將專案部署到正式環境時，應使用 Laravel 的 Route 快取功能。使用 Route 快取就能大大地降低註冊所有 Route 所需的時間。要產生 Route 快取，請執行 `route:cache` Artisan 指令：

```shell
php artisan route:cache
```

執行這個指令後，每個 Request 都會自動載入快取的 Route 檔。請記得，當新增新 Route 後，必須重新產生 Route 快取。因此，應在進行專案部署的時候才執行 `route:cache` 指令。

可以使用 `route:clear` 指令來清除 Route 快取：

```shell
php artisan route:clear
```
