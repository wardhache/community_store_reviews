<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating;

use Package;
use Page;
use PageType;
use PageTemplate;
use Database;
use File;
use Core;
use Config;
use Events;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRating as StoreReviewRating;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\Review as StoreReview;

/**
 * @Entity
 * @Table(name="CommunityStoreReviewRatings")
 */
class ReviewRatingValues
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $rraID;

    /**
     * @Column(type="integer")
     */
    protected $rID;

    /**
     * @Column(type="integer")
     */
    protected $raID;

    /**
     * @Column(type="float")
     */
    protected $rraValue;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\Review", inversedBy="ReviewRatingValues", cascade={"persist", "remove" })
     * @JoinColumn(name="rID", referencedColumnName="rID", onDelete="CASCADE")
     */
    protected $review;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRating", inversedBy="ReviewRatingValues", cascade={"persist", "remove" })
     * @JoinColumn(name="raID", referencedColumnName="raID", onDelete="CASCADE")
     */
    protected $rating;

    protected static $table = "CommunityStoreReviewRatings";

    public static function getTableName()
    {
        return self::$table;
    }

    public function setReviewID($rID)
    {
        $this->rID = $rID;
    }

    public function setRatingID($raID)
    {
        $this->raID = $raID;
    }

    public function setValue($rraValue)
    {
        $this->rraValue = $rraValue;
    }

    public function setReview($review)
    {
        $this->review = $review;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    public function getReviewID() {
      return $this->rID;
    }

    public function getRatingID() {
      return $this->raID;
    }

    public function getValue() {
      return $this->rraValue;
    }

    public function getReview() {
      return $this->review;
    }

    public function getRating() {
      return $this->rating;
    }

    public static function getByID($rraID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $rraID);
    }

    public static function getByReview($review) {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT rraID FROM " . self::getTableName() . " WHERE rID = ?", $review->getID());
        $ratings = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $ratings[] = self::getByID($row['rraID']);
            }
        }

        return $ratings;
    }

    public static function getAverageRatings() {
      $db = \Database::connection();
      $rows = $db->GetAll("SELECT raName, AVG(rraValue) AS avgValue FROM CommunityStoreReviewRatings rra INNER JOIN CommunityStoreRatings ra ON ra.raID = rra.raID GROUP BY rra.raID");

      return $rows;
    }

    public static function getAverageApprovedRatings() {
      $db = \Database::connection();
      $rows = $db->GetAll("SELECT raName, AVG(rraValue) AS avgValue FROM CommunityStoreReviewRatings rra
                          INNER JOIN CommunityStoreRatings ra ON ra.raID = rra.raID
                          WHERE rra.rID IN
                          (
                          SELECT rsh.rID FROM CommunityStoreReviewStatusHistories rsh
                          INNER JOIN CommunityStoreReviewStatuses rs ON rsh.rsID = rs.rsID
                          WHERE rs.rsHandle = ?
                          GROUP BY rsh.rID
                          ORDER BY rsh.rshDate DESC
                          )
                          GROUP BY rra.raID", 'approved');

      return $rows;
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function saveRatingForReview($data) {

      if ($data['rraID']) {
          $reviewRating = self::getByID($data['rraID']);
      } else {
          $reviewRating = new self();
      }

      if($data['rID'] && $data['raID']) {
          $review = StoreReview::getByID($data['rID']);
          $rating = StoreReviewRating::getByID($data['raID']);

          $reviewRating->setReview($review);
          $reviewRating->setRating($rating);
          $reviewRating->setValue($data['rraValue']);

          $reviewRating->save();

          return $reviewRating;
      } else {
          return false;
      }
    }
}
