<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 
 * Class AddressManager
 *
 * @since 2.0
 *
 * @Entity(table="address_manager")
 */
class AddressManager extends Model
{
    /**
     * 地址管理表
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * 用户id
     *
     * @Column()
     *
     * @var int
     */
    private $uid;

    /**
     * 币种名称
     *
     * @Column(name="coin_name", prop="coinName")
     *
     * @var string
     */
    private $coinName;

    /**
     * 公链名称，默认币种名称
     *
     * @Column(name="chain_name", prop="chainName")
     *
     * @var string
     */
    private $chainName;

    /**
     * 区块链地址或账号
     *
     * @Column()
     *
     * @var string
     */
    private $address;

    /**
     * 标签，石墨烯链才需要，如eos
     *
     * @Column()
     *
     * @var string
     */
    private $memo;

    /**
     * 备注
     *
     * @Column()
     *
     * @var string
     */
    private $remark;

    /**
     * 创建时间
     *
     * @Column(name="create_time", prop="createTime")
     *
     * @var int
     */
    private $createTime;


    /**
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param int $uid
     *
     * @return void
     */
    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @param string $coinName
     *
     * @return void
     */
    public function setCoinName(string $coinName): void
    {
        $this->coinName = $coinName;
    }

    /**
     * @param string $chainName
     *
     * @return void
     */
    public function setChainName(string $chainName): void
    {
        $this->chainName = $chainName;
    }

    /**
     * @param string $address
     *
     * @return void
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @param string $memo
     *
     * @return void
     */
    public function setMemo(string $memo): void
    {
        $this->memo = $memo;
    }

    /**
     * @param string $remark
     *
     * @return void
     */
    public function setRemark(string $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @param int $createTime
     *
     * @return void
     */
    public function setCreateTime(int $createTime): void
    {
        $this->createTime = $createTime;
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
    public function getUid(): ?int
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getCoinName(): ?string
    {
        return $this->coinName;
    }

    /**
     * @return string
     */
    public function getChainName(): ?string
    {
        return $this->chainName;
    }

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getMemo(): ?string
    {
        return $this->memo;
    }

    /**
     * @return string
     */
    public function getRemark(): ?string
    {
        return $this->remark;
    }

    /**
     * @return int
     */
    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

}
