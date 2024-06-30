---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/115/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:15:00Z'
---

# 資料庫：分頁

- [簡介](#introduction)
- [基礎用法](#basic-usage)
   - [為 Query Builder 的結果分頁](#paginating-query-builder-results)
   - [為 Eloquent 的結果分頁](#paginating-eloquent-results)
   - [Cursor Pagination](#cursor-pagination)
   - [手動建立 Paginator](#manually-creating-a-paginator)
   - [自訂 URL](#customizing-pagination-urls)
- [顯示 Pagination 的結果](#displaying-pagination-results)
   - [調整 Pagination Link Window](#adjusting-the-pagination-link-window)
   - [將結果轉為 JSON](#converting-results-to-json)
- [自訂 Pagination 的 View](#customizing-the-pagination-view)
   - [使用 Bootstrap](#using-bootstrap)
- [Paginator 與 LengthAwarePaginator 實體的方法](#paginator-instance-methods)
- [Cursor Paginator 實體的方法](#cursor-paginator-instance-methods)

<a name="introduction"></a>

## 簡介

在其他框架中，要進行分頁非常麻煩。我們希望在 Laravel 中可以非常輕鬆地做出分頁功能。Laravel 的 ^[Paginator](分頁程式)與 [Query Builder](/docs/{{version}}/queries) 以及 [Eloquent ORM](/docs/{{version}}/eloquent) 都進行了整合，不需要進行任何設定就能非常方便輕鬆地為資料庫內的資料進行分頁。

預設情況下，Paginator 產生的 HTML 相容於 [Tailwind CSS](https://tailwindcss.com/)。不過，Laravel 也有提供 Bootstrap Pagination 的支援。

<a name="tailwind-jit"></a>

#### Tailwind JIT

若要將 Laravel 的預設 Tailwind Pagination View 與 Tailwind JIT Engine 搭配使用，則請確保專案的 `tailwind.config.js` 中，`content` 索引鍵有參照到 Laravel 的 Pagination View，以避免 Pagination View 中的 Tailwind Class 被清除：

```js
content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
],
```

<a name="basic-usage"></a>

## 基礎用法

<a name="paginating-query-builder-results"></a>

### 為 Query Builder 的結果進行分頁

要將資料進行分頁有許多方法。最簡單的方法就是在 [Query Builder](/docs/{{version}}/queries) 或 [Eloquent query](/docs/{{version}}/eloquent) 上使用 `paginate` 方法。`paginate` 方法會自動依照使用者額目前正在檢視的頁面來設定查詢的「LIMIT」與「OFFSET」。預設情況下，會使用 HTTP Request 上的 `page` Query String 引數來偵測目前的頁面。Laravel 會自動偵測這個值，並且在 Paginator 所產生的連結中也會自動插入這個值。

在這個範例中，傳給 `paginate` 方法的唯一一個引數為要「^[每頁](Per Page)」要顯示的項目數。在這個例子中，我們來指定每頁要顯示 `15` 筆資料：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\DB;
    
    class UserController extends Controller
    {
        /**
         * Show all application users.
         *
         * @return \Illuminate\Http\Response
         */
        public function index()
        {
            return view('user.index', [
                'users' => DB::table('users')->paginate(15)
            ]);
        }
    }

<a name="simple-pagination"></a>

#### Simple Pagination

`paginate` 方法會在從資料庫中取得資料前，先計算該查詢所包含的資料數。要這麼做 Paginator 才知道這些資料總共有多少頁。不過，如果不打算在 UI 上顯示總頁數，那就不需要去計算資料數。

因此，如果我們只需要在網站 UI 上顯示「上一頁」與「下一頁」按鈕，則可以使用 `simplePaginate` 方法來執行單一、有效率的查詢：

    $users = DB::table('users')->simplePaginate(15);

<a name="paginating-eloquent-results"></a>

### 為 Eloquent 查詢結果進行分頁

我們也可以為 [Eloquent](/docs/{{version}}/eloquent) 的查詢結果進行分頁。在這個例子中，我們會為 `App\Models\User` Model 進行分頁，並在每頁中顯示 15 筆資料。在程式碼中可以看到，Eloquent 分頁的語法幾乎與 Query Builder 的語法相同：

    use App\Models\User;
    
    $users = User::paginate(15);

當然，我們也可以在呼叫 `paginate` 方法前先在查詢上設定其他的查詢條件，如 `where` 子句：

    $users = User::where('votes', '>', 100)->paginate(15);

我們也可以在 Eloquent Model 上使用 `simplePaginate` 方法進行分頁：

    $users = User::where('votes', '>', 100)->simplePaginate(15);

類似地，也可以使用 `cursorPaginate` 來以 Cursor 為 Eloquent Model 進行分頁：

    $users = User::where('votes', '>', 100)->cursorPaginate(15);

<a name="multiple-paginator-instances-per-page"></a>

#### 在同一頁中包含多個 Paginator 實體

在網站中，有時候我們會需要在同一頁中顯示兩個不同的 Paginator。不過，如果這兩個 Paginator 實體都使用 `page` Query String 引數來保存目前頁碼的話，則這兩個 Paginator 會衝突。為了解決這樣的衝突，我們可以使用 `paginate`、`simplePaginate`、與 `cursorPaginate` 方法的第三個引數來指定用來保存該 Paginator 頁碼的 Query String 參數：

    use App\Models\User;
    
    $users = User::where('votes', '>', 100)->paginate(
        $perPage = 15, $columns = ['*'], $pageName = 'users'
    );

<a name="cursor-pagination"></a>

### 使用 Cursor 來分頁

`paginate` 與 `simplePaginate` 會使用 SQL 的「Offset」子句來建立查詢，而使用 Cursor 的分頁則會建立一個「Where」子句，該子句會在查詢中用來排序的欄位上進行比較。因此，在 Laravel 所有的分頁方法中，對於資料效能而言，使用 Cursor 的分頁是最有效率的。對於大量的資料、或是可以「無限」往下滑的 UI 來說，就特別適合使用這個方法。

與使用 Offset 的分頁方式不同。使用 Offset 時，Paginator 所產生的 URL 中，Query String 內會包含頁碼。而使用 Cursor 的分頁方式則會在 Query String 中包含一個「Cursor」字串。這個 Cursor 是一個經過編碼的字串，該字串用來表示下一頁的分頁查詢應從哪個地方開始分頁，以及分頁的方向為何：

```nothing
http://localhost/users?cursor=eyJpZCI6MTUsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0
```

我們可以使用 Query Builder 所提供的 `cursorPaginate` 方法來建立使用 Cursor 的 Paginator 實體。該方法會回傳一個 `Illuminate\Pagination\CursorPaginator` 的實體：

    $users = DB::table('users')->orderBy('id')->cursorPaginate(15);

取得 Cursor Paginator 實體後，就可以像使用 `paginate` 與 `simplePaginate` 方法一樣[顯示分頁結果](#displaying-pagination-results)。有關 Cursor Paginator 上所提供的實體方法之更多資訊，請參考 [Cursor Paginator 實體方法的說明文件](#cursor-paginator-instance-methods)。

> {note} 查詢中功能必須要有「Order By」子句，才可使用 Cursor 的分頁。

<a name="cursor-vs-offset-pagination"></a>

#### Cursor 與 Offset Pagination 的比較

為了說明使用 Offset 的 Pagination 與使用 Cursor 的 Pagination 間有何差異，讓我們先來看看一個範例的 SQL 查詢。不管使用下面這兩個查詢中的哪個查詢，都會顯示以 `id` 排列 `users` 資料表時，「第二頁」的資料：

```sql
# 使用 Offset 的 Pagination...
select * from users order by id asc limit 15 offset 15;

# 使用 Cursor 的 Pagination...
select * from users where id > 15 order by id asc limit 15;
```

比起使用 Offset 的 Pagination，使用 Cursor 的 Pagination 有下列優點：

- 當資料量龐大時，若「Order By」的欄位有索引，則使用 Cursor 的 Pagination 會比較有效率。這是因為，「Offset」子句會先掃描所有先前已經配對的資料。
- 如果這些資料很常寫入的話，當使用者在件事頁面時，若在這個頁面中有新增或刪除資料，使用 Offset 的 Pagination 可能會跳過或重複顯示某些資料。

不過，使用 Cursor 的 Pagination 也有下列限制：

- 與 `simplePaginate` 類似，使用 Cursor 的 Pagination 只能顯示「下一頁」與「上一頁」的連結，無法產生頁碼連結。
- 在使用 Cursor 的 Pagination 中，必須至少以 1 個不重複欄位排序，或是以多個組合起來不重複的欄位進行排序。不支援有 `null` 值的欄位。
- 若要在「Order By」子句中包含運算式，則必須先將這些運算式加到「Select」子句內，並設定^[別名](Alias)後以別名來在「Order By」中使用。

<a name="manually-creating-a-paginator"></a>

### 手動建立 Paginator

有時候，我們會需要手動建立 Pagination 實體，並手動傳入記憶體中已有的值。若要手動建立 Pagination，則可依照需求手動建立 `Illuminate\Pagination\Paginator`、`Illuminate\Pagination\LengthAwarePaginator`、或 `Illuminate\Pagination\CursorPaginator` 的實體。

使用 `Paginator` 與 `CursorPaginator` 類別時，這兩個類別不需要知道資料的總數。因此，在這兩個類別上也沒有能取得最後一頁頁碼的方法。`LengthAwarePaginator` 接受的引數則幾乎與 `Paginator` 相同，不過，`LengthAwarePaginator` 必須要知道資料的總數。

換句話說，`Paginator` 對應 Query Builder 上的 `simplePaginate` 方法，而 `CursorPaginator` 則是對應 `cursorPaginate` 方法，`LengthAwarePaginator` 對應 `paginate` 方法。

> {note} 手動建立 Paginator 實體時，應「切割 - Slice」要傳給 Paginator 的結果陣列。如果不知道要如何切割陣列，請參考 [array_slice](https://secure.php.net/manual/en/function.array-slice.php) PHP 函式。

<a name="customizing-pagination-urls"></a>

### 自訂分頁的 URL

預設情況下，Paginator 會產生與目前 Request 網址相同的 URI。不過，只要使用 Paginator 的 `withPath` 方法，我們就能自訂 Paginator 在產生連結時要使用的 URI。舉例來說，若我們要產生像 `http://example.com/admin/users?page=N` 這樣的連結，則我們需要將 `/admin/users` 傳給 `withPath` 方法：

    use App\Models\User;
    
    Route::get('/users', function () {
        $users = User::paginate(15);
    
        $users->withPath('/admin/users');
    
        //
    });

<a name="appending-query-string-values"></a>

#### 加上 Query String 值

可以使用 `appends` 方法來將 Query String 加到分頁連結的最後面。舉例來說，若要在每個分頁連結後方都加上 `sort=votes`，則應這樣呼叫 `appends`：

    use App\Models\User;
    
    Route::get('/users', function () {
        $users = User::paginate(15);
    
        $users->appends(['sort' => 'votes']);
    
        //
    });

若想將目前 Request 中所有的 Query String 值都加到分頁連結後，請使用 `withQueryString` 方法：

    $users = User::paginate(15)->withQueryString();

<a name="appending-hash-fragments"></a>

#### 附加 Hash Fragment

若想在 Paginator 產生的網址後方加上「Hash Fragment」，請使用 `fragment` 方法。舉例來說，若要在每個分頁鏈接後方加上 `#users`，則請像這樣叫用 `fragment` 方法：

    $users = User::paginate(15)->fragment('users');

<a name="displaying-pagination-results"></a>

## 顯示分頁結果

呼叫 `paginate` 方法時，該方法會回傳 `Illuminate\Pagination\LengthAwarePaginator` 的實體，而呼叫 `simplePaginate` 方法時，則會回傳 `Illuminate\Pagination\Paginator` 的實體。最後，當呼叫 `cursorPaginate` 方法時，會回傳 `Illuminate\Pagination\CursorPaginator` 的實體。

這些物件都提供了各種用來描述分頁結果資料的方法。出了這些輔助方法外，Paginator 實體也是迭代器，所以可以像陣列一樣，以迴圈存取 Paginator 實體。因此，取得結果後，我們就可以使用 [Blade](/docs/{{version}}/blade) 來顯示結果並轉譯出頁面的連結：

```html
<div class="container">
    @foreach ($users as $user)
        {{ $user->name }}
    @endforeach
</div>

{{ $users->links() }}
```

`links` 方法會將分頁結果中其他頁面的連結轉譯出來。轉譯出來的這些連結都會包含適當的 `page` Query String 變數。請記得，由 `links` 方法所產生的 HTML 連結相容於 [Tailwind CSS 框架](https://tailwindcss.com)。

<a name="adjusting-the-pagination-link-window"></a>

### 調整分頁連結的 Window

Paginator 在顯示分頁連結時，會顯示目前的頁碼以及該頁碼兩側各三頁的連結。只要使用 `onEachSide` 方法，就能控制 Paginator 在產生連結時目前頁碼的兩側各要顯示多少頁：

    {{ $users->onEachSide(5)->links() }}

<a name="converting-results-to-json"></a>

### 將分頁結果轉為 JSON

Laravel 的 Paginator 類別實作了 `Illuminate\Contracts\Support\Jsonable` 介面 Contract，並提供了一個 `toJson` 方法。因此，要將分頁結果轉為 JSON 非常簡單。我們也可以在 Route 或 Controller 動作中回傳 Paginator 實體來將 Paginator 實體轉為 JSON：

    use App\Models\User;
    
    Route::get('/users', function () {
        return User::paginate();
    });

Paginator 轉換出來的 JSON 中會包含一些^[詮釋](Meta)資訊，如 `total`、`current_page`、`last_page`……等。在 JSON 陣列中，分頁結果的資料放在 `data` 索引鍵中。下列為從 Route 中回傳 Paginator 實體所產生的 JSON 範例：

    {
       "total": 50,
       "per_page": 15,
       "current_page": 1,
       "last_page": 4,
       "first_page_url": "http://laravel.app?page=1",
       "last_page_url": "http://laravel.app?page=4",
       "next_page_url": "http://laravel.app?page=2",
       "prev_page_url": null,
       "path": "http://laravel.app",
       "from": 1,
       "to": 15,
       "data":[
            {
                // 資料...
            },
            {
                // 資料...
            }
       ]
    }

<a name="customizing-the-pagination-view"></a>

## 自訂分頁的 View

預設情況下，轉譯出來顯示分頁連結的 View 是相容於 [Tailwind CSS](https://tailwindcss.com) 框架的。不過，若不使用 Tailwind，則也可以自行定義自己的 View 來轉譯這些連結。在 Paginator 實體上呼叫 `links` 方法時，可傳入 View 的名稱作為該方法的第一個引數：

    {{ $paginator->links('view.name') }}
    
    // 傳入額外資料給 View...
    {{ $paginator->links('view.name', ['foo' => 'bar']) }}

不過，要自訂分頁連結最簡單的方法是使用 `vendor:publish` 來將分頁 View 安裝到 `resources/views/vendor` 目錄下：

    php artisan vendor:publish --tag=laravel-pagination

該指令會將分頁的 View 放到專案的 `resources/views/vendor/pagination` 目錄下。該目錄下的 `tailwind.blade.php` 為預設的分頁 View。我們可以編輯該檔案來修改分頁的 HTML。

若想指定用不同的檔案來作為預設的分頁 View，則可在 `App\Providers\AppServiceProvider` 類別的 `boot` 方法內叫用 Paginator 的 `defaultView` 與 `defaultSimpleView` 方法：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Pagination\Paginator;
    use Illuminate\Support\Facades\Blade;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            Paginator::defaultView('view-name');
    
            Paginator::defaultSimpleView('view-name');
        }
    }

<a name="using-bootstrap"></a>

### 使用 Bootstrap

Laravel 也提供了適用於 [Bootstrap CSS](https://getbootstrap.com/) 的分頁 View。若要使用這些 View 來替代預設的 Tailwind View，可以在 `App\Providers\AppServiceProvider` 內的 `boot` 方法中呼叫 Paginator 的 `useBootstrap` 方法：

    use Illuminate\Pagination\Paginator;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
    }

<a name="paginator-instance-methods"></a>

## Paginator / LengthAwarePaginator 實體方法

各個 Paginator 的實體都有提供下列方法，可用來存取額外的資訊：

| 方法 | 說明 |
| --- | --- |
| `$paginator->count()` | 取得目前頁面的項目數。 |
| `$paginator->currentPage()` | 取得目前頁碼。 |
| `$paginator->firstItem()` | 取得結果中第一項的結果編號。 |
| `$paginator->getOptions()` | 取得 Paginator 的選項。 |
| `$paginator->getUrlRange($start, $end)` | 建立一組分頁 URL 的範圍。 |
| `$paginator->hasPages()` | 判斷是否有足夠多的項目可將結果拆分為多頁顯示。 |
| `$paginator->hasMorePages()` | 判斷資料存放空間中是否還有更多項目。 |
| `$paginator->items()` | 取得目前頁面的項目。 |
| `$paginator->lastItem()` | 取得結果中最後一項的結果編號。 |
| `$paginator->lastPage()` | 取得最後一頁的頁碼。(使用 `simplePaginate` 時無本方法。) |
| `$paginator->nextPageUrl()` | 取得下一頁的 URL。 |
| `$paginator->onFirstPage()` | 判斷 Paginator 是否在第一頁。 |
| `$paginator->perPage()` | 每頁顯示的項目數。 |
| `$paginator->previousPageUrl()` | 取得前一頁的 URL。 |
| `$paginator->total()` | 判斷資料存放空間中符合項目的總數。(使用 `simplePaginate` 時無本方法。) |
| `$paginator->url($page)` | 取得給定頁碼的 URL。 |
| `$paginator->getPageName()` | 取得用來存放頁碼的 Query String 變數。 |
| `$paginator->setPageName($name)` | 設定用來存放頁碼的 Query String 變數。 |

<a name="cursor-paginator-instance-methods"></a>

## Cursor Paginator 的實體方法

各個 Cursor Paginator 的實體都有提供下列方法，可用來存取額外的資訊：

| 方法 | 說明 |
| --- | --- |
| `$paginator->count()` | 取得目前頁面的項目數。 |
| `$paginator->cursor()` | 取得目前的 Cursor 實體。 |
| `$paginator->getOptions()` | 取得 Paginator 的選項。 |
| `$paginator->hasPages()` | 判斷是否有足夠多的項目可將結果拆分為多頁顯示。 |
| `$paginator->hasMorePages()` | 判斷資料存放空間中是否還有更多項目。 |
| `$paginator->getCursorName()` | 取得用來存放 Cursor 的 Query String 變數。 |
| `$paginator->items()` | 取得目前頁面的項目。 |
| `$paginator->nextCursor()` | 取得下一組項目的 Cursor 實體。 |
| `$paginator->nextPageUrl()` | 取得下一頁的 URL。 |
| `$paginator->onFirstPage()` | 判斷 Paginator 是否在第一頁。 |
| `$paginator->perPage()` | 每頁顯示的項目數。 |
| `$paginator->previousCursor()` | 取得上一組項目的 Cursor 實體。 |
| `$paginator->previousPageUrl()` | 取得前一頁的 URL。 |
| `$paginator->setCursorName()` | 設定用來存放 Cursor 的 Query String 變數。 |
| `$paginator->url($cursor)` | 取得給定 Cursor 實體的 URL。 |
