<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatusHistory as StoreReviewStatusHistory;

/**
 * @Entity
 * @Table(name="CommunityStoreReviewStatuses")
 */
class ReviewStatus extends Object
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $rsID;

    /** @Column(type="string") */
    protected $rsHandle;

    /** @Column(type="string") */
    protected $rsName;

    /** @Column(type="boolean") */
    protected $rsShowFrontend;

    /** @Column(type="integer",nullable=true) */
    protected $rsSortOrder;

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatusHistory", mappedBy="reviewstatus",cascade={"persist"}))
     */
    protected $reviews;

    protected static $table = "CommunityStoreReviewStatuses";

    public static function getTableName()
    {
        return self::$table;
    }

    public function setHandle($rsHandle)
    {
        $this->rsHandle = $rsHandle;
    }

    public function setName($rsName)
    {
        $this->rsName = $rsName;
    }

    public function setShowFrontend($rsShowFrontend)
    {
        $this->rsShowFrontend = $rsShowFrontend;
    }

    public function setSortOrder($rsSortOrder)
    {
        $this->rsSortOrder = $rsSortOrder;
    }

    public function getID() {
      return $this->rsID;
    }

    public function getHandle() {
      return $this->rsHandle;
    }

    public function getName() {
      return $this->rsName;
    }

    public function getShowFrontend() {
      return $this->rsShowFrontend;
    }

    public function getSortOrder() {
      return $this->rsSortOrder;
    }

    public function getReviews() {
      return $this->reviews;
    }

    public static function add($rsHandle, $rsName = null, $rsShowFrontend = 1, $rsSortOrder = 0)
    {
        if (is_null($rsName)) {
            $textHelper = new TextHelper();
            $rsName = $textHelper->unhandle($rsHandle);
        }
        $db = \Database::connection();
        $sql = "INSERT INTO " . self::getTableName() . " (rsHandle, rsName, rsShowFrontend, rsSortOrder) VALUES (?, ?, ?, ?)";
        $values = array(
            $rsHandle,
            $rsName,
            $rsShowFrontend,
            $rsSortOrder
        );
        $db->query($sql, $values);
    }

    public function update($data = array(), $ignoreFilledColumns = false)
    {
        $reviewStatusArray = array(
            'rsName' => $this->rsName,
            'rsShowFrontend' => $this->rsShowFrontend,
            'rsSortOrder' => $this->rsSortOrder
        );

        $reviewStatusUpdateColumns = $ignoreFilledColumns ? array_diff($reviewStatusArray, $data) : array_merge($reviewStatusArray, $data);
        unset($reviewStatusUpdateColumns['rsID']);
        if (count($reviewStatusUpdateColumns) > 0) {
            $columnPhrase = implode('=?, ', array_keys($reviewStatusUpdateColumns)) . "=?";
            $values = array_values($reviewStatusUpdateColumns);
            $values[] = $this->rsID;
            \Database::connection()->Execute("UPDATE " . self::getTableName() . " SET " . $columnPhrase . " WHERE rsID=?", $values);
            return true;
        }
        return false;
    }

    public static function getByID($rsID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $rsID);
    }

    public static function getByHandle($rsHandle)
    {
        $db = \Database::connection();
        $data = $db->GetRow("SELECT rsID FROM " . self::getTableName() . " WHERE rsHandle=?", $rsHandle);

        return self::getByID($data['rsID']);
    }

    public static function getByShowFrontend() {
        $db = \Database::connection();
        $data = $db->GetRow("SELECT rsID FROM " . self::getTableName() . " WHERE rsShowFrontend=?", 1);

        return self::getByID($data['rsID']);
    }

    public static function getAll()
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT rsID FROM " . self::getTableName() . " ORDER BY rsSortOrder ASC, rsID ASC");
        $statuses = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $statuses[] = self::getByID($row['rsID']);
            }
        }

        return $statuses;
    }

    public static function getList() {
        $statuses = array();
        foreach (self::getAll() as $status) {
            $statuses[$status->getHandle()] = t($status->getName());
        }

        return $statuses;
    }
}
