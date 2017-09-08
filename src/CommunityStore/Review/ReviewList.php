<?php
namespace Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review;

use Database;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\Review as StoreReview;
use Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Review\ReviewStatus\ReviewStatus as StoreReviewStatus;

class ReviewList extends ItemList
{

    protected $cIDs = array();
    protected $sortBy = "";
    protected $status = "";
    protected $location = "";
    protected $fromDate = "";
    protected $toDate = "";
    protected $limit = 0;

    public function setCID($cID)
    {
        $this->cIDs[] = $cID;
    }

    public function setCIDs($cIDs)
    {
        $this->cIDs = array_merge($this->cIDs, array_values($cIDs));
    }

    public function setSortBy($sort)
    {
        $this->sortBy = $sort;
    }

    public function setSearch($search)
    {
        $this->search = $search;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setFromDate($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d', strtotime('-30 days'));
        }
        $this->fromDate = $date;
    }

    public function setToDate($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d', strtotime('+30 days'));
        }
        $this->toDate = $date;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function createQuery()
    {
        $this->query
        ->select('r.rID', 'AVG(ra.rraValue)')
        ->from('CommunityStoreReviews', 'r')
        ->innerJoin('r', 'CommunityStoreReviewRatings', 'ra', 'r.rID = ra.rID')
        ->groupBy('r.rID');
    }

    public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $paramcount = 0;

        if (!empty($this->status) && trim($this->status) != '') {
            $db = \Database::connection();
            $status = StoreReviewStatus::getByHandle($this->status);
            $matchingReviews = $db->query("SELECT rID FROM CommunityStoreReviewStatusHistories t1
                                            WHERE rsID = ? and
                                                t1.rshDate = (SELECT MAX(t2.rshDate)
                                                             FROM CommunityStoreReviewStatusHistories t2
                                                             WHERE t2.rID = t1.rID)", array($status->getID()));
            $reviewIDs = array();

            while ($value = $matchingReviews->fetchRow()) {
                $reviewIDs[] = $value['rID'];
            }

            if (!empty($reviewIDs)) {
                if ($paramcount > 0) {
                    $this->query->andWhere('r.rID in ('.implode(',', $reviewIDs).')');
                } else {
                    $this->query->where('r.rID in ('.implode(',', $reviewIDs).')');
                }
            } else {
                $this->query->where('1 = 0');
            }
        }

        if (is_array($this->cIDs) && !empty($this->cIDs) && !empty($this->location)) {
            if($this->location == 'category') {
               $this->query->innerJoin('r', 'CommunityStoreProductLocations', 'l', 'r.pID = l.pID and l.cID in (' .  implode(',', $this->cIDs). ')');
            } else {
               $productJoin = true;
               $this->query->innerJoin('r', 'CommunityStoreProducts', 'p', 'r.pID = p.pID and p.cID in (' .  implode(',', $this->cIDs). ')');
            }

        }

        if (isset($this->search)) {
            $this->query->where('rID like ?')->setParameter($paramcount++, '%'. $this->search. '%');
        }

        if (isset($this->fromDate) && !empty($this->fromDate)) {
            $this->query->andWhere('DATE(rDate) >= DATE(?)')->setParameter($paramcount++, $this->fromDate);
        }
        if (isset($this->toDate) && !empty($this->toDate)) {
            $this->query->andWhere('DATE(rDate) <= DATE(?)')->setParameter($paramcount++, $this->toDate);
        }

        switch ($this->sortBy) {
            case "alpha":
                if(!$productJoin) {
                  $this->query->innerJoin('r', 'CommunityStoreProducts', 'p', 'r.pID = p.pID');
                }
                $this->query->orderBy('p.pName', 'asc');
                $this->query->orderBy('r.rNickname', 'asc');
                break;
            case "date_asc":
                $this->query->orderBy('r.rDate', 'asc');
                break;
            case "date_desc":
                $this->query->orderBy('r.rDate', 'desc');
                break;
            case "rating_asc":
                $this->query->orderBy('AVG(ra.rraValue)', 'asc');
                break;
            case "rating_desc":
                $this->query->orderBy('AVG(ra.rraValue)', 'desc');
                break;
            default:
                $this->query->orderBy('r.rID', 'desc');
                break;
        }

        return $this->query;
    }

    public function getResult($queryRow)
    {
        return StoreReview::getByID($queryRow['rID']);
    }

    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $values = $query->execute()->fetchAll();
            $count = count($values);

            $query->resetQueryParts(array('groupBy', 'orderBy', 'having', 'join', 'where', 'from'))->from('DUAL')->select($count . ' c ');
        });
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->select('count(distinct r.rID)')->setMaxResults(1)->execute()->fetchColumn();
    }
}
