<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

function get_left_right_coin($coinType,$priceType)
{
    $right_table_arr = [
        "filusdt" => '',
        "filcny" => '',
        "usdtcny" => '',
    ];

    $right_table = [
        "usdt" => [
            'cny',
            'fil',
        ],
        "cny" => [
            'fil',
            'usdt',
        ],
        'fil' => [
            'usdt',
            'cny',
        ]
    ];

    $coinLeft = "";
    $coinRight = "";

    if (!isset($right_table[$coinType])) {
        throw new \Exception('不提供此种类型的闪兑');

    } else {
        foreach ($right_table[$coinType] as $v) {
            if ($v == $priceType) {
                $table_name = $coinType . $priceType;
                if (!array_key_exists($table_name, $right_table_arr)) {
                    $table_name = $priceType . $coinType;
                    if (!array_key_exists($table_name, $right_table_arr)) {
                        throw new \Exception('不提供此种类型的闪兑');
                    } else {

                        $coinLeft = $priceType;
                        $coinRight = $coinType;
                        break;
                    }
                } else {

                    $coinLeft = $coinType;
                    $coinRight = $priceType;
                    break;
                }
            }
        }
    }


    return [$coinLeft,$coinRight];
}
