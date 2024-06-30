---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/53/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:18:00Z'
---

# Eloquent：更動子與型別轉換

- [簡介](#introduction)
- [Accessor 與 Mutator](#accessors-and-mutators)
   - [定義 Accessor](#defining-an-accessor)
   - [定義 Mutator](#defining-a-mutator)
- [屬性的型別轉換](#attribute-casting)
   - [陣列與 JSON 的型別轉換](#array-and-json-casting)
   - [日期的型別轉換](#date-casting)
   - [Enum 的型別轉換](#enum-casting)
   - [Encrypted 型別轉換](#encrypted-casting)
   - [查詢階段的型別轉換](#query-time-casting)
- [自訂型別轉換](#custom-casts)
   - [Value Object 的型別轉換](#value-object-casting)
   - [陣列與 JSON 的序列化](#array-json-serialization)
   - [輸入型別轉換](#inbound-casting)
   - [型別轉換的參數](#cast-parameters)
   - [Castable](#castables)

<a name="introduction"></a>

## 簡介

通過存取子 (Accessor)、更動子 (Mutator)、與型別轉換，便可在從 Model 實體上存取 Eloquent 屬性時轉換其值。舉例來說，我們可能想用 [Laravel 的加密功能](/docs/{{version}}/encryption) 來在資料庫內加密某個值，並在從 Eloquent Model 上存取該屬性時自動解密。或者，我們可能會想將某個值轉換為 JSON 字串來儲存進資料庫，然後在 Eloquent Model 上以陣列來存取。

<a name="accessors-and-mutators"></a>

## 存取子 (Accessor) 與更動子 (Mutator)

<a name="defining-an-accessor"></a>

### 定義存取子

存取子 (Accessor) 可在存取 Eloquent 屬性時轉換其值。若要定義存取子，可在 Model 中建立一個 `get{Attribute}Attribute` 方法。其中，`{Attribute}` 方法即為要存取的欄位之「Studly 命名法」大小寫的屬性名稱。

在這個例子中，我們會為 `first_name` 屬性定義存取子。在嘗試取得 `first_name` 屬性時，Eloquent 會自動呼叫該存取子：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * Get the user's first name.
         *
         * @param  string  $value
         * @return string
         */
        public function getFirstNameAttribute($value)
        {
            return ucfirst($value);
        }
    }

如上所見，該欄位的原始值會傳給該存取子，讓你可以進行操作並回傳值。若要存取存取子的值，只需要在 Model 實體上存取 `first_name` 屬性即可：

    use App\Models\User;
    
    $user = User::find(1);
    
    $firstName = $user->first_name;

存取子也不一定得要與單一屬性互動。你也可以使用存取子來回傳一個從現有屬性組合來的新的值：

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

> {tip} 若想讓過這些計算過的值包含在 Model 的陣列或 JSON 呈現上，則[需要將這些欄位附加上去](/docs/{{version}}/eloquent-serialization#appending-values-to-json)。

<a name="defining-a-mutator"></a>

### 定義更動子

更動子 (Mutator) 可在寫入 Eloquent 屬性時轉換其值。若要定義更動子，可在 Model 中建立一個 `set{Attribute}Attribute` 方法。其中，`{Attribute}` 方法即為要存取的欄位之「Studly 命名法」大小寫的屬性名稱。

我們來為 `first_name` 屬性定義更動子。該更動子會在嘗試於 Model 上設定 `first_name` 屬性值時被呼叫：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * Set the user's first name.
         *
         * @param  string  $value
         * @return void
         */
        public function setFirstNameAttribute($value)
        {
            $this->attributes['first_name'] = strtolower($value);
        }
    }

該更動子會接收目前正在設定的屬性的值，讓你可以更改其值並將更改過的值設定在 Eloquent Model 內部的 `$attributes` 屬性上。若要使用這個更動子，只需要在 Eloquent Model 上設定 `first_name` 屬性即可：

    use App\Models\User;
    
    $user = User::find(1);
    
    $user->first_name = 'Sally';

在此範例中，`setFirstNameAttribute` 函式會以 `Sally` 值呼叫。改更動子接著會在名字上套用 `strtolower` 韓式，並將其結果設定到內部的 `$attribuets` 陣列上。

<a name="attribute-casting"></a>

## 屬性型別轉換

屬性型別轉換提供了與存取子及更動子類似的方法。不過，你不需要手動在 Model 內定義任何額外的方法。通過 Model 上的 `$casts` 屬性，就可以方便地將屬性轉換為常見的資料型別。

`$casts` 屬性應為一個陣列，其索引鍵為要進行型別轉換的屬性名稱，而值則為要將該欄位進行型別轉換的型別。支援的轉換型別如下：

<div class="content-list" markdown="1">

- `array`
- `AsStringable::class`
- `boolean`
- `collection`
- `date`
- `datetime`
- `immutable_date`
- `immutable_datetime`
- `decimal:`<code>&lt;digits&gt;</code>
- `double`
- `encrypted`
- `encrypted:array`
- `encrypted:collection`
- `encrypted:object`
- `float`
- `integer`
- `object`
- `real`
- `string`
- `timestamp`

</div>

為了演示屬性型別轉換，我們來對 `is_admin` 屬性進行型別轉換。該欄位在資料庫中是以整數 (`0` 或 `1`) 來表示布林值的：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The attributes that should be cast.
         *
         * @var array
         */
        protected $casts = [
            'is_admin' => 'boolean',
        ];
    }

定義好型別轉換後，只要存取 `is_admin` 屬性，即使該屬性在資料庫中以整數來儲存，該屬性值總是會被轉換為布林值：

    $user = App\Models\User::find(1);
    
    if ($user->is_admin) {
        //
    }

若有需要在執行階段加上新的、臨時的型別轉換，則可使用 `mergeCasts` 方法。這些型別轉換定義會被加到所有在 Model 中已定義的型別轉換上：

    $user->mergeCasts([
        'is_admin' => 'integer',
        'options' => 'object',
    ]);

> {note} `null` 的屬性將不會進行型別轉換。此外，定義型別轉換 (或屬性) 時，也不應有相同名稱的關聯。

<a name="stringable-casting"></a>

#### Stringable 的型別轉換

可以使用 `Illuminate\Database\Eloquent\Casts\AsStringable` 型別轉換類別來講 Model 屬性轉換為 [Fluent `Illuminate\Support\Stringable` 物件] (/docs/{{version}}/helpers#fluent-strings-method-list):

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Casts\AsStringable;
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The attributes that should be cast.
         *
         * @var array
         */
        protected $casts = [
            'directory' => AsStringable::class,
        ];
    }

<a name="array-and-json-casting"></a>

### 陣列與 JSON 的型別轉換

`array` 型別轉換特別適合搭配宜 JSON 序列化保存的欄位。舉例來說，說資料庫內有個包含了序列化 JSON 的 `JSON` 或 `TEXT` 欄位型別，則加上 `array` 型別轉換，就可以在從 Eloquent Model 上存取該欄位時自動將屬性反串聯化為 PHP 陣列：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The attributes that should be cast.
         *
         * @var array
         */
        protected $casts = [
            'options' => 'array',
        ];
    }

定義好型別轉換後，存取 `options` 屬性時就會自動從 JSON 反序列化為 PHP 陣列。為 `options` 賦值時，提供的陣列也會被序列化回 JSON 以進行儲存：

    use App\Models\User;
    
    $user = User::find(1);
    
    $options = $user->options;
    
    $options['key'] = 'value';
    
    $user->options = $options;
    
    $user->save();

若要使用更精簡的方法來更新 JSON 屬性中的單一欄位，可以在呼叫 `update` 方法時使用 `->` 運算子：

    $user = User::find(1);
    
    $user->update(['options->key' => 'value']);

<a name="array-object-and-collection-casting"></a>

#### 陣列物件與 Collection 的型別轉換

雖然使用標準的 `array` 型別轉換對於大多數專案就夠用了，但 `array` 也有一些缺點。由於 `array` 型別轉換回傳的是原生型別，因此我們無法直接更改陣列的元素。舉例來說，下列程式碼會觸發 PHP 錯誤：

    $user = User::find(1);
    
    $user->options['key'] = $value;

為了解決這個問題，Laravel 提供了一個 `AsArrayObject` 型別轉換，可用來將 JSON 屬性轉換為 [ArrayObject](https://www.php.net/manual/en/class.arrayobject.php) 類別。改功能使用 Laravel 的[自訂型別轉換](#custom-casts)實作，可讓 Laravel 進行智慧快取並變換更改過的物件，也能讓個別元素在修改時不觸發 PHP 錯誤。若要使用 `AsArrayObject` 型別轉換，只需要將其指派給屬性即可：

    use Illuminate\Database\Eloquent\Casts\AsArrayObject;
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => AsArrayObject::class,
    ];

Laravel 還提供了一個類似的 `AsCollection` 型別轉換，可將 JSON 屬性轉換為 Laravel 的 [Collection](/docs/{{version}}/collections) 實體：

    use Illuminate\Database\Eloquent\Casts\AsCollection;
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => AsCollection::class,
    ];

<a name="date-casting"></a>

### 日期的型別轉換

預設情況下，Eloquent 會將 `created_at` 與 `updated_at` 欄位轉換為 [Carbon](https://github.com/briannesbitt/Carbon) 實體。Carbon 繼承自 PHP 的 `DateTime` 類別，並提供了各種實用方法。可以通過往 Model 的 `$casts` 屬性陣列內定義額外的日期型別轉換來給其他日期屬性進行轉換。通常來說，日期應使用 `datetime` 或 `immutable_datetime` 型別轉換類型。

在定義 `date` 或 `datetime` 型別轉換時，也可以指定日期的格式。該格式會在 [Model 被序列化成陣列或 JSON](/docs/{{version}}/eloquent-serialization) 時使用：

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];

在將欄位轉換為日期時，可以將相應的 Model 屬性值設為 UNIX 時戳、日期字串 (`Y-m-d`)、日期與時間字串、或是 `DateTime` / `Carbon` 實體。日期的值會被正確地轉換並保存在資料庫中。

在 Model 中定義 `serializeDate` 方法，即可為 Model 中所有的日期定義預設的序列化方法。改方法並不會影響日期儲存到資料庫時的格式化方法：

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }

若要指定用來將 Model 日期保存在資料庫時使用的格式，可在 Model 中定義 `$dateFormat` 屬性：

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'U';

<a name="date-casting-and-timezones"></a>

#### 日期型別轉換、序列化、與時區

預設情況下，不論專案的 `timezone` 設定選項設為哪個時區，`date` 與 `datetime` 都會將日期序列化為 UTC 的 ISO-8601 日期字串 (`1986-05-28T21:05:54.000000Z`)。我們強烈建議你保持使用這個序列化格式，也建議你只將專案的 `timezone` 設定選項設為預設的 `UTC`，並讓專案中以 UTC 來儲存所有的日期時間。在專案中保持一致地使用 UTC 時區，可為其他 PHP 與 JavaScript 的日期操作函示庫提供最大的互用性。

若有在 `date` 或 `datetime` 型別轉換內提供自訂格式，如 `datetime:Y-m-d H:i:s`，則在進行日期序列化時，會使用 Carbon 實體內部的時區。一般來說，這個時區就是專案的 `timezone` 設定選項。

<a name="enum-casting"></a>

### Enum 的型別轉換

> {note} Enum 型別轉換只可在 PHP 8.1 以上使用。

Eloquent 也能讓我們將屬性值轉換為 PHP Enum。為此，可在 Model 中的 `$casts` 屬性陣列中指定要型別轉換的屬性與 Enum：

    use App\Enums\ServerStatus;
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => ServerStatus::class,
    ];

定義好 Model 的型別轉換後，每次存取該屬性時就會自動轉換對 Enum 進行轉換：

    if ($server->status == ServerStatus::provisioned) {
        $server->status = ServerStatus::ready;
    
        $server->save();
    }

<a name="encrypted-casting"></a>

### 加密的型別轉換

`encrypted` 型別轉換會通過 Laravel 的內建[加密](/docs/{{version}}/encryption)功能來加密 Model 的屬性值。此外，還有 `encrypted:array`, `encrypted:collection`, `encrypted:object`, `AsEncryptedArrayObject`, 與 `AsEncryptedCollection` 等型別轉換，這些型別轉換都與其未加密的版本一樣以相同方式運作。不過，可想而知，底層的值會先加密才保存進資料庫。

由於加密後的文字長度時無法預測的，且通常比明文的版本還要長，因此請確保其資料庫欄位為 `TEXT` 型別或更大的型別。此外，由於在資料庫中值都是經過加密的，因此你也沒辦法查詢或搜尋加密過的屬性質。

<a name="query-time-casting"></a>

### 查詢時的型別轉換

有時候我們可能會需要在執行查詢時套用型別轉換，例如從資料表中選擇原始資料時。舉例來說，假設有下列查詢：

    use App\Models\Post;
    use App\Models\User;
    
    $users = User::select([
        'users.*',
        'last_posted_at' => Post::selectRaw('MAX(created_at)')
                ->whereColumn('user_id', 'users.id')
    ])->get();

查詢結果中的 `last_posted_at` 屬性會是字串。如果我們可以將 `datetime` 型別轉換在執行查詢時套用到這個屬性上就好了。好佳在，我們可以通過使用 `withCasts` 方法來達成：

    $users = User::select([
        'users.*',
        'last_posted_at' => Post::selectRaw('MAX(created_at)')
                ->whereColumn('user_id', 'users.id')
    ])->withCasts([
        'last_posted_at' => 'datetime'
    ])->get();

<a name="custom-casts"></a>

## 自訂型別轉換

Laravel 中有各式內建的實用型別轉換類型。不過，有時候我們也會需要定義自己的型別轉換類型。我們可以通過實作 `CastsAttributes` 介面來自訂型別轉換類型。

實作了這個介面的類別必須定義一組 `get` 與 `set` 方法。`get` 方法用於將儲存在資料庫內的原始值轉換為型別值；`set` 方法則負責將型別值轉換為可儲存在資料庫內的原始值。在這裡，我們將重新實作一個內建的 `json` 型別轉換類型為例：

    <?php
    
    namespace App\Casts;
    
    use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
    
    class Json implements CastsAttributes
    {
        /**
         * Cast the given value.
         *
         * @param  \Illuminate\Database\Eloquent\Model  $model
         * @param  string  $key
         * @param  mixed  $value
         * @param  array  $attributes
         * @return array
         */
        public function get($model, $key, $value, $attributes)
        {
            return json_decode($value, true);
        }
    
        /**
         * Prepare the given value for storage.
         *
         * @param  \Illuminate\Database\Eloquent\Model  $model
         * @param  string  $key
         * @param  array  $value
         * @param  array  $attributes
         * @return string
         */
        public function set($model, $key, $value, $attributes)
        {
            return json_encode($value);
        }
    }

定義好自訂的型別轉換類型後，就可以使用類別名稱將其附加到 Model 屬性內：

    <?php
    
    namespace App\Models;
    
    use App\Casts\Json;
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The attributes that should be cast.
         *
         * @var array
         */
        protected $casts = [
            'options' => Json::class,
        ];
    }

<a name="value-object-casting"></a>

### 數值物件的型別轉換

進行型別轉換時，我們不只可以將值轉換為 PHP 的原生型別，我們還可以將值轉換為物件。定義這種將值轉換為物件的自訂型別轉換就跟轉換成原生型別類似。不過，在這種型別轉換類別中的 `set` 方法應回傳一組在 Model 上用於設定原始、可儲存值的索引鍵/值配對。

在這裡，我們以將多個 Model 值轉換到單一 `Address` 數值物件的自訂型別轉換類別為例。我們假設 `Address` 值有兩個公用屬性：`lineOne` 與 `lineTwo`：

    <?php
    
    namespace App\Casts;
    
    use App\Models\Address as AddressModel;
    use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
    use InvalidArgumentException;
    
    class Address implements CastsAttributes
    {
        /**
         * Cast the given value.
         *
         * @param  \Illuminate\Database\Eloquent\Model  $model
         * @param  string  $key
         * @param  mixed  $value
         * @param  array  $attributes
         * @return \App\Models\Address
         */
        public function get($model, $key, $value, $attributes)
        {
            return new AddressModel(
                $attributes['address_line_one'],
                $attributes['address_line_two']
            );
        }
    
        /**
         * Prepare the given value for storage.
         *
         * @param  \Illuminate\Database\Eloquent\Model  $model
         * @param  string  $key
         * @param  \App\Models\Address  $value
         * @param  array  $attributes
         * @return array
         */
        public function set($model, $key, $value, $attributes)
        {
            if (! $value instanceof AddressModel) {
                throw new InvalidArgumentException('The given value is not an Address instance.');
            }
    
            return [
                'address_line_one' => $value->lineOne,
                'address_line_two' => $value->lineTwo,
            ];
        }
    }

對數值物件進行型別轉換時，對數值物件進行的所有更改都會在 Model 儲存前同步回 Model 上：

    use App\Models\User;
    
    $user = User::find(1);
    
    $user->address->lineOne = 'Updated Address Value';
    
    $user->save();

> {tip} 若有打算要將包含數值物件的 Eloquent Model 序列化為 JSON 或陣列，則該數值物件應實作 `Illuminate\Contracts\Support\Arrayable` 與 `JsonSerializable` 介面。

<a name="array-json-serialization"></a>

### Array / JSON 的序列化

當 Eloquent Model 通過 `toArray` 與 `toJson` 轉換為陣列或 JSON 時，只要自訂的型別轉換數值物件有實作 `Illuminate\Contracts\Support\Arrayable` 與 `JsonSerializable` 介面，該數值物件也會一併被序列化。不過，若我們使用的數值物件是來自第三方套件的，那我們可能就沒辦法提供這些負責序列化介面。

因此，我們可以指定讓自訂型別轉換類別來負責處理數值物件的序列化。為此，自訂型別轉換類別應實作 `Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes` 介面。實作這個介面，就代表該類別中應包含一個 `serialize` 方法，該方法應回傳數值物件的序列化形式：

    /**
     * Get the serialized representation of the value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function serialize($model, string $key, $value, array $attributes)
    {
        return (string) $value;
    }

<a name="inbound-casting"></a>

### 輸入型別轉換

我們偶爾會需要一種型別轉換：只在對 Model 賦值時轉換值，存取時則不進行任何轉換。純輸入的型別轉換最常見的例子就是「雜湊」型別轉換。純輸入的型別轉換應實作 `CastsInboundAttributes` 介面，該介面只要求要定義 `set` 方法。

    <?php
    
    namespace App\Casts;
    
    use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
    
    class Hash implements CastsInboundAttributes
    {
        /**
         * The hashing algorithm.
         *
         * @var string
         */
        protected $algorithm;
    
        /**
         * Create a new cast class instance.
         *
         * @param  string|null  $algorithm
         * @return void
         */
        public function __construct($algorithm = null)
        {
            $this->algorithm = $algorithm;
        }
    
        /**
         * Prepare the given value for storage.
         *
         * @param  \Illuminate\Database\Eloquent\Model  $model
         * @param  string  $key
         * @param  array  $value
         * @param  array  $attributes
         * @return string
         */
        public function set($model, $key, $value, $attributes)
        {
            return is_null($this->algorithm)
                        ? bcrypt($value)
                        : hash($this->algorithm, $value);
        }
    }

<a name="cast-parameters"></a>

### 型別轉換的參數

在 Model 上設定自訂型別轉換時，可以指定型別轉換的參數，請使用 `:` 字元來區分型別轉換類別名稱與參數，並使用逗號來區分多個參數。這些參數會傳給型別轉換類別的建構函式：

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];

<a name="castables"></a>

### Castable

我們可以讓專案中的數值物件自己定義自己的自訂型別轉換類別。與在 Model 中設定自訂的型別轉換類別相比，我們可以設定實作了 `Illuminate\Contracts\Database\Eloquent\Castable` 介面的數值物件類別：

    use App\Models\Address;
    
    protected $casts = [
        'address' => Address::class,
    ];

實作了 `Castable` 介面的物件必須定義 `castUsing` 方法。該方法則應回傳用於對 `Castable` 類別進行型別轉換的自訂型別轉換類別名稱：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Contracts\Database\Eloquent\Castable;
    use App\Casts\Address as AddressCast;
    
    class Address implements Castable
    {
        /**
         * Get the name of the caster class to use when casting from / to this cast target.
         *
         * @param  array  $arguments
         * @return string
         */
        public static function castUsing(array $arguments)
        {
            return AddressCast::class;
        }
    }

即使是使用 `Castable` 類別，也可以在 `$casts` 定義中提供引數。這些引數會被傳給 `castUsing` 方法：

    use App\Models\Address;
    
    protected $casts = [
        'address' => Address::class.':argument',
    ];

<a name="anonymous-cast-classes"></a>

#### Castable 與匿名型別轉換類別

通過將「Castable」與 PHP 的[匿名函式](https://www.php.net/manual/en/language.oop5.anonymous.php)搭配使用，我們就能在單一 Castable 物件內定義數值物件與其型別轉換邏輯。為此，請在數值物件的 `castUsing` 方法內回傳一個匿名類別。這個匿名類別應實作 `CastsAttributes` 介面：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Contracts\Database\Eloquent\Castable;
    use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
    
    class Address implements Castable
    {
        // ...
    
        /**
         * Get the caster class to use when casting from / to this cast target.
         *
         * @param  array  $arguments
         * @return object|string
         */
        public static function castUsing(array $arguments)
        {
            return new class implements CastsAttributes
            {
                public function get($model, $key, $value, $attributes)
                {
                    return new Address(
                        $attributes['address_line_one'],
                        $attributes['address_line_two']
                    );
                }
    
                public function set($model, $key, $value, $attributes)
                {
                    return [
                        'address_line_one' => $value->lineOne,
                        'address_line_two' => $value->lineTwo,
                    ];
                }
            };
        }
    }
