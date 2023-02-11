---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/65/en-zhtw
progress: 100
updatedAt: '2023-02-11T10:27:00Z'
---

# Laravel Envoy

- [簡介](#introduction)
- [安裝](#installation)
- [撰寫任務](#writing-tasks)
   - [定義任務](#defining-tasks)
   - [多伺服器](#multiple-servers)
   - [設定](#setup)
   - [變數](#variables)
   - [Story](#stories)
   - [Hook](#completion-hooks)
- [執行任務](#running-tasks)
   - [確認任務的執行](#confirming-task-execution)
- [通知](#notifications)
   - [Slack](#slack)
   - [Discord](#discord)
   - [Telegram](#telegram)
   - [Microsoft Teams](#microsoft-teams)

<a name="introduction"></a>

## 簡介

[Laravel Envoy](https://github.com/laravel/envoy) 是一個用於在遠端伺服器上執行通用性任務的工具。只要使用 [Blade](/docs/{{version}}/blade) 風格的語法，就能輕鬆地設定部署任務、執行 Artisan 指令…等更多的任務。目前，Envoy 只支援 Mac 與 Linux 作業系統。不過，在 Windows 上可以通過 [WSL2](https://docs.microsoft.com/zh-tw/windows/wsl/install-win10) 來支援。

<a name="installation"></a>

## 安裝

首先，使用 Composer 套件管理員來將 Envoy 裝到你的專案中：

    composer require laravel/envoy --dev

安裝好 Envoy 後，Envoy 執行檔就會被放在專案的 `vendor/bin` 目錄下：

    php vendor/bin/envoy

<a name="writing-tasks"></a>

## 撰寫任務

<a name="defining-tasks"></a>

### 定義任務

「任務」是 Envoy 中基本的建置區塊。任務定義了當某個任務被呼叫時，要在遠端伺服器上執行哪些 Shell 指令。舉例來說，可以定義一個會在專案的所有 Queue Worker 伺服器上執行 `php artisan queue:restart` 指令的任務。

所有的 Envoy 任務都應定義在專案根目錄上的 `Envoy.blade.php` 檔中。我們來看看一個入門範例：

```bash
@servers(['web' => ['user@192.168.1.1'], 'workers' => ['user@192.168.1.2']])

@task('restart-queues', ['on' => 'workers'])
    cd /home/user/example.com
    php artisan queue:restart
@endtask
```

就像這樣，檔案最上方定義了一組 `@servers` 的陣列，可以在定義任務的 `on` 選項中參照到這些伺服器。`@servers` 定義必須保持在同一行內。在 `@task` 定義內，應放置所有該任務在伺服器上被呼叫時要執行的 Shell 指令。

<a name="local-tasks"></a>

#### 本機任務

只要將伺服器的 IP 指定為 `127.0.0.1`，就可以強制讓某個 Script 在你的本機電腦上執行：

```bash
@servers(['localhost' => '127.0.0.1'])
```

<a name="importing-envoy-tasks"></a>

#### 匯入 Envoy 任務

使用 `@import` 指示詞即可匯入其他 Envoy 檔案，讓其他 Envoy 檔案中的 Story 與任務變成目前這個檔案內的 Story 與任務。匯入的檔案後，就呼叫匯入檔案內的定義：

```bash
@import('vendor/package/Envoy.blade.php')
```

<a name="multiple-servers"></a>

### 多個伺服器

在 Envoy 中，我們可以輕鬆地在多個伺服器上執行同一個任務。首先，在 `@servers` 定義中加上更多的伺服器。請為各個伺服器指定一個不重複的名稱。定義好伺服器後，就可以在任務的 `on` 陣列中列出這些伺服器：

```bash
@servers(['web-1' => '192.168.1.1', 'web-2' => '192.168.1.2'])

@task('deploy', ['on' => ['web-1', 'web-2']])
    cd /home/user/example.com
    git pull origin {{ $branch }}
    php artisan migrate --force
@endtask
```

<a name="parallel-execution"></a>

#### 平行執行

預設情況下，任務會在各個伺服器間依序執行。也就是說，某個任務會先在第一個伺服器上執行，然後才在第二個伺服器上執行。若想讓某個任務在多個伺服器間平行執行，請在任務定義中加上 `parallel` 選項：

```bash
@servers(['web-1' => '192.168.1.1', 'web-2' => '192.168.1.2'])

@task('deploy', ['on' => ['web-1', 'web-2'], 'parallel' => true])
    cd /home/user/example.com
    git pull origin {{ $branch }}
    php artisan migrate --force
@endtask
```

<a name="setup"></a>

### 設定

有時候，我們需要在 Envoy 任務前先執行一些 PHP 程式碼。可以使用 `@setup` 指示詞來定義一組 PHP 程式碼區塊，這個程式碼區塊會在任務前執行：

```php
@setup
    $now = new DateTime;
@endsetup
```

若有需要在任務執行前 require 其他的 PHP 的哪敢，可以在 `Envoy.blade.php` 檔案的頂端使用 `@include` 指示詞：

```bash
@include('vendor/autoload.php')

@task('restart-queues')
    # ...
@endtask
```

<a name="variables"></a>

### 變數

若有需要，可以在執行 Envoy 時在指令列上指定要傳給 Envoy 的引數：

    php vendor/bin/envoy run deploy --branch=master

可以使用 Blade 的「echo」語法來在任務中存取這些選項。在任務中，也可以定義 Blade 的 `if` 陳述式與迴圈。舉例來說，我們來看看一個在執行 `git pull` 指令前先檢查 `$branch` 變數是否存在的範例：

```bash
@servers(['web' => ['user@192.168.1.1']])

@task('deploy', ['on' => 'web'])
    cd /home/user/example.com

    @if ($branch)
        git pull origin {{ $branch }}
    @endif

    php artisan migrate --force
@endtask
```

<a name="stories"></a>

### Story

「Story」是一組任務，通過「Story」可以將這組任務放在單一一個名稱下以方便重複使用。舉例來說，若要讓 `deploy` Story 執行 `update-code` 與 `install-dependencies` 任務，我們只需要在 `deploy` 這個 Story 的定義中分別列出這些任務即可：

```bash
@servers(['web' => ['user@192.168.1.1']])

@story('deploy')
    update-code
    install-dependencies
@endstory

@task('update-code')
    cd /home/user/example.com
    git pull origin master
@endtask

@task('install-dependencies')
    cd /home/user/example.com
    composer install
@endtask
```

寫好 Story 後，就可以像執行任務一樣執行 Story：

    php vendor/bin/envoy run deploy

<a name="completion-hooks"></a>

### Hook

在執行任務與 Story 時，也會執行到多個 Hook。Envoy 支援的 Hook 類型為 `@before`、`@after`、`@error`、`@success`、與 `@finished`。這些 Hook 中所有的程式碼都會在本機上以 PHP 執行，而不是在執行任務的遠端伺服器上執行：

可以依照需求任意定義這些 Hook。這些 Hook 會以在 Envoy 指令稿內出現的順序執行：

<a name="hook-before"></a>

#### `@before`

在執行各個任務之前，會執行所有在 Envoy 指令稿內註冊的 `@before` hook 。`@before` Hook 會收到正在執行的任務名稱：

```php
@before
    if ($task === 'deploy') {
        // ...
    }
@endbefore
```

<a name="completion-after"></a>

#### `@after`

在執行各個任務之後，會執行所有在 Envoy 指令稿內註冊的 `@after` hook 。`@after` Hook 會收到剛才執行完畢的任務名稱：

```php
@after
    if ($task === 'deploy') {
        // ...
    }
@endafter
```

<a name="completion-error"></a>

#### `@error`

當有任何任務失敗 (終止代碼大於 `0` 時)，會執行所有在 Envoy 指令稿內註冊的 `@error` hook 。`@error` Hook 會收到剛才執行完畢的任務名稱：

```php
@error
    if ($task === 'deploy') {
        // ...
    }
@enderror
```

<a name="completion-success"></a>

#### `@success`

若所有任務都完成執行且沒有產生錯誤，則會執行所有 Envoy 指令稿內註冊的 `@success` Hook：

```bash
@success
    // ...
@endsuccess
```

<a name="completion-finished"></a>

#### `@finished`

當所有任務都執行完畢後 (無論終止狀態碼為何)，會執行所有 `@finished` Hook。`@finished` Hook 會收到已完成任務的終止狀態碼，該狀態碼可能是 `null`，或是大於或等於 `0` 的 `integer`：

```bash
@finished
    if ($exitCode > 0) {
        // 其中一個任務有錯誤...
    }
@endfinished
```

<a name="running-tasks"></a>

## 執行任務

若要執行專案中 `Envoy.blade.php` 檔案所定義的任務或 Story，可以執行 Envoy 的 `run` 指令，並傳入要執行的任務或 Story 名稱。Envoy 會執行該任務，並在執行任務時顯示遠端伺服器上的輸出：

    php vendor/bin/envoy run deploy

<a name="confirming-task-execution"></a>

### 確認任務的執行

若想在任務於遠端伺服器上執行前提示確認，請在任務定義中加上 `confirm` 指示詞。對於一些破壞性的操作，就特別適合這個選項：

```bash
@task('deploy', ['on' => 'web', 'confirm' => true])
    cd /home/user/example.com
    git pull origin {{ $branch }}
    php artisan migrate
@endtask
```

<a name="notifications"></a>

## 通知

<a name="slack"></a>

### Slack

Envoy 支援在當任務執行完畢後將通知傳送給 [Slack](https://slack.com)。`@slack` 指示詞接受一組 Slack Hook URL 與一組 Channel (頻道) / User name (使用者名稱)。可以在 Slack 的控制面板中建立「Incoming WebHooks (連入 Webhook)」來取得一組 Webhook URL。

請將完整的 Webhook URL 作為第一個引數傳給 `@slack` 指示詞。傳給 `@slack` 指示詞的第二個引數應為頻道名稱 (`#channel`) 或使用者名稱 (`@user`)：

    @finished
        @slack('webhook-url', '#bots')
    @endfinished

預設情況下，Envoy 會傳送描述已執行任務的訊息給通知頻道。不過，只要傳入第三個引數給 `@slack` 指示詞，就可以使用自定訊息來複寫這個訊息：

    @finished
        @slack('webhook-url', '#bots', 'Hello, Slack.')
    @endfinished

<a name="discord"></a>

### Discord

Envoy 也支援在各個任務執行完畢後傳送通知給 [Discord](https://discord.com)。`@discord` 指示詞接受一個 Discord Hook URL 與訊息。若要取得 Webhook URL，請在 Dsicrod 的伺服器設定建立一個「Webhook」，並指定該 Webhook 要傳送訊息到哪個頻道。請將完整的 Webhook URL 傳入 `@discord` 指示詞內：

    @finished
        @discord('discord-webhook-url')
    @endfinished

<a name="telegram"></a>

### Telegram

Envoy 也支援在各個任務執行完畢後傳送通知到 [Telegram](https://telegram.org)。`@telegram` 指示詞接受一個 Telegram Bot ID 與一個 Chat ID。若要取得 Bot ID，可以使用 [BotFather](https://t.me/botfather) 來建立一個新的 Bot。使用 [@username_to_id_bot](https://t.me/username_to_id_bot) 即可取得有效的 Chat ID。請傳入完整的 Bot ID 與 Chat ID 給 `@telegram` 指示詞：

    @finished
        @telegram('bot-id','chat-id')
    @endfinished

<a name="microsoft-teams"></a>

### Microsoft Teams

Envoy 也支援在任務執行完成後傳送通知給 [Microsoft Teams](https://www.microsoft.com/en-us/microsoft-teams)。`@microsoftTeams` 指示詞的引數為一個 Teams Webhook (必填)、一個訊息、主題色 (success、info、warning、error)、與一組選項陣列。若要取得 Teams Webhook，請建立一個新的 [連入 Webhook](https://docs.microsoft.com/zh-tw/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook)。Teams 的 API 還有其他選項，可用在自定 Message Box，如標題、摘要、段落等。更多資訊請參考 [Microsoft Teams 的說明文件](https://docs.microsoft.com/zh-tw/microsoftteams/platform/webhooks-and-connectors/how-to/connectors-using?tabs=cURL#example-of-connector-message)。請傳入完整的 Webhook URL 給 `@microsoftTeams` 指示詞：

    @finished
        @microsoftTeams('webhook-url')
    @endfinished
