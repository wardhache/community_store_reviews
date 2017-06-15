<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review;

use Package;
use Database;
use Config;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;

/**
 * @Entity
 * @Table(name="CommunityStoreReviewReminderQueue")
 */
class ReviewReminder
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $rrqID;

    /**
     * @Column(type="integer")
     */
    protected $oID;

    /**
     * @Column(type="datetime")
     */
    protected $rrqDate;

    /**
     * @Column(type="date")
     */
    protected $rrqScheduledDate;

    /**
     * @Column(type="boolean")
     */
    protected $rrqSent;

    protected static $table = "CommunityStoreReviewReminderQueue";

    public static function getTableName()
    {
        return self::$table;
    }

    public function setDate($rrqDate)
    {
        $this->rrqDate = $rrqDate;
    }

    public function setScheduledDate($rrqScheduledDate)
    {
        $this->rrqScheduledDate = $rrqScheduledDate;
    }

    public function setSent($rrqSent)
    {
        $this->rrqSent = $rrqSent;
    }

    public function setOrder($order) {
        $this->oID = $order->getOrderID();
    }

    public function getID() {
      return $this->rrqID;
    }

    public function getOrderID() {
      return $this->oID;
    }

    public function getDate() {
      return $this->rrqDate;
    }

    public function getScheduledDate() {
      return $this->rrqScheduledDate;
    }

    public function getFrom() {
      return $this->rrqFrom;
    }

    public function getSent() {
      return $this->rrqSent;
    }

    public function getOrder() {
      return StoreOrder::getByID($this->oID);
    }

    public static function getByID($rrqID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $rrqID);
    }

    public static function getByOrderID($oID) {
        $db = \Database::connection();
        $data = $db->GetRow("SELECT rrqID FROM " . self::getTableName() . " WHERE oID=?", $oID);

        return self::getByID($data['rrqID']);
    }

    public static function getByScheduledDate($date = null) {
        if($date == null) {
          $date = date('Y-m-d');
        }

        $db = \Database::connection();
        $rows = $db->GetAll("SELECT rrqID FROM " . self::getTableName() . " WHERE rrqScheduledDate=? AND rrqSent=?", array($date, 0));
        $reminders = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $reminders[] = self::getByID($row['rrqID']);
            }
        }

        return $reminders;
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function add($order) {
      if(!empty($order)) {
        $reviewReminderOrderAfterDays = Config::get('community_store_review.reminderOrderAfterDays');
        if(empty($reviewReminderOrderAfterDays) && trim($reviewReminderOrderAfterDays) != '') {
          $reviewReminderOrderAfterDays = 14;
        }

        $scheduledDate = new \DateTime();
        $scheduledDate->modify('+' . $reviewReminderOrderAfterDays . ' day');

        if(empty(self::getByOrderID($order->getOrderID()))) {
          $reviewReminder = new self();
          $reviewReminder->setOrder($order);
          $reviewReminder->setDate(new \DateTime());
          $reviewReminder->setScheduledDate($scheduledDate);
          $reviewReminder->setSent(0);

          $reviewReminder->save();
        }
      }

    }
}
