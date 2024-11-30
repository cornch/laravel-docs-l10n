---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/37/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 61.69
---

# 控制器 - Controller

- [簡介](#introduction)
- [撰寫 Controller](#writing-controllers)
  - [「Controller」基礎](#basic-controllers)
  - [單一動作的 Controller](#single-action-controllers)
  
- [Controller Middleware](#controller-middleware)
- [Resource Controller](#resource-controllers)
  - [部分的 Resource Route](#restful-partial-resource-routes)
  - [巢狀 Resource](#restful-nested-resources)
  - [命名 Resource Route](#restful-naming-resource-routes)
  - [命名 Resource Route 的參數](#restful-naming-resource-route-parameters)
  - [限制範圍的 Resource Route](#restful-scoping-resource-routes)
  - [本土化 Resource URI](#restful-localizing-resource-uris)
  - [補充 Resource Controller](#restful-supplementing-resource-controllers)
  - [單例的 Resource Controller](#singleton-resource-controllers)
  
- [Dependency Injection and Controllers](#dependency-injection-and-controllers)

<a name="introduction"></a>

## 簡介

比起在路由檔案中使用閉包來定義所有的請求處理邏輯，你可能會想使用「Controller」類別來管理這個行為。Controller 可以將相關的請求處理邏輯放在單一類別內。舉例來說，`UserController` 類別可以處理所有有關使用者的連入請求，包含顯示、建立、更新與刪除使用者。預設情況下，Controller 存放在 `app/Http/Controllers` 目錄下。

<a name="writing-controllers"></a>

## 撰寫 Controller

<a name="basic-controllers"></a>

### 基礎 Controller

若要快速產生新的 Controller，可以執行 `make:controller` Artisan 指令。預設情況下，專案中所有的 Controller 都保存在 `app/Http/Controllers` 目錄：

```shell
php artisan make:controller UserController
```
來看一個基本的 Controller 例子。一個 Controller 可以包含任意數量的 Public 方法，這些 Public 方法會用來回應連入的 HTTP Request：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Models\User;
    use Illuminate\View\View;
    
    class UserController extends Controller
    {
        /**
         * Show the profile for a given user.
         */
        public function show(string $id): View
        {
            return view('user.profile', [
                'user' => User::findOrFail($id)
            ]);
        }
    }
寫好 Controller 類別與方法後，就可以像這樣定義一個 Route 至該 Controller 方法：

    use App\Http\Controllers\UserController;
    
    Route::get('/user/{id}', [UserController::class, 'show']);
當有連入請求符合這個路由 URI 時，將叫用 `App\Http\Controllers\UserController` 類別的 `show` 方法，且 route 參數會被傳入這個方法內。

> [!NOTE]  
> Controllers are not **required** to extend a base class. However, it is sometimes convenient to extend a base controller class that contains methods that should be shared across all of your controllers.

<a name="single-action-controllers"></a>

### 單一動作的 Controller

若某個 Controller 動作特別複雜，則可以將這個動作放到獨立的 Controller 類別。為此，可在該 Controller 內定義一個單一的 `__invoke` 方法：

    <?php
    
    namespace App\Http\Controllers;
    
    class ProvisionServer extends Controller
    {
        /**
         * Provision a new web server.
         */
        public function __invoke()
        {
            // ...
        }
    }
當為單一動作的 Controller 註冊路由時，不需要指定 Controller 方法。只需要傳入該 Controller 的名稱給 Router 即可：

    use App\Http\Controllers\ProvisionServer;
    
    Route::post('/server', ProvisionServer::class);
可以通過 `make:controller` Artisan 指令的 `--invokable` 選項來建立可被叫用的 Controller：

```shell
php artisan make:controller ProvisionServer --invokable
```
> [!NOTE]  
> Controller 的 Stub 可通過[發佈 Stub](/docs/{{version}}/artisan#stub-customization) 來自定。

<a name="controller-middleware"></a>

## Controller Middleware

可在路由檔案中指派 [Middleware](/docs/{{version}}/middleware) 給 Controller 的路由：

    Route::get('profile', [UserController::class, 'show'])->middleware('auth');
Or, you may find it convenient to specify middleware within your controller class. To do so, your controller should implement the `HasMiddleware` interface, which dictates that the controller should have a static `middleware` method. From this method, you may return an array of middleware that should be applied to the controller's actions:

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Routing\Controllers\HasMiddleware;
    use Illuminate\Routing\Controllers\Middleware;
    
    class UserController extends Controller implements HasMiddleware
    {
        /**
         * Get the middleware that should be assigned to the controller.
         */
        public static function middleware(): array
        {
            return [
                'auth',
                new Middleware('log', only: ['index']),
                new Middleware('subscribed', except: ['store']),
            ];
        }
    
        // ...
    }
You may also define controller middleware as closures, which provides a convenient way to define an inline middleware without writing an entire middleware class:

    use Closure;
    use Illuminate\Http\Request;
    
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            function (Request $request, Closure $next) {
                return $next($request);
            },
        ];
    }
<a name="resource-controllers"></a>

## 資源 Controller

若將專案內的各個 Eloquent Model 當作是「資源」，則我們通常會在專案中對各個資源進行同一系列的動作。舉例來說，假設專案中有個 `Photo` Model 與 `Movie` Model。則使用者應該可以建立 (Create)、檢視 (Read)、更新 (Update)、或刪除 (Delete) 這些資源。

由於這個常見的使用情況，Laravel 資源路由可將常見的建立 (Create)、讀取 (Read)、更新 (Update) 與刪除 (Delete) (即「CRUD」) 通過單行程式碼來指派路由。要開始建立資源路由，可使用 `make:controller` Artisan 指令的 `--resource` 選項來快速建立處理這些動作的 Controller：

```shell
php artisan make:controller PhotoController --resource
```
這個指令會在 `app/Http/Controllers/PhotoController.php` 下產生一個 Controller。該 Controller 會包含用於各個可用資源操作的方法。接著，可以註冊一個指向該 Controller 的資源路由：

    use App\Http\Controllers\PhotoController;
    
    Route::resource('photos', PhotoController::class);
這一個路由定義會建立多個路由來處理對該資源的數種動作。剛才產生的 Controller 已經預先有了用於這幾個動作的方法了。請記得，你可以隨時通過執行 `route:list` Artisan 指令來快速檢視專案的路由。

也可以通過傳入陣列給 `resources` 方法來一次註冊多個資源 Controller：

    Route::resources([
        'photos' => PhotoController::class,
        'posts' => PostController::class,
    ]);
<a name="actions-handled-by-resource-controllers"></a>

#### Actions Handled by Resource Controllers

| 動詞 | URI | 動作 | Route 名稱 |
| --- | --- | --- | --- |
| GET | `/photos` | index | photos.index |
| GET | `/photos/create` | create | photos.create |
| POST | `/photos` | store | photos.store |
| GET | `/photos/{photo}` | show | photos.show |
| GET | `/photos/{photo}/edit` | edit | photos.edit |
| PUT/PATCH | `/photos/{photo}` | update | photos.update |
| DELETE | `/photos/{photo}` | destroy | photos.destroy |

<a name="customizing-missing-model-behavior"></a>

#### 自訂找不到 Model 的行為

通常來說，若找不到隱式繫結的資源 Model 時會產生一個 404 HTTP 回應。不過，可以在定義資源路由時通過呼叫 `missing` 方法來自訂這個行為。`missing` 方法接受一個閉包，該閉包會在任何資源的路由上找不到隱式繫結的 Model 時被叫用：

    use App\Http\Controllers\PhotoController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Redirect;
    
    Route::resource('photos', PhotoController::class)
            ->missing(function (Request $request) {
                return Redirect::route('photos.index');
            });
<a name="soft-deleted-models"></a>

#### 軟刪除的 Model

一般來說，隱式型別繫結不會取得被[軟刪除](/docs/{{version}}/eloquent#soft-deleting)的 Model，而會回傳 404 HTTP Response。不過，我們也可以在定義 Route 時呼叫 `withTrashed` 方法來讓 Laravel 取得這些已刪除的 Model：

    use App\Http\Controllers\PhotoController;
    
    Route::resource('photos', PhotoController::class)->withTrashed();
在呼叫 `withTrashed` 時若不提供屬性，則可讓 `show`、`edit`、與 `update` Resource Route 存取軟刪除的 Model。可以傳入一組陣列給 `withTrashed` 方法來指定只使用這些 Route 中的一部分：

    Route::resource('photos', PhotoController::class)->withTrashed(['show']);
<a name="specifying-the-resource-model"></a>

#### Specifying the Resource Model

若使用了[路由 Model 繫結](/docs/{{version}}/routing#route-model-binding)，且想型別提示資源 Controller 的方法，可以在產生 Controller 時使用 `--model` 選項：

```shell
php artisan make:controller PhotoController --model=Photo --resource
```
<a name="generating-form-requests"></a>

#### 產生 Form Request

可以在產生資源 Controller 時提供 `--requests` 選項來告訴 Artisan 要產生用於 Controller 中 storage 與 update 方法的 [Form Request 類別](/docs/{{version}}/validation#form-request-validation)：

```shell
php artisan make:controller PhotoController --model=Photo --resource --requests
```
<a name="restful-partial-resource-routes"></a>

### 部分資源路由

宣告資源路由時，比起宣告全部的預設動作，也可以只宣告該 Controller 要處理的一部分動作：

    use App\Http\Controllers\PhotoController;
    
    Route::resource('photos', PhotoController::class)->only([
        'index', 'show'
    ]);
    
    Route::resource('photos', PhotoController::class)->except([
        'create', 'store', 'update', 'destroy'
    ]);
<a name="api-resource-routes"></a>

#### API 資源路由

在會被 API 使用的資源路由時，我們通常會想排除用來顯示 HTML 樣板的路由，如 `create` 與 `edit`。為了方便起見，可以使用 `apiResource` 方法來自動排除這兩個路由：

    use App\Http\Controllers\PhotoController;
    
    Route::apiResource('photos', PhotoController::class);
也可以通過傳入陣列給 `apiResources` 方法來一次註冊多個 API 資源 Controller：

    use App\Http\Controllers\PhotoController;
    use App\Http\Controllers\PostController;
    
    Route::apiResources([
        'photos' => PhotoController::class,
        'posts' => PostController::class,
    ]);
若要快速建立不包含 `create` 或 `edit` 方法的 API 資源路由，請在執行 `make:contorller` 指令時使用 `--api` 開關：

```shell
php artisan make:controller PhotoController --api
```
<a name="restful-nested-resources"></a>

### 巢狀資源

有時候我們會需要為巢狀資源定義路由。舉例來說，某個照片資源可能會有多個附加到該照片的留言。要巢狀嵌套資源 Controller，我們可以在路由定義上使用「點」標記法：

    use App\Http\Controllers\PhotoCommentController;
    
    Route::resource('photos.comments', PhotoCommentController::class);
該路由會註冊一個巢狀資源，可使用像這樣的 URI 來存取：

    /photos/{photo}/comments/{comment}
<a name="scoping-nested-resources"></a>

#### 限定範圍的巢狀資源

Laravel 的[隱式 Model 繫結](/docs/{{version}}/routing#implicit-model-binding-scoping)功能可自動限制巢狀繫結的範圍，讓要被解析的子 Model 可被限制在屬於其上層 Model。只要在定義巢狀資源時使用 `scoped` 方法，就可以開啟自動範圍限制，並告訴 Laravel 應使用子資源的哪個欄位來取得。更多有關此的資訊，請參考[限制資源路由的範圍](#restful-scoping-resource-routes)的說明文件。

<a name="shallow-nesting"></a>

#### 淺層巢狀

通常，在 URI 中並不需要同時擁有上層 Model 與子 Model 的 ID，因為子 ID 已經是唯一的識別子了。若要在使用唯一如自動遞增的主鍵這樣的識別子來在 URI 區段中識別 Model，可使用「淺層巢狀 (Shallow Nesting)」：

    use App\Http\Controllers\CommentController;
    
    Route::resource('photos.comments', CommentController::class)->shallow();
這個路由定義會定義下列路由：

| 動詞 | URI | 動作 | Route 名稱 |
| --- | --- | --- | --- |
| GET | `/photos/{photo}/comments` | index | photos.comments.index |
| GET | `/photos/{photo}/comments/create` | create | photos.comments.create |
| POST | `/photos/{photo}/comments` | store | photos.comments.store |
| GET | `/comments/{comment}` | show | comments.show |
| GET | `/comments/{comment}/edit` | edit | comments.edit |
| PUT/PATCH | `/comments/{comment}` | update | comments.update |
| DELETE | `/comments/{comment}` | destroy | comments.destroy |

<a name="restful-naming-resource-routes"></a>

### 命名資源路由

預設情況下，所有的資源 Controller 動作都有對應的路由名稱。不過，可以通過將包含欲使用的路由名稱的陣列傳入 `names` 來複寫這些名稱：

    use App\Http\Controllers\PhotoController;
    
    Route::resource('photos', PhotoController::class)->names([
        'create' => 'photos.build'
    ]);
<a name="restful-naming-resource-route-parameters"></a>

### 命名資源路由參數

預設情況下，`Route::resource` 會為依照「單數化 (Singularized)」的資源名稱來為資源路由建立路由參數。可以輕鬆地通過 `parameters` 方法來對個別資源複寫資源名稱。傳入 `parameters` 的陣列應為一個包含資源名稱與參數名稱的關聯式陣列：

    use App\Http\Controllers\AdminUserController;
    
    Route::resource('users', AdminUserController::class)->parameters([
        'users' => 'admin_user'
    ]);
上述範例會為資源的 `show` 路由產生下列 URI：

    /users/{admin_user}
<a name="restful-scoping-resource-routes"></a>

### 限制資源路由的範圍

Laravel 的[限定範圍的隱式 Model 繫結](/docs/{{version}}/routing#implicit-model-binding-scoping)功能可自動限制巢狀繫結的範圍，讓要被解析的子 Model 可被限制在屬於其上層 Model。只要在定義巢狀資源時使用 `scoped` 方法，就可以開啟自動範圍限制，並告訴 Laravel 應使用子資源的哪個欄位來取得：

    use App\Http\Controllers\PhotoCommentController;
    
    Route::resource('photos.comments', PhotoCommentController::class)->scoped([
        'comment' => 'slug',
    ]);
該路由會註冊一個限定範圍的巢狀資源，可使用像這樣的 URI 來存取：

    /photos/{photo}/comments/{comment:slug}
當使用自訂鍵值的隱式繫結作為巢狀路由參數時，Laravel 會自動以慣例推測其上層 Model 上的關聯名稱來將限制巢狀 Model 的查詢範圍。在這個例子中，Laravel 會假設 `Photo` Model 有個名為 `comments` 的關聯 (即路由參數名稱的複數形)，該關聯將用於取得 `Comment` Model。

<a name="restful-localizing-resource-uris"></a>

### 本地化資源 URI

By default, `Route::resource` will create resource URIs using English verbs and plural rules. If you need to localize the `create` and `edit` action verbs, you may use the `Route::resourceVerbs` method. This may be done at the beginning of the `boot` method within your application's `App\Providers\AppServiceProvider`:

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::resourceVerbs([
            'create' => 'crear',
            'edit' => 'editar',
        ]);
    }
Laravel 的複數化程式 (Pluralizer) 可以[按照需求設定支援不同的語言](/docs/{{version}}/localization#pluralization-language)。自訂好動詞與複數化語言後，如  `Route::resource('publicacion', PublicacionController::class)` 這樣的 Resource Route 就會產生下列 URI：

    /publicacion/crear
    
    /publicacion/{publicaciones}/editar
<a name="restful-supplementing-resource-controllers"></a>

### 補充資源 Controller

若有需要為某個資源 Controller 增加除了預設資源路由以外的額外路由，則應在呼叫 `Route::resource` 方法前先定義這些路由。否則，又 `resource` 方法定義的路由可能會不可預期地取代所擴充的路由：

    use App\Http\Controller\PhotoController;
    
    Route::get('/photos/popular', [PhotoController::class, 'popular']);
    Route::resource('photos', PhotoController::class);
> [!NOTE]  
> 請記得要保持 Controller 的功能專一。若發現常常需要使用除了一般資源動作以外的方法，請考慮將 Controller 拆分成兩個、更小的 Controller。

<a name="singleton-resource-controllers"></a>

### 單例 Resource Controller

有時候，專案中可能會有只有一個實體的資源。舉例來說，我們可以編輯或更新使用者的「個人檔案 (Profile)」，而每個使用者通常都不會有超過一個的「個人檔案」。類似的，一張圖片可能也只有一個「縮圖」。這些資源就叫做「單例資源」，意思是，這些資源只會有一個實體。在這些情況下，我們可以註冊一個「單例」的 Resource Controller：

```php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::singleton('profile', ProfileController::class);
```
上面的單例 Resource 定義會註冊下列 Route。就像這樣，單例 Resource 不會註冊「建立」Route，而該程式碼註冊的 Route 也不接受識別子 (Identifier)，因為這些資源只會有一個實體：

| 動詞 | URI | 動作 | Route 名稱 |
| --- | --- | --- | --- |
| GET | `/profile` | show | profile.show |
| GET | `/profile/edit` | edit | profile.edit |
| PUT/PATCH | `/profile` | update | profile.update |

單例資源也可以被巢狀包含在標準的資源中：

```php
Route::singleton('photos.thumbnail', ThumbnailController::class);
```
在此範例中，`photos` 資源會擁有所有的[標準 Resource Route](#actions-handled-by-resource-controller)。不過，`thumbnail` 資源會是一個單例資源，並擁有下列 Route：

| 動詞 | URI | 動作 | Route 名稱 |
| --- | --- | --- | --- |
| GET | `/photos/{photo}/thumbnail` | show | photos.thumbnail.show |
| GET | `/photos/{photo}/thumbnail/edit` | edit | photos.thumbnail.edit |
| PUT/PATCH | `/photos/{photo}/thumbnail` | update | photos.thumbnail.update |

<a name="creatable-singleton-resources"></a>

#### 可建立的單例資源

有時候，我們會需要為某個單例資源定義建立與保存的 Route。這時，我們可以在註冊單例資源 Route 時呼叫 `creatable` 方法：

```php
Route::singleton('photos.thumbnail', ThumbnailController::class)->creatable();
```
在此範例中，會註冊下列 Route。如下所示，在可被建立的單例資源中，也會一併建立 `DELETE` Route：

| 動詞 | URI | 動作 | Route 名稱 |
| --- | --- | --- | --- |
| GET | `/photos/{photo}/thumbnail/create` | create | photos.thumbnail.create |
| POST | `/photos/{photo}/thumbnail` | store | photos.thumbnail.store |
| GET | `/photos/{photo}/thumbnail` | show | photos.thumbnail.show |
| GET | `/photos/{photo}/thumbnail/edit` | edit | photos.thumbnail.edit |
| PUT/PATCH | `/photos/{photo}/thumbnail` | update | photos.thumbnail.update |
| DELETE | `/photos/{photo}/thumbnail` | destroy | photos.thumbnail.destroy |

若想讓 Laravel 為單例資源註冊 `DELETE` Route，但又不想註冊建立與保存的 Route，可使用 `destroyable` 方法：

```php
Route::singleton(...)->destroyable();
```
<a name="api-singleton-resources"></a>

#### API 的單例資源

`apiSingleton` 方法可用來註冊通過 API 操作的單例資源。因此，這些資源不需要 `create` 與 `edit` Route：

```php
Route::apiSingleton('profile', ProfileController::class);
```
檔案，API 的單例資源也可以被設為 `creatable`，也就是可為該資源註冊 `store` 與 `destroy` Route：

```php
Route::apiSingleton('photos.thumbnail', ProfileController::class)->creatable();
```
<a name="dependency-injection-and-controllers"></a>

## Dependency Injection and Controllers

<a name="constructor-injection"></a>

#### 建構函式注入

Laravel 的 [Service Container](/docs/{{version}}/container) 會被用來解析所有的 Laravel Controller。因此，可以在 Controller 的建構函式內型別提示所有 Controller 所需要的依賴。所宣告的依賴會被自動解析並插入到 Controller 實體上：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Repositories\UserRepository;
    
    class UserController extends Controller
    {
        /**
         * Create a new controller instance.
         */
        public function __construct(
            protected UserRepository $users,
        ) {}
    }
<a name="method-injection"></a>

#### 方法注入

除了注入到建構函式內，也可以在 Controller 的方法上型別提示依賴。常見的使用情況是將 `Illuminate\Http\Request` 實體注入到 Controller 方法內：

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
            $name = $request->name;
    
            // Store the user...
    
            return redirect('/users');
        }
    }
若 Controller 方法也預期會從路由參數取得輸入，則請將路由引數放在其他依賴之後。舉例來說，若路由是像這樣定義：

    use App\Http\Controllers\UserController;
    
    Route::put('/user/{id}', [UserController::class, 'update']);
還是可以像這樣定義 Controller 方法來型別提示 `Illuminate\Http\Request` 並取得 `id` 參數：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    
    class UserController extends Controller
    {
        /**
         * Update the given user.
         */
        public function update(Request $request, string $id): RedirectResponse
        {
            // Update the user...
    
            return redirect('/users');
        }
    }