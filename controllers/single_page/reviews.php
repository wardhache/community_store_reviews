<?php

namespace Concrete\Package\CommunityStoreReviews\Controller\SinglePage;

use PageController;
use View;
use Core;
use Config;
use Loader;
use URL;
use \Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\Review as StoreReview;
use \Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewRating\ReviewRating as StoreReviewRating;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;

class Reviews extends PageController
{
    public function view()
    {
        $reviewReminder = false;
        $reviewData = array();

        if(!empty($_GET['rm']) || trim($_GET['rm']) != '') {
            $encryptor = \Core::make("helper/encryption");
            $output = $encryptor->decrypt($_GET['rm']);

            $data = array();
            $convert_to_array = explode('|', $output);
            for($i=0; $i < count($convert_to_array ); $i++){
                $key_value = explode('=', $convert_to_array [$i]);
                $data[$key_value[0]] = $key_value[1];
            }

            if(!empty($data) && isset($data['rHash'])) {
                $rHash = $data['rHash'];
                if($rHash == Config::get('community_store_review.reminderHash')) {
                    unset($data['rHash']);
                    $reviewReminder = true;
                    $reviewData = $data;
                }
            }
        }

        if($reviewReminder && !empty($reviewData) && count($reviewData) == 4) {
            $oID = $reviewData['oID'];
            $cID = $reviewData['cID'];
            $pID = $reviewData['pID'];
            $rRating = $reviewData['rRating'];

            $review = StoreReview::getByOrderProduct($oID, $pID);

            $product = StoreProduct::getByID($pID);
            $order = StoreOrder::getByID($oID);
            $this->set('showForm', true);
            $this->set('product', $product);
            $this->set('order', $order);
            $this->set('rating', $rRating);
            $this->set('customerID', $cID);

            if(!empty($review)) {
                $this->set('rSuccess', t('You already submitted a review of this product.'));
                $this->more_products($oID);
            } else {
                $formTitle = Config::get('community_store_review.formTitle');
                $this->set('formTitle', (!empty($formTitle) && trim($formTitle) != '' ? t($formTitle) : t('Write your own review')));
                $formSubmitText = Config::get('community_store_review.formSubmitText');
                $this->set('formSubmitText', (!empty($formSubmitText) && trim($formSubmitText) != '' ? t($formSubmitText) : t('Submit')));

                if(Config::get('community_store_review.captchaMail') == 1) {
                    $captcha = Loader::helper('validation/captcha');
                    $this->set('captcha', $captcha);
                }

                $this->set('standardRating', StoreReviewRating::getStandardRating());
                $this->set('customRatings', StoreReviewRating::getCustomRatings());

                $this->requireAsset('css', 'community-store-reviews');
            }
        } else {
            $this->set('error', t('Something went wrong...'));
        }
    }

    public function add_review()
    {
        $captcha = Loader::helper('validation/captcha');
        $this->view();
        $args = $this->post();

        if ($args) {
            $errors = $this->review_validate($args);
            $this->set('error', $errors->getList());

            if (!isset($args['ccmCaptchaCode'])) {
                $noCaptcha = true;
            }

            if (!$errors->has()) {
                if($captcha->check() || $noCaptcha) {
                    StoreReview::saveReview($args);

                    $thanksMessage = Config::get('community_store_review.formThankyouMsg');
                    if(empty($thanksMessage) && trim($thanksMessage) != '') {
                        $thanksMessage = t('Thanks!');
                    }
                    $this->set('rSuccess', $thanksMessage);

                    $product = StoreProduct::getByID($args['pID']);
                    $order = StoreOrder::getByID($args['oID']);
                    $this->set('showForm', true);
                    $this->set('product', $product);
                    $this->set('order', $order);
                    $this->set('customerID', $args['cID']);
                    $this->more_products($args['oID']);

                } else {
                    $errors = Core::make("helper/validation/error");
                    $errors->add(t('Incorrect image validation code. Please check the image and re-enter the letters or numbers as necessary.'));
                    $this->set('error', $errors->getList());
                }
            }
        }
    }

    public function review_validate($args)
    {
        $e = Core::make("helper/validation/error");

        if (empty($args['rNickname']) || trim($args['rNickname']) == '') {
            $e->add(t('%s is required', t('Nickname')));
        }

        if (empty($args['rTitle']) || trim($args['rTitle']) == '') {
            $e->add(t('%s is required', t('Title of the Review')));
        }

        if (empty($args['rating']) || count($args['rating']) != count(StoreReviewRating::getAllRatings())) {
            $e->add(t('You have to fill in all the ratings'));
        }

        return $e;
    }

    public function more_products($orderID)
    {
        $order = StoreOrder::getByID($orderID);
        $reviewedProducts = StoreReview::getReviewedProductIDsByOrderID($orderID);

        $orderItems = $order->getOrderItems();
        $moreProducts = array();

        if (count($reviewedProducts) < count($orderItems)) {
            foreach ($orderItems as $item) {
                if (empty($reviewedProducts) || !in_array($item->getProductID(), $reviewedProducts)) {
                    $moreProducts[] = $item;
                }
            }
        }

        if(!empty($moreProducts)) {
            $this->set('moreProducts', $moreProducts);
        }
    }
}
