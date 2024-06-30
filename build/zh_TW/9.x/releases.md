---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/135/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
---

# 版本資訊

- [版本策略](#versioning-scheme)
- [支援政策](#support-policy)
- [Laravel 9](#laravel-9)

<a name="versioning-scheme"></a>

## 版本策略

Laravel 及其第一方套件都遵守 [語義化版本](https://semver.org/lang/zh-Tw/)。框架的主要更新會每年釋出 (約在二月時)，而次版本與修訂版則可能頻繁到每週更新。次版本與修訂版 **絕對不會** 包含^[中斷性變更](Breaking Change)。

由於 Laravel 的主要更新會包含中斷性變更，因此在應用程式或套件中參照 Laravel 框架或其組件時，應使用如 `^9.0` 這樣的版本限制式。然而，我們竭力確保主要更新應可於一天之內完成。

<a name="named-arguments"></a>

#### 帶名稱的引數

[帶名稱引數](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments)功能尚未包含在 Laravel 的向下相容性方針內。我們可能會在有必要的時候更改函式的參數名稱以改進 Laravel 的程式碼。因此，在使用帶名稱引數呼叫 Laravel 方法時應格外注意，並瞭解到引數名稱未來可能會有所更改。

<a name="support-policy"></a>

## 支援政策

所有的 Laravel 版本都提供 18 個月的 Bug 修正，以及 2 年的安全性修正。對於其他的函式庫，如 Lumen，則只有最新的主要版本會收到 Bug 修正。此外，也請參考 [Laravel 支援的](/docs/{{version}}/database#introduction)資料庫版本。

| 版本 | PHP (*) | 釋出日期 | Bug 修正期限 | 安全性修正期限 |
| --- | --- | --- | --- | --- |
| 6 (LTS) | 7.2 - 8.0 | 2019 年 9 月 3 日 | 2022 年 1 月 25 日 | 2022 年 9 月 6 日 |
| 7 | 7.2 - 8.0 | 2020 年 3 月 3 日 | 2020 年 10 月 6 日 | 2021 年 3 月 3 日 |
| 8 | 7.3 - 8.1 | 2020 年 9 月 8 日 | 2022 年 7 月 26 日 | 2023 年 1 月 24 日 |
| 9 | 8.0 - 8.2 | 2022 年 2 月 8 日 | 2023 年 8 月 8 日 | 2024 年 2 月 6 日 |
| 10 | 8.1 - 8.2 | 2023 年第 1 季 | 2024 年 8 月 6 日 | 2025 年 2 月 4 日 |

<div class="version-colors">
    <div class="end-of-life">
        <div class="color-box"></div>
        <div>End of life</div>
    </div>
    <div class="security-fixes">
        <div class="color-box"></div>
        <div>Security fixes only</div>
    </div>
</div>

(*) 支援的 PHP 版本

<a name="laravel-9"></a>

## Laravel 9

讀者可能已經知道，從 Laravel 8 開始，Laravel 改為每年釋出新的主要版本。在此之前，每 6 個月都會釋出主要版本。這個改變是為了降低社群維護的負擔，並讓我們的開發團隊能想辦法在不包含中斷性更改 (Breaking Change) 的情況下繼續提供驚艷且強大的新功能。因此，我們在 Laravel 8 中，以不破壞向下相容性的前提下推出了許多強健的功能，如平行測試 (Parallel Testing) 支援、改進了 Breeze 入門套件、HTTP 用戶端的改進、甚至還出了一些如「一對多種之一 (Has One of Many)」等新的 Eloquent 關聯類型。

因此，我們對於在目前版本中釋出新功能的承諾也將導致未來的「主要 (Major)」版本將著重於一些「維護性」的任務，如更新上游套件等，讀者稍後可以在本版本資訊內讀到。

Laravel 9 延續了 Laravel 8.x 中推出的各種改進，並支援 Symfony 6.0 元件、Symfony Mailer、Flysystem 3.0、改進過的 `route:list` 輸出、Laravel Scout 資料庫 Driver、新的 Eloquent 存取子 / 更動子語法、通過 Enum 進行的隱式路由繫結、以及其他多個 Bug 修正與可用性提升。

<a name="php-8"></a>

### PHP 8.0

Laravel 9.x 所要求的最小 PHP 版本為 8.0。

<a name="symfony-mailer"></a>

### Symfony Mailer

*Symfony Mailer 支援由 [Dries Vints](https://github.com/driesvints), [James Brooks](https://github.com/jbrooksuk), 與 [Julius Kiekbusch](https://github.com/Jubeki) 參與貢獻*。

在以前版本的 Laravel 中，我們使用 [Swift Mailer](https://swiftmailer.symfony.com/docs/introduction.html) 函式庫來寄送外部 E-Mail。不過，這個函式庫已不在維護，Symfony Mailer 為其後繼者。

請參考[升級指南](/docs/{{version}}/upgrade#symfony-mailer)來瞭解如何確保你的專案能相容於 Symfony Mailer。

<a name="flysystem-3"></a>

### Flysystem 3.x

*Flysystem 3.x 支援由 [Dries Vints](https://github.com/driesvints) 參與貢獻*。

Laravel 9.x 更新了上游的 Flysystem 相依性套件為 Flysystem 3.x。Flysystem 驅動了 `Storage` Facade 中提供的所有檔案系統互動功能。

請參考[升級指南](/docs/{{version}}/upgrade#flysystem-3)來瞭解如何確保你的專案能相容於 Flysystem 3.x。

<a name="eloquent-accessors-and-mutators"></a>

### 改進過的 Eloquent 存取子與更動子

*改進過的 Eloquent ^[Accessor](存取子) 與 ^[Mutator](更動子) 由 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

Laravel 9.x 提供了一種定義 Eloquent [存取子與更動子](/docs/{{version}}/eloquent-mutators#accessors-and-mutators)的全新方法。在之前版本的 Laravel 中，唯一一種定義存取子與更動子的方法就只有在 Model 中像這樣定義由前置詞的方法：

```php
public function getNameAttribute($value)
{
    return strtoupper($value);
}

public function setNameAttribute($value)
{
    $this->attributes['name'] = $value;
}
```

不過，在 Laravel 9.x 中，只需要標示回傳型別為 `Illuminate\Database\Eloquent\Casts\Attribute`，就可以使用不含前置詞的單一一個方法來定義存取子與更動子：

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

public function name(): Attribute
{
    return new Attribute(
        get: fn ($value) => strtoupper($value),
        set: fn ($value) => $value,
    );
}
```

此外，這種定義存取子的新方法也會將以屬性回傳的物件值快取起來，就跟[自訂型別轉換類別](/docs/{{version}}/eloquent-mutators#custom-casts)一樣：

```php
use App\Support\Address;
use Illuminate\Database\Eloquent\Casts\Attribute;

public function address(): Attribute
{
    return new Attribute(
        get: fn ($value, $attributes) => new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two'],
        ),
        set: fn (Address $value) => [
            'address_line_one' => $value->lineOne,
            'address_line_two' => $value->lineTwo,
        ],
    );
}
```

<a name="enum-casting"></a>

### Enum Eloquent 屬性型別轉換

> **Warning** Enum 型別轉換只可在 PHP 8.1 以上使用。

*Enum 型別轉換由 [Mohamed Said](https://github.com/themsaid) 參與貢獻*。

現在，Eloquent 也能讓我們將屬性值轉換為 PHP 的 [「Backed」Enum](https://www.php.net/manual/en/language.enumerations.backed.php) 了。為此，可在 Model 中的 `$casts` 屬性陣列中指定要型別轉換的屬性與 Enum：

    use App\Enums\ServerStatus;
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => ServerStatus::class,
    ];

定義好 Model 的型別轉換後，每次存取該屬性時就會自動轉換對 Enum 進行轉換：

    if ($server->status == ServerStatus::Provisioned) {
        $server->status = ServerStatus::Ready;
    
        $server->save();
    }

<a name="implicit-route-bindings-with-enums"></a>

### 使用 Enum 的隱式路由繫結

*隱式路由繫結由 [Nuno Maduro](https://github.com/nunomaduro) 參與貢獻*。

PHP 8.1 新增了對 [Enum](https://www.php.net/manual/en/language.enumerations.backed.php) 的支援。Laravel 9.x 中提供了能在路由定義中對 Enum 進行型別提示的功能。加上型別提示後，只有當網址中的路由片段 (Segment) 為有效的 Enum 時，Laravel 才會叫用該路由。若不是有效的 Enum 值，則會自動回傳 HTTP 404 回應。舉例來說，假設有下列 Enum：

```php
enum Category: string
{
    case Fruits = 'fruits';
    case People = 'people';
}
```

我們可以定義一個只有當 `{category}` 路由片段為 `fruits` 或 `people` 時才會被叫用的路由。若為其他值，則會回傳 HTTP 404 回應：

```php
Route::get('/categories/{category}', function (Category $category) {
    return $category->value;
});
```

<a name="forced-scoping-of-route-bindings"></a>

### Route 繫結的強制限定範圍

*強制限定作用範圍的繫結由 [Claudio Dekker](https://github.com/claudiodekker) 參與貢獻*。

在之前版本的 Laravel 中，我們可以在路由定義中限定第二個 Eloquent Model 一定要是前一個 Eloquent Model 的子 Model。舉例來說，假設有下列這樣通過 Slug 取得特定使用者的部落格貼文的路由定義：

    use App\Models\Post;
    use App\Models\User;
    
    Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
        return $post;
    });

在巢狀路由參數中使用自訂索引鍵的隱式繫結時，Laravel 會自動使用慣例來猜測上層 Model 的關聯名稱，並在查詢巢狀 Model 時以此限定查詢範圍。不過，在以前版本的 Laravel 中，只有在子路由繫結上使用自訂索引鍵時才可使用此功能。

不過，在 Laravel 9.x 中，就算沒有提供自訂索引鍵，我們還是可以告訴 Laravel 要如何對「子」繫結限定範圍。為此，我們可以在定義路由時叫用 `scopeBindings` 方法：

    use App\Models\Post;
    use App\Models\User;
    
    Route::get('/users/{user}/posts/{post}', function (User $user, Post $post) {
        return $post;
    })->scopeBindings();

或者，也可以讓整個路由定義群組使用限定範圍的繫結：

    Route::scopeBindings()->group(function () {
        Route::get('/users/{user}/posts/{post}', function (User $user, Post $post) {
            return $post;
        });
    });

<a name="controller-route-groups"></a>

### Controller 路由群組

*路由群組的改進由 [Luke Downing](https://github.com/lukeraymonddowning) 參與貢獻*。

我們現在可以使用 `controller` 方法來在路由群組中為所有的路由定義通用的 Controller 了。定義好之後，當定義路由時，就只需要提供要叫用的 Controller 方法即可：

    use App\Http\Controllers\OrderController;
    
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders/{id}', 'show');
        Route::post('/orders', 'store');
    });

<a name="full-text"></a>

### 全文索引與 Where 子句

*全文索引與「where」子句由 [Taylor Otwell](https://github.com/taylorotwell) 與 [Dries Vints](https://github.com/driesvints) 參與貢獻*。

現在，在使用 MySQL 或 PostgresSQL 時，我們可以在欄位定義中新增 `fullText` 方法來產生全文索引 (Full Text Indexes)：

    $table->text('bio')->fullText();

此外，也可以使用 `whereFullText` 與 `orWhereFullText` 方法來在查詢中為有[全文索引](/docs/{{version}}/migrations#available-index-types)的欄位加上全文「where」子句。Laravel 會依據底層的資料庫系統將這些方法轉換為適當的 SQL。舉例來說，使用 MySQL 的專案會產生 `MATCH AGAINST` 子句：

    $users = DB::table('users')
               ->whereFullText('bio', 'web developer')
               ->get();

<a name="laravel-scout-database-engine"></a>

### Laravel Scout 資料庫引擎

*Laravel Scout 資料庫引擎由 [Taylor Otwell](https://github.com/taylorotwell) 與 [Dries Vints](https://github.com/driesvints) 參與貢獻*。

若你的專案使用中小型的資料庫，或是資料庫的工作量 (Workload) 不高的話，現在，你可以使用 Scout 的「database」引擎，而不需使用如 Algolia 或 MeiliSearch 等專門的搜尋服務。在從現有資料庫過濾結果時，資料庫引擎會使用「where like」查詢語句來取得搜尋結果。

要瞭解更多有關 Scout 資料庫引擎的資訊，請參考 [Scout 說明文件](/docs/{{version}}/scout)。

<a name="rendering-inline-blade-templates"></a>

### 轉譯內嵌的 Blade 樣板

*轉譯內嵌的 Blade 樣板由 [Jason Beggs](https://github.com/jasonlbeggs) 參與貢獻。轉譯內嵌的 Blade 元件由 [Toby Zerner](https://github.com/tobyzerner) 參與貢獻*。

有時候，我們可能會想將原始的 Blade 樣板字串轉譯為有效的 HTML。我們可以通過 `Blade` Facade 所提供的 `render` 方法來達成。`render` 方法接受 Blade 樣板字串，以及一個用來提供給樣板的可選資料陣列：

```php
use Illuminate\Support\Facades\Blade;

return Blade::render('Hello, {{ $name }}', ['name' => 'Julian Bashir']);
```

類似地，只要將元件實體傳給 `renderComponent` 方法，就可轉譯給定的類別元件：

```php
use App\View\Components\HelloComponent;

return Blade::renderComponent(new HelloComponent('Julian Bashir'));
```

<a name="slot-name-shortcut"></a>

### Slot 名稱捷徑

*Slot 名稱捷徑由 [Caleb Porzio](https://github.com/calebporzio) 參與貢獻*。

在之前版本的 Laravel 中，可在 `x-slot` 標籤上使用 `name` 屬性來提供 Slot 名稱：

```blade
<x-alert>
    <x-slot name="title">
        Server Error
    </x-slot>

    <strong>Whoops!</strong> Something went wrong!
</x-alert>
```

不過，從 Laravel 9.x 開始，就可以使用更方便簡潔的語法來指定 Slot 的名稱：

```xml
<x-slot:title>
    Server Error
</x-slot>
```

<a name="checked-selected-blade-directives"></a>

### Checked / Selected Blade 指示詞

*Checked 與 Selected Blade 指示詞由 [Ash Allen](https://github.com/ash-jc-allen) 與 [Taylor Otwell](https://github.com/taylorotwell) 參與貢獻*。

為了方便起見，現在可以使用 `@checked` 指示詞來輕鬆地標示給定 HTML 勾選框為「^[已勾選](Checked)」。這個指示詞會在條件為 `true` 時 Echo `checked`：

```blade
<input type="checkbox"
        name="active"
        value="active"
        @checked(old('active', $user->active)) />
```

類似地，`@selected` 指示詞可用來表示給定 Select 選項應為「^[已選擇](Selected)」：

```blade
<select name="version">
    @foreach ($product->versions as $version)
        <option value="{{ $version }}" @selected(old('version') == $version)>
            {{ $version }}
        </option>
    @endforeach
</select>
```

<a name="bootstrap-5-pagination-views"></a>

### Bootstrap 5 的分頁 View

*Bootstrap 5 分頁 View 由 [Jared Lewis](https://github.com/jrd-lewis) 參與貢獻*。

現在，Laravel 提供了適用於 [Bootstrap 5](https://getbootstrap.com/) 的分頁 View。若要使用這些 View 來替代預設的 Tailwind View，可以在 `App\Providers\AppServiceProvider` 內的 `boot` 方法中呼叫 Paginator 的 `useBootstrapFive` 方法：

    use Illuminate\Pagination\Paginator;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();
    }

<a name="improved-validation-of-nested-array-data"></a>

### 對巢狀陣列資料認證的改進

*針對巢狀陣列輸入的表單驗證改進由 [Steve Bauman](https://github.com/stevebauman) 參與貢獻*。

有時候，在為屬性指派認證規則時，我們可能會想存取給定巢狀陣列項目的值。現在，我們可以使用 `Rule::forEach` 方法來達成。`forEach` 方法接受一個閉包。在認證時，每次迭代陣列屬性都會叫用一次這個閉包，且該閉包會收到屬性值與完整展開的屬性名稱。該閉包應回傳一個陣列，其中包含要指派給陣列元素的認證規則：

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

<a name="laravel-breeze-api"></a>

### Laravel Breeze API 與 Next.js

*Laravel Breeze API Scaffolding 與 Next.js 入門套件由 [Taylor Otwell](https://github.com/taylorotwell) 與 [Miguel Piedrafita](https://twitter.com/m1guelpf) 參與貢獻*。

[Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-next) 入門套件現在有了「API」Scaffolding 模式，且有了完整的 [Next.js](https://nextjs.org) [前端實作](https://github.com/laravel/breeze-next)。如果你想使用 Laravel 作為後端並使用 Laravel Sanctum 的登入 API 給 JavaScript 前端使用的話，就適合這個入門套件 Scaffolding。

<a name="exception-page"></a>

### 改進過的 Ignition 例外頁面

*Ignition 由 [Spatie](https://spatie.be/) 開發*。

Ignition 是由 Spatie 製作的開放原始碼例外偵錯頁面。Ignition 現已被重新設計。Laravel 9.x 隨附了這個全新、改進過的 Ignition，並包含了亮色 / 暗色主題、可自訂的「在編輯器中開啟」功能⋯等。

<p align="center">
<img width="100%" src="https://user-images.githubusercontent.com/483853/149235404-f7caba56-ebdf-499e-9883-cac5d5610369.png"/>
</p>

<a name="improved-route-list"></a>

### 改進過的 `route:list` CLI 輸出

*改進過的 `route:list` CLI 輸出由 [Nuno Maduro](https://github.com/nunomaduro) 參與貢獻*。

Laravel 9.x 更新中的 `route:list` CLI 已大幅改進，能讓你在探索路由定義時獲得全新、漂亮的體驗。

<p align="center">
<img src="https://user-images.githubusercontent.com/5457236/148321982-38c8b869-f188-4f42-a3cc-a03451d5216c.png"/>
</p>

<a name="test-coverage-support-on-artisan-test-Command"></a>

### 使用 Artisan `test` 指令來取得測試覆蓋率

*使用 Artisan `test` 指令來取得測試覆蓋率由 [Nuno Maduro](https://github.com/nunomaduro) 參與貢獻*。

現在，Artisan `test` 指令有了全新的 `--coverage` 選項，能讓你用來確認測試為專案提供了多少的程式碼覆蓋率：

```shell
php artisan test --coverage
```

測試覆蓋率會直接顯示在 CLI 輸出中。

<p align="center">
<img width="100%" src="https://user-images.githubusercontent.com/5457236/150133237-440290c2-3538-4d8e-8eac-4fdd5ec7bd9e.png"/>
</p>

此外，若想指定測試覆蓋率的最小百分比值，可以使用 `--min` 選項。當沒滿足最小值時，測試套件就會執行失敗：

```shell
php artisan test --coverage --min=80.3
```

<p align="center">
<img width="100%" src="https://user-images.githubusercontent.com/5457236/149989853-a29a7629-2bfa-4bf3-bbf7-cdba339ec157.png"/>
</p>

<a name="soketi-echo-server"></a>

### Soketi Echo 伺服器

*Soketi Echo 伺服器由 [Alex Renoki](https://github.com/rennokki) 開發*。

雖然這個功能不侷限於 Laravel 9.x 使用，單 Laravel 也協助了 Soketi —— 使用 Node.js 撰寫的相容於 [Laravel Echo](/docs/{{version}}/broadcasting) 的 Web Socket 伺服器 ——提供說明文件。Soketi 提供了良好且開源的 Pusher 與 Ably 替代方案，可供偏好自行管理 Web Socket 伺服器的專案使用。

更多關於 Soketi 的資訊，請參考[廣播說明文件](/docs/{{version}}/broadcasting)與 [Soketi 的說明文件](https://docs.soketi.app/)。

<a name="improved-collections-ide-support"></a>

### 改進了 IDE 對 Collection 的支援

*針對 Collection 的 IDE 支援改進由 [Nuno Maduro](https://github.com/nunomaduro) 參與貢獻*。

Laravel 9.x 在 Collection 元件上新增了改進過的、「^[泛型](Generic)」風格的型別定義，提升了對 IDE 與靜態檢查的支援。如 [PHPStorm](https://blog.jetbrains.com/phpstorm/2021/12/phpstorm-2021-3-release/#support_for_future_laravel_collections) 等 IDE 或 [PHPStan](https://phpstan.org) 等靜態檢查工具現在可以原生地更理解 Laravel Collection 了。

<p align="center">
<img width="100%" src="https://user-images.githubusercontent.com/5457236/151783350-ed301660-1e09-44c1-b549-85c6db3f078d.gif"/>
</p>

<a name="new-helpers"></a>

### 新的輔助函式

Laravel 9.x 提供了兩個新的方便輔助函式，可以讓你在你自己的專案內使用。

<a name="new-helpers-str"></a>

#### `str`

`str` 會為給定的字串回傳一個 `Illuminate\Support\Stringable`。這個函式與 `Str::of` 方法等價：

    $string = str('Taylor')->append(' Otwell');
    
    // 'Taylor Otwell'

若沒有提供引數給 `str` 函式，則 `str` 會回傳一個 `Illuminate\Support\Str` 的實體：

    $snake = str()->snake('LaravelFramework');
    
    // 'laravel_framework'

<a name="new-helpers-to-route"></a>

#### `to_route`

`to_route` 方法會產生一個跳轉到給定命名路由的重新導向 HTTP 回應，讓我們能在路由與 Controller 中以更富語意的方法跳轉到命名路由：

    return to_route('users.show', ['user' => 1]);

若有需要，也可以傳入一個用於跳轉的 HTTP 狀態碼以及一些額外的回應標頭作為 to_route 方法的第三與第四個引數：

    return to_route('users.show', ['user' => 1], 302, ['X-Framework' => 'Laravel']);
