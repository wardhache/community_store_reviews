<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Report;

use Page;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewList as StoreReviewList;

class ReviewReport extends StoreReviewList
{
    public function __construct()
    {
        parent::__construct();
        $this->setLocation('category');
        $this->setCID(1);
        $this->setFromDate();
        $this->setToDate();
    }

    public static function getTotalsByRange($from, $to, $limit = 0)
    {
        $rr = new self();
        $rr->setCIDs(Page::getByID(1)->getCollectionChildrenArray());
        $rr->setFromDate($from);
        $rr->setToDate($to);
        //$rr->setLimit($limit);

        $total = 0;
        $approvedTotal = 0;
        $totalResults = 0;
        $totalResultsApproved = 0;
        foreach($rr->getResults() as $review) {
            $total = $total + $review->getAverageRating();
            $status = $review->getCurrentStatus();
            $totalResults++;
            if($status->getHandle() == 'approved') {
              $approvedTotal = $approvedTotal + $review->getAverageRating();
              $totalResultsApproved++;
            }
        }

        if($totalResults > 0) {
            $total = $total / $totalResults;
        }
        if($totalResultsApproved > 0) {
            $approvedTotal = $approvedTotal / $totalResultsApproved;
        }

        $totals = array(
            "number" => $totalResults,
            "total" => $total,
            "approvedNumber" => $totalResultsApproved,
            "approvedTotal" => $approvedTotal
        );

        return $totals;
    }

    public static function getTotalsByProduct() {
        $rr = new self();
        $rr->setCIDs(Page::getByID(1)->getCollectionChildrenArray());
        $rr->setFromDate("1970-01-01");
        $rr->setToDate();

        $products = array();
        foreach($rr->getResults() as $review) {
            $product = $review->getProduct();
            $productID = $product->getID();
            if(!array_key_exists($productID, $products)) {
                $products[$productID]['ID'] = $product->getID();
                $products[$productID]['name'] = $product->getName();
                $products[$productID]['number'] = 0;
                $products[$productID]['approvedNumber'] = 0;
                $products[$productID]['total'] = 0;
                $products[$productID]['approvedTotal'] = 0;
            }

            $products[$productID]['total'] = $products[$productID]['total'] + $review->getAverageRating();
            $products[$productID]['number']++;
            $status = $review->getCurrentStatus();
            if($status->getHandle() == 'approved') {
              $products[$productID]['approvedTotal'] = $products[$productID]['approvedTotal'] + $review->getAverageRating();
              $products[$productID]['approvedNumber']++;
            }
        }

        if(!empty($products)) {
            foreach($products as $product) {
                $productID = $product['ID'];
                if($product['number'] > 0) {
                    $products[$productID]['total'] = $products[$productID]['total'] / $product['number'];
                }
                if($product['approvedNumber'] > 0) {
                    $products[$productID]['approvedTotal'] = $products[$productID]['approvedTotal'] / $product['approvedNumber'];
                }
            }
        }

        return $products;
    }

    public static function getTodaysReviews()
    {
        $today = date('Y-m-d');

        return self::getTotalsByRange($today, $today, 0);
    }
    public static function getThirtyDays()
    {
        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

        return self::getTotalsByRange($thirtyDaysAgo, $today, 0);
    }
    public static function getYearToDate()
    {
        $today = date('Y-m-d');
        $jan1 = new \DateTime(date("Y")."-01-01");
        $jan1 = $jan1->format("Y-m-d");

        return self::getTotalsByRange($jan1, $today, 0);
    }
    public static function getByMonth($date)
    {
        $from = date('Y-m-01', strtotime($date));
        $to = date('Y-m-t', strtotime($date));

        return self::getTotalsByRange($from, $to, 0);
    }

}
