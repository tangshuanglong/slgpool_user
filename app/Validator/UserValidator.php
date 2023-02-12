<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Date;
use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Length;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class UserValidator
 * @package App\Validator
 * @Validator(name="UserValidator")
 */
class UserValidator
{

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $coin_type;

    /**
     * @IsString()
     * @var
     */
    protected $chain_name;

    /**
     * @IsString()
     * @Length(min=5, max=1024, message="地址错误")
     * @NotEmpty()
     * @var
     */
    protected $address;

    /**
     * @IsString()
     * @var
     */
    protected $account;

    /**
     * @IsString()
     * @NotEmpty(message="提币数量不能为空")
     * @var
     */
    protected $amount;

    /**
     * @IsInt()
     * @Enum(values={0, 1})
     * @var
     */
    protected $is_save_address;

    /**
     * @IsString()
     * @var
     */
    protected $memo;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $coin_name;

    /**
     * @IsString()
     * @var
     */
    protected $remark;

    /**
     * @IsInt()
     * @var
     */
    protected $id;

    /**
     * 手机验证码
     * @IsString()
     * @var
     */
    protected $mv_code;

    /**
     * 邮箱验证码
     * @IsString()
     * @var
     */
    protected $ev_code;

    /**
     * 谷歌验证码
     * @IsString()
     * @var
     */
    protected $gv_code;

    /**
     * 记录类型
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"transfer", "other","all"})
     * @var
     */
    protected $record_type;


    /**
     * 资产来源
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"dw", "mining"})
     * @var
     */
    protected $from;

    /**
     * 划转到哪
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"dw", "mining"})
     * @var
     */
    protected $to;

    /**
     * 资产类型
     * @IsString()
     * @NotEmpty()
     * @Enum(values={"dw", "mining"})
     * @var
     */
    protected $assets_type;

    /**
     * @IsInt()
     * @NotEmpty()
     * @var
     */
    protected $coin_id;

    /**
     * @IsInt()
     * @NotEmpty()
     * @var
     */
    protected $trade_type_id;

    /**
     * @IsString()
     * @NotEmpty()
     * @Date()
     * @var
     */
    protected $start_date;

    /**
     * @IsString()
     * @NotEmpty()
     * @Date()
     * @var
     */
    protected $end_date;

    /**
     * @isInt()
     * @NotEmpty()
     * @var
     */
    protected $product_type;

    /**
     * @isInt()
     * @NotEmpty()
     * @var
     */
    protected $order_id;
}
