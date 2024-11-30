---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/175/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 53.77
---

# View

- [簡介](#introduction)
  - [Writing Views in React / Vue](#writing-views-in-react-or-vue)
  
- [Creating and Rendering Views](#creating-and-rendering-views)
  - [巢狀的 View 目錄](#nested-view-directories)
  - [Creating the First Available View](#creating-the-first-available-view)
  - [Determining if a View Exists](#determining-if-a-view-exists)
  
- [Passing Data to Views](#passing-data-to-views)
  - [在所有 View 間共享資料](#sharing-data-with-all-views)
  
- [View Composer](#view-composers)
  - [View Creator](#view-creators)
  
- [最佳化 View](#optimizing-views)

<a name="introduction"></a>

## 簡介

當那果然，直接從 Route 或 Controller 中回傳整個 HTML 文件字串不是很實際。幸好，使用 View，我們就能方便地將所有的 HTML 都放在獨立的檔案中。

使用 View 就可從 Controller / 應用程式的邏輯中將顯示邏輯拆出來並放在 `resources/views` 目錄下。使用 Laravel 時，View 樣板通常使用 [Blade 樣板語言撰寫](/docs/{{version}}/blade)。一個簡單的 View 看起來可能像這樣：

```blade
<!-- View stored in resources/views/greeting.blade.php -->

<html>
    <body>
        <h1>Hello, {{ $name }}</h1>
    </body>
</html>
```
這個 View 保存在 `resources/views/greeting.blade.php`，因此我們可以像這樣使用全域的 `view` 輔助函式來回傳 View：

    Route::get('/', function () {
        return view('greeting', ['name' => 'James']);
    });
> [!NOTE]  
> 想瞭解更多有關如何撰寫 Blade 樣板的資訊嗎？請參考完整的 [Blade 說明文件](/docs/{{version}}/blade)來入門 Blade 樣板。

<a name="writing-views-in-react-or-vue"></a>

### Writing Views in React / Vue

除了使用 Blade 來在 PHP 中撰寫前端樣板外，許多開發人員也開始偏好使用 React 或 Vue 來撰寫樣板。在 Laravel 中，多虧有了 [Inertia](https://inertiajs.com/)，要使用 React 或 Vue 來撰寫樣板一點也不麻煩。Inertia 是一個可將 React 或 Vue 前端與 Laravel 後端搭配使用的一個套件，可讓我們不需處理建立 SPA 常見的麻煩。

Laravel 的 Breeze 與 Jetstream [入門套件](/docs/{{version}}/starter-kits) 藉由 Inertia 驅動，能讓你在製作下一個 Laravel 專案時有個好的起始點。此外，[Laravel Bootcamp](https://bootcamp.laravel.com) 還提供了使用 Inertia 製作 Laravel 專案的完整示範，並包含了使用 Vue 與 React 的範例。

<a name="creating-and-rendering-views"></a>

## Creating and Rendering Views

可以在專案的 `resources/views` 目錄下放置副檔名為 `.blade.php` 的檔案來建立 View，或者也可以使用 `make:view` Artisan 指令：

```shell
php artisan make:view greeting
```
`.blade.php` 副檔名告訴 Laravel 這個檔案是一個 [Blade 樣板](/docs/{{version}}/blade)。Blade 樣板中包含 HTML 與 Blade 指示詞 (Directive)，Blade 指示詞可用來輕鬆地輸出 (Echo) 資料、建立「if」陳述式、迭代資料⋯⋯等。

建立好 View 之後，就可以在專案的 Route 或 Controller 中使用全域的 `view` 輔助函式來回傳 View：

    Route::get('/', function () {
        return view('greeting', ['name' => 'James']);
    });
也可以使用 `View` Facade 來回傳 `View`：

    use Illuminate\Support\Facades\View;
    
    return View::make('greeting', ['name' => 'James']);
就像我們可以看到的，傳給 `view` 輔助函式的第一個引數是 View 檔案在 `resources/view` 目錄下對應的名稱。第二個引數是一組資料陣列，包含要提供給 View 的資料。在這個情況下，我們傳入了一個 `name` 變數，並在 View 裡面使用 [Blade 語法](/docs/{{version}}/blade)來顯示。

<a name="nested-view-directories"></a>

### 巢狀的 View 目錄

View 也可以巢狀放置在 `resources/views` 目錄中的子目錄。可使用「點 (.)」標記法來參照巢狀的 View。舉例來說，若有個 View 保存在 `resources/views/admin/profile.blade.php`，則我們可以在我們程式的 Route 或 Controller 中像這樣回傳這個 View：

    return view('admin.profile', $data);
> [!WARNING]  
> View 目錄的名稱不可包含 `.` 字元。

<a name="creating-the-first-available-view"></a>

### Creating the First Available View

使用 `View` Facade 的 `first` 方法，就可以建立給定 View 陣列中存在的第一個 View。這個方法適用於你的專案或套件能自訂 View 或複寫 View 時：

    use Illuminate\Support\Facades\View;
    
    return View::first(['custom.admin', 'admin'], $data);
<a name="determining-if-a-view-exists"></a>

### Determining if a View Exists

若有需要判斷某個 View 是否存在，可使用 `View` Facade。`exists` 方法會在 View 存在時回傳 `true`：

    use Illuminate\Support\Facades\View;
    
    if (View::exists('admin.profile')) {
        // ...
    }
<a name="passing-data-to-views"></a>

## Passing Data to Views

就像我們在前一個範例中看到的一樣，我們可以傳入一組資料陣列給 View 來讓這些資料在 View 中可用：

    return view('greetings', ['name' => 'Victoria']);
用這種方式傳遞資料時，這些專遞的資料應該是有索引鍵 / 值配對的陣列。將資料提供給 View 後，就可以使用這些資料的索引鍵來在 View 中存取其值，如 `<?php echo $name; ?>`。

除了將完整的資料陣列傳給 `view` 輔助函式外，也可以使用 `with` 方法來將單一資料項目提供給 View。`with` 方法會回傳 View 物件的實體，這樣一來我們就能在回傳 View 前繼續串上其他方法呼叫：

    return view('greeting')
                ->with('name', 'Victoria')
                ->with('occupation', 'Astronaut');
<a name="sharing-data-with-all-views"></a>

### 在所有 View 間共用資料

有時候，我們會需要在所有的 View 間共享某個資料。為此，可以使用 `View` Facade 的 `share` 方法。一般來說，我們應該在某個 Service Provider 的 `boot` 方法中呼叫 `share` 方法。我們可以在 `App\Providers\AppServiceProvider` 類別中呼叫，或者也可以建立一個獨立的 Service Provider 來放置共享的資料：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\View;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            // ...
        }
    
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            View::share('key', 'value');
        }
    }
<a name="view-composers"></a>

## View Composer

View Composer 是 View 在轉譯時會呼叫的回呼或類別方法。若你有一筆想在每次轉譯 View 時要繫結到 View 上的資料時，可以使用 View Composer 來協助我們將這類的邏輯拆分到一個地方。當你的程式中有許多的 Route 或 Controller 都回傳相同的 View，且這些 View 都需要同樣的資料時，View Composer 就特別適合。

Typically, view composers will be registered within one of your application's [service providers](/docs/{{version}}/providers). In this example, we'll assume that the `App\Providers\AppServiceProvider` will house this logic.

我們會使用 `View` Facade 的 `composer` 方法來註冊 View Composer。Laravel 並沒有提供放置基於類別的 View Composer 用的預設目錄，因此我們可以隨意放置 View Composer。舉例來說，我們可以建立一個 `app/View/Composers` 目錄來放置所有的 View Composer：

    <?php
    
    namespace App\Providers;
    
    use App\View\Composers\ProfileComposer;
    use Illuminate\Support\Facades;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\View\View;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            // ...
        }
    
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            // Using class based composers...
            Facades\View::composer('profile', ProfileComposer::class);
    
            // Using closure based composers...
            Facades\View::composer('welcome', function (View $view) {
                // ...
            });
    
            Facades\View::composer('dashboard', function (View $view) {
                // ...
            });
        }
    }
現在，我們已經註冊好 Composer 了。每當轉譯 `profile` View 時，就會執行 `App\View\Composers\ProfileComposer` 類別的 `compose` 方法。我慢來看看這個 Composer 類別的例子：

    <?php
    
    namespace App\View\Composers;
    
    use App\Repositories\UserRepository;
    use Illuminate\View\View;
    
    class ProfileComposer
    {
        /**
         * Create a new profile composer.
         */
        public function __construct(
            protected UserRepository $users,
        ) {}
    
        /**
         * Bind data to the view.
         */
        public function compose(View $view): void
        {
            $view->with('count', $this->users->count());
        }
    }
就像我們可以看到的，所有的 View Composer 都會經過 [Service Container] 解析，因此我們可以在 Composer 的 Constructor (建構函式) 上型別提示 (Type-Hint) 任何需要的相依性。

<a name="attaching-a-composer-to-multiple-views"></a>

#### Attaching a Composer to Multiple Views

只要將一組 View 陣列作為第一個引數傳給 `composer` 方法，我們就可以一次將一個 View Composer 附加到多個 View 上：

    use App\Views\Composers\MultiComposer;
    use Illuminate\Support\Facades\View;
    
    View::composer(
        ['profile', 'dashboard'],
        MultiComposer::class
    );
`composer` 方法也接受使用 `*` 字元作為萬用字元。這樣我們就可以將某個 Composer 附加到所有 View 上：

    use Illuminate\Support\Facades;
    use Illuminate\View\View;
    
    Facades\View::composer('*', function (View $view) {
        // ...
    });
<a name="view-creators"></a>

### View Creator

View "Creator" 與 View Composer 非常類似。不過，View Creator 會在 View 被初始化後馬上執行，而不是在 View 要轉譯前才執行。若要註冊 View Creator，請使用 `creator` 方法：

    use App\View\Creators\ProfileCreator;
    use Illuminate\Support\Facades\View;
    
    View::creator('profile', ProfileCreator::class);
<a name="optimizing-views"></a>

## 最佳化 View

預設情況下，Blade 樣板的 View 會在被使用時候才編譯。當正在執行的 Request 要轉譯 View 時，Laravel 會判斷這個 View 是否有已編譯的版本。若有已編譯版本，則 Laravel 會接著比較未編譯版本的 View 是否比已編譯版本新。若這個 View 沒有已編譯好的版本，或是未編譯版本有修改過，則 Laravel 會重新編譯這個 View。

在 Request 中編譯 View 會對效能造成一點點的負面影響。因此，Laravel 提供了一個 `view:cache` Artisan 指令，來讓我們預先編譯專案中使用的所有 View。為了提升效能，建議在部署流程中執行這個指令：

```shell
php artisan view:cache
```
可以使用 `view:clear` 指令來清除 View 快取：

```shell
php artisan view:clear
```