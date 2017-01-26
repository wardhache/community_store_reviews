<?php
namespace Concrete\Package\CommunityStoreReviews\Block\CommunityReviews;

use Concrete\Core\Block\BlockController;
use Core;
use Config;
use Page;
use Database;
use Request;
use User;
use Group;
use Loader;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRating as StoreReviewRating;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\Review as StoreReview;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewList as StoreReviewList;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatus as StoreReviewStatus;

defined('C5_EXECUTE') or die("Access Denied.");
class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreReviews';
    protected $btInterfaceWidth = "800";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "500";
    protected $btDefaultSet = 'community_store';

    public function getBlockTypeDescription()
    {
        return t("Add Reviews for Community Store");
    }

    public function getBlockTypeName()
    {
        return t("Reviews");
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('css', 'community-store-reviews');
    }

    public function view()
    {
        $reviews = new StoreReviewList();

        if ($this->filter == 'current' || $this->filter == 'page') {
            if($this->filter == 'page') {
              $page = Page::getByID($this->filterCID);
            } else {
              $page = Page::getCurrentPage();
            }
            $reviews->setCID($page->getCollectionID());
            $reviews->setLocation('product');
        }

        if ($this->filter == 'current_children' || $this->filter == 'page_children') {
            if($this->filter == 'page_children') {
              $page = Page::getByID($this->filterCID);
            } else {
              $page = Page::getCurrentPage();
            }

            $reviews->setCID($page->getCollectionID());
            if ($page) {
                $reviews->setCIDs($page->getCollectionChildrenArray());
            }
            $this->set('showProduct', true);
            $reviews->setLocation('category');
        }

        $status = StoreReviewStatus::getByShowFrontend();
        $reviews->setStatus($status->getHandle());
        $reviews->setItemsPerPage($this->maxReviews > 0 ? $this->maxReviews : 1000);
        $reviews->setSortBy($this->sortOrder);

        $paginator = $reviews->getPagination();
        $pagination = $paginator->renderDefaultView();
        $reviews = $paginator->getCurrentPageResults();

        $this->set('reviews', $reviews);
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        $u = new User();
        $userGroups = $u->getUserGroups();
        $groupsShowForm = Config::get('community_store_review.formGroups');
        $this->set('user', $u);

        $cPage = Page::getCurrentPage();
        $product = StoreProduct::getByCollectionID($cPage->getCollectionID());

        if(!empty($groupsShowForm) && !empty($userGroups) && !empty($product)) {
          $this->set('product', $product);
          foreach($userGroups as $groupID) {
            $userGroup = Group::getByID($groupID);
            $userGroupName = $userGroup->getGroupName();
            if(in_array($userGroupName, $groupsShowForm)) {
              $this->set('userShowForm', true);
              break;
            }
          }
        }

        $formTitle = Config::get('community_store_review.formTitle');
        $this->set('formTitle', (!empty($formTitle) && trim($formTitle) != '' ? t($formTitle) : t('Write your own review')));
        $formSubmitText = Config::get('community_store_review.formSubmitText');
        $this->set('formSubmitText', (!empty($formSubmitText) && trim($formSubmitText) != '' ? t($formSubmitText) : t('Submit')));

        if(($u->isLoggedIn() && Config::get('community_store_review.captchaFormLoggedIn') == 1) ||
          (!$u->isLoggedIn() && Config::get('community_store_review.captchaFormLoggedOut') == 1)) {
            $captcha = Loader::helper('validation/captcha');
            $this->set('captcha', $captcha);
        }

        $this->set('ratings', StoreReviewRating::getAllRatings());
    }

    public function save($args)
    {
        $args['showForm'] = isset($args['showForm']) ? 1 : 0;
        $args['showPagination'] = isset($args['showPagination']) ? 1 : 0;
        $args['maxReviews'] = (isset($args['maxReviews']) && $args['maxReviews'] > 0) ? $args['maxReviews'] : 0;

        parent::save($args);
    }

    public function validate($args)
    {
        $e = Core::make("helper/validation/error");
        $nh = Core::make("helper/number");

        if (($args['filter'] == 'page' || $args['filter'] == 'page_children') && $args['filterCID'] <= 0) {
            $e->add(t('A page must be selected'));
        }

        if ($args['maxReviews'] && !$nh->isInteger($args['maxReviews'])) {
            $e->add(t('Number of Products must be a whole number'));
        }

        return $e;
    }

    public function action_add_review() {
        $captcha = Loader::helper('validation/captcha');
        $this->view();
        $args = $this->post();

        if ($args) {
            $errors = $this->review_validate($args);
            $this->set('error', $errors->getList());

            if(!isset($args['ccmCaptchaCode'])) {
              $noCaptcha = true;
            }

            if (!$errors->has()) {
                if($captcha->check() || $noCaptcha) {
                  StoreReview::saveReview($args);

                  if($redirectCID = Config::get('community_store_review.formRedirectCID')) {
                    $rPage = Page::getByID($redirectCID);
                    $this->redirect($rPage->getCollectionLink());
                  } else {
                    $thanksMessage = Config::get('community_store_review.formThankyouMsg');
                    if(empty($thanksMessage) && trim($thanksMessage) != '') {
                      $thanksMessage = t('Thanks!');
                    }

                    $this->set('success', $thanksMessage);
                  }
                } else {
                  $errors = Core::make("helper/validation/error");
                  $errors->add(t('Incorrect image validation code. Please check the image and re-enter the letters or numbers as necessary.'));
                  $this->set('error', $errors->getList());
                }
            }

        }
    }

    public function review_validate($args) {
        $e = Core::make("helper/validation/error");
        $nh = Core::make("helper/number");

        if(empty($args['rNickname']) || trim($args['rNickname']) == '') {
            $e->add(t('%s is required', t('Nickname')));
        }
        if(empty($args['rTitle']) || trim($args['rTitle']) == '') {
            $e->add(t('%s is required', t('Title of the Review')));
        }
        if(empty($args['rating']) || count($args['rating']) != count(StoreReviewRating::getAllRatings())) {
            $e->add(t('You have to fill in all the ratings'));
        }

        return $e;
    }
}
