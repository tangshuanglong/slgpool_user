<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 用户邀请返佣等级
 * Class RebateLevel
 *
 * @since 2.0
 *
 * @Entity(table="rebate_level")
 */
class RebateLevel extends Model
{
    /**
     * 用户消费总金额，单位usdt
     *
     * @Column(name="consume_total_amount", prop="consumeTotalAmount")
     *
     * @var string
     */
    private $consumeTotalAmount;

    /**
     * 创建时间
     *
     * @Column(name="created_at", prop="createdAt")
     *
     * @var string
     */
    private $createdAt;

    /**
     * 自增id
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * 等级
     *
     * @Column()
     *
     * @var int
     */
    private $level;

    /**
     * 用户id
     *
     * @Column()
     *
     * @var int
     */
    private $uid;

    /**
     * 更新时间
     *
     * @Column(name="update_at", prop="updateAt")
     *
     * @var string
     */
    private $updateAt;


    /**
     * @param string $consumeTotalAmount
     *
     * @return self
     */
    public function setConsumeTotalAmount(string $consumeTotalAmount): self
    {
        $this->consumeTotalAmount = $consumeTotalAmount;

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
     * @param int $level
     *
     * @return self
     */
    public function setLevel(int $level): self
    {
        $this->level = $level;

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
     * @param string $updateAt
     *
     * @return self
     */
    public function setUpdateAt(string $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getConsumeTotalAmount(): ?string

    {
        return $this->consumeTotalAmount;
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
     * @return int
     */
    public function getLevel(): ?int

    {
        return $this->level;
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
    public function getUpdateAt(): ?string
    {
        return $this->updateAt;
    }


}
