<?php
if (!defined('_PS_VERSION_'))
    exit;

/**
 * Module for adding cron controller product updates.
 *
 */
class MyNostoCron extends Module
{

    /**
     * Constructor.
     *
     * Defines the module.
     */
    public function __construct()
    {
        $this->name = 'mynostocron';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'YourName';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array(
            'min' => '1.5',
            'max' => _PS_VERSION_
        );
        parent::__construct();
        $this->displayName = $this->l('My Nosto Cron');
        $this->description = $this->l('Module for adding Nosto custom cron job');
    }

    /**
     * Module installer.
     *
     * @return bool
     */
    public function install()
    {
        return parent::install();
    }
}