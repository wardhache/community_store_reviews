<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRatingValues as StoreReviewRatingValues;

/**
 * @Entity
 * @Table(name="CommunityStoreRatings")
 */
class ReviewRating extends Object
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $raID;

    /**
     * @Column(type="string")
     */
    protected $raHandle;

    /**
     * @Column(type="string")
     */
    protected $raName;

    /**
     * @Column(type="boolean",nullable=true)
     */
    protected $raStandard;

    /**
     * @Column(type="boolean")
     */
    protected $raActive;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $raSortOrder;

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRatingValues", mappedBy="reviewrating",cascade={"persist"}))
     */
    protected $ratings;

    protected static $table = "CommunityStoreRatings";

    public static function getTableName()
    {
        return self::$table;
    }

    public function setHandle($raHandle)
    {
        $this->raHandle = $raHandle;
    }

    public function setName($raName)
    {
        $this->raName = $raName;
    }

    public function setStandard($raStandard)
    {
        $this->raStandard = $raStandard;
    }

    public function setActive($raActive)
    {
        $this->raActive = $raActive;
    }

    public function setSortOrder($raSortOrder)
    {
        $this->raSortOrder = $raSortOrder;
    }

    public function getID() {
      return $this->raID;
    }

    public function getHandle() {
      return $this->raHandle;
    }

    public function getName() {
      return $this->raName;
    }

    public function getStandard() {
      return $this->raStandard;
    }

    public function getActive() {
      return $this->raActive;
    }

    public function getSortOrder() {
      return $this->raSortOrder;
    }

    public function getRatings() {
      return $this->ratings;
    }


    public function getFormHtml($checked = null, $layout = null) {
        if($this) {
          $html = '<div class="store-rating-container">' .
                    '<div class="store-rating-stars ' . $layout . '">' .
                      '<input class="store-rating-star" id="star-' . $this->raID . '-5" ' . ($checked==5 ? 'checked' : '') . ' type="radio" value="5" name="rating[' . $this->raID . ']"/>' .
                      '<label class="store-rating-star ' . $layout . '" for="star-' . $this->raID . '-5"></label>' .
                      '<input class="store-rating-star" id="star-' . $this->raID . '-4" ' . ($checked==4 ? 'checked' : '') . ' type="radio" value="4" name="rating[' . $this->raID . ']"/>' .
                      '<label class="store-rating-star ' . $layout . '" for="star-' . $this->raID . '-4"></label>' .
                      '<input class="store-rating-star" id="star-' . $this->raID . '-3" ' . ($checked==3 ? 'checked' : '') . ' type="radio" value="3" name="rating[' . $this->raID . ']"/>' .
                      '<label class="store-rating-star ' . $layout . '" for="star-' . $this->raID . '-3"></label>' .
                      '<input class="store-rating-star" id="star-' . $this->raID . '-2" ' . ($checked==2 ? 'checked' : '') . ' type="radio" value="2" name="rating[' . $this->raID . ']"/>' .
                      '<label class="store-rating-star ' . $layout . '" for="star-' . $this->raID . '-2"></label>' .
                      '<input class="store-rating-star" id="star-' . $this->raID . '-1" ' . ($checked==1 ? 'checked' : '') . ' type="radio" value="1" name="rating[' . $this->raID . ']"/>' .
                      '<label class="store-rating-star ' . $layout . '" for="star-' . $this->raID . '-1"></label>' .
                    '</div>' .
                  '</div>';

            return $html;
        } else {
            return null;
        }
    }

    public static function add($raHandle, $raName = null, $raSortOrder = 0, $raStandard = 0)
    {
        if (is_null($raName)) {
            $textHelper = new TextHelper();
            $raName = $textHelper->unhandle($raName);
        }
        $db = \Database::connection();
        $sql = "INSERT INTO " . self::getTableName() . " (raHandle, raName, raSortOrder, raStandard, raActive) VALUES (?, ?, ?, ?, ?)";
        $values = array(
            $raHandle,
            $raName,
            $raSortOrder,
            $raStandard,
            1
        );
        $db->query($sql, $values);
    }

    public function update($data = array(), $ignoreFilledColumns = false)
    {
        $ratingArray = array(
            'raHandle' => $this->raHandle,
            'raName' => $this->raName,
            'raSortOrder' => $this->raSortOrder,
            'raStandard' => $this->raStandard
        );

        $ratingUpdateColumns = $ignoreFilledColumns ? array_diff($ratingArray, $data) : array_merge($ratingArray, $data);
        unset($ratingUpdateColumns['raID']);
        if (count($ratingUpdateColumns) > 0) {
            $columnPhrase = implode('=?, ', array_keys($ratingUpdateColumns)) . "=?";
            $values = array_values($ratingUpdateColumns);
            $values[] = $this->raID;
            \Database::connection()->Execute("UPDATE " . self::getTableName() . " SET " . $columnPhrase . " WHERE raID=?", $values);
            return true;
        }
        return false;
    }

    public function delete($raID)
    {
      $sql = "UPDATE " . self::getTableName() . " SET raActive=? WHERE raID=?";
      \Database::connection()->Execute($sql, array(0, $raID));
    }

    public static function getByID($raID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $raID);
    }

    public static function getByHandle($raHandle)
    {
        $db = \Database::connection();
        $data = $db->GetRow("SELECT raID FROM " . self::getTableName() . " WHERE raHandle=?", $raHandle);

        return self::getByID($data['raID']);
    }

    public static function getAll()
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT raID FROM " . self::getTableName() . " WHERE raActive=? ORDER BY raSortOrder ASC, raID ASC", 1);
        $ratings = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $ratings[] = self::getByID($row['raID']);
            }
        }

        return $ratings;
    }

    public static function getStandardRating()
    {
        $db = \Database::connection();
        $raStandard = 1;
        $data = $db->GetRow("SELECT raID FROM " . self::getTableName() . " WHERE raStandard=?", $raStandard);

        return self::getByID($data['raID']);
    }

    public static function getCustomRatings()
    {
        $db = \Database::connection();
        $raStandard = 0;
        $rows = $db->GetAll("SELECT raID FROM " . self::getTableName() . " WHERE raStandard=? and raActive=? ORDER BY raSortOrder ASC, raID ASC", array($raStandard, 1));
        $ratings = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $ratings[] = self::getByID($row['raID']);
            }
        }

        return $ratings;
    }

    public static function getAllRatings() {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT raID FROM " . self::getTableName() . " WHERE raActive=? ORDER BY raSortOrder ASC, raID ASC", 1);
        $ratings = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $ratings[] = self::getByID($row['raID']);
            }
        }

        return $ratings;
    }

}
