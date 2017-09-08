<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review;

use Package;
use Page;
use PageType;
use PageTemplate;
use Database;
use File;
use Core;
use Config;
use Events;
use User;
use UserInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRatingValues as StoreReviewRatingValues;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewEvent as StoreReviewEvent;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatus as StoreReviewStatus;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatusHistory as StoreReviewStatusHistory;

/**
 * @Entity
 * @Table(name="CommunityStoreReviews")
 */
class Review
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $rID;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $cID;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $oID;

    /**
     * @Column(type="datetime")
     */
    protected $rDate;

    /**
     * @Column(type="string")
     */
    protected $rNickname;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $rTitle;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $rComment;

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRatingValues", mappedBy="review",cascade={"persist"}))
     */
    protected $ratings;

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatusHistory", mappedBy="review",cascade={"persist"}))
     */
    protected $statuses;

    protected static $table = "CommunityStoreReviews";

    public static function getTableName()
    {
        return self::$table;
    }

    public function setCustomerID($cID)
    {
        $this->cID = $cID;
    }

    public function setProductID($pID)
    {
        $this->pID = $pID;
    }

    public function setOrderID($oID)
    {
        $this->oID = $oID;
    }

    public function setDate($rDate)
    {
        $this->rDate = $rDate;
    }

    public function setNickname($rNickname)
    {
        $this->rNickname = $rNickname;
    }

    public function setTitle($rTitle)
    {
        $this->rTitle = $rTitle;
    }

    public function setComment($rComment)
    {
        $this->rComment = $rComment;
    }

    public function setProduct($product)
    {
        $this->pID = $product->getID();
    }

    public function getID()
    {
        return $this->rID;
    }

    public function getCustomerID()
    {
        return $this->cID;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function getOrderID()
    {
        return $this->oID;
    }

    public function getDate()
    {
        return $this->rDate;
    }

    public function getNickname()
    {
        return $this->rNickname;
    }

    public function getTitle()
    {
        return $this->rTitle;
    }

    public function getComment()
    {
        return $this->rComment;
    }

    public function getProduct()
    {
        return StoreProduct::getByID($this->pID);
    }

    public function getCustomer()
    {
        $customerID = $this->cID;

        if (!empty($customerID) && trim($customerID) != '' && trim($customerID) != 0) {
            $u = User::getByUserID($customerID);
            return $u;
        } else {
            return false;
        }
    }

    public function getCustomerInfo()
    {
        $customerID = $this->cID;

        if (!empty($customerID) && trim($customerID) != '' && trim($customerID) != 0) {
            $ui = UserInfo::getByID($customerID);

            return $ui;
        } else {
            return false;
        }
    }

    public function getRatings()
    {
        return $this->ratings;
    }

    public function getRatingsList()
    {
        $ratings = $this->ratings;

        $ratingsList = array();
        if (!empty($ratings)) {
            foreach ($ratings as $rating) {
                $ratingsList[$rating->getRating()->getName()] = $rating->getValue();
            }
        }

        return $ratingsList;
    }

    public function getAverageRating()
    {
        $ratings = $this->ratings;

        $averageRating = 0;
        if (!empty($ratings)) {
            foreach ($ratings as $rating) {
                $currentRating = $rating->getValue();
                $averageRating += $currentRating;
            }

            $averageRating = $averageRating / count($ratings);
        }

        return $averageRating;
    }

    public static function getAverageRatingOfProduct($product)
    {
        $db = \Database::connection();

        $status = $db->fetchAssoc("SELECT AVG(rra.rraValue) as avgRating FROM CommunityStoreReviews r
                                  INNER JOIN CommunityStoreReviewRatings rra ON rra.rID = r.rID
                                  WHERE r.pID = ?", array($product->getID()));

        return $status['avgRating'];
    }

    public static function getAverageApprovedRatingOfProduct($product)
    {
        $db = \Database::connection();

        $status = $db->fetchAssoc("SELECT AVG(rra.rraValue) as avgRating FROM CommunityStoreReviews r
                                  INNER JOIN CommunityStoreReviewStatusHistories rsh ON rsh.rID = r.rID
                                  INNER JOIN CommunityStoreReviewStatuses rs ON rs.rsID = rsh.rsID
                                  INNER JOIN CommunityStoreReviewRatings rra ON rra.rID = r.rID
                                  WHERE r.pID = ? AND rs.rsHandle = ?
                                  AND rsh.rshDate = 
                                  (SELECT MAX(rsh2.rshDate) FROM CommunityStoreReviewStatusHistories rsh2
                                  WHERE rsh2.rID = rsh.rID)", array($product->getID(), 'approved'));

        return $status['avgRating'];
    }

    public function getStatusHistory()
    {
        return $this->statuses;
    }

    public function getCurrentStatus()
    {
        $db = \Database::connection();

        $status = $db->fetchAssoc("SELECT rshID, rID, rsID FROM CommunityStoreReviewStatusHistories rsh 
                                  WHERE rsh.rID = ? 
                                  ORDER BY rshID DESC 
                                  LIMIT 0,1;", array($this->getID()));

        return StoreReviewStatus::getByID($status['rsID']);
    }

    public static function getByID($rID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $rID);
    }

    public static function getAllByOrderID($oID)
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT rID FROM " . self::getTableName() . " WHERE oID = ?", $oID);
        $reviews = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $reviews[] = self::getByID($row['rID']);
            }
        }

        return $reviews;
    }

    public static function getReviewedProductIDs()
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT DISTINCT pID FROM " . self::getTableName());

        $products = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $products[] = $row['pID'];
            }
        }

        return $products;
    }

    public static function getReviewedProductIDsByOrderID($oID)
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT pID FROM " . self::getTableName() . " WHERE oID = ?", $oID);
        $products = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $products[] = $row['pID'];
            }
        }

        return $products;
    }

    public static function getByOrderProduct($oID, $pID)
    {
        $db = \Database::connection();
        $data = $db->GetRow("SELECT rID FROM " . self::getTableName() . " WHERE oID=? AND pID=?", array($oID, $pID));

        if (empty($data)) {
          return null;
        } else {
          return self::getByID($data['rID']);
        }
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function saveReview($data)
    {
        if ($data['rID']) {
            $review = self::getByID($data['rID']);
            $originalReview = clone $review;

            $newReview = false;
        } else {
            $review = new self();
            $newReview = true;
        }

        if ($data['pID']) {
            $product = StoreProduct::getByID($data['pID']);

            $review->setNickname($data['rNickname']);
            $review->setTitle($data['rTitle']);
            $review->setComment($data['rComment']);
            $review->setProduct($product);
            $review->setCustomerID($data['cID']);
            $review->setOrderID($data['oID']);
            $review->setDate(new \DateTime());

            $review->save();

            $averageRating = 0;
            $ratingMaximum = 5;
            $ratingToPercentage = 100 / $ratingMaximum;

            if ($data['rating']) {
                foreach ($data['rating'] as $ratingID => $ratingValue) {
                    $ratingPercentage = $ratingValue * $ratingToPercentage;

                    $argsRating = array();
                    $argsRating['rID'] = $review->getID();
                    $argsRating['raID'] = $ratingID;
                    $argsRating['rraValue'] = $ratingPercentage;

                    StoreReviewRatingValues::saveRatingForReview($argsRating);
                    $averageRating += $ratingPercentage;
                }
                $averageRating = $averageRating / count($data['rating']);
            }

            if ($data['rsID']) {
                $currentReviewStatusID = StoreReviewStatusHistory::getCurrentStatusIdByReview($review);

                if($currentReviewStatusID != $data['rsID']) {
                    $rsID = $data['rsID'];
                    $newStatus = true;
                } else {
                    $newStatus = false;
                }
            } else {
                $autoApprove = Config::get('community_store_review.autoApprove');
                $autoApproveRating = Config::get('community_store_review.autoApproveRating');

                if ($autoApprove == 1 && !empty($autoApproveRating) &&
                    trim($autoApproveRating) != '' && $autoApproveRating <= $averageRating
                ) {
                    $status = StoreReviewStatus::getByHandle('approved');
                } else {
                    $status = StoreReviewStatus::getByHandle('pending');
                }

                $rsID = $status->getID();
                $newStatus = true;
            }

            if ($newStatus) {
                $rID = $review->getID();
                $rsID = $rsID;
                $uID = $data['cID'];

                StoreReviewStatusHistory::add($rID, $rsID, $uID);
            }

            if ($newReview) {
                $event = new StoreReviewEvent($review);
                Events::dispatch('on_community_store_review_add', $event);
            } else {
                $event = new StoreReviewEvent($originalReview, $review);
                Events::dispatch('on_community_store_review_update', $event);
            }

            $formNotifyMeOnSubmission = Config::get('community_store_review.formNotifyMeOnSubmission');
            if ($formNotifyMeOnSubmission == 1) {
                $review->sendNotificationEmail();
            }

            return $review;
        } else {
            return false;
        }
    }

    public function sendNotificationEmail()
    {
        $formRecipientEmail = Config::get('community_store_review.formRecipientEmail');

        if (!empty($formRecipientEmail) && trim($formRecipientEmail) != '') {
            $fromEmail = "store@" . $_SERVER['SERVER_NAME'];

            $mh = \Core::make('mail');

            $mh->from($fromEmail);
            $mh->to($formRecipientEmail);

            $ratings = StoreReviewRatingValues::getByReview($this);

            $mh->addParameter("review", $this);
            $mh->addParameter("ratings", $ratings);
            $mh->load("new_review_notification", "community_store_reviews");
            $mh->sendMail();
        }
    }
}
