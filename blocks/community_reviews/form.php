<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="row">

    <div class="col-xs-6">

        <fieldset>
            <legend><?= t('Reviews') ?></legend>

            <div class="form-group">
                <?= $form->label('filter', t('List Reviews of Product(s)')); ?>
                <?= $form->select('filter', array(
                    'all' => '** ' . t("All") . ' **',
                    'current' => t('Under current product'),
                    'page' => t('Under a specified product'),
                    'current_children' => t('Under current location'),
                    'page_children' => t('Under a specified location')
                ), $filter); ?>
            </div>

            <div class="form-group" id="pageselector">
                <div
                    class="form-group" <?= ($filter == 'page' || $filter == 'page_children' ? '' : 'style="display: none"'); ?> >
                    <?php
                    $ps = Core::make('helper/form/page_selector');
                    echo $ps->selectPage('filterCID', ($filterCID > 0 ? $filterCID : false)); ?>
                </div>
            </div>

            <div class="form-group">
                <?= $form->label('sortOrder', t('Sort Order')); ?>
                <?= $form->select('sortOrder', array('alpha' => t("Alphabetical"), 'date_asc' => t('Date Ascending'), 'date_desc' => t('Date Descending'), 'rating_asc' => t('Rating Ascending'), 'rating_desc' => t('Rating Descending')), $sortOrder); ?>
            </div>
        </fieldset>

        <fieldset>
            <legend><?= t('Review Form') ?></legend>

            <div class="form-group">
                <?= $form->checkbox('showForm', 1, $showForm); ?>
                <?= $form->label('showForm', t('Show Form')); ?>
                <div class="well well-sm">
                  <small>
                    <?= t('Review Form will only be displayed when the PageID is linked to a product.') ?>
                    <?= t('Otherwise the customers will not be able to add a review.'); ?>
                  </small>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="col-xs-6">
        <fieldset>
            <legend><?= t('Pagination and Display Options') ?></legend>

            <div class="form-group">
                <?= $form->label('title', t('Title')); ?>
                <?= $form->text('title', $title); ?>
            </div>

            <div class="form-group">
                <?= $form->label('maxReviews', t('Number of Reviews to Display')); ?>
                <?= $form->number('maxReviews', $maxReviews, array('min'=>'0', 'step'=>'1','placeholder'=>t('leave blank or 0 to list all matching reviews'))); ?>
            </div>

            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showPagination', 1, $showPagination); ?>
                    <?= t('Display pagination interface if more reviews are available than are displayed.') ?>
                </label>
            </div>

            <div class="form-group">
                <?= $form->label('reviewsPerRow', t('Reviews per Row')); ?>
                <?= $form->select('reviewsPerRow', array(1 => 1, 2 => 2, 3 => 3, 4 => 4), $reviewsPerRow ? $reviewsPerRow : 1); ?>
            </div>

        </fieldset>
    </div>
</div>

<script>
    $(function(){
        $('#filter').change(function () {
            if ($(this).val() == 'page' || $(this).val() == 'page_children') {
                $('#pageselector>div').show();
            } else {
                $('#pageselector>div').hide();
            }
        });
    });
</script>
