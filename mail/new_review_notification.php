<?php
defined('C5_EXECUTE') or die("Access Denied.");

$dh = Core::make('helper/date');

$subject = t("New Review Notification on %s (%s)", $review->getProduct()->getName(), $dh->formatDateTime($review->getDate()));

/**
 * HTML BODY START
 */
ob_start();

?>
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
    <html>
    <head>
    </head>
    <body>
    <h2><?= t('A review has been submitted') ?></h2>

    <p><strong><?= t("Review") ?>:</strong> <?= $review->getID() ?></p>
    <p><?= t('Date submitted');?>: <?= $dh->formatDateTime($review->getDate())?></p>

    <table border="0" width="100%" style="border-collapse: collapse;">
        <tr>
            <td valign="top" style="margin-top: 15px;">
                <h3><?= t('Details') ?></h3>
                <table border="0" width="100%">
                    <tr>
                        <td><?= t('Nickname'); ?></td>
                        <td><?= $review->getNickname(); ?></td>
                    </tr>
                    <?php if (!empty($ratings)) { ?>
                        <?php foreach ($ratings as $rating) { ?>
                            <tr>
                                <td><?= t($rating->getRating()->getName()); ?></td>
                                <td>
                                    <?php
                                        for ($i = 0; $i < 100; $i += 20) {
                                            if ($rating->getValue() <= $i ) {
                                                echo "&#9734;";
                                            } else {
                                                echo "&#9733;";
                                            }
                                        }
                                    ?>
                                </td>
                            </tr>
                      <?php } ?>
                    <?php } ?>
                    <tr>
                        <td><?= t('Title'); ?></td>
                        <td><?= $review->getTitle(); ?></td>
                    </tr>
                    <tr>
                        <td><?= t('Comment'); ?></td>
                        <td><?= $review->getComment(); ?></td>
                    </tr>
                    <tr>
                        <td><?= t('Status'); ?></td>
                        <td><?= $review->getCurrentStatus()->getName(); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top">
                <p style="margin-top: 30px;">
                    <?= t('Click on the link to view this review'); ?><br />
                    <a href="<?php echo View::url('/dashboard/store/reviews/', 'review', $review->getID()) ?>">
                        <?php echo View::url('/dashboard/store/reviews/', 'review', $review->getID()) ?>
                    </a>
                </p>
            </td>
        </tr>
    </table>
    </body>
    </html>

<?php
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END
 *
 * ======================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>

<?= t("Review #:") ?> <?= $review->getID() ?>
<?= t("A new review has been submitted on your website") ?>
<?php

$body = ob_get_clean(); ?>
