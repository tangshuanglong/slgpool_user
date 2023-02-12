<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 货币兑换买入表
 * Class CoinExchange
 *
 * @since 2.0
 *
 * @Entity(table="coin_exchange")
 */
class CoinExchange extends Model
{
    /**
     * 最终金额
     *
     * @Column()
     *
     * @var string|null
     */
    private $amount;

    /**
     * 币种类型
     *
     * @Column(name="coin_type", prop="coinType")
     *
     * @var string
     */
    private $coinType;

    /**
     * 创建时间
     *
     * @Column(name="created_at", prop="createdAt")
     *
     * @var string|null
     */
    private $createdAt;

    /**
     * 交易类型 买入-buy   卖出-sell
     *
     * @Column(name="exchange_method", prop="exchangeMethod")
     *
     * @var string
     */
    private $exchangeMethod;

    /**
     * 自增id
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * 数量
     *
     * @Column()
     *
     * @var string
     */
    private $number;

    /**
     * 原交易总金额
     *
     * @Column(name="origin_amount", prop="originAmount")
     *
     * @var string
     */
    private $originAmount;

    /**
     * 兑换目标币种当前价格
     *
     * @Column()
     *
     * @var string
     */
    private $price;

    /**
     * 兑换目标币种类型
     *
     * @Column(name="price_type", prop="priceType")
     *
     * @var string
     */
    private $priceType;

    /**
     * 手续费
     *
     * @Column(name="service_charge", prop="serviceCharge")
     *
     * @var string|null
     */
    private $serviceCharge;

    /**
     * 用户id
     *
     * @Column()
     *
     * @var int
     */
    private $uid;


    /**
     * @param string|null $amount
     *
     * @return self
     */
    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

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
     * @param string $exchangeMethod
     *
     * @return self
     */
    public function setExchangeMethod(string $exchangeMethod): self
    {
        $this->exchangeMethod = $exchangeMethod;

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
     * @param string $number
     *
     * @return self
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @param string $originAmount
     *
     * @return self
     */
    public function setOriginAmount(string $originAmount): self
    {
        $this->originAmount = $originAmount;

        return $this;
    }

    /**
     * @param string $price
     *
     * @return self
     */
    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @param string $priceType
     *
     * @return self
     */
    public function setPriceType(string $priceType): self
    {
        $this->priceType = $priceType;

        return $this;
    }

    /**
     * @param string|null $serviceCharge
     *
     * @return self
     */
    public function setServiceCharge(?string $serviceCharge): self
    {
        $this->serviceCharge = $serviceCharge;

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
     * @return string|null
     */
    public function getAmount(): ?string
    
    {
        return $this->amount;
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
    public function getExchangeMethod(): ?string
    
    {
        return $this->exchangeMethod;
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
    public function getNumber(): ?string
    
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getOriginAmount(): ?string
    
    {
        return $this->originAmount;
    }

    /**
     * @return string
     */
    public function getPrice(): ?string
    
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getPriceType(): ?string
    
    {
        return $this->priceType;
    }

    /**
     * @return string|null
     */
    public function getServiceCharge(): ?string
    
    {
        return $this->serviceCharge;
    }

    /**
     * @return int
     */
    public function getUid(): ?int
    
    {
        return $this->uid;
    }


}
