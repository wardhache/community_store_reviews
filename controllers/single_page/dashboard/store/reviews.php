<?php

namespace Concrete\Package\CommunityStoreReviews\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Core;
use User;
use Group;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\Review as StoreReview;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatus as StoreReviewStatus;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewList as StoreReviewList;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatusHistory as StoreReviewStatusHistory;

class Reviews extends DashboardPageController
{

    public function view($status = '') {
        $reviewList = new StoreReviewList();

        if ($this->get('keywords')) {
            $reviewList->setSearch($this->get('keywords'));
        }

        if ($status) {
            $reviewList->setStatus($status);
        }

        $reviewList->setItemsPerPage(20);

        $paginator = $reviewList->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('reviewList',$paginator->getCurrentPageResults());
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);
        $this->set('reviewStatuses', StoreReviewStatus::getList());
        $this->requireAsset('css', 'communityStoreReviewsDashboard');
        $this->set('statuses', StoreReviewStatus::getAll());
    }

    public function review($rID) {
        $review = StoreReview::getByID($rID);

        if ($review) {
            $this->set("review", $review);
            $this->set('reviewStatuses', StoreReviewStatus::getList());
        } else {
            $this->redirect('/dashboard/store/reviews');
        }

        $this->requireAsset('css', 'communityStoreReviewsDashboard');
        $this->set('pageTitle', t("Review #") . $review->getID());
    }

    public function change_review_status() {
        $args = $this->post();
        $review = 0;

        if ($args) {
            if(isset($args['review']) && !empty($args['review']) && isset($args['status']) && !empty($args['status'])) {
                $u = new User();
                $g = Group::getByName("Administrators");
                if($u->inGroup($g) && $u->isLoggedIn()) {
                    $statusHandle = $args['status'];
                    $statusName = StoreReviewStatus::getByHandle($statusHandle)->getName();
                    $rID = trim($args['review']);
                    $rsID = StoreReviewStatus::getByHandle($statusHandle)->getID();
                    $uID = $u->getUserID();
                    StoreReviewStatusHistory::add($rID, $rsID, $uID);

                    if(!isset($args['reload']) || $args['reload'] != 1) {
                      switch($statusHandle) {
                        case "pending":
                          echo "<label class='label label-info'>" . $statusName . "</label>";
                          break;
                        case "approved":
                          echo "<label class='label label-success'>" . $statusName . "</label>";
                          break;
                        case "not_approved":
                          echo "<label class='label label-danger'>" . $statusName . "</label>";
                          break;
                      }
                      exit;
                    } else {
                      $review = $rID;
                    }
                }
            }
        }
        if(!isset($args['reload']) || $args['reload'] != 1) {
          exit;
        } else {
          if($review != 0) {
            $this->redirect('/dashboard/store/reviews/review',$review);
          } else {
            $this->redirect('/dashboard/store/reviews/');
          }

        }
    }
}
