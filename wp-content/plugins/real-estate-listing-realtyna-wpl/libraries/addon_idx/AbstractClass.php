<?php

/**
 * Class wpl_abstract
 * First of all we have our base class that specifies the skeleton for the delete algorithm
 */

abstract class AbstractClass
{
    final public function build($secret)
    {
        $this->idx_user($secret);
        $this->provider($secret);
        $this->payment($secret);
        $this->configuration($secret);
        $this->service_log($secret);
        $this->listings();
    }

    abstract public function idx_user($secret);
    abstract public function provider($secret);
    abstract public function payment($secret);
    abstract public function configuration($secret);
    abstract public function service_log($secret);
    abstract public function listings();
}