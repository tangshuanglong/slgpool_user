<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 
 * Class CoinWithdrawLog
 *
 * @since 2.0
 *
 * @Entity(table="coin_withdraw_log")
 */
class CoinWithdrawLog extends Model
{
    /**
     * 公链ID
     *
     * @Column(name="chain_id", prop="chainId")
     *
     * @var int
     */
    private $chainId;

    /**
     * 实际到账数量
     *
     * @Column(name="coin_actual_amount", prop="coinActualAmount")
     *
     * @var string
     */
    private $coinActualAmount;

    /**
     * 提币地址
     *
     * @Column(name="coin_address", prop="coinAddress")
     *
     * @var string
     */
    private $coinAddress;

    /**
     * 提币数量
     *
     * @Column(name="coin_amount", prop="coinAmount")
     *
     * @var string
     */
    private $coinAmount;

    /**
     * 币种id
     *
     * @Column(name="coin_id", prop="coinId")
     *
     * @var int
     */
    private $coinId;

    /**
     * 币种名称
     *
     * @Column(name="coin_name", prop="coinName")
     *
     * @var string
     */
    private $coinName;

    /**
     * 创建时间
     *
     * @Column(name="created_at", prop="createdAt")
     *
     * @var string
     */
    private $createdAt;

    /**
     * 提币申请记录表
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * Memo，供基于石墨烯公链使用。如eos，gxs
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
     * 申请状态，10-已提交申请，审核中，20-审核通过，30-审核不通过，40-提币失败，50-提币成功
     *
     * @Column()
     *
     * @var int
     */
    private $status;

    /**
     * token id
     *
     * @Column(name="token_id", prop="tokenId")
     *
     * @var int
     */
    private $tokenId;

    /**
     * 手续费：币数量
     *
     * @Column(name="trade_handling_fee", prop="tradeHandlingFee")
     *
     * @var string
     */
    private $tradeHandlingFee;

    /**
     * 转账的流水号，hash值
     *
     * @Column(name="tx_hash", prop="txHash")
     *
     * @var string|null
     */
    private $txHash;

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
     * 提币流水号，WD+165688[常数]+id
     *
     * @Column(name="withdraw_number", prop="withdrawNumber")
     *
     * @var string|null
     */
    private $withdrawNumber;


    /**
     * @param int $chainId
     *
     * @return self
     */
    public function setChainId(int $chainId): self
    {
        $this->chainId = $chainId;

        return $this;
    }

    /**
     * @param string $coinActualAmount
     *
     * @return self
     */
    public function setCoinActualAmount(string $coinActualAmount): self
    {
        $this->coinActualAmount = $coinActualAmount;

        return $this;
    }

    /**
     * @param string $coinAddress
     *
     * @return self
     */
    public function setCoinAddress(string $coinAddress): self
    {
        $this->coinAddress = $coinAddress;

        return $this;
    }

    /**
     * @param string $coinAmount
     *
     * @return self
     */
    public function setCoinAmount(string $coinAmount): self
    {
        $this->coinAmount = $coinAmount;

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
     * @param string $coinName
     *
     * @return self
     */
    public function setCoinName(string $coinName): self
    {
        $this->coinName = $coinName;

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
     * @param string $memo
     *
     * @return self
     */
    public function setMemo(string $memo): self
    {
        $this->memo = $memo;

        return $this;
    }

    /**
     * @param string $remark
     *
     * @return self
     */
    public function setRemark(string $remark): self
    {
        $this->remark = $remark;

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
     * @param int $tokenId
     *
     * @return self
     */
    public function setTokenId(int $tokenId): self
    {
        $this->tokenId = $tokenId;

        return $this;
    }

    /**
     * @param string $tradeHandlingFee
     *
     * @return self
     */
    public function setTradeHandlingFee(string $tradeHandlingFee): self
    {
        $this->tradeHandlingFee = $tradeHandlingFee;

        return $this;
    }

    /**
     * @param string|null $txHash
     *
     * @return self
     */
    public function setTxHash(?string $txHash): self
    {
        $this->txHash = $txHash;

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
     * @param string|null $withdrawNumber
     *
     * @return self
     */
    public function setWithdrawNumber(?string $withdrawNumber): self
    {
        $this->withdrawNumber = $withdrawNumber;

        return $this;
    }

    /**
     * @return int
     */
    public function getChainId(): ?int
    {
        return $this->chainId;
    }

    /**
     * @return string
     */
    public function getCoinActualAmount(): ?string
    {
        return $this->coinActualAmount;
    }

    /**
     * @return string
     */
    public function getCoinAddress(): ?string
    {
        return $this->coinAddress;
    }

    /**
     * @return string
     */
    public function getCoinAmount(): ?string
    {
        return $this->coinAmount;
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
    public function getCoinName(): ?string
    {
        return $this->coinName;
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
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getTokenId(): ?int
    {
        return $this->tokenId;
    }

    /**
     * @return string
     */
    public function getTradeHandlingFee(): ?string
    {
        return $this->tradeHandlingFee;
    }

    /**
     * @return string|null
     */
    public function getTxHash(): ?string
    {
        return $this->txHash;
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

    /**
     * @return string|null
     */
    public function getWithdrawNumber(): ?string
    {
        return $this->withdrawNumber;
    }

}
