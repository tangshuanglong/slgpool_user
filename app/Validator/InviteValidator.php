<?php

namespace App\Validator;


use Swoft\Validator\Annotation\Mapping\Date;
use Swoft\Validator\Annotation\Mapping\IsString;
use Swoft\Validator\Annotation\Mapping\NotEmpty;
use Swoft\Validator\Annotation\Mapping\Validator;

/**
 * Class InviteValidator
 * @package App\Validator
 * @Validator(name="InviteValidator")
 */
class InviteValidator{

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


}
