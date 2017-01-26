<?php

namespace Concrete\Package\CommunityStoreReviews\Controller\SinglePage\Dashboard\Store\Reports;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Page;
use Loader;
use Package;
use Concrete\Core\Legacy\ItemList;
use \Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewList as StoreReviewList;
use \Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Report\ReviewReport as StoreReviewReport;

class Reviews extends DashboardPageController
{

    public function view() {
        $rr = new StoreReviewReport();
        $this->set('rr',$rr);
        $pkg = Package::getByHandle('community_store');

        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $this->set('defaultFromDate',$thirtyDaysAgo);
        $this->set('defaultToDate',$today);

        $dateFrom = $this->post('dateFrom');
        $dateTo = $this->post('dateTo');
        if(!$dateFrom){ $dateFrom = $thirtyDaysAgo; }
        if(!$dateTo){ $dateTo = $today; }
        $this->set('dateFrom',$dateFrom);
        $this->set('dateTo',$dateTo);

        $reviewsTotal = $rr::getTotalsByRange($dateFrom,$dateTo);
        $this->set('reviewsTotal', $reviewsTotal);

        $productReviews = $rr::getTotalsByProduct();
        $il = new ItemList();
        $il->setItemsPerPage(20);
        $il->setItems($productReviews);
        $results = $il->getPage();
        $this->set('productReviews',$results);
        $this->set('il', $il);

        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('css', 'communityStoreReviewsDashboard');
    }

}
