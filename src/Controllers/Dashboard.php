<?php

namespace SamPoyigi\Telescope\Controllers;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Facades\Template;

/**
 * Telescope Admin Controller
 */
class Dashboard extends AdminController
{
    public $requiredPermissions = 'SamPoyigi.Telescope.Access';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tools', 'telescope');
    }

    public function index()
    {
        Template::setTitle('Telescope Dashboard');
        Template::setHeading('Telescope Dashboard');
    }
}
