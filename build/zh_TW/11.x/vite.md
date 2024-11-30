---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/180/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 50.17
---

# 打包素材 (Vite)

- [簡介](#introduction)
- [安裝與設定](#installation)
  - [安裝 Node](#installing-node)
  - [Installing Vite and the Laravel Plugin](#installing-vite-and-laravel-plugin)
  - [設定 Vite](#configuring-vite)
  - [Loading Your Scripts and Styles](#loading-your-scripts-and-styles)
  
- [執行 Vite](#running-vite)
- [處理 JavaScript](#working-with-scripts)
  - [別名](#aliases)
  - [Vue](#vue)
  - [React](#react)
  - [Inertia](#inertia)
  - [URL 的處理](#url-processing)
  
- [處理 CSS](#working-with-stylesheets)
- [Working With Blade and Routes](#working-with-blade-and-routes)
  - [使用 Vite 處理靜態素材](#blade-processing-static-assets)
  - [Refreshing on Save](#blade-refreshing-on-save)
  - [別名](#blade-aliases)
  
- [Asset Prefetching](#asset-prefetching)
- [自定 Base URL](#custom-base-urls)
- [環境變數](#environment-variables)
- [Disabling Vite in Tests](#disabling-vite-in-tests)
- [伺服器端轉譯 (SSR)](#ssr)
- [Script and Style Tag Attributes](#script-and-style-attributes)
  - [Content Security Policy (CSP) Nonce](#content-security-policy-csp-nonce)
  - [Subresource Integrity (SRI)](#subresource-integrity-sri)
  - [任意屬性](#arbitrary-attributes)
  
- [進階客製化](#advanced-customization)
  - [修正開發伺服器的 URL](#correcting-dev-server-urls)
  

<a name="introduction"></a>

## 簡介

[Vite](https://vitejs.dev) 是現代化的前端建置工具，提供快速的開發環境，並可快速為正式環境打包程式碼。在使用 Laravel 製作程式時，我們通常會用 Vite 來將專案的 CSS 與 JavaScript 檔案打包成可在正式環境使用的資源。

Laravel 與 Vite 進行了無縫整合，並提供了官方的外掛程式以及 Blade 指示詞來讓在開發環境與正式環境上載入資源。

> [!NOTE]  
> 已經在使用 Laravel Mix 了嗎？在新安裝的 Laravel 中，Vite 已經取代了 Laravel Mix。若要瀏覽 Mix 的說明文件，請瀏覽 [Laravel Mix 網站](https://laravel-mix.com/)。若要切換至 Vite，請檢視我們的 [Vite 遷移指南](https://github.com/laravel/vite-plugin/blob/main/UPGRADE.md#migrating-from-laravel-mix-to-vite)。

<a name="vite-or-mix"></a>

#### Choosing Between Vite and Laravel Mix

在改用 Vite 前，新的 Laravel 專案使用的都是 [Mix](https://laravel-mix.com/)。Mix 在打包資源時使用的是 [webpack](https://webpack.js.org/)。Vite 則重點在為大量使用 JavaScript 的專案提供更快速更有生產力的環境。若要開發的專案有使用 SPA  (單頁應用程式，Single Page Application)，甚至可能還是使用了 [Inertia](https://inertiajs.com) 等工具來開發時，則最適合使用 Vite。

若在開發的專案是一些只使用 JavaScript 來「點綴」的傳統伺服器端轉譯的專案，或是使用 [Livewire](https://livewire.laravel.com) 時，也可以使用 Vite。不過，有一些功能是只有 Laravel Mix 支援而 Vite 不支援的，例如將一些資源直接複製到建置結果中，並且不直接在 JavaScript 程式中參照這些資源。

<a name="migrating-back-to-mix"></a>

#### Migrating Back to Mix

剛開始建立新 Laravel 專案，但用到了 Vite Scaffolding 而需要遷移回 Laravel Mix 與 Webpack 嗎？沒問題。請參考我們的[官方 Vite 至 Mix 遷移指南 (英語)](https://github.com/laravel/vite-plugin/blob/main/UPGRADE.md#migrating-from-vite-to-laravel-mix)。

<a name="installation"></a>

## 安裝與設定

> [!NOTE]  
> **注意** 下列文件討論的是如何手動安裝與設定 Laravel Vite 外掛。不過，Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了所有的 Vite Scaffolding。這些入門套件是要開始使用 Laravel 與 Vite 最快的方法。

<a name="installing-node"></a>

### 安裝 Node

請確定有安裝 Node.js (16 版以上)與 NPM，才能開始執行 Vite 與 Laravel 的外掛：

```sh
node -v
npm -v
```
可以從 [Node 官方網站](https://nodejs.org/en/download/)中取得圖形界面安裝程式來輕鬆地安裝最新版的 Node 與 NPM。或者，如果你用的是 [Laravel Sail](/docs/{{version}}/sail)，可以像這樣在 Sail 上叫用 Node 與 NPM：

```sh
./vendor/bin/sail node -v
./vendor/bin/sail npm -v
```
<a name="installing-vite-and-laravel-plugin"></a>

### Installing Vite and the Laravel Plugin

在新安裝的 Laravel 中，可以看到專案根目錄下有個 `package.json`。預設的 `package.json` 檔案已包含了所有開始使用 Vite 與 Laravel Vite 外掛所需的東西。可以使用 NPM 來安裝專案的前端相依套件：

```sh
npm install
```
<a name="configuring-vite"></a>

### 設定 Vite

Vite 使用專案根目錄的 `vite.config.js` 檔案來設定。可以依據需求隨意更改該檔案，也可以依照需求來安裝其他的外掛，如 `@vitejs/plugin-vue` 或 `@vitejs/plugin-react`。

要使用 Laravel 的 Vite 外掛，需要指定專案的 ^[Entry Point](%E9%80%B2%E5%85%A5%E9%BB%9E)。Entry Point 可以是 JavaScript 或 CSS 檔，也可以是使用預處理語言的檔案，如 TypeScript、JSX、TSX、或 Sass。

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/css/app.css',
            'resources/js/app.js',
        ]),
    ],
});
```
若要製作 SPA，或是使用 Intertia 建置的程式，則在 Vite 中最好不使用 CSS Entry Point：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/css/app.css', // [tl! remove]
            'resources/js/app.js',
        ]),
    ],
});
```
不要使用 CSS 進入點，而應該在 JavaScript 中 Import CSS 檔。一般來說，就是在 `resources/js/app.js` 檔案內 Import：

```js
import './bootstrap';
import '../css/app.css'; // [tl! add]
```
Laravel 的 Vite 外掛也支援多個 Entry Point，且還有一些進階的設定選項，如 [SSR Entry Point](#ssr)。

<a name="working-with-a-secure-development-server"></a>

#### Working With a Secure Development Server

若你的本機開發環境使用 HTTPS，則在連線到 Vite 開發伺服器時可能會遇到一些問題。

If you are using [Laravel Herd](https://herd.laravel.com) and have secured the site or you are using [Laravel Valet](/docs/{{version}}/valet) and have run the [secure command](/docs/{{version}}/valet#securing-sites) against your application, the Laravel Vite plugin will automatically detect and use the generated TLS certificate for you.

If you secured the site using a host that does not match the application's directory name, you may manually specify the host in your application's `vite.config.js` file:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // ...
            detectTls: 'my-app.test', // [tl! add]
        }),
    ],
});
```
使用其他網頁伺服器時，應產生受信任的憑證，並手動設定 Vite 使用所產生的憑證：

```js
// ...
import fs from 'fs'; // [tl! add]

const host = 'my-app.test'; // [tl! add]

export default defineConfig({
    // ...
    server: { // [tl! add]
        host, // [tl! add]
        hmr: { host }, // [tl! add]
        https: { // [tl! add]
            key: fs.readFileSync(`/path/to/${host}.key`), // [tl! add]
            cert: fs.readFileSync(`/path/to/${host}.crt`), // [tl! add]
        }, // [tl! add]
    }, // [tl! add]
});
```
若無法產生系統所信任的憑證，可以安裝並設定 [`@vitejs/plugin-basic-ssl` 外掛](https://github.com/vitejs/vite-plugin-basic-ssl)。在使用未受信任的憑證時，就需要打開執行 `npm run dev` 指令時顯示在主控台的「Local」連結，以在瀏覽器中接受 Vite 開發伺服器的憑證警告。

<a name="configuring-hmr-in-sail-on-wsl2"></a>

#### Running the Development Server in Sail on WSL2

When running the Vite development server within [Laravel Sail](/docs/{{version}}/sail) on Windows Subsystem for Linux 2 (WSL2), you should add the following configuration to your `vite.config.js` file to ensure the browser can communicate with the development server:

```js
// ...

export default defineConfig({
    // ...
    server: { // [tl! add:start]
        hmr: {
            host: 'localhost',
        },
    }, // [tl! add:end]
});
```
If your file changes are not being reflected in the browser while the development server is running, you may also need to configure Vite's [`server.watch.usePolling` option](https://vitejs.dev/config/server-options.html#server-watch).

<a name="loading-your-scripts-and-styles"></a>

### Loading Your Scripts and Styles

With your Vite entry points configured, you may now reference them in a `@vite()` Blade directive that you add to the `<head>` of your application's root template:

```blade
<!DOCTYPE html>
<head>
    {{-- ... --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```
若使用 JavaScript 來匯入 CSS，則只需要加上 JavaScript 的 Entry Point 即可：

```blade
<!DOCTYPE html>
<head>
    {{-- ... --}}

    @vite('resources/js/app.js')
</head>
```
`@vite` 指示詞會自動偵測到 Vite 的開發伺服器，並自動注入 Vite 的用戶端，讓我們可以使用 HMR (熱模組取代，Hot Module Replacement)。在建置模式時，該指示詞會自動載入編譯過、加上版本的素材，並包含任何已匯入的 CSS。

若有需要，也可以在呼叫 `@vite` 指示詞時指定編譯資源的建置路徑：

```blade
<!doctype html>
<head>
    {{-- Given build path is relative to public path. --}}

    @vite('resources/js/app.js', 'vendor/courier/build')
</head>
```
<a name="inline-assets"></a>

#### Inline Assets

Sometimes it may be necessary to include the raw content of assets rather than linking to the versioned URL of the asset. For example, you may need to include asset content directly into your page when passing HTML content to a PDF generator. You may output the content of Vite assets using the `content` method provided by the `Vite` facade:

```blade
@use('Illuminate\Support\Facades\Vite')

<!doctype html>
<head>
    {{-- ... --}}

    <style>
        {!! Vite::content('resources/css/app.css') !!}
    </style>
    <script>
        {!! Vite::content('resources/js/app.js') !!}
    </script>
</head>
```
<a name="running-vite"></a>

## 執行 Vite

要執行 Vite 有兩種方法。一種是使用 `dev` 指令來執行開發伺服器，適合用在本機環境開發時。開發伺服器會自動偵測任何檔案的修改，並自動反應到所有開啟的瀏覽器視窗中。

或者，也可以執行 `build` 指令。`build` 指令會為專案的素材加上版本，並打包這些素材，讓我們可以將其部署到正式環境中：

```shell
# Run the Vite development server...
npm run dev

# Build and version the assets for production...
npm run build
```
If you are running the development server in [Sail](/docs/{{version}}/sail) on WSL2, you may need some [additional configuration](#configuring-hmr-in-sail-on-wsl2) options.

<a name="working-with-scripts"></a>

## 處理 JavaScript

<a name="aliases"></a>

### 別名

預設情況下，Laravel 的 Vite 外掛提供了一個常見的別名，來讓你快速開始並方便地匯入專案素材：

```js
{
    '@' => '/resources/js'
}
```
也可以在 `vite.config.js` 設定檔中加上你自己的設定來複寫這個 `'@'` 別名：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel(['resources/ts/app.tsx']),
    ],
    resolve: {
        alias: {
            '@': '/resources/ts',
        },
    },
});
```
<a name="vue"></a>

### Vue

If you would like to build your frontend using the [Vue](https://vuejs.org/) framework, then you will also need to install the `@vitejs/plugin-vue` plugin:

```sh
npm install --save-dev @vitejs/plugin-vue
```
接著，就可以在 `vite.config.js` 設定檔中加上該外掛。接著，要將 Vue 外掛與 Laravel 搭配使用還需要進行一些步驟：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel(['resources/js/app.js']),
        vue({
            template: {
                transformAssetUrls: {
                    // The Vue plugin will re-write asset URLs, when referenced
                    // in Single File Components, to point to the Laravel web
                    // server. Setting this to `null` allows the Laravel plugin
                    // to instead re-write asset URLs to point to the Vite
                    // server instead.
                    base: null,

                    // The Vue plugin will parse absolute URLs and treat them
                    // as absolute paths to files on disk. Setting this to
                    // `false` will leave absolute URLs un-touched so they can
                    // reference assets in the public directory as expected.
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
```
> [!NOTE]  
> Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了適當的 Laravel、Vue、與 Vite 設定。請參考看看使用 [Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-inertia)，來用最快的速度開始使用 Laravel、Vue、與 Vite。

<a name="react"></a>

### React

If you would like to build your frontend using the [React](https://reactjs.org/) framework, then you will also need to install the `@vitejs/plugin-react` plugin:

```sh
npm install --save-dev @vitejs/plugin-react
```
可以在 `vite.config.js` 設定檔中加上該外掛：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel(['resources/js/app.jsx']),
        react(),
    ],
});
```
請確認包含 JSX 的檔案都使用 `.jsx` 或 `.tsx` 副檔名。若有需要，請記得更新 Entry Point，像[上文所提到的](#configuring-vite)。

還需要在現有的 `@vite` Blade 指示詞旁一起使用 `@viteReactRefresh` 指示詞。

```blade
@viteReactRefresh
@vite('resources/js/app.jsx')
```
`@viteReactRefresh` 指示詞必須在 `@vite` 指示詞前呼叫。

> [!NOTE]  
> Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了適當的 Laravel、React、與 Vite 設定。請參考看看使用 [Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-inertia)，來用最快的速度開始使用 Laravel、React、與 Vite。

<a name="inertia"></a>

### Inertia

Laravel 的 Vite 外掛中提供了一個方便的 `resolvePageComponent` 函式，來協助我們解析 Inertia 的頁面元件。下面是搭配 Vue 3 使用該輔助函式的範例。除了在 Vue 3 上使用外，也可以在如 React 等其他的框架上使用該函式：

```js
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
});
```
If you are using Vite's code splitting feature with Inertia, we recommend configuring [asset prefetching](#asset-prefetching).

> [!NOTE]  
> Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了適當的 Laravel、Inertia、與 Vite 設定。請參考看看使用 [Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-inertia)，來用最快的速度開始使用 Laravel、Inertia、與 Vite。

<a name="url-processing"></a>

### URL 的處理

When using Vite and referencing assets in your application's HTML, CSS, or JS, there are a couple of caveats to consider. First, if you reference assets with an absolute path, Vite will not include the asset in the build; therefore, you should ensure that the asset is available in your public directory. You should avoid using absolute paths when using a [dedicated CSS entrypoint](#configuring-vite) because, during development, browsers will try to load these paths from the Vite development server, where the CSS is hosted, rather than from your public directory.

使用相對路徑參照素材時，請記得，路徑是相對於正在參照該資源的檔案。Vite 會複寫使用相對路徑所參照的素材，並為其加上版本，然後進行打包。

來看一下下面這樣的專案結構：

```nothing
public/
  taylor.png
resources/
  js/
    Pages/
      Welcome.vue
  images/
    abigail.png
```
下列範例展示 Vite 如何處理相對與絕對 URL：

```html
<!-- This asset is not handled by Vite and will not be included in the build -->
<img src="/taylor.png">

<!-- This asset will be re-written, versioned, and bundled by Vite -->
<img src="../../images/abigail.png">
```
<a name="working-with-stylesheets"></a>

## 處理 CSS

你可以在 [Vite 說明文件](https://vitejs.dev/guide/features.html#css)中瞭解更多有關 Vite 對 CSS 的支援。若使用如 [Tailwind](https://tailwindcss.com) 等的 PostCSS 外掛，可在專案根目錄中建立一個 `postcss.config.js` 檔。Vite 會自動套用該檔案中的設定：

```js
export default {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
};
```
> [!NOTE]  
> Laravel's [starter kits](/docs/{{version}}/starter-kits) already include the proper Tailwind, PostCSS, and Vite configuration. Or, if you would like to use Tailwind and Laravel without using one of our starter kits, check out [Tailwind's installation guide for Laravel](https://tailwindcss.com/docs/guides/laravel).

<a name="working-with-blade-and-routes"></a>

## Working With Blade and Routes

<a name="blade-processing-static-assets"></a>

### 使用 Vite 處理靜態素材

在 JavaScript 或 CSS 中參照素材時，Vite 會自動處理這些素材並為其加上版本。此外，在建置基於 Blade 的專案時，也可以使用 Vite 來處理只在 Blade 中被參照的靜態資源，並為這些資源加上版本。

不過，若要達成此目的，我們需要先在專案的 ^[Entry Point](%E9%80%B2%E5%85%A5%E9%BB%9E) 匯入這些素材，好讓 Vite 知道有這些素材的存在。舉例來說，若想處理並為所有 `resources/images` 下的圖片、以及 `resources/fonts` 下的所有字體加上版本，就需要在專案的 `resources/js/app.js` Entry Point 內加上下列程式碼：

```js
import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
```
接著在執行 `npm run build` 時，這些素材就會自動被 Vite 處理。我們接著就可以在 Blade 樣板中使用 `Vite::asset` 方法來參照這些素材。該方法會回傳給定資源加上版本後的 URL：

```blade
<img src="{{ Vite::asset('resources/images/logo.png') }}">
```
<a name="blade-refreshing-on-save"></a>

### Refreshing on Save

若專案使用 Blade 這樣傳統式的伺服器端轉譯，則 Vite 可以在 View 檔案被修改的時候幫你自動重新整理瀏覽器來改進開發流程。若要開始讓 Vite 自動重新整理，只需要將 `refresh` 選項設為 `true` 即可。

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // ...
            refresh: true,
        }),
    ],
});
```
當 `refresh` 選項設為 `true` 後，在執行 `npm run dev` 時，一旦在下列目錄中保存檔案，就會觸發瀏覽器進行整頁的重新整理：

- `app/Livewire/**`
- `app/View/Components/**`
- `lang/**`
- `resources/lang/**`
- `resources/views/**`
- `routes/**`

若使用 [Ziggy](https://github.com/tighten/ziggy) 來在網頁前端中產生 Route 連結，監看 `routes/**` 目錄就很實用。

若這些預設的路徑不符合你的需求，也可以自行指定一組要監看的路徑清單：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // ...
            refresh: ['resources/views/**'],
        }),
    ],
});
```
其實，Laravel 的 Vite 外掛使用了 [`vite-plugin-full-reload`](https://github.com/ElMassimo/vite-plugin-full-reload) 套件，該套件還提供了一些選項，能微調這個重新整理功能的行為。若有需要自定這類微調，可以加上一個 `config` 定義：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // ...
            refresh: [{
                paths: ['path/to/watch/**'],
                config: { delay: 300 }
            }],
        }),
    ],
});
```
<a name="blade-aliases"></a>

### 別名

在 JavaScript 專案中，為常用的目錄[建立別名](#aliases)是很常見的。不過，我們也可以使用 `Illuminate\Support\Facade\Vite` 類別的 `macro` 方法來建立能在 Blade 中使用的別名。一般來說，「^[Macro](%E5%B7%A8%E9%9B%86)」應在某個 [Service Provider](/docs/{{version}}/providers) 內定義：

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::macro('image', fn (string $asset) => $this->asset("resources/images/{$asset}"));
    }
定義好 Macro 後，就可以在樣板中呼叫。舉例來說，我們可以使用 `image` Macro 定義來參照到位在 `resources/images/logo.png` 的素材：

```blade
<img src="{{ Vite::image('logo.png') }}" alt="Laravel Logo">
```
<a name="asset-prefetching"></a>

## Asset Prefetching

When building an SPA using Vite's code splitting feature, required assets are fetched on each page navigation. This behavior can lead to delayed UI rendering. If this is a problem for your frontend framework of choice, Laravel offers the ability to eagerly prefetch your application's JavaScript and CSS assets on initial page load.

You can instruct Laravel to eagerly prefetch your assets by invoking the `Vite::prefetch` method in the `boot` method of a [service provider](/docs/{{version}}/providers):

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ...
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
```
In the example above, assets will be prefetched with a maximum of `3` concurrent downloads on each page load. You can modify the concurrency to suit your application's needs or specify no concurrency limit if the application should download all assets at once:

```php
/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Vite::prefetch();
}
```
By default, prefetching will begin when the [page *load* event](https://developer.mozilla.org/en-US/docs/Web/API/Window/load_event) fires. If you would like to customize when prefetching begins, you may specify an event that Vite will listen for:

```php
/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Vite::prefetch(event: 'vite:prefetch');
}
```
Given the code above, prefetching will now begin when you manually dispatch the `vite:prefetch` event on the `window` object. For example, you could have prefetching begin three seconds after the page loads:

```html
<script>
    addEventListener('load', () => setTimeout(() => {
        dispatchEvent(new Event('vite:prefetch'))
    }, 3000))
</script>
```
<a name="custom-base-urls"></a>

## 自定 Base URL

若由 Vite 編譯出來的素材會被部署到與專案不同的網域上，如 CDN 等，則可在專案的 `.env` 檔案中指定 `ASSET_URL` 環境變數：

```env
ASSET_URL=https://cdn.example.com
```
設定好素材 URL 後，所有被複寫的素材 URL 的前方都會被加上該設定值：

```nothing
https://cdn.example.com/build/assets/app.9dce8d17.js
```
請記得，[Vite 不會複寫絕對 URL](#url-processing)，所以這些絕對 URL 會被保持原樣。

<a name="environment-variables"></a>

## 環境變數

在專案的 `.env` 檔中，只要為環境變數名稱冠上 `VITE_` 前置詞，就可將這些環境變數注入到 JavaScript 中：

```env
VITE_SENTRY_DSN_PUBLIC=http://example.com
```
可以使用 `import.meta.env` 物件來存取注入的變數：

```js
import.meta.env.VITE_SENTRY_DSN_PUBLIC
```
<a name="disabling-vite-in-tests"></a>

## Disabling Vite in Tests

Laravel 的 Vite 整合會在執行測試時嘗試解析素材，要能解析素材就必須要執行 Vite 開發伺服器或建置素材。

If you would prefer to mock Vite during testing, you may call the `withoutVite` method, which is available for any tests that extend Laravel's `TestCase` class:

```php
test('without vite example', function () {
    $this->withoutVite();

    // ...
});
```
```php
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_without_vite_example(): void
    {
        $this->withoutVite();

        // ...
    }
}
```
若想在所有測試中禁用 Vite，可在基礎 `TestCase` 類別的 `setUp` 方法中呼叫 `withoutVite` 方法：

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void// [tl! add:start]
    {
        parent::setUp();

        $this->withoutVite();
    }// [tl! add:end]
}
```
<a name="ssr"></a>

## 伺服器端轉譯 (SSR)

使用 Laravel 的 Vite 外掛，就可以無痛在 Vite 上設定伺服器端轉譯 (SSR, Server Side Rendering)。若要開始設定 SSR，請建立一個 `resources/js/ssr.js` 來作為 SSR 的進入點，並在 Laravel 的 Vite 外掛上傳入一組設定來指定該進入點：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js',
        }),
    ],
});
```
為了避免我們忘記要重新建置 SSR 進入點，建議調整專案的 `package.json` 內的「build」指令來建立 SSR 建置：

```json
"scripts": {
     "dev": "vite",
     "build": "vite build" // [tl! remove]
     "build": "vite build && vite build --ssr" // [tl! add]
}
```
接著，若要開始建置並執行 SSR 伺服器，可執行下列指令：

```sh
npm run build
node bootstrap/ssr/ssr.js
```
If you are using [SSR with Inertia](https://inertiajs.com/server-side-rendering), you may instead use the `inertia:start-ssr` Artisan command to start the SSR server:

```sh
php artisan inertia:start-ssr
```
> [!NOTE]  
> Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了適當的 Laravel、Inertia SSR、與 Vite 設定。請參考看看使用 [Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-inertia)，來用最快的速度開始使用 Laravel、Inertia SSR、與 Vite。

<a name="script-and-style-attributes"></a>

## Script and Style Tag Attributes

<a name="content-security-policy-csp-nonce"></a>

### 內容安全性原則 (CSP) 的 Nonce

若想在 script 與 style 上為[內容安全性原則 (CSP, Content Security Policy)](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP) 加上 [`nonce` 屬性](https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/nonce)，可以自定一個 [Middleware](/docs/{{version}}/middleware) 來使用 `useCspNonce` 方法來產生或指定一個 Nonce。

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class AddContentSecurityPolicyHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        return $next($request)->withHeaders([
            'Content-Security-Policy' => "script-src 'nonce-".Vite::cspNonce()."'",
        ]);
    }
}
```
呼叫 `useCspNonce` 方法後，Laravel 就會自動在所有產生的 script 與 style 標籤上包含一個 `nonce` 屬性。

若有需要在別處指定 Nonce，如在 Laravel [入門套件](/docs/{{version}}/starter-kits)中所包含的 [Ziggy `@route` 指示詞](https://github.com/tighten/ziggy#using-routes-with-a-content-security-policy)，則可以使用 `cspNonce` 方法來取得該 Nonce：

```blade
@routes(nonce: Vite::cspNonce())
```
若已有 Nonce，且想讓 Laravel 使用該 Nonce，則可傳入該 Nonce 給 `useCspNonce` 方法：

```php
Vite::useCspNonce($nonce);
```
<a name="subresource-integrity-sri"></a>

### 子資源完整性 (SRI)

若 Vite Manifest 中有包含資源的 ^[`integrity`](%E5%AE%8C%E6%95%B4%E6%80%A7) 雜湊，則 Laravel 會自動在所有 Vite 產生的 script 與 style 標籤上加上 `integrity` 屬性，已強制確保[子資源完整性 (SRI, Subresource Integrity)](https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity)。預設情況下，Vite 不會在其 Manifest 檔中包含 `integrity` 雜湊。但只要安裝 [`vite-plugin-manifest-sri`](https://www.npmjs.com/package/vite-plugin-manifest-sri) NPM 外掛，就可啟用該功能：

```shell
npm install --save-dev vite-plugin-manifest-sri
```
可以在 `vite.config.js` 檔中啟用該外掛：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import manifestSRI from 'vite-plugin-manifest-sri';// [tl! add]

export default defineConfig({
    plugins: [
        laravel({
            // ...
        }),
        manifestSRI(),// [tl! add]
    ],
});
```
若有需要，也可以指定 Manifest 中保存 Integrity 雜湊的索引鍵：

```php
use Illuminate\Support\Facades\Vite;

Vite::useIntegrityKey('custom-integrity-key');
```
若有需要完全禁用自動偵測，可傳入 `false` 給 `useIntegrityKey` 方法：

```php
Vite::useIntegrityKey(false);
```
<a name="arbitrary-attributes"></a>

### 任意屬性

若有需要在 script 與 style 標籤上加入其他的額外屬性，如 [`data-turbo-track`](https://turbo.hotwired.dev/handbook/drive#reloading-when-assets-change) 等，則可使用 `useScriptTagAttributes` 與 `useStyleTagAttributes` 方法。一般來說，應該在某個 [Service Provider](/docs/{{version}}/providers) 中叫用該方法：

```php
use Illuminate\Support\Facades\Vite;

Vite::useScriptTagAttributes([
    'data-turbo-track' => 'reload', // Specify a value for the attribute...
    'async' => true, // Specify an attribute without a value...
    'integrity' => false, // Exclude an attribute that would otherwise be included...
]);

Vite::useStyleTagAttributes([
    'data-turbo-track' => 'reload',
]);
```
若有需要有條件地新增屬性，則可傳入一個回呼。該回呼會收到素材的原始檔路徑、其 URL、該素材的 Manifest Chunk、以及整個 Manifest：

```php
use Illuminate\Support\Facades\Vite;

Vite::useScriptTagAttributes(fn (string $src, string $url, array|null $chunk, array|null $manifest) => [
    'data-turbo-track' => $src === 'resources/js/app.js' ? 'reload' : false,
]);

Vite::useStyleTagAttributes(fn (string $src, string $url, array|null $chunk, array|null $manifest) => [
    'data-turbo-track' => $chunk && $chunk['isEntry'] ? 'reload' : false,
]);
```
> [!WARNING]  
> 在執行 Vite 開發伺服器時，`$chunk` 與 `$manifest` 屬性會是 `null`。

<a name="advanced-customization"></a>

## 進階客製化

在新安裝好的 Laravel Vite 外掛中使用到了一些合理的慣例。這些慣例應該適用於大多數專案。不過，有時候我們還是需要自定 Vite 的姓外。若要啟用額外的客製化選項，Laravel 提供了下列方法與選項，可用於替代 `@vite` Blade 指示詞：

```blade
<!doctype html>
<head>
    {{-- ... --}}

    {{
        Vite::useHotFile(storage_path('vite.hot')) // Customize the "hot" file...
            ->useBuildDirectory('bundle') // Customize the build directory...
            ->useManifestFilename('assets.json') // Customize the manifest filename...
            ->withEntryPoints(['resources/js/app.js']) // Specify the entry points...
            ->createAssetPathsUsing(function (string $path, ?bool $secure) { // Customize the backend path generation for built assets...
                return "https://cdn.example.com/{$path}";
            })
    }}
</head>
```
在 `vite.config.js` 中，也可以指定相同的設定：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'storage/vite.hot', // Customize the "hot" file...
            buildDirectory: 'bundle', // Customize the build directory...
            input: ['resources/js/app.js'], // Specify the entry points...
        }),
    ],
    build: {
      manifest: 'assets.json', // Customize the manifest filename...
    },
});
```
<a name="correcting-dev-server-urls"></a>

### 修正開發伺服器的 URL

在 Vite 生態系統中，有些外掛會假設 URL 以斜線 (`/`) 開頭的 URL 是指向 Vite 開發伺服器的。不過，由於 Laravel 整合的特性，這個假設在 Laravel 中並不成立。

舉例來說，在使用 Vite 伺服器提供素材時，`vite-imagetools` 外掛會像下面這樣輸出 URL：

```html
<img src="/@imagetools/f0b2f404b13f052c604e632f2fb60381bf61a520">
```
`vite-imagetools` 外掛預期這個輸出 URL 會被 Vite 攔截，讓這個外掛能處理所有以 `/@imagetools` 開頭的所有 URL。若你使用的外掛有預期這樣的行為，就需要手動修正 URL。可以在 `vite.config.js` 檔案中修改 `transformOnServe` 選項來修正 URL。

In this particular example, we will prepend the dev server URL to all occurrences of `/@imagetools` within the generated code:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { imagetools } from 'vite-imagetools';

export default defineConfig({
    plugins: [
        laravel({
            // ...
            transformOnServe: (code, devServerUrl) => code.replaceAll('/@imagetools', devServerUrl+'/@imagetools'),
        }),
        imagetools(),
    ],
});
```
現在，Vite 伺服器在提供素材時，就會輸出指向 Vite 開發伺服器的 URL：

```html
- <img src="/@imagetools/f0b2f404b13f052c604e632f2fb60381bf61a520"><!-- [tl! remove] -->
+ <img src="http://[::1]:5173/@imagetools/f0b2f404b13f052c604e632f2fb60381bf61a520"><!-- [tl! add] -->
```