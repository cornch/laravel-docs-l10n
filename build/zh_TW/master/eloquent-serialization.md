---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/59/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:26:00Z'
---

# Eloquent：序列化

- [簡介](#introduction)
- [序列化 Model 與 Collection](#serializing-models-and-collections)
   - [序列化為陣列](#serializing-to-arrays)
   - [序列化為 JSON](#serializing-to-json)
- [從 JSON 中隱藏屬性](#hiding-attributes-from-json)
- [將資料加入 JSON](#appending-values-to-json)
- [日期的序列化](#date-serialization)

<a name="introduction"></a>

## 簡介

在使用 Laravel 製作 API 時，我們常常會需要將 Model 於關聯轉換為陣列或 JSON。Eloquent 中包含了一些用來進行這些轉換的方便方法，我們也能控制哪些屬性要被包含在 Model 序列化呈現中。

> **Note** 若想瞭解有關控制 Eloquent Model 與 JSON 序列化更強健的方法，請參考 [Eloquent API 資源](/docs/{{version}}/eloquent-resources)說明文件。

<a name="serializing-models-and-collections"></a>

## 序列化 Model 與 Collection

<a name="serializing-to-arrays"></a>

### 序列化為陣列

若要將 Model 與其已載入的[關聯](/docs/{{version}}/eloquent-relationships)轉換為陣列，可使用 `toArray` 方法。該方法是遞歸的，因此所有的屬性與所有的關聯（還有關聯的關聯）都會被轉換為陣列：

    use App\Models\User;
    
    $user = User::with('roles')->first();
    
    return $user->toArray();

`attributesToArray` 方法可用來將 Model 的屬性轉換為陣列，但不轉換關聯：

    $user = User::first();
    
    return $user->attributesToArray();

也可以使用 Collection 實體上的 `toArray` 方法來將整個包含 Model 的 [Collection](/docs/{{version}}/eloquent-collections) 都轉換為陣列：

    $users = User::all();
    
    return $users->toArray();

<a name="serializing-to-json"></a>

### 序列化為 JSON

若要將 Model 轉換為 JSON，請使用 `toJson` 方法。與 `toArray` 方法類似，`toJson` 方法是遞歸的，因此所有屬性與關聯都會被轉換為 JSON。我們還能指定任何 [PHP 支援的](https://secure.php.net/manual/en/function.json-encode.php)任何 JSON 編碼選項：

    use App\Models\User;
    
    $user = User::find(1);
    
    return $user->toJson();
    
    return $user->toJson(JSON_PRETTY_PRINT);

或者，我們也可以將 Model 或 Collection 型別轉換成字串，這麼做會自動呼叫 Model 或 Collection 上的 `toJson` 方法：

    return (string) User::find(1);

由於 Model 與 Collection 在轉換為字串時會被轉換成 JSON，因此，我們可以直接在路由或 Controller 內回傳 Eloquent 物件。當這些 Eloquent Model 與 Collection 被從路由或 Controller 回傳時，Laravel 會自動將他們序列化成 JSON：

    Route::get('users', function () {
        return User::all();
    });

<a name="relationships"></a>

#### 關聯

當 Eloquent Model 被轉換為 JSON 時，Eloquent 會自動將所有已載入的關聯以屬性的方式包含在 JSON 內。此外，雖然 Eloquent 屬性是使用「駝峰命名法 (camelCase)」作為方法名稱定義的，但關聯的 JSON 屬性會是「蛇形命名法 (snake_case)」。

<a name="hiding-attributes-from-json"></a>

## 從 JSON 中隱藏屬性

有時候，我們可能會讓如密碼等屬性不要被包含在 Model 的陣列或 JSON 呈現上。為此，請在 Model 中加上一個 `$hidden` 屬性。列在 `$hidden` 屬性陣列中的屬性將不會被包含在 Model 的序列化呈現中：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = ['password'];
    }

> **Note** 若要隱藏關聯，請將關聯的方法名稱加到 Eloquent Model 的 `$hidden` 屬性內。

或者，我們也可以使用 `visible` 屬性來定義一個「允許列表」，代表要被包含在 Model 之陣列與 JSON 呈現的屬性。當 Model 被轉換為陣列或 JSON 時，所有不在 `$visible` 陣列內的屬性都會被隱藏：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The attributes that should be visible in arrays.
         *
         * @var array
         */
        protected $visible = ['first_name', 'last_name'];
    }

<a name="temporarily-modifying-attribute-visibility"></a>

#### 暫時修改屬性的能見度

對於某個 Model 實體，若我們想讓一些平常都是隱藏的屬性顯示出來，可以使用 `makeVisible` 方法。`makeVisible` 方法會回傳 Model 的實體：

    return $user->makeVisible('attribute')->toArray();

同樣的，若想隱藏一些平常顯示的屬性，可以使用 `makeHidden` 方法。

    return $user->makeHidden('attribute')->toArray();

若想暫時複寫所有 Visible 或 Hidden 屬性的話，可使用對應的 `setVisible` 與 `setHidden` 方法：

    return $user->setVisible(['id', 'name'])->toArray();
    
    return $user->setHidden(['email', 'password', 'remember_token'])->toArray();

<a name="appending-values-to-json"></a>

## 將值附加到 JSON

有時候，在將 Model 轉換為陣列或 JSON 時，我們可能會想新增一些資料庫中沒有對應欄位的屬性。為此，請先為該值定義一個 [Accessor](/docs/{{version}}/eloquent-mutators)：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * Determine if the user is an administrator.
         */
        protected function isAdmin(): Attribute
        {
            return new Attribute(
                get: fn () => 'yes',
            );
        }
    }

建立好 Accessor 後，請將屬性名稱加到 Model 中的 `appends` 屬性。請注意，屬性名稱一般在序列化呈現中都使用「蛇形命名法 (snake_case)」，但 Accessor 的 PHP 方法是使用「駝峰命名法 (camelCase)」定義的：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The accessors to append to the model's array form.
         *
         * @var array
         */
        protected $appends = ['is_admin'];
    }

將屬性加到 `appends` 列表後，該屬性就會被加到 Model 的陣列與 JSON 呈現中。在 `appends` 陣列中的屬性也會尊重 Model 上的 `visible` 與 `hidden` 設定。

<a name="appending-at-run-time"></a>

#### 在執行階段附加

在執行階段時，我們可以使用 `append` 方法來讓 Model 實體附加額外的屬性。或者，我們也可以使用 `setAppends` 方法來複寫給定 Model 實體上的整個附加屬性陣列：

    return $user->append('is_admin')->toArray();
    
    return $user->setAppends(['is_admin'])->toArray();

<a name="date-serialization"></a>

## 日期的序列化

<a name="customizing-the-default-date-format"></a>

#### 自訂預設的日期格式

複寫 `serializeDate` 方法即可定義預設的序列化方法。該方法並不會影響日期儲存到資料庫時的格式化方法：

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

<a name="customizing-the-date-format-per-attribute"></a>

#### 為個別屬性自訂日期格式

可以在 Model 的[型別轉換宣告](/docs/{{version}}/eloquent-mutators#attribute-casting)中指定日期格式，來為個別 Eloquent 日期屬性自訂序列化格式：

    protected $casts = [
        'birthday' => 'date:Y-m-d',
        'joined_at' => 'datetime:Y-m-d H:00',
    ];
