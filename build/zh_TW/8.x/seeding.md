---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/151/en-zhtw'
updatedAt: '2023-02-11T10:28:00Z'
contributors: {  }
progress: 57.69
---

# 資料庫：Seeding

- [簡介](#introduction)
- [撰寫 Seeders](#writing-seeders)
  - [使用 Model Factory](#using-model-factories)
  - [呼叫其他 Seeder](#calling-additional-seeders)
  
- [執行 Seeder](#running-seeders)

<a name="introduction"></a>

## 簡介

在 Laravel 中，我們可以使用 Seed 類別來為資料庫提供初始資料。Seed 類別存放在 `database/seeders` 目錄中。預設情況下，Laravel 中已定義了一個 `DatabaseSeeder` 類別。在這個類別中，我們可以呼叫 `call` 方法來執行其他 Seed 類別，好讓我們能控制資料填充的順序。

> [!TIP]  
> 在進行 Seeder 時，會自動禁用[大量賦值保護](/docs/{{version}}/eloquent#mass-assignment)。

<a name="writing-seeders"></a>

## 撰寫 Seeder

若要產生 Seeder，請執行 `make:seeder` [Artisan 指令](/docs/{{version}}/artisan)。Laravel 所產生的所有 Seeder 都會放在 `database/seeders` 目錄下：

    php artisan make:seeder UserSeeder
Seeder 類別中預設只包含了一個方法：`run`。執行 `db:seed` [Artisan 指令](/docs/{{version}}/artisan) 時，會呼叫該方法。在 `run` 方法中，我們可以任意將資料寫入資料庫內。我們可以使用 [Query Builder](/docs/{{version}}/queries) 來手動寫入資料，或是使用 [Eloquent Model Factory](/docs/{{version}}/database-testing#defining-model-factories) 來寫入資料。

來看看一個範例，讓我們來修改預設的 `DatabaseSeeder` 類別，並在 `run` 方法內新增一個資料庫 Insert 陳述式：

    <?php
    
    namespace Database\Seeders;
    
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Str;
    
    class DatabaseSeeder extends Seeder
    {
        /**
         * Run the database seeders.
         *
         * @return void
         */
        public function run()
        {
            DB::table('users')->insert([
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('password'),
            ]);
        }
    }
> [!TIP]  
> 在 `run` 方法的簽章 (Signature) 中，我們可以 ^[Type-Hint](%E5%9E%8B%E5%88%A5%E6%8F%90%E7%A4%BA) 任何需要的相依性。Laravel 的 [Service Container](/docs/{{version}}/container) 會自動解析 Type-Hint 中的相依性。

<a name="using-model-factories"></a>

### 使用 Model Factory

當然，手動為每個要填入的 Model 指定屬性值是很麻煩的。我們不需要這麼做，而可以使用 [Model Factory](/docs/{{version}}/database-testing#defining-model-factories) 來方便地產生大量資料。首先，請先看看 [Model Factory 的說明文件](/docs/{{version}}/database-testing#defining-model-factories)以瞭解如何定義 Factory。

舉例來說，我們先來建立 50 個使用者，其中每個使用者都有 1 篇關聯的貼文：

    use App\Models\User;
    
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        User::factory()
                ->count(50)
                ->hasPosts(1)
                ->create();
    }
<a name="calling-additional-seeders"></a>

### 呼叫其他 Seeder

在 `DatabaseSeeder` 類別中，我們可以使用 `call` 方法來執行其他 Seed 類別。使用 `call` 方法，我們就可以將資料填充的城市拆分成多個檔案，以避免單一 Seeder 類別過於肥大。`call` 方法接受一組要執行的 Seeder 類別名稱陣列：

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
        ]);
    }
<a name="running-seeders"></a>

## 執行 Seeder

我們可以執行 `db:seed` Artisan 指令來填充資料庫。預設情況下，`db:seed` 指令會執行 `Database\Seeders\DatabaseSeeder` 類別，在該類別內可以進一步叫用其他 Seed 類別。不過，我們也可以使用 `--class` 選項來個別指定要執行的 Seeder 類別：

    php artisan db:seed
    
    php artisan db:seed --class=UserSeeder
我們也可以使用 `migrate:fresh` 指令，並搭配 `--seed` 選項來填充資料。該指令會刪除所有資料表，並重新執行所有的 Migration。若有需要完全重建資料庫，就很適合使用這個指令：

    php artisan migrate:fresh --seed
<a name="forcing-seeding-production"></a>

#### 在正式環境中強制執行 Seeder

有些資料填充的動作是可能會導致資料被修改或消失。為了避免在正式環境資料庫中執行資料填充指令，因此在 `production` 環境中執行 Seeder 時，會出現提示要求確認。若要強制 Seeder 而不跳出提示，請使用 `--force` 旗標：

    php artisan db:seed --force