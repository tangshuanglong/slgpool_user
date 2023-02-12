<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 返佣记录表
 * Class RewardLog
 *
 * @since 2.0
 *
 * @Entity(table="reward_log")
 */
class RewardLog extends Model
{
    /**
     * 数量
     *
     * @Column()
     *
     * @var string
     */
    private $amount;

    /**
     * 购买数量
     *
     * @Column(name="buy_amount", prop="buyAmount")
     *
     * @var string
     */
    private $buyAmount;

    /**
     * 币种类型
     *
     * @Column(name="coin_type", prop="coinType")
     *
     * @var string
     */
    private $coinType;

    /**
     * 创建日期
     *
     * @Column(name="created_at", prop="createdAt")
     *
     * @var string|null
     */
    private $createdAt;

    /**
     * 奖励来自哪个账号
     *
     * @Column(name="from_account", prop="fromAccount")
     *
     * @var string
     */
    private $fromAccount;

    /**
     * 奖励来自哪个用户id
     *
     * @Column(name="from_uid", prop="fromUid")
     *
     * @var int
     */
    private $fromUid;

    /**
     * 自增id
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * 状态，0-未赠送，1-已赠送
     *
     * @Column()
     *
     * @var int
     */
    private $status;

    /**
     * 奖励用户id
     *
     * @Column(name="to_uid", prop="toUid")
     *
     * @var int
     */
    private $toUid;


    /**
     * @param string $amount
     *
     * @return self
     */
    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param string $buyAmount
     *
     * @return self
     */
    public function setBuyAmount(string $buyAmount): self
    {
        $this->buyAmount = $buyAmount;

        return $this;
    }

    /**
     * @param string $coinType
     *
     * @return self
     */
    public function setCoinType(string $coinType): self
    {
        $this->coinType = $coinType;

        return $this;
    }

    /**
     * @param string|null $createdAt
     *
     * @return self
     */
    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param string $fromAccount
     *
     * @return self
     */
    public function setFromAccount(string $fromAccount): self
    {
        $this->fromAccount = $fromAccount;

        return $this;
    }

    /**
     * @param int $fromUid
     *
     * @return self
     */
    public function setFromUid(int $fromUid): self
    {
        $this->fromUid = $fromUid;

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
     * @param int $status
     *
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param int $toUid
     *
     * @return self
     */
    public function setToUid(int $toUid): self
    {
        $this->toUid = $toUid;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): ?string
    
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getBuyAmount(): ?string
    
    {
        return $this->buyAmount;
    }

    /**
     * @return string
     */
    public function getCoinType(): ?string
    
    {
        return $this->coinType;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getFromAccount(): ?string
    
    {
        return $this->fromAccount;
    }

    /**
     * @return int
     */
    public function getFromUid(): ?int
    
    {
        return $this->fromUid;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatus(): ?int
    
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getToUid(): ?int
    
    {
        return $this->toUid;
    }


}
