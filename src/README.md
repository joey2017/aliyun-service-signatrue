
# Laravel-Aliyun-service-signatrue
*[阿里云服务api请求签名示例]*

------

## 环境和程序要求

| 程序 | 版本 |
| -------- | -------- |
| PHP| `>= 7.1` |
| MySQL| `>= 5.5` |
| laravel/laravel| `>= 5.5` |

----

## 安装
* 通过composer，这是推荐的方式，可以使用composer.json 声明依赖，或者直接运行下面的命令。

```shell
 composer require leezj/laravel-aliyun-service-signature

```
 
* 放入composer.json文件中

```json
"require": {
    "leezj/laravel-aliyun-signatrue": "*"
}
```    
 然后运行
```shell
composer update
```

----

## 使用

添加服务提供商
```
'providers' => [
    ...
    Aliyun\Signatrue\AliyunServiceProvider::class,
]
```
2.发布配置文件
```shell
php artisan vendor:publish --provider="Aliyun\Signatrue\AliyunServiceProvider"
```
> 此命令会在 config 目录下生成一个 `aliyun.php` 配置文件，你可以在此进行自定义配置。

3. 代码使用
```php
// 成功返回 第一个参数可接受item和resource
return $this->success($user,$meta);
