<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 邀请记录表
 * Class InviteLog
 *
 * @since 2.0
 *
 * @Entity(table="invite_log")
 */
class InviteLog extends Model
{
    /**
     * 邀请时间
     *
     * @Column(name="create_time", prop="createTime")
     *
     * @var int
     */
    private $createTime;

    /**
     * 
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * 被邀请注册时的账号
     *
     * @Column(name="invited_account", prop="invitedAccount")
     *
     * @var string
     */
    private $invitedAccount;

    /**
     * 被邀请的用户id
     *
     * @Column(name="invited_uid", prop="invitedUid")
     *
     * @var string
     */
    private $invitedUid;

    /**
     * 状态，0-已失效，1-生效中
     *
     * @Column()
     *
     * @var int
     */
    private $status;

    /**
     * 邀请用户id
     *
     * @Column()
     *
     * @var int
     */
    private $uid;


    /**
     * @param int $createTime
     *
     * @return self
     */
    public function setCreateTime(int $createTime): self
    {
        $this->createTime = $createTime;

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
     * @param string $invitedAccount
     *
     * @return self
     */
    public function setInvitedAccount(string $invitedAccount): self
    {
        $this->invitedAccount = $invitedAccount;

        return $this;
    }

    /**
     * @param string $invitedUid
     *
     * @return self
     */
    public function setInvitedUid(string $invitedUid): self
    {
        $this->invitedUid = $invitedUid;

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
    public function getCreateTime(): ?int
    
    {
        return $this->createTime;
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
    public function getInvitedAccount(): ?string
    
    {
        return $this->invitedAccount;
    }

    /**
     * @return string
     */
    public function getInvitedUid(): ?string
    
    {
        return $this->invitedUid;
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
    public function getUid(): ?int
    
    {
        return $this->uid;
    }


}
