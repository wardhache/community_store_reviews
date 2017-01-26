<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Events;
use User;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\Review as StoreReview;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatus as StoreReviewStatus;

/**
 * @Entity
 * @Table(name="CommunityStoreReviewStatusHistories")
 */
class ReviewStatusHistory extends Object
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $rshID;

    /**
      * @Column(type="integer")
      */
    protected $rID;

    /**
      * @Column(type="integer")
      */
    protected $rsID;

    /**
      * @Column(type="datetime")
      */
    protected $rshDate;

    /**
      * @Column(type="integer", nullable=true)
      */
    protected $uID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\Review",  cascade={"persist"})
     * @JoinColumn(name="rID", referencedColumnName="rID", onDelete="CASCADE")
     */
    protected $review;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatus",  cascade={"persist"})
     * @JoinColumn(name="rsID", referencedColumnName="rsID", onDelete="CASCADE")
     */
    protected $status;

    protected static $table = "CommunityStoreReviewStatusHistories";

    public static function getTableName()
    {
        return self::$table;
    }

    public function setReviewID($rID)
    {
        $this->rID = $rID;
    }

    public function setStatusID($rsID)
    {
        $this->rsID = $rsID;
    }

    public function setDate($rshDate)
    {
        $this->rshDate = $rshDate;
    }

    public function setUserID($uID)
    {
        $this->uID = $uID;
    }

    public function setReview($review) {
        $this->review = $review;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getReviewID() {
        return $this->rID;
    }

    public function getStatusID() {
        return $this->rsID;
    }

    public function getDate() {
        return $this->rshDate;
    }

    public function getUserID() {
        return $this->uID;
    }

    public function getReview() {
        return $this->review;
    }

    public function getStatus() {
        return $this->status;
    }

    public static function add($rID, $rsID, $uID)
    {
        $db = \Database::connection();
        $sql = "INSERT INTO " . self::getTableName() . " (rID, rsID, rshDate, uID) VALUES (?, ?, ?, ?)";
        $values = array(
            $rID,
            $rsID,
            date('Y-m-d H:i:s'),
            $uID
        );
        $db->query($sql, $values);
    }

    public static function getCurrentStatusIdByReview($review)
    {
        $db = \Database::connection();
        $data = $db->GetRow("SELECT rsID FROM " . self::getTableName() . " WHERE rID=? ORDER BY rshID DESC LIMIT 0,1", $review->getID());

        return $data['rsID'];
    }



}
