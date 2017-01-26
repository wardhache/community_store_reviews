<?php
namespace Concrete\Package\CommunityStoreReviews;

use Package;
use Page;
use SinglePage;
use Route;
use Database;
use Asset;
use AssetList;
use Config;
use Events;
use Loader;
use Whoops\Exception\ErrorException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Installer;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatus as StoreReviewStatus;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRating as StoreReviewRating;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewReminder as StoreReviewReminder;


class controller extends Package{

  protected $pkgHandle = 'community_store_reviews';
  protected $appVersionRequired = '5.7.5.8';
  protected $pkgVersion = '0.7.7';

  public function getPackageDescription(){
    return t("Boost your store and ask customer feedback.");
  }

  public function getPackageName(){
    return t("Community Store Reviews");
  }

  public function on_start(){
    $al = AssetList::getInstance();
    $al->register('css', 'community-store-reviews', 'css/community-store-reviews.css', array('version' => '1', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false), $this);
    $al->register('css', 'communityStoreReviewsDashboard', 'css/communityStoreReviewsDashboard.css', array('version' => '1', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false), $this);

    Events::addListener('on_community_store_order_status_update', function($event) {
      $reviewReminderEnabled = Config::get('community_store_review.reminder');
      if($reviewReminderEnabled == 1) {
        $order = $event->getOrder();
        if(!empty($order)) {
          $status = $order->getStatusHandle();
          $reviewReminderOrderStatusID = Config::get('community_store_review.reminderOrderStatus');
          $reviewReminderOrderStatusHandle = StoreOrderStatus::getByID($reviewReminderOrderStatusID)->getHandle();

          if($status == $reviewReminderOrderStatusHandle) {
            StoreReviewReminder::add($order);
          }
        }
      }
    });
  }

  public function install(){
    $pkg = parent::install();
    $this->configure();

    $db = \Database::connection();
    $id = Loader::helper('validation/identifier');

    $table = StoreReviewStatus::getTableName();
    $statuses = array(
        array('rsHandle' => 'pending', 'rsName' => t('Pending'), 'rsShowFrontend' => 0, 'rsSortOrder' => 0),
        array('rsHandle' => 'approved', 'rsName' => t('Approved'), 'rsShowFrontend' => 1, 'rsSortOrder' => 1),
        array('rsHandle' => 'not_approved', 'rsName' => t('Not Approved'), 'rsShowFrontend' => 0, 'rsSortOrder' => 2),
    );
    $db->query("DELETE FROM " . $table);
    foreach ($statuses as $status) {
        StoreReviewStatus::add($status['rsHandle'], $status['rsName'], $status['rsShowFrontend'], $status['rsSortOrder']);
    }

    $table = StoreReviewRating::getTableName();
    $rating = array('raHandle' => 'general', 'raName' => t('General'), 'raSortOrder' => 0, 'raStandard' => 1);
    $db->query("DELETE FROM " . $table);
    StoreReviewRating::add($rating['raHandle'], $rating['raName'], $rating['raSortOrder'], $rating['raStandard']);

    Installer::setConfigValue('community_store_review.autoApprove', 0);
    Installer::setConfigValue('community_store_review.captchaMail', 0);
    Installer::setConfigValue('community_store_review.captchaFormLoggedIn', 0);
    Installer::setConfigValue('community_store_review.captchaFormLoggedOut', 1);
    Installer::setConfigValue('community_store_review.reminder', 0);
    Installer::setConfigValue('community_store_review.reminderHash', $id->getString());
    Installer::setConfigValue('community_store_review.reminderOrderStatus', StoreOrderStatus::getByHandle('delivered')->getID());
    Installer::setConfigValue('community_store_review.formGroups', array("Administrators", "Store Customer"));
    Installer::setConfigValue('community_store_review.formTitle', "Write your own review");
    Installer::setConfigValue('community_store_review.formSubmitText', "Submit");
    Installer::setConfigValue('community_store_review.formThankyouMsg', "Thanks!");
  }

  public function upgrade(){
    parent::upgrade();
    $this->configure();
  }

  public function uninstall(){
    $pkg = parent::uninstall();
  }

  public function configure() {
    $pkg = Package::getByHandle('community_store_reviews');

    Installer::installSinglePage('/dashboard/store/reviews', $pkg);
    Installer::installSinglePage('/dashboard/store/settings/reviews', $pkg);
    Installer::installSinglePage('/dashboard/store/reports/reviews', $pkg);
    Installer::installSinglePage('/reviews', $pkg);
    Page::getByPath('/reviews/')->setAttribute('exclude_nav', 1);

    Installer::installBlock('community_reviews', $pkg);

    \Concrete\Core\Job\Job::installByPackage('community_store_review_reminder', $pkg);
  }

}
