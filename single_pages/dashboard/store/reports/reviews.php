<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStoreReviews\Src\CommunityStore\Report\ReviewReport;
?>
<div class="row">
	<div class="col-xs-12 col-md-4">
		<div class="panel-sale panel panel-default">
			<?php $tr = ReviewReport::getTodaysReviews(); ?>
			<div class="panel-heading">
				<h2 class="panel-title"><?= t("Today's Reviews")?></h2>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-12 stat">
            <div>
              <strong><?= t('Total')?> </strong>
            </div>
						<div>
              <?php if(empty($tr['total'])) { ?>
                <?= t('No reviews found'); ?>
              <?php } else { ?>
								<div class="store-rating-box">
									<div class="store-rating" style="width: <?= $tr['total']; ?>%;"></div>
								</div>
              <?php } ?>
            </div>
					</div>
        </div>
        <div class="row">
					<div class="col-xs-12 col-sm-12 stat">
            <div>
              <strong><?= t('Approved')?> </strong>
            </div>
            <div>
              <?php if(empty($tr['totalApproved'])) { ?>
                <?= t('No approved reviews found'); ?>
              <?php } else { ?>
								<div class="store-rating-box">
									<div class="store-rating" style="width: <?= $tr['totalApproved']; ?>%;"></div>
								</div>
              <?php } ?>
            </div>
					</div>
				</div>
			</div>
		</div>
	</div>
  <div class="col-xs-12 col-md-4">
		<div class="panel-sale panel panel-default">
			<?php $td = ReviewReport::getThirtyDays(); ?>
			<div class="panel-heading">
				<h2 class="panel-title"><?= t("Past 30 Days")?></h2>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-12 stat">
            <div>
              <strong><?= t('Total')?> </strong>
            </div>
						<div>
              <?php if(empty($td['total'])) { ?>
                <?= t('No reviews found'); ?>
              <?php } else { ?>
								<div class="store-rating-box">
									<div class="store-rating" style="width: <?= $td['total']; ?>%;"></div>
								</div>
              <?php } ?>
            </div>
					</div>
        </div>
        <div class="row">
					<div class="col-xs-12 col-sm-12 stat">
            <div>
              <strong><?= t('Approved')?> </strong>
            </div>
            <div>
              <?php if(empty($td['totalApproved'])) { ?>
                <?= t('No approved reviews found'); ?>
              <?php } else { ?>
								<div class="store-rating-box">
									<div class="store-rating" style="width: <?= $td['totalApproved']; ?>%;"></div>
								</div>
              <?php } ?>
            </div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-4">
		<div class="panel-sale panel panel-default">
			<?php $yd = ReviewReport::getYearToDate(); ?>
			<div class="panel-heading">
				<h2 class="panel-title"><?= t("Year To Date")?></h2>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-12 stat">
						<div>
							<strong><?= t('Total')?> </strong>
						</div>
						<div>
							<?php if(empty($yd['total'])) { ?>
								<?= t('No reviews found'); ?>
							<?php } else { ?>
								<div class="store-rating-box">
									<div class="store-rating" style="width: <?= $yd['total']; ?>%;"></div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 stat">
						<div>
							<strong><?= t('Approved')?> </strong>
						</div>
						<div>
							<?php if(empty($yd['totalApproved'])) { ?>
								<?= t('No approved reviews found'); ?>
							<?php } else { ?>
								<div class="store-rating-box">
									<div class="store-rating" style="width: <?= $yd['totalApproved']; ?>%;"></div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<hr>
<div class="well">
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			<h3><?= t("View Reviews by Date")?></h3>
		</div>
		<div class="col-xs-12 col-sm-8 text-right">
			<form action="<?=URL::to('/dashboard/store/reports/reviews')?>" method="post" class="form form-inline order-report-form">
				<div class="form-group">
					<?= Core::make('helper/form/date_time')->date('dateFrom', $dateFrom); ?>
				</div>
				<div class="form-group">
					<?= Core::make('helper/form/date_time')->date('dateTo', $dateTo); ?>
				</div>
				<input type="submit" class="btn btn-primary">
			</form>
		</div>
	</div>
	<hr>
	<h4><?= t("Summary")?></h4>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= t("Total Reviews")?></th>
				<th><?= t("Rating Total Reviews")?></th>
				<th><?= t("Approved Reviews")?></th>
				<th><?= t("Rating Approved Reviews")?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?= $reviewsTotal['number']?> </td>
				<td>
					<?php if($reviewsTotal['number'] > 0) { ?>
						<div class="store-rating-box">
							<div class="store-rating" style="width: <?= $reviewsTotal['total']; ?>%;"></div>
						</div>
					<?php } ?>
				</td>
				<td><?= $reviewsTotal['approvedNumber']?> </td>
				<td>
					<?php if($reviewsTotal['approvedNumber'] > 0) { ?>
						<div class="store-rating-box">
							<div class="store-rating" style="width: <?= $reviewsTotal['approvedTotal']; ?>%;"></div>
						</div>
					<?php } ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<hr />
<div class="row">
	<div class="col-sm-12">
		<h3><?= t("View Reviews by Product")?></h3>
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?= t("Product")?></th>
					<th><?= t("Total Reviews")?></th>
					<th><?= t("Rating Total Reviews")?></th>
					<th><?= t("Approved Reviews")?></th>
					<th><?= t("Rating Approved Reviews")?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($productReviews)) { ?>
					<?php foreach($productReviews as $pr) { ?>
						<tr>
							<td><a href="<?= URL::to('/dashboard/store/products/edit/', $pr['ID']); ?>"><?= $pr['name']; ?></a></td>
							<td><?= $pr['number']?> </td>
							<td>
								<?php if($pr['number'] > 0) { ?>
									<div class="store-rating-box">
										<div class="store-rating" style="width: <?= $pr['total']; ?>%;"></div>
									</div>
								<?php } ?>
							</td>
							<td><?= $pr['approvedNumber']?> </td>
							<td>
								<?php if($pr['approvedNumber'] > 0) { ?>
									<div class="store-rating-box">
										<div class="store-rating" style="width: <?= $pr['approvedTotal']; ?>%;"></div>
									</div>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
			</tbody>
		</table>
		<?= $il->displayPagingV2(); ?>
	</div>
</div>
