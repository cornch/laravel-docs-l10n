---
contributors:
  13334671:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/13334671/medium/1eb2ac36ce24a892c96a869fce7ca359.jpg
    name: Yi-Jyun Pan
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/81/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
---

# Laravel Homestead

- [簡介](#introduction)
- [安裝與設定](#installation)
   - [第一步](#first-steps)
   - [設定 Homestead](#configuring-homestead)
   - [設定 Nginx 網站](#configuring-nginx-sites)
   - [設定服務](#configuring-services)
   - [啟動 Vagrant Box](#launching-the-vagrant-box)
   - [個別專案安裝](#per-project-installation)
   - [安裝選配功能](#installing-optional-features)
   - [別名](#aliases)
- [更新 Homestead](#updating-homestead)
- [日常使用](#daily-usage)
   - [通過 SSH 連線](#connecting-via-ssh)
   - [新增額外的網站](#adding-additional-sites)
   - [環境變數](#environment-variables)
   - [通訊埠](#ports)
   - [PHP 版本](#php-versions)
   - [連線至資料庫](#connecting-to-databases)
   - [資料庫備份](#database-backups)
   - [設定 Cron 排程](#configuring-cron-schedules)
   - [設定 MailHog](#configuring-mailhog)
   - [設定 Minio](#configuring-minio)
   - [Laravel Dusk](#laravel-dusk)
   - [共享你的環境](#sharing-your-environment)
- [除錯與分析](#debugging-and-profiling)
   - [使用 Xdebug 來針對 Web Request 進行除錯](#debugging-web-requests)
   - [針對 CLI 應用程式進行除錯](#debugging-cli-applications)
   - [使用 Blackfire 來針對應用程式進行分析](#profiling-applications-with-blackfire)
- [網路介面](#network-interfaces)
- [擴充 Homestead](#extending-homestead)
- [Provider 特定的設定](#provider-specific-settings)
   - [VirtualBox](#provider-specific-virtualbox)

<a name="introduction"></a>

## 簡介

Laravel 致力於改良整個 PHP 的開發體驗，這也包含你的本機開發環境。[Laravel Homestead](https://github.com/laravel/homestead) 是一個預先封裝的 Vagrant Box，由 Laravel 官方所推出。使用 Homestead，你不需要在本機上安裝 PHP、網頁伺服器和其他伺服器軟體，就能享受完整且舒適的開發環境。

[Vagrant](https://www.vagrantup.com) 讓管理和佈建 (Provision) 虛擬機器變得簡單、優雅。Vagrant Box 完全可以隨時扔掉：出問題的時候，可以在幾分鐘內銷毀並重新建立 box！

Homestead 可以在 Windows、macOS 或 Linux 系統上執行，內建 Nginx、PHP、MySQL、PostgreSQL、Redis、Memcached、Node 以及所有其他有助於你開發驚豔 Laravel 專案的軟體。

> {note} 若你使用 Windows，則必須啟用硬體虛擬化 (VT-x)。VT-x 通常可以在你的 BIOS 中啟用。若在 UEFI 系統上使用 Hyper-V，則可能必須另外停用 Hyper-V 才能存取 VT-x。

<a name="included-software"></a>

### 預裝軟體

<style>
    #software-list > ul {
        column-count: 2; -moz-column-count: 2; -webkit-column-count: 2;
        column-gap: 5em; -moz-column-gap: 5em; -webkit-column-gap: 5em;
        line-height: 1.9;
    }
</style>

<div id="software-list" markdown="1">
- Ubuntu 20.04
- Git
- PHP 8.1
- PHP 8.0
- PHP 7.4
- PHP 7.3
- PHP 7.2
- PHP 7.1
- PHP 7.0
- PHP 5.6
- Nginx
- MySQL 8.0
- lmm
- Sqlite3
- PostgreSQL 13
- Composer
- Node (With Yarn, Bower, Grunt, and Gulp)
- Redis
- Memcached
- Beanstalkd
- Mailhog
- avahi
- ngrok
- Xdebug
- XHProf / Tideways / XHGui
- wp-cli
</div>

<a name="optional-software"></a>

### 選裝軟體

<style>
    #software-list > ul {
        column-count: 2; -moz-column-count: 2; -webkit-column-count: 2;
        column-gap: 5em; -moz-column-gap: 5em; -webkit-column-gap: 5em;
        line-height: 1.9;
    }
</style>

<div id="software-list" markdown="1">
- Apache
- Blackfire
- Cassandra
- Chronograf
- CouchDB
- Crystal & Lucky Framework
- Docker
- Elasticsearch
- EventStoreDB
- Gearman
- Go
- Grafana
- InfluxDB
- MariaDB
- Meilisearch
- MinIO
- MongoDB
- Neo4j
- Oh My Zsh
- Open Resty
- PM2
- Python
- R
- RabbitMQ
- RVM (Ruby Version Manager)
- Solr
- TimescaleDB
- Trader <small>(PHP extension)</small>
- Webdriver & Laravel Dusk Utilities
</div>

<a name="installation-and-setup"></a>

## 安裝與設定

<a name="first-steps"></a>

### 第一步

在啟動你的 Homestead 環境之前，你得先安裝 [Vagrant](https://www.vagrantup.com/downloads.html) 和下面其中一個支援的虛擬機軟體：

- [VirtualBox 6.1.x](https://www.virtualbox.org/wiki/Downloads)
- [Parallels](https://www.parallels.com/products/desktop/)

這些所有的軟體，在所有主流作業系統上都有提供簡易的視覺化安裝工具。

若要使用 Parallels Provider，則需要先安裝免費的 [Parallels Vagrant 擴充程式](https://github.com/Parallels/vagrant-parallels)。

<a name="installing-homestead"></a>

#### 安裝 Homestead

若要安裝 Homestead，請將 Homestead 的 ^[Repository](存放庫) ^[Clone](複製) 到你的宿主機上。由於 Homestead 虛擬機會成為執行你所有 Laravel 專案的伺服器主機，因此建議將該 Repository 複製到家目錄中的 `Homestead` 資料夾。在這份說明文件中，我們會將此目錄稱為「Homestead 目錄」：

```bash
git clone https://github.com/laravel/homestead.git ~/Homestead
```

Clone 好 Laravel Homestead 儲存庫之後，請 ^[Checkout](簽出) `release` 分支。此分支為最新穩定版的 Homestead：

    cd ~/Homestead
    
    git checkout release

接下來，請在你的 Homestead 目錄執行 `bash init.sh` 指令，以建立 `Homestead.yaml` 設定檔。`Homestead.yaml` 檔案可以讓你調整 Homestead 實體中的所有設定。這個檔案會放在 Homestead 目錄當中：

    // macOS / Linux...
    bash init.sh
    
    // Windows...
    init.bat

<a name="configuring-homestead"></a>

### 設定 Homestead

<a name="setting-your-provider"></a>

#### 設定 Provider

`Homestead.yaml` 的 `provider` 索引鍵，用來指定要使用的 Vagrant Provider：`virtualbox` 或 `parallels`：

    provider: virtualbox

> {note} 若使用 Apple Silicon，則必須在 `Homestead.yaml` 檔案中加上 `box: laravel/homestead-arm`。Apple Sillicon 上必須使用 Parallels Provider。

<a name="configuring-shared-folders"></a>

#### 設定共享的資料夾

`Homestead.yaml` 檔案中的 `folders` 屬性列出了所有要與 Homestead 環境共享的資料夾。若在本機上修改這些資料夾中的檔案，將會同步到 Homestead 虛擬環境中。可依照需求增加共享資料夾的設定：

```yaml
folders:
    - map: ~/code/project1
      to: /home/vagrant/project1
```

> {note} Windows 使用者無法使用 `~/` 路徑語法，請改用專案的完整路徑，如 `C:\Users\user\Code\project`。

請務必為各個專案分別設定各自的共享資料夾映射 (Mapping)，而不要包含許多專案的一個大資料夾映射到虛擬機內。在映射資料夾時，虛擬機必須隨時追蹤映射目錄下的 **所有** 磁碟讀寫。若資料夾中包含了大量的檔案，可能會影響使用效能。

```yaml
folders:
    - map: ~/code/project1
      to: /home/vagrant/project1
    - map: ~/code/project2
      to: /home/vagrant/project2
```

> {note} 在使用 Homestead 時，千萬不要 ^[Mount](掛載) `.` (即目前目錄)。Vagrant 不會將目前目錄掛載到 `/vagrant`，且會使一些選用功能失效，並在佈建時產生未預期的結果。

若要啟用 [NFS](https://www.vagrantup.com/docs/synced-folders/nfs.html)，可以在資料夾映射中新增 `type` 選項：

    folders:
        - map: ~/code/project1
          to: /home/vagrant/project1
          type: "nfs"

> {note} 在 Windows 上使用 NFS 時，請考慮安裝 [vagrant-winnfsd](https://github.com/winnfsd/vagrant-winnfsd) 外掛。此外掛會在 Homestead 虛擬機中確保檔案與目錄擁有正確的使用者與群組權限正確。

也可以在 `options` 索引鍵中列出其他 Vagrant [Synced Folders](https://www.vagrantup.com/docs/synced-folders/basic_usage.html) 功能所支援的選項：

    folders:
        - map: ~/code/project1
          to: /home/vagrant/project1
          type: "rsync"
          options:
              rsync__args: ["--verbose", "--archive", "--delete", "-zz"]
              rsync__exclude: ["node_modules"]

<a name="configuring-nginx-sites"></a>

### 設定 Nginx 網站

不熟悉 Nginx 嗎？沒問題。在 `Homestead.yaml` 檔案中的 `sites` 屬性可讓你輕鬆將一個「網域 (Domain)」映射到 Homestead 環境中的一個資料夾。在 `Homestead.yaml` 中已包含了一個範例網站設定。你可以依照需求在 Homestead 環境中設定任意數量的網站。Homestead 可為你在進行的所有 Laravel 專案作為一個便利的虛擬化環境：

    sites:
        - map: homestead.test
          to: /home/vagrant/project1/public

若在佈建 Homestead 虛擬機後更改了 `sites` 屬性，則必須在終端機中執行 `vagrant reload --provision` 指令以更新虛擬機內的 Nginx 設定。

> {note} Homestead 的 Script 已儘量做得等冪 (Idempotent)。不過，若在佈建時遇到問題，則請執行 `vagrant destroy && vagrant up` 指令來刪除並重建虛擬機。

<a name="hostname-resolution"></a>

#### Homestead 解析

Homestead 會使用 `mDNS` 來發佈主機名稱以自動進行主機的解析 (Resolution)。若在 `Homestead.yaml` 中設定 `hostname: homestead`，則會自動讓該主機可在 `homestead.local` 上存取。在 macOS、iOS 與 Linux 桌面發佈版中預設包含了 `mDNS` 支援。若使用 Windows，則必須安裝 [Bonjour Print Services (Windows)](https://support.apple.com/kb/DL999?viewlocale=zh_TW&locale=zh_TW)。

自動主機名稱最適合與 Homestead 的[各專案安裝](#per-project-installation)功能。若在單一 Homestead 實體中管理多個網站，則可在你電腦中的 `hosts` 檔案內為各個網站新增其「網域」。`hosts` 檔案會將 Homstead 網站的 Request 重新導向到 Homestead 虛擬機內。在 macOS 與 Linux 中，該檔案位於 `/etc/hosts`。在 Windows 中，該檔案位於 `C:\Windows\System32\drivers\etc\hosts`。新增到該檔案中的內容應該類似這樣：

    192.168.56.56  homestead.test

請確保其中列出的 IP 位址是 `Homestead.yaml` 檔案中所設定的 IP 位址。將網域新增到 `hosts` 檔案並重新開啟 Vagrant Box 後，就可以在網頁瀏覽器中存取這些網站：

```bash
http://homestead.test
```

<a name="configuring-services"></a>

### 設定服務

預設情況下 Homestead 會啟動許多服務。不過，你可能會想在佈建時自訂要啟用或不啟用這些服務。舉例來說，你可以在 `Homestead.yaml` 中調整 `services` 選項，以啟用 PostgreSQL 並禁用 MySQL：

```yaml
services:
    - enabled:
        - "postgresql"
    - disabled:
        - "mysql"
```

指定的服務會依據其在 `enabled` 與 `disabled` 指示詞內的順序來開啟或停止。

<a name="launching-the-vagrant-box"></a>

### 啟動 Vagrant Box

依照需求編輯好 `Homestead.yaml` 後，請在 Homestead 目錄中執行 `vagrant up` 指令。Vagrant 會啟動虛擬機，並設定共享資料夾與 Nginx 網站。

若要刪除虛擬機，可使用 `vagrant destroy` 指令。

<a name="per-project-installation"></a>

### 各專案安裝

除了在全域環境中安裝 Homestead 並在多個專案間共用一個 Homestead 虛擬機，也可以為各個專案設定各自的 Homestead 實體。若在各個專案間想在專案內包含 `Vagrantfile`，則這種方法特別適合。在專案內包含 `Vagrantfile`，就能讓其他參與此專案的人在 Clone 了 Repository 後能馬上執行 `vagrant up`：

可以使用 Composer 套件管理員來講 Homestead 安裝到專案中：

```bash
composer require laravel/homestead --dev
```

安裝好 Homestead 後，請執行 Homestead 的 `make` 指令來為專案產生 `Vagrantfile` 與 `Homestead.yaml` 檔案。這些檔案會被放置在專案的跟目錄。`make` 指令會自動設定 `Homestead.yaml` 檔案中的 `sites` 與 `folders` 指示詞：

    // macOS / Linux...
    php vendor/bin/homestead make
    
    // Windows...
    vendor\\bin\\homestead make

接著，在終端機內執行 `vagrant up` 指令後，就可以在瀏覽器中以 `http://homestead.test` 來存取你的專案。再次提醒，若未使用自動[主機名稱解析](#hostname-resolution)功能，就必須在 `/etc/hosts` 檔案中新增 `homestead.test` 或其他自定網域。

<a name="installing-optional-features"></a>

### 安裝選配功能

選配軟體可以使用 `Homestead.yaml` 中的 `features` 選項來安裝。大多數的功能都可透過布林 (Boolean) 值來啟用或禁用；有部分功能可以設定多個選項：

    features:
        - blackfire:
            server_id: "server_id"
            server_token: "server_value"
            client_id: "client_id"
            client_token: "client_value"
        - cassandra: true
        - chronograf: true
        - couchdb: true
        - crystal: true
        - docker: true
        - elasticsearch:
            version: 7.9.0
        - eventstore: true
            version: 21.2.0
        - gearman: true
        - golang: true
        - grafana: true
        - influxdb: true
        - mariadb: true
        - meilisearch: true
        - minio: true
        - mongodb: true
        - neo4j: true
        - ohmyzsh: true
        - openresty: true
        - pm2: true
        - python: true
        - r-base: true
        - rabbitmq: true
        - rvm: true
        - solr: true
        - timescaledb: true
        - trader: true
        - webdriver: true

<a name="elasticsearch"></a>

#### Elasticsearch

可以在支援版本範圍內指定 Elasticsearch 的版本。指定版本時，應使用完整的版本號碼 (主版號.次版號.修正版號 / major.minor.patch)。預設的 Elasticsearch 安裝會建立一個名為「homestead」的叢集 (Cluster)。在設定 Elasticsearch 的記憶體時，不應設定大於作業系統一半的記憶體量，因此請確保 Homestead 虛擬機的記憶體量是 Elasticsearch 所使用量的兩倍。

> {tip} 請參考 [Elasticsearch 說明文件](https://www.elastic.co/guide/en/elasticsearch/reference/current) 以瞭解如何自定設定。

<a name="mariadb"></a>

#### MariaDB

若啟用 MariaDB，則會將 MySQL 移除並安裝 MariaDB。一般來說 MariaDB 可視為是 MySQL 的替代品，因此在專案的資料庫設定中請繼續使用 `mysql` 資料庫 Driver。

<a name="mongodb"></a>

#### MongoDB

預設的 MongoDB 安裝會將資料庫使用者名稱設為 `homestead`，其密碼為 `secret`。

<a name="neo4j"></a>

#### Neo4j

預設的 Neo4j 安裝會將資料庫使用者名稱設為 `homestead`，並設定密碼 `secret`。若要存取 Neo4j 瀏覽器，請在網頁瀏覽器中瀏覽 `http://homestead.test:7474`。通訊埠 `7687` (Bolt)、`7474` (HTTP) 與 `7473` (HTTPS) 已設定好可處理來自 Neo4j 用戶端的 Request。

<a name="aliases"></a>

### 別名

只要在 Homestead 目錄下修改 `aliases` 檔案，就可以為 Homestead 虛擬機內的 Bash 新增別名 (Alais)：

    alias c='clear'
    alias ..='cd ..'

更新好 `aliases` 檔案後，應使用 `vagrant reload --provision` 指令來重新佈建 Homestead 虛擬機。重新佈建可確保讓新的 Alias 套用到虛擬機裡。

<a name="updating-homestead"></a>

## 更新 Homestead

開始更新 Homestead 前，請先在 Homestead 目錄中執行下列指令來移除目前的虛擬機：

    vagrant destroy

接著，我們需要更新 Homestead 的原始碼。若以 Clone 方式來取得 Repository，則可在之前 Clone 的路徑下執行下列指令：

    git fetch
    
    git pull origin release

這幾個指令會從 GitHub Repository 中 ^[Pull](拉取) 最新的 Homestead 程式碼、取得最新的 Tag、並 ^[Checkout](簽出) 最新的版本。可以在 Homestead 的 [GitHub Releases 頁](https://github.com/laravel/homestead/releases)中找到最新發佈的穩定版。

若使用專案的 `composer.json` 檔案來安裝 Homestead，則請確保 `composer.json` 檔案中有包含 `"laravel/homestead": "^12"`，並更新你的相依性套件：

    composer update

接著，請使用 `vagrant box update` 指令來更新 Vagrant Box：

    vagrant box update

更新好 Vagrant Box 後，請從 Homestead 目錄中執行 `bash init.sh` 指令以更新其他額外的 Homestead 設定檔。在執行該指令時，程式會詢問你是否要覆蓋現有的 `Homestead.yaml`、`after.sh` 與 `aliases` 檔案：

    // macOS / Linux...
    bash init.sh
    
    // Windows...
    init.bat

最後，需要重新產生 Homestead 虛擬機以使用最新的 Vagrant 安裝：

    vagrant up

<a name="daily-usage"></a>

## 日常使用

<a name="connecting-via-ssh"></a>

### 使用 SSH 連線

在 Homestead 目錄下執行 `vagrant ssh` 終端機指令就可 SSH 進虛擬機。

<a name="adding-additional-sites"></a>

### 新增額外的網站

佈建好 Homestead 環境並執行後，我們可能會需要為其他 Laravel 專案來新增額外的 Nginx 網站。在單一 Homestead 環境中，你可以根據需求在其中執行任意數量的 Laravel 專案。若要新增額外的網站，請在 `Homestead.yaml` 中加入網站。

    sites:
        - map: homestead.test
          to: /home/vagrant/project1/public
        - map: another.test
          to: /home/vagrant/project2/public

> {note} 請確保已為該專案目錄設定好[資料夾映射](#configuring-shared-folders)，然後再新增網站。

若 Vagrant 沒有自動管理「hosts」檔案，則還需要將這個新網站加入到 hosts 檔案中。在 macOS 與 Linux 上，該檔案位於 `/etc/hosts`。在 Windows 上，該檔案位於 `C:\Windows\System32\drivers\etc\hosts`：

    192.168.56.56  homestead.test
    192.168.56.56  another.test

新增好網站後，請在 Homestead 目錄下執行 `vagrant reload --provision` 終端機指令。

<a name="site-types"></a>

#### 網站類型

Homestead 支援多種「類型」的網站，能讓你輕鬆執行非 Laravel 的專案。舉例來說，我們可以使用 `statamic` 網站類型來輕鬆地將 Statamic 專案加到 Homestead 中：

```yaml
sites:
    - map: statamic.test
      to: /home/vagrant/my-symfony-project/web
      type: "statamic"
```

可用的網站類型包含：`apache`, `apigility`, `expressive`, `laravel` (預設), `proxy`, `silverstripe`, `statamic`, `symfony2`, `symfony4` 與 `zf`。

<a name="site-parameters"></a>

#### 網站參數

可以使用 `params` site 指示詞來新增額外的 Nginx `fastcgi_param` 值：

    sites:
        - map: homestead.test
          to: /home/vagrant/project1/public
          params:
              - key: FOO
                value: BAR

<a name="environment-variables"></a>

### 環境變數

可以將環境變數加入到 `Homestead.yaml` 檔案來定義全域的環境變數：

    variables:
        - key: APP_ENV
          value: local
        - key: FOO
          value: bar

更新好 `Homestead.yaml` 檔案後，請確保有執行 `vagrant reload --provision` 指令來重新佈建虛擬機。重新佈建虛擬機會更新所有已安裝 PHP 版本的 PHP-FPM 設定值，並同時更新 `vagrant` 使用者的環境。

<a name="ports"></a>

### 通訊埠

預設情況下，下列通訊埠會被 ^[Forward](轉送) 到 Homestead 環境中：

<div class="content-list" markdown="1">

- **HTTP:** 8000 &rarr; Forward 至 80
- **HTTPS:** 44300 &rarr; Forward 至 443

</div>

<a name="forwarding-additional-ports"></a>

#### Forward 額外的通訊埠

若有需要，可以在 `Homestead.yaml` 檔案中定義 `ports` 設定項目來講額外的通訊埠 Forward 到 Vagrant Box。更新好 `Homestead.yaml` 檔案後，請確保有執行 `vagrant reload --provision` 指令來重新佈建虛擬機：

    ports:
        - send: 50000
          to: 5000
        - send: 7777
          to: 777
          protocol: udp

下面列出了一些額外 Homestead 服務的通訊埠。依照需求，你可能會想將這些通訊埠從宿主機 Forward 到 Vagrant Box 中：

<div class="content-list" markdown="1">

- **SSH:** 2222 &rarr; 至 22
- **ngrok UI:** 4040 &rarr; 至 4040
- **MySQL:** 33060 &rarr; 至 3306
- **PostgreSQL:** 54320 &rarr; 至 5432
- **MongoDB:** 27017 &rarr; 至 27017
- **Mailhog:** 8025 &rarr; 至 8025
- **Minio:** 9600 &rarr; 至 9600

</div>

<a name="php-versions"></a>

### PHP 版本

Homestead 第 6 版起支援在同一個虛擬機中執行多個 PHP 版本。可以在 `Homestead.yaml` 檔案中指定某個網站要使用哪個 PHP 版本。可用的 PHP 版本為："5.6", "7.0", "7.1", "7.2", "7.3", "7.4", "8.0" (預設值) 與 "8.1"：

    sites:
        - map: homestead.test
          to: /home/vagrant/project1/public
          php: "7.1"

[在 Homestead 虛擬機中](#connecting-via-ssh)，可以通過 CLI 來使用任一支援的 PHP 版本：

    php5.6 artisan list
    php7.0 artisan list
    php7.1 artisan list
    php7.2 artisan list
    php7.3 artisan list
    php7.4 artisan list
    php8.0 artisan list
    php8.1 artisan list

只要在 Homestead 虛擬機中執行下列指令，就可以更改 CLI 預設使用的 PHP 版本：

    php56
    php70
    php71
    php72
    php73
    php74
    php80
    php81

<a name="connecting-to-databases"></a>

### 連線到資料庫

預設情況下，Homestead 已為 MySQL 與 PostgreSQL 設定好了 `homestead` 資料庫。若要從宿主機的資料庫客戶端連線到 MySQL 或 PostgreSQL 資料庫，則請連線到 `127.0.0.1` 上的 `33060` (MySQL) 或 `54320` (PostgreSQL) 通訊埠。使用者名稱與密碼為 `homestead` / `secret`。

> {note} 當從宿主機中連線到資料庫時，應使用這些非標準的通訊埠。在 Laravel 專案的 `database` 設定檔中，應使用預設的 3306 與 5432 通訊埠，因為 Laravel 是在虛擬機**裡面**執行的。

<a name="database-backups"></a>

### 資料庫備份

Homestead 可以在 Homestead 虛擬機被刪除時自動備份資料庫。若要使用此功能，必須使用 Vagrant 2.1.0 版或更新的版本。或者，若使用較舊版本的 Vagrant，就必須安裝 `vagrant-triggers` 外掛。若要啟用自動資料庫備份，請將下列這行加入到 `Homestead.yaml` 檔案中：

    backup: true

設定好了之後，Homestead 會在執行了 `vagrant destroy` 指令時將資料庫匯出到 `mysql_backup` 與 `postgres_backup` 目錄。這些目錄會被放在 Homestead 的安裝目錄下，或是在使用[個別專案安裝](#per-project-installation)時在專案根目錄下。

<a name="configuring-cron-schedules"></a>

### 設定 Cron 排程

只要設定讓 `schedule:run` Artisan 指令每分鐘執行一次，就可以使用 Laravel 方便的[排程 Cron Job](/docs/{{version}}/scheduling) 功能。`schedule:run` 指令會檢查 `App\Console\Kernel` 類別中定義的 Job 排程，並判斷要執行哪個已排程的任務。

若想讓 Homestead 網站執行 `schedule:run` 指令，則請在定義網站時將 `schedule` 選項設為 `true`：

```yaml
sites:
    - map: homestead.test
      to: /home/vagrant/project1/public
      schedule: true
```

網站的 Cron Job 會被定義在 Homestead 虛擬機中的 `/etc/cron.d` 目錄。

<a name="configuring-mailhog"></a>

### 設定 MailHog

[MailHog](https://github.com/mailhog/MailHog) 能讓你攔截寄出的 Email，並在不實際將郵件寄送給其收件人的情況下檢視該郵件。若要使用 MailHog，請使用下列 Email 設定來更新專案的 `.env` 檔：

    MAIL_MAILER=smtp
    MAIL_HOST=localhost
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null

設定好 MailHog 後，就可以在 `http://localhost:8025` 上存取 MailHog 的主控台。

<a name="configuring-minio"></a>

### 設定 Minio

[Minio](https://github.com/minio/minio) 是一個開放原始碼的物件存放伺服器 (Object Storage Server)，具有與 Amazon S3 相容的 API。若要安裝 Minio，請更新 `Homestead.yaml` 檔案，在 [features](#installing-optional-features) 段落中加入下列設定：

    minio: true

預設情況下，Minio 會在 9600 通訊埠上執行。只要瀏覽 `http://localhost:9600`，就可以存取 Minio 的控制面板。預設的 Access Key 為 `homestead`，而預設的 Secret Key 為 `secretkey`。存取 Minio 時，請使用 Region `us-east-1`。

若要使用 Minio，需要調整專案的 `config/filesystems.php` 設定檔中的 S3 Disk 設定。需要在 Disk 設定中新增 `use_path_style_endpoint` 選項，並將 `url` 索引鍵改為 `endpoint`：

    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'endpoint' => env('AWS_URL'),
        'use_path_style_endpoint' => true,
    ]

最後，請確保 `.env` 檔中有下列設定：

```bash
AWS_ACCESS_KEY_ID=homestead
AWS_SECRET_ACCESS_KEY=secretkey
AWS_DEFAULT_REGION=us-east-1
AWS_URL=http://localhost:9600
```

若要佈建由 Minio 驅動的「S3」^[Bucket](貯體)，請在 `Homestead.yaml` 中新增 `buckets` 指示詞。定義好 Bucket 後，請在終端機中執行 `vagrant reload --provision` 指令：

```yaml
buckets:
    - name: your-bucket
      policy: public
    - name: your-private-bucket
      policy: none
```

支援的 `policy` 值有： `none`, `download`, `upload` 與 `public`。

<a name="laravel-dusk"></a>

### Laravel Dusk

若要在 Homestead 中執行 [Laravel Dusk](/docs/{{version}}/dusk) 測試，請在 Homestead 設定中啟用 [`webdriver` 功能](#installing-optional-features)：

```yaml
features:
    - webdriver: true
```

啟用 `webdriver` 功能後，請在終端機執行 `vagrant reload --provision` 指令。

<a name="sharing-your-environment"></a>

### 分享你的環境

有時候，我們會想將目前在進行的工作進度分享給同事或客戶看。Vagrant 內建了此功能，只需要使用 `vagrant share` 指令即可。不過，若在 `Homestead.yaml` 檔案中設定了多個網站，則此功能將無法使用。

為了解決此問題，Homestead 也包含了自己的 `share` 指令。若要使用此功能，請先使用 `vagrant ssh` 來 [SSH 進你的 Homestead 虛擬機](#connecting-via-ssh)，然後執行 `share homestead.test` 指令。該指令會共享 `Homestead.yaml` 設定檔中設定的 `homstead.test` 網站。可以將 `homestead.test` 替換為其他在 `Homestead.yaml` 中設定的網站：

    share homestead.test

執行該指令後，可以看到 Ngrok 畫面顯示在螢幕上，其中包含了網路活動紀錄，以及此共享網站的公開存取網址。若想自行指定地區、子網域、或是其他 Ngrok 執行階段選項，請將這些選項加到 `share` 指令：

    share homestead.test -region=eu -subdomain=laravel

> {note} 提醒一下，Vagrant 本身應被視為不安全的，而當使用 `share` 指令時，會使 Vagrant 虛擬機被暴露到 ^[Internet](網際網路) 上。

<a name="debugging-and-profiling"></a>

## 除錯與分析

<a name="debugging-web-requests"></a>

### 使用 Xdebug 來對網頁 Request 進行除錯

Homestead 支援使用 [Xdebug](https://xdebug.org) 來進行逐步除錯 (Step Debugging)。舉例來說，當你在瀏覽器中存取網頁時，PHP 會連線到你的 IDE，讓你能檢查並修改正在執行的程式碼。

預設情況下，Xdebug 已在執行並準備好接受任何連線。若需要在 CLI 中啟用 Xdebug，請在 Homestead 虛擬機中執行 `sudo phpenmod xdebug` 指令。接著，請依照 IDE 的說明來啟用除錯功能。最後，請使用瀏覽器擴充功能或是[書籤小程式 (Bookmarklet)](https://www.jetbrains.com/phpstorm/marklets/) 來設定讓瀏覽器觸發 Xdebug。

> {note} 使用 Xdebug 會讓 PHP 的執行速度顯著變慢。若要禁用 Xdebug，請在 Homestead 虛擬機中執行 `sudo phpdismod xdebug` 並重新啟動 FPM 服務。

<a name="autostarting-xdebug"></a>

#### 自動啟動 Xdebug

在針對會向 Web 伺服器開啟 Request 的功能性測試進行除錯時，設定自動啟動除錯會比將傳入自定 Header 或 Cookie 來觸發除錯來得容易。若要強制讓 Xdebug 自動啟動，請修改 Homestead 虛擬機中的 `/etc/php/7.x/fpm/conf.d/20-xdebug.ini` 檔案，並加入下列設定：

```ini
; 若 Homestead.yaml 包含了與該 IP 位址的不同子網路 (Subnet)，則此位址可能會不同...
xdebug.remote_host = 192.168.10.1
xdebug.remote_autostart = 1
```

<a name="debugging-cli-applications"></a>

### 針對 CLI 程式進行除錯

若要針對 PHP CLI 程式進行除錯，請在 Homestead 虛擬機中使用 `xphp` Shell 別名：

    xphp /path/to/script

<a name="profiling-applications-with-blackfire"></a>

### 使用 Blackfire 來針對程式進行分析

[Blackfire](https://blackfire.io/docs/introduction) 是一個可用來分析 (Profiling) Web Request 與 CLI 應用程式的服務。Blackfire 提供了互動性的使用者介面，上面會以呼叫圖 (Call-Graph) 與時間軸來顯示分析資料。Blackfire 可用於開發、測試、與正式環境，並且不會影響到終端使用者。此外，Blackfire 還提供了針對程式碼與 `php.ini` 設定檔的效能、品質、與安全性檢查。

[Blackfire Player](https://blackfire.io/docs/player/index) 是一個開放原始碼的網頁爬蟲 (Crawling)、網頁測試、以及網頁採集 (Scraping) 程式，可用來與 Blackfire 搭配使用以自動化狀況分析。

若要啟用 Blackfire，請使用 Homestead 設定檔中的「features」設定：

```yaml
features:
    - blackfire:
        server_id: "server_id"
        server_token: "server_value"
        client_id: "client_id"
        client_token: "client_value"
```

[需要有 Blackfire 帳號](https://blackfire.io/signup) 才可取得 Blackfire 的 Server ^[Credentials](認證) 與 Client Credentials。Blackfire 提供多種用於分析應用程式的選項，包含使用 CLI 工具，或是使用瀏覽器擴充功能。請[參考 Blackfire 的說明文件以瞭解更多資訊](https://blackfire.io/docs/cookbooks/index)。

<a name="network-interfaces"></a>

## 網路介面

`Homestead.yaml` 檔案的 `networks` 屬性可用來設定 Homestead 虛擬機的網路介面。可以依照需求增加任意數量的網路介面：

```yaml
networks:
    - type: "private_network"
      ip: "192.168.10.20"
```

若要啟用 [bridged](https://www.vagrantup.com/docs/networking/public_network.html) 介面，請設定該網路的 `bridge` 設定，並將網路類型改為 `public_network`：

```yaml
networks:
    - type: "public_network"
      ip: "192.168.10.20"
      bridge: "en1: Wi-Fi (AirPort)"
```

若要啟用 [DHCP](https://www.vagrantup.com/docs/networking/public_network.html)，只需要在設定檔中移除 `ip` 選項即可：

```yaml
networks:
    - type: "public_network"
      bridge: "en1: Wi-Fi (AirPort)"
```

<a name="extending-homestead"></a>

## 擴充 Homestead

使用 Homestead 目錄的 `after.sh` 工序指令，就可以擴充 Homestead。在該檔案內，可以依照需求任意新增 Shell 指令以設定與自定虛擬機。

在自定 Homestead 時，Ubuntu 可能會詢問你是否想保留套件的原始設定檔，或是以全新的設定檔覆蓋。若要避免出現此狀況，請在安裝套件時使用下列指令，以避免複寫掉 Homestead 之前所產生的設定檔：

    sudo apt-get -y \
        -o Dpkg::Options::="--force-confdef" \
        -o Dpkg::Options::="--force-confold" \
        install package-name

<a name="user-customizations"></a>

### 使用者自定

與團隊一起使用 Homestead 時，可能會需要調整 Homestead 以符合你的個人開發風格。若要針對個人自定，請在 Homestead 根目錄下建立 `user-customizations.sh` 檔案 (也就是放在與 `Homestead.yaml` 檔案相同的目錄下)。在該檔案中，可依照個人需求任意調整設定。`user-customizations.sh` 不應被放進版本控制系統中。

<a name="provider-specific-settings"></a>

## Provider 特定的設定

<a name="provider-specific-virtualbox"></a>

### VirtualBox

<a name="natdnshostresolver"></a>

#### `natdnshostresolver`

預設情況下，Homestead 會將 `natdnshostresolver` 設定設為 `on`，好讓 Homestead 能使用宿主作業系統的 DNS 設定。若想調整此行為，請在 `Homestead.yaml` 檔案中加入下列設定：

```yaml
provider: virtualbox
natdnshostresolver: 'off'
```

<a name="symbolic-links-on-windows"></a>

#### Windows 上的符號連結

若在 Windows 裝置上，符號連結 (Symbolic Link) 無法正確使用，則請在 `Vagrantfile` 中新增下列區塊：

```ruby
config.vm.provider "virtualbox" do |v|
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]
end
```
