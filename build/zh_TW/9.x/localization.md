---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/95/en-zhtw'
updatedAt: '2023-02-11T12:59:00Z'
contributors: {  }
progress: 56.45
---

# 本土化

- [簡介](#introduction)
  - [設定語系](#configuring-the-locale)
  - [複數化語言](#pluralization-language)
  
- [定義翻譯字串](#defining-translation-strings)
  - [使用短的索引鍵](#using-short-keys)
  - [使用翻譯字串作為索引鍵](#using-translation-strings-as-keys)
  
- [取得翻譯字串](#retrieving-translation-strings)
  - [在翻譯字串中取代參數](#replacing-parameters-in-translation-strings)
  - [單複數的處理](#pluralization)
  
- [覆寫套件的語系檔](#overriding-package-language-files)

<a name="introduction"></a>

## 簡介

Laravel 的本土化 (Localization, L10N) 功能可讓我們方便地在多個語系中取得字串，讓我們的程式能更簡單地支援多種語言。

Laravel 提供了兩種管理翻譯字串的方法。第一種方式，就是將翻譯字串保存在 `lang` 目錄內。在這個目錄中，可以為程式要支援的每一個語言都建立一個子目錄。這也是 Laravel 管理如表單驗證錯誤訊息等內建功能翻譯字串的方式：

    /lang
        /en
            messages.php
        /es
            messages.php
第二種方式，是將翻譯字串定義在 `lang` 目錄下的 JSON 檔中。用這種方式時，要支援的每個語言在該目錄中都有一個對應的 JSON 檔。若專案沒有太多要翻譯的字串的話，建議使用這種做法：

    /lang
        en.json
        es.json
在本文件中，我們稍候會討論各種管理翻譯字串的方法。

<a name="configuring-the-locale"></a>

### 設定語系

專案的預設語系設定在 `config/app.php` 設定檔的 `locale` 設定選項中。可以自由修改這個值為合適的設定。

也可以在執行階段使用 `App` Facade 提供的 `setLocale` 方法來為單一 HTTP Request 設定預設語系：

    use Illuminate\Support\Facades\App;
    
    Route::get('/greeting/{locale}', function ($locale) {
        if (! in_array($locale, ['en', 'es', 'fr'])) {
            abort(400);
        }
    
        App::setLocale($locale);
    
        //
    });
也可以設定一個「遞補語系」，當目前語系中找不到給定的翻譯字串時，就會使用遞補語系。遞補語系跟預設語系一樣，在 `config/app.php` 設定檔中設定：

    'fallback_locale' => 'en',
<a name="determining-the-current-locale"></a>

#### 判斷目前的語系

可使用 `App` Facade  上的 `currentLocale` 與  `isLocale` 方法來判斷目前的語系，或是確認目前語系是否為給定值：

    use Illuminate\Support\Facades\App;
    
    $locale = App::currentLocale();
    
    if (App::isLocale('en')) {
        //
    }
<a name="pluralization-language"></a>

### 複數化 (Pluralization) 語言

在 Laravel 中，Eloquent 等地方會使用「複數化程式 (Pluralizer)」來將單數字串轉為複數字串。我們可以讓這個複數化程式使用英文以外的語言。若要讓複數化程式使用英文以外的語言，請在專案的其中一個 Service Provider 中 `boot` 方法內叫用 `useLanguage` 方法。複數化程式目前支援的語言有：法文 `french`、書面挪威語 `norwegian-bokmal`、葡萄牙文 `portuguese`、西班牙文 `spanish`、與土耳其文 `turkish`：

    use Illuminate\Support\Pluralizer;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Pluralizer::useLanguage('spanish');     
    
        // ...     
    }
> [!WARNING]  
> 若自訂了複數化程式的語言，則請顯式定義 Eloquent Model 的[資料表名稱](/docs/{{version}}/eloquent#table-names)。

<a name="defining-translation-strings"></a>

## 定義翻譯字串

<a name="using-short-keys"></a>

### 使用短的索引鍵

一般來說，翻譯字串都保存在 `lang` 目錄內。在這個目錄中，專案要支援的每一個語言都應有一個子目錄。這也是 Laravel 管理如表單驗證錯誤訊息等內建功能翻譯字串的方式：

    /lang
        /en
            messages.php
        /es
            messages.php
所有的語系檔都回傳一個有字串索引鍵的陣列。如：

    <?php
    
    // lang/en/messages.php
    
    return [
        'welcome' => 'Welcome to our application!',
    ];
> [!WARNING]  
> 對於會因國家或地區而有所區別的語系，請依照 ISO 15897 命名語系檔目錄。舉例來說，英式英語應使用「en_GB」而非「en-gb」。

<a name="using-translation-strings-as-keys"></a>

### 使用翻譯字串作為索引鍵

對於有大量可翻譯字串的專案，若將每個字串都定義為「短索引鍵」，在 View 中參照這些索引鍵的時候很容易造成混謠，且要為每個翻譯字串都想一組索引鍵也很麻煩。

因此，Laravel 提供了使用「預設」翻譯作為翻譯字串索引鍵的支援。使用翻譯字串作為索引鍵的語系檔保存在 `lang` 目錄下的 JSON 檔中。舉例來說，若我們的專案有西班牙語翻譯，就可建立一個像這樣的 `lang/es.json` 檔：

```json
{
    "I love programming.": "Me encanta programar."
}
```
#### 索引鍵 / 檔案的衝突

請不要定義與其他語系檔名衝突的翻譯字串。舉例來說，為荷蘭語「NL」翻譯 `__('Action')` 時，若有 `nl/action.php` 檔案但 `nl.json` 檔不存在時，翻譯程式就會回傳 `nl/action.php` 的內容。

<a name="retrieving-translation-strings"></a>

## 取得翻譯字串

可以使用 `__` 輔助函式來從語系檔中取得翻譯字串。若使用「短索引鍵」來定義翻譯字串的話，請使用「點 (.)」標記法來傳入包含該索引鍵的檔案、以及該索引鍵。舉例來說，我們來從 `lang/en/messages.php` 語系檔中取得 `welcome` 翻譯字串：

    echo __('messages.welcome');
若指定的翻譯字串不存在時，`__` 函式會回傳給定的字串索引值。因此，在上述範例中，若 `messages.welcome` 索引鍵不存在，`__` 函式會回傳 `messages.welcome`。

若使用[預設翻譯字串作為翻譯索引鍵](#using-translation-strings-as-keys)，則請傳入預設翻譯字串給 `__` 函式：

    echo __('I love programming.');
同樣地，若翻譯字串不存在，`__` 函式會回傳給定的翻譯字串索引鍵。

若使用[Blade 樣板引擎](/docs/{{version}}/blade)，可使用 `{{ }}` Echo 語法來顯示翻譯字串：

    {{ __('messages.welcome') }}
<a name="replacing-parameters-in-translation-strings"></a>

### 在翻譯字串中取代參數

若有需要的話，也可以在翻譯字串中定義^[預留位置](Placeholder)。所有的預留位置都以 `:` 字元作前置詞。舉例來說，我們可以定義一個有預留位置的歡迎訊息：

    'welcome' => 'Welcome, :name',
取得翻譯字串時若要取代這個預留位置，可傳入一組取代用陣列作為 `__` 函式的第二個引數：

    echo __('messages.welcome', ['name' => 'dayle']);
若預留位置只包含大寫字母，或是首字母大寫，則翻譯字串值也會依照相應的方法調整大小寫：

    'welcome' => 'Welcome, :NAME', // Welcome, DAYLE
    'goodbye' => 'Goodbye, :Name', // Goodbye, Dayle
<a name="object-replacement-formatting"></a>

#### 物件取代格式

若在預留位置上嘗試提供物件時，則會呼叫該物件的 `__toString` 方法。[`__toString`](https://www.php.net/manual/en/language.oop5.magic.php#object.tostring) 方法是 PHP 的其中一個「魔法方法」。不過，有的時候我們可能無法控制給定類別的 `__toString` 方法，如：來自第三方函式庫的類別。

因此，在 Laravel 中，我們可以針對特定類型的物件註冊自訂的格式處理常式。若要自定格式處理常式，請呼叫 Translator 的 `stringable` 方法。`stringable` 方法接受一個閉包，請在該閉包的型別提示 (Type-hint) 中指定其負責格式化的物件。一般情況下來說，應在專案的 `AppServiceProvider` 類別中 `boot` 方法內呼叫這個 `stringable` 方法：

    use Illuminate\Support\Facades\Lang;
    use Money\Money;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Lang::stringable(function (Money $money) {
            return $money->formatTo('en_GB');
        });
    }
<a name="pluralization"></a>

### 複數化

複數是一個很複雜的問題，因為不同的語言有各種不同的複雜複數規則。不過，Laravel 可以讓你依據定義的複數化規則來有不同的翻譯字串。使用 `|` 字串就可以區分字串的單數與複數形：

    'apples' => 'There is one apple|There are many apples',
當然，使用[翻譯字串作為索引鍵](#using-translation-strings-as-keys)時也支援複數化：

```json
{
    "There is one apple|There are many apples": "Hay una manzana|Hay muchas manzanas"
}
```
也可以為不同的值指定不同翻譯字串以建立更複雜的複數化規則：

    'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
定義有複數化選項的翻譯字串後，可使用 `trans_choice` 函式來取得給定「數目」的字串。在這個例子中，由於給定數目大於 1，所以會回傳該翻譯字串的複數形：

    echo trans_choice('messages.apples', 10);
也可以在複數化字串中定義預留位置屬性。可以在 `trans_choice` 的第三個引數上傳入陣列來取代預留位置：

    'minutes_ago' => '{1} :value minute ago|[2,*] :value minutes ago',
    
    echo trans_choice('time.minutes_ago', 5, ['value' => 5]);
若想顯示傳入 `trans_choice` 函式的整數值，可使用內建的 `:count` 預留位置：

    'apples' => '{0} There are none|{1} There is one|[2,*] There are :count',
<a name="overriding-package-language-files"></a>

## 覆寫套件的語系檔

有的套件中包含了套件自己的語系檔。除了直接修改套件的檔案來更改語系檔內容外，還可以在 `lang/vendor/{package}/{locale}` 目錄內放置檔案來覆寫這些語系檔。

舉例來說，若想為 `skyrim/hearthfire` 套件覆寫的 `messages.php` 內的英文翻譯，我們可以在 `lang/vendor/hearthfire/en/messages.php` 中放置一個語系檔。在這個的檔案內，我們只需要定義要覆寫的翻譯字串即可。未覆寫的翻譯字串會從該套件的原始語系檔中載入。
