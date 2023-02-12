<?php
return [
    //提现
    'withdraw_key'             => 'withdraw_key_',
    //提现限制
    'withdraw_limit'           => 'withdraw_limit_key_',
    // 币币账户钱包锁
    'user_dw_wallet_lock'      => 'user_dw_wallet_lock_key_',
    // 币币账户钱包队列
    'user_dw_wallet_queue'     => 'user_dw_wallet_queue_key',
    // 借贷账户钱包锁
    'user_loan_wallet_lock'    => 'user_loan_wallet_lock_key_',
    //借贷账户钱包队列
    'user_loan_wallet_queue'   => 'user_loan_wallet_queue_key',
    // 挖矿账户钱包锁
    'user_mining_wallet_lock'  => 'user_mining_wallet_lock_key_',
    // 挖矿账户钱包队列
    'user_mining_wallet_queue' => 'user_mining_wallet_queue_key',
    //账户余额异常用户 任何一个账户异常都要处理
    'user_wallet_abnormal'     => 'user:wallet:abnormal',
];
