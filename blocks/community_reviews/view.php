<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<?php $dh = Core::make('helper/date'); ?>

<div class="store-reviews">
  <div class="row">
    <div class="<?= (!$userShowForm ? 'col-sm-12' : 'col-sm-7'); ?> col-xs-12">
      <h3><?= (!empty($title) && trim($title) != '' ? $title : t('Reviews')); ?></h3>
      <?php if(!empty($reviews)) { ?>
        <?php
        $columnClass = 'col-md-12';

        if ($reviewsPerRow == 2) {
            $columnClass = 'col-md-6';
        }

        if ($reviewsPerRow == 3) {
            $columnClass = 'col-md-4';
        }

        if ($reviewsPerRow == 4) {
            $columnClass = 'col-md-3';
        }
        ?>
          <?php $reviewCount = 0; ?>
          <?php foreach($reviews as $review) { ?>
            <?php if($reviewCount % $reviewsPerRow == 0) { ?>
              <div class="row">
            <?php } ?>
            <div class="<?= $columnClass; ?>">
              <div class="store-review">
                <div class="store-review-nickname">
                  <?= $review->getNickname(); ?>
                  <span class="store-review-date">(<?= t('Posted on: ') ?> <?= $dh->formatCustom('Y-m-d', $review->getDate()); ?>)</span>
                </div>
                <?php if($showProduct) { ?>
                  <div class="store-review-product">
                    <?php $productPage = Page::getByID($review->getProduct()->getPageID()); ?>
                    <?= t('For: '); ?>
                    <a href="<?= $productPage->getCollectionLink(); ?>"><?= $review->getProduct()->getName(); ?></a>
                  </div>
                <?php } ?>
                <div class="store-review-title">
                  <?= $review->getTitle(); ?>
                </div>
                <div class="store-review-ratings">
                  <?php $reviewRatings = $review->getRatingsList(); ?>
                  <?php if(!empty($reviewRatings)) { ?>
                    <table class="store-review-ratings-table">
                      <?php foreach($reviewRatings as $name => $value) { ?>
                        <tr>
                          <td><span class="store-review-ratings-title"><?= t($name); ?></span></td>
                          <td>
                            <div class="store-rating-box-small">
                              <div class="store-rating" style="width: <?= $value; ?>%;"></div>
                            </div>
                          </td>
                        </tr>
                      <?php } ?>
                    </table>
                  <?php } ?>
                </div>
                <div class="store-review-comment">
                  <?= $review->getComment(); ?>
                </div>
              </div>
            </div>
            <?php $reviewCount++; ?>
            <?php if($reviewCount % $reviewsPerRow == 0 || $reviewCount == count($reviews)) { ?>
              </div>
            <?php } ?>
          <?php } ?>
          
        <?php } else { ?>
          <div class="alert alert-info">
            <?= t('No reviews found...'); ?>
          </div>
        <?php } ?>
        <?php
        if($showPagination){
            if ($paginator->getTotalPages() > 1) {
                echo $pagination;
            }
        }
        ?>
    </div>
    <?php if($userShowForm) { ?>
      <div class="col-sm-5 col-xs-12">
          <h3><?= $formTitle ?></h3>
          <?php if($success) { ?>
            <div class="alert alert-success">
              <?= $success; ?>
            </div>
          <?php } else { ?>
            <form method="post" action="<?= $this->action('add_review'); ?>">
              <?= $form->hidden('pID', $product->getID()); ?>
              <?= $form->hidden('cID', $user->getUserID()); ?>
              <?php if($error) { ?>
                <div class="alert alert-danger">
                  <ul>
                    <?php foreach($error as $e) { ?>
                      <li><?= $e; ?></li>
                    <?php } ?>
                  </ul>
                </div>
              <?php } ?>
              <div class="form-group">
                <?= $form->label('rNickname', t('Nickname')); ?>
                <?= $form->text('rNickname', $rNickname); ?>
              </div>
              <div class="form-group">
                <?= $form->label('rTitle', t('Title of Review')); ?>
                <?= $form->text('rTitle', $rTitle); ?>
              </div>
              <?php if($ratings) { ?>
                  <?php foreach($ratings as $rating) { ?>
                    <div class="form-group">
                      <?= $form->label('rating[]', t($rating->getName())); ?>
                      <?= $rating->getFormHtml(); ?>
                    </div>
                  <?php } ?>
              <?php } ?>
              <div class="form-group">
                <?= $form->label('rComment', t('Review')); ?>
                <?= $form->textarea('rComment', $rComment, array('rows' => 10)); ?>
              </div>
              <?php if($captcha) { ?>
                <div class="form-group">
                  <?= $form->label('catpcha', t('Please type the letters an numers shown in the image. Click the image to see another Captha.')); ?>
                  <?php $captcha->showInput(); ?>
                  <?php $captcha->display(); ?>
                </div>
              <?php } ?>
              <div class="form-group">
                <?= $form->submit('rSubmit', $formSubmitText, array('class' => 'btn btn-primary')); ?>
              </div>
            </form>
          <?php } ?>
      </div>
    <?php } ?>
  </div>
</div>
