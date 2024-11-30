---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/13/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 49.24
---

# 授權

- [簡介](#introduction)
- [Gate](#gates)
  - [撰寫 Gate](#writing-gates)
  - [授權動作](#authorizing-actions-via-gates)
  - [Gate Response](#gate-responses)
  - [攔截 Gate 檢查](#intercepting-gate-checks)
  - [內嵌授權](#inline-authorization)
  
- [建立 Policy](#creating-policies)
  - [產生 Policy](#generating-policies)
  - [註冊 Policy](#registering-policies)
  
- [撰寫 Policy](#writing-policies)
  - [Policy 方法](#policy-methods)
  - [Policy Response](#policy-responses)
  - [不使用 Model 的方法](#methods-without-models)
  - [訪客使用者](#guest-users)
  - [Policy 篩選器](#policy-filters)
  
- [通過 Policy 來授權動作](#authorizing-actions-using-policies)
  - [通過使用者 Model](#via-the-user-model)
  - [通過 Controller 的輔助函式](#via-controller-helpers)
  - [通過 Middleware](#via-middleware)
  - [通過 Blade 樣板](#via-blade-templates)
  - [提供額外的上下文](#supplying-additional-context)
  

<a name="introduction"></a>

## 簡介

除了提供內建的[認證](/docs/{{version}}/authentication)服務，Laravel 也提供了一種能依給定資源來授權使用者的簡單功能。舉例來說，雖然使用者已登入，但這個使用者可能未被授權可更新或刪除網站所管理的特定的 Eloquent Model 或資料庫記錄。Laravel 的授權功能提供了一種簡單且有條理的方法來管理這些種類的授權。

Laravel 提供了兩種主要方法來授權動作：[Gate](#gates) 與 [Policy](#creating-policies)。可以把 Gate 與 Policy 想成是路由與 Controller。Gate 提供了一種簡單、基於閉包的方法來進行授權；而 Policy 就像 Controller 一樣，可以將授權邏輯依照特定的 Model 或資源來進行分組。在本說明文件中，我們會先來探討 Gate，然後再來看看 Policy。

在製作應用程式時，不需要只使用 Gate 或只使用 Policy。大多數應用程式都在某種程度上組合使用 Gate 與 Policy，這樣完全沒問題！Gate 最適合用來處理與 Model 或資源沒關係的操作，如檢視管理員縱覽頁。相較之下，Policy 則應使用於想授權對特定 Model 或 Resource 的操作時

<a name="gates"></a>

## Gate

<a name="writing-gates"></a>

### 撰寫 Gate

> [!WARNING]  
> Gate 是學習基礎 Laravel 授權功能的最好的方法。但是，在製作大型 Laravel 應用程式時，應考慮通過 [Policy](#creating-policies) 來整理各個授權規則。

Gate 是簡單的閉包，用來判斷使用者是否已被授權執行特定的動作。通常來說，Gate 會在 `App\Providers\AuthServiceProvider` 類別中的 `boot` 方法內通過 `Gate` Facade 來定義。Gate 會收到一個使用者實體作為其第一個引數，並且可能還會接受到額外的引數，如相關的 Eloquent Model。

在此範例中，我們會定義一個用來判斷使用者能否更新給定 `App\Models\Post` Model 的 Gate。這個 Gate 會通過比對使用者的 `id` 與建立該貼文的 `user_id` 來進行判斷：

    use App\Models\Post;
    use App\Models\User;
    use Illuminate\Support\Facades\Gate;
    
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    
        Gate::define('update-post', function (User $user, Post $post) {
            return $user->id === $post->user_id;
        });
    }
就像 Controller 一樣，Gate 也可以通過類別回呼陣列來定義：

    use App\Policies\PostPolicy;
    use Illuminate\Support\Facades\Gate;
    
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    
        Gate::define('update-post', [PostPolicy::class, 'update']);
    }
<a name="authorizing-actions-via-gates"></a>

### 授權動作

若要通過 Gate 來授權某個動作，可以使用 `Gate` Facade 提供的 `allows` 或 `denies` 方法。請注意，不需要將目前已登入的使用者傳給這幾個方法。Laravel 會自動處理好將使用者傳給 Gate 閉包。通常，我們會在 Controller 執行需要授權的特定動作前呼叫這些 Gate 授權方法：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Models\Post;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Gate;
    
    class PostController extends Controller
    {
        /**
         * Update the given post.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \App\Models\Post  $post
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request, Post $post)
        {
            if (! Gate::allows('update-post', $post)) {
                abort(403);
            }
    
            // Update the post...
        }
    }
如果想判斷除了目前登入使用者以外其他的使用者能否執行特定動作，可以使用 `Gate` Facade 上的 `forUser` 方法：

    if (Gate::forUser($user)->allows('update-post', $post)) {
        // The user can update the post...
    }
    
    if (Gate::forUser($user)->denies('update-post', $post)) {
        // The user can't update the post...
    }
可以通過 `any` 或 `none` 方法來在任何時候授權多個動作：

    if (Gate::any(['update-post', 'delete-post'], $post)) {
        // The user can update or delete the post...
    }
    
    if (Gate::none(['update-post', 'delete-post'], $post)) {
        // The user can't update or delete the post...
    }
<a name="authorizing-or-throwing-exceptions"></a>

#### 授權或擲回例外

如果想在使用者不允許執行給定動作時自動擲回 `Illuminate\Auth\Access\AuthorizationException`，可以使用 `Gate` Facade 的 `authorize` 方法。`AuthorizationException` 的實體會自動被 Laravel 的例外處理常式轉換為 403 HTTP 回應：

    Gate::authorize('update-post', $post);
    
    // The action is authorized...
<a name="gates-supplying-additional-context"></a>

#### 提供額外的上下文

用於授權權限的 Gate 方法 (`allows`, `denies`, `check`, `any`, `none`, `authorize`, `can`, `cannot`) 與授權的 [Blade 指示詞](#via-blade-templates) (`@can`, `@cannot`, `@canany`) 都接受一個陣列作為其第二引數。這些陣列的元素會被作為引數傳給 Gate 閉包，並且可在進行權限認證時提供額外的上下文：

    use App\Models\Category;
    use App\Models\User;
    use Illuminate\Support\Facades\Gate;
    
    Gate::define('create-post', function (User $user, Category $category, $pinned) {
        if (! $user->canPublishToGroup($category->group)) {
            return false;
        } elseif ($pinned && ! $user->canPinPosts()) {
            return false;
        }
    
        return true;
    });
    
    if (Gate::check('create-post', [$category, $pinned])) {
        // The user can create the post...
    }
<a name="gate-responses"></a>

### Gate 回應

到目前為止，我們只看過了回傳簡單布林值的 Gate。但，有時候我們可能會想回傳一些更具體的回覆，並在其中包含錯誤訊息。為此，可以在 Gate 內回傳 `Illuminate\Auth\Access\Response`：

    use App\Models\User;
    use Illuminate\Auth\Access\Response;
    use Illuminate\Support\Facades\Gate;
    
    Gate::define('edit-settings', function (User $user) {
        return $user->isAdmin
                    ? Response::allow()
                    : Response::deny('You must be an administrator.');
    });
即使從 Gate 內回傳授權回應，`Gate::allows` 方法依然會回傳簡單的布林值。不過，可以使用 `Gate::inspect` 方法來取得 Gate 回傳的完整授權回應：

    $response = Gate::inspect('edit-settings');
    
    if ($response->allowed()) {
        // The action is authorized...
    } else {
        echo $response->message();
    }
在使用 `Gate::authorize` 方法時，若動作未被授權，會回傳 `AuthorizationException`。這時，授權回應所提供的錯誤訊息會進一步被傳給 HTTP 回應：

    Gate::authorize('edit-settings');
    
    // The action is authorized...
<a name="customising-gate-response-status"></a>

#### 自定 HTTP Response 狀態

當 Gate 拒絕某個動作時，會回傳 `403` HTTP Response。不過，在某些情況下，若能回傳其他的 HTTP 狀態碼更好。我們可以使用 `Illuminate\Auth\Access\Response` 類別的 `denyWithStatus` 靜態建構函式來自訂在授權檢查失敗的時候要回傳的 HTTP 狀態碼：

    use App\Models\User;
    use Illuminate\Auth\Access\Response;
    use Illuminate\Support\Facades\Gate;
    
    Gate::define('edit-settings', function (User $user) {
        return $user->isAdmin
                    ? Response::allow()
                    : Response::denyWithStatus(404);
    });
而因為使用 `404` Response 來隱藏資源是在 Web App 中常見的手段，因此為了方便， Laravel 也提供了 `denyAsNotFound` 方法：

    use App\Models\User;
    use Illuminate\Auth\Access\Response;
    use Illuminate\Support\Facades\Gate;
    
    Gate::define('edit-settings', function (User $user) {
        return $user->isAdmin
                    ? Response::allow()
                    : Response::denyAsNotFound();
    });
<a name="intercepting-gate-checks"></a>

### 攔截 Gate 檢查

有的時候，我們可能會想授權特定使用者所有的權限。可以使用 `before` 方法來定義會在所有權限檢查前執行的閉包：

    use Illuminate\Support\Facades\Gate;
    
    Gate::before(function ($user, $ability) {
        if ($user->isAdministrator()) {
            return true;
        }
    });
若 `before` 閉包回傳了一個非 null 的結果，則該結果值會被當作權限檢查的結果。

可以使用 `after` 方法來定義一個會在所有其他權限檢查後執行的閉包：

    Gate::after(function ($user, $ability, $result, $arguments) {
        if ($user->isAdministrator()) {
            return true;
        }
    });
與 `before` 方法類似，若 `after` 閉包回傳了非 null 的結果，則該結果會被當作權限檢查的結果。

<a name="inline-authorization"></a>

### 內嵌授權

有時候，我們可能需要判斷目前登入的使用者是否有權限進行給定動作，但我們不想給這個動作撰寫獨立的 Gate。在 Laravel，我們可以使用 `Gate::allowIf` 與 `Gate::denyIf` 方法來進行這類的「內嵌」授權檢查：

```php
use Illuminate\Support\Facades\Gate;

Gate::allowIf(fn ($user) => $user->isAdministrator());

Gate::denyIf(fn ($user) => $user->banned());
```
若該動作未授權、或是使用者未登入，則 Laravel 會自動擲回 `Illuminate\Auth\Access\AuthorizationException` 例外。`AuthorizationException` 實體會自動由 Laravel 的例外處理常式轉換為 403 HTTP 回應。

<a name="creating-policies"></a>

## 建立 Policy

<a name="generating-policies"></a>

### 產生 Policy

Policy 是用來依照特定 Model 或資源阻止授權邏輯的類別。舉例來說，若你的專案是個部落格，則可能會有 `App\Models\Post` Model 以及對應的 `App\Policies\PostPolicy` 來授權使用者進行建立或更新貼文之類的動作。

可以通過 `make:policy` Artisan 指令來產生 Policy。產生的 Policy 會被放在 `app/Policies` 目錄內。若專案中沒有該目錄中，則 Laravel 會自動建立：

```shell
php artisan make:policy PostPolicy
```
`make:policy` 指令會產生一個空的 Policy 類別。若想產生一個與檢視 (View)、建立 (Create)、更新 (Update)、刪除 (Delete) 資源有關的範例 方法的 Policy 類別，可以在執行指令時提供 `--model` 選項：

```shell
php artisan make:policy PostPolicy --model=Post
```
<a name="registering-policies"></a>

### 註冊 Policy

建立好 Policy 類別後，需要註冊 Policy。註冊 Policy 就能告訴 Laravel：在進行授權動作時，遇到特定 Model 時要使用哪個對應 Policy。

在新安裝的 Laravel 專案中的 `App\Providers\AuthServiceProvider` 內，有一個 `policies` 屬性。該屬性會將 Eloquent Model 映射到其對應的 Policy。通過註冊 Policy，就可以告訴 Laravel 在權限檢查時遇到的 Eloquent Model 要使用哪個 Policy：

    <?php
    
    namespace App\Providers;
    
    use App\Models\Post;
    use App\Policies\PostPolicy;
    use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
    use Illuminate\Support\Facades\Gate;
    
    class AuthServiceProvider extends ServiceProvider
    {
        /**
         * The policy mappings for the application.
         *
         * @var array
         */
        protected $policies = [
            Post::class => PostPolicy::class,
        ];
    
        /**
         * Register any application authentication / authorization services.
         *
         * @return void
         */
        public function boot()
        {
            $this->registerPolicies();
    
            //
        }
    }
<a name="policy-auto-discovery"></a>

#### Policy Auto-Discovery

除了手動註冊 Model Policy，只要 Model 與 Policy 都遵守 Laravel 的命名常規，Laravel 就能自動找到 Policy。講得更仔細一點，各個 Policy 應放置與 Model 目錄下或  Model 目錄上層的 `Policies` 目錄內。因此，舉例來說，Model 可能被放在 `app/Models` 目錄內，而模型則可能被放在 `app/Policies` 目錄內。此時，Laravel 會先在 `app/Models/Policies` 內檢查 Policy，然後才到 `app/Policies` 內尋找。另外，Policy 名稱也必須與 Model 名稱相同，然後在後方加上 `Policy` 後綴。如此一來，`User` Model 對應的 Policy 類別就是 `UserPolicy`。

若讀者想定義自己的 Policy Discovery 邏輯，可以通過 `Gate::guessPolicyNamesUsing` 方法來註冊自訂 Policy Discovery 閉包。通常來說，這個方法應該在專案的 `AuthServiceProvider` 內的 `boot` 方法內呼叫：

    use Illuminate\Support\Facades\Gate;
    
    Gate::guessPolicyNamesUsing(function ($modelClass) {
        // Return the name of the policy class for the given model...
    });
> [!WARNING]  
> 所有在 `AuthServiceProvider` 中顯式映射之 Policy 的優先級都會比 Auto-Discover 的 Policy 高。

<a name="writing-policies"></a>

## 撰寫 Policy

<a name="policy-methods"></a>

### Policy 方法

註冊好 Policy 類別後，可以為每個授權的動作加上方法。舉例來說，我們來在 `PostPolicy` 中定義一個 `update` 方法，用來判斷給定的 `App\Models\User` 可否更新給定的 `App\Models\Post` 實體。

`update` 方法會在其引數內收到 `User` 與 `Post` 實體，並且應回傳 `true` 或 `false` 來判斷該使用者是否有權限更新給定的 `Post`。因此，在這個例子中，我們會認證使用者的 `id` 是否與貼文的 `user_id` 相符：

    <?php
    
    namespace App\Policies;
    
    use App\Models\Post;
    use App\Models\User;
    
    class PostPolicy
    {
        /**
         * Determine if the given post can be updated by the user.
         *
         * @param  \App\Models\User  $user
         * @param  \App\Models\Post  $post
         * @return bool
         */
        public function update(User $user, Post $post)
        {
            return $user->id === $post->user_id;
        }
    }
我們可以繼續在 Policy 內為各種所需的權限檢查定義更多額外方法。舉例來說，我們可以定義 `view` 或 `delete` 方法來對各種與 `Post` 有關的動作進行權限檢查。但不要忘了，你可以隨意為 Policy 的方法命名。

通過 Artisan 主控台產生 Policy 時若有使用 `--model` 選項，則 Policy 就已經包含了 `viewAny`, `view`, `create`, `update`, `delete`, `restore` 與 `forceDelete` 動作的方法。

> [!NOTE]  
> 所有的 Policy 都經由 Laravel 的 [Service Container](/docs/{{version}}/container) 進行解析，這樣一來，可以在 Policy 的建構函式 (Constructor) 內對任何所需的相依項進行型別提示，這些相依項會被自動插入到 Class 內。

<a name="policy-responses"></a>

### Policy 回應

到目前為止，我們只看過了回傳簡單布林值的 Policy 方法。但，有時候我們可能會想回傳一些更具體的回覆，並在其中包含錯誤訊息。為此，可以在 Policy 方法內回傳 `Illuminate\Auth\Access\Response`：

    use App\Models\Post;
    use App\Models\User;
    use Illuminate\Auth\Access\Response;
    
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response
     */
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id
                    ? Response::allow()
                    : Response::deny('You do not own this post.');
    }
當從 Policy 內回傳授權回應時，`Gate::allows` 方法依然會回傳簡單的布林值。不過，可以使用 `Gate::inspect` 方法來取得 Gate 回傳的完整授權回應：

    use Illuminate\Support\Facades\Gate;
    
    $response = Gate::inspect('update', $post);
    
    if ($response->allowed()) {
        // The action is authorized...
    } else {
        echo $response->message();
    }
在使用 `Gate::authorize` 方法時，若動作未被授權，會回傳 `AuthorizationException`。這時，授權回應所提供的錯誤訊息會進一步被傳給 HTTP 回應：

    Gate::authorize('update', $post);
    
    // The action is authorized...
<a name="customising-policy-response-status"></a>

#### 自定 HTTP Response 狀態

當 Policy 方法拒絕某個動作時，會回傳 `403` HTTP Response。不過，在某些情況下，若能回傳其他的 HTTP 狀態碼更好。我們可以使用 `Illuminate\Auth\Access\Response` 類別的 `denyWithStatus` 靜態建構函式來自訂在授權檢查失敗的時候要回傳的 HTTP 狀態碼：

    use App\Models\Post;
    use App\Models\User;
    use Illuminate\Auth\Access\Response;
    
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response
     */
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id
                    ? Response::allow()
                    : Response::denyWithStatus(404);
    }
而因為使用 `404` Response 來隱藏資源是在 Web App 中常見的手段，因此為了方便， Laravel 也提供了 `denyAsNotFound` 方法：

    use App\Models\Post;
    use App\Models\User;
    use Illuminate\Auth\Access\Response;
    
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response
     */
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id
                    ? Response::allow()
                    : Response::denyAsNotFound();
    }
<a name="methods-without-models"></a>

### 沒有 Model 的方法

有些 Policy 方法只會收到目前登入使用者的實體。最常見的情況就是在授權 `create` 動作時。舉例來說，若正在建立部落格，則可能會想判斷某個使用者是否有權限建立任何貼文。在這種情況想，Policy 方法應該只會收到使用者實體：

    /**
     * Determine if the given user can create posts.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->role == 'writer';
    }
<a name="guest-users"></a>

### 訪客使用者

預設情況下，當連入 HTTP 請求並不是由已登入使用者發起的時候，所有的 Gate 與 Policy 都會回傳 `false`。不過，我們可以通過在使用者的引數定義上定義「可選」的型別提示，或是提供一個 `null` 預設值，來讓這些權限檢查可以進到 Gate 與 Policy 中：

    <?php
    
    namespace App\Policies;
    
    use App\Models\Post;
    use App\Models\User;
    
    class PostPolicy
    {
        /**
         * Determine if the given post can be updated by the user.
         *
         * @param  \App\Models\User  $user
         * @param  \App\Models\Post  $post
         * @return bool
         */
        public function update(?User $user, Post $post)
        {
            return optional($user)->id === $post->user_id;
        }
    }
<a name="policy-filters"></a>

### Policy 篩選器

我們可能會想讓特定使用者擁有某個 Policy 中擁有的所有權限。為此，可以在 Policy 內定義一個 `before` 方法。`before` 方法會在 Policy 內任何其他方法之前被執行，如此一來我們便有機會可以在預定的 Policy 方法被實際執行前對該行為進行授權。這個功能最常見的使用情況就是用來授權網站管理員來進行所有動作：

    use App\Models\User;
    
    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }
若拒絕特定類型的使用者的所有授權，可以在 `before` 方法內回傳 `false`。若回傳 `null`，則權限檢查會繼續傳到 Policy 方法內。

> [!WARNING]  
> 若 Policy 類別內不含要檢查權限名稱的方法，則 `before` 方法將不會被呼叫。

<a name="authorizing-actions-using-policies"></a>

## 通過 Policy 來授權動作

<a name="via-the-user-model"></a>

### 通過 User Model

Laravel 專案內建的 `App\Models\User` Model 中包含了兩個實用的方法，可以用來進行權限檢查：`can` 與 `cannot`。`can` 與 `cannot` 方法接收用要進行權限檢查的動作名稱，以及相關的 Model。舉例來說，讓我們來判斷某個使用者是否有權限更新給定的 `App\Models\Post` Model。一般來說，這個檢查會在 Controller 的方法內進行：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Models\Post;
    use Illuminate\Http\Request;
    
    class PostController extends Controller
    {
        /**
         * Update the given post.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \App\Models\Post  $post
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request, Post $post)
        {
            if ($request->user()->cannot('update', $post)) {
                abort(403);
            }
    
            // Update the post...
        }
    }
若已為給定的 Model [註冊好 Policy](#registering-policies)，則 `can` 方法會自動呼叫適當的 Policy，並回傳布林結果值。若沒有為該 Model 註冊好的 Policy，則 `can` 方法會呼叫符合給定動作名稱的閉包 Gate。

<a name="user-model-actions-that-dont-require-models"></a>

#### 不需要 Model 的動作

請記得，某些對應到 Policy 方法的動作，如 `create`，並不要求 Model 實體。這種情況下，可以將類別名稱傳給 `can` 方法。類別名稱會用來判斷對動作進行權限檢查時要使用哪個 Policy：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Models\Post;
    use Illuminate\Http\Request;
    
    class PostController extends Controller
    {
        /**
         * Create a post.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            if ($request->user()->cannot('create', Post::class)) {
                abort(403);
            }
    
            // Create the post...
        }
    }
<a name="via-controller-helpers"></a>

### 通過 Controller 輔助函式

除了 `App\Models\User` Model 上提供的實用方法外，Laravel 還為所有繼承了 `App\Http\Controller` 基礎類別的 Controller 提供了一個實用的 `authorize` 方法。

與 `can` 方法類似，這個方法接收要進行權限檢查的動作名稱、以及相關的 Model。若該動作未被授權，則 `authorize` 方法會擲回 `Illuminate\Auth\Access\AuthroizationException` 例外，Laravel 的例外處理常式會自動將該例外轉成有 403 狀態碼的 HTTP 回應：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Models\Post;
    use Illuminate\Http\Request;
    
    class PostController extends Controller
    {
        /**
         * Update the given blog post.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \App\Models\Post  $post
         * @return \Illuminate\Http\Response
         *
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function update(Request $request, Post $post)
        {
            $this->authorize('update', $post);
    
            // The current user can update the blog post...
        }
    }
<a name="controller-actions-that-dont-require-models"></a>

#### 不需要 Model 的動作

與前面討論過的一樣，某些 Policy 方法，如 `create`，並不要求 Model 實體。這種情況下，應將類別名稱傳給 `authorize` 方法。類別名稱會用來判斷對動作進行權限檢查時要使用哪個 Policy：

    use App\Models\Post;
    use Illuminate\Http\Request;
    
    /**
     * Create a new blog post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request)
    {
        $this->authorize('create', Post::class);
    
        // The current user can create blog posts...
    }
<a name="authorizing-resource-controllers"></a>

#### 授權 Resource Controller

若使用 [Resource Controller](/docs/{{version}}/controllers#resource-controllers)，則可以在 Controller 建構函式中使用 `authorizeResource` 方法。這個方法會將適當的 `can` Middleware 定義附加到該 Resource Controller 的方法內。

`authorizeResource` 方法接受 Model 類別名稱作為其第一個引數，而路由名稱或包含 Model ID 的請求參數將為第二個引數。請先確定 [Resource Controller](/docs/{{version}}/controllers#resource-controllers) 是使用 `--model` 旗標建立的，這樣該類別才有所需的方法簽章與型別提示：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Models\Post;
    use Illuminate\Http\Request;
    
    class PostController extends Controller
    {
        /**
         * Create the controller instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->authorizeResource(Post::class, 'post');
        }
    }
下列 Controller 方法會映射到其對應的 Policy 方法。當請求被路由到給定的 Controller 方法時，對應的 Policy 方法會在 Controller 方法執行前被自動叫用：

| Controller 方法 | Policy 方法 |
| --- | --- |
| index | viewAny |
| show | view |
| create | create |
| store | create |
| edit | update |
| update | update |
| destroy | delete |

> [!NOTE]  
> 可以使用 `--model` 選項搭配 `make:policy` 指令來快速為給定的 Model 產生 Policy：`php artisan make:policy PostPolicy --model=Post`。

<a name="via-middleware"></a>

### 通過 Middleware

Laravel 提供了一個可以用來在連入請求進入路由或 Controller 前進行權限檢查的 Middleware。預設情況下，`Illuminate\Auth\Middleware\Authorize` Middleware 在 `App\Http\Kernel` 類別內被指派到 `can` 索引鍵上。我們來看看一個使用 `can` Middleware 對使用者能否更新貼文進行權限檢查的例子：

    use App\Models\Post;
    
    Route::put('/post/{post}', function (Post $post) {
        // The current user may update the post...
    })->middleware('can:update,post');
在此例子中，我們將兩個引數傳給了 `can` Middleware。第一個引數是我們想進行權限檢查的動作名稱，而第二個引數是我們想傳給 Policy 方法的路由參數。在這個例子中，由於我們使用了[隱式 Model 繫結](/docs/{{version}}/routing#implicit-binding)，所以會將 `App\Models\Post` Model 傳給 Policy 方法。若使用者沒有權限執行給定的動作，則這個 Middleware 會回傳狀態碼 403 的 HTTP 回應。

為了方便起見，也可以使用 `can` 方法來將 `can` Middleware 附加到路由上：

    use App\Models\Post;
    
    Route::put('/post/{post}', function (Post $post) {
        // The current user may update the post...
    })->can('update', 'post');
<a name="middleware-actions-that-dont-require-models"></a>

#### 不需要 Model 的動作

再強調一次，某些 Policy 方法，如 `create`，並不要求 Model 實體。這種情況下，可以將類別名稱傳給 Middleware。這個類別名稱會用來判斷對動作進行權限檢查時要使用哪個 Policy：

    Route::post('/post', function () {
        // The current user may create posts...
    })->middleware('can:create,App\Models\Post');
在字串形式的 Middleware 定義中指定完整的類別名稱可能會有點麻煩。因此，我們也可以使用 `can` 方法來將 `can` Middleware 附加到路由上：

    use App\Models\Post;
    
    Route::post('/post', function () {
        // The current user may create posts...
    })->can('create', Post::class);
<a name="via-blade-templates"></a>

### 通過 Blade 樣板

在撰寫 Blade 樣板時，我們可能會在使用者有權限執行給定動作時顯示某一部分的頁面。舉例來說，我們可能想在使用者真的可以更新貼文時才顯示更新表單。這時，可以使用 `@can` 與 `@cannot` 指示詞：

```blade
@can('update', $post)
    <!-- The current user can update the post... -->
@elsecan('create', App\Models\Post::class)
    <!-- The current user can create new posts... -->
@else
    <!-- ... -->
@endcan

@cannot('update', $post)
    <!-- The current user cannot update the post... -->
@elsecannot('create', App\Models\Post::class)
    <!-- The current user cannot create new posts... -->
@endcannot
```
這些指示詞是撰寫 `@if` 與 `@unless` 陳述式時的方便捷徑。上方的 `@can` 與 `@cannot` 陳述式與下列陳述式相同：

```blade
@if (Auth::user()->can('update', $post))
    <!-- The current user can update the post... -->
@endif

@unless (Auth::user()->can('update', $post))
    <!-- The current user cannot update the post... -->
@endunless
```
可以在包含一系列動作的陣列中判斷某個使用者是否有權限執行其中的任意動作。為此，請使用 `@canany` 指示詞：

```blade
@canany(['update', 'view', 'delete'], $post)
    <!-- The current user can update, view, or delete the post... -->
@elsecanany(['create'], \App\Models\Post::class)
    <!-- The current user can create a post... -->
@endcanany
```
<a name="blade-actions-that-dont-require-models"></a>

#### 不需要 Model 的動作

與其他大多數的授權方法一樣，當某個動作不需要 Model 實體時，可以將類別名稱傳給 `@can` 與 `@cannot` 指示詞：

```blade
@can('create', App\Models\Post::class)
    <!-- The current user can create posts... -->
@endcan

@cannot('create', App\Models\Post::class)
    <!-- The current user can't create posts... -->
@endcannot
```
<a name="supplying-additional-context"></a>

### 提供額外的上下文

當使用 Policy 對動作進行權限檢查時，可以將陣列作為第二引數傳給各種權限檢查函式與輔助函式。陣列中的第一個元素是用來判斷要叫用哪個 Policy 的，而剩下的元素則會作為參數傳給 Policy 方法，可用來在做權限檢查時提供額外的上下文。舉例來說，假設有下列 `PostPolicy` 方法定義，其中包含了一個額外的 `$category` 參數：

    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @param  int  $category
     * @return bool
     */
    public function update(User $user, Post $post, int $category)
    {
        return $user->id === $post->user_id &&
               $user->canUpdateCategory($category);
    }
在嘗試判斷登入使用者能否更新給定貼文時，我們可以像這樣叫用該 Policy 方法：

    /**
     * Update the given blog post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', [$post, $request->category]);
    
        // The current user can update the blog post...
    }