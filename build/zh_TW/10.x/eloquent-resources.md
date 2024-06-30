---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/57/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:26:00Z'
---

# Eloquent：API 資源

- [簡介](#introduction)
- [產生 Resource](#generating-resources)
- [概念概覽](#concept-overview)
   - [Resource Collection](#resource-collections)
- [撰寫 Resource](#writing-resources)
   - [資料包裝](#data-wrapping)
   - [分頁](#pagination)
   - [條件式屬性](#conditional-attributes)
   - [條件式關聯](#conditional-relationships)
   - [新增詮釋資料](#adding-meta-data)
- [Resource Response](#resource-responses)

<a name="introduction"></a>

## 簡介

在製作 API 的時候，我們可能會需要一個轉換層來將 Eloquent Model 轉換為實際要回傳給使用者的 JSON 回應。舉例來說，有一些屬性我們只希望特定使用者能看到，其他使用者不能看；或者，我們可能會希望在 Model 的 JSON 呈現上總是包含特定的關聯。Eloquent 的 Resource 類別能讓我們輕鬆自如地將 Model 與 Model Collection 轉換為 JSON。

當然，我們還是可以用 Eloquent Model 上的 `toJson` 方法來將其轉為 JSON。不過，Eloquent Resource 能讓我們對於 Model 與其關聯要怎麼被 JSON 序列化有更大的控制。

<a name="generating-resources"></a>

## 產生 Resource

若要產生 Resource 類別，可以使用 `make:resource` Artisan 指令。預設情況下，Resource 會被放在專案中的 `app/Http/Resources` 目錄。Resource 繼承自 `Illuminate\Http\Resources\Json\JsonResource` 類別：

```shell
php artisan make:resource UserResource
```

<a name="generating-resource-collections"></a>

#### Resource Collection

除了產生能轉換個別 Model 的 Resource 外，也可以產生一個 Resource 來轉換一組包含 Model 的 Collection。這樣以來，就可以在我們的 JSON 回應中包含連結或其他詮釋資訊等與該資源中整個 Collection 相關的資訊。

要建立 Resource Collection，應在建立資源時使用 `--collection` 旗標。或者，也可以在資源名稱後方加上 `Collection`，以讓 Laravel 知道我們要建立的是 Resource Collection。

```shell
php artisan make:resource User --collection

php artisan make:resource UserCollection
```

<a name="concept-overview"></a>

## 概念概覽

> **Note** 這裡提供的是對於 Resource 與 Resource Collection 的高階概覽。我們強烈建議你閱讀本文中的其他段落以深入瞭解 Resource 提供的客製化功能。

在深入瞭解撰寫 Resource 時可用的所有方法前，我們先來用一種高階的方式看看 Laravel 中可以怎麼使用 Resource。Resource 類別代表的是需要被轉換為 JSON 結構的單一 Model。舉例來說，下列是一個簡單的 `UserResource` Resource 類別：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    
    class UserResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }

每個 Resource 類別都有一個 `toArray` 方法，`toArray` 方法回傳一組包含屬性的陣列，當資源從路由或 Controller 方法中作為回應回傳時，這些屬性會被轉為 JSON。

可以注意到，我們直接使用 `$this` 變數來存取 Model 的屬性。這是因為，在存取屬性與方法時，Resource 類別會自動幫我們將這些存取代理 (Proxy) 到底層的 Model，以讓我們能方便地存取。定義好 Resource 之後，就可以從路由或 Controller 中回傳這個 Resource。該 Resource 的建構函式中接受底層的 Model 實體：

    use App\Http\Resources\UserResource;
    use App\Models\User;
    
    Route::get('/user/{id}', function (string $id) {
        return new UserResource(User::findOrFail($id));
    });

<a name="resource-collections"></a>

### Resource Collection

在路由或 Controller 中回傳一組包含 Resource 的 Collection、或是有分頁的回應時，建立 Resource 的時候應使用 Resource 類別提供的 `collection` 方法：

    use App\Http\Resources\UserResource;
    use App\Models\User;
    
    Route::get('/users', function () {
        return UserResource::collection(User::all());
    });

請注意，在回傳 Collection 的同時，這麼做將無法附上額外的詮釋資料。若想自訂 Resource Collection 的回應，可以建立一個專門的 Resource 來代表該 Collection：

```shell
php artisan make:resource UserCollection
```

產生好 Resource Collection 後，就可以輕鬆地定義要被包含在回應中的詮釋資料：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    
    class UserCollection extends ResourceCollection
    {
        /**
         * Transform the resource collection into an array.
         *
         * @return array<int|string, mixed>
         */
        public function toArray(Request $request): array
        {
            return [
                'data' => $this->collection,
                'links' => [
                    'self' => 'link-value',
                ],
            ];
        }
    }

定義好 Resource Collection 後，就可以在路由或 Controller 內回傳這個 Resource Collection：

    use App\Http\Resources\UserCollection;
    use App\Models\User;
    
    Route::get('/users', function () {
        return new UserCollection(User::all());
    });

<a name="preserving-collection-keys"></a>

#### 保留 Collection 的索引鍵

從路由內回傳 Resource Collection 的時候，Laravel 會重設該 Collection 的索引鍵，讓索引鍵按找數字順序排列。不過，可以在 Resource 類別中加上 `preserveKeys` 屬性來讓 Collection 保留其原始的索引鍵：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Resources\Json\JsonResource;
    
    class UserResource extends JsonResource
    {
        /**
         * Indicates if the resource's collection keys should be preserved.
         *
         * @var bool
         */
        public $preserveKeys = true;
    }

`preservedKeys` 屬性設為 `true` 的時，當我們從路由或 Controller 內回傳這個 Collection 的時候，就會保留其中的索引鍵：

    use App\Http\Resources\UserResource;
    use App\Models\User;
    
    Route::get('/users', function () {
        return UserResource::collection(User::all()->keyBy->id);
    });

<a name="customizing-the-underlying-resource-class"></a>

#### 自訂底層的 Resource 類別

一般來說，Laravel 會將 Collection 內的各個結果映射到其單數 (Singular) 的 Resource 類別上，然後再用來填充 Resource Collection 的 `$this->collection`。Laravel 會使用 Collection 的類別名稱去掉 `Collection` 來推測單數 Resource 的名稱。此外，根據使用者的個人偏好，單數 Resource 有可能會以 `Resource` 結尾，也有可能不會。

舉例來說，`UserCollection` 會將給定的 User 實體映射到 `UserResource` 資源上。若要自訂此行為，可以複寫 Resource Collection 的 `$collects` 屬性：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Resources\Json\ResourceCollection;
    
    class UserCollection extends ResourceCollection
    {
        /**
         * The resource that this resource collects.
         *
         * @var string
         */
        public $collects = Member::class;
    }

<a name="writing-resources"></a>

## 撰寫 Resource

> **Note** 若你還未閱讀《[概念概覽](#concept-overview)》，我們強烈建議你在繼續之前先閱讀該段落。

Resource 只負責把給定的 Model 轉換為陣列。因此，每個 Resource 都包含了一個 `toArray` 方法，可用來將 Model 的屬性轉換為對適合用在 API 的陣列，並讓你能在路由或 Controller 內回傳這個陣列：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    
    class UserResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }

定義好 Resource 後，我們就可以直接在路由或 Controller 內將其回傳：

    use App\Http\Resources\UserResource;
    use App\Models\User;
    
    Route::get('/user/{id}', function (string $id) {
        return new UserResource(User::findOrFail($id));
    });

<a name="relationships"></a>

#### 關聯

若要定義想被包含在回應內的關聯資源，可直接將這些關聯加在 Resource 的 `toArray` 方法內。在這個例子中，我們會使用 `PostResource` Resource 的 `collection` 方法來講使用者的部落格貼文加到 Resource 回應內：

    use App\Http\Resources\PostResource;
    use Illuminate\Http\Request;
    
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->posts),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

> **Note** 若只想在關聯已載入的情況下才將這些關聯包含在回應內，請參考[條件式關聯](#conditional-relationships)。

<a name="writing-resource-collections"></a>

#### Resource Collection

Resource 會將單一 Model 轉換為陣列，Resource Collection 則將一組包含 Model 的 Collection 轉換為陣列。不過，並不需要為每個 Model 都應以一個對應的 Resource Collection，因為所有的 Resource 都有提供一個 `collection` 方法，可以讓你即時產生一個特別的 Resource Collection：

    use App\Http\Resources\UserResource;
    use App\Models\User;
    
    Route::get('/users', function () {
        return UserResource::collection(User::all());
    });

不過，若有需要定義與 Collection 一起回傳的詮釋資料 (Meta Data)，就需要定義你自己的 Resource Collection：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    
    class UserCollection extends ResourceCollection
    {
        /**
         * Transform the resource collection into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request): array
        {
            return [
                'data' => $this->collection,
                'links' => [
                    'self' => 'link-value',
                ],
            ];
        }
    }

與單數 Resource 類似，Resource Collection 也可以直接在路由或 Controller 內回傳：

    use App\Http\Resources\UserCollection;
    use App\Models\User;
    
    Route::get('/users', function () {
        return new UserCollection(User::all());
    });

<a name="data-wrapping"></a>

### 資料包裝

預設情況下，當 Resource 回應被轉成 JSON 時，最外層的資源會被包裝在 `data` 索引鍵地下。因此，舉例來說，正常的 Resource Collection 回應會長這樣：

```json
{
    "data": [
        {
            "id": 1,
            "name": "Eladio Schroeder Sr.",
            "email": "therese28@example.com"
        },
        {
            "id": 2,
            "name": "Liliana Mayert",
            "email": "evandervort@example.com"
        }
    ]
}
```

若使用 `data` 以外的其他自訂索引鍵，可以在 Resource 類別內定義一個 `$wrap`屬性：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Resources\Json\JsonResource;
    
    class UserResource extends JsonResource
    {
        /**
         * The "data" wrapper that should be applied.
         *
         * @var string|null
         */
        public static $wrap = 'user';
    }

若不想要包裝最外層的資源，請叫用基礎 `Illuminate\Http\Resources\Json\JsonResource` 類別底下的 `withoutWrapping` 方法。一般來說，應在 `AppServiceProvider` 或其他每個請求都會載入的 [Service Provider](/docs/{{version}}/providers) 內呼叫這個方法：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\ServiceProvider;
    
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
            JsonResource::withoutWrapping();
        }
    }

> **Warning** `withoutWrapping` 方法只會影響最外層的回應。`withoutWrapping` 方法不會移除手動新增到 Resource Collection 內的 `data` 索引鍵。

<a name="wrapping-nested-resources"></a>

#### 包裝巢狀 Resource

對於要如何包裝 Resource 的關聯，開發人員擁有絕對的自由。若想讓所有無論是不是巢狀的 Resource Collection 都被包裝在 `data` 索引鍵內，則可以為每個 Resource 都定義一個 Resource Collection 類別，並以 `data` 索引鍵回傳 Collection。

讀者可能會疑惑：這麼做會不會讓最外層的 Resource 被包裝在 `data` 索引鍵裡兩次？別擔心，Laravel 不會讓你不小心把 Resource 重複包裝的。因此，在轉換 Resource Collection 時，完全不需擔心 Resource Collection 的巢狀層級：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Resources\Json\ResourceCollection;
    
    class CommentsCollection extends ResourceCollection
    {
        /**
         * Transform the resource collection into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request): array
        {
            return ['data' => $this->collection];
        }
    }

<a name="data-wrapping-and-pagination"></a>

#### 資料包裝與分頁

當使用 Resource 回應來回船分頁過的 Collection 時，就算有呼叫過 `withoutWrapper` 方法，Laravel 也會將這些 Resource 資料放在 `data` 索引鍵裡。這是因為，所有經過分頁的回應都會包含如 `meta` 與 `links` 等有關 Paginator 狀態的資訊：

```json
{
    "data": [
        {
            "id": 1,
            "name": "Eladio Schroeder Sr.",
            "email": "therese28@example.com"
        },
        {
            "id": 2,
            "name": "Liliana Mayert",
            "email": "evandervort@example.com"
        }
    ],
    "links":{
        "first": "http://example.com/users?page=1",
        "last": "http://example.com/users?page=1",
        "prev": null,
        "next": null
    },
    "meta":{
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://example.com/users",
        "per_page": 15,
        "to": 10,
        "total": 10
    }
}
```

<a name="pagination"></a>

### 分頁

可以將 Laravel 的 Paginator 實體傳入 Resource 的 `collection` 方法或自訂 Resource Collection 中：

    use App\Http\Resources\UserCollection;
    use App\Models\User;
    
    Route::get('/users', function () {
        return new UserCollection(User::paginate());
    });

所有經過分頁的回應都會包含 `meta` 與 `links` 等關於 Paginator 狀態的資訊：

```json
{
    "data": [
        {
            "id": 1,
            "name": "Eladio Schroeder Sr.",
            "email": "therese28@example.com"
        },
        {
            "id": 2,
            "name": "Liliana Mayert",
            "email": "evandervort@example.com"
        }
    ],
    "links":{
        "first": "http://example.com/users?page=1",
        "last": "http://example.com/users?page=1",
        "prev": null,
        "next": null
    },
    "meta":{
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://example.com/users",
        "per_page": 15,
        "to": 10,
        "total": 10
    }
}
```

<a name="customizing-the-pagination-information"></a>

#### 自訂分頁的資訊

若想自定分頁 Response 中 `links` 或 `meta` 索引鍵內所包含的資訊，可在 Resource 上定義 `paginationInformation` 方法。該方法會收到一個 `$paginated` 資料，以及一個陣列的 `$default` 資訊。`$default` 是一個包含 `links` 與 `meta` 索引鍵的陣列：

    /**
     * Customize the pagination information for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array $paginated
     * @param  array $default
     * @return array
     */
    public function paginationInformation($request, $paginated, $default)
    {
        $default['links']['custom'] = 'https://example.com';
    
        return $default;
    }

<a name="conditional-attributes"></a>

### 有條件的屬性

有時候，我們只想在滿足特定條件的時候才在 Resource 回應內包含某個屬性。舉例來說，我們或許會只在目前使用者是「管理員 (Administrator)」時才將某個值包含在回應內。Laravel 為這種情況提供了一個輔助函式。可以使用 `when` 來有條件地在 Resource 回應內新增屬性：

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'secret' => $this->when($request->user()->isAdmin(), 'secret-value'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

在這個例子中，只有在已登入使用者的 `idAdmin` 方法回傳 `true` 時，最終的 Resource 回應內才會包含 `secret` 索引鍵。若 `isAdmin` 方法回傳 `false`，則在 Resource 回應回傳給用戶端之前，`secret` 索引鍵就會被移除。使用 `when` 方法就可以用一種語意化的方法來定義 Resource，而不需要建立陣列時使用條件式陳述式。

`when` 的第二個引數也可以傳入一個閉包。可以使用這個閉包來只在條件為 `true` 時計算結果值：

    'secret' => $this->when($request->user()->isAdmin(), function () {
        return 'secret-value';
    }),

`whenHas` 方法可用來在當底層 Model 內真的有包含某個屬性時將該屬性包含進來：

    'name' => $this->whenHas('name'),

此外，當屬性不為 null 時，也可以使用 `whereNotNull` 來在 Resource Response 中包含某個屬性：

    'name' => $this->whenNotNull($this->name),

<a name="merging-conditional-attributes"></a>

#### 合併有條件的屬性

有時候，我們可能會有數個屬性想依據相同的條件來被包含在 Resource 回應中。在這種情況下，可以使用 `mergeWhen` 方法來只在給定條件為 `true` 時將這些屬性包含在回應中：

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            $this->mergeWhen($request->user()->isAdmin(), [
                'first-secret' => 'value',
                'second-secret' => 'value',
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

跟剛才一樣，如果給定條件為 `false`，則這些屬性將在傳回給用戶端前被從 Resource 回應中移除。

> **Warning** `mergeWhen` 方法不可使用在組合使用數字與字串索引鍵的陣列上。此外，`mergeWhen` 也不能使用在數字索引鍵沒有連續的陣列上。

<a name="conditional-relationships"></a>

### 有條件的關聯

除了有條件地載入屬性外，我們還能依據關聯是否已載入到 Model 上來有條件地將關聯包含在 Resource 回應中。這樣一來，我們的 Controller 就能決定要載入哪些關聯，而 Resource 就可以輕鬆地在有載入這些關聯的時候才將這些關聯包含在回應中。最後，這麼做就能輕鬆地在 Resource 內避免「N+1」查詢。

可以使用 `whenLoaded` 方法來有條件地載入關聯。為了避免不必要地載入關聯，這個方法接受關聯的名稱，而非關聯物件本身：

    use App\Http\Resources\PostResource;
    
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

在這個例子中，若尚未載入關聯，則在回傳給用戶端前 `posts` 索引鍵就會被移除。

<a name="conditional-relationship-counts"></a>

#### 條件式關聯的計數

除了可有條件地包含關聯外，我們也可以依據關聯的計數是否已載入到 Model 上來將關聯的「計數 (Count)」包含到 Resource Response 上：

    new UserResource($user->loadCount('posts'));

`whenCounted` 方法可用來有條件地在 Resource Response 上包含關聯的計數。使用該方法也能避免在關聯沒有計數時將其包含進來：

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts_count' => $this->whenCounted('posts'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

在這個範例中，若 `posts` 關聯的計數未載入，則 `posts_count` 索引鍵就會在傳給用戶端前被移除。

其他的匯總類型，如 `avg`, `sum`, min`與`max`也可以使用`whenAggregated` 方法來有條件地載入：

```php
'words_avg' => $this->whenAggregated('posts', 'words', 'avg'),
'words_sum' => $this->whenAggregated('posts', 'words', 'sum'),
'words_min' => $this->whenAggregated('posts', 'words', 'min'),
'words_max' => $this->whenAggregated('posts', 'words', 'max'),
```

<a name="conditional-pivot-information"></a>

#### 有條件的樞紐 (Pivot) 資訊

除了有條件地將關聯資訊加到 Resource 回應內之外，我們還能使用 `whenPivotLoaded` 方法來有條件地包含 Many-to-many 關聯的中介資料表中的資料庫。`whenPivotLoaded` 方法的第一個引數為樞紐資料表的名稱，第二個引數則為一個閉包，該閉包應回傳當 Model 上有樞紐資訊時要回傳的值：

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'expires_at' => $this->whenPivotLoaded('role_user', function () {
                return $this->pivot->expires_at;
            }),
        ];
    }

若關聯使用[自訂的中介資料表 Model](/docs/{{version}}/eloquent-relationships#defining-custom-intermediate-table-models)，則應講樞紐資料表的實體作為第一個引數傳給 `whenPivotLoaded` 方法：

    'expires_at' => $this->whenPivotLoaded(new Membership, function () {
        return $this->pivot->expires_at;
    }),

若中介資料表使用 `pivot` 以外的存取方法，則可以使用 `whenPivotLoadesAs` 方法：

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'expires_at' => $this->whenPivotLoadedAs('subscription', 'role_user', function () {
                return $this->subscription->expires_at;
            }),
        ];
    }

<a name="adding-meta-data"></a>

### 新增詮釋資料

有的 JSON API 標準中要求要有 Resource 與 Resource Collection 回應的詮釋資料 (Meta Data)。通常包含如 Resource 或關聯 Resource 的連結 (`links`)、或是有關 Resource 本身的詮釋資料等。若想回傳關於 Resource 的額外詮釋資料，請將這些資料包含在 `toArray` 方法內。舉例來說，我們可能會想在轉換 Resource Collection 時包含 `link` 資訊：

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }

當從 Resource 內回傳額外的詮釋資料時，不需擔心是否會不小心複寫在回傳經過分頁的資料時 Laravel 自動新增的 `links` 或 `meta` 等資料。若有定義額外的 `links`，這些 `links` 會跟 Paginator 提供的連結合併在一起。

<a name="top-level-meta-data"></a>

#### 最上層的詮釋資料

有時候，我們可能會想只在當目前 Resource 是回傳的最外層 Resource 時才包含某些詮釋資料。一般來說，這種情況的詮釋資料就是對於回應的詮釋資料。若要定義這種詮釋資料，可在 Resource 類別內加上一個 `with` 方法。這個方法應回傳一組包含詮釋資料的陣列，用以在目前 Resource 是最外層 Resource 時包含在 Resource 回應內：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Resources\Json\ResourceCollection;
    
    class UserCollection extends ResourceCollection
    {
        /**
         * Transform the resource collection into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request): array
        {
            return parent::toArray($request);
        }
    
        /**
         * Get additional data that should be returned with the resource array.
         *
         * @return array<string, mixed>
         */
        public function with(Request $request): array
        {
            return [
                'meta' => [
                    'key' => 'value',
                ],
            ];
        }
    }

<a name="adding-meta-data-when-constructing-resources"></a>

#### 在建立 Resource 時加上詮釋資料

我們也可以在路由或 Controller 內建構 Resource 時加上最上層的資料。 所有 Resource 內都提供了一個 `additional` 方法，可將要加到 Resource 回應內的資料放在陣列中傳給該方法：

    return (new UserCollection(User::all()->load('roles')))
                    ->additional(['meta' => [
                        'key' => 'value',
                    ]]);

<a name="resource-responses"></a>

## Resource 回應

剛才已經讀過，我們可以直接從路由或 Controller 內回傳 Resource：

    use App\Http\Resources\UserResource;
    use App\Models\User;
    
    Route::get('/user/{id}', function (string $id) {
        return new UserResource(User::findOrFail($id));
    });

不過，有的時候我們會需要在回應被傳回用戶端前自訂外連 HTTP 回應。有兩種方法可以自訂外連 HTTP 回應。第一種方法，我們可以將 `response` 方法串連到 Resource 後面。該方法會回傳一個 `Illuminate\Http\JsonResponse` 實體，讓我們能對回應的標頭有完整的控制權：

    use App\Http\Resources\UserResource;
    use App\Models\User;
    
    Route::get('/user', function () {
        return (new UserResource(User::find(1)))
                    ->response()
                    ->header('X-Value', 'True');
    });

或者，也可以在 Resource 裡面定義一個 `withResponse`。這個方法會在該 Resource 是回應中最外層 Resource 時被呼叫：

    <?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    
    class UserResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request): array
        {
            return [
                'id' => $this->id,
            ];
        }
    
        /**
         * Customize the outgoing response for the resource.
         */
        public function withResponse(Request $request, JsonResponse $response): void
        {
            $response->header('X-Value', 'True');
        }
    }
