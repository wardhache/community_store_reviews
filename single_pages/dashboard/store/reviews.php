<?php
defined('C5_EXECUTE') or die("Access Denied.");
$dh = Core::make('helper/date');
?>

<?php if ($controller->getTask() == 'review') { ?>
    <div class="ccm-dashboard-header-buttons">
      <form action="<?= $this->action('change_review_status'); ?>" method="post">
        <?= $form->hidden('review', $review->getID()); ?>
        <?= $form->hidden('reload', 1); ?>
        <?php $currentStatus = $review->getCurrentStatus(); ?>
        <?php $statusHandle = $currentStatus->getHandle(); ?>
        <?php if($statusHandle == "pending" || $statusHandle == "not_approved") { ?>
          <button type="submit" value="approved" class="btn btn-success" name="status"><?= t('Approve Review'); ?></button>
        <?php } ?>
        <?php if($statusHandle == "pending" || $statusHandle == "approved") { ?>
          <button type="submit" value="not_approved" class="btn btn-danger" name="status"><?= t('Decline Review'); ?></button>
        <?php } ?>
      </form>
    </div>

    <div class="row">
        <div class="col-sm-8">
            <p><strong><?= t('Review placed'); ?>:</strong> <?= $dh->formatDateTime($review->getDate())?></p>
         </div>
        <div class="col-sm-4">
        <?php
        $currentStatus = $review->getCurrentStatus();
        switch($currentStatus->getHandle()) {
            case "pending":
                echo '<p class="alert alert-info text-center"><strong>' . $currentStatus->getName() . '</strong></p>';
                break;
            case "approved":
                echo '<p class="alert alert-success text-center"><strong>' . $currentStatus->getName() . '</strong></p>';
                break;
            case "not_approved":
                echo '<p class="alert alert-danger text-center"><strong>' . $currentStatus->getName() . '</strong></p>';
                break;
        }
        ?>
        </div>
    </div>

    <fieldset>
    <legend><?= t("Review Details")?></legend>

    <div class="row">
        <div class="col-sm-4">
            <h4><?= t("Nickname")?></h4>
            <p>
              <?= $review->getNickname()?>
              <?php $customer = $review->getCustomerInfo(); ?>
              <?php
                if($customer) {
                  $customerName = $customer->getAttribute("billing_first_name"). " " . $customer->getAttribute("billing_last_name");
                  if(trim($customerName) == '') {
                    $customerName = $customer->getUsername();
                  }
                  echo '(' . t('Customer:') . ' <a href="'. URL::to('dashboard/users/search/view/', $customer->getUserID()) . '">' .  $customerName . '</a>)';
                }
              ?>

            </p>

            <h4><?= t("Title")?></h4>
            <p><?= $review->getTitle()?></p>

            <h4><?= t("Review")?></h4>
            <p><?= $review->getComment()?></p>
        </div>

        <div class="col-sm-4">
          <?php $ratings = $review->getRatingsList(); ?>
          <?php
            if(!empty($ratings)) {
              foreach($ratings as $name => $value) {
                ?>
                  <h4><?= t($name); ?></h4>
                  <div class="store-rating-box">
                    <div class="store-rating" style="width: <?= $value; ?>%;"></div>
                  </div>
                <?php
              }
            }
          ?>
        </div>

        <div class="col-sm-4">
              <?php $product = $review->getProduct(); ?>
              <?php if(!empty($product)) { ?>
                <h4><?= $product->getName(); ?></h4>
                <p>
                  <?php
                  $salePrice = $product->getSalePrice();
                  if (isset($salePrice) && $salePrice != "") {
                      echo '<span class="sale-price">' . t("On Sale: ") . $product->getFormattedSalePrice() . '</span>';
                      echo '&nbsp;'.t('was').'&nbsp;';
                      echo '<span class="original-price">' . $product->getFormattedOriginalPrice() . '</span>';
                  } else {
                      echo $product->getFormattedPrice();
                  }
                  ?>
                </p>
                <p>
                  <?php
                  $imgObj = $product->getImageObj();
                  if (is_object($imgObj)) {
                      $thumb = Core::make('helper/image')->getThumbnail($imgObj, 300, 300, true);
                      ?>
                      <div class="store-review-product">
                         <img src="<?= $thumb->src ?>">
                      </div>
                  <?php } ?>
                </p>
                <p>
                  <a href="<?= URL::to('/dashboard/store/products/edit/', $product->getID()); ?>" class="btn btn-primary"><?= t('View Product'); ?></a>
                </p>
              <?php } ?>
        </div>
    </div>
    </fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a class="btn btn-default" href="<?=URL::to('/dashboard/store/reviews/')?>" ><?= t('View All Reviews')?></a>
        </div>
    </div>
<?php } else { ?>
    <div class="ccm-dashboard-header-buttons">
    </div>

    <div class="ccm-dashboard-content-full">
        <form role="form" class="form-inline ccm-search-fields">
            <div class="ccm-search-fields-row">
                <?php if($statuses){?>
                    <ul id="group-filters" class="nav nav-pills">
                        <li><a href="<?= \URL::to('/dashboard/store/reviews/')?>"><?= t('All Statuses')?></a></li>
                        <?php foreach($statuses as $status){ ?>
                            <li><a href="<?= \URL::to('/dashboard/store/reviews/', $status->getHandle())?>"><?= t($status->getName());?></a></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>

            <div class="ccm-search-fields-row ccm-search-fields-submit">
                <div class="form-group">
                    <div class="ccm-search-main-lookup-field">
                        <i class="fa fa-search"></i>
                        <?= $form->search('keywords', $searchRequest['keywords'], array('placeholder' => t('Search Reviews')))?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary pull-right"><?= t('Search')?></button>

            </div>

        </form>

        <table class="ccm-search-results-table">
            <thead>
                <th><a><?= t("Review %s","#")?></a></th>
                <th><a><?= t("Date")?></a></th>
                <th><a><?= t("Product")?></a></th>
                <th><a><?= t("Nickname")?></a></th>
                <th><a><?= t("Avg. Rating")?></a></th>
                <th><a><?= t("Status")?></a></th>
                <th><a><?= t("Actions")?></a></th>
                <th><a><?= t("View")?></a></th>
            </thead>
            <tbody>
                <?php
                    foreach($reviewList as $review){
                ?>
                    <tr class="danger" data-row="<?= $review->getID() ?>">
                        <td data-column="id">
                            <a href="<?=URL::to('/dashboard/store/reviews/review/',$review->getID())?>"><?= $review->getID()?></a>
                        </td>
                        <td><?= $dh->formatDateTime($review->getDate())?></td>
                        <td data-column="product">
                            <?php $product = $review->getProduct(); ?>
                            <?php if(!empty($product)) { ?>
                              <a href="<?= URL::to('/dashboard/store/products/edit/', $product->getID()); ?>"><?= $product->getName(); ?></a>
                            <?php } else { ?>
                              <em><?= t('Not found'); ?></em>
                            <?php } ?>
                        </td>
                        <td><?= $review->getNickname(); ?></td>
                        <td data-column="rating">
                          <div class="store-rating-box">
                            <div class="store-rating" style="width: <?= $review->getAverageRating(); ?>%;"></div>
                          </div>
                        </td>
                        <td data-column="status">
                          <?php $statusHandle = $review->getCurrentStatus()->getHandle(); ?>
                          <?php $statusName = $review->getCurrentStatus()->getName(); ?>
                          <?php
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
                          ?>
                        </td>
                        <td data-column="actions">
                          <?php if($statusHandle == "pending") { ?>
                            <a href="" data-status-handle="approved" data-review="<?= $review->getID()?>" class="btn btn-success store-dashboard-review-status" title="<?= t('Approve'); ?>"><i class="fa fa-check"></i></a>
                            <a href="" data-status-handle="not_approved" data-review="<?= $review->getID()?>" class="btn btn-danger store-dashboard-review-status" title="<?= t('Decline'); ?>"><i class="fa fa-times"></i></a>
                          <?php } ?>
                        </td>
                        <td data-column="view"><a class="btn btn-primary" href="<?=URL::to('/dashboard/store/reviews/review/',$review->getID())?>"><?= t("View")?></a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php if ($paginator->getTotalPages() > 1) { ?>
        <?= $pagination ?>
    <?php } ?>

    <script>
      $(function() {
        $('.store-dashboard-review-status').on('click', function(e) {
          e.preventDefault();
          var statusHandle = $(this).data('status-handle');
          var reviewID = $(this).data('review');

          if(statusHandle != null && reviewID != null) {
            $.ajax({
               type: 'post',
               url: '<?= $this->action('change_review_status'); ?>',
               data: {
                  review: reviewID,
                  status: statusHandle
               },
               //dataType: 'json',
               success: function(data) {
                  $('table.ccm-search-results-table tbody tr[data-row=' + reviewID + '] td[data-column=status]').empty().html(data);
                  $('table.ccm-search-results-table tbody tr[data-row=' + reviewID + '] td[data-column=actions]').empty();
               },
            });
          } else {
            alert('<?= t("The status of the review could not be changed."); ?>');
          }
        })
      });
    </script>
<?php } ?>
