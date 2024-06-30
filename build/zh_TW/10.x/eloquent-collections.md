---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/51/en-zhtw
progress: 100
updatedAt: '2023-02-11T10:27:00Z'
---

# Eloquent：Collection

- [簡介](#introduction)
- [可用方法](#available-methods)
- [自訂 Collections](#custom-collections)

<a name="introduction"></a>

## 簡介

Eloquent 中，所有回傳多筆 Model 結果的方法都會回傳 `Illuminate\Database\Eloquent\Collection` 的實體，這些方法包含 `get` 方法或是以關聯進行存取時。Eloquent Collection 物件繼承自 Laravel 的[基礎 Collection](/docs/{{version}}/collections)，因此，這些 Collection 也自然繼承了數十種可用來流暢地操作底層 Eloquent Model 陣列的方法。記得看看 Laravel Collection 說明文件來了解這些實用的方法！

所有的 Collection 也可作為迭代器使用，因此你可以很輕鬆地像迭代一般 PHP 陣列一樣迭代這些 Collection：

    use App\Models\User;
    
    $users = User::where('active', 1)->get();
    
    foreach ($users as $user) {
        echo $user->name;
    }

不過，剛才也提到過，Collection 比起陣列來說多了很多更強大的功能。而且，Collection 也提供了多種可通過直觀介面串連使用的各個 map / reduce 操作。舉例來說，我們可以將所有非啟用狀態的 Model 移除，並取得剩餘使用者的名字：

    $names = User::all()->reject(function (User $user) {
        return $user->active === false;
    })->map(function (User $user) {
        return $user->name;
    });

<a name="eloquent-collection-conversion"></a>

#### Eloquent Collection 轉換

雖然大多數的 Eloquent Collection 方法都會回傳新的 Eloquent Collection 實體，但 `collapse`, `flatten`, `flip`, `keys`, `pluck`, 與 `zip` 方法都會回傳[基礎 collection](/docs/{{version}}/collections)實體。同樣地，若 `map` 操作回傳不包含任何 Eloquent Model 的實體，則會回傳基礎 Collection 實體。

<a name="available-methods"></a>

## 可用方法

所有的 Eloquent Collection 都繼承自基礎 [Laravel Collection](/docs/{{version}}/collections#available-methods) 物件。因此，這些 Collection 都繼承了基礎 Collection 類別所提供的所有強大方法。

除此之外，`Illuminate\Database\Eloquent\Collection` 類別還提供了一組可用來處理 Model Collection 的方法。大多數的方法都會回傳 `Illuminate\Database\Eloquent\Collection` 實體。不過，有些如 `modelKeys` 之類的方法則會回傳 `Illuminate\Support\Collection` 實體。

<style>
    .collection-method-list > p {
        columns: 14.4em 1; -moz-columns: 14.4em 1; -webkit-columns: 14.4em 1;
    }

    .collection-method-list a {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .collection-method code {
        font-size: 14px;
    }

    .collection-method:not(.first-collection-method) {
        margin-top: 50px;
    }
</style>

<div class="collection-method-list" markdown="1">

[append](#method-append) [contains](#method-contains) [diff](#method-diff) [except](#method-except) [find](#method-find) [fresh](#method-fresh) [intersect](#method-intersect) [load](#method-load) [loadMissing](#method-loadMissing) [modelKeys](#method-modelKeys) [makeVisible](#method-makeVisible) [makeHidden](#method-makeHidden) [only](#method-only) [setVisible](#method-setVisible) [setHidden](#method-setHidden) [toQuery](#method-toquery) [unique](#method-unique)

</div>

<a name="method-append"></a>

#### `append($attributes)` {.collection-method .first-collection-method}

`append` 方法可用來表示某個屬性應被[附加](/docs/{{version}}/eloquent-serialization#appending-values-to-json)到該 Collection 中的每個 Model 上。可傳入一組屬性陣列或單一屬性給該方法：

    $users->append('team');
    
    $users->append(['team', 'is_admin']);

<a name="method-contains"></a>

#### `contains($key, $operator = null, $value = null)` {.collection-method}

`contains` 方法可用來判斷給定的 Model 實體是否包含在該 Collection 內。該方法可接受主鍵 (Primary Key) 或 Model 實體：

    $users->contains(1);
    
    $users->contains(User::find(1));

<a name="method-diff"></a>

#### `diff($items)` {.collection-method}

`diff` 方法會回傳所有不存在給定 Collection 中的所有 Model：

    use App\Models\User;
    
    $users = $users->diff(User::whereIn('id', [1, 2, 3])->get());

<a name="method-except"></a>

#### `except($keys)` {.collection-method}

`except` 方法會回傳沒有給定主鍵的所有 Model：

    $users = $users->except([1, 2, 3]);

<a name="method-find"></a>

#### `find($key)` {.collection-method}

`find` 方法會回傳主鍵符合給定索引鍵的 Model。若 `$key` 為 Model 實體，則 `find` 會嘗試回傳符合該主鍵的 Model。若 `$key` 為一組索引鍵的陣列，則 `find` 會回傳所有主鍵包含在給定陣列中的 Model：

    $users = User::all();
    
    $user = $users->find(1);

<a name="method-fresh"></a>

#### `fresh($with = [])` {.collection-method}

`fresh` 方法會從資料庫中取得該 Collection 中各個 Model 的最新實體 (Fresh Instance)。此外，若有指定關聯，則會積極式載入 (Eager Loading) 這些關聯：

    $users = $users->fresh();
    
    $users = $users->fresh('comments');

<a name="method-intersect"></a>

#### `intersect($items)` {.collection-method}

`diff` 方法會回傳所有同時存在於給定 Collection 中的所有 Model：

    use App\Models\User;
    
    $users = $users->intersect(User::whereIn('id', [1, 2, 3])->get());

<a name="method-load"></a>

#### `load($relations)` {.collection-method}

`load` 方法會積極式載入 (Eager Loading) Collection 中所有 Model 上的給定關聯：

    $users->load(['comments', 'posts']);
    
    $users->load('comments.author');
    
    $users->load(['comments', 'posts' => fn ($query) => $query->where('active', 1)]);

<a name="method-loadMissing"></a>

#### `loadMissing($relations)` {.collection-method}

`loadMissing` 方法會在該 Collection 中所有 Model 的給定關聯尚未載入時將其積極式載入 (Eager Loading)：

    $users->loadMissing(['comments', 'posts']);
    
    $users->loadMissing('comments.author');
    
    $users->loadMissing(['comments', 'posts' => fn ($query) => $query->where('active', 1)]);

<a name="method-modelKeys"></a>

#### `modelKeys()` {.collection-method}

`modelKeys` 方法會回傳該 Collection 中所有方法的主鍵：

    $users->modelKeys();
    
    // [1, 2, 3, 4, 5]

<a name="method-makeVisible"></a>

#### `makeVisible($attributes)` {.collection-method}

`makeVisible` 方法會將該 Collection 中所有 Model 上通常「隱藏 (Hidden)」的屬性[設為可見](/docs/{{version}}/eloquent-serialization#hiding-attributes-from-json)：

    $users = $users->makeVisible(['address', 'phone_number']);

<a name="method-makeHidden"></a>

#### `makeHidden($attributes)` {.collection-method}

`makeHidden` 方法會將該 Collection 中所有 Model 上通常「可見 (Visible)」的屬性[設為隱藏](/docs/{{version}}/eloquent-serialization#hiding-attributes-from-json)：

    $users = $users->makeHidden(['address', 'phone_number']);

<a name="method-only"></a>

#### `only($keys)` {.collection-method}

`except` 方法會回傳符合給定主鍵的所有 Model：

    $users = $users->only([1, 2, 3]);

<a name="method-setVisible"></a>

#### `setVisible($attributes)` {.collection-method}

`setVisible` 方法可[暫時複寫掉](/docs/{{version}}/eloquent-serialization#temporarily-modifying-attribute-visibility)該 Collection 中各個 Model 的所有 ^[visible](可見) 屬性：

    $users = $users->setVisible(['id', 'name']);

<a name="method-setHidden"></a>

#### `setHidden($attributes)` {.collection-method}

`setHidden` 方法可[暫時複寫掉](/docs/{{version}}/eloquent-serialization#temporarily-modifying-attribute-visibility)該 Collection 中各個 Model 的所有 ^[hidden](隱藏) 屬性：

    $users = $users->setHidden(['email', 'password', 'remember_token']);

<a name="method-toquery"></a>

#### `toQuery()` {.collection-method}

`toQuery` 方法回傳 Eloquent Query Builder 實體，並包含了由該 Collection 中 Model 的主索引鍵所組成的 `whereIn` 限制式：

    use App\Models\User;
    
    $users = User::where('status', 'VIP')->get();
    
    $users->toQuery()->update([
        'status' => 'Administrator',
    ]);

<a name="method-unique"></a>

#### `unique($key = null, $strict = false)` {.collection-method}

`unique` 方法會回傳該 Collection 中所有不重複的 Model。只要某個 Model 在該 Collection 中有另一個具有相同型別與相同主鍵的 Model，就會被移除：

    $users = $users->unique();

<a name="custom-collections"></a>

## 自訂 Collection

若想在與給定 Model 互動時使用自訂 `Collection` 實體，可在 Model 中定義一個 `newCollection` 方法：

    <?php
    
    namespace App\Models;
    
    use App\Support\UserCollection;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * Create a new Eloquent Collection instance.
         *
         * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
         * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
         */
        public function newCollection(array $models = []): Collection
        {
            return new UserCollection($models);
        }
    }

定義好 `newCollection` 方法後，之後，在 Eloquent 通常會回傳 `Illuminate\Database\Eloquent\Collection` 實體的時候，就會變成你的自訂 Collection 實體。若想在專案中的所有 Model 上都使用某個自訂 Collection，則可在一個基礎 Model 類別上定義 `newCollection` 方法，並讓專案內所有的 Model 都繼承該基礎 Model：
