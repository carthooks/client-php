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
