<?php

namespace Concrete\Package\CommunityStoreReviews\Job;

use Config;
use UserInfo;
use URL;
use Job as AbstractJob;
use \Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewReminder as StoreReviewReminder;

class CommunityStoreReviewReminder extends AbstractJob
{

    public function getJobName()
    {
        return t('Community Store Review Reminder');
    }

    public function getJobDescription()
    {
        return t('Sends a mail to customers for reviewing their last order.');
    }


    public function run()
    {
        try {
            $reminders = StoreReviewReminder::getByScheduledDate();

            if (!empty($reminders)) {
                foreach ($reminders as $reminder) {
                    $order = $reminder->getOrder();

                    $reviewReminderFrom = Config::get('community_store_review.reminderFrom');

                    if (empty($reviewReminderFrom) && trim($reviewReminderFrom) != '') {
                        $reviewReminderFrom = "store@" . $_SERVER['SERVER_NAME'];
                    }

                    $customerID = $order->getCustomerID();
                    $customerInfo = UserInfo::getByID($customerID);
                    $reviewReminderTo = $customerInfo->getUserEmail();

                    $siteName = Config::get('concrete.site');
                    $url = URL::to('/reviews');
                    $mailHash = Config::get('community_store_review.reminderHash');

                    $mailHeaderHtml = Config::get('community_store_review.reminderMailHeaderHtml');
                    $mailHeaderContent = Config::get('community_store_review.reminderMailHeaderContent');
                    $mailFooterContent = Config::get('community_store_review.reminderMailFooterContent');
                    $mailFooterHtml = Config::get('community_store_review.reminderMailFooterHtml');

                    $mh = Core::make('mail');

                    $mh->from($reviewReminderFrom);
                    $mh->to($reviewReminderTo);

                    $mh->addParameter("order", $order);
                    $mh->addParameter("siteName", $siteName);
                    $mh->addParameter("url", $url);
                    $mh->addParameter("mailHash", $mailHash);
                    $mh->addParameter("mailHeaderHtml", $mailHeaderHtml);
                    $mh->addParameter("mailHeaderContent", $mailHeaderContent);
                    $mh->addParameter("mailFooterContent", $mailFooterContent);
                    $mh->addParameter("mailFooterHtml", $mailFooterHtml);
                    $mh->load("review_reminder", "community_store_reviews");
                    $mh->sendMail();

                    $reminder->setSent(1);
                    $reminder->save();
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
