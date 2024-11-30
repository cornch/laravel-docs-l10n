---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/55/en-zhtw'
updatedAt: '2024-06-30T08:26:00Z'
contributors: {  }
progress: 48.17
---

# Eloquent：關聯

- [簡介](#introduction)
- [定義關聯](#defining-relationships)
  - [One to One / Has One](#one-to-one)
  - [One to Many / Has Many](#one-to-many)
  - [One to Many (Inverse) / Belongs To](#one-to-many-inverse)
  - [Has One of Many](#has-one-of-many)
  - [間接一對一](#has-one-through)
  - [間接一對多](#has-many-through)
  
- [Many to Many Relationships](#many-to-many)
  - [取得中介資料表欄位](#retrieving-intermediate-table-columns)
  - [Filtering Queries via Intermediate Table Columns](#filtering-queries-via-intermediate-table-columns)
  - [Ordering Queries via Intermediate Table Columns](#ordering-queries-via-intermediate-table-columns)
  - [定義自訂的中介資料表 Model](#defining-custom-intermediate-table-models)
  
- [多型關聯](#polymorphic-relationships)
  - [One to One](#one-to-one-polymorphic-relations)
  - [One to Many](#one-to-many-polymorphic-relations)
  - [One of Many](#one-of-many-polymorphic-relations)
  - [Many to Many](#many-to-many-polymorphic-relations)
  - [自訂多型類型](#custom-polymorphic-types)
  
- [動態關聯](#dynamic-relationships)
- [查詢關聯](#querying-relations)
  - [Relationship Methods vs. Dynamic Properties](#relationship-methods-vs-dynamic-properties)
  - [查詢關聯存在](#querying-relationship-existence)
  - [查詢關聯不存在](#querying-relationship-absence)
  - [查詢 MorphTo 關聯](#querying-morph-to-relationships)
  
- [彙總關聯的 Model](#aggregating-related-models)
  - [關聯 Model 計數](#counting-related-models)
  - [其他彙總函式](#other-aggregate-functions)
  - [Counting Related Models on Morph To Relationships](#counting-related-models-on-morph-to-relationships)
  
- [積極式載入 (Eager Loading)](#eager-loading)
  - [帶有條件的積極式載入](#constraining-eager-loads)
  - [消極的積極式載入 (Lazy Eager Loading)](#lazy-eager-loading)
  - [預防消極載入 (Lazy Loading)](#preventing-lazy-loading)
  
- [Inserting and Updating Related Models](#inserting-and-updating-related-models)
  - [`save` 方法](#the-save-method)
  - [`create` 方法](#the-create-method)
  - [BelongsTo 關聯](#updating-belongs-to-relationships)
  - [Many to Many Relationships](#updating-many-to-many-relationships)
  
- [更新上層 Model 的時戳](#touching-parent-timestamps)

<a name="introduction"></a>

## 簡介

資料庫中的資料表通常會互相彼此關聯。舉例來說，部落格文章可能會有許多的留言，而訂單則可能會關聯到建立訂單的使用者。在 Eloquent 中，要管理並處理這些關聯非常簡單，並支援多種常見的關聯：

<div class="content-list" markdown="1">
- [一對一](#one-to-one)
- [一對多](#one-to-many)
- [多對多](#many-to-many)
- [間接一對一](#has-one-through)
- [間接一對多](#has-many-through)
- [一對一 (多型)](#one-to-one-polymorphic-relations)
- [一對多 (多型)](#one-to-many-polymorphic-relations)
- [多對多 (多型)](#many-to-many-polymorphic-relations)

</div>
<a name="defining-relationships"></a>

## 定義關聯

Eloquent 關聯是作為方法定義在 Eloquent Model 類別中。由於關聯也可當作強大的 [Query Builder](/docs/{{version}}/queries) 使用，因此將關聯定義為方法也能讓方法得以串連使用並進行查詢。舉例來說，我們可以在這個 `posts` 關聯中串上額外的查詢條件：

    $user->posts()->where('active', 1)->get();
不過，在更深入瞭解如何使用關聯以前，我們先來了解一下如何定義 Eloquent 所支援的各種關聯型別吧！

<a name="one-to-one"></a>

### One to One / Has One

一對一關聯是一種非常基本的資料庫關聯。舉例來說，一個 `User` Model 可能與一個 `Phone` Model 有關。要定義這個關聯，我們先在 `User` Model 中定義一個 `phone` 方法。`phone` 方法應呼叫 `hasOne` 方法並回傳其結果。`hasOne` 方法是通過 Model 的 `Illuminate\Database\Eloquent\Model` 基礎類別提供的：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    
    class User extends Model
    {
        /**
         * Get the phone associated with the user.
         */
        public function phone(): HasOne
        {
            return $this->hasOne(Phone::class);
        }
    }
傳給 `hasOne` 方法的第一個引述是關聯 Model 類別的名稱。定義好關聯後，我們就可以通過 Eloquent 的動態屬性來存取這個關聯的紀錄。動態屬性能讓我們像在存取定義在 Model 上的屬性一樣來存取關聯方法：

    $phone = User::find(1)->phone;
Eloquent 會通過上層 Model 的名稱來判斷關聯的外部索引鍵 (Foreign Key)。在這個例子中，Eloquent 會自動假設 `Phone` Model 中有個 `user_id` 外部索引鍵。若要複寫這個慣例用法的話，可以傳入第二個引數給 `hasOne` 方法：

    return $this->hasOne(Phone::class, 'foreign_key');
此外，Eloquent 還會假設這個外部索引鍵應該要有個與上層資料的主索引鍵欄位相同的值。換句話說，Eloquent 會在 `Phone` 紀錄的 `user_id` 欄位中找到與該使用者 `id` 欄位值相同的資料。若想在關聯中使用 `id` 或 Model 的 `$primaryKey` 屬性意外的其他主索引鍵值的話，可傳入第三個引數給 `hasOne` 方法：

    return $this->hasOne(Phone::class, 'foreign_key', 'local_key');
<a name="one-to-one-defining-the-inverse-of-the-relationship"></a>

#### Defining the Inverse of the Relationship

好了，我們現在可以在 `User` Model 中存取 `Phone` Model 了。接著，我們來在 `Phone` Model 上定義關聯，好讓我們能在存取擁有這隻電話的使用者。我們可以使用 `belongsTo` 方法來定義反向的 `hasOne` 關聯：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    
    class Phone extends Model
    {
        /**
         * Get the user that owns the phone.
         */
        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }
    }
當叫用 `user` 方法時，Eloquent 會嘗試尋找一筆 `id` 符合 `Phone` Model 中 `user_id` 欄位的 `User` Model。

Eloquent 會檢查關聯方法的名稱，並在這個方法的名稱後加上 `_id` 來自動判斷外部索引鍵名稱。因此，在這個例子中，Eloquent 會假設 `Phone` Model 有個 `user_id` 欄位。不過，若 `Phone` Model 的外部索引鍵不是 `user_id`，則可以傳遞一個自訂索引鍵名稱給 `belongsTo`，作為第二個引數：

    /**
     * Get the user that owns the phone.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'foreign_key');
    }
若上層 Model 不使用 `id` 作為其主索引鍵，或是想要使用不同的欄位來尋找關聯的 Model，則可以傳遞第三個引數給 `belongsTo` 方法來指定上層資料表的自訂索引鍵：

    /**
     * Get the user that owns the phone.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'foreign_key', 'owner_key');
    }
<a name="one-to-many"></a>

### One to Many / Has Many

一對多關聯可用來定義某個有一個或多個子 Model 的單一 Model。舉例來說，部落格文章可能有無限數量筆留言。與其他 Eloquent 關聯一樣，一對多關聯可通過在 Eloquent Model 中定義方法來定義：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    
    class Post extends Model
    {
        /**
         * Get the comments for the blog post.
         */
        public function comments(): HasMany
        {
            return $this->hasMany(Comment::class);
        }
    }
請記得，Eloquent 會自動為 `Comment` Model 判斷適當的外部索引鍵欄位。依照慣例，Eloquent 會去上層 Model 的「蛇形命名法 (snake_case)」名稱，並在其後加上 `_id`。因此，在這個例子中，Eloquent 會假設 `Comment` Model 上的外部索引鍵欄位為 `post_id`。

定義好關聯方法後，我們就可以通過 `comments` 屬性來存取關聯留言的 [Collection](/docs/{{version}}/eloquent-collections)。請記得，由於 Eloquent 提供了「動態關聯屬性」，因此我們可以像我們是在 Model 上定義屬性一樣地存取關聯方法：

    use App\Models\Post;
    
    $comments = Post::find(1)->comments;
    
    foreach ($comments as $comment) {
        // ...
    }
由於所有的關聯也同時是 Query Builder，因此我們也能通過呼叫 `comments` 方法並繼續在查詢上串上條件來進一步給關聯加上查詢條件：

    $comment = Post::find(1)->comments()
                        ->where('title', 'foo')
                        ->first();
就像 `hasOne` 方法，我們也可以通過傳遞額外的參數給 `hasMany` 來複寫外部與內部的索引鍵：

    return $this->hasMany(Comment::class, 'foreign_key');
    
    return $this->hasMany(Comment::class, 'foreign_key', 'local_key');
<a name="automatically-hydrating-parent-models-on-children"></a>

#### Automatically Hydrating Parent Models on Children

Even when utilizing Eloquent eager loading, "N + 1" query problems can arise if you try to access the parent model from a child model while looping through the child models:

```php
$posts = Post::with('comments')->get();

foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->post->title;
    }
}
```
In the example above, an "N + 1" query problem has been introduced because, even though comments were eager loaded for every `Post` model, Eloquent does not automatically hydrate the parent `Post` on each child `Comment` model.

If you would like Eloquent to automatically hydrate parent models onto their children, you may invoke the `chaperone` method when defining a `hasMany` relationship:

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    
    class Post extends Model
    {
        /**
         * Get the comments for the blog post.
         */
        public function comments(): HasMany
        {
            return $this->hasMany(Comment::class)->chaperone();
        }
    }
Or, if you would like to opt-in to automatic parent hydration at run time, you may invoke the `chaperone` model when eager loading the relationship:

```php
use App\Models\Post;

$posts = Post::with([
    'comments' => fn ($comments) => $comments->chaperone(),
])->get();
```
<a name="one-to-many-inverse"></a>

### One to Many (Inverse) / Belongs To

現在，我們已經可以存取一篇文章的所有留言了。讓我們來定義一個關聯，以從留言去的其上層的文章。要定義 `hasMany` 關聯的相反，我們可以在子 Model 中定義一個呼叫了 `belongsTo` 方法的關聯方法：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    
    class Comment extends Model
    {
        /**
         * Get the post that owns the comment.
         */
        public function post(): BelongsTo
        {
            return $this->belongsTo(Post::class);
        }
    }
定義好關聯後，我們就可以通過存取 `post`「動態關聯屬性」來取得留言的上層文章：

    use App\Models\Comment;
    
    $comment = Comment::find(1);
    
    return $comment->post->title;
在上述例子中，Eloquent 會嘗試找到 `id` 符合 `Comments` Model 中 `post_id` 欄位的 `Post` Model。

Eloquent 會檢查關聯方法的名稱，並在該名稱後加上 `_`，然後再加上上層 Model 的主索引鍵欄位名稱作為預設的外部索引鍵名稱。因此，在這個例子中，Eloquent 會假設 `Post` Model 在 `comments` 資料表中的外部索引鍵為 `post_id`。

不過，若沒有依照這種慣例來命名關聯的外部索引鍵，則可以將自訂的外部索引鍵傳遞給 `belongsTo` 方法作為第二個引數：

    /**
     * Get the post that owns the comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'foreign_key');
    }
若上層 Model 不使用 `id` 作為其主索引鍵，或是想要使用不同的欄位來尋找關聯的 Model，則可以傳遞第三個引數給 `belongsTo` 方法來指定上層資料表的自訂索引鍵：

    /**
     * Get the post that owns the comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'foreign_key', 'owner_key');
    }
<a name="default-models"></a>

#### 預設 Model

`belongsTo`, `hasOne`, `hasOneThrough`, 以及 `morphOne` 關聯可定義一個預設 Model，當給定的關聯為 `null` 時會回傳該預設 Model。這種模式通常稱為 [Null Object pattern](https://en.wikipedia.org/wiki/Null_Object_pattern)，並能讓你在程式碼中減少條件檢查的次數。在下列範例中，`user` 關聯會在沒有使用者附加在 `Post` Model 時回傳一個空的 `App\Models\User` Model：

    /**
     * Get the author of the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }
若要為預設的 Model 設定屬性，則可以傳入陣列或閉包給 `withDefault` 方法：

    /**
     * Get the author of the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Guest Author',
        ]);
    }
    
    /**
     * Get the author of the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(function (User $user, Post $post) {
            $user->name = 'Guest Author';
        });
    }
<a name="querying-belongs-to-relationships"></a>

#### 查詢 Belongs To 關聯

在查詢「Belongs To」關聯的子項目時，可以手動建立用於取得相應 Eloquent Model 的 `where` 子句：

    use App\Models\Post;
    
    $posts = Post::where('user_id', $user->id)->get();
不過，使用 `whereBelongsTo` 方法可能會比較方便。該方法會為給定的 Model 自動判斷適當的關聯與外部索引鍵：

    $posts = Post::whereBelongsTo($user)->get();
我們也可以提供一個 [Collection](/docs/{{version}}/eloquent-collections) 實體給 `whereBelongsTo` 方法。這時，Laravel 會取得所有上層 Model 有包含在該 Collection 中的 Model：

    $users = User::where('vip', true)->get();
    
    $posts = Post::whereBelongsTo($users)->get();
預設情況下，Larave 會依據 Model 的類別名稱來判斷與給定 Model 有關的關聯。不過，我們也可以通過傳入第二個引數給 `whereBelongsTo` 方法來手動指定關聯的名稱：

    $posts = Post::whereBelongsTo($user, 'author')->get();
<a name="has-one-of-many"></a>

### Has One of Many

有時候，某個 Model 可能有多個關聯 Model，而我們可能會想取多個關聯 Model 中「最新」或「最舊」的關聯 Model。舉例來說，`User` Model (使用者) 可能會關聯到多個 `Order` Model (訂單)，而我們可能會想定義一種方便的方法來存取使用者最新的訂單。我們可以通過將 `hasOne` 關聯類型與 `ofMany` 方法搭配使用來達成：

```php
/**
 * Get the user's most recent order.
 */
public function latestOrder(): HasOne
{
    return $this->hasOne(Order::class)->latestOfMany();
}
```
同樣的，我們也可以定義一個方法來取得一個關聯中「最舊」或第一個關聯的 Model：

```php
/**
 * Get the user's oldest order.
 */
public function oldestOrder(): HasOne
{
    return $this->hasOne(Order::class)->oldestOfMany();
}
```
預設情況下，`latestOfMany` 與 `oldestOfMany` 方法會依照該 Model 的主索引鍵來取得最新或最舊的 Model，而該索引鍵必須要是可以排序的。不過，有時候我們可能會想從一個更大的關聯中通過另一種方法來取得單一 Model：

舉例來說，我們可以使用 `ofMany` 方法來去的使用者下過金額最高的訂單。`ofMany` 方法的第一個引數為可排序的欄位，接著則是要套用哪個匯總函式 (`min` 或 `max` 等) 在關聯的 Model 上：

```php
/**
 * Get the user's largest order.
 */
public function largestOrder(): HasOne
{
    return $this->hasOne(Order::class)->ofMany('price', 'max');
}
```
> [!WARNING]  
> 由於 PostgreSQL 不支援在 UUID 欄位上執行 `MAX` 函式，因此目前一對多關聯無法搭配 PostgreSQL 的 UUID 欄位使用。

<a name="converting-many-relationships-to-has-one-relationships"></a>

#### Converting "Many" Relationships to Has One Relationships

在使用 `latestOfMany`, `oldestOfMany` 或 `ofMany` 方法來取得單一 Model 時，常常都是在這個 Model 上已經有定義「Has Many」關聯的情況。針對此狀況，Laravel 提供了一個方便的作法：你只要在關聯上呼叫 `one` 方法，就可以輕鬆地將此關聯轉換為「Has One」關聯：

```php
/**
 * Get the user's orders.
 */
public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}

/**
 * Get the user's largest order.
 */
public function largestOrder(): HasOne
{
    return $this->orders()->one()->ofMany('price', 'max');
}
```
<a name="advanced-has-one-of-many-relationships"></a>

#### Advanced Has One of Many Relationships

我們還可以進一步地做出進階的「一對多中之一」關聯。舉例來說，`Product` Model 可能會有許多相應的 `Price` Model，這些 `Price` Model 會在每次更新商品價格後保留在系統內。此外，我們也可以進一步地通過 `published_at` 欄位來讓某個商品價格在未來的時間點生效。

因此，總結一下，我們會需要取得最新且已發布的價格，且發佈時間不可是未來。此外，若有兩個價格的發佈時間相同，則我們取 ID 最大的那個價格。為此，我們必須傳入一個陣列給 `ofMany` 方法，該陣列序包用來判斷最新價格的可排序欄位。此外，我們會提供一個閉包給 `ofMany` 方法作為第二個引述。這個閉包會負責為關聯查詢加上額外的發佈時間條件：

```php
/**
 * Get the current pricing for the product.
 */
public function currentPricing(): HasOne
{
    return $this->hasOne(Price::class)->ofMany([
        'published_at' => 'max',
        'id' => 'max',
    ], function (Builder $query) {
        $query->where('published_at', '<', now());
    });
}
```
<a name="has-one-through"></a>

### 間接一對一

「間接一對一 (has-one-through)」關聯定義了與另一個 Model 間的一對一關係。不過，使用這種關聯代表宣告關聯的 Model 可以 **通過** 一個 Model 來對應到另一個 Model 的實體。

舉例來說，在汽車維修網站中，每個 `Mechanic` Model (零件) 可以跟一個 `Car` Model 關聯。而每個 `Car` Model (汽車) 則可以關聯到一個 `Owner` Model (車主)。雖然零件與車主在資料庫中並沒有直接的關聯性，但我們可以 **通過** `Car` Model 來在零件上存取車主。來看看要定義這種關聯所需的資料表：

    mechanics
        id - integer
        name - string
    
    cars
        id - integer
        model - string
        mechanic_id - integer
    
    owners
        id - integer
        name - string
        car_id - integer
現在，我們已經瞭解了這種關聯性的資料表結構。讓我們來在 `Mechanic` Model 上定義關聯：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOneThrough;
    
    class Mechanic extends Model
    {
        /**
         * Get the car's owner.
         */
        public function carOwner(): HasOneThrough
        {
            return $this->hasOneThrough(Owner::class, Car::class);
        }
    }
傳給 `hasOneThrough` 方法的第一個引述是最後我們想存取的 Model 名稱；第二個引數則是中介 Model 的名稱。

或者，若這個關聯中所涉及的所有 Model 上都已定義了相關的關聯，則可以呼叫 `through` 方法，並提供這些關聯的名稱來以串聯呼叫的方式定義「has-one-through」關聯。舉例來說，若 `Mechanic` 方法中有 `cars` 關聯，而 `Car` Model 中有 `owner` 屬性，則可像這樣定義「has-one-through」關聯來將 Mechanic 與 Owner 關聯起來：

```php
// String based syntax...
return $this->through('cars')->has('owner');

// Dynamic syntax...
return $this->throughCars()->hasOwner();
```
<a name="has-one-through-key-conventions"></a>

#### 索引鍵慣例

在進行關聯查詢時，會使用到典型的 Eloquent 外部索引鍵慣例。若想自訂關聯使用的索引鍵，則可以將自訂索引鍵傳給 `hasOneThrough` 方法的第三個與第四個引數。第三個引數為中介 Model 上的外部索引鍵名稱。第四個引數則是最終 Model 的外部索引鍵名稱。第五個引數則為內部索引鍵，而第六個引述則是中介 Model 上的內部索引鍵：

    class Mechanic extends Model
    {
        /**
         * Get the car's owner.
         */
        public function carOwner(): HasOneThrough
        {
            return $this->hasOneThrough(
                Owner::class,
                Car::class,
                'mechanic_id', // Foreign key on the cars table...
                'car_id', // Foreign key on the owners table...
                'id', // Local key on the mechanics table...
                'id' // Local key on the cars table...
            );
        }
    }
或者，就像剛才討論過的，若此關聯所涉及的所有 Model 中都已定義了相關的關聯，則可以呼叫 `through` 方法，並提供這些關聯的名稱，來以串聯呼叫的方式來定義「has-one-through」關聯。使用這種方式，即可重複使用現有關聯中定義的索引鍵慣例：

```php
// String based syntax...
return $this->through('cars')->has('owner');

// Dynamic syntax...
return $this->throughCars()->hasOwner();
```
<a name="has-many-through"></a>

### 間接一對多

「間接一對多 (has-many-through)」關聯提供了一個方便的方法來通過中介關聯存取另一個關聯。舉例來說，假設我們有一個像 [Laravel Vapor](https://vapor.laravel.com) 這樣的部署平台。`Project` Model (專案)可通過一個中介的 `Environment` Model (環境) 來存取多個 `Deployment` Model (部署)。依照這個例子，我們可以很輕鬆的取得特定專案的所有部署。來看看定義這個關聯性所需的資料表：

    projects
        id - integer
        name - string
    
    environments
        id - integer
        project_id - integer
        name - string
    
    deployments
        id - integer
        environment_id - integer
        commit_hash - string
現在，我們已經瞭解了這種關聯性的資料表結構。讓我們來在 `Project` Model 上定義關聯：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasManyThrough;
    
    class Project extends Model
    {
        /**
         * Get all of the deployments for the project.
         */
        public function deployments(): HasManyThrough
        {
            return $this->hasManyThrough(Deployment::class, Environment::class);
        }
    }
傳給 `hasManyThrough` 方法的第一個引述是最後我們想存取的 Model 名稱；第二個引數則是中介 Model 的名稱。

或者，若這個關聯中所涉及的所有 Model 上都已定義了相關的關聯，則可以呼叫 `through` 方法，並提供這些關聯的名稱來以串聯呼叫的方式定義「has-many-through」關聯。舉例來說，若 `Project` 方法中有 `environments` 關聯，而 `Environment` Model 中有 `deployments` 屬性，則可像這樣定義「has-many-through」關聯來將 Project 與 Deployment 關聯起來：

```php
// String based syntax...
return $this->through('environments')->has('deployments');

// Dynamic syntax...
return $this->throughEnvironments()->hasDeployments();
```
雖然 `Deployment` Model 的資料表不包含 `project_id` 欄位，但 `hasManyThrough` 關聯可讓我們通過 `$project->deployments` 來存取專案的部署。為了取得這些 Model，Eloquent 會先在中介的 `Environment` Model 資料表上讀取 `project_id`。找到相關的環境 ID 後，再通過這些 ID 來查詢 `Deployment` Model 的資料表。

<a name="has-many-through-key-conventions"></a>

#### 索引鍵慣例

在進行關聯查詢時，會使用到典型的 Eloquent 外部索引鍵慣例。若想自訂關聯使用的索引鍵，則可以將自訂索引鍵傳給 `hasManyThrough` 方法的第三個與第四個引數。第三個引數為中介 Model 上的外部索引鍵名稱。第四個引數則是最終 Model 的外部索引鍵名稱。第五個引數則為內部索引鍵，而第六個引述則是中介 Model 上的內部索引鍵：

    class Project extends Model
    {
        public function deployments(): HasManyThrough
        {
            return $this->hasManyThrough(
                Deployment::class,
                Environment::class,
                'project_id', // Foreign key on the environments table...
                'environment_id', // Foreign key on the deployments table...
                'id', // Local key on the projects table...
                'id' // Local key on the environments table...
            );
        }
    }
或者，就像剛才討論過的，若此關聯所涉及的所有 Model 中都已定義了相關的關聯，則可以呼叫 `through` 方法，並提供這些關聯的名稱，來以串聯呼叫的方式來定義「has-many-through」關聯。使用這種方式，即可重複使用現有關聯中定義的索引鍵慣例：

```php
// String based syntax...
return $this->through('environments')->has('deployments');

// Dynamic syntax...
return $this->throughEnvironments()->hasDeployments();
```
<a name="many-to-many"></a>

## Many to Many Relationships

比起 `hasOne` 或 `hasMany`，多對多關聯稍微複雜一點。一個多對多關聯的例子是：一位使用者可以有多個職位，而這些職位也會被網站中的其他使用者使用。舉例來說，某位使用者可能會被設定職位「作者」與「編輯」，但這些職位也可能會被指派給其他使用者。因此，一位使用者可以有多個職位，而一個職位則可以有多位使用者。

<a name="many-to-many-table-structure"></a>

#### 資料表結構

要定義這種關聯，我們需要三張資料表：`users`, `roles`, 與 `role_user`。`role_user` 資料表的名稱是由關聯的 Model 名稱按照字母排序串接而來的，裡面包含了 `user_id` 與 `role_id` 欄位。這張資料表會用來作為關聯使用者與職位的中介資料表。

請記得，由於一個職位可以同時關聯到多位使用者，因此我們沒辦法在 `roles` 資料表上設定 `user_id` 欄位。若這麼做的話，一個職位就只能有一位使用者。為了要讓職位能被設定給多位使用者，我們會需要 `role_user` 資料表。我們可以總結一下，資料表的結構會長這樣：

    users
        id - integer
        name - string
    
    roles
        id - integer
        name - string
    
    role_user
        user_id - integer
        role_id - integer
<a name="many-to-many-model-structure"></a>

#### Model 架構

我們可以通過撰寫一個回傳 `belongsToMany` 方法執行結果的方法來定義多對多關聯。`belongsToMany` 方法是由 `Illuminate\Database\Eloquent\Model` 基礎類別提供的，你的專案中所有的 Eloquent Model 都使用了這個類別。舉例來說，讓我們來在 `User` Model 上定義一個 `roles` 方法。傳入這個方法的第一個引述是關聯 Model 類別的名稱：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    
    class User extends Model
    {
        /**
         * The roles that belong to the user.
         */
        public function roles(): BelongsToMany
        {
            return $this->belongsToMany(Role::class);
        }
    }
定義好關聯後，就可以使用 `roles` 動態關聯屬性來存取該使用者的角色：

    use App\Models\User;
    
    $user = User::find(1);
    
    foreach ($user->roles as $role) {
        // ...
    }
由於所有的關聯也同時是 Query Builder，因此我們也能通過呼叫 `roles` 方法並繼續在查詢上串上條件來進一步給關聯加上查詢條件：

    $roles = User::find(1)->roles()->orderBy('name')->get();
為了判斷該關聯的中介資料表表名，Eloquent 會將兩個關聯 Model 的名稱按照字母排序串接在一起。不過，這個慣例是可以隨意複寫的，只需要傳入第二個引數給 `belongsToMany` 方法即可：

    return $this->belongsToMany(Role::class, 'role_user');
除了自訂中介表的表名外，也可以傳入額外的引數給 `belongsToMany` 來自訂中介表上的欄位名稱。第三個引數目前定義關聯的 Model 的外部索引鍵，而第四個引述則是要連結的 Model 的外部索引鍵：

    return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
<a name="many-to-many-defining-the-inverse-of-the-relationship"></a>

#### Defining the Inverse of the Relationship

若要定義 many-to-many 的「相反」關聯，應先在關聯的 Model 上定義一個同樣回傳 `belongsToMany` 方法結果的方法。接著我們的使用者與角色的例子，我們來在 `Role` Model 上定義 `users` 方法：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    
    class Role extends Model
    {
        /**
         * The users that belong to the role.
         */
        public function users(): BelongsToMany
        {
            return $this->belongsToMany(User::class);
        }
    }
如你所見，除了這邊是參照 `App\Models\User` 外，關聯定義跟 `User` Model 中對應的部分完全一樣。由於我們使用的還是 `belongsToMany` 方法，因此，在定義「反向」的 many-to-many 關聯時，一樣可以使用一般的資料表與索引鍵自訂選項。

<a name="retrieving-intermediate-table-columns"></a>

### 取得中介資料表欄位

讀者可能已經瞭解到，處理 Many-to-Many 關聯時必須要有一張中介資料表。Eloquent 提供了一些非常適用的方法來與中介資料表互動。舉例來說，假設 `User` Model 有許多關聯的 `Role` Model。存取這個關聯後，我們可以使用 Model 上的 `pivot` 屬性來存取中介資料表：

    use App\Models\User;
    
    $user = User::find(1);
    
    foreach ($user->roles as $role) {
        echo $role->pivot->created_at;
    }
可以注意到，我們取得的每個 `Role` 資料表都會自動獲得一個 `pivot` 屬性。這個屬性包含了一個代表中介資料表的 Model。

預設情況下，只有 Model 的索引鍵會出現在 `Pivot` Model 上。若中介資料表包含了其他額外的屬性，則需要在定義關聯時指定這些屬性：

    return $this->belongsToMany(Role::class)->withPivot('active', 'created_by');
若想讓中介資料表擁有 Eloquent 能自動維護的 `created_at` 與 `updated_at` 時戳，可在定義關聯的時候呼叫 `withTimestamps` 方法：

    return $this->belongsToMany(Role::class)->withTimestamps();
> [!WARNING]  
> 使用 Eloquent 自動維護時戳的中介資料表會需要擁有 `created_at` 與 `updated_at` 兩個時戳欄位。

<a name="customizing-the-pivot-attribute-name"></a>

#### Customizing the `pivot` Attribute Name

剛才也有提過，我們可以使用 `pivot` 屬性來存取中介資料表的屬性。不過，我們可以自訂這個屬性的名稱以讓其跟貼合在專案中的用途。

舉例來說，我們的專案中可能會包含能讓使用者訂閱 Podcast 的功能，我們可能會想在使用者與 Podcast 間使用 Many-to-Many 關聯。在這個例子中，我們可能會想將中介資料表屬性的名稱從 `pivot` 改成 `subscription`。可以在定義關聯時使用 `as` 方法來完成：

    return $this->belongsToMany(Podcast::class)
                    ->as('subscription')
                    ->withTimestamps();
指定好自訂的中介資料表屬性後，就可以使用自訂的名稱來存取中介資料表資料：

    $users = User::with('podcasts')->get();
    
    foreach ($users->flatMap->podcasts as $podcast) {
        echo $podcast->subscription->created_at;
    }
<a name="filtering-queries-via-intermediate-table-columns"></a>

### Filtering Queries via Intermediate Table Columns

也可以在定義關聯時使用 `wherePivot`, `wherePivotIn`, `wherePivotNotIn`, `wherePivotBetween`, `wherePivotNotBetween`, `wherePivotNull`, 與 `wherePivotNotNull` 方法來過濾 `belongsToMany` 關聯查詢的回傳結果：

    return $this->belongsToMany(Role::class)
                    ->wherePivot('approved', 1);
    
    return $this->belongsToMany(Role::class)
                    ->wherePivotIn('priority', [1, 2]);
    
    return $this->belongsToMany(Role::class)
                    ->wherePivotNotIn('priority', [1, 2]);
    
    return $this->belongsToMany(Podcast::class)
                    ->as('subscriptions')
                    ->wherePivotBetween('created_at', ['2020-01-01 00:00:00', '2020-12-31 00:00:00']);
    
    return $this->belongsToMany(Podcast::class)
                    ->as('subscriptions')
                    ->wherePivotNotBetween('created_at', ['2020-01-01 00:00:00', '2020-12-31 00:00:00']);
    
    return $this->belongsToMany(Podcast::class)
                    ->as('subscriptions')
                    ->wherePivotNull('expired_at');
    
    return $this->belongsToMany(Podcast::class)
                    ->as('subscriptions')
                    ->wherePivotNotNull('expired_at');
<a name="ordering-queries-via-intermediate-table-columns"></a>

### Ordering Queries via Intermediate Table Columns

可以使用 `orderByPivot` 方法來排序 `belongsToMany` 關聯查詢回傳結果。在下列範例中，我們會取得使用者 (User) 的所有最新徽章 (Badge)：

    return $this->belongsToMany(Badge::class)
                    ->where('rank', 'gold')
                    ->orderByPivot('created_at', 'desc');
<a name="defining-custom-intermediate-table-models"></a>

### 定義自訂的中介表 Model

若想定義一個代表多對多關聯之中介資料表的自訂 Model，則可以在定義關聯時呼叫 `using` 方法。自訂樞紐 Model (Pivot Model) 能讓我們有機會在樞紐 Model 上定義一些額外的行為，如方法或 ^[Cast](%E5%9E%8B%E5%88%A5%E8%BD%89%E6%8F%9B) 等。

要自訂多對多樞紐 Model，則應繼承 `Illuminate\Database\Eloquent\Relations\Pivot` 類別。多型多對多的樞紐 Model 則應繼承 `Illuminate\Database\Eloquent\Relations\MorphPivot`。舉例來說，我們可以定義一個使用了 `RoleUser` 樞紐 Model 的` Role` Model：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    
    class Role extends Model
    {
        /**
         * The users that belong to the role.
         */
        public function users(): BelongsToMany
        {
            return $this->belongsToMany(User::class)->using(RoleUser::class);
        }
    }
定義 `RoleUser` Model 時，應繼承 `Illuminate\Database\Eloquent\Relations\Pivot` 類別：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Relations\Pivot;
    
    class RoleUser extends Pivot
    {
        // ...
    }
> [!WARNING]  
> 樞紐 Model 不能使用 `SoftDeletes` Trait。若有需要對樞紐紀錄作軟刪除，請考慮將樞紐 Model 改寫成真正的 Eloquent Model。

<a name="custom-pivot-models-and-incrementing-ids"></a>

#### Custom Pivot Models and Incrementing IDs

若有定義了使用自訂樞紐 Model 的多對多關聯，且該樞紐 Model 由自動遞增的主索引鍵 (Auto-Incrementing Primary Key)，則應確保這個自訂樞紐 Model 類別由定義一個設為 `true` 的 `incrementing` 屬性。

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
<a name="polymorphic-relationships"></a>

## Polymorphic (多型) 關聯

使用多型關聯，就能讓子 Model 通過單一關聯來隸屬於多種 Model。舉例來說，假設我們正在製作一個能讓使用者分享部落格貼文與影片的網站。在這種例子中，`Comment` (留言) Model 有可能隸屬於 `Post` (貼文) Model，也可能隸屬於 `Video` (影片) Model。

<a name="one-to-one-polymorphic-relations"></a>

### One to One (Polymorphic)

<a name="one-to-one-polymorphic-table-structure"></a>

#### 資料表結構

多型的一對一關聯於一般的一對一關聯類似。不過，在這種關聯中的子 Model 可以使用一種關聯來表示出對超過一種 Model 的從屬關係。舉例來說，部落格的 `Post` (貼文) 與 `User` (使用者) 可能會共享一個多型關聯的 `Image` (圖片) Model。使用多型的一對一關聯，就能讓我們製作一張用來儲存不重複圖片的資料表，並將該資料表關聯到貼文跟使用者上。首先，我們來看看下列資料表架構：

    posts
        id - integer
        name - string
    
    users
        id - integer
        name - string
    
    images
        id - integer
        url - string
        imageable_id - integer
        imageable_type - string
可以注意到 `images` 資料表上的 `imageable_id` 與 `imageable_type` 欄位。`imageable_id` 欄位用來包含貼文或使用者的 ID 值，而 `imageable_type` 欄位則用來包含上層 Model 的類別名稱。`imageable_type` 是用來給 Eloquent 判斷上層 Model 的「型別 (Type)」，以在存取 `imageable` 關聯時能回傳該上層 Model。在這種情況下，這個欄位的內容會是 `App\Models\Post` 或 `App\Models\User`。

<a name="one-to-one-polymorphic-model-structure"></a>

#### Model 架構

接著，讓我們來看看要製作這種關聯所需要的 Model 定義：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    
    class Image extends Model
    {
        /**
         * Get the parent imageable model (user or post).
         */
        public function imageable(): MorphTo
        {
            return $this->morphTo();
        }
    }
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphOne;
    
    class Post extends Model
    {
        /**
         * Get the post's image.
         */
        public function image(): MorphOne
        {
            return $this->morphOne(Image::class, 'imageable');
        }
    }
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphOne;
    
    class User extends Model
    {
        /**
         * Get the user's image.
         */
        public function image(): MorphOne
        {
            return $this->morphOne(Image::class, 'imageable');
        }
    }
<a name="one-to-one-polymorphic-retrieving-the-relationship"></a>

#### Retrieving the Relationship

定義好資料庫資料表與 Model 後，就可以通過這些 Model 來存取關聯。舉例來說，若要取得一則貼文的圖片，我們可以存取 `image` 動態關聯屬性：

    use App\Models\Post;
    
    $post = Post::find(1);
    
    $image = $post->image;
可以通過存取呼叫 `morphTo` 之方法的名稱來取得多型 Model 的上層 Model。在這個例子中，就是 `Image` Model 的 `imageable` 方法。因此，我們可以用動態關聯屬性來存取該方法：

    use App\Models\Image;
    
    $image = Image::find(1);
    
    $imageable = $image->imageable;
依據擁有該圖片的 Model 類型，`Image` Model 上的 `imageable` 關聯會回傳 `Post` 或 `User` 實體。

<a name="morph-one-to-one-key-conventions"></a>

#### 索引鍵慣例

若有需要，也可以指定多型子 Model 所使用的「id」與「type」欄位名稱。若要自訂這些欄位的名稱，請先確保有將關聯的名稱傳給 `morphTo` 方法的第一個引數。一般來說，這個值應該要與方法名稱相同，因此我們可以使用 PHP 的 `__FUNCTION__` 常數：

    /**
     * Get the model that the image belongs to.
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'imageable_type', 'imageable_id');
    }
<a name="one-to-many-polymorphic-relations"></a>

### One to Many (Polymorphic)

<a name="one-to-many-polymorphic-table-structure"></a>

#### 資料表結構

One-to-Many 的多型關聯與一般的 One-to-Many 關聯很類似。不過，在多型關聯中，可以使用單一關聯來讓子 Model 可以隸屬於多種類型的 Model。舉例來說，假設有個使用者可以在貼文與影片上「留言」的網站。若使用多型關聯，我們可以使用單一一個 `comments` 表來包含用於貼文與影片的留言。首先，來看看需要建立這種關聯的資料表結構：

    posts
        id - integer
        title - string
        body - text
    
    videos
        id - integer
        title - string
        url - string
    
    comments
        id - integer
        body - text
        commentable_id - integer
        commentable_type - string
<a name="one-to-many-polymorphic-model-structure"></a>

#### Model 架構

接著，讓我們來看看要製作這種關聯所需要的 Model 定義：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    
    class Comment extends Model
    {
        /**
         * Get the parent commentable model (post or video).
         */
        public function commentable(): MorphTo
        {
            return $this->morphTo();
        }
    }
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    
    class Post extends Model
    {
        /**
         * Get all of the post's comments.
         */
        public function comments(): MorphMany
        {
            return $this->morphMany(Comment::class, 'commentable');
        }
    }
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    
    class Video extends Model
    {
        /**
         * Get all of the video's comments.
         */
        public function comments(): MorphMany
        {
            return $this->morphMany(Comment::class, 'commentable');
        }
    }
<a name="one-to-many-polymorphic-retrieving-the-relationship"></a>

#### Retrieving the Relationship

定義好資料表與 Model 後，就可以使用 Model 的動態關聯屬性來存取這個關聯。舉例來說，若要存取某個貼文的所有留言，我們可以使用 `comments` 動態屬性：

    use App\Models\Post;
    
    $post = Post::find(1);
    
    foreach ($post->comments as $comment) {
        // ...
    }
也可以通過存取呼叫 `morphTo` 之方法的名稱來取得多型子 Model 的上層 Model。在這個例子中，就是 `Comment` Model 的 `commentable` 方法。因此，我們可以用動態關聯屬性來存取該方法以取得留言的上層 Model：

    use App\Models\Comment;
    
    $comment = Comment::find(1);
    
    $commentable = $comment->commentable;
依照不同的留言上層 Model 類型，`Comment` Model 的 `commentable` 關聯回傳的不是 `Post` 實體就是 `Video` 實體。

<a name="polymorphic-automatically-hydrating-parent-models-on-children"></a>

#### Automatically Hydrating Parent Models on Children

Even when utilizing Eloquent eager loading, "N + 1" query problems can arise if you try to access the parent model from a child model while looping through the child models:

```php
$posts = Post::with('comments')->get();

foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->commentable->title;
    }
}
```
In the example above, an "N + 1" query problem has been introduced because, even though comments were eager loaded for every `Post` model, Eloquent does not automatically hydrate the parent `Post` on each child `Comment` model.

If you would like Eloquent to automatically hydrate parent models onto their children, you may invoke the `chaperone` method when defining a `morphMany` relationship:

    class Post extends Model
    {
        /**
         * Get all of the post's comments.
         */
        public function comments(): MorphMany
        {
            return $this->morphMany(Comment::class, 'commentable')->chaperone();
        }
    }
Or, if you would like to opt-in to automatic parent hydration at run time, you may invoke the `chaperone` model when eager loading the relationship:

```php
use App\Models\Post;

$posts = Post::with([
    'comments' => fn ($comments) => $comments->chaperone(),
])->get();
```
<a name="one-of-many-polymorphic-relations"></a>

### One of Many (Polymorphic)

有時候，某個 Model 可能有多個關聯 Model，而我們可能會想取多個關聯 Model 中「最新」或「最舊」的關聯 Model。舉例來說，`User` Model (使用者) 可能會關聯到多個 `Image` Model (圖片)，而我們可能會想定義一種方便的方法來存取使用者最新的圖片。我們可以通過將 `morphOne` 關聯類型與 `ofMany` 方法搭配使用來達成：

```php
/**
 * Get the user's most recent image.
 */
public function latestImage(): MorphOne
{
    return $this->morphOne(Image::class, 'imageable')->latestOfMany();
}
```
同樣的，我們也可以定義一個方法來取得一個關聯中「最舊」或第一個關聯的 Model：

```php
/**
 * Get the user's oldest image.
 */
public function oldestImage(): MorphOne
{
    return $this->morphOne(Image::class, 'imageable')->oldestOfMany();
}
```
預設情況下，`latestOfMany` 與 `oldestOfMany` 方法會依照該 Model 的主索引鍵來取得最新或最舊的 Model，而該索引鍵必須要是可以排序的。不過，有時候我們可能會想從一個更大的關聯中通過另一種方法來取得單一 Model：

舉例來說，我們可以使用 `ofMany` 方法來去的使用者獲得最多「讚」的圖片。`ofMany` 方法的第一個引數為可排序的欄位，接著則是要套用哪個匯總函式 (`min` 或 `max` 等) 在關聯的 Model 上：

```php
/**
 * Get the user's most popular image.
 */
public function bestImage(): MorphOne
{
    return $this->morphOne(Image::class, 'imageable')->ofMany('likes', 'max');
}
```
> [!NOTE]  
> 還有辦法建立建立更進階的「One of Many」關聯。更多資訊請參考 [Has One of Many 說明文件](#advanced-has-one-of-many-relationships)。

<a name="many-to-many-polymorphic-relations"></a>

### Many to Many (Polymorphic)

<a name="many-to-many-polymorphic-table-structure"></a>

#### 資料表結構

多型的 Many-to-Many 關聯比「Morph One」或「Morph Many」都稍微複雜一點。舉例來說，`Post` Model 與 `Video` Model 可以共用一個多型關聯的 `Tag` Model。在這種情況下使用多型的 Many-to-Many 可以讓我們的專案中只需要一張資料表來儲存獨立的 Tag，就可以關聯給 Post 跟 Video。首先，來看看要建立這種關聯的資料表架構：

    posts
        id - integer
        name - string
    
    videos
        id - integer
        name - string
    
    tags
        id - integer
        name - string
    
    taggables
        tag_id - integer
        taggable_id - integer
        taggable_type - string
> [!NOTE]  
> 在進一步深入瞭解多型的 Many-to-Many 關聯前，我們建議你先閱讀有關普通 [Many-to-Many 關聯](#many-to-many)的說明文件。

<a name="many-to-many-polymorphic-model-structure"></a>

#### Model 架構

接著，我們就可以開始在 Model 上定義關聯了。`Post` 與 `Video` Model 都包含了一個 `tags` 方法，該方法中會呼叫基礎 Eloquent Model 類別中的 `morphToMany` 方法。

`morphToMany` 方法接受關聯 Model 的名稱，以及「關聯名稱」。根據我們設定給中介表的名稱以及其中包含的索引鍵，我們可以將關聯推導為「taggable」：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphToMany;
    
    class Post extends Model
    {
        /**
         * Get all of the tags for the post.
         */
        public function tags(): MorphToMany
        {
            return $this->morphToMany(Tag::class, 'taggable');
        }
    }
<a name="many-to-many-polymorphic-defining-the-inverse-of-the-relationship"></a>

#### Defining the Inverse of the Relationship

接著，在 `Tag` Model 中，我們可以為 Tag 的各個可能的上層 Model 定義個別的方法。因此，在這個例子中，我們會定義一個 `posts` 方法與一個 `videos` 方法。這兩個方法都應回傳 `morphedByMany` 方法的結果。

`morphedByMany` 方法接受關聯 Model 的名稱，以及「關聯名稱」。根據我們設定給中介表的名稱以及其中包含的索引鍵，我們可以將關聯推導為「taggable」：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphToMany;
    
    class Tag extends Model
    {
        /**
         * Get all of the posts that are assigned this tag.
         */
        public function posts(): MorphToMany
        {
            return $this->morphedByMany(Post::class, 'taggable');
        }
    
        /**
         * Get all of the videos that are assigned this tag.
         */
        public function videos(): MorphToMany
        {
            return $this->morphedByMany(Video::class, 'taggable');
        }
    }
<a name="many-to-many-polymorphic-retrieving-the-relationship"></a>

#### Retrieving the Relationship

定義好資料庫資料表與 Model 後，就可以通過這些 Model 來存取關聯。舉例來說，若要取得一則貼文的 Tag，我們可以使用 `tags` 動態關聯屬性：

    use App\Models\Post;
    
    $post = Post::find(1);
    
    foreach ($post->tags as $tag) {
        // ...
    }
可以在多型子 Model 中通過存取呼叫 `morphedByMany` 的方法名稱來存取多型關聯的上層 Model。在這個例子中，就是 `Tag` Model 上的 `posts` 與 `videos` 方法：

    use App\Models\Tag;
    
    $tag = Tag::find(1);
    
    foreach ($tag->posts as $post) {
        // ...
    }
    
    foreach ($tag->videos as $video) {
        // ...
    }
<a name="custom-polymorphic-types"></a>

### 自訂多型型別

預設情況下，Laravel 會使用類別的完整格式名稱 (Fully Qualified Class Name) 來儲存關聯 Model 的「類型 (Type)」。具體而言，在上述的 One-to-Many 例子中，`Comment` Model 可以隸屬於 `Post` Model、也可以隸屬於 `Video` Model，因此預設的 `commentable_type` 就分別會是 `App\Models\Post` 或 `App\Models\Video`。不過，開發人員可能會想將這些值從專案的內部結構中解耦 (Decouple) 出來。

舉例來說，我們可以使用像 `post` 或 `video` 等簡單的字串作為「型別」，而不是使用 Model 名稱。這樣一來，即使我們修改了 Model 的名稱，資料庫中的多型「type」欄位值也會繼續有效：

    use Illuminate\Database\Eloquent\Relations\Relation;
    
    Relation::enforceMorphMap([
        'post' => 'App\Models\Post',
        'video' => 'App\Models\Video',
    ]);
可以在 `App\Providers\AppServiceProvider` 類別或依照需求自行的 Service Provider 中之 `boot` 方法內呼叫 `enforceMorphMap` 方法：

我們可以使用 Model 的 `getMorphClass` 方法來在執行階段判斷給定 Model 的 Morph 別名。相反的，我們可以使用 `Relation::getMorphedModel` 方法來取得 Morph 別名的完整格式類別名稱：

    use Illuminate\Database\Eloquent\Relations\Relation;
    
    $alias = $post->getMorphClass();
    
    $class = Relation::getMorphedModel($alias);
> [!WARNING]  
> 在專案中使用「Morph Map」時，所有的 morphable `*_type` 欄位值還是會保持原本的完整各式類別名稱，需要再更改為其「映射 (Map)」的名稱。

<a name="dynamic-relationships"></a>

### 動態關聯

可以使用 `resolveRelationUsing` 方法來在執行階段定義 Eloquent Model 間的關聯。雖然對於一般的專案開發並不建議這麼做，但在開發 Laravel 套件的時候偶爾會很實用。

`resolveRelationUsing` 方法接受自訂的關聯名稱作為其第一個引述。第二個傳入該方法的引數應為閉包，該閉包應接受一個 Model 實體並回傳一個有效的 Eloquent 關聯定義。一般來說，應在某個 [Service Provider](/docs/{{version}}/providers) 內的 boot 方法中定義動態關聯。

    use App\Models\Order;
    use App\Models\Customer;
    
    Order::resolveRelationUsing('customer', function (Order $orderModel) {
        return $orderModel->belongsTo(Customer::class, 'customer_id');
    });
> [!WARNING]  
> 在定義動態關聯時，請總是提供顯式的索引鍵名稱給 Eloquent 關聯方法。

<a name="querying-relations"></a>

## 查詢關聯

由於所有的 Eloquent 關聯都是以方法來定義的，所以我們可以呼叫這些方法來取得關聯的實體，而不需執行查詢來載入關聯的 Model。此外，每種 Eloquent 關聯都可作為 [Query Builder](/docs/{{version}}/queries) 使用，因此我們也能在最終向資料庫執行 SQL 查詢前往關聯查詢串上一些查詢條件。

舉例來說，假設我們有一個部落格網站，其中 `User` Model 可以關聯到 `Post` Model：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    
    class User extends Model
    {
        /**
         * Get all of the posts for the user.
         */
        public function posts(): HasMany
        {
            return $this->hasMany(Post::class);
        }
    }
我們可以查詢 `posts` 關聯，並在關聯上像這樣加上額外的條件：

    use App\Models\User;
    
    $user = User::find(1);
    
    $user->posts()->where('active', 1)->get();
在關聯上我們可以使用任何的 Laravel [Query Builder](/docs/{{version}}/queries) 方法，因此請確保有先閱讀過 Query Builder 的說明文件以瞭解有哪些方法可以使用。

<a name="chaining-orwhere-clauses-after-relationships"></a>

#### 在關聯後方串上 `orWhere` 子句

像上面的範例中一樣，在進行查詢的時候我們可以自由地往關聯新增查詢。不過，在將 `orWhere` 自居串上關聯時要注意，因為 `orWhere` 自居可能會被邏輯性地分組在與關聯條件相同的層級上：

    $user->posts()
            ->where('active', 1)
            ->orWhere('votes', '>=', 100)
            ->get();
上述的例子會產生下列的 SQL。如你所見，`or` 子句會讓查詢回傳 **所有** 大於 100 得票數的貼文。這個查詢不會被限制在任何特定使用者上：

```sql
select *
from posts
where user_id = ? and active = 1 or votes >= 100
```
在大多數的情況下，應該使用[邏輯群組](/docs/{{version}}/queries#logical-grouping)以將條件檢查放在括號中進行分組：

    use Illuminate\Database\Eloquent\Builder;
    
    $user->posts()
            ->where(function (Builder $query) {
                return $query->where('active', 1)
                             ->orWhere('votes', '>=', 100);
            })
            ->get();
上述的例子會產生下列 SQL。可以注意到，查詢條件已正確地進行邏輯分組，且查詢有保持限制在特定使用者上：

```sql
select *
from posts
where user_id = ? and (active = 1 or votes >= 100)
```
<a name="relationship-methods-vs-dynamic-properties"></a>

### Relationship Methods vs. Dynamic Properties

若不想在 Eloquent 關聯查詢上新增任何額外的查詢條件，則可以直接將關聯作為屬性一樣存取。舉例來說，接續使用我們的 `User` 與 `Post` 範例 Model，我們可以像這樣存取 User 的所有 Post：

    use App\Models\User;
    
    $user = User::find(1);
    
    foreach ($user->posts as $post) {
        // ...
    }
動態屬性會被「延遲載入 (Lazy Loading)」，這表示，這些關聯資料只有在實際存取的時候才會被載入。也因此，開發人員常常會使用[積極式載入](#eager-loading)來預先載入稍後會被存取的關聯。使用預先載入，就可以顯著地降低許多在載入 Model 關聯時會被執行的 SQL 查詢。

<a name="querying-relationship-existence"></a>

### 查詢存在的關聯

在取得 Model 紀錄時，我們可能會想依據關聯是否存在來限制查詢結果。舉例來說，假設我們想取得所有至少有一篇留言的部落格貼文。為此，我們可以將關聯的名稱傳入 `has` 或 `orHas` 方法中：

    use App\Models\Post;
    
    // Retrieve all posts that have at least one comment...
    $posts = Post::has('comments')->get();
我們也可以指定一個運算子與總數來進一步自訂查詢：

    // Retrieve all posts that have three or more comments...
    $posts = Post::has('comments', '>=', 3)->get();
可以使用「點 (.)」標記法來撰寫巢狀的 `has` 陳述式。舉例來說，我們可以取得所有至少有一篇含有圖片的留言的部落格貼文：

    // Retrieve posts that have at least one comment with images...
    $posts = Post::has('comments.images')->get();
若需要更多功能，可以使用 `whereHas` 或 `orWhereHas` 方法來在 `has` 查詢上定義額外的查詢條件，如檢查留言的內容等：

    use Illuminate\Database\Eloquent\Builder;
    
    // Retrieve posts with at least one comment containing words like code%...
    $posts = Post::whereHas('comments', function (Builder $query) {
        $query->where('content', 'like', 'code%');
    })->get();
    
    // Retrieve posts with at least ten comments containing words like code%...
    $posts = Post::whereHas('comments', function (Builder $query) {
        $query->where('content', 'like', 'code%');
    }, '>=', 10)->get();
> [!WARNING]  
> 由於 Eloquent 目前並不支援在多個資料庫間查詢關聯的存否，因此要查詢的關聯必須在同一個資料庫中。

<a name="inline-relationship-existence-queries"></a>

#### 內嵌的存在關聯查詢

若想要使用附加在關聯查詢上的簡單且單一的 Where 條件來查詢關聯的存否，那麼用 `whereRelation`、`orWhereRelation`、`whereMorphRelation`、`orWhereMorphRelation` 方法應該會很方便。舉例來說，我們可以查詢所有有未審核 (Unapproved) 留言的貼文：

    use App\Models\Post;
    
    $posts = Post::whereRelation('comments', 'is_approved', false)->get();
當然，就像呼叫 Query Builder 的 `where` 方法一樣，我們也可以指定運算子：

    $posts = Post::whereRelation(
        'comments', 'created_at', '>=', now()->subHour()
    )->get();
<a name="querying-relationship-absence"></a>

### 查詢不存在的關聯

在取得 Model 紀錄時，我們可能會想依據關聯的是否不存在來限制查詢結果。舉例來說，假設我們想取得所有 **沒有** 留言的部落格貼文。為此，我們可以將關聯的名稱傳入 `doesntHave` 或 `orDoesntHave` 方法中：

    use App\Models\Post;
    
    $posts = Post::doesntHave('comments')->get();
若需要更多功能，可以使用 `whereDoesntHave` 或 `orWhereDoesntHave` 方法來在 `doesntHave` 查詢上加上額外的查詢條件，如檢查留言的內容等：

    use Illuminate\Database\Eloquent\Builder;
    
    $posts = Post::whereDoesntHave('comments', function (Builder $query) {
        $query->where('content', 'like', 'code%');
    })->get();
我們也可以使用「點 (.)」標記法來對巢狀關聯進行查詢。舉例來說，下列查詢會取得所有沒有留言的貼文。不過，具有未禁言作者發表留言的文章也會被包含在結果裡面：

    use Illuminate\Database\Eloquent\Builder;
    
    $posts = Post::whereDoesntHave('comments.author', function (Builder $query) {
        $query->where('banned', 0);
    })->get();
<a name="querying-morph-to-relationships"></a>

### 查詢 Morph To 關聯

若要查詢「Morph To」關聯是否存在，可以使用 `whereHasMorph` 或 `whereDoesntHaveMorph` 方法。這些方法都接受關聯名稱作為其第一個引數。接著，這個方法還接受要被包含在查詢裡的關聯 Model 名稱。最後，我們還可以提供用來自訂關聯查詢的閉包：

    use App\Models\Comment;
    use App\Models\Post;
    use App\Models\Video;
    use Illuminate\Database\Eloquent\Builder;
    
    // Retrieve comments associated to posts or videos with a title like code%...
    $comments = Comment::whereHasMorph(
        'commentable',
        [Post::class, Video::class],
        function (Builder $query) {
            $query->where('title', 'like', 'code%');
        }
    )->get();
    
    // Retrieve comments associated to posts with a title not like code%...
    $comments = Comment::whereDoesntHaveMorph(
        'commentable',
        Post::class,
        function (Builder $query) {
            $query->where('title', 'like', 'code%');
        }
    )->get();
有時候，我們可能會想依據多型關聯 Model 的「類型」來新增查詢條件。傳給 `whereHasMorph` 方法的閉包可接受一個 `$type` 值作為其第二個引述。使用 `$type`引述，就可以檢查正在建立的查詢是什麼「類型」：

    use Illuminate\Database\Eloquent\Builder;
    
    $comments = Comment::whereHasMorph(
        'commentable',
        [Post::class, Video::class],
        function (Builder $query, string $type) {
            $column = $type === Post::class ? 'content' : 'title';
    
            $query->where($column, 'like', 'code%');
        }
    )->get();
Sometimes you may want to query for the children of a "morph to" relationship's parent. You may accomplish this using the `whereMorphedTo` and `whereNotMorphedTo` methods, which will automatically determine the proper morph type mapping for the given model. These methods accept the name of the `morphTo` relationship as their first argument and the related parent model as their second argument:

    $comments = Comment::whereMorphedTo('commentable', $post)
                          ->orWhereMorphedTo('commentable', $video)
                          ->get();
<a name="querying-all-morph-to-related-models"></a>

#### 查詢所有關聯的 Model

我們可以提供 `*` 作為萬用字元，而不需以陣列列出所有可能的多型 Model。這樣以來 Laravel 就會從資料庫中取得所有可能的多型類型。Laravel 會執行一個額外的查詢來進行此行動：

    use Illuminate\Database\Eloquent\Builder;
    
    $comments = Comment::whereHasMorph('commentable', '*', function (Builder $query) {
        $query->where('title', 'like', 'foo%');
    })->get();
<a name="aggregating-related-models"></a>

## 彙總關聯的 Model

<a name="counting-related-models"></a>

### 計數關聯的 Model

有時候我們可能會想知道給定關聯中關聯 Model 的數量，但又不想真正載入這些 Model。為此，我們可以使用 `withCount` 方法。`withCount` 方法會在查詢結果的 Model 中加上一個 `{關聯}_count` 屬性：

    use App\Models\Post;
    
    $posts = Post::withCount('comments')->get();
    
    foreach ($posts as $post) {
        echo $post->comments_count;
    }
只要將陣列傳入 `withCount` 方法，就可以為多個關聯「計數」，或是在查詢上加上額外的查詢條件：

    use Illuminate\Database\Eloquent\Builder;
    
    $posts = Post::withCount(['votes', 'comments' => function (Builder $query) {
        $query->where('content', 'like', 'code%');
    }])->get();
    
    echo $posts[0]->votes_count;
    echo $posts[0]->comments_count;
也可以為關聯總數結果加上別名，這樣就能對單一關聯計算多次數量：

    use Illuminate\Database\Eloquent\Builder;
    
    $posts = Post::withCount([
        'comments',
        'comments as pending_comments_count' => function (Builder $query) {
            $query->where('approved', false);
        },
    ])->get();
    
    echo $posts[0]->comments_count;
    echo $posts[0]->pending_comments_count;
<a name="deferred-count-loading"></a>

#### 延後 (Deferred) 數量計算的載入

使用 `loadCount` 方法，就可以在上層 Model 已經載入後再接著載入關聯的計數：

    $book = Book::first();
    
    $book->loadCount('genres');
若想在計數查詢上設定額外的查詢條件，可以傳入一組陣列，其索引鍵應為要計數的關聯。陣列的值則為一個閉包，用來接收 Query Builder 實體：

    $book->loadCount(['reviews' => function (Builder $query) {
        $query->where('rating', 5);
    }])
<a name="relationship-counting-and-custom-select-statements"></a>

#### Relationship Counting and Custom Select Statements

若想組合使用 `withCount` 與 `select` 陳述式，請在 `select` 方法後再呼叫 `withCount`：

    $posts = Post::select(['title', 'body'])
                    ->withCount('comments')
                    ->get();
<a name="other-aggregate-functions"></a>

### 其他彙總函式

除了 `withCount` 方法外，Eloquent 也提供了 `withMin`, `withMax`, `withAvg`, `withSum`, 與 `withExists` 等方法。這些方法會在查詢結果的 Model 上加上一個  `{關聯}_{函式}_{欄位}` 屬性：

    use App\Models\Post;
    
    $posts = Post::withSum('comments', 'votes')->get();
    
    foreach ($posts as $post) {
        echo $post->comments_sum_votes;
    }
若想使用另一個名稱來存取彙總函式的結果，可自行指定別名：

    $posts = Post::withSum('comments as total_comments', 'votes')->get();
    
    foreach ($posts as $post) {
        echo $post->total_comments;
    }
與 `loadCount` 方法類似，Eloquent 中也有這些方法的延遲 (Deferred) 版本。可以在已經取得的 Eloquent Model 上進行這些額外的彙總運算：

    $post = Post::first();
    
    $post->loadSum('comments', 'votes');
若想組合使用這些彙總與 `select` 陳述式，請在 `select` 方法後再呼叫這些彙總函式：

    $posts = Post::select(['title', 'body'])
                    ->withExists('comments')
                    ->get();
<a name="counting-related-models-on-morph-to-relationships"></a>

### Counting Related Models on Morph To Relationships

若想積極式載入「Morph to」關聯、或是關聯 Model 計數等由關聯回傳的功能，可以使用 `morphTo` 關聯的 `morphWithCount` 方法，並搭配 `with` 方法使用。

在這個例子中，我們假設 `Photo` 與 `Post` Model 會建立 `ActivityFeed` Model。假設 `ActivityFeed` Model 定義一個名為  `parentable` 的「Morph to」關聯，可讓使用者在某一 `ActivityFeed` 實體上取得上層的 `Photo` 或 `Post` Model。此外，我們也假設 `Photo` Model「Have Many (有多個)」 `Tag` Model，而 `Post` Model「Have Many」`Comment` Model。

接著，來假設我們現在要去的 `ActivityFeed` 實體，並為取得的每個 `ActivityFeed` 實體積極式載入 `parentable` 上層 Model。此外，我們也想知道上層的每張圖片各有多少個 Tag、還有上層的每篇貼文各有多少則留言：

    use Illuminate\Database\Eloquent\Relations\MorphTo;
    
    $activities = ActivityFeed::with([
        'parentable' => function (MorphTo $morphTo) {
            $morphTo->morphWithCount([
                Photo::class => ['tags'],
                Post::class => ['comments'],
            ]);
        }])->get();
<a name="morph-to-deferred-count-loading"></a>

#### 延後 (Deferred) 數量計算的載入

假設我們已經取得 `ActivityFeed` Model (活動摘要)，接著，我們想要載入與活動摘要關聯的各種 `parentable` Model 的巢狀關聯數量。我們可以使用 `loadMorphCount` 方法來完成：

    $activities = ActivityFeed::with('parentable')->get();
    
    $activities->loadMorphCount('parentable', [
        Photo::class => ['tags'],
        Post::class => ['comments'],
    ]);
<a name="eager-loading"></a>

## 積極式載入

以屬性方式存取 Eloquent 關聯時，關聯的 Model 會被「消極式載入 (Lazy Load)」。這表示，直到首次存取該屬性前，關聯資料都不會被載入。不過，Eloquent 也可以在查詢上層 Model 時就「積極式載入 (Eager Load)」關聯。積極式載入可以減少「N + 1」問題。為了示範什麼是 N + 1 問題，我們先假設有個「隸屬於 (Belongs to)」`Author` Model 的 `Book` Model：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    
    class Book extends Model
    {
        /**
         * Get the author that wrote the book.
         */
        public function author(): BelongsTo
        {
            return $this->belongsTo(Author::class);
        }
    }
現在，我們來取得所有書籍與其作者：

    use App\Models\Book;
    
    $books = Book::all();
    
    foreach ($books as $book) {
        echo $book->author->name;
    }
這個迴圈會執行一個查詢來取得資料表中所有的書籍，然後每本書都會再執行一個查詢來取得書籍的作者。因此，若我們有 25 本書，上述程式碼就會執行 26 筆資料庫查詢：1 個查詢來取得書籍，另外 25 個額外的查詢來取得每本書的作者。

幸好，我們可以使用積極式載入來把這一連串行動降低為只需要 2 個查詢。在建立查詢時，可以使用 `with` 方法來指定哪個關聯要被積極式載入：

    $books = Book::with('author')->get();
    
    foreach ($books as $book) {
        echo $book->author->name;
    }
這樣一來，就只會執行 2 個查詢 —— 一個查詢去的所有的書籍，另一個查詢則取得所有書籍的作者。

```sql
select * from books

select * from authors where id in (1, 2, 3, 4, 5, ...)
```
<a name="eager-loading-multiple-relationships"></a>

#### 積極式載入多個關聯

有時候，我們可能需要積極式載入多個不同的關聯。要載入多個不同的關聯，只需要傳入一組包含關聯的陣列給 `with` 方法即可：

    $books = Book::with(['author', 'publisher'])->get();
<a name="nested-eager-loading"></a>

#### 巢狀積極式載入

若要積極載入關聯的關聯，可以使用「點 (.)」標記法。舉例來說，讓我們來積極載入所有書籍的作者，以及所有作者的聯絡方式 (Contact)：

    $books = Book::with('author.contacts')->get();
或者，只要傳入一組巢狀陣列給 `with` 方法，就可以積極式載入巢狀關聯。若要積極式載入多個巢狀關聯，該方法很好用：

    $books = Book::with([
        'author' => [
            'contacts',
            'publisher',
        ],
    ])->get();
<a name="nested-eager-loading-morphto-relationships"></a>

#### 積極載入巢狀的 `morphTo` 關聯

若想積極載入 `morphTo` 關聯、或是巢狀的關聯等由 morphTo 關聯回傳的功能，可以使用 `morphTo` 關聯的 `morphWith` 方法，並搭配 `with` 方法使用。為了讓我們更瞭解這個功能，我們先來看看下列 Model：

    <?php
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    
    class ActivityFeed extends Model
    {
        /**
         * Get the parent of the activity feed record.
         */
        public function parentable(): MorphTo
        {
            return $this->morphTo();
        }
    }
在這個例子中，先假設 `Event`, `Photo`, 與 `Post` 會建立 `ActivityFeed` Model。另外，也來假設 `Event` Model 隸屬於 `Calendar` Model，而 `Photo` Model 則與 `Tag` Model 相關聯，然後 `Post` Model 隸屬於 `Author` Model。

有了這些 Model 定義與關聯，我們就可以取得 `ActivityFeed` Model 實體，然後積極載入所有 `parentable` Model 與這些 `parentable` Model 的巢狀關聯：

    use Illuminate\Database\Eloquent\Relations\MorphTo;
    
    $activities = ActivityFeed::query()
        ->with(['parentable' => function (MorphTo $morphTo) {
            $morphTo->morphWith([
                Event::class => ['calendar'],
                Photo::class => ['tags'],
                Post::class => ['author'],
            ]);
        }])->get();
<a name="eager-loading-specific-columns"></a>

#### 積極載入特定欄位

有時候，我們可能並不像取得關聯的所有欄位。為此，Eloquent 能讓我們指定要取得關聯的哪些欄位：

    $books = Book::with('author:id,name,book_id')->get();
> [!WARNING]  
> 使用這個功能時，請務必在欄位列表中包含 `id` 欄位以及其他相關的外部索引鍵欄位。

<a name="eager-loading-by-default"></a>

#### Eager Loading by Default

對於某些 Model，我們可能會希望這個 Model 總是能載入一些關聯。為此，我們可以在這種 Model 上定義一個 `$with` 屬性：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    
    class Book extends Model
    {
        /**
         * The relationships that should always be loaded.
         *
         * @var array
         */
        protected $with = ['author'];
    
        /**
         * Get the author that wrote the book.
         */
        public function author(): BelongsTo
        {
            return $this->belongsTo(Author::class);
        }
    
        /**
         * Get the genre of the book.
         */
        public function genre(): BelongsTo
        {
            return $this->belongsTo(Genre::class);
        }
    }
若想為單一查詢移除 `$with` 屬性中的某個項目，可以使用 `without` 方法：

    $books = Book::without('author')->get();
若想為單一查詢複寫 `$with` 屬性中的所有項目，可以使用 `withOnly` 方法：

    $books = Book::withOnly('genre')->get();
<a name="constraining-eager-loads"></a>

### 包含查詢條件的積極載入

在積極載入關聯時，我們有時候可能會希望能給積極載入查詢指定額外的查詢條件。可以通過傳入一組包含關聯的陣列給 `with` 方法來達成。這個陣列的索引鍵應為關聯的名稱，而陣列值則為要給積極載入查詢加上額外查詢條件的閉包：

    use App\Models\User;
    use Illuminate\Contracts\Database\Eloquent\Builder;
    
    $users = User::with(['posts' => function (Builder $query) {
        $query->where('title', 'like', '%code%');
    }])->get();
在這個例子中，Eloquent 只會積極載入 `title` 欄位含有關鍵字 `code` 的文章。你還可以呼叫其他的 [Query Builder](/docs/{{version}}/queries) 方法來進一步自訂積極式載入：

    $users = User::with(['posts' => function (Builder $query) {
        $query->orderBy('created_at', 'desc');
    }])->get();
<a name="constraining-eager-loading-of-morph-to-relationships"></a>

#### Constraining Eager Loading of `morphTo` Relationships

在積極載入 `morphTo` 關聯時，Eloquent 會為關聯 Model 的每個類型都執行多筆查詢。我們可以使用 `MorphTo` 關聯的 `constrain` 方法來對這些查詢分別加上額外的查詢條件：

    use Illuminate\Database\Eloquent\Relations\MorphTo;
    
    $comments = Comment::with(['commentable' => function (MorphTo $morphTo) {
        $morphTo->constrain([
            Post::class => function ($query) {
                $query->whereNull('hidden_at');
            },
            Video::class => function ($query) {
                $query->where('type', 'educational');
            },
        ]);
    }])->get();
在這個範例中，Eloquent 只會積極載入非隱藏的貼文，以及 `type` 值不是「educational」的影片。

<a name="constraining-eager-loads-with-relationship-existence"></a>

#### 通過判斷關聯是否存在來作為 Eager Loading 的條件

有時候，我們可能需要檢查某個關聯是否存在，而同時又要依照這個條件來載入關聯。舉例來說，我們想取得 `User` Model，而這些 `User` Model 必須擁有滿足某個查詢條件的 `Post` Model，並且在這些 User 上積極載入符合這些條件的 `Post`。這種情況可以使用 `withWhereHas` 方法來達成：

    use App\Models\User;
    
    $users = User::withWhereHas('posts', function ($query) {
        $query->where('featured', true);
    })->get();
<a name="lazy-eager-loading"></a>

### 消極的積極式載入

有時候，我們可能需要在已取得上層 Model 後才積極載入某個關聯。舉例來說，當想動態決定是否要載入關聯 Model 時，這種功能特別適合：

    use App\Models\Book;
    
    $books = Book::all();
    
    if ($someCondition) {
        $books->load('author', 'publisher');
    }
若想在積極載入查詢上設定額外的查詢條件，可以傳入一組陣列，其索引鍵應為要載入的關聯。陣列的值則為一個閉包，用來接收 Query Builder 實體：

    $author->load(['books' => function (Builder $query) {
        $query->orderBy('published_date', 'asc');
    }]);
若想只在某個關聯未被載入時才載入該關聯，可使用 `loadMissing` 方法：

    $book->loadMissing('author');
<a name="nested-lazy-eager-loading-morphto"></a>

#### Nested Lazy Eager Loading and `morphTo`

若想積極式載入 `morphTo` 關聯、或是關聯 Model 的巢狀關聯等由 morphTo 關聯所回傳的功能，可以使用 `loadMorph` 方法：

這個方法的第一個引數是 `morphTo` 關聯的名稱，第二個引數則是一組包含 Model / 關聯配對的陣列。為了說明這個功能，先來看看下列 Model：

    <?php
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    
    class ActivityFeed extends Model
    {
        /**
         * Get the parent of the activity feed record.
         */
        public function parentable(): MorphTo
        {
            return $this->morphTo();
        }
    }
在這個例子中，先假設 `Event`, `Photo`, 與 `Post` 會建立 `ActivityFeed` Model。另外，也來假設 `Event` Model 隸屬於 `Calendar` Model，而 `Photo` Model 則與 `Tag` Model 相關聯，然後 `Post` Model 隸屬於 `Author` Model。

有了這些 Model 定義與關聯，我們就可以取得 `ActivityFeed` Model 實體，然後積極載入所有 `parentable` Model 與這些 `parentable` Model 的巢狀關聯：

    $activities = ActivityFeed::with('parentable')
        ->get()
        ->loadMorph('parentable', [
            Event::class => ['calendar'],
            Photo::class => ['tags'],
            Post::class => ['author'],
        ]);
<a name="preventing-lazy-loading"></a>

### 預防消極載入

前面也說明過，對你的專案來說，積極載入關聯通常可以顯著提升效能。因此，我們可能會希望讓 Laravel 總是避免消極式載入關聯。為此，我們可以呼叫基礎 Eloquent Model 上的 `preventLazyLoading` 方法。一般來說，應該在你的專案中 `AppServiceProvider` 類別的 `boot` 方法內呼叫這個方法。

`preventLazyLoading` 方法接受一個可選的布林引數，用來判斷是否應防止消極式載入。舉例來說，我們肯跟會希望只在非正式環境下才進用消極式載入，這樣一來，就算正式環境上的程式碼內不小心有個消極式載入的關聯，正式環境也可以正常運作：

```php
use Illuminate\Database\Eloquent\Model;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Model::preventLazyLoading(! $this->app->isProduction());
}
```
阻止消極式載入後，當程式嘗試要消極載入任何 Eloquent 關聯時，Eloquent 會擲回一個 `Illuminate\Database\LazyLoadingViolationException` 例外。

可以使用 `handleLazyLoadingViolationsUsing` 方法來自訂當發生消極載入時要如何處置。舉例來說，我們可以使用這個方法來讓 Laravel 在遇到消極載入的時候紀錄到日誌，而不是使用例外在終止程式的執行：

```php
Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
    $class = $model::class;

    info("Attempted to lazy load [{$relation}] on model [{$class}].");
});
```
<a name="inserting-and-updating-related-models"></a>

## Inserting and Updating Related Models

<a name="the-save-method"></a>

### `save` 方法

Eloquent 提供了一些方便的方法來給關聯新增新 Model。舉例來說，我們可能會需要給貼文新增新留言。比起手動在 `Comment` Model 上設定 `post_id`，我們可以使用關聯的 `save` Model 來插入留言：

    use App\Models\Comment;
    use App\Models\Post;
    
    $comment = new Comment(['message' => 'A new comment.']);
    
    $post = Post::find(1);
    
    $post->comments()->save($comment);
請注意，我們不是以動態屬性的方式來存取 `comment` 關聯，而是呼叫 `comments` 方法來取得關聯的實體。`save` 方法會自動為新建立的 `Comment` Model 加上適當的 `post_id` 值。

若有需要保存多個關聯 Model，可以使用 `saveMany` 方法：

    $post = Post::find(1);
    
    $post->comments()->saveMany([
        new Comment(['message' => 'A new comment.']),
        new Comment(['message' => 'Another new comment.']),
    ]);
`save` 與 `saveMany` 會將 Model 實體保存起來。不過，保存好的 Model 並不會被加到上層 Model 中已經載入到記憶體的關聯。在使用 `save` 或 `saveMany` 方法後，若有打算要存取這些關聯，可使用 `refresh` 方法來重新載入 Model 與其關聯：

    $post->comments()->save($comment);
    
    $post->refresh();
    
    // All comments, including the newly saved comment...
    $post->comments;
<a name="the-push-method"></a>

#### Recursively Saving Models and Relationships

若想讓 `save` 方法保存 Model 與其所有相關的關聯 Model，可以使用 `push` 方法。在這個例子中，`Post` Model、`Post` Model 的留言、留言的作者等都會一起被保存：

    $post = Post::find(1);
    
    $post->comments[0]->message = 'Message';
    $post->comments[0]->author->name = 'Author Name';
    
    $post->push();
`pushQuietly` 方法可用在不產生任何 Event 的情況下來保存 Model 於其關聯：

    $post->pushQuietly();
<a name="the-create-method"></a>

### `create` 方法

除了 `save` 跟 `saveMany` 方法外，也可以使用 `create` 方法來建立 Model 並插入資料庫。`create` 方法接受一組包含屬性的陣列。`save` 與 `create` 間不同的地方在於：`save` 接收完整的 Eloquent Model 實體，而 `create` 接收的是純 PHP 的 `array`。`create` 方法會回傳新建立的 Model：

    use App\Models\Post;
    
    $post = Post::find(1);
    
    $comment = $post->comments()->create([
        'message' => 'A new comment.',
    ]);
可以使用 `createMany` 方法來建立多個關聯的 Model：

    $post = Post::find(1);
    
    $post->comments()->createMany([
        ['message' => 'A new comment.'],
        ['message' => 'Another new comment.'],
    ]);
`createQuietly` 與 `createManyQuietly` 方法可用來在不分派任何 Event 的情況下建立 Model：

    $user = User::find(1);
    
    $user->posts()->createQuietly([
        'title' => 'Post title.',
    ]);
    
    $user->posts()->createManyQuietly([
        ['title' => 'First post.'],
        ['title' => 'Second post.'],
    ]);
也可以使用 `findOrNew`, `firstOrNew`, `firstOrCreate`, 與 `updateOrCreate` 等方法來[在關聯上建立並更新 Model](/docs/{{version}}/eloquent#upserts)。

> [!NOTE]  
> 在使用 `create` 方法前，請先閱讀[大量賦值](/docs/{{version}}/eloquent#mass-assignment)的說明文件。

<a name="updating-belongs-to-relationships"></a>

### Belongs To 關聯

若想將子 Model 指派給新的上層 Model，可以使用 `associate` 方法。在這個例子中，`User` Model 定義了一個連到 `Account` Model 的 `belongsTo` 關聯。`associate` 方法會在子 Model 上設定外部索引鍵：

    use App\Models\Account;
    
    $account = Account::find(10);
    
    $user->account()->associate($account);
    
    $user->save();
若要從子 Model 上移除上層 Model，可以使用 `dissociate` 方法。這個方法會將關聯的外部索引鍵設為 `null`：

    $user->account()->dissociate();
    
    $user->save();
<a name="updating-many-to-many-relationships"></a>

### Many to Many Relationships

<a name="attaching-detaching"></a>

#### 附加 / 解除附加

Eloquent 還提供一些能讓處理多對多關聯更方便的方法。舉例來說，先假設一個使用者 (User) 可以有多個職位 (Role)，而一個職位可以有多個使用者。可以使用 `attach` 方法來將某個職位附加到使用者身上，`attach` 會在關聯的中介資料表上插入一筆紀錄來完成：

    use App\Models\User;
    
    $user = User::find(1);
    
    $user->roles()->attach($roleId);
在把關聯附加到 Model 上時，可以傳入一組陣列，包含額外要被插入到中介資料表上的資料：

    $user->roles()->attach($roleId, ['expires' => $expires]);
有時候，我們還會需要從使用者身上移除某個職位。若要移除 Many-to-Many 關聯的紀錄，請使用 `detach` 方法。`detach` 方法會從中介資料表上移除相應的紀錄。不過，使用者跟職位兩個 Model 都還會保留在資料庫中：

    // Detach a single role from the user...
    $user->roles()->detach($roleId);
    
    // Detach all roles from the user...
    $user->roles()->detach();
為了更方便使用，`attach` 與 `detach` 也能接受一組包含 ID 的陣列作為輸入：

    $user = User::find(1);
    
    $user->roles()->detach([1, 2, 3]);
    
    $user->roles()->attach([
        1 => ['expires' => $expires],
        2 => ['expires' => $expires],
    ]);
<a name="syncing-associations"></a>

#### 同步關聯

可以使用 `sync` 方法來設定 Many-to-Many 關聯。`sync` 方法接受一組包含 ID 的陣列，用以插入中介資料表。中介資料表中若有不在此陣列中的 ID 則會被移除。因此，完成這個操作後，中介資料表中就只會有給定陣列中的 ID：

    $user->roles()->sync([1, 2, 3]);
也可以使用 ID 來傳入額外的中介資料表值：

    $user->roles()->sync([1 => ['expires' => true], 2, 3]);
如喔想為每個同步的 Model ID 都插入相同的中介資料表值，則可以使用 `syncWithPivotValue` 方法：

    $user->roles()->syncWithPivotValues([1, 2, 3], ['active' => true]);
若想從給定陣列中移除現有的 ID，則可以使用 `syncWithoutDetaching` 方法：

    $user->roles()->syncWithoutDetaching([1, 2, 3]);
<a name="toggling-associations"></a>

#### 切換關聯

Many-to-Many 關聯還提供了一個 `toggle` 方法，可以用來「切換 (Toggle)」給定關聯 Model ID 的附加狀態。若給定的 ID 目前是已附加的狀態，則該 ID 會被解除附加。反之，若目前未附加，則會被附加上去：

    $user->roles()->toggle([1, 2, 3]);
也可以使用 ID 來傳入額外的中介資料表值：

    $user->roles()->toggle([
        1 => ['expires' => true],
        2 => ['expires' => true],
    ]);
<a name="updating-a-record-on-the-intermediate-table"></a>

#### Updating a Record on the Intermediate Table

若想更新關聯的中介資料表上現有的紀錄，可以使用 `updateExistingPivot` 方法。這個方法接受中介資料表的外部索引鍵以及一組包含要更新屬性的陣列：

    $user = User::find(1);
    
    $user->roles()->updateExistingPivot($roleId, [
        'active' => false,
    ]);
<a name="touching-parent-timestamps"></a>

## 更新上層的時戳

若某 Model 有定義對另一個 Model 的 `belongsTo` 或 `belongsToMany` 關聯 —— 如 `Comment` Model 隸屬於 `Post` Model 等 —— 有時候，若能在子 Model 更新時也一併更新上層 Model 的時戳會很實用。

舉例來說，當 `Comment` Model 更新後，我們可能會想自動「更新 (Touch)」擁有該 `Comment` 的 `Post` Model 上的 `updated_at` 時戳，將該時戳設為目前的日期與時間。為此，我們可以在子 Model 內新增一個 `touches` 屬性，其中包含關聯的名稱。當子 Model 更新後，這些關聯的 `updated_at` 時戳也會一起更新：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    
    class Comment extends Model
    {
        /**
         * All of the relationships to be touched.
         *
         * @var array
         */
        protected $touches = ['post'];
    
        /**
         * Get the post that the comment belongs to.
         */
        public function post(): BelongsTo
        {
            return $this->belongsTo(Post::class);
        }
    }
> [!WARNING]  
> 只有在使用 Eloquent 的 `save` 方法來更新子 Model 時，才會更新上傳 Model 的時戳。
