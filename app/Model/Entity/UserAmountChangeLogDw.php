<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 
 * Class UserAmountChangeLogDw
 *
 * @since 2.0
 *
 * @Entity(table="user_amount_change_log_dw")
 */
class UserAmountChangeLogDw extends Model
{
    /**
     * 操作金额
     *
     * @Column()
     *
     * @var string
     */
    private $amount;

    /**
     * 失败原因
     *
     * @Column(name="cause_fail", prop="causeFail")
     *
     * @var string
     */
    private $causeFail;

    /**
     * 币种ID
     *
     * @Column(name="coin_id", prop="coinId")
     *
     * @var int
     */
    private $coinId;

    /**
     * 创建时间
     *
     * @Column(name="created_at", prop="createdAt")
     *
     * @var string
     */
    private $createdAt;

    /**
     * 自增ID
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * 操作方法
     *
     * @Column()
     *
     * @var string
     */
    private $method;

    /**
     * 状态，0-未处理，1-已处理，2-处理失败
     *
     * @Column(name="status_flag", prop="statusFlag")
     *
     * @var int
     */
    private $statusFlag;

    /**
     * 交易类型ID
     *
     * @Column(name="trade_type_id", prop="tradeTypeId")
     *
     * @var int
     */
    private $tradeTypeId;

    /**
     * 用户ID
     *
     * @Column()
     *
     * @var int
     */
    private $uid;

    /**
     * 更新时间
     *
     * @Column(name="updated_at", prop="updatedAt")
     *
     * @var string
     */
    private $updatedAt;


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
     * @param string $causeFail
     *
     * @return self
     */
    public function setCauseFail(string $causeFail): self
    {
        $this->causeFail = $causeFail;

        return $this;
    }

    /**
     * @param int $coinId
     *
     * @return self
     */
    public function setCoinId(int $coinId): self
    {
        $this->coinId = $coinId;

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
     * @param string $method
     *
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

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
     * @param int $tradeTypeId
     *
     * @return self
     */
    public function setTradeTypeId(int $tradeTypeId): self
    {
        $this->tradeTypeId = $tradeTypeId;

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
     * @param string $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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
    public function getCauseFail(): ?string
    {
        return $this->causeFail;
    }

    /**
     * @return int
     */
    public function getCoinId(): ?int
    {
        return $this->coinId;
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
    public function getMethod(): ?string
    {
        return $this->method;
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
    public function getTradeTypeId(): ?int
    {
        return $this->tradeTypeId;
    }

    /**
     * @return int
     */
    public function getUid(): ?int
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

}
