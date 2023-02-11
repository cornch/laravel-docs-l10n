---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/105/en-zhtw
progress: 100
updatedAt: '2023-02-11T10:28:00Z'
---

# Laravel Mix

- [簡介](#introduction)

<a name="introduction"></a>

## 簡介

[Laravel Mix](https://github.com/laravel-mix/laravel-mix) 是一個由 [Laracasts](https://laracasts.com) 作者 Jeffrey Way 開發的套件，該套件使用了各種常見的 CSS 與 JavaScript ^[預處理器](Pre-Processor)，可在 Laravel 專案上使用 Fluent API 來定義 [Webpack](https://webpack.js.org) 建置步驟。

換句話說，使用 Mix 就可以輕鬆地編譯並 ^[Minify](最小化 / 壓縮) 專案的 CSS 與 JavaScript 檔案。只要簡單地串接幾個方法，就可以流暢的定義^[素材管道](Asset Pipeline)。例如：

```js
mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css');
```

若你搞不懂怎麼用 Webpack 與編輯素材、或是覺得很複雜的話，你一定會喜歡 Laravel Mix 的。不過，不一定要使用 Laravel Mix 也能開發你的網站。你可以自由決定要使用哪個素材管道工具，甚至也可以不使用任何工具。

> **Note** 在新安裝的 Laravel 中，Vite 已經取代了 Laravel Mix。若要瀏覽 Mix 的說明文件，請瀏覽 [Laravel Mix 官方網站](https://laravel-mix.com/)。若要切換至 Vite，請檢視我們的 [Vite 遷移指南](https://github.com/laravel/vite-plugin/blob/main/UPGRADE.md#migrating-from-laravel-mix-to-vite)。
