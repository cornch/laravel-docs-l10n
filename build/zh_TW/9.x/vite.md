---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/180/en-zhtw
progress: 100
updatedAt: '2023-01-25T18:14:00Z'
---

# Bundling Assets (Vite)

- [簡介](#introduction)
- [安裝與設定](#installation)
   - [安裝 Node](#installing-node)
   - [安裝 Vite 與 Laravel 外掛](#installing-vite-and-laravel-plugin)
   - [設定 Vite](#configuring-vite)
   - [載入 Script 與 CSS](#loading-your-scripts-and-styles)
- [執行 Vite](#running-vite)
- [處理 JavaScript](#working-with-scripts)
   - [別名](#aliases)
   - [Vue](#vue)
   - [React](#react)
   - [Inertia](#inertia)
   - [URL 的處理](#url-processing)
- [處理 CSS](#working-with-stylesheets)
- [處理 Blade 與 Route](#working-with-blade-and-routes)
- [自定 Base URL](#custom-base-urls)
- [環境變數](#environment-variables)
- [伺服器端轉譯 (SSR)](#ssr)
- [Script 與 Style 標籤的屬性](#script-and-style-attributes)
   - [Content Security Policy (CSP) Nonce](#content-security-policy-csp-nonce)
   - [Subresource Integrity (SRI)](#subresource-integrity-sri)
   - [任意屬性](#arbitrary-attributes)

<a name="introduction"></a>

## 簡介

[Vite](https://vitejs.dev) 是現代化的前端建置工具，提供快速的開發環境，並可快速為正式環境打包程式碼。在使用 Laravel 製作程式時，我們通常會用 Vite 來將專案的 CSS 與 JavaScript 檔案打包成可在正式環境使用的資源。

Laravel 與 Vite 進行了無縫整合，並提供了官方的外掛程式以及 Blade 指示詞來讓在開發環境與正式環境上載入資源。

> **Note** 已經在使用 Laravel Mix 了嗎？在新安裝的 Laravel 中，Vite 已經取代了 Laravel Mix。若要瀏覽 Mix 的說明文件，請瀏覽 [Laravel Mix 網站](https://laravel-mix.com/)。若要切換至 Vite，請檢視我們的 [Vite 遷移指南](https://github.com/laravel/vite-plugin/blob/main/UPGRADE.md#migrating-from-laravel-mix-to-vite)。

<a name="vite-or-mix"></a>

#### 該選擇 Vite 還是 Laravel Mix

在改用 Vite 前，新的 Laravel 專案使用的都是 [Mix](https://laravel-mix.com/)。Mix 在打包資源時使用的是 [webpack](https://webpack.js.org/)。Vite 則重點在為大量使用 JavaScript 的專案提供更快速更有生產力的環境。若要開發的專案有使用 SPA (單頁應用程式，Single Page Application)，甚至可能還是使用了 [Inertia](https://inertiajs.com) 等工具來開發時，則最適合使用 Vite。

若在開發的專案是一些只使用 JavaScript 來「點綴」的傳統伺服器端轉譯的專案，或是使用 [Livewire](https://laravel-livewire.com) 時，也可以使用 Vite。不過，有一些功能是只有 Laravel Mix 支援而 Vite 不支援的，例如將一些資源直接複製到建置結果中，並且不直接在 JavaScript 程式中參照這些資源。

<a name="migrating-back-to-mix"></a>

#### 遷移回 Mix

剛開始建立新 Laravel 專案，但用到了 Vite Scaffolding 而需要遷移回 Laravel Mix 與 Webpack 嗎？沒問題。請參考我們的[官方 Vite 至 Mix 遷移指南 (英語)](https://github.com/laravel/vite-plugin/blob/main/UPGRADE.md#migrating-from-vite-to-laravel-mix)。

<a name="installation"></a>

## 安裝與設定

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

### 安裝 Vite 與 Laravel 外掛

在新安裝的 Laravel 中，可以看到專案根目錄下有個 `package.json`。預設的 `package.json` 檔案已包含了所有開始使用 Vite 與 Laravel Vite 外掛所需的東西。可以使用 NPM 來安裝專案的前端相依套件：

```sh
npm install
```

<a name="configuring-vite"></a>

### 設定 Vite

Vite 使用專案根目錄的 `vite.config.js` 檔案來設定。可以依據需求隨意更改該檔案，也可以依照需求來安裝其他的外掛，如 `@vitejs/plugin-vue` 或 `@vitejs/plugin-react`。

要使用 Laravel 的 Vite 外掛，需要指定專案的 ^[Entry Point](進入點)。Entry Point 可以是 JavaScript 或 CSS 檔，也可以是使用預處理語言的檔案，如 TypeScript、JSX、TSX、或 Sass。

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

#### 在 HTTPS 的開發伺服器使用 Vite

If your development web server is running on HTTPS, including Valet's [secure command](/docs/{{version}}/valet#securing-sites), you may run into issues connecting to the Vite development server. You may configure Vite to also run on HTTPS by adding the following to your `vite.config.js` configuration file:

```js
export default defineConfig({
    // ...
    server: { // [tl! add]
        https: true, // [tl! add]
        host: 'localhost', // [tl! add]
    }, // [tl! add]
});
```

You will also need to accept the certificate warning for Vite's development server in your browser by following the "Local" link in your console when running the `npm run dev` command.

<a name="loading-your-scripts-and-styles"></a>

### 載入 Script 與 CSS

設定好 Vite 的 Entry Point 後，我們接著就只需要在專案根樣板中的 `<head>` 內使用 `@vite()` Blade 指示詞來參照這些 Entry Point 即可：

```blade
<!doctype html>
<head>
    {{-- ... --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

若使用 JavaScript 來匯入 CSS，則只需要加上 JavaScript 的 Entry Point 即可：

```blade
<!doctype html>
<head>
    {{-- ... --}}

    @vite('resources/js/app.js')
</head>
```

`@vite` 指示詞會自動偵測到 Vite 的開發伺服器，並自動注入 Vite 的用戶端，讓我們可以使用 HMR (熱模組取代，Hot Module Replacement)。在建置模式時，該指示詞會自動載入編譯過、加上版本的素材，並包含任何已匯入的 CSS。

<a name="running-vite"></a>

## 執行 Vite

要執行 Vite 有兩種方法。一種是使用 `dev` 指令來執行開發伺服器，適合用在本機環境開發時。開發伺服器會自動偵測任何檔案的修改，並自動反應到所有開啟的瀏覽器視窗中。

或者，也可以執行 `build` 指令。`build` 指令會為專案的素材加上版本，並打包這些素材，讓我們可以將其部署到正式環境中：

```shell
# 執行 Vite 的開發伺服器...
npm run dev

# 建置素材並加上版本以在正式環境下使用...
npm run build
```

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

若要讓 Vue 外掛與 Laravel 的 Vite 外掛一起使用，還需要在 `vite.config.js` 設定檔中加上一些其他的設定：

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
                    // reference assets in the public directly as expected.
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
```

> **Note** Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了適當的 Laravel、Vue、與 Vite 設定。請參考看看使用 [Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-inertia)，來用最快的速度開始使用 Laravel、Vue、與 Vite。

<a name="react"></a>

### React

在 Vite 中使用 React 時，需要確定任何包含 JSX 的檔案都使用 `.jsx` 或 `.tsx` 副檔名，並請記得，若有需要的話要[像剛才提到的一樣](#configuring-vite)更新 Entry Point。除了現有的 `@vite` 指示詞外，可能也許要再加上額外的 `@viteReactRefresh` Blade 指示詞。

```blade
@viteReactRefresh
@vite('resources/js/app.jsx')
```

`@viteReactRefresh` 指示詞必須在 `@vite` 指示詞前呼叫。

> **Note** Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了適當的 Laravel、React、與 Vite 設定。請參考看看使用 [Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-inertia)，來用最快的速度開始使用 Laravel、React、與 Vite。

<a name="inertia"></a>

### Inertia

Laravel 的 Vite 外掛中提供了一個方便的 `resolvePageComponent` 函式，來協助我們解析 Inertia 的頁面元件。下面是搭配 Vue 3 使用該輔助函式的範例。除了在 Vue 3 上使用外，也可以在如 React 等其他的框架上使用該函式：

```js
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
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

> **Note** Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了適當的 Laravel、Inertia、與 Vite 設定。請參考看看使用 [Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-inertia)，來用最快的速度開始使用 Laravel、Inertia、與 Vite。

<a name="url-processing"></a>

### URL 的處理

When using Vite and referencing assets in your application's HTML, CSS, or JS, there are a couple of things to consider. First, if you reference assets with an absolute path, Vite will not include the asset in the build; therefore, you should ensure that the asset is available in your public directory.

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
<!-- 該素材不會被 Vite 出列，且不會包含在建置結果中 -->
<img src="/taylor.png">

<!-- 該素材會被 Vite 複寫，且會加上版本並打包 -->
<img src="../../images/abigail.png">
```

<a name="working-with-stylesheets"></a>

## 處理 CSS

你可以在 [Vite 說明文件](https://vitejs.dev/guide/features.html#css)中瞭解更多有關 Vite 對 CSS 的支援。若使用如 [Tailwind](https://tailwindcss.com) 等的 PostCSS 外掛，可在專案根目錄中建立一個 `postcss.config.js` 檔。Vite 會自動套用該檔案中的設定：

```js
module.exports = {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
};
```

<a name="working-with-blade-and-routes"></a>

## 處理 Blade 與 Route

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

When the `refresh` option is `true`, saving files in `resources/views/**`, `app/View/Components/**`, and `routes/**` will trigger the browser to perform a full page refresh while you are running `npm run dev`.

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
node bootstrap/ssr/ssr.mjs
```

> **Note** Laravel 的[入門套件](/docs/{{version}}/starter-kits)中已經包含了適當的 Laravel、Inertia SSR、與 Vite 設定。請參考看看使用 [Laravel Breeze](/docs/{{version}}/starter-kits#breeze-and-inertia)，來用最快的速度開始使用 Laravel、Inertia SSR、與 Vite。

<a name="script-and-style-attributes"></a>

## Script 與 Style 標籤的屬性

<a name="content-security-policy-csp-nonce"></a>

### 內容安全性原則 (CSP) 的 Nonce

若想在 script 與 style 上為[內容安全性原則 (CSP, Content Security Policy)](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP) 加上 [`nonce` 屬性](https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/nonce)，可以自定一個 [Middleware](/docs/{{version}}/middleware) 來使用 `useCspNonce` 方法來產生或指定一個 Nonce。

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Vite;

class AddContentSecurityPolicyHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
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

若 Vite Manifest 中有包含資源的 ^[`integrity`](完整性) 雜湊，則 Laravel 會自動在所有 Vite 產生的 script 與 style 標籤上加上 `integrity` 屬性，已強制確保[子資源完整性 (SRI, Subresource Integrity)](https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity)。預設情況下，Vite 不會在其 Manifest 檔中包含 `integrity` 雜湊。但只要安裝 [`vite-plugin-manifest-uri`](https://www.npmjs.com/package/vite-plugin-manifest-sri) NPM 外掛，就可啟用該功能：

```shell
npm install -D vite-plugin-manifest-sri
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
    'data-turbo-track' => 'reload', // 為屬性指定值...
    'async' => true, // 指定一個沒有值的屬性...
    'integrity' => false, // 排除掉一個原本會被包含的屬性...
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

> **Warning** 在執行 Vite 開發伺服器時，`$chunk` 與 `$manifest` 屬性會是 `null`。
