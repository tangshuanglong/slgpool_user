<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Length;
use Swoft\Validator\Annotation\Mapping\Max;
use Swoft\Validator\Annotation\Mapping\Min;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class PayValidator
 * @package App\Validator
 * @Validator(name="PayValidator")
 */
class PayValidator{


    /**
     * @IsInt()
     * @NotEmpty()
     * @var
     */
    protected $pay_method_id;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $account_name;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $out_trade_order_no;
    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $account_number;


    /**
     * @IsInt()
     * @var
     */
    protected $id;

    /**
     * @IsInt(message="充值金额必须为正整数")
     * @Min(value=1,message="最少充值金额为1")
     * @Max(value=99999999,message="最多充值金额为99999999")
     * @var
     */
    protected $price;
}
