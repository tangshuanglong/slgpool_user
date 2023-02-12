<?php


namespace App\Validator;

use Swoft\Validator\Annotation\Mapping\IsInt;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\Length;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Url;
use Swoft\Validator\Annotation\Mapping\Validator;
use Swoft\Validator\Contract\ValidatorInterface;

/**
 * Class CoinExchangeValidator
 * @package App\Validator
 * @Validator(name="CoinExchangeValidator")
 */
class CoinExchangeValidator{

    /**
     * @IsInt()
     * @NotEmpty()
     * @var
     */
    protected $uid;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $coin_type;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $price_type;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $exchange_method;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $number;
}
