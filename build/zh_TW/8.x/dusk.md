# Laravel Dusk

- [簡介](#introduction)
- [安裝](#installation)
    - [管理 ChromeDriver 的安裝](#managing-chromedriver-installations)
    - [使用其他瀏覽器](#using-other-browsers)
- [入門](#getting-started)
    - [產生測試](#generating-tests)
    - [資料庫 Migration](#migrations)
    - [執行測試](#running-tests)
    - [處理環境](#environment-handling)
- [「瀏覽器」基礎](#browser-basics)
    - [建立瀏覽器](#creating-browsers)
    - [導航](#navigation)
    - [縮放瀏覽器視窗](#resizing-browser-windows)
    - [瀏覽器巨集](#browser-macros)
    - [驗證](#authentication)
    - [Cookies](#cookies)
    - [執行 JavaScript](#executing-javascript)
    - [截圖](#taking-a-screenshot)
    - [將主控台輸出儲存到磁碟上](#storing-console-output-to-disk)
    - [將網頁原始碼保存到磁碟上](#storing-page-source-to-disk)
- [與元素互動](#interacting-with-elements)
    - [Dusk 選擇器](#dusk-selectors)
    - [文字、值、與屬性](#text-values-and-attributes)
    - [與表單互動](#interacting-with-forms)
    - [附加檔案](#attaching-files)
    - [按下按鈕](#pressing-buttons)
    - [點擊連結](#clicking-links)
    - [使用鍵盤](#using-the-keyboard)
    - [使用滑鼠](#using-the-mouse)
    - [JavaScript 對話方塊](#javascript-dialogs)
    - [區域性選擇器](#scoping-selectors)
    - [等待元素](#waiting-for-elements)
    - [滾動以顯示將某個元素](#scrolling-an-element-into-view)
- [可用的 Assertion](#available-assertions)
- [頁面 - Page](#pages)
    - [產生 Page](#generating-pages)
    - [設定 Page](#configuring-pages)
    - [瀏覽到 Page](#navigating-to-pages)
    - [選擇器簡寫](#shorthand-selectors)
    - [Page 方法](#page-methods)
- [元件 - Component](#components)
    - [產生 Component](#generating-components)
    - [使用 Component](#using-components)
- [持續整合](#continuous-integration)
    - [Heroku CI](#running-tests-on-heroku-ci)
    - [Travis CI](#running-tests-on-travis-ci)
    - [GitHub Actions](#running-tests-on-github-actions)

<a name="introduction"></a>
## 簡介

Laravel Dusk provides an expressive, easy-to-use browser automation and
testing API. By default, Dusk does not require you to install JDK or
Selenium on your local computer. Instead, Dusk uses a standalone
[ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/home)
installation. However, you are free to utilize any other Selenium compatible
driver you wish.

<a name="installation"></a>
## 安裝

To get started, you should add the `laravel/dusk` Composer dependency to
your project:

    composer require --dev laravel/dusk

> {note} 若要手動註冊 Dusk 的 Service Provider，請**不要**在正式環境內加上該 Provider，因為這麼做會導致應用程式內可登入任意使用者。

安裝好 Dusk 套件後，請執行 `dusk:install` Artisan 指令。`dusk:install` 指令會建立
`tests/Browser` 資料夾，以及一個 Dusk 範例測試：

    php artisan dusk:install

接著，請在應用程式的 `.env` 檔內設定 `APP_URL` 環境變數。該變數應符合要在瀏覽器內存取應用程式的 URL。

> {tip} 若使用 [Laravel Sail](/docs/{{version}}/sail) 來管理本機開發環境，也請一併參考 Sail 說明文件中有關[設定與執行 Dusk 測試](/docs/{{version}}/sail#laravel-dusk)的部分。

<a name="managing-chromedriver-installations"></a>
### 管理 ChromeDriver 安裝

若想安裝與 Laravel Dusk 附帶的 ChromeDriver 不同的版本，可使用 `dusk:chrome-driver` 指令：

    # 為目前作業系統安裝最新版的 ChromeDriver…
    php artisan dusk:chrome-driver

    # 為目前作業系統安裝指定版本的 ChromeDriver…
    php artisan dusk:chrome-driver 86

    # 為所有支援的作業系統安裝給定版本的 ChromeDriver
    php artisan dusk:chrome-driver --all

    # 為目前作業系統安裝能偵測到的 Chrome / Chromiums 版本的 ChromeDriver…
    php artisan dusk:chrome-driver --detect

> {note} 要使用 Dusk，`chromedriver` 二進位執行檔必須可執行。若無法執行 Dusk，請通過下列指令確保該二進位執行檔可執行：`chmod -R 0755 vendor/laravel/dusk/bin/`。

<a name="using-other-browsers"></a>
### 使用其他瀏覽器

By default, Dusk uses Google Chrome and a standalone
[ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/home)
installation to run your browser tests. However, you may start your own
Selenium server and run your tests against any browser you wish.

要開始使用其他瀏覽器，請開啟 `tests/DuskTestCase.php` 檔。該檔案內為應用程式內所有 Dusk
測試的基礎測試類別。請在該檔案內移除 `startChromeDriver` 方法的呼叫。這樣一來可以讓 Dusk 不要自動開啟
ChromeDriver：

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        // static::startChromeDriver();
    }

接著，可以修改 `driver` 方法來連先到所選的 URL 與連結埠。另外，也可以修改應傳給 WebDriver 的「Desired
Capabilities (所需功能)」：

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        return RemoteWebDriver::create(
            'http://localhost:4444/wd/hub', DesiredCapabilities::phantomjs()
        );
    }

<a name="getting-started"></a>
## 入門

<a name="generating-tests"></a>
### 產生測試

若要產生 Dusk 測試，請使用 `dusk:make` Artisan 指令。產生的測試將放置於 `tests/Browser` 目錄內：

    php artisan dusk:make LoginTest

<a name="migrations"></a>
### Database Migrations

我們所撰寫的大部分的測試所互動的頁面都會從應用程式的資料庫內取得資料。不過，Dusk 測試不應使用 `RefreshDatabase`
Trait。`RefreshDatabase` Trait 所仰賴的資料庫 Transaction 無法在跨 HTTP 請求間使用。請改用
`DatabaseMigrations` Trait 來代替，該 Trait 會為每個測試重新 Migrate 資料庫：

    <?php

    namespace Tests\Browser;

    use App\Models\User;
    use Illuminate\Foundation\Testing\DatabaseMigrations;
    use Laravel\Dusk\Chrome;
    use Tests\DuskTestCase;

    class ExampleTest extends DuskTestCase
    {
        use DatabaseMigrations;
    }

> {note} 在記憶體內的 SQLite 資料庫無法在執行 Dusk 測試時使用。由於瀏覽器會在自己的處理程序內執行，因此將無法存取其他處理程序中在記憶體內的資料庫。

<a name="running-tests"></a>
### 執行測試

若要執行瀏覽器測試，請執行 `dusk` Artisan 指令：

    php artisan dusk

若在上次執行 `dusk` 指令時有測試失敗了，則可以通過 `dusk:fails` 指令來先重新執行失敗的測試以節省時間：

    php artisan dusk:fails

`dusk` 指令接受所有一般 PHPUnit
測試執行程式所接受的引數，如可以只執行特定[群組](https://phpunit.de/manual/current/en/appendixes.annotations.html#appendixes.annotations.group)內的測試：

    php artisan dusk --group=foo

> {tip} 若使用 [Laravel Sail](/docs/{{version}}/sail) 來管理本機開發環境，請參考 Sail 說明文件中有關[設定與執行 Dusk 測試](/docs/{{version}}/sail#laravel-dusk)的部分。

<a name="manually-starting-chromedriver"></a>
#### 手動啟動 ChromeDriver

預設情況下，Dusk 會自動嘗試開啟 ChromeDriver。若你所使用的系統無法自動開啟 ChromeDriver，則可以在執行 `dusk`
指令前手動啟動 ChromeDriver。若想手動啟動 ChromeDriver，則應先在 `test/DuskTestCase.php`
檔中將下列部分註解掉：

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        // static::startChromeDriver();
    }

此外，若在 9515 連結埠以外的其他連結埠上開啟 ChromeDriver，則應在相同類別內修改 `driver` 方法以修改為相應的連結埠：

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()
        );
    }

<a name="environment-handling"></a>
### 處理環境

若要在執行測試時強制讓 Dusk 使用自己的環境檔，請在專案根目錄下建立一個 `.env.dusk.{environment}` 檔案。舉例來說，若會在
`local` 環境下執行 `dusk`，請建立 `.env.dusk.local` 檔案。

執行測試時，Dusk 會備份 `.env` 檔，並將 Dusk 環境檔重新命名為 `.env`。測試完成後，會恢復原本的 `.env` 檔。

<a name="browser-basics"></a>
## 「瀏覽器」基礎

<a name="creating-browsers"></a>
### 建立瀏覽器

要開始使用瀏覽器，我們先來建立一個用來驗證能否登入應用程式的測試。產生測試後，我們就可以修改該測試、前往登入頁、輸入帳號密碼、並點擊「登入」按鈕。要建立瀏覽器實體，可在
Dusk 測試內呼叫 `browser` 方法：

    <?php

    namespace Tests\Browser;

    use App\Models\User;
    use Illuminate\Foundation\Testing\DatabaseMigrations;
    use Laravel\Dusk\Chrome;
    use Tests\DuskTestCase;

    class ExampleTest extends DuskTestCase
    {
        use DatabaseMigrations;

        /**
         * A basic browser test example.
         *
         * @return void
         */
        public function test_basic_example()
        {
            $user = User::factory()->create([
                'email' => 'taylor@laravel.com',
            ]);

            $this->browse(function ($browser) use ($user) {
                $browser->visit('/login')
                        ->type('email', $user->email)
                        ->type('password', 'password')
                        ->press('Login')
                        ->assertPathIs('/home');
            });
        }
    }

如上所見，`browser` 方法接受一個閉包。Dusk 會自動將瀏覽器實體傳入該閉包內，而該實體也是用來與應用程式互動以及用來進行 Assertion
的主要物件。

<a name="creating-multiple-browsers"></a>
#### 建立多個瀏覽器

有時候，我們需要建立多個瀏覽器來正確地進行測試。舉例來說，在測試與 WebSocket
互動的聊天畫面時可能會需要多個瀏覽器。若要建立多個瀏覽器，只需要將多個瀏覽器引數加到提供給 `browser` 方法的閉包上即可：

    $this->browse(function ($first, $second) {
        $first->loginAs(User::find(1))
              ->visit('/home')
              ->waitForText('Message');

        $second->loginAs(User::find(2))
               ->visit('/home')
               ->waitForText('Message')
               ->type('message', 'Hey Taylor')
               ->press('Send');

        $first->waitForText('Hey Taylor')
              ->assertSee('Jeffrey Way');
    });

<a name="navigation"></a>
### 導航

`visit` 方法可用來在應用程式內導航到特定的 URI 上：

    $browser->visit('/login');

可以使用 `visitRoute` 方法來導航到[命名路由](/docs/{{version}}/routing#named-routes)：

    $browser->visitRoute('login');

可以使用 `back` 與 `forward` 方法來導航到「上一頁」與「下一頁」：

    $browser->back();

    $browser->forward();

可以使用 `refresh` 方法來重新整理頁面：

    $browser->refresh();

<a name="resizing-browser-windows"></a>
### 縮放瀏覽器視窗

可以使用 `resize` 方法來調整瀏覽器視窗的大小：

    $browser->resize(1920, 1080);

`maximize` 方法可用來最大化瀏覽器視窗：

    $browser->maximize();

`fitContent` 方法會將瀏覽器視窗縮放到符合其內容的大小：

    $browser->fitContent();

當測試失敗時，Dusk 會自動縮放瀏覽器視窗來符合其內容，以進行截圖。可以通過在測試內呼叫 `disableFitOnFailure`
方法來禁用此功能：

    $browser->disableFitOnFailure();

可以使用 `move` 方法來將瀏覽器視窗移動到畫面上的不同位置：

    $browser->move($x = 100, $y = 100);

<a name="browser-macros"></a>
### 瀏覽器 Macro

若想定義可在各個測試內重複使用的自定瀏覽器方法，可使用 `Browser` 類別上的 `macro` 方法。通常來說，該方法應在某個 [Service
Provider](/docs/{{version}}/providers) 的 `boot` 方法內呼叫：

    <?php

    namespace App\Providers;

    use Illuminate\Support\ServiceProvider;
    use Laravel\Dusk\Browser;

    class DuskServiceProvider extends ServiceProvider
    {
        /**
         * Register Dusk's browser macros.
         *
         * @return void
         */
        public function boot()
        {
            Browser::macro('scrollToElement', function ($element = null) {
                $this->script("$('html, body').animate({ scrollTop: $('$element').offset().top }, 0);");

                return $this;
            });
        }
    }

`macro` 方法接受一個名稱作為其第一個引數，以及閉包作為其第二個引數。當在 `Browser` 實體上以方法呼叫該 Macro 時，會執行該
Macro 的閉包：

    $this->browse(function ($browser) use ($user) {
        $browser->visit('/pay')
                ->scrollToElement('#credit-card-details')
                ->assertSee('Enter Credit Card Details');
    });

<a name="authentication"></a>
### 驗證

一般來說，我們會需要測試需要登入的頁面。可以使用 Dusk 的 `loginAs` 方法來避免每個測試都需要處理應用程式的登入畫面。`loginAs`
方法接受 Authenticatable Model 所關聯的主索引鍵，或是 Authenticatable Model 實體：

    use App\Models\User;

    $this->browse(function ($browser) {
        $browser->loginAs(User::find(1))
              ->visit('/home');
    });

> {note} 使用 `loginAs` 方法後，在該檔案內所有的測試都將使用該使用者 Session。

<a name="cookies"></a>
### Cookie

可以使用 `cookie` 方法來取得或設定加密的 Cookie 值。預設情況下，Laravel 所建立的所有 Cookie 都是經過加密的：

    $browser->cookie('name');

    $browser->cookie('name', 'Taylor');

可以使用 `plainCookie` 方法來取得或設定未加密的 Cookie 值：

    $browser->plainCookie('name');

    $browser->plainCookie('name', 'Taylor');

可以使用 `deleteCookie` 方法來刪除給定的 Cookie：

    $browser->deleteCookie('name');

<a name="executing-javascript"></a>
### 執行 JavaScript

可以使用 `script` 方法來在瀏覽器內執行任意的 JavaScript 陳述式：

    $output = $browser->script('document.documentElement.scrollTop = 0');

    $output = $browser->script([
        'document.body.scrollTop = 0',
        'document.documentElement.scrollTop = 0',
    ]);

<a name="taking-a-screenshot"></a>
### 截圖

可以使用 `screenshot` 方法來截圖，並將截圖保存為給定的檔案名稱。所有的截圖都會保存在
`tests/Browser/screenshots` 目錄內：

    $browser->screenshot('filename');

<a name="storing-console-output-to-disk"></a>
### 將主控台輸出保存至磁碟

可以使用 `storeConsoleLog` 方法來將目前瀏覽器的主控台輸出以給定的檔案名稱寫入到磁碟內。主控台輸出會保存在
`tests/Browser/console` 目錄內：

    $browser->storeConsoleLog('filename');

<a name="storing-page-source-to-disk"></a>
### 將頁面原始碼儲存至磁碟

可以使用 `storeSource` 方法來將目前頁面的原始碼以給定的檔案名稱寫入到磁碟內。頁面原始碼會保存在
`tests/Browser/source` 目錄內：

    $browser->storeSource('filename');

<a name="interacting-with-elements"></a>
## 與元素互動

<a name="dusk-selectors"></a>
### Dusk 選擇器

在撰寫 Dusk 測試時，選擇一個好的 CSS 選擇器來與元素互動是最難的一部分。日子一天天過去，當前端有更改時，若有像下列這樣的 CSS
選擇器就有可能讓測試失敗：

    // HTML...

    <button>Login</button>

    // Test...

    $browser->click('.login-page .container div > button');

使用 Dusk 選擇器，就能讓開發人員更專注於撰寫有效的測試，而不是記住 CSS 選擇器。若要定義選擇請，請在 HTML 元素內加上 `dusk`
屬性。接著，當與 Dusk 瀏覽器互動時，請在該選擇器前方加上 `@` 來在測試內操作該元素：

    // HTML...

    <button dusk="login-button">Login</button>

    // Test...

    $browser->click('@login-button');

<a name="text-values-and-attributes"></a>
### 文字、值、與屬性

<a name="retrieving-setting-values"></a>
#### 取得與設定值

Dusk 內提供了數種可與目前頁面上元素的值、顯示文字、與屬性互動的方法。舉例來說，若要在某個符合給定 CSS 或 Dusk
選擇器的元素上取得該元素的「值 (Value)」，可使用 `value` 方法：

    // 取得值…
    $value = $browser->value('selector');

    // 設定值…
    $browser->value('selector', 'value');

可以使用 `inputValue` 方法來取得某個給定欄位名稱之 input 元素的「值 (Value)」：

    $value = $browser->inputValue('field');

<a name="retrieving-text"></a>
#### 取得文字

可使用 `text` 方法來取得符合給定選擇器之元素的顯示文字：

    $text = $browser->text('selector');

<a name="retrieving-attributes"></a>
#### 取得屬性

最後，可使用 `attribute` 方法來取得符合給定選擇器之元素的屬性值：

    $attribute = $browser->attribute('selector', 'value');

<a name="interacting-with-forms"></a>
### 與表單互動

<a name="typing-values"></a>
#### 鍵入值

Dusk 提供了多種與表單以及 Input 元素互動的方法。首先，來看看一個在 Input 欄位內鍵入文字的例子：

    $browser->type('email', 'taylor@laravel.com');

請注意這裡，雖然可將 CSS 選擇器傳入 `type` 方法，但並不需特別傳入。若未提供 CSS 選擇器，則 Dusk 會搜尋符合給定 `name`
屬性的 `input` 或 `textarea` 欄位。

若要在不將其原本內容清除的情況下將文字附加在最後面，可以使用 `append` 方法：

    $browser->type('tags', 'foo')
            ->append('tags', ', bar, baz');

可以使用 `clear` 方法來清除某個 Input 的值：

    $browser->clear('email');

可以使用 `typeSlowly` 方法來讓 Dusk 輸入得慢一點。預設情況下，Dusk 會在每個按鍵間暫停 100
毫秒。若要自定按鍵按下間的時間，可將適當的毫秒數作為第三個引數傳給該方法：

    $browser->typeSlowly('mobile', '+1 (202) 555-5555');

    $browser->typeSlowly('mobile', '+1 (202) 555-5555', 300);

可以使用 `appendSlowly` 方法來慢慢地將文字附加到最後：

    $browser->type('tags', 'foo')
            ->appendSlowly('tags', ', bar, baz');

<a name="dropdowns"></a>
#### 下拉選單

若要在 `select` 元素上選擇可用的值，可使用 `select` 方法。與 `type` 方法類似，`select` 方法並不要求要提供完整的
CSS 選擇器。將值傳給 `select` 方法時，應傳入底層的選項值而非顯示的文字：

    $browser->select('size', 'Large');

也可以通過省略第二個引數來隨機選擇選項：

    $browser->select('size');

<a name="checkboxes"></a>
#### 多選框

若要「勾選」多選框，可使用 `check` 方法。與其他 Input 有關的方法類似，並不需要傳入完整的 CSS 選擇器。若找不到對應的 CSS
選擇器，Dusk 會自動搜尋符合 `name` 屬性的多選框：

    $browser->check('terms');

可使用 `uncheck` 方法來「取消勾選」多選框：

    $browser->uncheck('terms');

<a name="radio-buttons"></a>
#### 單選框

若要「勾選」`radio` 單選框，可使用 `check` 方法。與其他 Input 有關的方法類似，並不需要傳入完整的 CSS 選擇器。若找不到對應的
CSS 選擇器，Dusk 會自動搜尋符合 `name` 屬性的 `radio` 單選框：

    $browser->radio('size', 'large');

<a name="attaching-files"></a>
### 附加檔案

可使用 `attach` 方法來將檔案附加到 `file` Input 元素上。與其他 Input 有關的方法類似，並不需要傳入完整的 CSS
選擇器。若找不到對應的 CSS 選擇器，Dusk 會自動搜尋符合 `name` 屬性的 `file` Input：

    $browser->attach('photo', __DIR__.'/photos/mountains.png');

> {note} 要使用 attach 函式，伺服器上必須有安裝 `Zip` PHP 擴充套件並已啟用。

<a name="pressing-buttons"></a>
### 按下按鈕

`press` 方法可用來點擊頁面上的按鈕元素。傳給 `press` 方法的第一個引數可以是按鈕的顯示文字，或是 CSS / Dusk 選擇器：

    $browser->press('Login');

在送出表單時，許多應用程式會在按鈕按下的時候禁用表單的送出按鈕，並在表單送出的 HTTP
請求完成後重新啟用該按鈕。若要按下按鈕並等待該按鈕重新啟用，可使用 `pressAndWaitFor` 方法：

    // 按下按鈕並等待最多 5 秒讓該按鈕重新啟用……
    $browser->pressAndWaitFor('Save');

    // 按下按鈕並等待最多 1 秒讓該按鈕重新啟用……
    $browser->pressAndWaitFor('Save', 1);

<a name="clicking-links"></a>
### 點擊連結

若要點擊連結，可使用瀏覽器實體上的 `clickLink` 方法。`clickLink` 方法會點擊有給定顯示文字的連結：

    $browser->clickLink($linkText);

可使用 `seeLink` 方法來判斷給定的顯示文字是否在頁面上可見：

    if ($browser->seeLink($linkText)) {
        // ...
    }

> {note} 該方法需要與 jQuery 互動。若頁面上沒有 jQuery 可用，則 Dusk 會自動將 jQuery 插入到頁面上以在測試期間使用。

<a name="using-the-keyboard"></a>
### 使用鍵盤

比起使用一般的 `type` 方法，`keys` 方法提供了可對給定元素進行一系列更複雜輸入的能力。舉例來說，可以讓 Dusk
在輸入數值的時候按著某個輔助按鍵。在這個範例中，於符合給定選擇器的元素內輸入 `taylor` 文字時，會按著 `Shift` 鍵。輸入完
`taylor` 後，`swift` 會在不按下任何輔助按鍵的情況下輸入：

    $browser->keys('selector', ['{shift}', 'taylor'], 'swift');

`keys` 方法的另一個實用用途是給主要 CSS 選擇器傳送一組「鍵盤快捷鍵」：

    $browser->keys('.app', ['{command}', 'j']);

> {tip} 所有的輔助按鍵，如 `{command}` 都以 `{}` 字元來進行包裝，且符合 `Facebook\WebDriver\WebDriverKeys` 中所定義的常數值。可[在 GitHub 上找到](https://github.com/php-webdriver/php-webdriver/blob/master/lib/WebDriverKeys.php)這些常數值。

<a name="using-the-mouse"></a>
### 使用滑鼠

<a name="clicking-on-elements"></a>
#### 點擊元素

可使用 `click` 方法來點擊符合給定 CSS 或 Dusk 選擇器的元素：

    $browser->click('.selector');

可使用 `clickAtXPath` 方法來點擊符合給定 XPath 運算式的元素：

    $browser->clickAtXPath('//div[@class = "selector"]');

可使用 `clickAtPoint` 方法來點擊在相對於瀏覽器檢視區域上，符合給定座標點上最上層的元素：

    $browser->clickAtPoint($x = 0, $y = 0);

可使用 `doubleClick` 方法來模擬使用滑鼠點兩下：

    $browser->doubleClick();

可使用 `rightClick` 方法來模擬按滑鼠右鍵：

    $browser->rightClick();

    $browser->rightClick('.selector');

可使用 `clickAndHold` 方法來模擬按下滑鼠按鈕並保持按下。若接著呼叫 `releaseMouse` 方法，則會取消這個行為並放開滑鼠按鈕：

    $browser->clickAndHold()
            ->pause(1000)
            ->releaseMouse();

<a name="mouseover"></a>
#### 滑鼠移至上方

當需要將滑鼠移至符合給定 CSS 或 Dusk 選擇器的元素上時，可使用 `mouseover` 方法：

    $browser->mouseover('.selector');

<a name="drag-drop"></a>
#### 拖放

可使用 `drag` 方法來將符合給定選擇器元素拖曳至另一個元素上：

    $browser->drag('.from-selector', '.to-selector');

或者，也可以將某個元素在單一方向上拖曳：

    $browser->dragLeft('.selector', $pixels = 10);
    $browser->dragRight('.selector', $pixels = 10);
    $browser->dragUp('.selector', $pixels = 10);
    $browser->dragDown('.selector', $pixels = 10);

最後，可以依照給定偏移值來拖曳元素：

    $browser->dragOffset('.selector', $x = 10, $y = 10);

<a name="javascript-dialogs"></a>
### JavaScript 對話方塊

Dusk 提供了多種與 JavaScript 對話方塊互動的方法。舉例來說，可以使用 `waitForDialog` 方法來等待 JavaScript
對話方塊出現。該方法可接收一個可選的引數來判斷要等幾秒讓該對話方塊顯示出來：

    $browser->waitForDialog($seconds = null);

可使用 `assertDialogOpened` 方法來判斷某個對話方塊是否已顯示，且包含給定的訊息：

    $browser->assertDialogOpened('Dialog message');

若該 JavaScript 對話方塊包含輸入提示，可使用 `typeInDialog` 方法來在該提示中輸入數值：

    $browser->typeInDialog('Hello World');

若要點擊「確定」按鈕來關閉開啟的 JavaScript 對話方塊，可以叫用 `acceptDialog` 方法：

    $browser->acceptDialog();

若要點擊「取消」按鈕來關閉開啟的 JavaScript 對話方塊，可以叫用 `dismissDialog` 方法：

    $browser->dismissDialog();

<a name="scoping-selectors"></a>
### 區域性選擇器

有的時候，我們可能會想把多個操作限制到某個特定選擇器裡面。舉例來說，我們在判斷某段文字是否有出現時，可能只想在某個表格內檢查，並在檢查完畢後接著在該表格內點擊某個按鈕。可以使用
`with` 方法來達成。在提供給 `with` 方法的閉包內所進行的操作都會被限制在某個選擇器之內：

    $browser->with('.table', function ($table) {
        $table->assertSee('Hello World')
              ->clickLink('Delete');
    });

某些時候，我們可能需要在目前的 Scope 外執行 Assertion。可以使用 `elsewhere` 與
`elsewhereWhenAvailable` 方法來進行：

     $browser->with('.table', function ($table) {
        // 目前的 Scope 為 `body .table`...

        $browser->elsewhere('.page-title', function ($title) {
            // 目前的 Scope 為 `body .page-title`...
            $title->assertSee('Hello World');
        });

        $browser->elsewhereWhenAvailable('.page-title', function ($title) {
            // 目前的 Scope 為 `body .page-title`...
            $title->assertSee('Hello World');
        });
     });

<a name="waiting-for-elements"></a>
### 等待元素

在測試使用了大量 JavaScript 的應用程式時，常常會需要「等待」特定元素或資料出現後才能繼續進行測試。在 Dusk
中可以輕鬆做到。只需要使用幾個方法，就可以等待元素出現在頁面上，或是等待某個給定的 JavaScript 運算式取值變為 `true`。

<a name="waiting"></a>
#### 等待

若只是需要將測試暫停幾毫秒，可使用 `pause` 方法：

    $browser->pause(1000);

<a name="waiting-for-selectors"></a>
#### 等待選擇器

`waitFor` 方法可用來暫停執行測試，並等到符合給定 CSS 或 Dusk
選擇器的元素顯示在頁面上。預設情況下，該方法會最多會暫停測試五秒，超過則會拋出例外。若有需要，可以將自訂的逾時閥值傳入為該方法的第二個引數：

    // 等待選擇器最多 5 秒...
    $browser->waitFor('.selector');

    // 等待選擇器最多 1 秒...
    $browser->waitFor('.selector', 1);

也可以等待某個符合給定選擇器的元素出現給定文字：

    // 等待選擇器包含給定文字，最多等待 5 秒...
    $browser->waitForTextIn('.selector', 'Hello World');

    // 等待選擇器包含給定文字，最多等待 1 秒...
    $browser->waitForTextIn('.selector', 'Hello World', 1);

也可以等待某個符合給定選擇器的元素消失在頁面上：

    // 等待選擇器小時，最多 5 秒...
    $browser->waitUntilMissing('.selector');

    // 等待選擇器小時，最多 1 秒...
    $browser->waitUntilMissing('.selector', 1);

<a name="scoping-selectors-when-available"></a>
#### 可用時進入選擇器的 Scope

有時候我們可能會想等待符合給定選擇器的元素出現在頁面上後再接著與該元素互動。舉例來說，我們可能會想等待某個 Modal 視窗出現，然後在該 Modal
內點擊「OK」按鈕。可以使用 `whenAvailable` 方法來完成。在給定閉包內進行的所有元素操作都會被限制在原始選擇器的作用範圍內：

    $browser->whenAvailable('.modal', function ($modal) {
        $modal->assertSee('Hello World')
              ->press('OK');
    });

<a name="waiting-for-text"></a>
#### 等待文字

可使用 `waitForText` 方法來等待給定文字顯示在頁面上：

    // Wait a maximum of five seconds for the text...
    $browser->waitForText('Hello World');

    // Wait a maximum of one second for the text...
    $browser->waitForText('Hello World', 1);

You may use the `waitUntilMissingText` method to wait until the displayed
text has been removed from the page:

    // Wait a maximum of five seconds for the text to be removed...
    $browser->waitUntilMissingText('Hello World');

    // Wait a maximum of one second for the text to be removed...
    $browser->waitUntilMissingText('Hello World', 1);

<a name="waiting-for-links"></a>
#### Waiting For Links

The `waitForLink` method may be used to wait until the given link text is
displayed on the page:

    // Wait a maximum of five seconds for the link...
    $browser->waitForLink('Create');

    // Wait a maximum of one second for the link...
    $browser->waitForLink('Create', 1);

<a name="waiting-on-the-page-location"></a>
#### Waiting On The Page Location

When making a path assertion such as `$browser->assertPathIs('/home')`, the assertion can fail if `window.location.pathname` is being updated asynchronously. You may use the `waitForLocation` method to wait for the location to be a given value:

    $browser->waitForLocation('/secret');

You may also wait for a [named
route's](/docs/{{version}}/routing#named-routes) location:

    $browser->waitForRoute($routeName, $parameters);

<a name="waiting-for-page-reloads"></a>
#### Waiting for Page Reloads

If you need to make assertions after a page has been reloaded, use the
`waitForReload` method:

    $browser->click('.some-action')
            ->waitForReload()
            ->assertSee('something');

<a name="waiting-on-javascript-expressions"></a>
#### Waiting On JavaScript Expressions

Sometimes you may wish to pause the execution of a test until a given
JavaScript expression evaluates to `true`. You may easily accomplish this
using the `waitUntil` method. When passing an expression to this method, you
do not need to include the `return` keyword or an ending semi-colon:

    // Wait a maximum of five seconds for the expression to be true...
    $browser->waitUntil('App.data.servers.length > 0');

    // Wait a maximum of one second for the expression to be true...
    $browser->waitUntil('App.data.servers.length > 0', 1);

<a name="waiting-on-vue-expressions"></a>
#### Waiting On Vue Expressions

The `waitUntilVue` and `waitUntilVueIsNot` methods may be used to wait until
a [Vue component](https://vuejs.org) attribute has a given value:

    // Wait until the component attribute contains the given value...
    $browser->waitUntilVue('user.name', 'Taylor', '@user');

    // Wait until the component attribute doesn't contain the given value...
    $browser->waitUntilVueIsNot('user.name', null, '@user');

<a name="waiting-with-a-callback"></a>
#### Waiting With A Callback

Many of the "wait" methods in Dusk rely on the underlying `waitUsing`
method. You may use this method directly to wait for a given closure to
return `true`. The `waitUsing` method accepts the maximum number of seconds
to wait, the interval at which the closure should be evaluated, the closure,
and an optional failure message:

    $browser->waitUsing(10, 1, function () use ($something) {
        return $something->isReady();
    }, "Something wasn't ready in time.");

<a name="scrolling-an-element-into-view"></a>
### Scrolling An Element Into View

Sometimes you may not be able to click on an element because it is outside
of the viewable area of the browser. The `scrollIntoView` method will scroll
the browser window until the element at the given selector is within the
view:

    $browser->scrollIntoView('.selector')
            ->click('.selector');

<a name="available-assertions"></a>
## 可用的 Assertion

Dusk provides a variety of assertions that you may make against your
application. All of the available assertions are documented in the list
below:

<style>
    .collection-method-list > p {
        column-count: 3; -moz-column-count: 3; -webkit-column-count: 3;
        column-gap: 2em; -moz-column-gap: 2em; -webkit-column-gap: 2em;
    }

    .collection-method-list a {
        display: block;
    }
</style>

<div class="collection-method-list" markdown="1">
[assertTitle](#assert-title)
[assertTitleContains](#assert-title-contains)
[assertUrlIs](#assert-url-is)
[assertSchemeIs](#assert-scheme-is)
[assertSchemeIsNot](#assert-scheme-is-not)
[assertHostIs](#assert-host-is)
[assertHostIsNot](#assert-host-is-not)
[assertPortIs](#assert-port-is)
[assertPortIsNot](#assert-port-is-not)
[assertPathBeginsWith](#assert-path-begins-with)
[assertPathIs](#assert-path-is)
[assertPathIsNot](#assert-path-is-not)
[assertRouteIs](#assert-route-is)
[assertQueryStringHas](#assert-query-string-has)
[assertQueryStringMissing](#assert-query-string-missing)
[assertFragmentIs](#assert-fragment-is)
[assertFragmentBeginsWith](#assert-fragment-begins-with)
[assertFragmentIsNot](#assert-fragment-is-not)
[assertHasCookie](#assert-has-cookie)
[assertHasPlainCookie](#assert-has-plain-cookie)
[assertCookieMissing](#assert-cookie-missing)
[assertPlainCookieMissing](#assert-plain-cookie-missing)
[assertCookieValue](#assert-cookie-value)
[assertPlainCookieValue](#assert-plain-cookie-value)
[assertSee](#assert-see)
[assertDontSee](#assert-dont-see)
[assertSeeIn](#assert-see-in)
[assertDontSeeIn](#assert-dont-see-in)
[assertSeeAnythingIn](#assert-see-anything-in)
[assertSeeNothingIn](#assert-see-nothing-in)
[assertScript](#assert-script)
[assertSourceHas](#assert-source-has)
[assertSourceMissing](#assert-source-missing)
[assertSeeLink](#assert-see-link)
[assertDontSeeLink](#assert-dont-see-link)
[assertInputValue](#assert-input-value)
[assertInputValueIsNot](#assert-input-value-is-not)
[assertChecked](#assert-checked)
[assertNotChecked](#assert-not-checked)
[assertRadioSelected](#assert-radio-selected)
[assertRadioNotSelected](#assert-radio-not-selected)
[assertSelected](#assert-selected)
[assertNotSelected](#assert-not-selected)
[assertSelectHasOptions](#assert-select-has-options)
[assertSelectMissingOptions](#assert-select-missing-options)
[assertSelectHasOption](#assert-select-has-option)
[assertSelectMissingOption](#assert-select-missing-option)
[assertValue](#assert-value)
[assertAttribute](#assert-attribute)
[assertAriaAttribute](#assert-aria-attribute)
[assertDataAttribute](#assert-data-attribute)
[assertVisible](#assert-visible)
[assertPresent](#assert-present)
[assertNotPresent](#assert-not-present)
[assertMissing](#assert-missing)
[assertDialogOpened](#assert-dialog-opened)
[assertEnabled](#assert-enabled)
[assertDisabled](#assert-disabled)
[assertButtonEnabled](#assert-button-enabled)
[assertButtonDisabled](#assert-button-disabled)
[assertFocused](#assert-focused)
[assertNotFocused](#assert-not-focused)
[assertAuthenticated](#assert-authenticated)
[assertGuest](#assert-guest)
[assertAuthenticatedAs](#assert-authenticated-as)
[assertVue](#assert-vue)
[assertVueIsNot](#assert-vue-is-not)
[assertVueContains](#assert-vue-contains)
[assertVueDoesNotContain](#assert-vue-does-not-contain)
</div>

<a name="assert-title"></a>
#### assertTitle

Assert that the page title matches the given text:

    $browser->assertTitle($title);

<a name="assert-title-contains"></a>
#### assertTitleContains

Assert that the page title contains the given text:

    $browser->assertTitleContains($title);

<a name="assert-url-is"></a>
#### assertUrlIs

Assert that the current URL (without the query string) matches the given
string:

    $browser->assertUrlIs($url);

<a name="assert-scheme-is"></a>
#### assertSchemeIs

Assert that the current URL scheme matches the given scheme:

    $browser->assertSchemeIs($scheme);

<a name="assert-scheme-is-not"></a>
#### assertSchemeIsNot

Assert that the current URL scheme does not match the given scheme:

    $browser->assertSchemeIsNot($scheme);

<a name="assert-host-is"></a>
#### assertHostIs

Assert that the current URL host matches the given host:

    $browser->assertHostIs($host);

<a name="assert-host-is-not"></a>
#### assertHostIsNot

Assert that the current URL host does not match the given host:

    $browser->assertHostIsNot($host);

<a name="assert-port-is"></a>
#### assertPortIs

Assert that the current URL port matches the given port:

    $browser->assertPortIs($port);

<a name="assert-port-is-not"></a>
#### assertPortIsNot

Assert that the current URL port does not match the given port:

    $browser->assertPortIsNot($port);

<a name="assert-path-begins-with"></a>
#### assertPathBeginsWith

Assert that the current URL path begins with the given path:

    $browser->assertPathBeginsWith('/home');

<a name="assert-path-is"></a>
#### assertPathIs

Assert that the current path matches the given path:

    $browser->assertPathIs('/home');

<a name="assert-path-is-not"></a>
#### assertPathIsNot

Assert that the current path does not match the given path:

    $browser->assertPathIsNot('/home');

<a name="assert-route-is"></a>
#### assertRouteIs

Assert that the current URL matches the given [named
route's](/docs/{{version}}/routing#named-routes) URL:

    $browser->assertRouteIs($name, $parameters);

<a name="assert-query-string-has"></a>
#### assertQueryStringHas

Assert that the given query string parameter is present:

    $browser->assertQueryStringHas($name);

Assert that the given query string parameter is present and has a given
value:

    $browser->assertQueryStringHas($name, $value);

<a name="assert-query-string-missing"></a>
#### assertQueryStringMissing

Assert that the given query string parameter is missing:

    $browser->assertQueryStringMissing($name);

<a name="assert-fragment-is"></a>
#### assertFragmentIs

Assert that the URL's current hash fragment matches the given fragment:

    $browser->assertFragmentIs('anchor');

<a name="assert-fragment-begins-with"></a>
#### assertFragmentBeginsWith

Assert that the URL's current hash fragment begins with the given fragment:

    $browser->assertFragmentBeginsWith('anchor');

<a name="assert-fragment-is-not"></a>
#### assertFragmentIsNot

Assert that the URL's current hash fragment does not match the given
fragment:

    $browser->assertFragmentIsNot('anchor');

<a name="assert-has-cookie"></a>
#### assertHasCookie

Assert that the given encrypted cookie is present:

    $browser->assertHasCookie($name);

<a name="assert-has-plain-cookie"></a>
#### assertHasPlainCookie

Assert that the given unencrypted cookie is present:

    $browser->assertHasPlainCookie($name);

<a name="assert-cookie-missing"></a>
#### assertCookieMissing

Assert that the given encrypted cookie is not present:

    $browser->assertCookieMissing($name);

<a name="assert-plain-cookie-missing"></a>
#### assertPlainCookieMissing

Assert that the given unencrypted cookie is not present:

    $browser->assertPlainCookieMissing($name);

<a name="assert-cookie-value"></a>
#### assertCookieValue

Assert that an encrypted cookie has a given value:

    $browser->assertCookieValue($name, $value);

<a name="assert-plain-cookie-value"></a>
#### assertPlainCookieValue

Assert that an unencrypted cookie has a given value:

    $browser->assertPlainCookieValue($name, $value);

<a name="assert-see"></a>
#### assertSee

Assert that the given text is present on the page:

    $browser->assertSee($text);

<a name="assert-dont-see"></a>
#### assertDontSee

Assert that the given text is not present on the page:

    $browser->assertDontSee($text);

<a name="assert-see-in"></a>
#### assertSeeIn

Assert that the given text is present within the selector:

    $browser->assertSeeIn($selector, $text);

<a name="assert-dont-see-in"></a>
#### assertDontSeeIn

Assert that the given text is not present within the selector:

    $browser->assertDontSeeIn($selector, $text);

<a name="assert-see-anything-in"></a>
#### assertSeeAnythingIn

Assert that any text is present within the selector:

    $browser->assertSeeAnythingIn($selector);

<a name="assert-see-nothing-in"></a>
#### assertSeeNothingIn

Assert that no text is present within the selector:

    $browser->assertSeeNothingIn($selector);

<a name="assert-script"></a>
#### assertScript

Assert that the given JavaScript expression evaluates to the given value:

    $browser->assertScript('window.isLoaded')
            ->assertScript('document.readyState', 'complete');

<a name="assert-source-has"></a>
#### assertSourceHas

Assert that the given source code is present on the page:

    $browser->assertSourceHas($code);

<a name="assert-source-missing"></a>
#### assertSourceMissing

Assert that the given source code is not present on the page:

    $browser->assertSourceMissing($code);

<a name="assert-see-link"></a>
#### assertSeeLink

Assert that the given link is present on the page:

    $browser->assertSeeLink($linkText);

<a name="assert-dont-see-link"></a>
#### assertDontSeeLink

Assert that the given link is not present on the page:

    $browser->assertDontSeeLink($linkText);

<a name="assert-input-value"></a>
#### assertInputValue

Assert that the given input field has the given value:

    $browser->assertInputValue($field, $value);

<a name="assert-input-value-is-not"></a>
#### assertInputValueIsNot

Assert that the given input field does not have the given value:

    $browser->assertInputValueIsNot($field, $value);

<a name="assert-checked"></a>
#### assertChecked

Assert that the given checkbox is checked:

    $browser->assertChecked($field);

<a name="assert-not-checked"></a>
#### assertNotChecked

Assert that the given checkbox is not checked:

    $browser->assertNotChecked($field);

<a name="assert-radio-selected"></a>
#### assertRadioSelected

Assert that the given radio field is selected:

    $browser->assertRadioSelected($field, $value);

<a name="assert-radio-not-selected"></a>
#### assertRadioNotSelected

Assert that the given radio field is not selected:

    $browser->assertRadioNotSelected($field, $value);

<a name="assert-selected"></a>
#### assertSelected

Assert that the given dropdown has the given value selected:

    $browser->assertSelected($field, $value);

<a name="assert-not-selected"></a>
#### assertNotSelected

Assert that the given dropdown does not have the given value selected:

    $browser->assertNotSelected($field, $value);

<a name="assert-select-has-options"></a>
#### assertSelectHasOptions

Assert that the given array of values are available to be selected:

    $browser->assertSelectHasOptions($field, $values);

<a name="assert-select-missing-options"></a>
#### assertSelectMissingOptions

Assert that the given array of values are not available to be selected:

    $browser->assertSelectMissingOptions($field, $values);

<a name="assert-select-has-option"></a>
#### assertSelectHasOption

Assert that the given value is available to be selected on the given field:

    $browser->assertSelectHasOption($field, $value);

<a name="assert-select-missing-option"></a>
#### assertSelectMissingOption

Assert that the given value is not available to be selected:

    $browser->assertSelectMissingOption($field, $value);

<a name="assert-value"></a>
#### assertValue

Assert that the element matching the given selector has the given value:

    $browser->assertValue($selector, $value);

<a name="assert-attribute"></a>
#### assertAttribute

Assert that the element matching the given selector has the given value in
the provided attribute:

    $browser->assertAttribute($selector, $attribute, $value);

<a name="assert-aria-attribute"></a>
#### assertAriaAttribute

Assert that the element matching the given selector has the given value in
the provided aria attribute:

    $browser->assertAriaAttribute($selector, $attribute, $value);

For example, given the markup `<button aria-label="Add"></button>`, you may assert against the `aria-label` attribute like so:

    $browser->assertAriaAttribute('button', 'label', 'Add')

<a name="assert-data-attribute"></a>
#### assertDataAttribute

Assert that the element matching the given selector has the given value in
the provided data attribute:

    $browser->assertDataAttribute($selector, $attribute, $value);

For example, given the markup `<tr id="row-1" data-content="attendees"></tr>`, you may assert against the `data-label` attribute like so:

    $browser->assertDataAttribute('#row-1', 'content', 'attendees')

<a name="assert-visible"></a>
#### assertVisible

Assert that the element matching the given selector is visible:

    $browser->assertVisible($selector);

<a name="assert-present"></a>
#### assertPresent

Assert that the element matching the given selector is present:

    $browser->assertPresent($selector);

<a name="assert-not-present"></a>
#### assertNotPresent

Assert that the element matching the given selector is not present in the
source:

    $browser->assertNotPresent($selector);

<a name="assert-missing"></a>
#### assertMissing

Assert that the element matching the given selector is not visible:

    $browser->assertMissing($selector);

<a name="assert-dialog-opened"></a>
#### assertDialogOpened

Assert that a JavaScript dialog with the given message has been opened:

    $browser->assertDialogOpened($message);

<a name="assert-enabled"></a>
#### assertEnabled

Assert that the given field is enabled:

    $browser->assertEnabled($field);

<a name="assert-disabled"></a>
#### assertDisabled

Assert that the given field is disabled:

    $browser->assertDisabled($field);

<a name="assert-button-enabled"></a>
#### assertButtonEnabled

Assert that the given button is enabled:

    $browser->assertButtonEnabled($button);

<a name="assert-button-disabled"></a>
#### assertButtonDisabled

Assert that the given button is disabled:

    $browser->assertButtonDisabled($button);

<a name="assert-focused"></a>
#### assertFocused

Assert that the given field is focused:

    $browser->assertFocused($field);

<a name="assert-not-focused"></a>
#### assertNotFocused

Assert that the given field is not focused:

    $browser->assertNotFocused($field);

<a name="assert-authenticated"></a>
#### assertAuthenticated

Assert that the user is authenticated:

    $browser->assertAuthenticated();

<a name="assert-guest"></a>
#### assertGuest

Assert that the user is not authenticated:

    $browser->assertGuest();

<a name="assert-authenticated-as"></a>
#### assertAuthenticatedAs

Assert that the user is authenticated as the given user:

    $browser->assertAuthenticatedAs($user);

<a name="assert-vue"></a>
#### assertVue

Dusk even allows you to make assertions on the state of [Vue
component](https://vuejs.org) data. For example, imagine your application
contains the following Vue component:

    // HTML...

    <profile dusk="profile-component"></profile>

    // Component Definition...

    Vue.component('profile', {
        template: '<div>{{ user.name }}</div>',

        data: function () {
            return {
                user: {
                    name: 'Taylor'
                }
            };
        }
    });

You may assert on the state of the Vue component like so:

    /**
     * A basic Vue test example.
     *
     * @return void
     */
    public function testVue()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertVue('user.name', 'Taylor', '@profile-component');
        });
    }

<a name="assert-vue-is-not"></a>
#### assertVueIsNot

Assert that a given Vue component data property does not match the given
value:

    $browser->assertVueIsNot($property, $value, $componentSelector = null);

<a name="assert-vue-contains"></a>
#### assertVueContains

Assert that a given Vue component data property is an array and contains the
given value:

    $browser->assertVueContains($property, $value, $componentSelector = null);

<a name="assert-vue-does-not-contain"></a>
#### assertVueDoesNotContain

Assert that a given Vue component data property is an array and does not
contain the given value:

    $browser->assertVueDoesNotContain($property, $value, $componentSelector = null);

<a name="pages"></a>
## Pages

Sometimes, tests require several complicated actions to be performed in
sequence. This can make your tests harder to read and understand. Dusk Pages
allow you to define expressive actions that may then be performed on a given
page via a single method. Pages also allow you to define short-cuts to
common selectors for your application or for a single page.

<a name="generating-pages"></a>
### Generating Pages

To generate a page object, execute the `dusk:page` Artisan command. All page
objects will be placed in your application's `tests/Browser/Pages`
directory:

    php artisan dusk:page Login

<a name="configuring-pages"></a>
### Configuring Pages

By default, pages have three methods: `url`, `assert`, and `elements`. We
will discuss the `url` and `assert` methods now. The `elements` method will
be [discussed in more detail below](#shorthand-selectors).

<a name="the-url-method"></a>
#### The `url` Method

The `url` method should return the path of the URL that represents the
page. Dusk will use this URL when navigating to the page in the browser:

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/login';
    }

<a name="the-assert-method"></a>
#### The `assert` Method

The `assert` method may make any assertions necessary to verify that the
browser is actually on the given page. It is not actually necessary to place
anything within this method; however, you are free to make these assertions
if you wish. These assertions will be run automatically when navigating to
the page:

    /**
     * Assert that the browser is on the page.
     *
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url());
    }

<a name="navigating-to-pages"></a>
### Navigating To Pages

Once a page has been defined, you may navigate to it using the `visit`
method:

    use Tests\Browser\Pages\Login;

    $browser->visit(new Login);

Sometimes you may already be on a given page and need to "load" the page's
selectors and methods into the current test context. This is common when
pressing a button and being redirected to a given page without explicitly
navigating to it. In this situation, you may use the `on` method to load the
page:

    use Tests\Browser\Pages\CreatePlaylist;

    $browser->visit('/dashboard')
            ->clickLink('Create Playlist')
            ->on(new CreatePlaylist)
            ->assertSee('@create');

<a name="shorthand-selectors"></a>
### Shorthand Selectors

The `elements` method within page classes allows you to define quick,
easy-to-remember shortcuts for any CSS selector on your page. For example,
let's define a shortcut for the "email" input field of the application's
login page:

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@email' => 'input[name=email]',
        ];
    }

Once the shortcut has been defined, you may use the shorthand selector
anywhere you would typically use a full CSS selector:

    $browser->type('@email', 'taylor@laravel.com');

<a name="global-shorthand-selectors"></a>
#### Global Shorthand Selectors

After installing Dusk, a base `Page` class will be placed in your
`tests/Browser/Pages` directory. This class contains a `siteElements` method
which may be used to define global shorthand selectors that should be
available on every page throughout your application:

    /**
     * Get the global element shortcuts for the site.
     *
     * @return array
     */
    public static function siteElements()
    {
        return [
            '@element' => '#selector',
        ];
    }

<a name="page-methods"></a>
### Page Methods

In addition to the default methods defined on pages, you may define
additional methods which may be used throughout your tests. For example,
let's imagine we are building a music management application. A common
action for one page of the application might be to create a
playlist. Instead of re-writing the logic to create a playlist in each test,
you may define a `createPlaylist` method on a page class:

    <?php

    namespace Tests\Browser\Pages;

    use Laravel\Dusk\Browser;

    class Dashboard extends Page
    {
        // Other page methods...

        /**
         * Create a new playlist.
         *
         * @param  \Laravel\Dusk\Browser  $browser
         * @param  string  $name
         * @return void
         */
        public function createPlaylist(Browser $browser, $name)
        {
            $browser->type('name', $name)
                    ->check('share')
                    ->press('Create Playlist');
        }
    }

Once the method has been defined, you may use it within any test that
utilizes the page. The browser instance will automatically be passed as the
first argument to custom page methods:

    use Tests\Browser\Pages\Dashboard;

    $browser->visit(new Dashboard)
            ->createPlaylist('My Playlist')
            ->assertSee('My Playlist');

<a name="components"></a>
## 元件

Components are similar to Dusk’s “page objects”, but are intended for pieces
of UI and functionality that are re-used throughout your application, such
as a navigation bar or notification window. As such, components are not
bound to specific URLs.

<a name="generating-components"></a>
### Generating Components

To generate a component, execute the `dusk:component` Artisan command. New
components are placed in the `tests/Browser/Components` directory:

    php artisan dusk:component DatePicker

As shown above, a "date picker" is an example of a component that might
exist throughout your application on a variety of pages. It can become
cumbersome to manually write the browser automation logic to select a date
in dozens of tests throughout your test suite. Instead, we can define a Dusk
component to represent the date picker, allowing us to encapsulate that
logic within the component:

    <?php

    namespace Tests\Browser\Components;

    use Laravel\Dusk\Browser;
    use Laravel\Dusk\Component as BaseComponent;

    class DatePicker extends BaseComponent
    {
        /**
         * Get the root selector for the component.
         *
         * @return string
         */
        public function selector()
        {
            return '.date-picker';
        }

        /**
         * Assert that the browser page contains the component.
         *
         * @param  Browser  $browser
         * @return void
         */
        public function assert(Browser $browser)
        {
            $browser->assertVisible($this->selector());
        }

        /**
         * Get the element shortcuts for the component.
         *
         * @return array
         */
        public function elements()
        {
            return [
                '@date-field' => 'input.datepicker-input',
                '@year-list' => 'div > div.datepicker-years',
                '@month-list' => 'div > div.datepicker-months',
                '@day-list' => 'div > div.datepicker-days',
            ];
        }

        /**
         * Select the given date.
         *
         * @param  \Laravel\Dusk\Browser  $browser
         * @param  int  $year
         * @param  int  $month
         * @param  int  $day
         * @return void
         */
        public function selectDate(Browser $browser, $year, $month, $day)
        {
            $browser->click('@date-field')
                    ->within('@year-list', function ($browser) use ($year) {
                        $browser->click($year);
                    })
                    ->within('@month-list', function ($browser) use ($month) {
                        $browser->click($month);
                    })
                    ->within('@day-list', function ($browser) use ($day) {
                        $browser->click($day);
                    });
        }
    }

<a name="using-components"></a>
### Using Components

Once the component has been defined, we can easily select a date within the
date picker from any test. And, if the logic necessary to select a date
changes, we only need to update the component:

    <?php

    namespace Tests\Browser;

    use Illuminate\Foundation\Testing\DatabaseMigrations;
    use Laravel\Dusk\Browser;
    use Tests\Browser\Components\DatePicker;
    use Tests\DuskTestCase;

    class ExampleTest extends DuskTestCase
    {
        /**
         * A basic component test example.
         *
         * @return void
         */
        public function testBasicExample()
        {
            $this->browse(function (Browser $browser) {
                $browser->visit('/')
                        ->within(new DatePicker, function ($browser) {
                            $browser->selectDate(2019, 1, 30);
                        })
                        ->assertSee('January');
            });
        }
    }

<a name="continuous-integration"></a>
## Continuous Integration

> {note} Most Dusk continuous integration configurations expect your Laravel application to be served using the built-in PHP development server on port 8000. Therefore, before continuing, you should ensure that your continuous integration environment has an `APP_URL` environment variable value of `http://127.0.0.1:8000`.

<a name="running-tests-on-heroku-ci"></a>
### Heroku CI

To run Dusk tests on [Heroku
CI](https://www.heroku.com/continuous-integration), add the following Google
Chrome buildpack and scripts to your Heroku `app.json` file:

    {
      "environments": {
        "test": {
          "buildpacks": [
            { "url": "heroku/php" },
            { "url": "https://github.com/heroku/heroku-buildpack-google-chrome" }
          ],
          "scripts": {
            "test-setup": "cp .env.testing .env",
            "test": "nohup bash -c './vendor/laravel/dusk/bin/chromedriver-linux > /dev/null 2>&1 &' && nohup bash -c 'php artisan serve > /dev/null 2>&1 &' && php artisan dusk"
          }
        }
      }
    }

<a name="running-tests-on-travis-ci"></a>
### Travis CI

To run your Dusk tests on [Travis CI](https://travis-ci.org), use the
following `.travis.yml` configuration. Since Travis CI is not a graphical
environment, we will need to take some extra steps in order to launch a
Chrome browser. In addition, we will use `php artisan serve` to launch PHP's
built-in web server:

    language: php

    php:
      - 7.3

    addons:
      chrome: stable

    install:
      - cp .env.testing .env
      - travis_retry composer install --no-interaction --prefer-dist --no-suggest
      - php artisan key:generate
      - php artisan dusk:chrome-driver

    before_script:
      - google-chrome-stable --headless --disable-gpu --remote-debugging-port=9222 http://localhost &
      - php artisan serve &

    script:
      - php artisan dusk

<a name="running-tests-on-github-actions"></a>
### GitHub Actions

If you are using [Github Actions](https://github.com/features/actions) to
run your Dusk tests, you may use the following configuration file as a
starting point. Like TravisCI, we will use the `php artisan serve` command
to launch PHP's built-in web server:

    name: CI
    on: [push]
    jobs:

      dusk-php:
        runs-on: ubuntu-latest
        steps:
          - uses: actions/checkout@v2
          - name: Prepare The Environment
            run: cp .env.example .env
          - name: Create Database
            run: |
              sudo systemctl start mysql
              mysql --user="root" --password="root" -e "CREATE DATABASE 'my-database' character set UTF8mb4 collate utf8mb4_bin;"
          - name: Install Composer Dependencies
            run: composer install --no-progress --no-suggest --prefer-dist --optimize-autoloader
          - name: Generate Application Key
            run: php artisan key:generate
          - name: Upgrade Chrome Driver
            run: php artisan dusk:chrome-driver `/opt/google/chrome/chrome --version | cut -d " " -f3 | cut -d "." -f1`
          - name: Start Chrome Driver
            run: ./vendor/laravel/dusk/bin/chromedriver-linux &
          - name: Run Laravel Server
            run: php artisan serve &
          - name: Run Dusk Tests
            env:
              APP_URL: "http://127.0.0.1:8000"
            run: php artisan dusk
          - name: Upload Screenshots
            if: failure()
            uses: actions/upload-artifact@v2
            with:
              name: screenshots
              path: tests/Browser/screenshots
          - name: Upload Console Logs
            if: failure()
            uses: actions/upload-artifact@v2
            with:
              name: console
              path: tests/Browser/console
