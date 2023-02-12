<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 * 推送用户文章表
 * Class ArticleToUser
 *
 * @since 2.0
 *
 * @Entity(table="article_to_user")
 */
class ArticleToUser extends Model
{
    /**
     * 推送给用户的文章id
     * @Id(incrementing=false)
     * @Column(name="article_id", prop="articleId")
     *
     * @var int
     */
    private $articleId;

    /**
     * 用户id
     *
     * @Column(name="user_id", prop="userId")
     *
     * @var int
     */
    private $userId;


    /**
     * @param int $articleId
     *
     * @return self
     */
    public function setArticleId(int $articleId): self
    {
        $this->articleId = $articleId;

        return $this;
    }

    /**
     * @param int $userId
     *
     * @return self
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getArticleId(): ?int
    
    {
        return $this->articleId;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    
    {
        return $this->userId;
    }


}
