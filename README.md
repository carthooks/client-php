## install
```shell
composer require carthooks/carthooks
```

## usage
```php
use Carthooks\CartHooksClient;

$client = new CartHooksClient($api_key, $api_secret);
```

### Get items
```php

    $items = $client->getItems($app_id, $collection_id, [
        'fields' => ['name'],
        'filters' => [
            'age' => [
                '$eq' => '16',
            ],
        ],
        'sort' => ['age'],
        'pagination' => [
            'page' => 1,
            'pageSize' => 10,
        ],
    ]);
```

### Get item
```php
    $item = $client->getItem($app_id, $collection_id, $item_id);
```

### Create item
```php
    $item = $client->createItem($app_id, $collection_id, [
        'name' => 'test',
        'age' => 16,
    ]);
```

### Update item
```php
    $item = $client->updateItem($app_id, $collection_id, $item_id, [
        'name' => 'test',
        'age' => 16,
    ]);
```

### Upload assets
```php
    $token = $client->getUploadToken(); //auto expire after 30 minutes
    $tempAsset = $client->upload($token, $_FILES['myFile']['tmp_name']); //will auto delete after 30 minutes, if not bind to item

    $item = $client->updateItem($app_id, $collection_id, $item_id, [
        'name' => 'test',
        'age' => 16,
        'photo' => $tempAsset, //bind assets
    ]);
```

### Upload use frontend
```php
Route::get('/upload-token', function () {
    $client = new CartHooksClient($api_key, $api_secret);
    $token = $client->getUploadToken();
    return $token;
});
```

```
npm i carthooks-upload
```

```html
<template>
    <el-upload
        action=""
        :http-request="uploadFunction"
        :before-upload="beforeUpload"
        name="file">
        <el-button size="small">upload</el-button>
    </el-upload>
</template>
<script>
import axios from 'axios';
import upload from 'carthooks-upload'
export default {
    data() {
        return {
            limit: 1,
            token: null,
        }
    },
    methods: {
        uploadFunction(item) {
            upload(this.token, item.file, {
                onProgress: (p) => {
                    item.onProgress({ percent: Math.floor(p * 100) });
                }
            }).then((res) => {
                this.$message.success('upload success')
            }).catch((e) => {
                this.$message.error('upload error')
            })
        },
        async beforeUpload(file) {
            await axios.get('/upload-token').then(res => {
                this.token = res.data;
            })
        },
    }
}
</script>
