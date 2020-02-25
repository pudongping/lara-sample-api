# Laravel Shop 接口文档

- [登录注册](#login-register)
    - [手机号登录注册](#phone-register)
    - [客户端直接传递 openid 方式登录注册（客户端做授权认证）](#openid-register)
    - [第三方授权登录（服务端做授权认证）](#social-register)
    - [已经登录的用户，提供 openid 绑定社交账号（授权认证客户端已完成）](#bound-openid-in-client)
    - [已经登录的用户，绑定社交账号（授权认证需在服务端完成）](#bound-openid-in-server)


<a name="login-register"></a>
## 登录注册

<a name="phone-register"></a>
### 手机号登录注册

#### 登录场景下：

1. POST-用户名/手机号/邮箱 登录-api/authorizations

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
account | 是 | 账号：支持用户名/中国手机号/邮箱 | a1234/15549328669/abc@qq.com
password | 是 | 密码（不低于6个字符的字符串） | 123456
remember | 否 | 是否开启记住我 | 1=记住 或者 0=不记住

```
用户名： /^[a-zA-Z]([-_a-zA-Z0-9]{3,20})+$/
手机号：/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/
电子邮箱：/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/
```

返回说明

正确时返回的JSON数据包如下：

```
{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 13:44:17",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhLXNhbXBsZS1hcGkudGVzdFwvYXBpXC9hdXRob3JpemF0aW9ucyIsImlhdCI6MTU4MjYwOTQ1NywiZXhwIjoxNTgyNjEzMDU3LCJuYmYiOjE1ODI2MDk0NTcsImp0aSI6IndOWm96ODhqR3dLSnBjaUgiLCJzdWIiOjEsInBydiI6IjEzZThkMDI4YjM5MWYzYjdiNjNmMjE5MzNkYmFkNDU4ZmYyMTA3MmUifQ.BTdSohlkmgUxcadbJ4B0FZoAw090-ZKLUAQJgfzFGbU",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}

```

参数 | 描述
--- | ---
access_token | 访问 token
token_type | token 授权方式为 Authorization Bearer 方式
expires_in | access_token 有效期，默认为 3600 秒

> 当用户只用了社交账号注册登录后，但是想通过手机号登录，此时则需要用户重置下密码才可以。

#### 注册场景下：

1. POST-获取图片验证码-api/captchas

参数：无

返回说明

正确时返回的JSON数据包如下：

```
{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-24 22:15:57",
    "data": {
        "captcha_key": "captcha-bpiRuCbGS1aPhmW",
        "expired_at": "2020-02-24 22:17:57",
        "captcha_image_content": ""
    }
}

```

参数 | 描述
--- | ---
captcha_key | 图片验证码的 key
expired_at | 图片验证码的有效期（图片验证码只能使用一次，验证失败后需要重新获取，默认有效期 2 分钟）
captcha_image_content | 宽 150px， 高40px的 `base64` 5 个字节的图片验证码，不区分大小写

---

2. POST-检验登录字段-api/checkRegister

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
phone | 是 | 手机号 | 15549328669
password | 是 | 密码 | 123456
captcha_key | 是 | 图片验证码的 key（调用获取图片验证码的接口时会返回的 captcha_key）| captcha-bpiRuCbGS1aPhmW
captcha_code | 是 | 图片验证码 | 6hqcg （5个字节，不区分大小写）

返回说明

正确时返回的JSON数据包如下：

```

{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 01:11:03",
    "data": {
        "register_key": "checkRegister-PpRa6BHXn3ch65s",
        "expired_at": "2020-02-25 01:16:03"
    }
}

```

参数 | 描述
--- | ---
register_key | 返回的注册 key
expired_at | 注册 key 的有效时间 （默认有效期为 5 分钟）

> 接口调用之后会自动发送手机验证码

---

3. POST-手机号注册-api/register

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
register_key | 是 | 注册 key | checkRegister-IAEAnBTXRFPl5eO
phone_code | 是 | 手机验证码 | 123456

返回说明

正确时返回的JSON数据包如下：

```
{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 01:27:36",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhLXNhbXBsZS1hcGkudGVzdFwvYXBpXC9yZWdpc3RlciIsImlhdCI6MTU4MjU2NTI1NiwiZXhwIjoxNTgyNTY4ODU2LCJuYmYiOjE1ODI1NjUyNTYsImp0aSI6ImxSdFVkSUNTQ29OSTBUTkciLCJzdWIiOjIsInBydiI6IjEzZThkMDI4YjM5MWYzYjdiNjNmMjE5MzNkYmFkNDU4ZmYyMTA3MmUifQ.BZX-yBSO4HaUcKb7WivpfqG0gK6TQ7PgingkgFlPnr4",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}

```

参数 | 描述
--- | ---
access_token | 访问 token
token_type | token 授权方式为 Authorization Bearer 方式
expires_in | access_token 有效期，默认为 3600 秒

> 手机号的方式注册成功之后，会自动登录。

<a name="openid-register"></a>
### 客户端直接传递 openid 方式登录注册（客户端做授权认证）

1. POST-openid 登录检测是否已经绑定了手机号-api/checkBoundPhone/:social_type

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
social_type | 是 | url 链接地址参数，授权登录的类型 | 目前仅支持：weixin、weibo、qyweixin
openid | 是 | 第三方授权方式的唯一标识，比如微信为 openid、 企业微信为 userId | oHt9G1HJqhNFbXPUJKZMEVig
unionid | 否 | 如果是微信的话，可能会含有 unionid | oHt9G1HJqhNFbXPUJKZMEVig

返回说明

正确时返回的JSON数据包如下：

```
{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 10:14:38",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhLXNhbXBsZS1hcGkudGVzdFwvYXBpXC9jaGVja0JvdW5kUGhvbmVcL3dlaXhpbiIsImlhdCI6MTU4MjU5Njg3OCwiZXhwIjoxNTgyNjAwNDc4LCJuYmYiOjE1ODI1OTY4NzgsImp0aSI6IlYyNENiNzJ6djFuZXRxOHEiLCJzdWIiOjEsInBydiI6IjEzZThkMDI4YjM5MWYzYjdiNjNmMjE5MzNkYmFkNDU4ZmYyMTA3MmUifQ.HN-I2qr7h-vVPvSa7JN6CLVK0kQ-xmPGeLtWrJLvELg",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

参数 | 描述
--- | ---
access_token | 访问 token
token_type | token 授权方式为 Authorization Bearer 方式
expires_in | access_token 有效期，默认为 3600 秒

> 如果用户已经绑定了手机号（可以理解为用户是在进行 `登录` 操作），则会直接登录成功。如果用户没有绑定手机号（可以理解为用户是在进行 `注册`操作），那么就需要进行以下流程。

没有绑定手机号时，会返回的 JSON 数据包如下：

```

{
    "code": 20006,
    "msg": "手机号未绑定",
    "time": "2020-02-25 13:54:00"
}

```

2. POST-发送短信验证码-api/verificationCodes

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
phone | 是 | 中国手机号 | 18502728041

返回说明

正确时返回的JSON数据包如下：

```

{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 10:22:55",
    "data": {
        "phone_key": "verificationCode_UTJbuSXROfihqTk",
        "expired_at": "2020-02-25 10:27:55"
    }
}

```

参数 | 描述
--- | ---
phone_key | 手机 key
expired_at | 手机短信验证码过期时间（默认 5 分钟后过期）

3. POST-openid 第一次登录（需先获取短信验证码）-api/socials/:social_type/login

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
social_type | 是 | url 链接地址参数，授权登录的类型 | 目前仅支持：weixin、weibo、qyweixin
openid | 是 | 第三方授权方式的唯一标识，比如微信为 openid、 企业微信为 userId | oHt9G1HJqhNFbXPUJKZMEVig
phone_key | 是 | 获取短信验证码接口时，返回的手机 key | verificationCode_UTJbuSXROfihqTk
phone_code | 是 | 接收到的短信验证码 | 123456

返回说明

正确时返回的JSON数据包如下：

```
{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 10:55:01",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhLXNhbXBsZS1hcGkudGVzdFwvYXBpXC9zb2NpYWxzXC93ZWl4aW5cL2xvZ2luIiwiaWF0IjoxNTgyNTk5MzAxLCJleHAiOjE1ODI2MDI5MDEsIm5iZiI6MTU4MjU5OTMwMSwianRpIjoibDBtYkVhWFpDdVdPMnhVQiIsInN1YiI6MywicHJ2IjoiMTNlOGQwMjhiMzkxZjNiN2I2M2YyMTkzM2RiYWQ0NThmZjIxMDcyZSJ9.ZnJHaW5DaBQF7KCpQm3WwHJHhVk8182Y1-l3dyTIuGc",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

参数 | 描述
--- | ---
access_token | 访问 token
token_type | token 授权方式为 Authorization Bearer 方式
expires_in | access_token 有效期，默认为 3600 秒

> 注册成功后会直接登录成功


<a name="social-register"></a>
### 第三方授权登录（服务端做授权认证）

#### 登录场景下：

1. POST-第三方授权登录（服务端授权）-api/socials/:social_type/authorizations

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
social_type | 是 | url 链接地址参数，授权登录的类型 | 目前仅支持：weixin、weibo、qyweixin
code | 是（只传 code 情况下）/ 否 （当传 access_token 情况下） | 第三方授权 code | 5e8494f888cdb8ea1e3806dc35027db8
access_token | 是（只传 access_token 情况下） / 否 （当传 code 情况下）| 第三方授权访问令牌 | 30_VbFmbVAVDN0NnYSlIuDCxikSMOqvDZDFJO3rGQM85jFrRxWqIu5tSjJ3lyZ3NyW2OmuhQg-v3dqGMUZg1G1ALg
openid | 否（当微信授权登录时，且传递 access_token 的情况下，则为必须） | 微信用户的唯一标识 | oHt9G1HJqhNFbXPUJKZMEVig

> code 和 access_token 参数理论上传参时相互斥，但如果不小心 code 和 access_token 同时传递，那么会优先采用 code 去获取认证。（微信企业号授权时，建议只传参 code，因为服务端获取 `UserId` 时，需要同时含有 access_token 和 code参数，只传 code 会减少前端工作量）

返回说明

正确时返回的JSON数据包如下：

```

{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 13:18:05",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhLXNhbXBsZS1hcGkudGVzdFwvYXBpXC9zb2NpYWxzXC93ZWl4aW5cL2F1dGhvcml6YXRpb25zIiwiaWF0IjoxNTgyNjA3ODg1LCJleHAiOjE1ODI2MTE0ODUsIm5iZiI6MTU4MjYwNzg4NSwianRpIjoiaHJBcUNXZ0ZYN0VTNTMzYiIsInN1YiI6NCwicHJ2IjoiMTNlOGQwMjhiMzkxZjNiN2I2M2YyMTkzM2RiYWQ0NThmZjIxMDcyZSJ9.PQb1yh8tWZnjphfRt0sB-ETZKCvUA9Ntt97FaLz4NA8",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}

```

参数 | 描述
--- | ---
access_token | 访问 token
token_type | token 授权方式为 Authorization Bearer 方式
expires_in | access_token 有效期，默认为 3600 秒

---

#### 注册场景下：

1. POST-第三方授权登录（服务端授权）-api/socials/:social_type/authorizations

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
social_type | 是 | url 链接地址参数，授权登录的类型 | 目前仅支持：weixin、weibo、qyweixin
code |  是（只传 code 情况下）/ 否 （当传 access_token 情况下）  | 第三方授权 code | 5e8494f888cdb8ea1e3806dc35027db8
access_token | 是（只传 access_token 情况下） / 否 （当传 code 情况下） | 第三方授权访问令牌 | 30_VbFmbVAVDN0NnYSlIuDCxikSMOqvDZDFJO3rGQM85jFrRxWqIu5tSjJ3lyZ3NyW2OmuhQg-v3dqGMUZg1G1ALg
openid | 否（当微信授权登录时，且传递 access_token 的情况下，则为必须） | 微信用户的唯一标识 | oHt9G1HJqhNFbXPUJKZMEVig

> code 和 access_token 参数理论上传参时相互斥，但如果不小心 code 和 access_token 同时传递，那么会优先采用 code 去获取认证。（微信企业号授权时，建议只传参 code，因为服务端获取 `UserId` 时，需要同时含有 access_token 和 code参数，只传 code 会减少前端工作量）

返回说明

正确时返回的JSON数据包如下：

```

{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 12:31:43",
    "data": {
        "social_user_key": "social_user-9gf0wmRevvcTnSs",
        "expired_at": "2020-02-25 12:41:43"
    }
}

```

参数 | 描述
--- | ---
social_user_key | 第三方授权时的注册 key
expired_at | 授权时的注册 key 的过期时间 （默认 10 分钟后过期）

> 注册场景下，如果多次访问此接口会将 `social_user_key` 重新生成，如果之前的 `social_user_key` 没有过期，则仍然有效。

2. POST-发送短信验证码-api/verificationCodes

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
phone | 是 | 中国手机号 | 18502728041

返回说明

正确时返回的JSON数据包如下：

```

{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 12:57:43",
    "data": {
        "phone_key": "verificationCode_vIqYbyK0nSt0s0a",
        "expired_at": "2020-02-25 13:02:43"
    }
}

```

参数 | 描述
--- | ---
phone_key | 手机 key
expired_at | 手机短信验证码过期时间（默认 5 分钟后过期）

3. POST-openid 第一次登录（需先获取短信验证码）-api/socials/:social_type/login

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
social_type | 是 | url 链接地址参数，授权登录的类型（这里需要和第一步中的 `social_type` 保持一致） | 目前仅支持：weixin、weibo、qyweixin
phone_key | 是 | 获取短信验证码接口时，返回的手机 key | verificationCode_vIqYbyK0nSt0s0a
phone_code | 是 | 接收到的短信验证码 | 123456
social_user_key | 是 | 第三方授权时的注册 key（第一步中返回的 social_user_key） | social_user-9gf0wmRevvcTnSs

返回说明

正确时返回的JSON数据包如下：

```
{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 12:58:37",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhLXNhbXBsZS1hcGkudGVzdFwvYXBpXC9zb2NpYWxzXC93ZWl4aW5cL2xvZ2luIiwiaWF0IjoxNTgyNjA2NzE3LCJleHAiOjE1ODI2MTAzMTcsIm5iZiI6MTU4MjYwNjcxNywianRpIjoieElISU55TlpGQUlmcGVnRCIsInN1YiI6NCwicHJ2IjoiMTNlOGQwMjhiMzkxZjNiN2I2M2YyMTkzM2RiYWQ0NThmZjIxMDcyZSJ9.2JrgUHlBJ-agrKcC4668vAjpozmy67WUDc0a2Nwz-ew",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

参数 | 描述
--- | ---
access_token | 访问 token
token_type | token 授权方式为 Authorization Bearer 方式
expires_in | access_token 有效期，默认为 3600 秒

> 注册成功后会直接登录成功


<a name="bound-openid-in-client"></a>

### 已经登录的用户，提供 openid 绑定社交账号（授权认证客户端已完成）

1. PATCH-用户登录之后提供 openid 绑定第三方授权-api/user/:social_type/bound

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
social_type | 是 | url 链接地址参数，授权登录的类型 | 目前仅支持：weixin、weibo、qyweixin
openid | 是 | 第三方授权方式的唯一标识，比如微信为 openid、 企业微信为 userId | oHt9G1HJqhNFbXPUJKZMEVig
unionid | 否 | 如果是微信的话，可能会含有 unionid | oHt9G1HJqhNFbXPUJKZMEVig

返回说明

正确时返回的JSON数据包如下：

```
{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 14:14:49"
}

```

<a name="bound-openid-in-server"></a>

### 已经登录的用户，绑定社交账号（授权认证需在服务端完成）

1. POST-第三方授权登录（服务端授权）-api/socials/:social_type/authorizations

参数说明

参数 | 是否必须 | 说明 | 例子
--- | --- | --- |---
social_type | 是 | url 链接地址参数，授权登录的类型 | 目前仅支持：weixin、weibo、qyweixin
code |  是（只传 code 情况下）/ 否 （当传 access_token 情况下）  | 第三方授权 code | 5e8494f888cdb8ea1e3806dc35027db8
access_token | 是（只传 access_token 情况下） / 否 （当传 code 情况下） | 第三方授权访问令牌 | 30_VbFmbVAVDN0NnYSlIuDCxikSMOqvDZDFJO3rGQM85jFrRxWqIu5tSjJ3lyZ3NyW2OmuhQg-v3dqGMUZg1G1ALg
openid | 否（当微信授权登录时，且传递 access_token 的情况下，则为必须） | 微信用户的唯一标识 | oHt9G1HJqhNFbXPUJKZMEVig

> code 和 access_token 参数理论上传参时相互斥，但如果不小心 code 和 access_token 同时传递，那么会优先采用 code 去获取认证。（微信企业号授权时，建议只传参 code，因为服务端获取 `UserId` 时，需要同时含有 access_token 和 code参数，只传 code 会减少前端工作量）

返回说明

正确时返回的JSON数据包如下：

```

{
    "code": 200,
    "msg": "操作成功",
    "time": "2020-02-25 14:30:55"
}

```

> 此时之所以能够复用此接口，主要是利用了 `用户已经登录` 的特性。三种情况：1、用户没有绑定手机号调用此接口时为社交账号注册，将返回 `social_user_key` 字段，去调用 「发送短信验证码-api/verificationCodes」、「2.2、openid 第一次登录（需先获取短信验证码）-api/socials/:social_type/login」实现注册登录逻辑。2、用户已经绑定了手机号，但是没有登录时，采用授权方式访问此接口，则会直接登录成功。3、用户已经登录成功（必然已经绑定了手机号），但是没有绑定社交账号时，调用此接口则为绑定社交账号。
