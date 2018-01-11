<?php

class iHRIS_TrainingHistory extends I2CE_Form {
    public function __construct( $factory, $name, $id='0' ) {
        parent::__construct( $factory, $name, $id );
        $now = I2CE_Date::now();
        $this->fields['from_date']->setYearRange( 2006, $now->year() + 0 );
        $this->fields['to_date']->setYearRange( 2000, $now->year() + 5 );
    }
}

?>
