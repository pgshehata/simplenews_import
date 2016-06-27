<?php

namespace Drupal\simplenews_import\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simplenews_import\Controller\ImportController;

/**
 * Class ImportForm.
 *
 * @package Drupal\simplenews_import\Form
 */
class ImportForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    protected
            function getEditableConfigNames() {
        return [
            'simplenews_import.import',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public
            function getFormId() {
        return 'import_form';
    }

    /**
     * {@inheritdoc}
     */
    public
            function buildForm(array $form, FormStateInterface $form_state) {
        $form['import_form']                           = array(
            '#type'        => 'fieldset',
            '#title'       => $this->t('subscriber import'),
            '#collapsible' => FALSE,
        );
        $form['import_form']['file']                   = array(
            '#type'              => 'managed_file',
            '#title'             => $this->t('File'),
            '#description'       => $this->t('Upload a CSV (comma separated) file.Please put the mail field in the first column.'),
            '#upload_validators' => array(
                'file_validate_extensions' => array('csv'),
            ),
            '#required'          => TRUE,
        );
        $form['import_form']['delimiter']              = array(
            '#type'          => 'textfield',
            '#title'         => $this->t('Delimiter'),
            '#description'   => $this->t('Characters such as commas separate each field'),
            '#default_value' => ',',
            '#size'          => 5,
            '#maxlength'     => 1,
        );
        $form['import_form']['newsletter_system_name'] = array(
            '#type'        => 'select',
            '#title'       => $this->t('Newsletter'),
            '#description' => $this->t('Select newsletter to add your contacts to.'),
            '#options'     => $this->getNewsletterOptions()
        );
        $form['import_form']['language']               = array(
            '#type'    => 'select',
            '#title'   => $this->t('Language'),
            '#options' => $this->getLanguageOptions()
        );
        $form['import_form']['mail_confirmation']      = array(
            '#type'        => 'select',
            '#title'       => $this->t('Email Confirmation'),
            '#description' => $this->t('Select (yes) if you want to send a request mail to every contact.'),
            '#options'     => array('1' => $this->t('No'), '2' => $this->t('Yes'))
        );
        $form['actions']['#type']                      = 'actions';
        $form['actions']['submit']                     = array(
            '#type'        => 'submit',
            '#value'       => $this->t('Import'),
            '#button_type' => 'primary',
        );

        // By default, render the form using theme_system_config_form().
        $form['#theme'] = 'system_config_form';

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public
            function validateForm(array &$form, FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public
            function submitForm(array &$form, FormStateInterface $form_state) {
        $ImportController = new ImportController;
        $imported_count   = $ImportController->import($form_state);
        drupal_set_message($imported_count . " " . $this->t("mail(s) has been successfully imported."));
    }

    private
            function getNewsletterOptions() {
        $newsletters         = simplenews_newsletter_get_all();
        $newsletters_options = array();
        if (is_array($newsletters)) {
            foreach ($newsletters as $key => $value) {
                $newsletters_options[$key] = $newsletters[$key]->name;
            }
        }
        return $newsletters_options;
    }

    private
            function getLanguageOptions() {
        $languages         = Drupal::languageManager()->getLanguages();
        $languages_options = array();
        if (is_array($languages)) {
            foreach ($languages as $key => $value) {
                $languages_options[$key] = $languages[$key]->getName();
            }
        }
        return $languages_options;
    }

}
