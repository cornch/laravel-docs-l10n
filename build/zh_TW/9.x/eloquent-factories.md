---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/181/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 52.78
---

# Eloquent：Factory

- [簡介](#introduction)
- [定義 Model Factory](#defining-model-factories)
  - [產生 Factory](#generating-factories)
  - [State - Factory 狀態](#factory-states)
  - [Factory 回呼](#factory-callbacks)
  
- [使用 Factory 建立 Model](#creating-models-using-factories)
  - [初始化 Model](#instantiating-models)
  - [保存 Model](#persisting-models)
  - [Sequence - 序列](#sequences)
  
- [Factory 關聯](#factory-relationships)
  - [HasMany 關聯](#has-many-relationships)
  - [BelongsTo 關聯](#belongs-to-relationships)
  - [多對多關聯](#many-to-many-relationships)
  - [多型關聯](#polymorphic-relationships)
  - [在 Factory 內定義關聯](#defining-relationships-within-factories)
  - [在關聯上回收利用現有的 Model](#recycling-an-existing-model-for-relationships)
  

<a name="introduction"></a>

## 簡介

在測試專案或為資料庫填充資料時，我們可能會需要先插入一些資料到資料庫內。比起在建立這個測試資料時手動指定各個欄位的值，在 Laravel 中，我們可以使用 Model Factory 來為各個 [Eloquent Model](/docs/{{version}}/eloquent) 定義一系列的預設屬性。

若要看看如何撰寫 Factory 的範例，請參考專案中的 `database/factories/UserFactory.php`。該 Factory 包含在所有的 Laravel 新專案內，裡面有下列 Factory 定義：

    namespace Database\Factories;
    
    use Illuminate\Database\Eloquent\Factories\Factory;
    use Illuminate\Support\Str;
    
    class UserFactory extends Factory
    {
        /**
         * Define the model's default state.
         *
         * @return array
         */
        public function definition()
        {
            return [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
            ];
        }
    }
如上所示，最基礎的 Factory 格式就像這樣，只需繼承 Laravel 的基礎 Factory 類別並定義一個 `definition` 方法。`definition` 方法應回傳一組預設的屬性值，會在使用 Factory 建立 Model 時被套用到該 Model 上。

通過 `fake` 輔助函式，Factory 就可以存取 [Faker](https://github.com/FakerPHP/Faker) PHP 函式庫。該函式庫可用來方便地產生各種類型的隨機資料以進行測試或資料填充。

> [!NOTE]  
> 可以通過在 `config/app.php` 設定檔中加上 `faker_locale` 選項來設定專案的 Faker 語系設定。

<a name="defining-model-factories"></a>

## 定義 Model Factory

<a name="generating-factories"></a>

### 產生 Factory

若要建立 Factory，請執行 `make:factory` [Artisan 指令](/docs/{{version}}/artisan)：

```shell
php artisan make:factory PostFactory
```
新的 Factory 類別會被放在 `database/factories` 目錄內。

<a name="factory-and-model-discovery-conventions"></a>

#### Model 於 Factory 的自動偵測慣例

定義好 Factory 後，就可以使用 `Illuminate\Database\Eloquent\Factories\HasFactory` Trait 提供給 Model 的靜態 `factory` 方法來為該 Model 初始化一個 Factory 實體。

`HasFactory` Trait 的 `factory` 方法會使用慣例來判斷適合用於該 Model 的 Factory。更準確來講，該方法會在 `Database\Factories` 命名空間下尋找符合該 Model 名稱並以 `Factory` 結尾的類別。若這些慣例不適合用在你正在寫的專案或 Factory，則可以在 Model 上複寫 `newFactory` 方法來直接回傳與該 Model 對應的 Factory 實體：

    use Database\Factories\Administration\FlightFactory;
    
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return FlightFactory::new();
    }
接著，在對應的 Factory 上定義一個 `model` 屬性：

    use App\Administration\Flight;
    use Illuminate\Database\Eloquent\Factories\Factory;
    
    class FlightFactory extends Factory
    {
        /**
         * The name of the factory's corresponding model.
         *
         * @var string
         */
        protected $model = Flight::class;
    }
<a name="factory-states"></a>

### State - Factory 狀態

State 操作方法可定義一些個別的修改，並可任意組合套用到 Model Factory 上。舉例來說，`Database\Factories\UserFactory` Factory 可包含一個 `suspended` (已停用) State 方法，用來修改該 Model Factory 的預設屬性值。

State 變換方法通常是呼叫 Laravel 基礎 Factory 類別所提供的 `state` 方法。這個 `state` 方法接受一個閉包，該閉包會收到一組陣列，陣列內包含了由這個 Factory 所定義的原始屬性。該閉包應回傳一組陣列，期中包含要修改的屬性：

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'account_status' => 'suspended',
            ];
        });
    }
#### 「^[Trashed](%E5%B7%B2%E5%88%AA%E9%99%A4)」State

若 Eloquent Model 有開啟[軟刪除](/docs/{{version}}/eloquent#soft-deleting)功能，則我們可以叫用內建的 `trashed` State 方法來代表要建立的 Model 應被標記為「已軟刪除」。所有的 Factory 都自動擁有該方法，因此不需手動定義 `trashed` State：

    use App\Models\User;
    
    $user = User::factory()->trashed()->create();
<a name="factory-callbacks"></a>

### Factory 回呼

Factory 回呼使用 `afterMaking` 與 `afterCreating` 方法來註冊，能讓你在產生或建立 Model 時執行額外的任務。要註冊這些回呼，應在 Factory 類別上定義一個 `configure` 方法。Laravel 會在 Factory 初始化後自動呼叫這個方法：

    namespace Database\Factories;
    
    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\Factory;
    use Illuminate\Support\Str;
    
    class UserFactory extends Factory
    {
        /**
         * Configure the model factory.
         *
         * @return $this
         */
        public function configure()
        {
            return $this->afterMaking(function (User $user) {
                //
            })->afterCreating(function (User $user) {
                //
            });
        }
    
        // ...
    }
<a name="creating-models-using-factories"></a>

## 使用 Factory 來建立 Model

<a name="instantiating-models"></a>

### 產生 Model

定義好 Factory 後，就可以使用 `Illuminate\Database\Eloquent\Factories\HasFactory` trait 提供給 Model 的 `factory` 靜態方法來產生用於該 Model 的 Factory 實體。來看看一些建立 Model 的範例。首先，我們先使用 `make` 方法來在不儲存進資料庫的情況下建立 Model：

    use App\Models\User;
    
    $user = User::factory()->make();
可以使用 `count` 方法來建立包含多個 Model 的 Collection：

    $users = User::factory()->count(3)->make();
<a name="applying-states"></a>

#### 套用 State

也可以將 [State](#factory-states) 套用至 Model 上。若想套用多個 State 變換到 Model 上，只需要直接呼叫 State 變換方法即可：

    $users = User::factory()->count(5)->suspended()->make();
<a name="overriding-attributes"></a>

#### 複寫屬性

若想複寫 Model 上的一些預設值，可以傳入陣列到 `make` 方法上。只要指定要取代的屬性即可，剩下的屬性會保持 Factory 所指定的預設值：

    $user = User::factory()->make([
        'name' => 'Abigail Otwell',
    ]);
或者，也可以直接在 Factory 實體上呼叫 `state` 方法來內嵌 State 變換：

    $user = User::factory()->state([
        'name' => 'Abigail Otwell',
    ])->make();
> [!NOTE]  
> [大量賦值保護](/docs/{{version}}/eloquent#mass-assignment) 會在使用 Factory 建立 Model 時自動禁用。

<a name="persisting-models"></a>

### 保存 Model

`create` 方法會產生 Model 實體並使用 Eloquent 的 `save` 方法來將其永久保存於資料庫內：

    use App\Models\User;
    
    // Create a single App\Models\User instance...
    $user = User::factory()->create();
    
    // Create three App\Models\User instances...
    $users = User::factory()->count(3)->create();
可以通過將一組屬性陣列傳入 `create` 方法來複寫該 Factory 的預設 Model 屬性：

    $user = User::factory()->create([
        'name' => 'Abigail',
    ]);
<a name="sequences"></a>

### Sequence - 序列

有時候，我們可能會需要為每個建立的 Model 更改某個特定的屬性。可以通過將 State 變換定義為序列來達成。舉例來說，我們可能會想為每個建立的使用者設定 `admin` 欄位的值為 `Y` 或 `N`：

    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\Sequence;
    
    $users = User::factory()
                    ->count(10)
                    ->state(new Sequence(
                        ['admin' => 'Y'],
                        ['admin' => 'N'],
                    ))
                    ->create();
在上面的範例中，有五個使用者會以 `admin` 值 `Y` 建立，另外五個使用者將以 `admin` 值 `N` 建立。

若有需要，也可以提供閉包作為序列的值。該閉包會在每次序列需要新值是被叫用：

    $users = User::factory()
                    ->count(10)
                    ->state(new Sequence(
                        fn ($sequence) => ['role' => UserRoles::all()->random()],
                    ))
                    ->create();
在 Sequence 閉包中，可以在注入到閉包中的 Sequence 實體上存取 `$index` 與 `$count` 屬性。`$index` 屬性包含了該 Sequence 到目前為止所進行的迭代數，而 `$count` 屬性則代表了該 Sequence 總過將被叫用幾次：

    $users = User::factory()
                    ->count(10)
                    ->sequence(fn ($sequence) => ['name' => 'Name '.$sequence->index])
                    ->create();
為了讓開發起來更方便，也提供了一個 `sequence` 方法可用來套用 Sequence。該方法會在內部幫你呼叫 `state` 方法。`sequence` 方法的引數為一個陣列，或是一組會被依序套用的屬性陣列：

    $users = User::factory()
                    ->count(2)
                    ->sequence(
                        ['name' => 'First User'],
                        ['name' => 'Second User'],
                    )
                    ->create();
<a name="factory-relationships"></a>

## Factory 關聯

<a name="has-many-relationships"></a>

### HasMany 關聯

接著，來看看如何使用 Laravel 中流利的 Factory 方法建立 Eloquent Model 關聯。首先，假設專案中有個 `App\Models\User` Model 以及 `App\Models\Post` Model。然後，假設 `User` Model 中定義了對 `Post` 的 `hasMany` 關聯。我們可以使用 Laravel Factory 提供的 `has` 方法來建立一個有三篇貼文的使用者。這個 `has` 方法接受一個 Factory 實體：

    use App\Models\Post;
    use App\Models\User;
    
    $user = User::factory()
                ->has(Post::factory()->count(3))
                ->create();
依照慣例，當傳入 `Post` Model 給 `has` 方法時，Laravel 會假設 `User` Model 中有定義這個關聯的 `posts` 方法。若有需要，可以明顯指定要操作的關聯名稱：

    $user = User::factory()
                ->has(Post::factory()->count(3), 'posts')
                ->create();
當然，也可以在關聯 Model 上進行 State 操作。此外，若 State 更改需要存取上層 Model，也可以傳入基於閉包的 State 變換：

    $user = User::factory()
                ->has(
                    Post::factory()
                            ->count(3)
                            ->state(function (array $attributes, User $user) {
                                return ['user_type' => $user->type];
                            })
                )
                ->create();
<a name="has-many-relationships-using-magic-methods"></a>

#### 使用魔術方法

為了方便起見，可以使用 Laravel 的魔術 Factory 關聯方法來建立關聯。舉例來說，下列範例會使用慣例來判斷應通過 `User` Model 上的 `posts` 關聯方法來建立關聯 Model：

    $user = User::factory()
                ->hasPosts(3)
                ->create();
在使用魔術方法建立 Factory 關聯時，可以傳入包含屬性的陣列來在關聯 Model 上複寫：

    $user = User::factory()
                ->hasPosts(3, [
                    'published' => false,
                ])
                ->create();
若 State 更改需要存取上層 Model，可以提供一個基於閉包的 State 變換：

    $user = User::factory()
                ->hasPosts(3, function (array $attributes, User $user) {
                    return ['user_type' => $user->type];
                })
                ->create();
<a name="belongs-to-relationships"></a>

### BelongsTo 關聯

我們已經瞭解如何使用 Factory 來建立「Has Many」關聯了，接著來看看這種關聯的想法。使用 `for` 方法可以用來定義使用 Factory 建立的 Model 所隸屬 (Belong To) 的上層 Model。舉例來說，我們可以建立三個隸屬於單一使用者的 `App\Models\Post` Model 實體：

    use App\Models\Post;
    use App\Models\User;
    
    $posts = Post::factory()
                ->count(3)
                ->for(User::factory()->state([
                    'name' => 'Jessica Archer',
                ]))
                ->create();
若已經有應與這些正在建立的 Model 關聯的上層 Model 實體，可以將該 Model 實體傳入 `for` 方法：

    $user = User::factory()->create();
    
    $posts = Post::factory()
                ->count(3)
                ->for($user)
                ->create();
<a name="belongs-to-relationships-using-magic-methods"></a>

#### 使用魔術方法

為了方便起見，可以使用 Laravel 的魔術 Factory 關聯方法來定義「Belongs To」關聯。舉例來說，下列範例會使用慣例來判斷應使用 `Post` Model 上的 `user` 關聯方法來設定這三個貼文應隸屬於哪裡：

    $posts = Post::factory()
                ->count(3)
                ->forUser([
                    'name' => 'Jessica Archer',
                ])
                ->create();
<a name="many-to-many-relationships"></a>

### 多對多關聯

與 [HasMany 關聯](#has-many-relationships)，「多對多」關聯也可以通過 `has` 方法建立：

    use App\Models\Role;
    use App\Models\User;
    
    $user = User::factory()
                ->has(Role::factory()->count(3))
                ->create();
<a name="pivot-table-attributes"></a>

#### Pivot 表屬性

若有需要為這些 Model 定義關聯 Pivot／中介資料表上的屬性，則可使用 `hasAttached` 方法。這個方法接受一個陣列，其中包含 Pivot 資料表上的屬性名稱，第二個引數則為其值：

    use App\Models\Role;
    use App\Models\User;
    
    $user = User::factory()
                ->hasAttached(
                    Role::factory()->count(3),
                    ['active' => true]
                )
                ->create();
若 State 更改需要存取關聯 Model，可以提供一個基於閉包的 State 變換：

    $user = User::factory()
                ->hasAttached(
                    Role::factory()
                        ->count(3)
                        ->state(function (array $attributes, User $user) {
                            return ['name' => $user->name.' Role'];
                        }),
                    ['active' => true]
                )
                ->create();
若已有 Model 實體想讓正在建立的 Model 附加，可以將該 Model 實體傳入 `hasAttached` 方法。在此範例中，會將三個相同的角色附加給三個使用者：

    $roles = Role::factory()->count(3)->create();
    
    $user = User::factory()
                ->count(3)
                ->hasAttached($roles, ['active' => true])
                ->create();
<a name="many-to-many-relationships-using-magic-methods"></a>

#### 使用魔術方法

為了方便起見，可以使用 Laravel 的魔術 Factory 關聯方法來定義 Many to Many 關聯。舉例來說，下列範例會使用慣例來判斷應通過 `User` Model 上的 `roles` 關聯方法來建立關聯 Model：

    $user = User::factory()
                ->hasRoles(1, [
                    'name' => 'Editor'
                ])
                ->create();
<a name="polymorphic-relationships"></a>

### 多型 (Polymorphic) 關聯

[多型 (Polymorphic) 關聯](/docs/{{version}}/eloquent-relationships#polymorphic-relationships) 也可以使用 Factory 來建立。可使用與一般「HasMany」關聯相同的方法來建多型「Morph Many」關聯。舉例來說，若 `App\Models\Post` Model 使用 `morphMany` 關聯到 `App\Models\Comment` Model：

    use App\Models\Post;
    
    $post = Post::factory()->hasComments(3)->create();
<a name="morph-to-relationships"></a>

#### MorphTo 關聯

在建立 `morphTo` 關聯時無法使用魔法方法。必須直接使用 `for` 方法，並明顯提供該關聯的名稱。舉例來說，假設 `Comment` Model 有個 `commantable` 方法，該方法定義了 `morphTo` 關聯。在這種情況下，我們可以直接使用 `for` 方法來建立三個隸屬於單一貼文的留言：

    $comments = Comment::factory()->count(3)->for(
        Post::factory(), 'commentable'
    )->create();
<a name="polymorphic-many-to-many-relationships"></a>

#### 多型的多對多關聯

要建立多型的「多對多」(`morphyToMany` / `morphedByMany`) 關聯，就與其他非多型的「多對多」關聯一樣：

    use App\Models\Tag;
    use App\Models\Video;
    
    $videos = Video::factory()
                ->hasAttached(
                    Tag::factory()->count(3),
                    ['public' => true]
                )
                ->create();
當然，也可以使用 `has` 魔法方法來建立多型的「多對多」關聯：

    $videos = Video::factory()
                ->hasTags(3, ['public' => true])
                ->create();
<a name="defining-relationships-within-factories"></a>

### 在 Factory 中定義關聯

若要在 Model Factory 中定義關聯，則通常需要為該關聯的外部索引鍵 (Foreign Key) 指定新的 Factory 實體。一般是使用「相反」的關聯來處理，如 `belongsTo` 與 `morphTo` 關聯。舉例來說，若想在建立貼文時建立新使用者，可以像這樣：

    use App\Models\User;
    
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->title(),
            'content' => fake()->paragraph(),
        ];
    }
若該關聯的欄位仰賴定義其的 Factory，則可以在屬性中放入閉包。該閉包會收到該 Factory 取值結果的屬性陣列：

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'user_type' => function (array $attributes) {
                return User::find($attributes['user_id'])->type;
            },
            'title' => fake()->title(),
            'content' => fake()->paragraph(),
        ];
    }
<a name="recycling-an-existing-model-for-relationships"></a>

### 在關聯上回收使用現有的 Model

若有多個 Model 與另一個 Model 共用一個共同的關聯，則可以使用 ^[`recycle`](%E5%9B%9E%E6%94%B6) 方法來確保 Factory 所建立的關聯都重複使用此 Model 的某個單一實體：

舉例來說，假設有 ^[`Airline`](%E8%88%AA%E7%A9%BA%E5%85%AC%E5%8F%B8)、^[`Fligh`](%E8%88%AA%E7%8F%AD)、^[`Ticket`](%E6%A9%9F%E7%A5%A8) 三個 Model，其中，Ticket 隸屬於 (BelongsTo) Airline 與 Flight，而 Flight 也同時隸屬於 Airline。在建立 Ticket 時，我們可能會想在 Ticket 與 Flight 上都使用同一個 Airline。因此，我們可以將 Airline 實體傳給 `recycle` 方法：

    Ticket::factory()
        ->recycle(Airline::factory()->create())
        ->create();
如果你的 Model 都隸屬於 (BelongsTo) 一組相同的使用者或團隊，那麼就很適合使用 `recycle` 方法。

也可傳入一組現有 Model 的 Collection 給 `recycle` 方法。傳入 Collection 給 `recycle` 方法時，當 Factory 需要此類型的 Model 時，就會從此 Collection 中隨機選擇一個 Model：

    Ticket::factory()
        ->recycle($airlines)
        ->create();