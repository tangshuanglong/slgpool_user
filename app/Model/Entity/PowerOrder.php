<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 订单表
 * Class PowerOrder
 *
 * @since 2.0
 *
 * @Entity(table="power_order")
 */
class PowerOrder extends Model
{
    /**
     * 活动赠送算力
     *
     * @Column(name="activity_give_hash", prop="activityGiveHash")
     *
     * @var string|null
     */
    private $activityGiveHash;

    /**
     * 上架时间 单位天
     *
     * @Column(name="added_time", prop="addedTime")
     *
     * @var int
     */
    private $addedTime;

    /**
     * 购买数量
     *
     * @Column(name="buy_quantity", prop="buyQuantity")
     *
     * @var int
     */
    private $buyQuantity;

    /**
     * 币种类型
     *
     * @Column(name="coin_type", prop="coinType")
     *
     * @var string
     */
    private $coinType;

    /**
     * 下单时间
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
     * 昨日收益，每天10点发放的昨日收益
     *
     * @Column()
     *
     * @var string
     */
    private $income;

    /**
     * 昨日收益 奖励的平台币，每天10点发放的昨日收益
     *
     * @Column(name="income_reward", prop="incomeReward")
     *
     * @var string
     */
    private $incomeReward;

    /**
     * 获取积分 单位bsf
     *
     * @Column()
     *
     * @var int
     */
    private $integral;

    /**
     * 是否体验，0-否， 1-是
     *
     * @Column(name="is_experience", prop="isExperience")
     *
     * @var int|null
     */
    private $isExperience;

    /**
     * 是否下单的时候立即抵押，0-否，1-是。如果是矿机，为0的时候动态抵押
     *
     * @Column(name="is_pledge", prop="isPledge")
     *
     * @var int
     */
    private $isPledge;

    /**
     * 是否转卖，0-否，1-是
     *
     * @Column(name="is_resell", prop="isResell")
     *
     * @var int
     */
    private $isResell;

    /**
     * 管理费用
     *
     * @Column(name="manage_fee", prop="manageFee")
     *
     * @var string
     */
    private $manageFee;

    /**
     * 订单号
     *
     * @Column(name="order_number", prop="orderNumber")
     *
     * @var string
     */
    private $orderNumber;

    /**
     * -1等待抵押 0-待上架，1-服务中，2-未付款，3-已完成，4-转让中，5-已转让, 6-取消，7-转让中待支付
     *
     * @Column(name="order_status", prop="orderStatus")
     *
     * @var int
     */
    private $orderStatus;

    /**
     * 订单类型，1-原始订单，2-转卖订单
     *
     * @Column(name="order_type", prop="orderType")
     *
     * @var int
     */
    private $orderType;

    /**
     * 期限，单位 天
     *
     * @Column()
     *
     * @var int
     */
    private $period;

    /**
     * 抵押总金额
     *
     * @Column(name="pledge_price", prop="pledgePrice")
     *
     * @var string
     */
    private $pledgePrice;

    /**
     * 电费付费类型 1-年付，2-月付，3-抵扣
     *
     * @Column(name="power_paid_type", prop="powerPaidType")
     *
     * @var int
     */
    private $powerPaidType;

    /**
     * 产品单价
     *
     * @Column()
     *
     * @var string
     */
    private $price;

    /**
     * 产品ID
     *
     * @Column(name="product_id", prop="productId")
     *
     * @var int
     */
    private $productId;

    /**
     * 产品名称
     *
     * @Column(name="product_name", prop="productName")
     *
     * @var string
     */
    private $productName;

    /**
     * 产品类型，1-矿机，2-算力
     *
     * @Column(name="product_type", prop="productType")
     *
     * @var int
     */
    private $productType;

    /**
     * 实际能够封满的有效算力，矿机需要
     *
     * @Column(name="real_hash", prop="realHash")
     *
     * @var string
     */
    private $realHash;

    /**
     * 奖励平台币比例
     *
     * @Column(name="reward_ratio", prop="rewardRatio")
     *
     * @var string
     */
    private $rewardRatio;

    /**
     * 上架日期
     *
     * @Column(name="shelf_date", prop="shelfDate")
     *
     * @var string|null
     */
    private $shelfDate;

    /**
     * 总算力
     *
     * @Column(name="total_hash", prop="totalHash")
     *
     * @var string
     */
    private $totalHash;

    /**
     * 累计总收益
     *
     * @Column(name="total_income", prop="totalIncome")
     *
     * @var string
     */
    private $totalIncome;

    /**
     * 累计总收益 奖励的平台币
     *
     * @Column(name="total_income_reward", prop="totalIncomeReward")
     *
     * @var string
     */
    private $totalIncomeReward;

    /**
     * 订单总价
     *
     * @Column(name="total_price", prop="totalPrice")
     *
     * @var string
     */
    private $totalPrice;

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
     * 有效算力
     *
     * @Column(name="valid_hash", prop="validHash")
     *
     * @var string
     */
    private $validHash;

    /**
     * 矿工节点号
     *
     * @Column(name="work_number", prop="workNumber")
     *
     * @var string
     */
    private $workNumber;


    /**
     * @param string|null $activityGiveHash
     *
     * @return self
     */
    public function setActivityGiveHash(?string $activityGiveHash): self
    {
        $this->activityGiveHash = $activityGiveHash;

        return $this;
    }

    /**
     * @param int $addedTime
     *
     * @return self
     */
    public function setAddedTime(int $addedTime): self
    {
        $this->addedTime = $addedTime;

        return $this;
    }

    /**
     * @param int $buyQuantity
     *
     * @return self
     */
    public function setBuyQuantity(int $buyQuantity): self
    {
        $this->buyQuantity = $buyQuantity;

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
     * @param string $income
     *
     * @return self
     */
    public function setIncome(string $income): self
    {
        $this->income = $income;

        return $this;
    }

    /**
     * @param string $incomeReward
     *
     * @return self
     */
    public function setIncomeReward(string $incomeReward): self
    {
        $this->incomeReward = $incomeReward;

        return $this;
    }

    /**
     * @param int $integral
     *
     * @return self
     */
    public function setIntegral(int $integral): self
    {
        $this->integral = $integral;

        return $this;
    }

    /**
     * @param int|null $isExperience
     *
     * @return self
     */
    public function setIsExperience(?int $isExperience): self
    {
        $this->isExperience = $isExperience;

        return $this;
    }

    /**
     * @param int $isPledge
     *
     * @return self
     */
    public function setIsPledge(int $isPledge): self
    {
        $this->isPledge = $isPledge;

        return $this;
    }

    /**
     * @param int $isResell
     *
     * @return self
     */
    public function setIsResell(int $isResell): self
    {
        $this->isResell = $isResell;

        return $this;
    }

    /**
     * @param string $manageFee
     *
     * @return self
     */
    public function setManageFee(string $manageFee): self
    {
        $this->manageFee = $manageFee;

        return $this;
    }

    /**
     * @param string $orderNumber
     *
     * @return self
     */
    public function setOrderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * @param int $orderStatus
     *
     * @return self
     */
    public function setOrderStatus(int $orderStatus): self
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * @param int $orderType
     *
     * @return self
     */
    public function setOrderType(int $orderType): self
    {
        $this->orderType = $orderType;

        return $this;
    }

    /**
     * @param int $period
     *
     * @return self
     */
    public function setPeriod(int $period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @param string $pledgePrice
     *
     * @return self
     */
    public function setPledgePrice(string $pledgePrice): self
    {
        $this->pledgePrice = $pledgePrice;

        return $this;
    }

    /**
     * @param int $powerPaidType
     *
     * @return self
     */
    public function setPowerPaidType(int $powerPaidType): self
    {
        $this->powerPaidType = $powerPaidType;

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
     * @param int $productId
     *
     * @return self
     */
    public function setProductId(int $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @param string $productName
     *
     * @return self
     */
    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * @param int $productType
     *
     * @return self
     */
    public function setProductType(int $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    /**
     * @param string $realHash
     *
     * @return self
     */
    public function setRealHash(string $realHash): self
    {
        $this->realHash = $realHash;

        return $this;
    }

    /**
     * @param string $rewardRatio
     *
     * @return self
     */
    public function setRewardRatio(string $rewardRatio): self
    {
        $this->rewardRatio = $rewardRatio;

        return $this;
    }

    /**
     * @param string|null $shelfDate
     *
     * @return self
     */
    public function setShelfDate(?string $shelfDate): self
    {
        $this->shelfDate = $shelfDate;

        return $this;
    }

    /**
     * @param string $totalHash
     *
     * @return self
     */
    public function setTotalHash(string $totalHash): self
    {
        $this->totalHash = $totalHash;

        return $this;
    }

    /**
     * @param string $totalIncome
     *
     * @return self
     */
    public function setTotalIncome(string $totalIncome): self
    {
        $this->totalIncome = $totalIncome;

        return $this;
    }

    /**
     * @param string $totalIncomeReward
     *
     * @return self
     */
    public function setTotalIncomeReward(string $totalIncomeReward): self
    {
        $this->totalIncomeReward = $totalIncomeReward;

        return $this;
    }

    /**
     * @param string $totalPrice
     *
     * @return self
     */
    public function setTotalPrice(string $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

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
     * @param string $validHash
     *
     * @return self
     */
    public function setValidHash(string $validHash): self
    {
        $this->validHash = $validHash;

        return $this;
    }

    /**
     * @param string $workNumber
     *
     * @return self
     */
    public function setWorkNumber(string $workNumber): self
    {
        $this->workNumber = $workNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getActivityGiveHash(): ?string
    
    {
        return $this->activityGiveHash;
    }

    /**
     * @return int
     */
    public function getAddedTime(): ?int
    
    {
        return $this->addedTime;
    }

    /**
     * @return int
     */
    public function getBuyQuantity(): ?int
    
    {
        return $this->buyQuantity;
    }

    /**
     * @return string
     */
    public function getCoinType(): ?string
    
    {
        return $this->coinType;
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
    public function getIncome(): ?string
    
    {
        return $this->income;
    }

    /**
     * @return string
     */
    public function getIncomeReward(): ?string
    
    {
        return $this->incomeReward;
    }

    /**
     * @return int
     */
    public function getIntegral(): ?int
    
    {
        return $this->integral;
    }

    /**
     * @return int|null
     */
    public function getIsExperience(): ?int
    
    {
        return $this->isExperience;
    }

    /**
     * @return int
     */
    public function getIsPledge(): ?int
    
    {
        return $this->isPledge;
    }

    /**
     * @return int
     */
    public function getIsResell(): ?int
    
    {
        return $this->isResell;
    }

    /**
     * @return string
     */
    public function getManageFee(): ?string
    
    {
        return $this->manageFee;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): ?string
    
    {
        return $this->orderNumber;
    }

    /**
     * @return int
     */
    public function getOrderStatus(): ?int
    
    {
        return $this->orderStatus;
    }

    /**
     * @return int
     */
    public function getOrderType(): ?int
    
    {
        return $this->orderType;
    }

    /**
     * @return int
     */
    public function getPeriod(): ?int
    
    {
        return $this->period;
    }

    /**
     * @return string
     */
    public function getPledgePrice(): ?string
    
    {
        return $this->pledgePrice;
    }

    /**
     * @return int
     */
    public function getPowerPaidType(): ?int
    
    {
        return $this->powerPaidType;
    }

    /**
     * @return string
     */
    public function getPrice(): ?string
    
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getProductId(): ?int
    
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function getProductName(): ?string
    
    {
        return $this->productName;
    }

    /**
     * @return int
     */
    public function getProductType(): ?int
    
    {
        return $this->productType;
    }

    /**
     * @return string
     */
    public function getRealHash(): ?string
    
    {
        return $this->realHash;
    }

    /**
     * @return string
     */
    public function getRewardRatio(): ?string
    
    {
        return $this->rewardRatio;
    }

    /**
     * @return string|null
     */
    public function getShelfDate(): ?string
    
    {
        return $this->shelfDate;
    }

    /**
     * @return string
     */
    public function getTotalHash(): ?string
    
    {
        return $this->totalHash;
    }

    /**
     * @return string
     */
    public function getTotalIncome(): ?string
    
    {
        return $this->totalIncome;
    }

    /**
     * @return string
     */
    public function getTotalIncomeReward(): ?string
    
    {
        return $this->totalIncomeReward;
    }

    /**
     * @return string
     */
    public function getTotalPrice(): ?string
    
    {
        return $this->totalPrice;
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
     * @return string
     */
    public function getValidHash(): ?string
    
    {
        return $this->validHash;
    }

    /**
     * @return string
     */
    public function getWorkNumber(): ?string
    
    {
        return $this->workNumber;
    }


}
