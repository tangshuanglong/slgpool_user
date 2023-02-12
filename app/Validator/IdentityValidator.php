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
 * Class IdentityValidator
 * @package App\Validator
 * @Validator(name="IdentityValidator")
 */
class IdentityValidator
{

    /**
     * @IsInt(message="请填写国家地区")
     * @NotEmpty()
     * @var
     */
    protected $country_id;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $real_name;

    /**
     * @IsInt()
     * @NotEmpty()
     * @var
     */
    protected $identity_type_id;

    /**
     * @IsString()
     * @NotEmpty()
     * @var
     */
    protected $identity_number;

    /**
     * @IsString()
     * @Url()
     * @var
     */
    protected $identity_front;

    /**
     * @IsString()
     * @Url()
     * @var
     */
    protected $identity_reverse;

}
