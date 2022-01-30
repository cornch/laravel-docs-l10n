# Eloquent：關聯

- [簡介](#introduction)
- [定義關聯](#defining-relationships)
    - [一對一](#one-to-one)
    - [一對多](#one-to-many)
    - [一對多 (反向) / Belongs To](#one-to-many-inverse)
    - [一對多中之一](#has-one-of-many)
    - [間接一對一](#has-one-through)
    - [間接一對多](#has-many-through)
- [多對多關聯](#many-to-many)
    - [存取中間資料表欄位](#retrieving-intermediate-table-columns)
    - [通過中間資料表欄位來過濾查詢](#filtering-queries-via-intermediate-table-columns)
    - [定義自訂的中間資料表 Model](#defining-custom-intermediate-table-models)
- [多型關聯](#polymorphic-relationships)
    - [一對一](#one-to-one-polymorphic-relations)
    - [一對多](#one-to-many-polymorphic-relations)
    - [多中之一](#one-of-many-polymorphic-relations)
    - [多對多](#many-to-many-polymorphic-relations)
    - [自訂多型類型](#custom-polymorphic-types)
- [動態關聯](#dynamic-relationships)
- [查詢關聯](#querying-relations)
    - [關聯方法 Vs. 動態屬性](#relationship-methods-vs-dynamic-properties)
    - [查詢關聯存在](#querying-relationship-existence)
    - [查詢關聯不存在](#querying-relationship-absence)
    - [查詢多型關聯](#querying-morph-to-relationships)
- [匯總關聯的 Model](#aggregating-related-models)
    - [計算關聯的 Model 數量](#counting-related-models)
    - [其他匯總函式](#other-aggregate-functions)
    - [計算關聯 Model 的多型關聯數量](#counting-related-models-on-morph-to-relationships)
- [積極式載入](#eager-loading)
    - [帶條件的積極式載入](#constraining-eager-loads)
    - [消極的積極式載入](#lazy-eager-loading)
    - [預防消極式載入](#preventing-lazy-loading)
- [插入與更新關聯的 Model](#inserting-and-updating-related-models)
    - [`save` 方法](#the-save-method)
    - [`create` 方法](#the-create-method)
    - [BelongsTo 關聯](#updating-belongs-to-relationships)
    - [多對多關聯](#updating-many-to-many-relationships)
- [更新上層 Model 的時戳](#touching-parent-timestamps)

<a name="introduction"></a>
## 簡介

資料庫中的資料表通常會互相彼此關聯。舉例來說，部落格文章可能會有許多的留言，而訂單則可能會關聯到建立訂單的使用者。在 Eloquent
中，要管理並處理這些關聯非常簡單，並支援多種常見的關聯：

<div class="content-list" markdown="1">
- [一對一](#one-to-one)
- [一對多](#one-to-many)
- [多對一](#many-to-many)
- [間接一對一](#has-one-through)
- [間接一對多](#has-many-through)
- [一對一 (多型)](#one-to-one-polymorphic-relations)
- [一對多 (多型)](#one-to-many-polymorphic-relations)
- [多對多 (多型)](#many-to-many-polymorphic-relations)
</div>

<a name="defining-relationships"></a>
## 定義關聯

Eloquent 關聯是作為方法定義在 Eloquent Model 類別中。由於關聯也可當作強大的 [Query
Builder](/docs/{{version}}/queries)
使用，因此將關聯定義為方法也能讓方法得以串連使用並進行查詢。舉例來說，我們可以在這個 `posts` 關聯中串上額外的查詢條件：

    $user->posts()->where('active', 1)->get();

不過，在更深入瞭解如何使用關聯以前，我們先來了解一下如何定義 Eloquent 所支援的各種關聯型別吧！

<a name="one-to-one"></a>
### 一對一

一對一關聯是一種非常基本的資料庫關聯。舉例來說，一個 `User` Model 可能與一個 `Phone` Model 有關。要定義這個關聯，我們先在
`User` Model 中定義一個 `phone` 方法。`phone` 方法應呼叫 `hasOne` 方法並回傳其結果。`hasOne` 方法是通過
Model 的 `Illuminate\Database\Eloquent\Model` 基礎類別提供的：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        /**
         * Get the phone associated with the user.
         */
        public function phone()
        {
            return $this->hasOne(Phone::class);
        }
    }

傳給 `hasOne` 方法的第一個引述是關聯 Model 類別的名稱。定義好關聯後，我們就可以通過 Eloquent
的動態屬性來存取這個關聯的紀錄。動態屬性能讓我們像在存取定義在 Model 上的屬性一樣來存取關聯方法：

    $phone = User::find(1)->phone;

Eloquent 會通過上層 Model 的名稱來判斷關聯的外部索引鍵 (Foreign Key)。在這個例子中，Eloquent 會自動假設
`Phone` Model 中有個 `user_id` 外部索引鍵。若要複寫這個慣例用法的話，可以傳入第二個引數給 `hasOne` 方法：

    return $this->hasOne(Phone::class, 'foreign_key');

此外，Eloquent 還會假設這個外部索引鍵應該要有個與上層資料的主索引鍵欄位相同的值。換句話說，Eloquent 會在 `Phone` 紀錄的
`user_id` 欄位中找到與該使用者 `id` 欄位值相同的資料。若想在關聯中使用 `id` 或 Model 的 `$primaryKey`
屬性意外的其他主索引鍵值的話，可傳入第三個引數給 `hasOne` 方法：

    return $this->hasOne(Phone::class, 'foreign_key', 'local_key');

<a name="one-to-one-defining-the-inverse-of-the-relationship"></a>
#### 定義反向的關聯

好了，我們現在可以在 `User` Model 中存取 `Phone` Model 了。接著，我們來在 `Phone` Model
上定義關聯，好讓我們能在存取擁有這隻電話的使用者。我們可以使用 `belongsTo` 方法來定義反向的 `hasOne` 關聯：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Phone extends Model
    {
        /**
         * Get the user that owns the phone.
         */
        public function user()
        {
            return $this->belongsTo(User::class);
        }
    }

當叫用 `user` 方法時，Eloquent 會嘗試尋找一筆 `id` 符合 `Phone` Model 中 `user_id` 欄位的 `User`
Model。

Eloquent 會檢查關聯方法的名稱，並在這個方法的名稱後加上 `_id` 來自動判斷外部索引鍵名稱。因此，在這個例子中，Eloquent 會假設
`Phone` Model 有個 `user_id` 欄位。不過，若 `Phone` Model 的外部索引鍵不是
`user_id`，則可以傳遞一個自訂索引鍵名稱給 `belongsTo`，作為第二個引數：

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'foreign_key');
    }

若上層 Model 不使用 `id` 作為其主索引鍵，或是想要使用不同的欄位來尋找關聯的 Model，則可以傳遞第三個引數給 `belongsTo`
方法來指定上層資料表的自訂索引鍵：

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'foreign_key', 'owner_key');
    }

<a name="one-to-many"></a>
### 一對多

一對多關聯可用來定義某個有一個或多個子 Model 的單一 Model。舉例來說，部落格文章可能有無限數量筆留言。與其他 Eloquent
關聯一樣，一對多關聯可通過在 Eloquent Model 中定義方法來定義：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Post extends Model
    {
        /**
         * Get the comments for the blog post.
         */
        public function comments()
        {
            return $this->hasMany(Comment::class);
        }
    }

請記得，Eloquent 會自動為 `Comment` Model 判斷適當的外部索引鍵欄位。依照慣例，Eloquent 會去上層 Model
的「蛇形命名法 (snake_case)」名稱，並在其後加上 `_id`。因此，在這個例子中，Eloquent 會假設 `Comment` Model
上的外部索引鍵欄位為 `post_id`。

定義好關聯方法後，我們就可以通過 `comments` 屬性來存取關聯留言的
[Collection](/docs/{{version}}/eloquent-collections)。請記得，由於 Eloquent
提供了「動態關聯屬性」，因此我們可以像我們是在 Model 上定義屬性一樣地存取關聯方法：

    use App\Models\Post;

    $comments = Post::find(1)->comments;

    foreach ($comments as $comment) {
        //
    }

由於所有的關聯也同時是 Query Builder，因此我們也能通過呼叫 `comments` 方法並繼續在查詢上串上條件來進一步給關聯加上查詢條件：

    $comment = Post::find(1)->comments()
                        ->where('title', 'foo')
                        ->first();

就像 `hasOne` 方法，我們也可以通過傳遞額外的參數給 `hasMany` 來複寫外部與內部的索引鍵：

    return $this->hasMany(Comment::class, 'foreign_key');

    return $this->hasMany(Comment::class, 'foreign_key', 'local_key');

<a name="one-to-many-inverse"></a>
### 一對多 (反向) / 隸屬於 (Belongs To)

現在，我們已經可以存取一篇文章的所有留言了。讓我們來定義一個關聯，以從留言去的其上層的文章。要定義 `hasMany` 關聯的相反，我們可以在子
Model 中定義一個呼叫了 `belongsTo` 方法的關聯方法：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Comment extends Model
    {
        /**
         * Get the post that owns the comment.
         */
        public function post()
        {
            return $this->belongsTo(Post::class);
        }
    }

定義好關聯後，我們就可以通過存取 `post`「動態關聯屬性」來取得留言的上層文章：

    use App\Models\Comment;

    $comment = Comment::find(1);

    return $comment->post->title;

在上述例子中，Eloquent 會嘗試找到 `id` 符合 `Comments` Model 中 `post_id` 欄位的 `Post` Model。

Eloquent 會檢查關聯方法的名稱，並在該名稱後加上 `_`，然後再加上上層 Model
的主索引鍵欄位名稱作為預設的外部索引鍵名稱。因此，在這個例子中，Eloquent 會假設 `Post` Model 在 `comments`
資料表中的外部索引鍵為 `post_id`。

不過，若沒有依照這種慣例來命名關聯的外部索引鍵，則可以將自訂的外部索引鍵傳遞給 `belongsTo` 方法作為第二個引數：

    /**
     * Get the post that owns the comment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'foreign_key');
    }

若上層 Model 不使用 `id` 作為其主索引鍵，或是想要使用不同的欄位來尋找關聯的 Model，則可以傳遞第三個引數給 `belongsTo`
方法來指定上層資料表的自訂索引鍵：

    /**
     * Get the post that owns the comment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'foreign_key', 'owner_key');
    }

<a name="default-models"></a>
#### 預設 Model

`belongsTo`, `hasOne`, `hasOneThrough`, 以及 `morphOne` 關聯可定義一個預設
Model，當給定的關聯為 `null` 時會回傳該預設 Model。這種模式通常稱為 [Null Object
pattern](https://en.wikipedia.org/wiki/Null_Object_pattern)，並能讓你在程式碼中減少條件檢查的次數。在下列範例中，`user`
關聯會在沒有使用者附加在 `Post` Model 時回傳一個空的 `App\Models\User` Model：

    /**
     * Get the author of the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

若要為預設的 Model 設定屬性，則可以傳入陣列或閉包給 `withDefault` 方法：

    /**
     * Get the author of the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Guest Author',
        ]);
    }

    /**
     * Get the author of the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(function ($user, $post) {
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

預設情況下，Larave 會依據 Model 的類別名稱來判斷與給定 Model 有關的關聯。不過，我們也可以通過傳入第二個引數給
`whereBelongsTo` 方法來手動指定關聯的名稱：

    $posts = Post::whereBelongsTo($user, 'author')->get();

<a name="has-one-of-many"></a>
### 一對多中之一

有時候，某個 Model 可能有多個關聯 Model，而我們可能會想取多個關聯 Model 中「最新」或「最舊」的關聯
Model。舉例來說，`User` Model (使用者) 可能會關聯到多個 `Order` Model
(訂單)，而我們可能會想定義一種方便的方法來存取使用者最新的訂單。我們可以通過將 `hasOne` 關聯類型與 `ofMany` 方法搭配使用來達成：

```php
/**
 * Get the user's most recent order.
 */
public function latestOrder()
{
    return $this->hasOne(Order::class)->latestOfMany();
}
```

同樣的，我們也可以定義一個方法來取得一個關聯中「最舊」或第一個關聯的 Model：

```php
/**
 * Get the user's oldest order.
 */
public function oldestOrder()
{
    return $this->hasOne(Order::class)->oldestOfMany();
}
```

預設情況下，`latestOfMany` 與 `oldestOfMany` 方法會依照該 Model 的主索引鍵來取得最新或最舊的
Model，而該索引鍵必須要是可以排序的。不過，有時候我們可能會想從一個更大的關聯中通過另一種方法來取得單一 Model：

舉例來說，我們可以使用 `ofMany` 方法來去的使用者下過金額最高的訂單。`ofMany`
方法的第一個引數為可排序的欄位，接著則是要套用哪個匯總函式 (`min` 或 `max` 等) 在關聯的 Model 上：

```php
/**
 * Get the user's largest order.
 */
public function largestOrder()
{
    return $this->hasOne(Order::class)->ofMany('price', 'max');
}
```

> {note} 由於 PostgreSQL 不支援在 UUID 欄位上執行 `MAX` 函式，因此目前一對多關聯無法搭配 PostgreSQL 的 UUID 欄位使用。

<a name="advanced-has-one-of-many-relationships"></a>
#### 進階的一對多中之一關聯

我們還可以進一步地做出進階的「一對多中之一」關聯。舉例來說，`Product` Model 可能會有許多相應的 `Price Model，這些
`Price` Model 會在每次更新商品價格後保留在系統內。此外，我們也可以進一步地通過 `published_at`
欄位來讓某個商品價格在未來的時間點生效。

因此，總結一下，我們會需要取得最新且已發布的價格，且發佈時間不可是未來。此外，若有兩個價格的發佈時間相同，則我們取 ID
最大的那個價格。為此，我們必須傳入一個陣列給 `ofMany` 方法，該陣列序包用來判斷最新價格的可排序欄位。此外，我們會提供一個閉包給
`ofMany` 方法作為第二個引述。這個閉包會負責為關聯查詢加上額外的發佈時間條件：

```php
/**
 * Get the current pricing for the product.
 */
public function currentPricing()
{
    return $this->hasOne(Price::class)->ofMany([
        'published_at' => 'max',
        'id' => 'max',
    ], function ($query) {
        $query->where('published_at', '<', now());
    });
}
```

<a name="has-one-through"></a>
### 間接一對一

「間接一對一 (has-one-through)」關聯定義了與另一個 Model 間的一對一關係。不過，使用這種關聯代表宣告關聯的 Model 可以
**通過** 一個 Model 來對應到另一個 Model 的實體。

舉例來說，在汽車維修網站中，每個 `Mechanic` Model (零件) 可以跟一個 `Car` Model 關聯。而每個 `Car` Model
(汽車) 則可以關聯到一個 `Owner` Model (車主)。雖然零件與車主在資料庫中並沒有直接的關聯性，但我們可以 **通過** `Car`
Model 來在零件上存取車主。來看看要定義這種關聯所需的資料表：

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

    class Mechanic extends Model
    {
        /**
         * Get the car's owner.
         */
        public function carOwner()
        {
            return $this->hasOneThrough(Owner::class, Car::class);
        }
    }

傳給 `hasOneThrough` 方法的第一個引述是最後我們想存取的 Model 名稱；第二個引數則是中介 Model 的名稱。

<a name="has-one-through-key-conventions"></a>
#### 索引鍵慣例

在進行關聯查詢時，會使用到典型的 Eloquent 外部索引鍵慣例。若想自訂關聯使用的索引鍵，則可以將自訂索引鍵傳給 `hasOneThrough`
方法的第三個與第四個引數。第三個引數為中介 Model 上的外部索引鍵名稱。第四個引數則是最終 Model
的外部索引鍵名稱。第五個引數則為內部索引鍵，而第六個引述則是中介 Model 上的內部索引鍵：

    class Mechanic extends Model
    {
        /**
         * Get the car's owner.
         */
        public function carOwner()
        {
            return $this->hasOneThrough(
                Owner::class,
                Car::class,
                'mechanic_id', // cars 表上的外部索引鍵...
                'car_id', //owners 表上的外部索引鍵...
                'id', // mechanics 表上的內部索引鍵...
                'id' // cars 表上的內部索引鍵...
            );
        }
    }

<a name="has-many-through"></a>
### 間接一對多

「間接一對多 (has-many-through)」關聯提供了一個方便的方法來通過中介關聯存取另一個關聯。舉例來說，假設我們有一個像 [Laravel
Vapor](https://vapor.laravel.com) 這樣的部署平台。`Project` Model (專案)可通過一個中介的
`Environment` Model (環境) 來存取多個 `Deployment` Model
(部署)。依照這個例子，我們可以很輕鬆的取得特定專案的所有部署。來看看定義這個關聯性所需的資料表：

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

    class Project extends Model
    {
        /**
         * Get all of the deployments for the project.
         */
        public function deployments()
        {
            return $this->hasManyThrough(Deployment::class, Environment::class);
        }
    }

傳給 `hasManyThrough` 方法的第一個引述是最後我們想存取的 Model 名稱；第二個引數則是中介 Model 的名稱。

雖然 `Deployment` Model 的資料表不包含 `project_id` 欄位，但 `hasManyThrough` 關聯可讓我們通過 `$project->deployments` 來存取專案的部署。為了取得這些 Model，Eloquent 會先在中介的 `Environment` Model 資料表上讀取 `project_id`。找到相關的環境 ID 後，再通過這些 ID 來查詢 `Deployment` Model 的資料表。

<a name="has-many-through-key-conventions"></a>
#### 索引鍵慣例

在進行關聯查詢時，會使用到典型的 Eloquent 外部索引鍵慣例。若想自訂關聯使用的索引鍵，則可以將自訂索引鍵傳給 `hasManyThrough`
方法的第三個與第四個引數。第三個引數為中介 Model 上的外部索引鍵名稱。第四個引數則是最終 Model
的外部索引鍵名稱。第五個引數則為內部索引鍵，而第六個引述則是中介 Model 上的內部索引鍵：

    class Project extends Model
    {
        public function deployments()
        {
            return $this->hasManyThrough(
                Deployment::class,
                Environment::class,
                'project_id', // environments 表上的外部索引鍵...
                'environment_id', // deployments 表上的外部索引鍵...
                'id', // projects 表上的內部索引鍵...
                'id' // environments 表上的內部索引鍵...
            );
        }
    }

<a name="many-to-many"></a>
## Many To Many 關聯

比起 `hasOne` 或
`hasMany`，多對多關聯稍微複雜一點。一個多對多關聯的例子是：一位使用者可以有多個職位，而這些職位也會被網站中的其他使用者使用。舉例來說，某位使用者可能會被設定職位「作者」與「編輯」，但這些職位也可能會被指派給其他使用者。因此，一位使用者可以有多個職位，而一個職位則可以有多位使用者。

<a name="many-to-many-table-structure"></a>
#### 資料表結構

要定義這種關聯，我們需要三張資料表：`users`, `roles`, 與 `role_user`。`role_user` 資料表的名稱是由關聯的
Model 名稱按照字母排序串接而來的，裡面包含了 `user_id` 與 `role_id` 欄位。這張資料表會用來作為關聯使用者與職位的中介資料表。

請記得，由於一個職位可以同時關聯到多位使用者，因此我們沒辦法在 `roles` 資料表上設定 `user_id`
欄位。若這麼做的話，一個職位就只能有一位使用者。為了要讓職位能被設定給多位使用者，我們會需要 `role_user`
資料表。我們可以總結一下，資料表的結構會長這樣：

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

我們可以通過撰寫一個回傳 `belongsToMany` 方法執行結果的方法來定義多對多關聯。`belongsToMany` 方法是由
`Illuminate\Database\Eloquent\Model` 基礎類別提供的，你的專案中所有的 Eloquent Model
都使用了這個類別。舉例來說，讓我們來在 `User` Model 上定義一個 `roles` 方法。傳入這個方法的第一個引述是關聯 Model
類別的名稱：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        /**
         * Get the phone associated with the user.
         */
        public function phone()
        {
            return $this->belongsToMany(Phone::class);
        }
    }

定義好關聯後，就可以使用 `roles` 動態關聯屬性來存取該使用者的角色：

    use App\Models\User;

    $user = User::find(1);

    foreach ($user->roles as $role) {
        //
    }

由於所有的關聯也同時是 Query Builder，因此我們也能通過呼叫 `roles` 方法並繼續在查詢上串上條件來進一步給關聯加上查詢條件：

    $roles = User::find(1)->roles()->orderBy('name')->get();

為了判斷該關聯的中介資料表表名，Eloquent 會將兩個關聯 Model
的名稱按照字母排序串接在一起。不過，這個慣例是可以隨意複寫的，只需要傳入第二個引數給 `belongsToMany` 方法即可：

    return $this->belongsToMany(Role::class, 'role_user');

除了自訂中介表的表名外，也可以傳入額外的引數給 `belongsToMany` 來自訂中介表上的欄位名稱。第三個引數目前定義關聯的 Model
的外部索引鍵，而第四個引述則是要連結的 Model 的外部索引鍵：

    return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');

<a name="many-to-many-defining-the-inverse-of-the-relationship"></a>
#### 定義反向的關聯

若要定義 many-to-many 的「相反」關聯，應先在關聯的 Model 上定義一個同樣回傳 `belongsToMany`
方法結果的方法。接著我們的使用者與角色的例子，我們來在 `Role` Model 上定義 `users` 方法：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Role extends Model
    {
        /**
         * The users that belong to the role.
         */
        public function users()
        {
            return $this->belongsToMany(User::class);
        }
    }

如你所見，除了這邊是參照 `App\Models\User` 外，關聯定義跟 `User` Model 中對應的部分完全一樣。由於我們使用的還是
`belongsToMany` 方法，因此，在定義「反向」的 many-to-many 關聯時，一樣可以使用一般的資料表與索引鍵自訂選項。

<a name="retrieving-intermediate-table-columns"></a>
### 取得中介資料表欄位

讀者可能已經瞭解到，處理 Many-to-Many 關聯時必須要有一張中介資料表。Eloquent
提供了一些非常適用的方法來與中介資料表互動。舉例來說，假設 `User` Model 有許多關聯的 `Role`
Model。存取這個關聯後，我們可以使用 Model 上的 `pivot` 屬性來存取中介資料表：

    use App\Models\User;

    $user = User::find(1);

    foreach ($user->roles as $role) {
        echo $role->pivot->created_at;
    }

可以注意到，我們取得的每個 `Role` 資料表都會自動獲得一個 `pivot` 屬性。這個屬性包含了一個代表中介資料表的 Model。

預設情況下，只有 Model 的索引鍵會出現在 `Pivot` Model 上。若中介資料表包含了其他額外的屬性，則需要在定義關聯時指定這些屬性：

    return $this->belongsToMany(Role::class)->withPivot('active', 'created_by');

若想讓中介資料表擁有 Eloquent 能自動維護的 `created_at` 與 `updated_at` 時戳，可在定義關聯的時候呼叫
`withTimestamps` 方法：

    return $this->belongsToMany(Role::class)->withTimestamps();

> {note} 使用 Eloquent 自動維護時戳的中介資料表會需要擁有 `created_at` 與 `updated_at` 兩個時戳欄位。

<a name="customizing-the-pivot-attribute-name"></a>
#### 自訂 `pivot` 屬性名稱

剛才也有提過，我們可以使用 `pivot` 屬性來存取中介資料表的屬性。不過，我們可以自訂這個屬性的名稱以讓其跟貼合在專案中的用途。

舉例來說，我們的專案中可能會包含能讓使用者訂閱 Podcast 的功能，我們可能會想在使用者與 Podcast 間使用 Many-to-Many
關聯。在這個例子中，我們可能會想將中介資料表屬性的名稱從 `pivot` 改成 `subscription`。可以在定義關聯時使用 `as`
方法來完成：

    return $this->belongsToMany(Podcast::class)
                    ->as('subscription')
                    ->withTimestamps();

指定好自訂的中介資料表屬性後，就可以使用自訂的名稱來存取中介資料表資料：

    $users = User::with('podcasts')->get();

    foreach ($users->flatMap->podcasts as $podcast) {
        echo $podcast->subscription->created_at;
    }

<a name="filtering-queries-via-intermediate-table-columns"></a>
### 通過中介資料表欄位來過濾查詢

也可以在定義關聯時使用 `wherePivot`, `wherePivotIn`, `wherePivotNotIn`,
`wherePivotBetween`, `wherePivotNotBetween`, `wherePivotNull`, 與
`wherePivotNotNull` 方法來過濾 `belongsToMany` 關聯查詢的回傳結果：

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

<a name="defining-custom-intermediate-table-models"></a>
### 定義自訂的中介表 Model

若想定義一個代表多對多關聯之中介資料表的自訂 Model，則可以在定義關聯時呼叫 `using` 方法。自訂樞紐 Model (Pivot Model)
能讓我們有機會在樞紐 Model 上定義一些額外的方法。

要自訂多對多樞紐 Model，則應繼承 `Illuminate\Database\Eloquent\Relations\Pivot`
類別。多型多對多的樞紐 Model 則應繼承
`Illuminate\Database\Eloquent\Relations\MorphPivot`。舉例來說，我們可以定義一個使用了
`RoleUser` 樞紐 Model 的` Role` Model：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Role extends Model
    {
        /**
         * The users that belong to the role.
         */
        public function users()
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
        //
    }

> {note} 樞紐 Model 不能使用 `SoftDeletes` Trait。若有需要對樞紐紀錄作軟刪除，請考慮將樞紐 Model 改寫成真正的 Eloquent Model。

<a name="custom-pivot-models-and-incrementing-ids"></a>
#### 自訂樞紐 Model 並遞增 ID

若有定義了使用自訂樞紐 Model 的多對多關聯，且該樞紐 Model 由自動遞增的主索引鍵 (Auto-Incrementing Primary
Key)，則應確保這個自訂樞紐 Model 類別由定義一個設為 `true` 的 `incrementing` 屬性。

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

<a name="polymorphic-relationships"></a>
## Polymorphic (多型) 關聯

使用多型關聯，就能讓子 Model 通過單一關聯來隸屬於多種
Model。舉例來說，假設我們正在製作一個能讓使用者分享部落格貼文與影片的網站。在這種例子中，`Comment` (留言) Model 有可能隸屬於
`Post` (貼文) Model，也可能隸屬於 `Video` (影片) Model。

<a name="one-to-one-polymorphic-relations"></a>
### 一對一 (多型)

<a name="one-to-one-polymorphic-table-structure"></a>
#### 資料表結構

多型的一對一關聯於一般的一對一關聯類似。不過，在這種關聯中的子 Model 可以使用一種關聯來表示出對超過一種 Model
的從屬關係。舉例來說，部落格的 `Post` (貼文) 與 `User` (使用者) 可能會共享一個多型關聯的 `Image` (圖片)
Model。使用多型的一對一關聯，就能讓我們製作一張用來儲存不重複圖片的資料表，並將該資料表關聯到貼文跟使用者上。首先，我們來看看下列資料表架構：

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

可以注意到 `images` 資料表上的 `imageable_id` 與 `imageable_type` 欄位。`imageable_id`
欄位用來包含貼文或使用者的 ID 值，而 `imageable_type` 欄位則用來包含上層 Model 的類別名稱。`imageable_type`
是用來給 Eloquent 判斷上層 Model 的「型別 (Type)」，以在存取 `imageable` 關聯時能回傳該上層
Model。在這種情況下，這個欄位的內容會是 `App\Models\Post` 或 `App\Models\User`。

<a name="one-to-one-polymorphic-model-structure"></a>
#### Model 架構

接著，讓我們來看看要製作這種關聯所需要的 Model 定義：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Image extends Model
    {
        /**
         * Get the parent imageable model (user or post).
         */
        public function imageable()
        {
            return $this->morphTo();
        }
    }

    class Post extends Model
    {
        /**
         * Get the post's image.
         */
        public function image()
        {
            return $this->morphOne(Image::class, 'imageable');
        }
    }

    class User extends Model
    {
        /**
         * Get the user's image.
         */
        public function image()
        {
            return $this->morphOne(Image::class, 'imageable');
        }
    }

<a name="one-to-one-polymorphic-retrieving-the-relationship"></a>
#### 取得關聯

定義好資料庫資料表與 Model 後，就可以通過這些 Model 來存取關聯。舉例來說，若要取得一則貼文的圖片，我們可以存取 `image`
動態關聯屬性：

    use App\Models\Post;

    $post = Post::find(1);

    $image = $post->image;

可以通過存取呼叫 `morphTo` 之方法的名稱來取得多型 Model 的上層 Model。在這個例子中，就是 `Image` Model 的
`imageable` 方法。因此，我們可以用動態關聯屬性來存取該方法：

    use App\Models\Image;

    $image = Image::find(1);

    $imageable = $image->imageable;

依據擁有該圖片的 Model 類型，`Image` Model 上的 `imageable` 關聯會回傳 `Post` 或 `User` 實體。

<a name="morph-one-to-one-key-conventions"></a>
#### 索引鍵慣例

若有需要，也可以指定多型子 Model 所使用的「id」與「type」欄位名稱。若要自訂這些欄位的名稱，請先確保有將關聯的名稱傳給 `morphTo`
方法的第一個引數。一般來說，這個值應該要與方法名稱相同，因此我們可以使用 PHP 的 `__FUNCTION__` 常數：

    /**
     * Get the model that the image belongs to.
     */
    public function imageable()
    {
        return $this->morphTo(__FUNCTION__, 'imageable_type', 'imageable_id');
    }

<a name="one-to-many-polymorphic-relations"></a>
### 一對多 (多型)

<a name="one-to-many-polymorphic-table-structure"></a>
#### 資料表結構

One-to-Many 的多型關聯與一般的 One-to-Many 關聯很類似。不過，在多型關聯中，可以使用單一關聯來讓子 Model
可以隸屬於多種類型的 Model。舉例來說，假設有個使用者可以在貼文與影片上「留言」的網站。若使用多型關聯，我們可以使用單一一個 `comments`
表來包含用於貼文與影片的留言。首先，來看看需要建立這種關聯的資料表結構：

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

    class Comment extends Model
    {
        /**
         * Get the parent commentable model (post or video).
         */
        public function commentable()
        {
            return $this->morphTo();
        }
    }

    class Post extends Model
    {
        /**
         * Get all of the post's comments.
         */
        public function comments()
        {
            return $this->morphMany(Comment::class, 'commentable');
        }
    }

    class Video extends Model
    {
        /**
         * Get all of the video's comments.
         */
        public function comments()
        {
            return $this->morphMany(Comment::class, 'commentable');
        }
    }

<a name="one-to-many-polymorphic-retrieving-the-relationship"></a>
#### 取得關聯

定義好資料表與 Model 後，就可以使用 Model 的動態關聯屬性來存取這個關聯。舉例來說，若要存取某個貼文的所有留言，我們可以使用
`comments` 動態屬性：

    use App\Models\Post;

    $post = Post::find(1);

    foreach ($post->comments as $comment) {
        //
    }

也可以通過存取呼叫 `morphTo` 之方法的名稱來取得多型子 Model 的上層 Model。在這個例子中，就是 `Comment` Model 的
`commentable` 方法。因此，我們可以用動態關聯屬性來存取該方法以取得留言的上層 Model：

    use App\Models\Comment;

    $comment = Comment::find(1);

    $commentable = $comment->commentable;

依照不同的留言上層 Model 類型，`Comment` Model 的 `commentable` 關聯回傳的不是 `Post` 實體就是
`Video` 實體。

<a name="one-of-many-polymorphic-relations"></a>
### 多中之一 (多型)

有時候，某個 Model 可能有多個關聯 Model，而我們可能會想取多個關聯 Model 中「最新」或「最舊」的關聯
Model。舉例來說，`User` Model (使用者) 可能會關聯到多個 `Image` Model
(圖片)，而我們可能會想定義一種方便的方法來存取使用者最新的圖片。我們可以通過將 `morphOne` 關聯類型與 `ofMany`
方法搭配使用來達成：

```php
/**
 * Get the user's most recent image.
 */
public function latestImage()
{
    return $this->morphOne(Image::class, 'imageable')->latestOfMany();
}
```

同樣的，我們也可以定義一個方法來取得一個關聯中「最舊」或第一個關聯的 Model：

```php
/**
 * Get the user's oldest image.
 */
public function oldestImage()
{
    return $this->morphOne(Image::class, 'imageable')->oldestOfMany();
}
```

預設情況下，`latestOfMany` 與 `oldestOfMany` 方法會依照該 Model 的主索引鍵來取得最新或最舊的
Model，而該索引鍵必須要是可以排序的。不過，有時候我們可能會想從一個更大的關聯中通過另一種方法來取得單一 Model：

舉例來說，我們可以使用 `ofMany` 方法來去的使用者獲得最多「讚」的圖片。`ofMany`
方法的第一個引數為可排序的欄位，接著則是要套用哪個匯總函式 (`min` 或 `max` 等) 在關聯的 Model 上：

```php
/**
 * Get the user's most popular image.
 */
public function bestImage()
{
    return $this->morphOne(Image::class, 'imageable')->ofMany('likes', 'max');
}
```

> {tip} 還有辦法建立建立更進階的「One of Many」關聯。更多資訊請參考 [Has One of Many 說明文件](#advanced-has-one-of-many-relationships)。

<a name="many-to-many-polymorphic-relations"></a>
### 多對多 (多型)

<a name="many-to-many-polymorphic-table-structure"></a>
#### 資料表結構

多型的 Many-to-Many 關聯比「Morph One」或「Morph Many」都稍微複雜一點。舉例來說，`Post` Model 與
`Video` Model 可以共用一個多型關聯的 `Tag` Model。在這種情況下使用多型的 Many-to-Many
可以讓我們的專案中只需要一張資料表來儲存獨立的 Tag，就可以關聯給 Post 跟 Video。首先，來看看要建立這種關聯的資料表架構：

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

> {tip} 在進一步深入瞭解多型的 Many-to-Many 關聯前，我們建議你先閱讀有關普通 [Many-to-Many 關聯](#many-to-many)的說明文件。

<a name="many-to-many-polymorphic-model-structure"></a>
#### Model 架構

接著，我們就可以開始在 Model 上定義關聯了。`Post` 與 `Video` Model 都包含了一個 `tags` 方法，該方法中會呼叫基礎
Eloquent Model 類別中的 `morphToMany` 方法。

`morphToMany` 方法接受關聯 Model
的名稱，以及「關聯名稱」。根據我們設定給中介表的名稱以及其中包含的索引鍵，我們可以將關聯推導為「taggable」：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Post extends Model
    {
        /**
         * Get all of the tags for the post.
         */
        public function tags()
        {
            return $this->morphToMany(Tag::class, 'taggable');
        }
    }

<a name="many-to-many-polymorphic-defining-the-inverse-of-the-relationship"></a>
#### 定義反向的關聯

接著，在 `Tag` Model 中，我們可以為 Tag 的各個可能的上層 Model 定義個別的方法。因此，在這個例子中，我們會定義一個
`posts` 方法與一個 `videos` 方法。這兩個方法都應回傳 `morphedByMany` 方法的結果。

`morphedByMany` 方法接受關聯 Model
的名稱，以及「關聯名稱」。根據我們設定給中介表的名稱以及其中包含的索引鍵，我們可以將關聯推導為「taggable」：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Tag extends Model
    {
        /**
         * Get all of the posts that are assigned this tag.
         */
        public function posts()
        {
            return $this->morphedByMany(Post::class, 'taggable');
        }

        /**
         * Get all of the videos that are assigned this tag.
         */
        public function videos()
        {
            return $this->morphedByMany(Video::class, 'taggable');
        }
    }

<a name="many-to-many-polymorphic-retrieving-the-relationship"></a>
#### 取得關聯

定義好資料庫資料表與 Model 後，就可以通過這些 Model 來存取關聯。舉例來說，若要取得一則貼文的 Tag，我們可以使用 `tags`
動態關聯屬性：

    use App\Models\Post;

    $post = Post::find(1);

    foreach ($post->tags as $tag) {
        //
    }

可以在多型子 Model 中通過存取呼叫 `morphedByMany` 的方法名稱來存取多型關聯的上層 Model。在這個例子中，就是 `Tag`
Model 上的 `posts` 與 `videos` 方法：

    use App\Models\Tag;

    $tag = Tag::find(1);

    foreach ($tag->posts as $post) {
        //
    }

    foreach ($tag->videos as $video) {
        //
    }

<a name="custom-polymorphic-types"></a>
### 自訂多型型別

預設情況下，Laravel 會使用類別的完整格式名稱 (Fully Qualified Class Name) 來儲存關聯 Model 的「類型
(Type)」。具體而言，在上述的 One-to-Many 例子中，`Comment` Model 可以隸屬於 `Post` Model、也可以隸屬於
`Video` Model，因此預設的 `commentable_type` 就分別會是 `App\Models\Post` 或
`App\Models\Video`。不過，開發人員可能會想將這些值從專案的內部結構中解耦 (Decouple) 出來。

舉例來說，我們可以使用像 `post` 或 `video` 等簡單的字串作為「型別」，而不是使用 Model 名稱。這樣一來，即使我們修改了 Model
的名稱，資料庫中的多型「type」欄位值也會繼續有效：

    use Illuminate\Database\Eloquent\Relations\Relation;

    Relation::enforceMorphMap([
        'post' => 'App\Models\Post',
        'video' => 'App\Models\Video',
    ]);

可以在 `App\Providers\AppServiceProvider` 類別或依照需求自行的 Service Provider 中之 `boot`
方法內呼叫 `enforceMorphMap` 方法：

我們可以使用 Model 的 `getMorphClass` 方法來在執行階段判斷給定 Model 的 Morph 別名。相反的，我們可以使用
`Relation::getMorphedModel` 方法來取得 Morph 別名的完整格式類別名稱：

    use Illuminate\Database\Eloquent\Relations\Relation;

    $alias = $post->getMorphClass();

    $class = Relation::getMorphedModel($alias);

> {note} 在專案中使用「Morph Map」時，所有的 morphable `*_type` 欄位值還是會保持原本的完整各式類別名稱，需要再更改為其「映射 (Map)」的名稱。

<a name="dynamic-relationships"></a>
### 動態關聯

可以使用 `resolveRelationUsing` 方法來在執行階段定義 Eloquent Model
間的關聯。雖然對於一般的專案開發並不建議這麼做，但在開發 Laravel 套件的時候偶爾會很實用。

`resolveRelationUsing` 方法接受自訂的關聯名稱作為其第一個引述。第二個傳入該方法的引數應為閉包，該閉包應接受一個 Model
實體並回傳一個有效的 Eloquent 關聯定義。一般來說，應在某個 [Service
Provider](/docs/{{version}}/providers) 內的 boot 方法中定義動態關聯。

    use App\Models\Order;
    use App\Models\Customer;

    Order::resolveRelationUsing('customer', function ($orderModel) {
        return $orderModel->belongsTo(Customer::class, 'customer_id');
    });

> {note} 在定義動態關聯時，請總是提供顯式的索引鍵名稱給 Eloquent 關聯方法。

<a name="querying-relations"></a>
## 查詢關聯

由於所有的 Eloquent 關聯都是以方法來定義的，所以我們可以呼叫這些方法來取得關聯的實體，而不需執行查詢來載入關聯的 Model。此外，每種
Eloquent 關聯都可作為 [Query Builder](/docs/{{version}}/queries)
使用，因此我們也能在最終向資料庫執行 SQL 查詢前往關聯查詢串上一些查詢條件。

舉例來說，假設我們有一個部落格網站，其中 `User` Model 可以關聯到 `Post` Model：

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        /**
         * Get all of the posts for the user.
         */
        public function posts()
        {
            return $this->hasMany(Post::class);
        }
    }

我們可以查詢 `posts` 關聯，並在關聯上像這樣加上額外的條件：

    use App\Models\User;

    $user = User::find(1);

    $user->posts()->where('active', 1)->get();

在關聯上我們可以使用任何的 Laravel [Query Builder](/docs/{{version}}/queries)
方法，因此請確保有先閱讀過 Query Builder 的說明文件以瞭解有哪些方法可以使用。

<a name="chaining-orwhere-clauses-after-relationships"></a>
#### 在關聯後方串上 `orWhere` 子句

像上面的範例中一樣，在進行查詢的時候我們可以自由地往關聯新增查詢。不過，在將 `orWhere` 自居串上關聯時要注意，因為 `orWhere`
自居可能會被邏輯性地分組在與關聯條件相同的層級上：

    $user->posts()
            ->where('active', 1)
            ->orWhere('votes', '>=', 100)
            ->get();

上述的例子會產生下列的 SQL。如你所見，`or` 子句會讓查詢回傳 **所有** 大於 100 得票數的使用者。這個查詢不會被限制在任何特定使用者上：

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
### 關聯方法 V.s. 動態屬性

若不想在 Eloquent 關聯查詢上新增任何額外的查詢條件，則可以直接將關聯作為屬性一樣存取。舉例來說，接續使用我們的 `User` 與 `Post`
範例 Model，我們可以像這樣存取 User 的所有 Post：

    use App\Models\User;

    $user = User::find(1);

    foreach ($user->posts as $post) {
        //
    }

動態屬性會被「延遲載入 (Lazy
Loading)」，這表示，這些關聯資料只有在實際存取的時候才會被載入。也因此，開發人員常常會使用[積極式載入](#eager-loading)來預先載入稍後會被存取的關聯。使用預先載入，就可以顯著地降低許多在載入
Model 關聯時會被執行的 SQL 查詢。

<a name="querying-relationship-existence"></a>
### 查詢存在的關聯

在取得 Model
紀錄時，我們可能會想依據關聯是否存在來限制查詢結果。舉例來說，假設我們想取得所有至少有一篇留言的部落格貼文。為此，我們可以將關聯的名稱傳入 `has`
或 `orHas` 方法中：

    use App\Models\Post;

    // 取得所有至少有一篇留言的文章...
    $posts = Post::has('comments')->get();

我們也可以指定一個運算子與總數來進一步自訂查詢：

    // 取得所有至少有 3 篇留言的文章...
    $posts = Post::has('comments', '>=', 3)->get();

可以使用「點 (.)」標記法來撰寫巢狀的 `has` 陳述式。舉例來說，我們可以取得所有至少有一篇含有圖片的留言的部落格貼文：

    // 取得至少有一篇有圖片的留言的部落格貼文...
    $posts = Post::has('comments.images')->get();

若需要更多功能，可以使用 `whereHas` 或 `orWhereHas` 方法來在 `has` 查詢上定義額外的查詢條件，如檢查留言的內容等：

    use Illuminate\Database\Eloquent\Builder;

    // 取得所有至少有一篇留言的內容 like code% 的貼文...
    $posts = Post::whereHas('comments', function (Builder $query) {
        $query->where('content', 'like', 'code%');
    })->get();

    // 取得所有至少有 10 篇留言的內容 like code% 的貼文...
    $posts = Post::whereHas('comments', function (Builder $query) {
        $query->where('content', 'like', 'code%');
    }, '>=', 10)->get();

> {note} 由於 Eloquent 目前並不支援在多個資料庫間查詢關聯的存否，因此要查詢的關聯必須在同一個資料庫中。

<a name="inline-relationship-existence-queries"></a>
#### 內嵌的存在關聯查詢

若想要使用附加在關聯查詢上的簡單且單一的 Where 條件來查詢關聯的存否，那麼用 `whereRelation` 與
`whereMorphRelation` 方法應該會很方便。舉例來說，我們可以查詢所有有未審核 (Unapproved) 留言的貼文：

    use App\Models\Post;

    $posts = Post::whereRelation('comments', 'is_approved', false)->get();

當然，就像呼叫 Query Builder 的 `where` 方法一樣，我們也可以指定運算子：

    $posts = Post::whereRelation(
        'comments', 'created_at', '>=', now()->subHour()
    )->get();

<a name="querying-relationship-absence"></a>
### 查詢不存在的關聯

在取得 Model 紀錄時，我們可能會想依據關聯的是否不存在來限制查詢結果。舉例來說，假設我們想取得所有 **沒有**
留言的部落格貼文。為此，我們可以將關聯的名稱傳入 `doesntHave` 或 `orDoesntHave` 方法中：

    use App\Models\Post;

    $posts = Post::doesntHave('comments')->get();

若需要更多功能，可以使用 `whereDoesntHave` 或 `orWhereDoesntHave` 方法來在 `doesntHave`
查詢上加上額外的查詢條件，如檢查留言的內容等：

    use Illuminate\Database\Eloquent\Builder;

    $posts = Post::whereDoesntHave('comments', function (Builder $query) {
        $query->where('content', 'like', 'code%');
    })->get();

我們也可以使用「點
(.)」標記法來對巢狀關聯進行查詢。舉例來說，下列查詢會取得所有沒有留言的貼文。不過，具有未禁言作者發表留言的文章也會被包含在結果裡面：

    use Illuminate\Database\Eloquent\Builder;

    $posts = Post::whereDoesntHave('comments.author', function (Builder $query) {
        $query->where('banned', 0);
    })->get();

<a name="querying-morph-to-relationships"></a>
### 查詢 Morph To 關聯

若要查詢「Morph To」關聯是否存在，可以使用 `whereHasMorph` 或 `whereDoesntHaveMorph`
方法。這些方法都接受關聯名稱作為其第一個引數。接著，這個方法還接受要被包含在查詢裡的關聯 Model 名稱。最後，我們還可以提供用來自訂關聯查詢的閉包：

    use App\Models\Comment;
    use App\Models\Post;
    use App\Models\Video;
    use Illuminate\Database\Eloquent\Builder;

    // 取得有關聯到貼文或影片的留言，且留言標題 like code%...
    $comments = Comment::whereHasMorph(
        'commentable',
        [Post::class, Video::class],
        function (Builder $query) {
            $query->where('title', 'like', 'code%');
        }
    )->get();

    // 取得有關聯到貼文或影片的留言，且留言標題 not like code%...
    $comments = Comment::whereDoesntHaveMorph(
        'commentable',
        Post::class,
        function (Builder $query) {
            $query->where('title', 'like', 'code%');
        }
    )->get();

有時候，我們可能會想依據多型關聯 Model 的「類型」來新增查詢條件。傳給 `whereHasMorph` 方法的閉包可接受一個 `$type`
值作為其第二個引述。使用 `$type`引述，就可以檢查正在建立的查詢是什麼「類型」：

    use Illuminate\Database\Eloquent\Builder;

    $comments = Comment::whereHasMorph(
        'commentable',
        [Post::class, Video::class],
        function (Builder $query, $type) {
            $column = $type === Post::class ? 'content' : 'title';

            $query->where($column, 'like', 'code%');
        }
    )->get();

<a name="querying-all-morph-to-related-models"></a>
#### 查詢所有關聯的 Model

我們可以提供 `*` 作為萬用字元，而不需以陣列列出所有可能的多型 Model。這樣以來 Laravel
就會從資料庫中取得所有可能的多型類型。Laravel 會執行一個額外的查詢來進行此行動：

    use Illuminate\Database\Eloquent\Builder;

    $comments = Comment::whereHasMorph('commentable', '*', function (Builder $query) {
        $query->where('title', 'like', 'foo%');
    })->get();

<a name="aggregating-related-models"></a>
## 彙總關聯的 Model

<a name="counting-related-models"></a>
### 計數關聯的 Model

有時候我們可能會想知道給定關聯中關聯 Model 的數量，但又不想真正載入這些 Model。為此，我們可以使用 `withCount`
方法。`withCount` 方法會在查詢結果的 Model 中加上一個 `{關聯}_count` 屬性：

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

    $book->loadCount(['reviews' => function ($query) {
        $query->where('rating', 5);
    }])

<a name="relationship-counting-and-custom-select-statements"></a>
#### 關聯計數與自訂 Select 陳述式

若想組合使用 `withCount` 與 `select` 陳述式，請在 `select` 方法後再呼叫 `withCount`：

    $posts = Post::select(['title', 'body'])
                    ->withCount('comments')
                    ->get();

<a name="other-aggregate-functions"></a>
### 其他彙總函式

除了 `withCount` 方法外，Eloquent 也提供了 `withMin`, `withMax`, `withAvg`, `withSum`,
與 `withExists` 等方法。這些方法會在查詢結果的 Model 上加上一個  `{關聯}_{函式}_{欄位}` 屬性：

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

與 `loadCount` 方法類似，Eloquent 中也有這些方法的延遲 (Deferred) 版本。可以在已經取得的 Eloquent Model
上進行這些額外的彙總運算：

    $post = Post::first();

    $post->loadSum('comments', 'votes');

若想組合使用這些彙總與 `select` 陳述式，請在 `select` 方法後再呼叫這些彙總函式：

    $posts = Post::select(['title', 'body'])
                    ->withExists('comments')
                    ->get();

<a name="counting-related-models-on-morph-to-relationships"></a>
### 在 Morph To 關聯上計算關聯 Model 的數量

If you would like to eager load a "morph to" relationship, as well as
related model counts for the various entities that may be returned by that
relationship, you may utilize the `with` method in combination with the
`morphTo` relationship's `morphWithCount` method.

In this example, let's assume that `Photo` and `Post` models may create
`ActivityFeed` models. We will assume the `ActivityFeed` model defines a
"morph to" relationship named `parentable` that allows us to retrieve the
parent `Photo` or `Post` model for a given `ActivityFeed`
instance. Additionally, let's assume that `Photo` models "have many" `Tag`
models and `Post` models "have many" `Comment` models.

Now, let's imagine we want to retrieve `ActivityFeed` instances and eager
load the `parentable` parent models for each `ActivityFeed` instance. In
addition, we want to retrieve the number of tags that are associated with
each parent photo and the number of comments that are associated with each
parent post:

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

Let's assume we have already retrieved a set of `ActivityFeed` models and
now we would like to load the nested relationship counts for the various
`parentable` models associated with the activity feeds. You may use the
`loadMorphCount` method to accomplish this:

    $activities = ActivityFeed::with('parentable')->get();

    $activities->loadMorphCount('parentable', [
        Photo::class => ['tags'],
        Post::class => ['comments'],
    ]);

<a name="eager-loading"></a>
## Eager Loading

When accessing Eloquent relationships as properties, the related models are
"lazy loaded". This means the relationship data is not actually loaded until
you first access the property. However, Eloquent can "eager load"
relationships at the time you query the parent model. Eager loading
alleviates the "N + 1" query problem. To illustrate the N + 1 query problem,
consider a `Book` model that "belongs to" to an `Author` model:

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Book extends Model
    {
        /**
         * Get the author that wrote the book.
         */
        public function author()
        {
            return $this->belongsTo(Author::class);
        }
    }

Now, let's retrieve all books and their authors:

    use App\Models\Book;

    $books = Book::all();

    foreach ($books as $book) {
        echo $book->author->name;
    }

This loop will execute one query to retrieve all of the books within the
database table, then another query for each book in order to retrieve the
book's author. So, if we have 25 books, the code above would run 26 queries:
one for the original book, and 25 additional queries to retrieve the author
of each book.

Thankfully, we can use eager loading to reduce this operation to just two
queries. When building a query, you may specify which relationships should
be eager loaded using the `with` method:

    $books = Book::with('author')->get();

    foreach ($books as $book) {
        echo $book->author->name;
    }

For this operation, only two queries will be executed - one query to
retrieve all of the books and one query to retrieve all of the authors for
all of the books:

```sql
select * from books

select * from authors where id in (1, 2, 3, 4, 5, ...)
```

<a name="eager-loading-multiple-relationships"></a>
#### Eager Loading Multiple Relationships

Sometimes you may need to eager load several different relationships. To do
so, just pass an array of relationships to the `with` method:

    $books = Book::with(['author', 'publisher'])->get();

<a name="nested-eager-loading"></a>
#### Nested Eager Loading

To eager load a relationship's relationships, you may use "dot" syntax. For
example, let's eager load all of the book's authors and all of the author's
personal contacts:

    $books = Book::with('author.contacts')->get();

<a name="nested-eager-loading-morphto-relationships"></a>
#### Nested Eager Loading `morphTo` Relationships

If you would like to eager load a `morphTo` relationship, as well as nested
relationships on the various entities that may be returned by that
relationship, you may use the `with` method in combination with the
`morphTo` relationship's `morphWith` method. To help illustrate this method,
let's consider the following model:

    <?php

    use Illuminate\Database\Eloquent\Model;

    class ActivityFeed extends Model
    {
        /**
         * Get the parent of the activity feed record.
         */
        public function parentable()
        {
            return $this->morphTo();
        }
    }

In this example, let's assume `Event`, `Photo`, and `Post` models may create
`ActivityFeed` models. Additionally, let's assume that `Event` models belong
to a `Calendar` model, `Photo` models are associated with `Tag` models, and
`Post` models belong to an `Author` model.

Using these model definitions and relationships, we may retrieve
`ActivityFeed` model instances and eager load all `parentable` models and
their respective nested relationships:

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
#### Eager Loading Specific Columns

You may not always need every column from the relationships you are
retrieving. For this reason, Eloquent allows you to specify which columns of
the relationship you would like to retrieve:

    $books = Book::with('author:id,name,book_id')->get();

> {note} When using this feature, you should always include the `id` column and any relevant foreign key columns in the list of columns you wish to retrieve.

<a name="eager-loading-by-default"></a>
#### Eager Loading By Default

Sometimes you might want to always load some relationships when retrieving a
model. To accomplish this, you may define a `$with` property on the model:

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

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
        public function author()
        {
            return $this->belongsTo(Author::class);
        }

        /**
         * Get the genre of the book.
         */
        public function genre()
        {
            return $this->belongsTo(Genre::class);
        }
    }

If you would like to remove an item from the `$with` property for a single
query, you may use the `without` method:

    $books = Book::without('author')->get();

If you would like to override all items within the `$with` property for a
single query, you may use the `withOnly` method:

    $books = Book::withOnly('genre')->get();

<a name="constraining-eager-loads"></a>
### Constraining Eager Loads

Sometimes you may wish to eager load a relationship but also specify
additional query conditions for the eager loading query. You can accomplish
this by passing an array of relationships to the `with` method where the
array key is a relationship name and the array value is a closure that adds
additional constraints to the eager loading query:

    use App\Models\User;

    $users = User::with(['posts' => function ($query) {
        $query->where('title', 'like', '%code%');
    }])->get();

In this example, Eloquent will only eager load posts where the post's
`title` column contains the word `code`. You may call other [query
builder](/docs/{{version}}/queries) methods to further customize the eager
loading operation:

    $users = User::with(['posts' => function ($query) {
        $query->orderBy('created_at', 'desc');
    }])->get();

> {note} The `limit` and `take` query builder methods may not be used when constraining eager loads.

<a name="constraining-eager-loading-of-morph-to-relationships"></a>
#### Constraining Eager Loading Of `morphTo` Relationships

If you are eager loading a `morphTo` relationship, Eloquent will run
multiple queries to fetch each type of related model. You may add additional
constraints to each of these queries using the `MorphTo` relation's
`constrain` method:

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    $comments = Comment::with(['commentable' => function (MorphTo $morphTo) {
        $morphTo->constrain([
            Post::class => function (Builder $query) {
                $query->whereNull('hidden_at');
            },
            Video::class => function (Builder $query) {
                $query->where('type', 'educational');
            },
        ]);
    }])->get();

In this example, Eloquent will only eager load posts that have not been
hidden and videos have a `type` value of "educational".

<a name="lazy-eager-loading"></a>
### Lazy Eager Loading

Sometimes you may need to eager load a relationship after the parent model
has already been retrieved. For example, this may be useful if you need to
dynamically decide whether to load related models:

    use App\Models\Book;

    $books = Book::all();

    if ($someCondition) {
        $books->load('author', 'publisher');
    }

If you need to set additional query constraints on the eager loading query,
you may pass an array keyed by the relationships you wish to load. The array
values should be closure instances which receive the query instance:

    $author->load(['books' => function ($query) {
        $query->orderBy('published_date', 'asc');
    }]);

To load a relationship only when it has not already been loaded, use the
`loadMissing` method:

    $book->loadMissing('author');

<a name="nested-lazy-eager-loading-morphto"></a>
#### Nested Lazy Eager Loading & `morphTo`

If you would like to eager load a `morphTo` relationship, as well as nested
relationships on the various entities that may be returned by that
relationship, you may use the `loadMorph` method.

This method accepts the name of the `morphTo` relationship as its first
argument, and an array of model / relationship pairs as its second
argument. To help illustrate this method, let's consider the following
model:

    <?php

    use Illuminate\Database\Eloquent\Model;

    class ActivityFeed extends Model
    {
        /**
         * Get the parent of the activity feed record.
         */
        public function parentable()
        {
            return $this->morphTo();
        }
    }

In this example, let's assume `Event`, `Photo`, and `Post` models may create
`ActivityFeed` models. Additionally, let's assume that `Event` models belong
to a `Calendar` model, `Photo` models are associated with `Tag` models, and
`Post` models belong to an `Author` model.

Using these model definitions and relationships, we may retrieve
`ActivityFeed` model instances and eager load all `parentable` models and
their respective nested relationships:

    $activities = ActivityFeed::with('parentable')
        ->get()
        ->loadMorph('parentable', [
            Event::class => ['calendar'],
            Photo::class => ['tags'],
            Post::class => ['author'],
        ]);

<a name="preventing-lazy-loading"></a>
### Preventing Lazy Loading

As previously discussed, eager loading relationships can often provide
significant performance benefits to your application. Therefore, if you
would like, you may instruct Laravel to always prevent the lazy loading of
relationships. To accomplish this, you may invoke the `preventLazyLoading`
method offered by the base Eloquent model class. Typically, you should call
this method within the `boot` method of your application's
`AppServiceProvider` class.

The `preventLazyLoading` method accepts an optional boolean argument that
indicates if lazy loading should be prevented. For example, you may wish to
only disable lazy loading in non-production environments so that your
production environment will continue to function normally even if a lazy
loaded relationship is accidentally present in production code:

```php
use Illuminate\Database\Eloquent\Model;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Model::preventLazyLoading(! $this->app->isProduction());
}
```

After preventing lazy loading, Eloquent will throw a
`Illuminate\Database\LazyLoadingViolationException` exception when your
application attempts to lazy load any Eloquent relationship.

You may customize the behavior of lazy loading violations using the
`handleLazyLoadingViolationsUsing` method. For example, using this method,
you may instruct lazy loading violations to only be logged instead of
interrupting the application's execution with exceptions:

```php
Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
    $class = get_class($model);

    info("Attempted to lazy load [{$relation}] on model [{$class}].");
});
```

<a name="inserting-and-updating-related-models"></a>
## Inserting & Updating Related Models

<a name="the-save-method"></a>
### The `save` Method

Eloquent provides convenient methods for adding new models to
relationships. For example, perhaps you need to add a new comment to a
post. Instead of manually setting the `post_id` attribute on the `Comment`
model you may insert the comment using the relationship's `save` method:

    use App\Models\Comment;
    use App\Models\Post;

    $comment = new Comment(['message' => 'A new comment.']);

    $post = Post::find(1);

    $post->comments()->save($comment);

Note that we did not access the `comments` relationship as a dynamic
property. Instead, we called the `comments` method to obtain an instance of
the relationship. The `save` method will automatically add the appropriate
`post_id` value to the new `Comment` model.

If you need to save multiple related models, you may use the `saveMany`
method:

    $post = Post::find(1);

    $post->comments()->saveMany([
        new Comment(['message' => 'A new comment.']),
        new Comment(['message' => 'Another new comment.']),
    ]);

The `save` and `saveMany` methods will persist the given model instances,
but will not add the newly persisted models to any in-memory relationships
that are already loaded onto the parent model. If you plan on accessing the
relationship after using the `save` or `saveMany` methods, you may wish to
use the `refresh` method to reload the model and its relationships:

    $post->comments()->save($comment);

    $post->refresh();

    // All comments, including the newly saved comment...
    $post->comments;

<a name="the-push-method"></a>
#### Recursively Saving Models & Relationships

If you would like to `save` your model and all of its associated
relationships, you may use the `push` method. In this example, the `Post`
model will be saved as well as its comments and the comment's authors:

    $post = Post::find(1);

    $post->comments[0]->message = 'Message';
    $post->comments[0]->author->name = 'Author Name';

    $post->push();

<a name="the-create-method"></a>
### The `create` Method

In addition to the `save` and `saveMany` methods, you may also use the
`create` method, which accepts an array of attributes, creates a model, and
inserts it into the database. The difference between `save` and `create` is
that `save` accepts a full Eloquent model instance while `create` accepts a
plain PHP `array`. The newly created model will be returned by the `create`
method:

    use App\Models\Post;

    $post = Post::find(1);

    $comment = $post->comments()->create([
        'message' => 'A new comment.',
    ]);

You may use the `createMany` method to create multiple related models:

    $post = Post::find(1);

    $post->comments()->createMany([
        ['message' => 'A new comment.'],
        ['message' => 'Another new comment.'],
    ]);

You may also use the `findOrNew`, `firstOrNew`, `firstOrCreate`, and
`updateOrCreate` methods to [create and update models on
relationships](/docs/{{version}}/eloquent#upserts).

> {tip} Before using the `create` method, be sure to review the [mass assignment](/docs/{{version}}/eloquent#mass-assignment) documentation.

<a name="updating-belongs-to-relationships"></a>
### Belongs To Relationships

If you would like to assign a child model to a new parent model, you may use
the `associate` method. In this example, the `User` model defines a
`belongsTo` relationship to the `Account` model. This `associate` method
will set the foreign key on the child model:

    use App\Models\Account;

    $account = Account::find(10);

    $user->account()->associate($account);

    $user->save();

To remove a parent model from a child model, you may use the `dissociate`
method. This method will set the relationship's foreign key to `null`:

    $user->account()->dissociate();

    $user->save();

<a name="updating-many-to-many-relationships"></a>
### Many To Many 關聯

<a name="attaching-detaching"></a>
#### Attaching / Detaching

Eloquent also provides methods to make working with many-to-many
relationships more convenient. For example, let's imagine a user can have
many roles and a role can have many users. You may use the `attach` method
to attach a role to a user by inserting a record in the relationship's
intermediate table:

    use App\Models\User;

    $user = User::find(1);

    $user->roles()->attach($roleId);

When attaching a relationship to a model, you may also pass an array of
additional data to be inserted into the intermediate table:

    $user->roles()->attach($roleId, ['expires' => $expires]);

Sometimes it may be necessary to remove a role from a user. To remove a
many-to-many relationship record, use the `detach` method. The `detach`
method will delete the appropriate record out of the intermediate table;
however, both models will remain in the database:

    // Detach a single role from the user...
    $user->roles()->detach($roleId);

    // Detach all roles from the user...
    $user->roles()->detach();

For convenience, `attach` and `detach` also accept arrays of IDs as input:

    $user = User::find(1);

    $user->roles()->detach([1, 2, 3]);

    $user->roles()->attach([
        1 => ['expires' => $expires],
        2 => ['expires' => $expires],
    ]);

<a name="syncing-associations"></a>
#### Syncing Associations

You may also use the `sync` method to construct many-to-many
associations. The `sync` method accepts an array of IDs to place on the
intermediate table. Any IDs that are not in the given array will be removed
from the intermediate table. So, after this operation is complete, only the
IDs in the given array will exist in the intermediate table:

    $user->roles()->sync([1, 2, 3]);

You may also pass additional intermediate table values with the IDs:

    $user->roles()->sync([1 => ['expires' => true], 2, 3]);

If you would like to insert the same intermediate table values with each of
the synced model IDs, you may use the `syncWithPivotValues` method:

    $user->roles()->syncWithPivotValues([1, 2, 3], ['active' => true]);

If you do not want to detach existing IDs that are missing from the given
array, you may use the `syncWithoutDetaching` method:

    $user->roles()->syncWithoutDetaching([1, 2, 3]);

<a name="toggling-associations"></a>
#### Toggling Associations

The many-to-many relationship also provides a `toggle` method which
"toggles" the attachment status of the given related model IDs. If the given
ID is currently attached, it will be detached. Likewise, if it is currently
detached, it will be attached:

    $user->roles()->toggle([1, 2, 3]);

<a name="updating-a-record-on-the-intermediate-table"></a>
#### Updating A Record On The Intermediate Table

If you need to update an existing row in your relationship's intermediate
table, you may use the `updateExistingPivot` method. This method accepts the
intermediate record foreign key and an array of attributes to update:

    $user = User::find(1);

    $user->roles()->updateExistingPivot($roleId, [
        'active' => false,
    ]);

<a name="touching-parent-timestamps"></a>
## Touching Parent Timestamps

When a model defines a `belongsTo` or `belongsToMany` relationship to
another model, such as a `Comment` which belongs to a `Post`, it is
sometimes helpful to update the parent's timestamp when the child model is
updated.

For example, when a `Comment` model is updated, you may want to
automatically "touch" the `updated_at` timestamp of the owning `Post` so
that it is set to the current date and time. To accomplish this, you may add
a `touches` property to your child model containing the names of the
relationships that should have their `updated_at` timestamps updated when
the child model is updated:

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

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
        public function post()
        {
            return $this->belongsTo(Post::class);
        }
    }

> {note} Parent model timestamps will only be updated if the child model is updated using Eloquent's `save` method.
