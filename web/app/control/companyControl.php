<?php


class companyControl extends myControl {

    private $company;
    private $recruit;
    private $resume;
    private $email_activate;

    function __construct() {
        parent::__construct();
        $this->email_activate=K('email_activate');
        $this->company = K('company');
        $this->recruit = K('recruit');
        $this->resume = K('resume');
    }


}
