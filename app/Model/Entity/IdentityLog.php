<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 
 * Class IdentityLog
 *
 * @since 2.0
 *
 * @Entity(table="identity_log")
 */
class IdentityLog extends Model
{
    /**
     * 国家id
     *
     * @Column(name="country_id", prop="countryId")
     *
     * @var int
     */
    private $countryId;

    /**
     * 国家中文名
     *
     * @Column(name="country_name_cn", prop="countryNameCn")
     *
     * @var string
     */
    private $countryNameCn;

    /**
     * 国家英文名
     *
     * @Column(name="country_name_en", prop="countryNameEn")
     *
     * @var string
     */
    private $countryNameEn;

    /**
     * 创建时间
     *
     * @Column(name="created_at", prop="createdAt")
     *
     * @var string
     */
    private $createdAt;

    /**
     * 身份证认证表
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * 证件正面
     *
     * @Column(name="identity_front", prop="identityFront")
     *
     * @var string
     */
    private $identityFront;

    /**
     * 证件号码
     *
     * @Column(name="identity_number", prop="identityNumber")
     *
     * @var string
     */
    private $identityNumber;

    /**
     * 证件反面
     *
     * @Column(name="identity_reverse", prop="identityReverse")
     *
     * @var string
     */
    private $identityReverse;

    /**
     * 用户的真实姓名
     *
     * @Column(name="real_name", prop="realName")
     *
     * @var string
     */
    private $realName;

    /**
     * 状态，0-待审核，1-审核通过，20审核不通过
     *
     * @Column(name="status_flag", prop="statusFlag")
     *
     * @var int
     */
    private $statusFlag;

    /**
     * 用户ID
     *
     * @Column()
     *
     * @var int
     */
    private $uid;


    /**
     * @param int $countryId
     *
     * @return self
     */
    public function setCountryId(int $countryId): self
    {
        $this->countryId = $countryId;

        return $this;
    }

    /**
     * @param string $countryNameCn
     *
     * @return self
     */
    public function setCountryNameCn(string $countryNameCn): self
    {
        $this->countryNameCn = $countryNameCn;

        return $this;
    }

    /**
     * @param string $countryNameEn
     *
     * @return self
     */
    public function setCountryNameEn(string $countryNameEn): self
    {
        $this->countryNameEn = $countryNameEn;

        return $this;
    }

    /**
     * @param string $createdAt
     *
     * @return self
     */
    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $identityFront
     *
     * @return self
     */
    public function setIdentityFront(string $identityFront): self
    {
        $this->identityFront = $identityFront;

        return $this;
    }

    /**
     * @param string $identityNumber
     *
     * @return self
     */
    public function setIdentityNumber(string $identityNumber): self
    {
        $this->identityNumber = $identityNumber;

        return $this;
    }

    /**
     * @param string $identityReverse
     *
     * @return self
     */
    public function setIdentityReverse(string $identityReverse): self
    {
        $this->identityReverse = $identityReverse;

        return $this;
    }

    /**
     * @param string $realName
     *
     * @return self
     */
    public function setRealName(string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * @param int $statusFlag
     *
     * @return self
     */
    public function setStatusFlag(int $statusFlag): self
    {
        $this->statusFlag = $statusFlag;

        return $this;
    }

    /**
     * @param int $uid
     *
     * @return self
     */
    public function setUid(int $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountryId(): ?int
    {
        return $this->countryId;
    }

    /**
     * @return string
     */
    public function getCountryNameCn(): ?string
    {
        return $this->countryNameCn;
    }

    /**
     * @return string
     */
    public function getCountryNameEn(): ?string
    {
        return $this->countryNameEn;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdentityFront(): ?string
    {
        return $this->identityFront;
    }

    /**
     * @return string
     */
    public function getIdentityNumber(): ?string
    {
        return $this->identityNumber;
    }

    /**
     * @return string
     */
    public function getIdentityReverse(): ?string
    {
        return $this->identityReverse;
    }

    /**
     * @return string
     */
    public function getRealName(): ?string
    {
        return $this->realName;
    }

    /**
     * @return int
     */
    public function getStatusFlag(): ?int
    {
        return $this->statusFlag;
    }

    /**
     * @return int
     */
    public function getUid(): ?int
    {
        return $this->uid;
    }

}
