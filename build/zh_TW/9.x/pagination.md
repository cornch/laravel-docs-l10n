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

To illustrate the differences between offset pagination and cursor pagination, let's examine some example SQL queries. Both of the following queries will both display the "second page" of results for a `users` table ordered by `id`:

```sql
# Offset Pagination...
select * from users order by id asc limit 15 offset 15;

# Cursor Pagination...
select * from users where id > 15 order by id asc limit 15;
```

The cursor pagination query offers the following advantages over offset pagination:

- For large data-sets, cursor pagination will offer better performance if the "order by" columns are indexed. This is because the "offset" clause scans through all previously matched data.

- For data-sets with frequent writes, offset pagination may skip records or show duplicates if results have been recently added to or deleted from the page a user is currently viewing.

However, cursor pagination has the following limitations:

- Like `simplePaginate`, cursor pagination can only be used to display "Next" and "Previous" links and does not support generating links with page numbers.

- It requires that the ordering is based on at least one unique column or a combination of columns that are unique. Columns with `null` values are not supported.

- Query expressions in "order by" clauses are supported only if they are aliased and added to the "select" clause as well.

<a name="manually-creating-a-paginator"></a>

### Manually Creating A Paginator

Sometimes you may wish to create a pagination instance manually, passing it an array of items that you already have in memory. You may do so by creating either an `Illuminate\Pagination\Paginator`, `Illuminate\Pagination\LengthAwarePaginator` or `Illuminate\Pagination\CursorPaginator` instance, depending on your needs.

The `Paginator` and `CursorPaginator` classes do not need to know the total number of items in the result set; however, because of this, these classes do not have methods for retrieving the index of the last page. The `LengthAwarePaginator` accepts almost the same arguments as the `Paginator`; however, it requires a count of the total number of items in the result set.

In other words, the `Paginator` corresponds to the `simplePaginate` method on the query builder, the `CursorPaginator` corresponds to the `cursorPaginate` method, and the `LengthAwarePaginator` corresponds to the `paginate` method.

> {note} When manually creating a paginator instance, you should manually "slice" the array of results you pass to the paginator. If you're unsure how to do this, check out the [array_slice](https://secure.php.net/manual/en/function.array-slice.php) PHP function.


<a name="customizing-pagination-urls"></a>

### Customizing Pagination URLs

By default, links generated by the paginator will match the current request's URI. However, the paginator's `withPath` method allows you to customize the URI used by the paginator when generating links. For example, if you want the paginator to generate links like `http://example.com/admin/users?page=N`, you should pass `/admin/users` to the `withPath` method:

    use App\Models\User;
    
    Route::get('/users', function () {
        $users = User::paginate(15);
    
        $users->withPath('/admin/users');
    
        //
    });

<a name="appending-query-string-values"></a>

#### Appending Query String Values

You may append to the query string of pagination links using the `appends` method. For example, to append `sort=votes` to each pagination link, you should make the following call to `appends`:

    use App\Models\User;
    
    Route::get('/users', function () {
        $users = User::paginate(15);
    
        $users->appends(['sort' => 'votes']);
    
        //
    });

You may use the `withQueryString` method if you would like to append all of the current request's query string values to the pagination links:

    $users = User::paginate(15)->withQueryString();

<a name="appending-hash-fragments"></a>

#### Appending Hash Fragments

If you need to append a "hash fragment" to URLs generated by the paginator, you may use the `fragment` method. For example, to append `#users` to the end of each pagination link, you should invoke the `fragment` method like so:

    $users = User::paginate(15)->fragment('users');

<a name="displaying-pagination-results"></a>

## Displaying Pagination Results

When calling the `paginate` method, you will receive an instance of `Illuminate\Pagination\LengthAwarePaginator`, while calling the `simplePaginate` method returns an instance of `Illuminate\Pagination\Paginator`. And, finally, calling the `cursorPaginate` method returns an instance of `Illuminate\Pagination\CursorPaginator`.

These objects provide several methods that describe the result set. In addition to these helpers methods, the paginator instances are iterators and may be looped as an array. So, once you have retrieved the results, you may display the results and render the page links using [Blade](/docs/{{version}}/blade):

```blade
<div class="container">
    @foreach ($users as $user)
        {{ $user->name }}
    @endforeach
</div>

{{ $users->links() }}
```

The `links` method will render the links to the rest of the pages in the result set. Each of these links will already contain the proper `page` query string variable. Remember, the HTML generated by the `links` method is compatible with the [Tailwind CSS framework](https://tailwindcss.com).

<a name="adjusting-the-pagination-link-window"></a>

### Adjusting The Pagination Link Window

When the paginator displays pagination links, the current page number is displayed as well as links for the three pages before and after the current page. Using the `onEachSide` method, you may control how many additional links are displayed on each side of the current page within the middle, sliding window of links generated by the paginator:

```blade
{{ $users->onEachSide(5)->links() }}
```

<a name="converting-results-to-json"></a>

### Converting Results To JSON

The Laravel paginator classes implement the `Illuminate\Contracts\Support\Jsonable` Interface contract and expose the `toJson` method, so it's very easy to convert your pagination results to JSON. You may also convert a paginator instance to JSON by returning it from a route or controller action:

    use App\Models\User;
    
    Route::get('/users', function () {
        return User::paginate();
    });

The JSON from the paginator will include meta information such as `total`, `current_page`, `last_page`, and more. The result records are available via the `data` key in the JSON array. Here is an example of the JSON created by returning a paginator instance from a route:

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
                // Record...
            },
            {
                // Record...
            }
       ]
    }

<a name="customizing-the-pagination-view"></a>

## Customizing The Pagination View

By default, the views rendered to display the pagination links are compatible with the [Tailwind CSS](https://tailwindcss.com) framework. However, if you are not using Tailwind, you are free to define your own views to render these links. When calling the `links` method on a paginator instance, you may pass the view name as the first argument to the method:

```blade
{{ $paginator->links('view.name') }}

<!-- Passing additional data to the view... -->
{{ $paginator->links('view.name', ['foo' => 'bar']) }}
```

However, the easiest way to customize the pagination views is by exporting them to your `resources/views/vendor` directory using the `vendor:publish` command:

```shell
php artisan vendor:publish --tag=laravel-pagination
```

This command will place the views in your application's `resources/views/vendor/pagination` directory. The `tailwind.blade.php` file within this directory corresponds to the default pagination view. You may edit this file to modify the pagination HTML.

If you would like to designate a different file as the default pagination view, you may invoke the paginator's `defaultView` and `defaultSimpleView` methods within the `boot` method of your `App\Providers\AppServiceProvider` class:

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Pagination\Paginator;
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

### Using Bootstrap

Laravel includes pagination views built using [Bootstrap CSS](https://getbootstrap.com/). To use these views instead of the default Tailwind views, you may call the paginator's `useBootstrapFour` or `useBootstrapFive` methods within the `boot` method of your `App\Providers\AppServiceProvider` class:

    use Illuminate\Pagination\Paginator;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();
        Paginator::useBootstrapFour();
    }

<a name="paginator-instance-methods"></a>

## Paginator / LengthAwarePaginator Instance Methods

Each paginator instance provides additional pagination information via the following methods:

| Method | Description |
| --- | --- |
| `$paginator->count()` | Get the number of items for the current page. |
| `$paginator->currentPage()` | Get the current page number. |
| `$paginator->firstItem()` | Get the result number of the first item in the results. |
| `$paginator->getOptions()` | Get the paginator options. |
| `$paginator->getUrlRange($start, $end)` | Create a range of pagination URLs. |
| `$paginator->hasPages()` | Determine if there are enough items to split into multiple pages. |
| `$paginator->hasMorePages()` | Determine if there are more items in the data store. |
| `$paginator->items()` | Get the items for the current page. |
| `$paginator->lastItem()` | Get the result number of the last item in the results. |
| `$paginator->lastPage()` | Get the page number of the last available page. (Not available when using `simplePaginate`). |
| `$paginator->nextPageUrl()` | Get the URL for the next page. |
| `$paginator->onFirstPage()` | Determine if the paginator is on the first page. |
| `$paginator->perPage()` | The number of items to be shown per page. |
| `$paginator->previousPageUrl()` | Get the URL for the previous page. |
| `$paginator->total()` | Determine the total number of matching items in the data store. (Not available when using `simplePaginate`). |
| `$paginator->url($page)` | Get the URL for a given page number. |
| `$paginator->getPageName()` | Get the query string variable used to store the page. |
| `$paginator->setPageName($name)` | Set the query string variable used to store the page. |

<a name="cursor-paginator-instance-methods"></a>

## Cursor Paginator Instance Methods

Each cursor paginator instance provides additional pagination information via the following methods:

| Method | Description |
| --- | --- |
| `$paginator->count()` | Get the number of items for the current page. |
| `$paginator->cursor()` | Get the current cursor instance. |
| `$paginator->getOptions()` | Get the paginator options. |
| `$paginator->hasPages()` | Determine if there are enough items to split into multiple pages. |
| `$paginator->hasMorePages()` | Determine if there are more items in the data store. |
| `$paginator->getCursorName()` | Get the query string variable used to store the cursor. |
| `$paginator->items()` | Get the items for the current page. |
| `$paginator->nextCursor()` | Get the cursor instance for the next set of items. |
| `$paginator->nextPageUrl()` | Get the URL for the next page. |
| `$paginator->onFirstPage()` | Determine if the paginator is on the first page. |
| `$paginator->onLastPage()` | Determine if the paginator is on the last page. |
| `$paginator->perPage()` | The number of items to be shown per page. |
| `$paginator->previousCursor()` | Get the cursor instance for the previous set of items. |
| `$paginator->previousPageUrl()` | Get the URL for the previous page. |
| `$paginator->setCursorName()` | Set the query string variable used to store the cursor. |
| `$paginator->url($cursor)` | Get the URL for a given cursor instance. |
