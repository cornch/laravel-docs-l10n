---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/45/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:18:00Z'
---

# 部署

- [簡介](#introduction)
- [伺服器需求](#server-requirements)
- [伺服器設定](#server-configuration)
   - [Nginx](#nginx)
- [最佳化](#optimization)
   - [Autoloader 的最佳化](#autoloader-optimization)
   - [最佳化設定檔的載入](#optimizing-configuration-loading)
   - [最佳化 Route 的載入](#optimizing-route-loading)
   - [最佳化 View 的載入](#optimizing-view-loading)
- [偵錯模式](#debug-mode)
- [使用 Forge 或 Vapor 部署](#deploying-with-forge-or-vapor)

<a name="introduction"></a>

## 簡介

準備好將 Laravel 專案部署到正式環境時，還有一些影響網站效能的重點要注意。在本文中，我們會討論一些正確部署 Laravel 專案的要點。

<a name="server-requirements"></a>

## 伺服器需求

Laravel Framework 有一些系統需求。請確保網頁伺服器有達到下列最小 PHP 版本需求與擴充套件需求：

<div class="content-list" markdown="1">

- PHP >= 7.3
- BCMath PHP 擴充套件
- Ctype PHP 擴充套件
- Fileinfo PHP 擴充套件
- JSON PHP 擴充套件
- Mbstring PHP 擴充套件
- OpenSSL PHP 擴充套件
- PDO PHP 擴充套件
- Tokenizer PHP 擴充套件
- XML PHP 擴充套件

</div>

<a name="server-configuration"></a>

## 伺服器設定

<a name="nginx"></a>

### Nginx

若將專案部署到執行 Nginx 的伺服器上，則應使用下列設定檔來開始設定網頁伺服器。當然，還需要根據伺服器來調整該檔案中的設定。**若需要協助管理伺服器，請參考使用 Laravel 官方的伺服器管理與部署服務，如 [Laravel Forge](https://forge.laravel.com)。**

像下列設定檔一樣，請確保網頁伺服器有將所有連入網站的請求重新導向到 `public/index.php` 檔案上。請絕對不要嘗試將 `index.php` 檔案移到專案根目錄上，因為以專案根目錄來開放網站可能導致一些機敏設定檔被暴露到公開的網際網路上：

    server {
        listen 80;
        listen [::]:80;
        server_name example.com;
        root /srv/example.com/public;
    
        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";
    
        index index.php;
    
        charset utf-8;
    
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
    
        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }
    
        error_page 404 /index.php;
    
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }
    
        location ~ /\.(?!well-known).* {
            deny all;
        }
    }

<a name="optimization"></a>

## 最佳化

<a name="autoloader-optimization"></a>

### Autoloader 最佳化

部署到正式環境時，請確定有最佳化 Composer 的類別 Autoloader 映射，以讓 Composer 可快速找到某個類別對應的檔案：

    composer install --optimize-autoloader --no-dev

> {tip} 除了最佳化 Autoloader 外，也應確保有將 `composer.lock` 檔案加到專案的版本控制儲存庫內。當有 `composer.lock` 檔時，專案的相依性套件可以安裝得更快。

<a name="optimizing-configuration-loading"></a>

### 最佳化設定檔的載入

在將專案部署到正式環境時，請確保部署流程中有執行 `config:cache` Artisan 指令：

    php artisan config:cache

該指令會將所有的 Laravel 設定檔合併為單一、經過快取的檔案。使用快取檔通常可以減少一些框架在載入設定值時讀取檔案系統的次數。

> {note} 若在部署流程中執行了 `config:cache` 指令，應確保只有在設定檔中呼叫 `env` 函式。設定檔被快取後，就不會再載入 `.env` 檔了。所有 `env` 函式查詢 `.env` 變數的呼叫都會回傳 `null`。

<a name="optimizing-route-loading"></a>

### 最佳化路由載入

若正在建立有許多路由的大型專案，請確保在部署過程中有執行 `route:cache` Artisan 指令：

    php artisan route:cache

該指令可將所有的路由註冊減少為快取檔案內的單一方法呼叫，在註冊上百個路由時，可藉此提升路由註冊的效能。

<a name="optimizing-view-loading"></a>

### 最佳化 View 載入

在將專案部署到正式環境時，請確保有在部署流程內執行 `view:cache` Artisan 指令：

    php artisan view:cache

該指令會預先編譯所有的 Blade View，這樣一來這些 View 就不會只在有需要的時候才進行編譯，可藉此提升每個有回傳 View 的請求效能。

<a name="debug-mode"></a>

## 偵錯模式

config/app.php 設定檔中的 debug 選項用來判斷錯誤在實際顯示給使用者時要包含多少資訊。預設情況下，這個選項被設為依照 APP_DEBUG 環境變數值，該環境變數儲存於 `.env` 檔內。

**在正式環境上，這個值一定要是 `false`。若在正式環境上將 `APP_DEBUG` 變數設為 `true`，則會有將機敏設定值暴露給應用程式終端使用者的風險。**

<a name="deploying-with-forge-or-vapor"></a>

## 使用 Forge /Vapor 來部署

<a name="laravel-forge"></a>

#### Laravel Forge

若你還未準備好自行管理伺服器設定，或不擅長設定各種執行大型 Laravel 專案所需要的設定，則 [Laravel Forge](https://forge.laravel.com) 是一個不錯的選擇。

Laravel Forge 可以在如 DigitalOcean, Linode, AWS… 等各種基礎建設提供商上建立伺服器。此外，Forge 還可以負責安裝並管理各種執行大型 Laravel 專案所需的工具，如 Nginx, MySQL, Redis, Memcached, Beanstalk… 等。

<a name="laravel-vapor"></a>

#### Laravel Vapor

若想試試完全為 Laravel 最佳化、Auto-Scaling 的 Serverless 部署平台，請參考看看 [Laravel Vapor](https://vapor.laravel.com)。Laravel Vapor 是一個為 Laravel 設計的 Serverless 部署平台，由 AWS 驅動。使用 Vapor 來發佈你的 Laravel 基礎建設，你會愛上 Serverless 這種能簡單擴充的架構。Laravel Vapor 已由 Laravel 的作者們精心最佳化，以讓 Vapor 能完美配合 Laravel 使用，並讓你能像往常在 Laravel 專案開發就好。
