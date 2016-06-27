<?php

namespace Drupal\simplenews_import\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class ImportController.
 *
 * @package Drupal\simplenews_import\Controller
 */
class ImportController extends ControllerBase {

    private
            $imported_contacts_count;

    public
            function __construct() {
        $this->imported_contacts_count = 0;
    }

    public
            function import(FormStateInterface $form_state) {
        $file_ids = (array) $form_state->getValue('file');

        foreach ($file_ids as $file_id) {
            $file = $file = File::load($file_id);
            if (is_object($file)) {
                $path      = $file->getFileUri();
                $real_path = Drupal::service('file_system')->realpath($path);
                $this->importSubscriber($real_path, $form_state);
                file_delete($file_id);
            }
        }
        return $this->imported_contacts_count;
    }

    private
            function importSubscriber($real_path, FormStateInterface $form_state) {
        $newsletter_system_name = $form_state->getValue('newsletter_system_name');
        $lang                   = $form_state->getValue('language');
        $mail_confirmation      = intval($form_state->getValue('mail_confirmation'));
        $delimiter              = $form_state->getValue('delimiter');
        $row                    = 1;
        if (($handle                 = fopen($real_path, "r")) !== FALSE) {
            $SubscriptionManager = Drupal::service('simplenews.subscription_manager');
            while (($data                = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                //echo "<p> $num champs à la ligne $row: <br /></p>\n";
                if (filter_var($data[0], FILTER_VALIDATE_EMAIL)) {

                    if ($mail_confirmation == 1) {
                        $SubscriptionManager->subscribe($data[0], $newsletter_system_name, false, "website", $lang);
                    }
                    else {
                        $SubscriptionManager->subscribe($data[0], $newsletter_system_name, true, "website", $lang);
                        $SubscriptionManager->sendConfirmations();
                    }
                    $this->imported_contacts_count++;
                }
                $row++;
            }
            fclose($handle);
        }
    }

}
