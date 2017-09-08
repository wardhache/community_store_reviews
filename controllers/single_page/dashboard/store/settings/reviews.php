<?php

namespace Concrete\Package\CommunityStoreReviews\Controller\SinglePage\Dashboard\Store\Settings;

use View;
use Core;
use Config;
use GroupList;
use URL;
use Loader;
use GroupTree;
use \Concrete\Core\Page\Controller\DashboardPageController;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatus as StoreReviewStatus;
use \Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRating as StoreReviewRating;

class Reviews extends DashboardPageController
{
    public function view()
    {
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');

        $this->set("reviewStatuses", StoreReviewStatus::getAll());
        $this->set("generalRating", StoreReviewRating::getStandardRating());
        $this->set("customRatings", StoreReviewRating::getCustomRatings());

        $orderStatuses = StoreOrderStatus::getAll();
        $orderStatusArr = array();
        if(!empty($orderStatuses)) {
            foreach($orderStatuses as $status) {
                $orderStatusArr[$status->getID()] = $status->getName();
            }
        }
        $this->set("orderStatuses", $orderStatusArr);

        $userGroupsArr = array();
        $groupTree = GroupTree::get();
        $node = $groupTree->getRootTreeNodeObject();
        $node->populateChildren();
        if (is_object($node)) {
            foreach($node->getChildNodes() as $key => $group) {
                $userGroupsArr[$key] = $group->getTreeNodeName();
            }
        }
        $this->set("userGroups", $userGroupsArr);
    }

    public function success()
    {
        $this->set('success',t('Settings Saved'));
        $this->view();
    }

    public function failed()
    {
        $this->view();
    }

    public function save() {
        $this->view();
        $args = $this->post();

        if ($args) {
            $errors = $this->validate($args);
            $this->error = $errors;

            if (!$errors->has()) {

                Config::save('community_store_review.autoApprove',$args['autoApprove']);
                Config::save('community_store_review.autoApproveRating',$args['autoApproveRating']);
                Config::save('community_store_review.formGroups', $args['formGroups']);
                Config::save('community_store_review.captchaMail', $args['captchaMail']);
                Config::save('community_store_review.captchaFormLoggedIn', $args['captchaFormLoggedIn']);
                Config::save('community_store_review.captchaFormLoggedOut', $args['captchaFormLoggedOut']);
                Config::save('community_store_review.formTitle', $args['formTitle']);
                Config::save('community_store_review.formSubmitText', $args['formSubmitText']);
                Config::save('community_store_review.formNotifyMeOnSubmission', $args['formNotifyMeOnSubmission']);
                Config::save('community_store_review.formRecipientEmail', $args['formRecipientEmail']);
                Config::save('community_store_review.formThankyouMsg', $args['formThankyouMsg']);
                Config::save('community_store_review.formRedirectCID', $args['formRedirectCID']);
                Config::save('community_store_review.reminder', $args['reminder']);
                Config::save('community_store_review.reminderOrderAfterDays', $args['reminderOrderAfterDays']);
                Config::save('community_store_review.reminderOrderStatus', $args['reminderOrderStatus']);
                Config::save('community_store_review.reminderCc', trim($args['reminderCc']));
                Config::save('community_store_review.reminderFrom', trim($args['reminderFrom']));
                Config::save('community_store_review.reminderFromName', trim($args['reminderFromName']));
                Config::save('community_store_review.reminderMailHeaderHtml', trim($args['reminderMailHeaderHtml']));
                Config::save('community_store_review.reminderMailHeaderContent', trim($args['reminderMailHeaderContent']));
                Config::save('community_store_review.reminderMailFooterContent', trim($args['reminderMailFooterContent']));
                Config::save('community_store_review.reminderMailFooterHtml', trim($args['reminderMailFooterHtml']));

                if (isset($args['rsID'])) {
                    foreach ($args['rsID'] as $key => $id) {
                        $reviewStatus = StoreReviewStatus::getByID($id);
                        $reviewStatusSettings = array(
                            'rsName' => ((isset($args['rsName'][$key]) && $args['rsName'][$key]!='') ?
                                $args['rsName'][$key] : $reviewStatus->getName()),
                            'rsShowFrontend' => isset($args['rsShowFrontend']) && $args['rsShowFrontend'] == $id ? 1 : 0,
                            'rsSortOrder' => $key
                        );
                        $reviewStatus->update($reviewStatusSettings);
                    }
                }

                $allRatings = StoreReviewRating::getAll();
                $allRatingIds = array();
                foreach($allRatings as $rating) {
                    $allRatingIds[$rating->getID()] = $rating->getName();
                }

                if (isset($args['raID'])) {
                    foreach ($args['raID'] as $key => $id) {
                        if (empty($id)) {
                            $newRating = array(
                                'raName' => $args['raName'][$key],
                                'raHandle' => $args['raHandle'][$key],
                                'raStandard' => $args['raStandard'][$key],
                                'raSortOrder' => $key
                            );
                            StoreReviewRating::add($newRating['raHandle'], $newRating['raName'], $newRating['raSortOrder'], $newRating['raStandard']);
                        } else {
                            $reviewRating = StoreReviewRating::getByID($id);
                            $reviewRatingSettings = array(
                                'raName' => $args['raName'][$key],
                                'raHandle' => $args['raHandle'][$key],
                                'raStandard' => $args['raStandard'][$key],
                                'raSortOrder' => $key
                            );
                            $reviewRating->update($reviewRatingSettings);
                            unset($allRatingIds[$id]);
                        }
                    }
                    foreach($allRatingIds as $ratingToDeleteID => $ratingToDeleteName) {
                        StoreReviewRating::delete($ratingToDeleteID);
                    }
                }

                $this->redirect('/dashboard/store/settings/reviews/success');
            }
        }
    }

    public function validate($args)
    {
        $e = Core::make('helper/validation/error');

        if (!isset($args['rsName'])) {
            $e->add(t('You must have at least one Review Status.'));
        }

        if (!isset($args['raName'])) {
            $e->add(t('You must have at least one Review Rating.'));
        } else {
            foreach($args['raName'] as $raName) {
                if(empty($raName)) {
                    $e->add(t('All the ratings must have a name'));
                    break;
                }
            }
        }

        if (isset($args['raHandle'])) {
            foreach($args['raHandle'] as $raHandle) {
                if(empty($raHandle)) {
                    $e->add(t('All the ratings must have a handle'));
                    break;
                }
                if(!preg_match('/^[a-zA-Z0-9_-]+$/', $raHandle)) {
                    $e->add(t('Only letters, numbers and underscore is allowed for a rating handle.'));
                    break;
                }
            }
        }

        if ($args['autoApprove'] == 1) {
            if (empty($args['autoApproveRating'])) {
                $e->add(t('You must assing a minimum average rating for auto approval'));
            } else {
                if($args['autoApproveRating'] < 0 || $args['autoApproveRating'] > 100) {
                    $e->add(t('The minimum average rating must be between 0 &amp; 100'));
                }
            }
        }

        if($args['reminder'] == 1) {
            if (empty($args['reminderOrderAfterDays'])) {
                $e->add(t('You must set the days after order creation for the review reminders.'));
            }
            if (empty($args['reminderOrderStatus'])) {
                $e->add(t('You must set the order status for the review reminders.'));
            }
            if (empty($args['reminderFrom'])) {
                $e->add(t('You must set the from e-mail for the review reminders.'));
            }
        }

        return $e;
    }
}
