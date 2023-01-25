---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/165/en-zhtw
progress: 57
updatedAt: '2023-01-25T16:13:00Z'
---

# 升級指南

- [Upgrading To 10.0 From 9.x](#upgrade-10.0)

<a name="high-impact-changes"></a>

## 高度影響的更改

<div class="content-list" markdown="1">

- [更新相依性套件](#updating-dependencies)
- [Updating Minimum Stability](#updating-minimum-stability)

</div>

<a name="medium-impact-changes"></a>

## 中度影響的更改

<div class="content-list" markdown="1">

- [Model "Dates" Property](#model-dates-property)
- [Service Mocking](#serving-mocking)

</div>

<a name="low-impact-changes"></a>

## Low Impact Changes

<div class="content-list" markdown="1">

- [Closure Validation Rule Messages](#closure-validation-rule-messages)
- [Monolog 3](#monolog-3)
- [Query Exception Constructor](#query-exception-constructor)
- [Rate Limiter Return Values](#rate-limiter-return-values)
- [Relation `getBaseQuery` Method](#relation-getbasequery-method)
- [The `Redirect::home` Method](#redirect-home)
- [The `Bus::dispatchNow` Method](#dispatch-now)

</div>

<a name="upgrade-10.0"></a>

## Upgrading To 10.0 From 9.x

<a name="estimated-upgrade-time-??-minutes"></a>

#### Estimated Upgrade Time: 10 Minutes

> **Note** We attempt to document every possible breaking change. Since some of these breaking changes are in obscure parts of the framework only a portion of these changes may actually affect your application. Want to save time? You can use [Laravel Shift](https://laravelshift.com/) to help automate your application upgrades.

<a name="updating-dependencies"></a>

### 相依性套件更新

**受影響的可能：高**

#### PHP 8.1.0 Required

Laravel now requires PHP 8.1.0 or greater.

#### Composer 相依性套件

請在專案的 `composer.json` 檔案中更新下列相依性套件：

<div class="content-list" markdown="1">

- `laravel/framework` to `^10.0`
- `spatie/laravel-ignition` to `^2.0`

</div>

#### Minimum Stability

You should update the `minimum-stability` setting in your application's `composer.json` file to `stable`:

```json
"minimum-stability": "stable",
```

### 資料庫

<a name="query-exception-constructor"></a>

#### Query Exception Constructor

**受影響的可能：非常低**

The `Illuminate\Database\QueryException` constructor now accepts a string connection name as its first argument. If your application is mainly throwing this exception, you should adjust your code accordingly.

### Eloquent

<a name="model-dates-property"></a>

#### Model "Dates" Property

**受影響的可能性：中等**

The Eloquent model's deprecated `$dates` property has been removed. Your application should now use the `$casts` property:

```php
protected $casts = [
    'deployed_at' => 'datetime',
];
```

<a name="relation-getbasequery-method"></a>

#### Relation `getBaseQuery` Method

**受影響的可能：非常低**

The `getBaseQuery` method on the `Illuminate\Database\Eloquent\Relations\Relation` class has been renamed to `toBase`.

### Logging

<a name="monolog-3"></a>

#### Monolog 3

**受影響的可能：低**

Laravel's Monolog dependency has been updated to Monolog 3.x. If you are directly interacting with Monolog within your application, you should review Monolog's [upgrade guide](https://github.com/Seldaek/monolog/blob/main/UPGRADE.md).

### Queues

<a name="dispatch-now"></a>

#### The `Bus::dispatchNow` Method

**受影響的可能：低**

The deprecated `Bus::dispatchNow` and `dispatch_now` methods have been removed. Instead, your application should use the `Bus::dispatchSync` and `dispatch_sync` methods, respectively.

### 路由

<a name="rate-limiter-return-values"></a>

#### Rate Limiter Return Values

**受影響的可能：低**

When invoking the `RateLimiter::attempt` method, the value returned by the provided closure will now be returned by the method. If nothing or `null` is returned, the `attempt` method will return `true`:

```php
$value = RateLimiter::attempt('key', 10, fn () => ['example'], 1);

$value; // ['example']
```

<a name="redirect-home"></a>

#### The `Redirect::home` Method

**受影響的可能：非常低**

The deprecated `Redirect::home` method has been removed. Instead, your application should redirect to an explicitly named route:

```php
return Redirect::route('home');
```

### 測試

<a name="service-mocking"></a>

#### Service Mocking

**受影響的可能性：中等**

The deprecated `MocksApplicationServices` trait has been removed from the framework. This trait provided testing methods such as `expectsEvents`, `expectsJobs`, and `expectsNotifications`.

If your application uses these methods, we recommend you transition to `Event::fake`, `Bus::fake`, and `Notification::fake`, respectively. You can learn more about mocking via the complete [mocking documentation](/docs/{{version}}/mocking).

### 表單驗證

<a name="closure-validation-rule-messages"></a>

#### Closure Validation Rule Messages

**受影響的可能：非常低**

When writing closure based custom validation rules, invoking the `$fail` callback more than once will now append the messages to an array instead of overwriting the previous message. Typically, this will not affect your application.

In addition, the `$fail` callback now returns an object. If you were previously type-hinting the return type of your validation closure, this may require you to update your type-hint:

```php
public function rules()
{
    'name' => [
        function ($attribute, $value, $fail) {
            $fail('validation.translation.key')->translate();
        },
    ],
}
```

<a name="miscellaneous"></a>

### 其他

We also encourage you to view the changes in the `laravel/laravel` [GitHub repository](https://github.com/laravel/laravel). While many of these changes are not required, you may wish to keep these files in sync with your application. Some of these changes will be covered in this upgrade guide, but others, such as changes to configuration files or comments, will not be.

You can easily view the changes with the [GitHub comparison tool](https://github.com/laravel/laravel/compare/9.x...10.x) and choose which updates are important to you. However, many of the changes shown by the GitHub comparison tool are due to our organization's adoption of PHP native types. These changes are backwards compatible and the adoption of them during the migration to Laravel 10 is optional.
