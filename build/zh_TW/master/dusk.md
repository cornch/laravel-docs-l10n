---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/49/en-zhtw'
updatedAt: '2024-06-30T08:18:00Z'
contributors: {  }
progress: 49.33
---

# Laravel Dusk

- [簡介](#introduction)
- [安裝](#installation)
  - [安裝 ChromeDriver](#managing-chromedriver-installations)
  - [使用其他瀏覽器](#using-other-browsers)
  
- [入門](#getting-started)
  - [產生測試](#generating-tests)
  - [Resetting the Database After Each Test](#resetting-the-database-after-each-test)
  - [執行測試](#running-tests)
  - [處理環境](#environment-handling)
  
- [「Browser」基礎](#browser-basics)
  - [建立 Browser](#creating-browsers)
  - [瀏覽](#navigation)
  - [縮放 Browser 視窗](#resizing-browser-windows)
  - [Browser Macro](#browser-macros)
  - [身份認證](#authentication)
  - [Cookie](#cookies)
  - [執行 JavaScript](#executing-javascript)
  - [Taking a Screenshot](#taking-a-screenshot)
  - [Storing Console Output to Disk](#storing-console-output-to-disk)
  - [Storing Page Source to Disk](#storing-page-source-to-disk)
  
- [與元素互動](#interacting-with-elements)
  - [Dusk 選擇器](#dusk-selectors)
  - [Text, Values, and Attributes](#text-values-and-attributes)
  - [處理表單](#interacting-with-forms)
  - [附加檔案](#attaching-files)
  - [按下按鈕](#pressing-buttons)
  - [點擊連結](#clicking-links)
  - [Using the Keyboard](#using-the-keyboard)
  - [Using the Mouse](#using-the-mouse)
  - [JavaScript 對話方塊](#javascript-dialogs)
  - [處理 IFrame](#interacting-with-iframes)
  - [限制範圍的選擇器](#scoping-selectors)
  - [Waiting for Elements](#waiting-for-elements)
  - [Scrolling an Element Into View](#scrolling-an-element-into-view)
  
- [可用的 Assertion](#available-assertions)
- [Page](#pages)
  - [產生 Page](#generating-pages)
  - [設定 Page](#configuring-pages)
  - [Navigating to Pages](#navigating-to-pages)
  - [選擇器簡寫](#shorthand-selectors)
  - [Page 方法](#page-methods)
  
- [Component (元件)](#components)
  - [產生 Component](#generating-components)
  - [使用 Component](#using-components)
  
- [持續整合 (Continuous Integration)](#continuous-integration)
  - [Heroku CI](#running-tests-on-heroku-ci)
  - [Travis CI](#running-tests-on-travis-ci)
  - [GitHub Actions](#running-tests-on-github-actions)
  - [Chipper CI](#running-tests-on-chipper-ci)
  

<a name="introduction"></a>

## 簡介

[Laravel Dusk](https://github.com/laravel/dusk) 提供了一個豐富、簡單易用的瀏覽器自動化與測試 API。預設情況下，使用 Dusk 不需要額外在本機電腦上安裝 JDK 或 Slenium。Dusk 會使用獨立的 [ChromeDriver](https://sites.google.com/chromium.org/driver) 安裝。不過，也可以自由使用其他 Selenium 相容的驅動器。

<a name="installation"></a>

## 安裝

要開始使用 Dusk，請先安裝 [Google Chrome](https://www.google.com/chrome)，並將 `laravel/dusk` Composer 相依性套件加到專案中：

```shell
composer require laravel/dusk --dev
```
> [!WARNING]  
> 若要手動註冊 Dusk 的 Service Provider，請**不要**在正式環境內加上該 Provider，因為這麼會讓所有人都能任意登入任何使用者。

安裝好 Dusk 套件後，請執行 `dusk:install` Artisan 指令。`dusk:install` 指令會建立 `tests/Browser` 目錄、一個 Dusk 範例測試、並安裝適用於你的作業系統的 Chrome Driver 二進位執行檔：

```shell
php artisan dusk:install
```
接著，請在專案的 `.env` 檔內設定 `APP_URL` 環境變數。該變數應符合要在瀏覽器內存取專案的 URL。

> [!NOTE]  
> 若使用 [Laravel Sail](/docs/{{version}}/sail) 來管理本機開發環境，也請一併參考 Sail 說明文件中有關[設定與執行 Dusk 測試](/docs/{{version}}/sail#laravel-dusk)的部分。

<a name="managing-chromedriver-installations"></a>

### 管理 ChromeDriver 安裝

若要安裝與 `dusk:install` 指令所安裝不同的 ChromeDriver 版本，可使用 `dusk:chrome-driver` 指令：

```shell
# Install the latest version of ChromeDriver for your OS...
php artisan dusk:chrome-driver

# Install a given version of ChromeDriver for your OS...
php artisan dusk:chrome-driver 86

# Install a given version of ChromeDriver for all supported OSs...
php artisan dusk:chrome-driver --all

# Install the version of ChromeDriver that matches the detected version of Chrome / Chromium for your OS...
php artisan dusk:chrome-driver --detect
```
> [!WARNING]  
> 要使用 Dusk，`chromedriver` 二進位執行檔必須可執行。若無法執行 Dusk，請通過下列指令確保該二進位執行檔可執行：`chmod -R 0755 vendor/laravel/dusk/bin/`。

<a name="using-other-browsers"></a>

### 使用其他瀏覽器

預設情況下，Dusk 會使用 Google Chrome 以及一個獨立的 [ChromeDriver](https://sites.google.com/chromium.org/driver) 安裝來執行瀏覽器測試。不過，可以自行開啟 Selenium 伺服器，並使用任何瀏覽器來執行測試。

要開始使用其他瀏覽器，請開啟 `tests/DuskTestCase.php` 檔。這個檔案是專案中所有 Dusk 測試的基礎測試類別。若在該檔案內移除 `startChromeDriver` 方法的呼叫，就可以讓 Dusk 不要自動開啟 ChromeDriver：

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     */
    public static function prepare(): void
    {
        // static::startChromeDriver();
    }
接著，可以修改 `driver` 方法來連先到所選的 URL 與連結埠。另外，也可以修改應傳給 WebDriver 的「Desired Capabilities (所需功能)」：

    use Facebook\WebDriver\Remote\RemoteWebDriver;
    
    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
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

```shell
php artisan dusk:make LoginTest
```
<a name="resetting-the-database-after-each-test"></a>

### Resetting the Database After Each Test

我們要寫的測試大部分都會使用到一些會從資料庫中取得資料的頁面。不過，Dusk 測試不應該使用 `RefreshDatabase` Trait。`RefreshDatabase` Trait 使用的是資料庫 Transaction，而在多個 HTTP 間是沒辦法使用 Trasaction 的。因此，有兩個替代方案：`DatabaseMigrations` Trait 與 `DatabaseTruncation` Trait。

<a name="reset-migrations"></a>

#### 使用資料庫 Migration

`DatabaseMigrations` Trait 會在每個測試前執行資料庫 Migration。不過，在各個測試前 Drop 資料表再重建一次通常會比 ^[Trauncate](%E6%88%AA%E6%96%B7) 資料表來得慢：

```php
<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;

uses(DatabaseMigrations::class);

//
```
```php
<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    use DatabaseMigrations;

    //
}
```
> [!WARNING]  
> 在記憶體內的 SQLite 資料庫無法在執行 Dusk 測試時使用。由於瀏覽器會在自己的處理程序內執行，因此將無法存取其他處理程序中在記憶體內的資料庫。

<a name="reset-truncation"></a>

#### 使用資料庫 Truncation

`DatabaseTruncation` Trait 會在第一個測試前執行資料庫 Migration，以確保資料庫資料表有被正確建立。接著，在之後的測試中，資料庫的資料表只會被 Truncate，這樣一來比起重新執行所有 Migration 來說會快很多：

```php
<?php

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;

uses(DatabaseTruncation::class);

//
```
```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    use DatabaseTruncation;

    //
}
```
預設情況下，這個 Trait 會 Truncate 除了 `migrations` 資料表以外的所有資料表。若要自定要 Truncate 的資料表，可以在測試類別上定義 `$tablesToTruncate` 屬性：

> [!NOTE]  
> If you are using Pest, you should define properties or methods on the base `DuskTestCase` class or on any class your test file extends.

    /**
     * Indicates which tables should be truncated.
     *
     * @var array
     */
    protected $tablesToTruncate = ['users'];
或者，也可以在測試類別上定義 `$exceptTables` 來指定在 Truncate 時要排除哪些資料表：

    /**
     * Indicates which tables should be excluded from truncation.
     *
     * @var array
     */
    protected $exceptTables = ['users'];
若要指定要 Truncate 資料表的資料庫連線，可在測試類別上定義 `$connectionsToTruncate` 屬性：

    /**
     * Indicates which connections should have their tables truncated.
     *
     * @var array
     */
    protected $connectionsToTruncate = ['mysql'];
若想在資料庫修剪 (Truncation) 進行前後執行程式碼，可在測試類別中定義 `beforeTruncatingDatabase` 或 `afterTruncatingDatabase` 方法：

    /**
     * Perform any work that should take place before the database has started truncating.
     */
    protected function beforeTruncatingDatabase(): void
    {
        //
    }
    
    /**
     * Perform any work that should take place after the database has finished truncating.
     */
    protected function afterTruncatingDatabase(): void
    {
        //
    }
<a name="running-tests"></a>

### 執行測試

若要執行瀏覽器測試，請執行 `dusk` Artisan 指令：

```shell
php artisan dusk
```
若在上次執行 `dusk` 指令時有測試失敗了，則可以通過 `dusk:fails` 指令來先重新執行失敗的測試以節省時間：

```shell
php artisan dusk:fails
```
The `dusk` command accepts any argument that is normally accepted by the Pest / PHPUnit test runner, such as allowing you to only run the tests for a given [group](https://docs.phpunit.de/en/10.5/annotations.html#group):

```shell
php artisan dusk --group=foo
```
> [!NOTE]  
> 若使用 [Laravel Sail](/docs/{{version}}/sail) 來管理本機開發環境，請參考 Sail 說明文件中有關[設定與執行 Dusk 測試](/docs/{{version}}/sail#laravel-dusk)的部分。

<a name="manually-starting-chromedriver"></a>

#### 手動啟動 ChromeDriver

預設情況下，Dusk 會自動嘗試開啟 ChromeDriver。若你所使用的系統無法自動開啟 ChromeDriver，則可以在執行 `dusk` 指令前手動啟動 ChromeDriver。若想手動啟動 ChromeDriver，則應先在 `test/DuskTestCase.php` 檔中將下列部分註解掉：

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     */
    public static function prepare(): void
    {
        // static::startChromeDriver();
    }
此外，若在 9515 連結埠以外的其他連結埠上開啟 ChromeDriver，則應在相同類別內修改 `driver` 方法以修改為相應的連結埠：

    use Facebook\WebDriver\Remote\RemoteWebDriver;
    
    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()
        );
    }
<a name="environment-handling"></a>

### 處理環境

若要在執行測試時強制讓 Dusk 使用自己的環境檔，請在專案根目錄下建立一個 `.env.dusk.{environment}` 檔案。舉例來說，若會在 `local` 環境下執行 `dusk`，請建立 `.env.dusk.local` 檔案。

執行測試時，Dusk 會備份 `.env` 檔，並將 Dusk 環境檔重新命名為 `.env`。測試完成後，會恢復原本的 `.env` 檔。

<a name="browser-basics"></a>

## 「瀏覽器」基礎

<a name="creating-browsers"></a>

### 建立瀏覽器

要開始使用瀏覽器，我們先來建立一個用來認證能否登入網站的測試。產生測試後，我們就可以修改該測試、前往登入頁、輸入帳號密碼、並點擊「登入」按鈕。要建立瀏覽器實體，可在 Dusk 測試內呼叫 `browser` 方法：

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;

uses(DatabaseMigrations::class);

test('basic example', function () {
    $user = User::factory()->create([
        'email' => 'taylor@laravel.com',
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Login')
                ->assertPathIs('/home');
    });
});
```
```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A basic browser test example.
     */
    public function test_basic_example(): void
    {
        $user = User::factory()->create([
            'email' => 'taylor@laravel.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/home');
        });
    }
}
```
如上所見，`browser` 方法接受一個閉包。Dusk 會自動將瀏覽器實體傳入該閉包內，瀏覽器實體是用來與網站互動以及用來進行 Assertion 的主要物件。

<a name="creating-multiple-browsers"></a>

#### 建立多個瀏覽器

有時候，我們需要建立多個瀏覽器來正確地進行測試。舉例來說，在測試與 WebSocket 互動的聊天畫面時可能會需要多個瀏覽器。若要建立多個瀏覽器，只需要將多個瀏覽器引數加到提供給 `browser` 方法的閉包上即可：

    $this->browse(function (Browser $first, Browser $second) {
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

`visit` 方法可用來在網站內導航到特定的 URI 上：

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
當測試失敗時，Dusk 會自動縮放瀏覽器視窗來符合其內容，以進行截圖。可以通過在測試內呼叫 `disableFitOnFailure` 方法來禁用此功能：

    $browser->disableFitOnFailure();
可以使用 `move` 方法來將瀏覽器視窗移動到畫面上的不同位置：

    $browser->move($x = 100, $y = 100);
<a name="browser-macros"></a>

### 瀏覽器 Macro

若想定義可在各個測試內重複使用的自訂瀏覽器方法，可使用 `Browser` 類別上的 `macro` 方法。通常來說，該方法應在某個 [Service Provider](/docs/{{version}}/providers) 的 `boot` 方法內呼叫：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\ServiceProvider;
    use Laravel\Dusk\Browser;
    
    class DuskServiceProvider extends ServiceProvider
    {
        /**
         * Register Dusk's browser macros.
         */
        public function boot(): void
        {
            Browser::macro('scrollToElement', function (string $element = null) {
                $this->script("$('html, body').animate({ scrollTop: $('$element').offset().top }, 0);");
    
                return $this;
            });
        }
    }
`macro` 方法接受一個名稱作為其第一個引數，以及閉包作為其第二個引數。當在 `Browser` 實體上以方法呼叫該 Macro 時，會執行該 Macro 的閉包：

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/pay')
                ->scrollToElement('#credit-card-details')
                ->assertSee('Enter Credit Card Details');
    });
<a name="authentication"></a>

### 登入認證

一般來說，我們會需要測試需要登入的頁面。可以使用 Dusk 的 `loginAs` 方法來避免每個測試都需要處理網站的登入畫面。`loginAs` 方法接受 Authenticatable Model 所關聯的主索引鍵，或是 Authenticatable Model 實體：

    use App\Models\User;
    use Laravel\Dusk\Browser;
    
    $this->browse(function (Browser $browser) {
        $browser->loginAs(User::find(1))
              ->visit('/home');
    });
> [!WARNING]  
> 使用 `loginAs` 方法後，在該檔案內所有的測試都將使用該使用者 Session。

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

    $browser->script('document.documentElement.scrollTop = 0');
    
    $browser->script([
        'document.body.scrollTop = 0',
        'document.documentElement.scrollTop = 0',
    ]);
    
    $output = $browser->script('return window.location.pathname');
<a name="taking-a-screenshot"></a>

### Taking a Screenshot

可以使用 `screenshot` 方法來截圖，並將截圖保存為給定的檔案名稱。所有的截圖都會保存在 `tests/Browser/screenshots` 目錄內：

    $browser->screenshot('filename');
`responsiveScreenshots` 方法可用來在各個 ^[Breakpoint](%E6%96%B7%E9%BB%9E) 上截取一系列的截圖：

    $browser->responsiveScreenshots('filename');
<a name="storing-console-output-to-disk"></a>

### Storing Console Output to Disk

可以使用 `storeConsoleLog` 方法來將目前瀏覽器的主控台輸出以給定的檔案名稱寫入到磁碟內。主控台輸出會保存在 `tests/Browser/console` 目錄內：

    $browser->storeConsoleLog('filename');
<a name="storing-page-source-to-disk"></a>

### Storing Page Source to Disk

可以使用 `storeSource` 方法來將目前頁面的原始碼以給定的檔案名稱寫入到磁碟內。頁面原始碼會保存在 `tests/Browser/source` 目錄內：

    $browser->storeSource('filename');
<a name="interacting-with-elements"></a>

## 與元素互動

<a name="dusk-selectors"></a>

### Dusk 選擇器

在撰寫 Dusk 測試時，選擇一個好的 CSS 選擇器來與元素互動是最難的一部分。日子一天天過去，當前端有更改時，若有像下列這樣的 CSS 選擇器就有可能讓測試失敗：

    // HTML...
    
    <button>Login</button>
    
    // Test...
    
    $browser->click('.login-page .container div > button');
使用 Dusk 選擇器，就能讓開發人員更專注於撰寫有效的測試，而不是記住 CSS 選擇器。若要定義選擇請，請在 HTML 元素內加上 `dusk` 屬性。接著，當與 Dusk 瀏覽器互動時，請在該選擇器前方加上 `@` 來在測試內操作該元素：

    // HTML...
    
    <button dusk="login-button">Login</button>
    
    // Test...
    
    $browser->click('@login-button');
若有需要，可以使用 `selectorHtmlAttribute` 方法來自定 Dusk Selector 使用的 HTML 屬性。一般來說，應在專案中 `AppServiceProvider` 內 `boot` 方法中呼叫該方法：

    use Laravel\Dusk\Dusk;
    
    Dusk::selectorHtmlAttribute('data-dusk');
<a name="text-values-and-attributes"></a>

### Text, Values, and Attributes

<a name="retrieving-setting-values"></a>

#### Retrieving and Setting Values

Dusk 內提供了數種可與目前頁面上元素的值、顯示文字、與屬性互動的方法。舉例來說，若要在某個符合給定 CSS 或 Dusk 選擇器的元素上取得該元素的「值 (Value)」，可使用 `value` 方法：

    // Retrieve the value...
    $value = $browser->value('selector');
    
    // Set the value...
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
請注意這裡，雖然可將 CSS 選擇器傳入 `type` 方法，但並不需特別傳入。若未提供 CSS 選擇器，則 Dusk 會搜尋符合給定 `name` 屬性的 `input` 或 `textarea` 欄位。

若要在不將其原本內容清除的情況下將文字附加在最後面，可以使用 `append` 方法：

    $browser->type('tags', 'foo')
            ->append('tags', ', bar, baz');
可以使用 `clear` 方法來清除某個 Input 的值：

    $browser->clear('email');
可以使用 `typeSlowly` 方法來讓 Dusk 輸入得慢一點。預設情況下，Dusk 會在每個按鍵間暫停 100 毫秒。若要自訂按鍵按下間的時間，可將適當的毫秒數作為第三個引數傳給該方法：

    $browser->typeSlowly('mobile', '+1 (202) 555-5555');
    
    $browser->typeSlowly('mobile', '+1 (202) 555-5555', 300);
可以使用 `appendSlowly` 方法來慢慢地將文字附加到最後：

    $browser->type('tags', 'foo')
            ->appendSlowly('tags', ', bar, baz');
<a name="dropdowns"></a>

#### 下拉選單

若要在 `select` 元素上選擇可用的值，可使用 `select` 方法。與 `type` 方法類似，`select` 方法並不要求要提供完整的 CSS 選擇器。將值傳給 `select` 方法時，應傳入底層的選項值而非顯示的文字：

    $browser->select('size', 'Large');
也可以通過省略第二個引數來隨機選擇選項：

    $browser->select('size');
在 `select` 方法的第二個引數中使用陣列，就可以選擇多個選項：

    $browser->select('categories', ['Art', 'Music']);
<a name="checkboxes"></a>

#### 多選框

若要「勾選」多選框，可使用 `check` 方法。與其他 Input 有關的方法類似，並不需要傳入完整的 CSS 選擇器。若找不到對應的 CSS 選擇器，Dusk 會自動搜尋符合 `name` 屬性的多選框：

    $browser->check('terms');
可使用 `uncheck` 方法來「取消勾選」多選框：

    $browser->uncheck('terms');
<a name="radio-buttons"></a>

#### 單選框

若要「勾選」`radio` 單選框，可使用 `check` 方法。與其他 Input 有關的方法類似，並不需要傳入完整的 CSS 選擇器。若找不到對應的 CSS 選擇器，Dusk 會自動搜尋符合 `name` 屬性的 `radio` 單選框：

    $browser->radio('size', 'large');
<a name="attaching-files"></a>

### 附加檔案

可使用 `attach` 方法來將檔案附加到 `file` Input 元素上。與其他 Input 有關的方法類似，並不需要傳入完整的 CSS 選擇器。若找不到對應的 CSS 選擇器，Dusk 會自動搜尋符合 `name` 屬性的 `file` Input：

    $browser->attach('photo', __DIR__.'/photos/mountains.png');
> [!WARNING]  
> 要使用 attach 函式，伺服器上必須有安裝 `Zip` PHP 擴充套件並已啟用。

<a name="pressing-buttons"></a>

### 按下按鈕

`press` 方法可用來點擊頁面上的按鈕元素。傳給 `press` 方法的引數可以是按鈕的顯示文字，也可以是 CSS / Dusk 選擇器：

    $browser->press('Login');
在送出表單時，許多網站會在按鈕按下的時候禁用表單的送出按鈕，並在表單送出的 HTTP 請求完成後重新啟用該按鈕。若要按下按鈕並等待該按鈕重新啟用，可使用 `pressAndWaitFor` 方法：

    // Press the button and wait a maximum of 5 seconds for it to be enabled...
    $browser->pressAndWaitFor('Save');
    
    // Press the button and wait a maximum of 1 second for it to be enabled...
    $browser->pressAndWaitFor('Save', 1);
<a name="clicking-links"></a>

### 點擊連結

若要點擊連結，可使用瀏覽器實體上的 `clickLink` 方法。`clickLink` 方法會點擊有給定顯示文字的連結：

    $browser->clickLink($linkText);
可使用 `seeLink` 方法來判斷給定的顯示文字是否在頁面上可見：

    if ($browser->seeLink($linkText)) {
        // ...
    }
> [!WARNING]  
> 該方法需要與 jQuery 互動。若頁面上沒有 jQuery 可用，則 Dusk 會自動將 jQuery 插入到頁面上以在測試期間使用。

<a name="using-the-keyboard"></a>

### Using the Keyboard

比起使用一般的 `type` 方法，`keys` 方法提供了可對給定元素進行一系列更複雜輸入的能力。舉例來說，可以讓 Dusk 在輸入數值的時候按著某個輔助按鍵。在這個範例中，於符合給定選擇器的元素內輸入 `taylor` 文字時，會按著 `Shift` 鍵。輸入完 `taylor` 後，`swift` 會在不按下任何輔助按鍵的情況下輸入：

    $browser->keys('selector', ['{shift}', 'taylor'], 'swift');
`keys` 方法的另一個實用用途是給主要 CSS 選擇器傳送一組「鍵盤快捷鍵」：

    $browser->keys('.app', ['{command}', 'j']);
> [!NOTE]  
> 所有的輔助按鍵，如 `{command}` 都以 `{}` 字元來進行包裝，且符合 `Facebook\WebDriver\WebDriverKeys` 中所定義的常數值。可[在 GitHub 上找到](https://github.com/php-webdriver/php-webdriver/blob/master/lib/WebDriverKeys.php)這些常數值。

<a name="fluent-keyboard-interactions"></a>

#### 流暢地使用鍵盤

Dusk 還提供了一個 `withKeyboard` 方法，讓你能使用 `Laravel\Dusk\Keyboard` 類別來流暢地進行複雜的鍵盤動作。`Keyboard` 類別提供了 `press`, `release`, `type` 與 `pause` 方法：

    use Laravel\Dusk\Keyboard;
    
    $browser->withKeyboard(function (Keyboard $keyboard) {
        $keyboard->press('c')
            ->pause(1000)
            ->release('c')
            ->type(['c', 'e', 'o']);
    });
<a name="keyboard-macros"></a>

#### 鍵盤巨集

若想定義可在各個測試內重複使用的自訂鍵盤動作，可使用 `Keyboard` 類別上的 `macro` 方法。一半來說，該方法應在某個 [Service Provider](/docs/{{version}}/providers) 的 `boot` 方法內呼叫：

    <?php
    
    namespace App\Providers;
    
    use Facebook\WebDriver\WebDriverKeys;
    use Illuminate\Support\ServiceProvider;
    use Laravel\Dusk\Keyboard;
    use Laravel\Dusk\OperatingSystem;
    
    class DuskServiceProvider extends ServiceProvider
    {
        /**
         * Register Dusk's browser macros.
         */
        public function boot(): void
        {
            Keyboard::macro('copy', function (string $element = null) {
                $this->type([
                    OperatingSystem::onMac() ? WebDriverKeys::META : WebDriverKeys::CONTROL, 'c',
                ]);
    
                return $this;
            });
    
            Keyboard::macro('paste', function (string $element = null) {
                $this->type([
                    OperatingSystem::onMac() ? WebDriverKeys::META : WebDriverKeys::CONTROL, 'v',
                ]);
    
                return $this;
            });
        }
    }
`macro` 方法接受一個名稱作為其第一個引數，以及閉包作為其第二個引數。當在 `Keyboard` 實體上以方法呼叫該 Macro 時，會執行該 Macro 的閉包：

    $browser->click('@textarea')
        ->withKeyboard(fn (Keyboard $keyboard) => $keyboard->copy())
        ->click('@another-textarea')
        ->withKeyboard(fn (Keyboard $keyboard) => $keyboard->paste());
<a name="using-the-mouse"></a>

### Using the Mouse

<a name="clicking-on-elements"></a>

#### Clicking on Elements

可使用 `click` 方法來點擊符合給定 CSS 或 Dusk 選擇器的元素：

    $browser->click('.selector');
可使用 `clickAtXPath` 方法來點擊符合給定 XPath 運算式的元素：

    $browser->clickAtXPath('//div[@class = "selector"]');
可使用 `clickAtPoint` 方法來點擊在相對於瀏覽器檢視區域上，符合給定座標點上最上層的元素：

    $browser->clickAtPoint($x = 0, $y = 0);
可使用 `doubleClick` 方法來模擬使用滑鼠點兩下：

    $browser->doubleClick();
    
    $browser->doubleClick('.selector');
可使用 `rightClick` 方法來模擬按滑鼠右鍵：

    $browser->rightClick();
    
    $browser->rightClick('.selector');
可使用 `clickAndHold` 方法來模擬按下滑鼠按鈕並保持按下。若接著呼叫 `releaseMouse` 方法，則會取消這個行為並放開滑鼠按鈕：

    $browser->clickAndHold('.selector');
    
    $browser->clickAndHold()
            ->pause(1000)
            ->releaseMouse();
`controlClick` 方法可用來在瀏覽器上模擬 `ctrl+click` 事件：

    $browser->controlClick();
    
    $browser->controlClick('.selector');
<a name="mouseover"></a>

#### 滑鼠移至上方

當需要將滑鼠移至符合給定 CSS 或 Dusk 選擇器的元素上時，可使用 `mouseover` 方法：

    $browser->mouseover('.selector');
<a name="drag-drop"></a>

#### Drag and Drop

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

Dusk 提供了多種與 JavaScript 對話方塊互動的方法。舉例來說，可以使用 `waitForDialog` 方法來等待 JavaScript 對話方塊出現。該方法可接收一個可選的引數來判斷要等幾秒讓該對話方塊顯示出來：

    $browser->waitForDialog($seconds = null);
可使用 `assertDialogOpened` 方法來判斷某個對話方塊是否已顯示，且包含給定的訊息：

    $browser->assertDialogOpened('Dialog message');
若該 JavaScript 對話方塊包含輸入提示，可使用 `typeInDialog` 方法來在該提示中輸入數值：

    $browser->typeInDialog('Hello World');
若要點擊「確定」按鈕來關閉開啟的 JavaScript 對話方塊，可以叫用 `acceptDialog` 方法：

    $browser->acceptDialog();
若要點擊「取消」按鈕來關閉開啟的 JavaScript 對話方塊，可以叫用 `dismissDialog` 方法：

    $browser->dismissDialog();
<a name="interacting-with-iframes"></a>

### 處理 IFrame

若有需要操作 iframe 中的元素，可以使用 `withinFrame` 方法。在提供給 `withinFrame` 方法的 Closure 中，所有的元素互動都會被限制在指定 iframe 的範圍內：

    $browser->withinFrame('#credit-card-details', function ($browser) {
        $browser->type('input[name="cardnumber"]', '4242424242424242')
            ->type('input[name="exp-date"]', '12/24')
            ->type('input[name="cvc"]', '123');
        })->press('Pay');
    });
<a name="scoping-selectors"></a>

### 區域性選擇器

有的時候，我們可能會想把多個操作限制到某個特定選擇器裡面。舉例來說，我們在判斷某段文字是否有出現時，可能只想在某個表格內檢查，並在檢查完畢後接著在該表格內點擊某個按鈕。可以使用 `with` 方法來達成。在提供給 `with` 方法的閉包內所進行的操作都會被限制在某個選擇器之內：

    $browser->with('.table', function (Browser $table) {
        $table->assertSee('Hello World')
              ->clickLink('Delete');
    });
某些時候，我們可能需要在目前的 Scope 外執行 Assertion。可以使用 `elsewhere` 與 `elsewhereWhenAvailable` 方法來進行：

     $browser->with('.table', function (Browser $table) {
        // Current scope is `body .table`...
    
        $browser->elsewhere('.page-title', function (Browser $title) {
            // Current scope is `body .page-title`...
            $title->assertSee('Hello World');
        });
    
        $browser->elsewhereWhenAvailable('.page-title', function (Browser $title) {
            // Current scope is `body .page-title`...
            $title->assertSee('Hello World');
        });
     });
<a name="waiting-for-elements"></a>

### Waiting for Elements

在測試使用了大量 JavaScript 的網站時，常常會需要「等待」特定元素或資料出現後才能繼續進行測試。在 Dusk 中可以輕鬆做到。只需要使用幾個方法，就可以等待元素顯示在頁面上，或是等待某個給定的 JavaScript 運算式取值變為 `true`。

<a name="waiting"></a>

#### 等待

若只是需要將測試暫停幾毫秒，可使用 `pause` 方法：

    $browser->pause(1000);
若只想在某個給定條件為 `true` 時暫停測試，可使用 `pauseIf` 方法：

    $browser->pauseIf(App::environment('production'), 1000);
類似的，若只想在某個給定條件不為 `true` 時暫停測試，可使用 `pauseUnless` 方法：

    $browser->pauseUnless(App::environment('testing'), 1000);
<a name="waiting-for-selectors"></a>

#### Waiting for Selectors

`waitFor` 方法可用來暫停執行測試，並等到符合給定 CSS 或 Dusk 選擇器的元素顯示在頁面上。預設情況下，該方法會最多會暫停測試五秒，超過則會擲回例外。若有需要，可以將自訂的逾時閥值傳入為該方法的第二個引數：

    // Wait a maximum of five seconds for the selector...
    $browser->waitFor('.selector');
    
    // Wait a maximum of one second for the selector...
    $browser->waitFor('.selector', 1);
也可以等待某個符合給定選擇器的元素出現給定文字：

    // Wait a maximum of five seconds for the selector to contain the given text...
    $browser->waitForTextIn('.selector', 'Hello World');
    
    // Wait a maximum of one second for the selector to contain the given text...
    $browser->waitForTextIn('.selector', 'Hello World', 1);
也可以等待某個符合給定選擇器的元素消失在頁面上：

    // Wait a maximum of five seconds until the selector is missing...
    $browser->waitUntilMissing('.selector');
    
    // Wait a maximum of one second until the selector is missing...
    $browser->waitUntilMissing('.selector', 1);
或者，也可以等待給定的選擇器為 Enabled 或 Disabled：

    // Wait a maximum of five seconds until the selector is enabled...
    $browser->waitUntilEnabled('.selector');
    
    // Wait a maximum of one second until the selector is enabled...
    $browser->waitUntilEnabled('.selector', 1);
    
    // Wait a maximum of five seconds until the selector is disabled...
    $browser->waitUntilDisabled('.selector');
    
    // Wait a maximum of one second until the selector is disabled...
    $browser->waitUntilDisabled('.selector', 1);
<a name="scoping-selectors-when-available"></a>

#### 可用時進入選擇器的 Scope

有時候我們可能會想等待符合給定選擇器的元素出現在頁面上後再接著與該元素互動。舉例來說，我們可能會想等待某個 Modal 視窗出現，然後在該 Modal 內點擊「OK」按鈕。可以使用 `whenAvailable` 方法來完成。在給定閉包內進行的所有元素操作都會被限制在原始選擇器的作用範圍內：

    $browser->whenAvailable('.modal', function (Browser $modal) {
        $modal->assertSee('Hello World')
              ->press('OK');
    });
<a name="waiting-for-text"></a>

#### Waiting for Text

可使用 `waitForText` 方法來等待給定文字顯示在頁面上：

    // Wait a maximum of five seconds for the text...
    $browser->waitForText('Hello World');
    
    // Wait a maximum of one second for the text...
    $browser->waitForText('Hello World', 1);
可以使用 `waitUntilMissingText` 方法來等待某個正在顯示的文字從頁面上移除：

    // Wait a maximum of five seconds for the text to be removed...
    $browser->waitUntilMissingText('Hello World');
    
    // Wait a maximum of one second for the text to be removed...
    $browser->waitUntilMissingText('Hello World', 1);
<a name="waiting-for-links"></a>

#### Waiting for Links

可使用 `waitForLink` 方法來等待給定連結文字顯示在頁面上：

    // Wait a maximum of five seconds for the link...
    $browser->waitForLink('Create');
    
    // Wait a maximum of one second for the link...
    $browser->waitForLink('Create', 1);
<a name="waiting-for-inputs"></a>

#### Waiting for Inputs

`waitForInput` 方法可用於等待給定輸入欄位顯示在頁面上：

    // Wait a maximum of five seconds for the input...
    $browser->waitForInput($field);
    
    // Wait a maximum of one second for the input...
    $browser->waitForInput($field, 1);
<a name="waiting-on-the-page-location"></a>

#### Waiting on the Page Location

在進行如 `$browser->assertPathIs('/home')` 這種路徑 Assertion 時，如果 `window.location.pathname` 是非同步更新的，則該 Assertion 可能會失敗。可以使用 `waitForLocation` 方法來等待路徑為給定的值：

    $browser->waitForLocation('/secret');
也可以使用 `waitForLocation` 方法來等待目前視窗的路徑符合完整的 URL：

    $browser->waitForLocation('https://example.com/path');
也可以等待 [命名路由](/docs/{{version}}/routing#named-routes) 的位置：

    $browser->waitForRoute($routeName, $parameters);
<a name="waiting-for-page-reloads"></a>

#### 等待頁面重新整理

若有需要在執行特定動作前等待頁面重新整理，請使用 `waitForReload` 方法：

    use Laravel\Dusk\Browser;
    
    $browser->waitForReload(function (Browser $browser) {
        $browser->press('Submit');
    })
    ->assertSee('Success!');
由於我們通常會在點擊按鈕後等待頁面重新整理，因此可以使用更方便的 `clickAndWaitForReload` 方法：

    $browser->clickAndWaitForReload('.selector')
            ->assertSee('something');
<a name="waiting-on-javascript-expressions"></a>

#### Waiting on JavaScript Expressions

有時候，我們可能會想暫停測試並等待某個給定的 JavaScript 運算式取值為 `true`。可使用 `waitUntil` 方法來輕鬆達成。將運算式傳給該方法時，不需要包含 `return` 關鍵字或結尾的分號：

    // Wait a maximum of five seconds for the expression to be true...
    $browser->waitUntil('App.data.servers.length > 0');
    
    // Wait a maximum of one second for the expression to be true...
    $browser->waitUntil('App.data.servers.length > 0', 1);
<a name="waiting-on-vue-expressions"></a>

#### Waiting on Vue Expressions

可使用 `waitUntilVue` 與 `waitUntilVueIsNot` 方法來等待給定的 [Vue 元件](https://vuejs.org) 屬性具有給定的值：

    // Wait until the component attribute contains the given value...
    $browser->waitUntilVue('user.name', 'Taylor', '@user');
    
    // Wait until the component attribute doesn't contain the given value...
    $browser->waitUntilVueIsNot('user.name', null, '@user');
<a name="waiting-for-javascript-events"></a>

#### Waiting for JavaScript Events

`waitForEvent` 方法可用來暫停執行測試，直到發生了某個 JavaScript 事件：

    $browser->waitForEvent('load');
會附加一個 Event Listener 到目前的 Scope 上，預設為 `body` 元素。在使用限定範圍的 Selector 時，則會將該 Event Listener 附加到符合的元素上：

    $browser->with('iframe', function (Browser $iframe) {
        // Wait for the iframe's load event...
        $iframe->waitForEvent('load');
    });
也可以使用 `waitForEvent` 方法的第二個引數來提供選擇器，以將 Event Listener 附加到特定的元素上：

    $browser->waitForEvent('load', '.selector');
也可以在 `document` 或 `window` 物件上等待事件：

    // Wait until the document is scrolled...
    $browser->waitForEvent('scroll', 'document');
    
    // Wait a maximum of five seconds until the window is resized...
    $browser->waitForEvent('resize', 'window', 5);
<a name="waiting-with-a-callback"></a>

#### Waiting With a Callback

在 Dusk 中，許多的「wait」方法都仰賴於底層的 `waitUsing` 方法。可以直接使用該方法來等待給定的閉包回傳 `true`。`waitUsing` 方法接受等待最大秒數、閉包取值的時間間隔、閉包、以及一個可選的錯誤訊息：

    $browser->waitUsing(10, 1, function () use ($something) {
        return $something->isReady();
    }, "Something wasn't ready in time.");
<a name="scrolling-an-element-into-view"></a>

### Scrolling an Element Into View

有時候，我們可能沒辦法點擊某個元素，因為該元素在瀏覽器可視區域外。使用 `scrollIntoView` 方法可以滾動瀏覽器視窗，直到給定選擇器元素出現在顯示區內：

    $browser->scrollIntoView('.selector')
            ->click('.selector');
<a name="available-assertions"></a>

## 可用的 Assertion

Dusk 提供了多種可對網站進行的 Assertion。下面列出了所有可用的 Assertion：

<style>
    .collection-method-list > p {
        columns: 10.8em 3; -moz-columns: 10.8em 3; -webkit-columns: 10.8em 3;
    }

    .collection-method-list a {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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
[assertIndeterminate](#assert-indeterminate)
[assertRadioSelected](#assert-radio-selected)
[assertRadioNotSelected](#assert-radio-not-selected)
[assertSelected](#assert-selected)
[assertNotSelected](#assert-not-selected)
[assertSelectHasOptions](#assert-select-has-options)
[assertSelectMissingOptions](#assert-select-missing-options)
[assertSelectHasOption](#assert-select-has-option)
[assertSelectMissingOption](#assert-select-missing-option)
[assertValue](#assert-value)
[assertValueIsNot](#assert-value-is-not)
[assertAttribute](#assert-attribute)
[assertAttributeContains](#assert-attribute-contains)
[assertAttributeDoesntContain](#assert-attribute-doesnt-contain)
[assertAriaAttribute](#assert-aria-attribute)
[assertDataAttribute](#assert-data-attribute)
[assertVisible](#assert-visible)
[assertPresent](#assert-present)
[assertNotPresent](#assert-not-present)
[assertMissing](#assert-missing)
[assertInputPresent](#assert-input-present)
[assertInputMissing](#assert-input-missing)
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
[assertVueDoesntContain](#assert-vue-doesnt-contain)

</div>
<a name="assert-title"></a>

#### assertTitle

判斷頁面標題符合給定文字：

    $browser->assertTitle($title);
<a name="assert-title-contains"></a>

#### assertTitleContains

判斷頁面標題包含給定文字：

    $browser->assertTitleContains($title);
<a name="assert-url-is"></a>

#### assertUrlIs

判斷目前 URL (不含查詢字串 Query String) 符合給定字串：

    $browser->assertUrlIs($url);
<a name="assert-scheme-is"></a>

#### assertSchemeIs

判斷目前 URL 的協定 (Scheme) 符合給定協定：

    $browser->assertSchemeIs($scheme);
<a name="assert-scheme-is-not"></a>

#### assertSchemeIsNot

判斷目前的 URL 協定 (Scheme) 不符合給定協定：

    $browser->assertSchemeIsNot($scheme);
<a name="assert-host-is"></a>

#### assertHostIs

判斷目前 URL 的主機名稱 (Host) 符合給定主機名稱：

    $browser->assertHostIs($host);
<a name="assert-host-is-not"></a>

#### assertHostIsNot

判斷目前 URL 的主機名稱 (Host) 不符合給定主機名稱：

    $browser->assertHostIsNot($host);
<a name="assert-port-is"></a>

#### assertPortIs

判斷目前 URL 的連接埠 (Port) 符合給定連接埠：

    $browser->assertPortIs($port);
<a name="assert-port-is-not"></a>

#### assertPortIsNot

判斷目前 URL 的連接埠 (Port) 不符合給定連接埠：

    $browser->assertPortIsNot($port);
<a name="assert-path-begins-with"></a>

#### assertPathBeginsWith

判斷目前 URL 的路徑 (Path) 以給定路徑開始：

    $browser->assertPathBeginsWith('/home');
<a name="assert-path-is"></a>

#### assertPathIs

判斷目前路徑 (Path) 符合給定路徑：

    $browser->assertPathIs('/home');
<a name="assert-path-is-not"></a>

#### assertPathIsNot

判斷目前路徑不符合給定路徑：

    $browser->assertPathIsNot('/home');
<a name="assert-route-is"></a>

#### assertRouteIs

判斷目前 URL 符合給定的 [命名路由](/docs/{{version}}/routing#named-routes) URL：

    $browser->assertRouteIs($name, $parameters);
<a name="assert-query-string-has"></a>

#### assertQueryStringHas

判斷查詢字串 (Query String) 有包含給定參數：

    $browser->assertQueryStringHas($name);
判斷查詢字串有包含給定參數，並符合給定的值：

    $browser->assertQueryStringHas($name, $value);
<a name="assert-query-string-missing"></a>

#### assertQueryStringMissing

判斷查詢字串 (Query String) 不包含給定的參數：

    $browser->assertQueryStringMissing($name);
<a name="assert-fragment-is"></a>

#### assertFragmentIs

判斷 URL 目前的雜湊片段 (Hash Fragment) 符合給定的片段：

    $browser->assertFragmentIs('anchor');
<a name="assert-fragment-begins-with"></a>

#### assertFragmentBeginsWith

判斷 URL 目前的雜湊片段 (Hash Fragment) 以給定的片段開始：

    $browser->assertFragmentBeginsWith('anchor');
<a name="assert-fragment-is-not"></a>

#### assertFragmentIsNot

判斷 URL 目前的雜湊片段 (Hash Fragment) 不符合給定的片段：

    $browser->assertFragmentIsNot('anchor');
<a name="assert-has-cookie"></a>

#### assertHasCookie

判斷 Cookie 中含有給定的加密 Cookie：

    $browser->assertHasCookie($name);
<a name="assert-has-plain-cookie"></a>

#### assertHasPlainCookie

判斷 Cookie 中含有給定的未加密 Cookie：

    $browser->assertHasPlainCookie($name);
<a name="assert-cookie-missing"></a>

#### assertCookieMissing

判斷 Cookie 中不包含給定的加密 Cookie：

    $browser->assertCookieMissing($name);
<a name="assert-plain-cookie-missing"></a>

#### assertPlainCookieMissing

判斷 Cookie 中不包含給定的未加密 Cookie：

    $browser->assertPlainCookieMissing($name);
<a name="assert-cookie-value"></a>

#### assertCookieValue

判斷加密 Cookie 為給定的值：

    $browser->assertCookieValue($name, $value);
<a name="assert-plain-cookie-value"></a>

#### assertPlainCookieValue

判斷未加密 Cookie 為給定的值：

    $browser->assertPlainCookieValue($name, $value);
<a name="assert-see"></a>

#### assertSee

判斷給定文字有出現在頁面上：

    $browser->assertSee($text);
<a name="assert-dont-see"></a>

#### assertDontSee

判斷給定文字未出現在頁面上：

    $browser->assertDontSee($text);
<a name="assert-see-in"></a>

#### assertSeeIn

判斷給定文字出現在選擇器中：

    $browser->assertSeeIn($selector, $text);
<a name="assert-dont-see-in"></a>

#### assertDontSeeIn

判斷給定文字未出現在選擇器中：

    $browser->assertDontSeeIn($selector, $text);
<a name="assert-see-anything-in"></a>

#### assertSeeAnythingIn

判斷選擇器中有包含任何文字：

    $browser->assertSeeAnythingIn($selector);
<a name="assert-see-nothing-in"></a>

#### assertSeeNothingIn

判斷選擇器中未包含任何文字：

    $browser->assertSeeNothingIn($selector);
<a name="assert-script"></a>

#### assertScript

判斷給定的 JavaScript 運算式取值為給定的值：

    $browser->assertScript('window.isLoaded')
            ->assertScript('document.readyState', 'complete');
<a name="assert-source-has"></a>

#### assertSourceHas

判斷給定的原始碼有出現在頁面上：

    $browser->assertSourceHas($code);
<a name="assert-source-missing"></a>

#### assertSourceMissing

判斷給定的原始碼未出現在頁面上：

    $browser->assertSourceMissing($code);
<a name="assert-see-link"></a>

#### assertSeeLink

判斷給定連結有出現在頁面上：

    $browser->assertSeeLink($linkText);
<a name="assert-dont-see-link"></a>

#### assertDontSeeLink

判斷給定連結未出現在頁面上：

    $browser->assertDontSeeLink($linkText);
<a name="assert-input-value"></a>

#### assertInputValue

判斷給定的輸入欄位為給定值：

    $browser->assertInputValue($field, $value);
<a name="assert-input-value-is-not"></a>

#### assertInputValueIsNot

判斷給定的輸入欄位不是給定值：

    $browser->assertInputValueIsNot($field, $value);
<a name="assert-checked"></a>

#### assertChecked

判斷給定多選況已勾選：

    $browser->assertChecked($field);
<a name="assert-not-checked"></a>

#### assertNotChecked

判斷給定多選況未勾選：

    $browser->assertNotChecked($field);
<a name="assert-indeterminate"></a>

#### assertIndeterminate

判斷給定 Checkbox 是否為 ^[Indeterminate](%E7%84%A1%E6%B3%95%E5%88%A4%E6%96%B7) 的狀態：

    $browser->assertIndeterminate($field);
<a name="assert-radio-selected"></a>

#### assertRadioSelected

判斷給定單選框欄位已選擇：

    $browser->assertRadioSelected($field, $value);
<a name="assert-radio-not-selected"></a>

#### assertRadioNotSelected

判斷給定單選框欄位未選擇：

    $browser->assertRadioNotSelected($field, $value);
<a name="assert-selected"></a>

#### assertSelected

判斷給定下拉選單已選擇給定值：

    $browser->assertSelected($field, $value);
<a name="assert-not-selected"></a>

#### assertNotSelected

判斷給定下拉選單未選擇給定值：

    $browser->assertNotSelected($field, $value);
<a name="assert-select-has-options"></a>

#### assertSelectHasOptions

判斷給定陣列中的值可被選取：

    $browser->assertSelectHasOptions($field, $values);
<a name="assert-select-missing-options"></a>

#### assertSelectMissingOptions

判斷給定陣列中的值不可被選取：

    $browser->assertSelectMissingOptions($field, $values);
<a name="assert-select-has-option"></a>

#### assertSelectHasOption

判斷給定值在給定欄位中可被選取：

    $browser->assertSelectHasOption($field, $value);
<a name="assert-select-missing-option"></a>

#### assertSelectMissingOption

判斷給定值不可被選取：

    $browser->assertSelectMissingOption($field, $value);
<a name="assert-value"></a>

#### assertValue

判斷符合給定選擇器的元素符合給定值：

    $browser->assertValue($selector, $value);
<a name="assert-value-is-not"></a>

#### assertValueIsNot

判斷符合給定選擇器的元素不符合給定值：

    $browser->assertValueIsNot($selector, $value);
<a name="assert-attribute"></a>

#### assertAttribute

判斷符合給定選擇器的元素中指定的屬性為給定值：

    $browser->assertAttribute($selector, $attribute, $value);
<a name="assert-attribute-contains"></a>

#### assertAttributeContains

判斷符合給定選擇器的元素中指定的屬性包含給定值：

    $browser->assertAttributeContains($selector, $attribute, $value);
<a name="assert-attribute-doesnt-contain"></a>

#### assertAttributeDoesntContain

Assert that the element matching the given selector does not contain the given value in the provided attribute:

    $browser->assertAttributeDoesntContain($selector, $attribute, $value);
<a name="assert-aria-attribute"></a>

#### assertAriaAttribute

判斷符合給定選擇器的元素中指定的 Aria 屬性為給定值：

    $browser->assertAriaAttribute($selector, $attribute, $value);
舉例來說，若有 `<button aria-label="Add">` 標記，則可像這樣判斷 `aria-label` 屬性：

    $browser->assertAriaAttribute('button', 'label', 'Add')
<a name="assert-data-attribute"></a>

#### assertDataAttribute

判斷符合給定選擇器的元素中指定的 Data 屬性為給定值：

    $browser->assertDataAttribute($selector, $attribute, $value);
舉例來說，若有 `<tr id="row-1" data-content="attendees"></tr>` 標記，則可像這樣判斷 `data-label` 屬性：

    $browser->assertDataAttribute('#row-1', 'content', 'attendees')
<a name="assert-visible"></a>

#### assertVisible

判斷符合給定選擇器的元素可見：

    $browser->assertVisible($selector);
<a name="assert-present"></a>

#### assertPresent

判斷符合給定選擇器的元素存在於原始碼中：

    $browser->assertPresent($selector);
<a name="assert-not-present"></a>

#### assertNotPresent

判斷符合給定選擇器的元素不存在於原始碼中：

    $browser->assertNotPresent($selector);
<a name="assert-missing"></a>

#### assertMissing

判斷符合給定選擇器的元素不可見：

    $browser->assertMissing($selector);
<a name="assert-input-present"></a>

#### assertInputPresent

判斷給定名稱的輸入欄位存在：

    $browser->assertInputPresent($name);
<a name="assert-input-missing"></a>

#### assertInputMissing

判斷給定名稱的輸入欄位不存在：

    $browser->assertInputMissing($name);
<a name="assert-dialog-opened"></a>

#### assertDialogOpened

判斷有給定訊息的 JavaScript 對話方塊開啟：

    $browser->assertDialogOpened($message);
<a name="assert-enabled"></a>

#### assertEnabled

判斷給定欄位啟用：

    $browser->assertEnabled($field);
<a name="assert-disabled"></a>

#### assertDisabled

判斷給定欄位禁用：

    $browser->assertDisabled($field);
<a name="assert-button-enabled"></a>

#### assertButtonEnabled

判斷給定按鈕啟用：

    $browser->assertButtonEnabled($button);
<a name="assert-button-disabled"></a>

#### assertButtonDisabled

判斷給定按鈕禁用：

    $browser->assertButtonDisabled($button);
<a name="assert-focused"></a>

#### assertFocused

判斷給定欄位已聚焦：

    $browser->assertFocused($field);
<a name="assert-not-focused"></a>

#### assertNotFocused

判斷給定欄位未聚焦：

    $browser->assertNotFocused($field);
<a name="assert-authenticated"></a>

#### assertAuthenticated

判斷使用者已登入：

    $browser->assertAuthenticated();
<a name="assert-guest"></a>

#### assertGuest

判斷使用者未登入：

    $browser->assertGuest();
<a name="assert-authenticated-as"></a>

#### assertAuthenticatedAs

判斷使用者已登入為給定使用者：

    $browser->assertAuthenticatedAs($user);
<a name="assert-vue"></a>

#### assertVue

在 Dusk 中，甚至可以對 [Vue 元件](https://vuejs.org) 資料的狀態進行 Assertion。舉例來說，假設網站中包含下列 Vue 元件：

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
則可像這樣判斷 Vue 元件的狀態：

```php
test('vue', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->assertVue('user.name', 'Taylor', '@profile-component');
    });
});
```
```php
/**
 * A basic Vue test example.
 */
public function test_vue(): void
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->assertVue('user.name', 'Taylor', '@profile-component');
    });
}
```
<a name="assert-vue-is-not"></a>

#### assertVueIsNot

判斷給定的 Vue 元件資料屬性不符合給定值：

    $browser->assertVueIsNot($property, $value, $componentSelector = null);
<a name="assert-vue-contains"></a>

#### assertVueContains

判斷給定的 Vue 元件資料屬性為陣列，並包含給定值：

    $browser->assertVueContains($property, $value, $componentSelector = null);
<a name="assert-vue-doesnt-contain"></a>

#### assertVueDoesntContain

判斷給定的 Vue 元件資料屬性為陣列，並且不包含給定值：

    $browser->assertVueDoesntContain($property, $value, $componentSelector = null);
<a name="pages"></a>

## Page

有時候，測試可能會需要按照順序執行多個複雜的動作。這樣一來可能會使測試難以閱讀與理解。通過 Dusk Page，便可定義描述性的動作，並以單一方法來在給定頁面上執行。使用 Page 還可為網站或單一頁面上常用的選擇器定義捷徑。

<a name="generating-pages"></a>

### 產生 Page

若要產生 Page 物件，請執行 `dusk:page` Artisan 指令。

    php artisan dusk:page Login
<a name="configuring-pages"></a>

### 設定 Page

預設情況下，Page 有三個方法：`url`、`assert`、與 `elements`。我們現在先來討論 `url` 與 `assert` 方法。[稍後會來詳細討論](#shorthand-selectors)有關 `elements` 方法。

<a name="the-url-method"></a>

#### `url` 方法

`url` 方法應回傳代表該 Page 的 URL 之路徑。在瀏覽器中前往該頁面時，Dusk 會使用該 URL：

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/login';
    }
<a name="the-assert-method"></a>

#### `assert` 方法

`assert` 方法可進行任意的 Assertion 判斷，來認證瀏覽器是否確實在該頁面上。該方法中不一定要有內容。不過，若有需要，可以自行進行 Assertion。這些 Assertion 會在前往該頁面後被自動執行：

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
    }
<a name="navigating-to-pages"></a>

### Navigating to Pages

Page 建立好後，就可以使用 `visit` 方法來前往該 Page：

    use Tests\Browser\Pages\Login;
    
    $browser->visit(new Login);
有時候，我們可能已經在某個給定頁面上，並且只需要將該頁面的選擇器與方法「載入」進目前的測試內容即可。常見的例子如通過點擊按鈕後跳轉至給定的頁面，而不是顯式前往該頁面。在此情況下，可使用 `on` 方法來載入頁面：

    use Tests\Browser\Pages\CreatePlaylist;
    
    $browser->visit('/dashboard')
            ->clickLink('Create Playlist')
            ->on(new CreatePlaylist)
            ->assertSee('@create');
<a name="shorthand-selectors"></a>

### 選擇器簡寫

Page 類別中的 `elements` 方法可讓你為頁面上的任意 CSS 選擇器定義快速、簡單好記的簡寫。舉例來說，來為網站登入頁的「email」輸入欄位定義捷徑：

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@email' => 'input[name=email]',
        ];
    }
定義好捷徑後，就可以在其他通常需要使用完整 CSS 選擇器的地方使用該選擇器簡寫：

    $browser->type('@email', 'taylor@laravel.com');
<a name="global-shorthand-selectors"></a>

#### 全域選擇器簡寫

安裝好 Dusk 後，`tests/Browser/Pages` 目錄下會包含一個基礎的 `Page` 類別。該類別包含了一個 `siteElements` 方法，可用來定義在網站中所有頁面都可用的全域選擇器簡寫：

    /**
     * Get the global element shortcuts for the site.
     *
     * @return array<string, string>
     */
    public static function siteElements(): array
    {
        return [
            '@element' => '#selector',
        ];
    }
<a name="page-methods"></a>

### Page 方法

除了 Page 中預設定義的方法外，也可以定義額外的方法來在測試中使用。舉例來說，假設我們正在製作一個音樂管理軟體。在該軟體中，建立播放清單可能會是個常見的動作。比起在每個測試中重複撰寫建立播放清單的邏輯，我們可以在 Page 類別內定義 `createPlaylist` 方法：

    <?php
    
    namespace Tests\Browser\Pages;
    
    use Laravel\Dusk\Browser;
    
    class Dashboard extends Page
    {
        // Other page methods...
    
        /**
         * Create a new playlist.
         */
        public function createPlaylist(Browser $browser, string $name): void
        {
            $browser->type('name', $name)
                    ->check('share')
                    ->press('Create Playlist');
        }
    }
定義好該方法後，就可以在任何使用該 Page 的測試中使用該方法。Browser 實體會自動作為第一個引數傳入給自訂 Page 方法內：

    use Tests\Browser\Pages\Dashboard;
    
    $browser->visit(new Dashboard)
            ->createPlaylist('My Playlist')
            ->assertSee('My Playlist');
<a name="components"></a>

## 元件

Component (元件) 與 Dusk 的「Page 物件」類似，不同的地方在於元件是用於一小部分的 UI，且在整個網站中都可重複使用。如：導航列或通知視窗。因此，元件並不限定於特定的 URL。

<a name="generating-components"></a>

### 產生 Component

若要產生 Component，請執行 `dusk:component` Artisan 指令。新建立的 Component 會放置於 `tests/Browser/Components` 目錄中：

    php artisan dusk:component DatePicker
像上面這樣，「date picker」是一個範例元件，該元件可能會在網站的各種頁面上出現。若要在測試套件中的數十個測試內手動轉寫瀏覽器自動化邏輯會很麻煩。因此，我們可以改用 Dusk Component 來代表 Date Picker，進而將此一邏輯封裝在該元件內：

    <?php
    
    namespace Tests\Browser\Components;
    
    use Laravel\Dusk\Browser;
    use Laravel\Dusk\Component as BaseComponent;
    
    class DatePicker extends BaseComponent
    {
        /**
         * Get the root selector for the component.
         */
        public function selector(): string
        {
            return '.date-picker';
        }
    
        /**
         * Assert that the browser page contains the component.
         */
        public function assert(Browser $browser): void
        {
            $browser->assertVisible($this->selector());
        }
    
        /**
         * Get the element shortcuts for the component.
         *
         * @return array<string, string>
         */
        public function elements(): array
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
         */
        public function selectDate(Browser $browser, int $year, int $month, int $day): void
        {
            $browser->click('@date-field')
                    ->within('@year-list', function (Browser $browser) use ($year) {
                        $browser->click($year);
                    })
                    ->within('@month-list', function (Browser $browser) use ($month) {
                        $browser->click($month);
                    })
                    ->within('@day-list', function (Browser $browser) use ($day) {
                        $browser->click($day);
                    });
        }
    }
<a name="using-components"></a>

### 使用 Component

定義好 Component 後，便可輕鬆地在任何測試內於 Date Picker 中選擇日期。而且，若選擇日期所需要的邏輯更改了，我們只需要更新 Component 即可：

```php
<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\DatePicker;

uses(DatabaseMigrations::class);

test('basic example', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->within(new DatePicker, function (Browser $browser) {
                    $browser->selectDate(2019, 1, 30);
                })
                ->assertSee('January');
    });
});
```
```php
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
     */
    public function test_basic_example(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->within(new DatePicker, function (Browser $browser) {
                        $browser->selectDate(2019, 1, 30);
                    })
                    ->assertSee('January');
        });
    }
}
```
<a name="continuous-integration"></a>

## 持續整合 (CI, Continuous Integration)

> [!WARNING]  
> 大多數的 Dusk CI 設定都假設你的 Laravel 應用程式放在連接埠 8000 的 PHP 內建開發伺服器上。因此，在繼續前，請先確保 CI 環境上有將 `APP_URL` 環境變數設為 `http://127.0.0.1:8000`。

<a name="running-tests-on-heroku-ci"></a>

### Heroku CI

若要在 [Heroku CI](https://www.heroku.com/continuous-integration) 上執行 Dusk 測試，請將下列 Google Chrome Buildpack 與指令嗎加到 Heroku 的 `app.json` 檔中：

    {
      "environments": {
        "test": {
          "buildpacks": [
            { "url": "heroku/php" },
            { "url": "https://github.com/heroku/heroku-buildpack-google-chrome" }
          ],
          "scripts": {
            "test-setup": "cp .env.testing .env",
            "test": "nohup bash -c './vendor/laravel/dusk/bin/chromedriver-linux > /dev/null 2>&1 &' && nohup bash -c 'php artisan serve --no-reload > /dev/null 2>&1 &' && php artisan dusk"
          }
        }
      }
    }
<a name="running-tests-on-travis-ci"></a>

### Travis CI

若要在 [Travis CI](https://travis-ci.org) 上執行 Dusk 測試，請使用下列 `.travis.yml` 設定檔。由於 Travis CI 並非圖形化環境，因此若要啟動 Chrome 瀏覽器，我們需要做一些額外的步驟。此外，我們會使用 `php artisan serve` 來啟動 PHP 的內建網頁伺服器：

```yaml
language: php

php:
  - 7.3

addons:
  chrome: stable

install:
  - cp .env.testing .env
  - travis_retry composer install --no-interaction --prefer-dist
  - php artisan key:generate
  - php artisan dusk:chrome-driver

before_script:
  - google-chrome-stable --headless --disable-gpu --remote-debugging-port=9222 http://localhost &
  - php artisan serve --no-reload &

script:
  - php artisan dusk
```
<a name="running-tests-on-github-actions"></a>

### GitHub Actions

若要使用 [GitHub Actions](https://github.com/features/actions) 來執行 Dusk 測試，可參考下列設定檔。與 TravisCI 一樣，我們會使用 `php artisan serve` 指令來啟動 PHP 的內建網頁伺服器：

```yaml
name: CI
on: [push]
jobs:

  dusk-php:
    runs-on: ubuntu-latest
    env:
      APP_URL: "http://127.0.0.1:8000"
      DB_USERNAME: root
      DB_PASSWORD: root
      MAIL_MAILER: log
    steps:
      - uses: actions/checkout@v4
      - name: Prepare The Environment
        run: cp .env.example .env
      - name: Create Database
        run: |
          sudo systemctl start mysql
          mysql --user="root" --password="root" -e "CREATE DATABASE \`my-database\` character set UTF8mb4 collate utf8mb4_bin;"
      - name: Install Composer Dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader
      - name: Generate Application Key
        run: php artisan key:generate
      - name: Upgrade Chrome Driver
        run: php artisan dusk:chrome-driver --detect
      - name: Start Chrome Driver
        run: ./vendor/laravel/dusk/bin/chromedriver-linux &
      - name: Run Laravel Server
        run: php artisan serve --no-reload &
      - name: Run Dusk Tests
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
```
<a name="running-tests-on-chipper-ci"></a>

### Chipper CI

若使用 [Chipper CI](https://chipperci.com) 來執行 Dusk 測試，則可使用下列設定檔作為起始點。我們會使用 PHP 的內建伺服器來執行 Laravel 以監聽 Request：

```yaml
# file .chipperci.yml
version: 1

environment:
  php: 8.2
  node: 16

# Include Chrome in the build environment
services:
  - dusk

# Build all commits
on:
   push:
      branches: .*

pipeline:
  - name: Setup
    cmd: |
      cp -v .env.example .env
      composer install --no-interaction --prefer-dist --optimize-autoloader
      php artisan key:generate
      
      # Create a dusk env file, ensuring APP_URL uses BUILD_HOST
      cp -v .env .env.dusk.ci
      sed -i "s@APP_URL=.*@APP_URL=http://$BUILD_HOST:8000@g" .env.dusk.ci

  - name: Compile Assets
    cmd: |
      npm ci --no-audit
      npm run build

  - name: Browser Tests
    cmd: |
      php -S [::0]:8000 -t public 2>server.log &
      sleep 2
      php artisan dusk:chrome-driver $CHROME_DRIVER
      php artisan dusk --env=ci
```
若要瞭解更多有關在 Chipper CI 上執行 Dusk 測試的資訊，包含如何使用資料庫等，請參考[官方的 Chipper CI 說明文件](https://chipperci.com/docs/testing/laravel-dusk-new/)。
