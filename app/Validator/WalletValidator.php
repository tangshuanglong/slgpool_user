<?php

namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\Enum;
use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Length;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class UserValidator
 * @package App\Validator
 * @Validator(name="WalletValidator")
 */
class WalletValidator{


    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $app_id;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $app_secret;

    /**
     * @IsString()
     * @var
     */
    protected $method;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $uid;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $coin_id;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $amount;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $trade_type;

    /**
     * @IsInt()
     * @NotEmpty()
     * @var
     */
    protected $timestamp;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $sign;

}
