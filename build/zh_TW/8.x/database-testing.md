# 資料庫測試

- [簡介](#introduction)
    - [為每個測試重設資料庫](#resetting-the-database-after-each-test)
- [定義 Model Factory](#defining-model-factories)
    - [概念概覽](#concept-overview)
    - [產生 Factory](#generating-factories)
    - [Factory State](#factory-states)
    - [Factory 回呼](#factory-callbacks)
- [使用 Factory 來建立 Model](#creating-models-using-factories)
    - [建立 Model](#instantiating-models)
    - [維持 Model](#persisting-models)
    - [序列](#sequences)
- [Factory 關聯](#factory-relationships)
    - [Has Many 關聯](#has-many-relationships)
    - [Belongs To 關聯](#belongs-to-relationships)
    - [Many To Many 關聯](#many-to-many-relationships)
    - [Polymorphic 關聯](#polymorphic-relationships)
    - [使用 Factory 定義關聯](#defining-relationships-within-factories)
- [執行 Seeder](#running-seeders)
- [可用的 Assertion](#available-assertions)

<a name="introduction"></a>
## 簡介

Laravel 提供了數種實用工具與 Assertion (判斷提示) 讓你能更輕鬆地測試資料庫驅動的應用程式。此外，Laravel 的 Model
Factory 與 Seeder 也讓使用應用程式的 Eloquent Model
與關聯來測試資料庫記錄更容易。我們會在接下來的說明文件內討論這些強大的工具。

<a name="resetting-the-database-after-each-test"></a>
### 在每個測試後重設資料庫

在進一步繼續之前，我們先來討論如何在每個測試前重設資料庫，這樣一來前一個測試的資料就不會影響到接下來的測試。Laravel 內含了
`Illuminate\Foundation\Testing\RefreshDatabase` Trait，會處理這樣的重設。只需要在測試類別內 use
這個 Trait 即可：

    <?php

    namespace Tests\Feature;

    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        use RefreshDatabase;

        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_basic_example()
        {
            $response = $this->get('/');

            // ...
        }
    }

<a name="defining-model-factories"></a>
## 定義 Model Factory

<a name="concept-overview"></a>
### 概念概覽

首先，來討論有關 Eloquent Model
Factory。在測試時，我們可能會需要在執行測試前先插入一些資料到資料庫內。比起在建立這個測試資料時手動指定各個欄位的值，Laravel 中可以使用
Model Factory 來為各個 [Eloquent Model](/docs/{{version}}/eloquent) 定義一系列的預設屬性。

若要看看如何撰寫 Factory 的範例，請參考應用程式中的 `database/factories/UserFactory.php`。該
Factory 包含在所有新的 Laravel 應用程式內，且包含了下列 Factory 定義：

    namespace Database\Factories;

    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\Factory;
    use Illuminate\Support\Str;

    class UserFactory extends Factory
    {
        /**
         * The name of the factory's corresponding model.
         *
         * @var string
         */
        protected $model = User::class;

        /**
         * Define the model's default state.
         *
         * @return array
         */
        public function definition()
        {
            return [
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
            ];
        }
    }

如你所見，最基礎的 Factory 格式就像這樣，繼承 Laravel 的基礎 Factory 類別並定義一個 `model` 屬性與
`definition` 方法。`definition` 方法應回傳一組預設的屬性至，會在使用 Factory 建立 Model 時被套用到該
Model 上。

通過 `faker` 屬性，Factory 就可以存取 [Faker](https://github.com/FakerPHP/Faker) PHP
函式庫。該函式庫可用來方便地產生各種類型的隨機資料以進行測試。

> {tip} 可以通過在 `config/app.php` 組態設定檔中加上 `faker_locale` 選項來設定應用程式的 Faker 地區設定。

<a name="generating-factories"></a>
### 產生 Factory

若要建立 Factory，請執行 `make:factory` [Artisan 指令](/docs/{{version}}/artisan)：

    php artisan make:factory PostFactory

新的 Factory 類別會被放在 `database/factories` 目錄內。

`--model` 選項可用來指定 Factory 要建立的 Model 名稱。這個選項會用來將給定的 Model 預先填寫到產生的 Factory
檔內：

    php artisan make:factory PostFactory --model=Post

<a name="factory-states"></a>
### Factory State

State 操作方法可定義一些個別的修改，並可任意組合套用到 Model Factory
上。舉例來說，`Database\Factories\UserFactory` Factory 可包含一個 `suspended` (已停用)
State 方法，用來修改該 Model Factory 的預設屬性值。

State 變換方法通常是呼叫 Laravel 基礎 Factory 類別所提供的 `state` 方法。這個 `state`
方法接受一個閉包，該閉包會收到一組陣列，陣列內包含了由這個 Factory 所定義的原始屬性。該閉包應回傳一組陣列，期中包含要修改的屬性：

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

<a name="factory-callbacks"></a>
### Factory 回呼

Factory 回呼使用 `afterMaking` 與 `afterCreating` 方法來註冊，能讓你在產生或建立 Model
時執行額外的任務。要註冊這些回呼，應在 Factory 類別上定義一個 `configure` 方法。Laravel 會在 Factory
初始化後自動呼叫這個方法：

    namespace Database\Factories;

    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\Factory;
    use Illuminate\Support\Str;

    class UserFactory extends Factory
    {
        /**
         * The name of the factory's corresponding model.
         *
         * @var string
         */
        protected $model = User::class;

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

定義好 Factory 後，就可以使用 `Illuminate\Database\Eloquent\Factories\HasFactory`
trait 提供給 Model 的 `factory` 靜態方法來產生用於該 Model 的 Factory 實體。來看看一些建立 Model
的範例。首先，我們先使用 `make` 方法來在不儲存進資料庫的情況下建立 Model：

    use App\Models\User;

    public function test_models_can_be_instantiated()
    {
        $user = User::factory()->make();

        // 在測試中使用 Model…
    }

可以使用 `count` 方法來建立包含多個 Model 的 Collection：

    $users = User::factory()->count(3)->make();

<a name="applying-states"></a>
#### 套用 State

也可以將 [State](#factory-states) 套用至 Model 上。若想套用多個 State 變換到 Model 上，只需要直接呼叫
State 變換方法即可：

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

> {tip} [大量賦值保護](/docs/{{version}}/eloquent#mass-assignment) 會在使用 Factory 建立 Model 時自動禁用。

<a name="connecting-factories-and-models"></a>
#### 連結 Factory 與 Model

`HasFactory` Trait 的 `factory` 方法會使用慣例來判斷用於該 Model 的適當 Factory。更準確來講，該方法會在
`Database\Factories` 命名空間下尋找符合該 Model 名稱並以 `Factory`
作為後綴的類別。若這些慣例不符合特定的應用程式或 Factory，則可以在 Model 上複寫 `newFactory` 方法來直接回傳與該 Model
對應的 Factory 實體：

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

<a name="persisting-models"></a>
### 持續性 Model

`create` 方法會產生 Model 實體並使用 Eloquent 的 `save` 方法來將其永久保存於資料庫內：

    use App\Models\User;

    public function test_models_can_be_persisted()
    {
        // 建立單一 App\Models\User 實體…
        $user = User::factory()->create();

        // 建立三個 App\Models\User 實體…
        $users = User::factory()->count(3)->create();

        // 在測試中使用 Model…
    }

可以通過將一組屬性陣列傳入 `create` 方法來複寫該 Factory 的預設 Model 屬性：

    $user = User::factory()->create([
        'name' => 'Abigail',
    ]);

<a name="sequences"></a>
### 序列

有時候，我們可能會需要為每個建立的 Model 更改某個特定的屬性。可以通過將 State
變換定義為序列來達成。舉例來說，我們可能會想為每個建立的使用者設定 `admin` 欄位的值為 `Y` 或 `N`：

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
                        fn () => ['role' => UserRoles::all()->random()],
                    ))
                    ->create();

<a name="factory-relationships"></a>
## Factory 關聯

<a name="has-many-relationships"></a>
### Has Many 關聯

Next, let's explore building Eloquent model relationships using Laravel's
fluent factory methods. First, let's assume our application has an
`App\Models\User` model and an `App\Models\Post` model. Also, let's assume
that the `User` model defines a `hasMany` relationship with `Post`. We can
create a user that has three posts using the `has` method provided by the
Laravel's factories. The `has` method accepts a factory instance:

    use App\Models\Post;
    use App\Models\User;

    $user = User::factory()
                ->has(Post::factory()->count(3))
                ->create();

By convention, when passing a `Post` model to the `has` method, Laravel will
assume that the `User` model must have a `posts` method that defines the
relationship. If necessary, you may explicitly specify the name of the
relationship that you would like to manipulate:

    $user = User::factory()
                ->has(Post::factory()->count(3), 'posts')
                ->create();

Of course, you may perform state manipulations on the related models. In
addition, you may pass a closure based state transformation if your state
change requires access to the parent model:

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
#### Using Magic Methods

For convenience, you may use Laravel's magic factory relationship methods to
build relationships. For example, the following example will use convention
to determine that the related models should be created via a `posts`
relationship method on the `User` model:

    $user = User::factory()
                ->hasPosts(3)
                ->create();

When using magic methods to create factory relationships, you may pass an
array of attributes to override on the related models:

    $user = User::factory()
                ->hasPosts(3, [
                    'published' => false,
                ])
                ->create();

You may provide a closure based state transformation if your state change
requires access to the parent model:

    $user = User::factory()
                ->hasPosts(3, function (array $attributes, User $user) {
                    return ['user_type' => $user->type];
                })
                ->create();

<a name="belongs-to-relationships"></a>
### Belongs To Relationships

Now that we have explored how to build "has many" relationships using
factories, let's explore the inverse of the relationship. The `for` method
may be used to define the parent model that factory created models belong
to. For example, we can create three `App\Models\Post` model instances that
belong to a single user:

    use App\Models\Post;
    use App\Models\User;

    $posts = Post::factory()
                ->count(3)
                ->for(User::factory()->state([
                    'name' => 'Jessica Archer',
                ]))
                ->create();

If you already have a parent model instance that should be associated with
the models you are creating, you may pass the model instance to the `for`
method:

    $user = User::factory()->create();

    $posts = Post::factory()
                ->count(3)
                ->for($user)
                ->create();

<a name="belongs-to-relationships-using-magic-methods"></a>
#### Using Magic Methods

For convenience, you may use Laravel's magic factory relationship methods to
define "belongs to" relationships. For example, the following example will
use convention to determine that the three posts should belong to the `user`
relationship on the `Post` model:

    $posts = Post::factory()
                ->count(3)
                ->forUser([
                    'name' => 'Jessica Archer',
                ])
                ->create();

<a name="many-to-many-relationships"></a>
### Many To Many Relationships

Like [has many relationships](#has-many-relationships), "many to many"
relationships may be created using the `has` method:

    use App\Models\Role;
    use App\Models\User;

    $user = User::factory()
                ->has(Role::factory()->count(3))
                ->create();

<a name="pivot-table-attributes"></a>
#### Pivot Table Attributes

If you need to define attributes that should be set on the pivot /
intermediate table linking the models, you may use the `hasAttached`
method. This method accepts an array of pivot table attribute names and
values as its second argument:

    use App\Models\Role;
    use App\Models\User;

    $user = User::factory()
                ->hasAttached(
                    Role::factory()->count(3),
                    ['active' => true]
                )
                ->create();

You may provide a closure based state transformation if your state change
requires access to the related model:

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

If you already have model instances that you would like to be attached to
the models you are creating, you may pass the model instances to the
`hasAttached` method. In this example, the same three roles will be attached
to all three users:

    $roles = Role::factory()->count(3)->create();

    $user = User::factory()
                ->count(3)
                ->hasAttached($roles, ['active' => true])
                ->create();

<a name="many-to-many-relationships-using-magic-methods"></a>
#### Using Magic Methods

For convenience, you may use Laravel's magic factory relationship methods to
define many to many relationships. For example, the following example will
use convention to determine that the related models should be created via a
`roles` relationship method on the `User` model:

    $user = User::factory()
                ->hasRoles(1, [
                    'name' => 'Editor'
                ])
                ->create();

<a name="polymorphic-relationships"></a>
### Polymorphic Relationships

[Polymorphic
relationships](/docs/{{version}}/eloquent-relationships#polymorphic-relationships)
may also be created using factories. Polymorphic "morph many" relationships
are created in the same way as typical "has many" relationships. For
example, if a `App\Models\Post` model has a `morphMany` relationship with a
`App\Models\Comment` model:

    use App\Models\Post;

    $post = Post::factory()->hasComments(3)->create();

<a name="morph-to-relationships"></a>
#### Morph To Relationships

Magic methods may not be used to create `morphTo` relationships. Instead,
the `for` method must be used directly and the name of the relationship must
be explicitly provided. For example, imagine that the `Comment` model has a
`commentable` method that defines a `morphTo` relationship. In this
situation, we may create three comments that belong to a single post by
using the `for` method directly:

    $comments = Comment::factory()->count(3)->for(
        Post::factory(), 'commentable'
    )->create();

<a name="polymorphic-many-to-many-relationships"></a>
#### Polymorphic Many To Many Relationships

Polymorphic "many to many" (`morphToMany` / `morphedByMany`) relationships
may be created just like non-polymorphic "many to many" relationships:

    use App\Models\Tag;
    use App\Models\Video;

    $videos = Video::factory()
                ->hasAttached(
                    Tag::factory()->count(3),
                    ['public' => true]
                )
                ->create();

Of course, the magic `has` method may also be used to create polymorphic
"many to many" relationships:

    $videos = Video::factory()
                ->hasTags(3, ['public' => true])
                ->create();

<a name="defining-relationships-within-factories"></a>
### Defining Relationships Within Factories

To define a relationship within your model factory, you will typically
assign a new factory instance to the foreign key of the relationship. This
is normally done for the "inverse" relationships such as `belongsTo` and
`morphTo` relationships. For example, if you would like to create a new user
when creating a post, you may do the following:

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
            'title' => $this->faker->title,
            'content' => $this->faker->paragraph,
        ];
    }

If the relationship's columns depend on the factory that defines it you may
assign a closure to an attribute. The closure will receive the factory's
evaluated attribute array:

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
            'title' => $this->faker->title,
            'content' => $this->faker->paragraph,
        ];
    }

<a name="running-seeders"></a>
## Running Seeders

If you would like to use [database seeders](/docs/{{version}}/seeding) to
populate your database during a feature test, you may invoke the `seed`
method. By default, the `seed` method will execute the `DatabaseSeeder`,
which should execute all of your other seeders. Alternatively, you pass a
specific seeder class name to the `seed` method:

    <?php

    namespace Tests\Feature;

    use Database\Seeders\OrderStatusSeeder;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        use RefreshDatabase;

        /**
         * Test creating a new order.
         *
         * @return void
         */
        public function test_orders_can_be_created()
        {
            // Run the DatabaseSeeder...
            $this->seed();

            // Run a specific seeder...
            $this->seed(OrderStatusSeeder::class);

            // ...
        }
    }

Alternatively, you may instruct the `RefreshDatabase` trait to automatically
seed the database before each test. You may accomplish this by defining a
`$seed` property on your test class:

    <?php

    namespace Tests\Feature;

    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        /**
         * Indicates whether the default seeder should run before each test.
         *
         * @var bool
         */
        protected $seed = true;

        // ...
    }

When the `$seed` property is `true`, the test will run the
`Database\Seeders\DatabaseSeeder` class before each test. However, you may
specify a specific seeder that should be executed by defining a `$seeder`
property on your test class:

    use Database\Seeders\OrderStatusSeeder;

    /**
     * Run a specific seeder before each test.
     *
     * @var string
     */
    protected $seeder = OrderStatusSeeder::class;

<a name="available-assertions"></a>
## Available Assertions

Laravel provides several database assertions for your
[PHPUnit](https://phpunit.de/) feature tests. We'll discuss each of these
assertions below.

<a name="assert-database-count"></a>
#### assertDatabaseCount

Assert that a table in the database contains the given number of records:

    $this->assertDatabaseCount('users', 5);

<a name="assert-database-has"></a>
#### assertDatabaseHas

Assert that a table in the database contains records matching the given key
/ value query constraints:

    $this->assertDatabaseHas('users', [
        'email' => 'sally@example.com',
    ]);

<a name="assert-database-missing"></a>
#### assertDatabaseMissing

Assert that a table in the database does not contain records matching the
given key / value query constraints:

    $this->assertDatabaseMissing('users', [
        'email' => 'sally@example.com',
    ]);

<a name="assert-deleted"></a>
#### assertDeleted

The `assertDeleted` asserts that a given Eloquent model has been deleted
from the database:

    use App\Models\User;

    $user = User::find(1);

    $user->delete();

    $this->assertDeleted($user);

The `assertSoftDeleted` method may be used to assert a given Eloquent model
has been "soft deleted":

    $this->assertSoftDeleted($user);
