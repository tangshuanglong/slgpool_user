<?php return array (
    //consul注册配置
    'consul' => [
        'address' => '127.0.0.1',
        'port' => 18310,
        'name' => 'us',
        'id' => 'us',
    ],
    'apollo' => require_once __DIR__."/../../../apollo.php",
    'wallet_app_id' => 'wallet',
    'wallet_app_secret' => 'Hzx8sQNxoNYRQbVwrM7l55F6y2GBXMRo',
    'coin_last_price_key' => 'coin:last:price:info',
);
