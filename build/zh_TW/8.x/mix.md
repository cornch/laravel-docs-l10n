---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/105/en-zhtw'
updatedAt: '2023-02-11T10:28:00Z'
contributors: {  }
progress: 61.21
---

# 編譯資源 (Mix)

- [簡介](#introduction)
- [安裝與設定](#installation)
- [執行 Mix](#running-mix)
- [處理樣式表](#working-with-stylesheets)
  - [Tailwind CSS](#tailwindcss)
  - [PostCSS](#postcss)
  - [Sass](#sass)
  - [URL 的處理](#url-processing)
  - [Source Map](#css-source-maps)
  
- [處理 JavaScript](#working-with-scripts)
  - [Vue](#vue)
  - [React](#react)
  - [拆分出第三方函式庫](#vendor-extraction)
  - [自訂 Webpack 設定](#custom-webpack-configuration)
  
- [版本化 / 避免快取](#versioning-and-cache-busting)
- [Browsersync 重新載入](#browsersync-reloading)
- [環境變數](#environment-variables)
- [通知](#notifications)

<a name="introduction"></a>

## 簡介

[Laravel Mix](https://github.com/JeffreyWay/laravel-mix) 是一個由 [Laracasts](https://laracasts.com) 作者 Jeffrey Way 開發的套件，該套件使用了各種常見的 CSS 與 JavaScript ^[預處理器](Pre-Processor)，可在 Laravel 專案上使用 Fluent API 來定義 [Webpack](https://webpack.js.org) 建置步驟。

換句話說，使用 Mix 就可以輕鬆地編譯並 ^[Minify](最小化 / 壓縮) 專案的 CSS 與 JavaScript 檔案。只要簡單地串接幾個方法，就可以流暢的定義^[素材管道](Asset Pipeline)。例如：

    mix.js('resources/js/app.js', 'public/js')
        .postCss('resources/css/app.css', 'public/css');
若你搞不懂怎麼用 Webpack 與編輯素材、或是覺得很複雜的話，你一定會喜歡 Laravel Mix 的。不過，不一定要使用 Laravel Mix 也能開發你的網站。你可以自由決定要使用哪個素材管道工具，甚至也可以不使用任何工具。

> [!TIP]  
> If you need a head start building your application with Laravel and [Tailwind CSS](https://tailwindcss.com), check out one of our [application starter kits](/docs/{{version}}/starter-kits).

<a name="installation"></a>

## 安裝與設定

<a name="installing-node"></a>

#### 安裝 Node

在執行 Mix 前，請先確保你的電腦上已安裝 Node.js 與 NPM：

    node -v
    npm -v
可以從 [Node 官方網站]中取得圖形界面安裝程式來輕鬆地安裝最新版的 Node 與 NPM。或者，如果你用的是 [Laravel Sail](/docs/{{version}}/sail)，可以像這樣在 Sail 上叫用 Node 與 NPM：

    ./sail node -v
    ./sail npm -v
<a name="installing-laravel-mix"></a>

#### 安裝 Laravel Mix

剩下的步驟就是安裝 Laravel Mix 了。在新安裝的 Laravel 專案中，可以在專案根目錄上看到一個 `package.json` 檔案。使用預設的 `package.json` 檔就可以直接開始使用 Laravel Mix 了。可以把這個檔案像成跟 `composer.json` 檔一樣，只不過 `package.json` 定義的是不是 PHP 的相依性套件而是 Node 的相依性套件。可以像這樣安裝 `package.json` 中參照的相依性套件：

    npm install
<a name="running-mix"></a>

## 執行 Mix

Mix 是基於 [Webpack](https://webpack.js.org) 的一個設定層。因此要執行 Mix 任務，只需要執行 Laravel 中預設 `package.json` 檔案內提供的其中一個 NPM ^[指令碼](Script)即可。執行 `dev` 或 `production` 指令碼後，專案中的所有 CSS 與 JavaScript 素材都會被編譯並放在專案的 `public` 目錄內：

    // Run all Mix tasks...
    npm run dev
    
    // Run all Mix tasks and minify output...
    npm run prod
<a name="watching-assets-for-changes"></a>

#### 監控素材的更改

`npm run watch` 指令會在終端機內持續執行，並^[監控](Watch)所有相關的 CSS 與 JavaScript 檔案有沒有被修改。若偵測到有檔案被更改過，Webpack 會自動重新編譯素材：

    npm run watch
對於一些特定的本機開發環境，Webpack 肯跟沒辦法偵測到檔案修改。若在你的系統上有這個狀況，請考慮使用 `watch-poll` 指令：

    npm run watch-poll
<a name="working-with-stylesheets"></a>

## 處理樣式表

你的專案中的 `webpack.mix.js` 就是用來編譯所有素材的入口。可以把這個檔案想成是對 [Webpack] 設定的^[包裝](Wrapper)。可以將多個 Mix 任務串連在一起，以定義要怎麼編譯素材。

<a name="tailwindcss"></a>

### Tailwind CSS

[Tailwind CSS](https://tailwindcss.com) 是一套現代的、^[Utility-First](Utility 優先)的框架，可在只接觸 HTML 的情況下製作出讓人驚艷的網站。我們來看看要怎麼在 Laravel 專案中搭配 Laravel Mix 使用 Tailwind CSS。首先，我們先使用 NPM 安裝 Tailwind，並產生 Tailwind 的設定檔：

    npm install
    
    npm install -D tailwindcss
    
    npx tailwindcss init
`init` 指令會產生一個 `tailwind.config.js` 檔案。在該檔案的 `content` 中，可用來定義專案中所有的 HTML 樣板、JavaScript 元件、以及其他包含 Tailwind Class 名稱的原始檔。在 `content` 中定義這些檔案的路徑後，Tailwind 才能在正式 CSS 組建中移除這些檔案未使用的 CSS Class：

```js
content: [
    './storage/framework/views/*.php',
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
],
```
接著，我們在專案的 `resources/css/app.css` 檔案中加上 Tailwind 的各個「^[Layer](%E5%B1%A4)」：

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```
設定好 Tailwind 的 Layer 後，就可以來修改專案的 `webpack.mix.js` 檔以編譯由 Tailwind 驅動的 CSS：

```js
mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
    ]);
```
最後，應在主要的 Layout 樣板中參考這個樣式表。大多數的專案都把主 Layout 保存在 `resources/views/layouts/app.blade.php` 中。此外，也請確保該樣板中有沒有加上^[回應式](Responsive) Viewport 的 `meta` 標籤：

```html
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="/css/app.css" rel="stylesheet">
</head>
```
<a name="postcss"></a>

### PostCSS

[PostCSS](https://postcss.org/) 是一個可用來變換 CSS 的強力工具。Laravel Mix 中已內建支援。預設情況下，Mix 使用了很流行的 [Autoprefixer](https://github.com/postcss/autoprefixer) 外掛來自動套用任何需要的 CSS3 ^[Vendor Prefix](%E5%BB%A0%E5%95%86%E5%89%8D%E7%BD%AE%E8%A9%9E)。不過，也可以自行增加專案所需要的其他外掛。

首先，請通過 NPM 安裝所需的外掛，然後把這些外掛放在呼叫 Mix `postCSS` 方法時提供的陣列裡面。`postCss` 方法接受的第一個引數是 CSS 檔的路徑，而第二個引數則是編譯好的檔案要放置的目錄：

    mix.postCss('resources/css/app.css', 'public/css', [
        require('postcss-custom-properties')
    ]);
或者，執行 `postCss` 時也可以不提供任何外掛，來做簡單的 CSS 編譯與 Minify：

    mix.postCss('resources/css/app.css', 'public/css');
<a name="sass"></a>

### Sass

`sass` 方法可用來把 [Sass](https://sass-lang.com/) 編譯瀏覽器看得懂的 CSS。`sass` 方法接受 Sass 檔的路徑作為其第一個引數，以及編譯好的檔案要放置的目錄作為其第二個引數：

    mix.sass('resources/sass/app.scss', 'public/css');
可以呼叫多次 `sass` 方法來將多個 Sass 檔編譯到各個對應的 CSS 檔上，並設定產生 CSS 的輸出目錄：

    mix.sass('resources/sass/app.sass', 'public/css')
        .sass('resources/sass/admin.sass', 'public/css/admin');
<a name="url-processing"></a>

### URL 的處理

由於 Laravel Mix 是基於 Webpack 製作的，因此我們最好也要瞭解一些 Webpack 的概念。在做 CSS 編譯時，Webpack 會複寫並最佳化樣式表中的 `url()` 呼叫。雖然一聽到這個狀況，感覺可能有點奇怪，但其實這時很實用的功能。我們先假設我們像編譯一個 Sass 檔，裡面包含了相對網址的圖片：

    .example {
        background: url('../images/example.png');
    }
> [!NOTE]  
> Absolute paths for any given `url()` will be excluded from URL-rewriting. For example, `url('/images/thing.png')` or `url('http://example.com/images/thing.png')` won't be modified.

預設情況下，Laravel Mix 與 Webpack 會找到 `example.png` 這個檔案，並將其複製到 `public/images` 資料夾中，然後在產生的樣式表中複寫掉 `url()`。因此，編譯過的 CSS 會是這樣：

    .example {
        background: url(/images/example.png?d41d8cd98f00b204e9800998ecf8427e);
    }
雖然這個功能可能很實用，但你可能已經依照你的需求設定好資料夾結構了。這時，我們可以像這樣禁用 `url()` 複寫：

    mix.sass('resources/sass/app.scss', 'public/css').options({
        processCssUrls: false
    });
在 `webpack.mix.js` 中這樣加上之後，Mix 就不會再尋找 `url()` 並幫你把素材複製到 public 目錄下了。換句話說，編譯好的 CSS 會跟你原本寫的一樣：

    .example {
        background: url("../images/thing.png");
    }
<a name="css-source-maps"></a>

### Source Map

雖然預設情況下沒有啟用 Source Map，但可以在 `webpack.mix.js` 檔案中呼叫 `mix.sourceMaps()` 方法來啟用。雖然這樣一來，編譯 / 效能的成本也會增加，但有了 Source Map，就算使用經過編譯的素材，也能為瀏覽器開發人員工具提供額外的偵錯資訊：

    mix.js('resources/js/app.js', 'public/js')
        .sourceMaps();
<a name="style-of-source-mapping"></a>

#### Source Map 的格式

Webpack 提供了多種 [Source Map 格式](https://webpack.js.org/configuration/devtool/#devtool)。預設情況下，Mix 的 Source Map 格式設為 `eval-source-map`，使用這種格式時建置時間會比較快。若想更改 Source Map 的格式，可使用 `sourceMaps` 方法：

    let productionSourceMaps = false;
    
    mix.js('resources/js/app.js', 'public/js')
        .sourceMaps(productionSourceMaps, 'source-map');
<a name="working-with-scripts"></a>

## 處理 JavaScript

Mix 提供了多種可協助你處理 JavaScript 檔案的功能，如編譯現代的 ECMAScript、^[打包模組](Module Bundling)、Minify、合併多個純 JavaScript 檔案⋯⋯等。更好的是，這些功能全部都可流暢地互相配合使用，完全不需額外設定：

    mix.js('resources/js/app.js', 'public/js');
只要這一行程式碼，就可以使用：

<div class="content-list" markdown="1">
- 最新的 ECMAScript 語法
- 模組 (Module)
- 為正式環境 Minify 原始碼

</div>
<a name="vue"></a>

### Vue

使用 `vue` 方法時，Mix 會自動安裝支援 Vue ^[單檔案元件](Single-File Component)所需的 Babel 外掛。不需要其他進一步的設定：

    mix.js('resources/js/app.js', 'public/js')
       .vue();
編譯好 JavaScript 後，就可以在專案中參照這個檔案：

```html
<head>
    <!-- ... -->

    <script src="/js/app.js"></script>
</head>
```
<a name="react"></a>

### React

Mix 會自動安裝支援 React 所需的 Babel 外掛。要開始使用 React，請呼叫 `react` 方法：

    mix.js('resources/js/app.jsx', 'public/js')
       .react();
Mix 會在幕後下載並包含適當的 `babel-preset-react` Babel 外掛。編譯好 JavaScript 後，就可以像這樣在專案內參照該檔案：

```html
<head>
    <!-- ... -->

    <script src="/js/app.js"></script>
</head>
```
<a name="vendor-extraction"></a>

### 拆分出第三方函式庫

將所有用在專案內的 JavaScript 跟一些^[第三方函式庫](Vendor Library) (如 React 或 Vue) 一起打包可能會有個缺點，就是我們很難做長期的快取。舉例來說，只要更新專案內的一部分程式碼，就算沒更改第三方函式庫，瀏覽器還是必須重新下載整個第三方函式庫。

若常常更改專案的 JavaScript，應考慮將第三方函式庫拆分成獨立的檔案。這樣一來，更改專案的程式碼就不會影響 `vendor.js` 這個大檔案的快取。通過 Mix 的 `extract` 方法就可輕鬆實現：

    mix.js('resources/js/app.js', 'public/js')
        .extract(['vue'])
`extract` 方法接收一組包含要拆分為獨立 `vendor.js` 檔案的函式庫或模組陣列。使用上述範例中的這個程式碼片段，Mix 會產生下列檔案：

<div class="content-list" markdown="1">
- `public/js/manifest.js`: *Webpack Manifest Runtime*
- `public/js/vendor.js`: *第三方函式庫*
- `public/js/app.js`: *專案程式碼*

</div>
為了避免產生 JavaScript 錯誤，請確保使用正確的順序載入這些檔案：

    <script src="/js/manifest.js"></script>
    <script src="/js/vendor.js"></script>
    <script src="/js/app.js"></script>
<a name="custom-webpack-configuration"></a>

### 自訂 Webpack 設定

有時候，我們可能會需要手動修改底層的 Webpack 設定。舉例來說，我們可能會想參照一個特別的 ^[Loader](%E8%BC%89%E5%85%A5%E7%A8%8B%E5%BC%8F)或外掛。

Mix 提供了一個實用的 `webpackConfig` 方法，能讓我們合併部分的 Webpack 設定。這樣做的好處是我們就不需要複製並維護一個完整的 `webpack.config.js`。`webpackConfig` 方法接受一個物件，該物件中應包含我們要套用的 [Webpack 設定](https://webpack.js.org/configuration/)。

    mix.webpackConfig({
        resolve: {
            modules: [
                path.resolve(__dirname, 'vendor/laravel/spark/resources/assets/js')
            ]
        }
    });
<a name="versioning-and-cache-busting"></a>

## 版本化 / 避免快取

許多的開發人員都會在編譯過的素材檔名後方加上時戳或一些不重複的字串來讓瀏覽器不要載入舊版的程式碼，強制載入新的素材。使用 `version` 方法，就可以讓 Mix 自動處理這個部分。

`version` 方法會在所有編譯的檔案名稱後方加上一段不重複的雜湊，讓我們能更方便地避免檔案被快取：

    mix.js('resources/js/app.js', 'public/js')
        .version();
產生好版本化的檔案後，因為我們不知道實際的檔案名稱，所以可使用 Laravel 的 `mix` 全域函式來在 [View](/docs/{{version}}/views) 載入有加上適當雜湊的素材。`mix` 韓式會自動判斷有雜湊的檔案目前的名稱：

    <script src="{{ mix('/js/app.js') }}"></script>
由於在開發的時候通常不需要使用版本化的檔案，因此我們可以設定讓 Mix 只在 `npm run prod` 時才做版本化：

    mix.js('resources/js/app.js', 'public/js');
    
    if (mix.inProduction()) {
        mix.version();
    }
<a name="custom-mix-base-urls"></a>

#### 自訂 Mix 的基礎 URL

若要將 Mix 編譯的素材部署到與你的網站不同的 CDN 上，則需要修改 `mix` 函式產生的^[基礎 URL](Base URL)。只需要在專案的 `config/app.php` 設定檔中加上 `mix_url` 設定即可：

    'mix_url' => env('MIX_ASSET_URL', null)
設定好 Mix URL 後，`mix` 函式在產生素材網址的時候，就會在前方加上剛才設定的 URL：

```bash
https://cdn.example.com/js/app.js?id=1964becbdd96414518cd
```
<a name="browsersync-reloading"></a>

## Browsersync 重新整理

[BrowserSync](https://browsersync.io/) 可以自動偵測檔案更改，並在不需手動重新整理的情況下將這些修改插入到瀏覽器內。可以呼叫 `mix.browserSync()` 方法來啟用 BrowserSync 支援：

```js
mix.browserSync('laravel.test');
```
可以傳入 JavaScript 物件給 `browserSync` 方法來指定 [BrowserSync選項](https://browsersync.io/docs/options)：

```js
mix.browserSync({
    proxy: 'laravel.test'
});
```
接著，使用 `npm run watch` 指令來開啟 Webpack 的開發伺服器。之後，當我們修改了監聽的 JavaScript 或 PHP 檔案時，瀏覽器就會即時重新整理頁面，並反映出所做的更改。

<a name="environment-variables"></a>

## 環境變數

只要在 `.env` 檔案中定義的環境變數名稱前方加上 `MIX_`，就可以將這些環境變數插入到 `webpack.mix.js` 檔案中：

    MIX_SENTRY_DSN_PUBLIC=http://example.com
在 `.env` 檔案中定義好環境變數後，就可以使用 `process.enb` 物件來存取這個變數。不過，若在任務執行時更改環境變數，則可能需要重新啟動該任務才能反映出更改過的值：

    process.env.MIX_SENTRY_DSN_PUBLIC
<a name="notifications"></a>

## 通知

當可以顯示通知時，Mix 會自動在編譯的時候顯示系統通知，能讓你即時了解到是否有編譯成功。不過，有的情況下我們可能會想禁用通知。這種情況包含在正式環境伺服器上執行 Mix 時。可以使用 `disableNotifications` 方法來禁用通知：

    mix.disableNotifications();