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
 * @Validator(name="AddressValidator")
 */
class AddressValidator{


    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $chain_name;

    /**
     * @IsString()
     * @Length(min=5, max=1024)
     * @NotEmpty()
     * @var
     */
    protected $address;

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
     * @NotEmpty()
     * @var
     */
    protected $remark;

    /**
     * @IsInt()
     * @var
     */
    protected $id;

}
