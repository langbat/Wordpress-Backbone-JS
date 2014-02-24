<?php

/*
 * 	set prices for additional packages
 * 	toDo: should be recoded for managing in backend.
 */

$addon_price = array(
    // 'basis' => array(
    // 		'regular' => '0',
    // 		'reduced' => '0'
    // 	),
    // 'top' => array(
    // 		'regular' => '99',
    // 		'reduced' => '39'
    // 	),
    // 'premium' => array(
    // 		'regular' => '399',
    // 		'reduced' => '399'
    // 	),

    'facebook' => array(
        'regular' => '99',
        'reduced' => '69'
    ),
    'newsletter' => array(
        'regular' => '79',
        'reduced' => '49'
    ),
    'blog' => array(
        'regular' => '179',
        'reduced' => '179'
    )
);

// ToDo: make this dynamic, somehow
/*
  maybe use  $et_global['post_fields']

  maybe  get_posts post_type=payment_plan
 */

// Basis:  		$planID = 12	8W: 1072 12W: 1073
// TOP-regular:	   	$planID = 13 	8W: 15	 12W: 16 
// TOP-reduced:	   	$planID = 17 	8W: 18	 12W: 19
// Premium-regular:	$planID = 20 	8W: 21	 12W: 22

$jobhandler = array(// [basis][regular][4weeks]
// jobhandler connects plans with jobtypes and durations	
// 4weeks = 28d // 8w = 56d // 12w =  84d

    'basis' => array(
        'regular' => array(
            'weeks4' => 12,
            'weeks8' => 1072,
            'weeks12' => 1073
        ),
        'reduced' => array(
            'weeks4' => 12,
            'weeks8' => 1072,
            'weeks12' => 1073
        )
    ),
    'top' => array(
        'regular' => array(
            'weeks4' => 13,
            'weeks8' => 15,
            'weeks12' => 16
        ),
        'reduced' => array(
            'weeks4' => 17,
            'weeks8' => 18,
            'weeks12' => 19
        )
    ),
    'premium' => array(
        'regular' => array(
            'weeks4' => 20,
            'weeks8' => 21,
            'weeks12' => 22
        ),
        'reduced' => array(
            'weeks4' => 20,
            'weeks8' => 21,
            'weeks12' => 22
        )
    )
);
?>
