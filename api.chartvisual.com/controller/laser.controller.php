<?php
global $controllerID,$controllerObject,$controllerFunction,$currency_pairs_supported;

/** ================
 *  Laser controller
 *  ================
 *
 *  api.chartvisual.com/laser/$currency_pair/$currency_time_frame
 *
 */




switch($controllerFunction)
{



    default:

        $currency_pair = $controllerFunction;
        $currency_time_frame = $controllerID;

        /**
         *  Error check to make sure the params are supported for time frame and currency pair
         */

        if(!in_array(strtolower($currency_pair),$currency_pairs_supported))
        {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array('message'=>'Currency pair ' . $currency_pair . ' not supported.')
            ));
        }

        if(!in_array(strtolower($currency_time_frame),$currency_time_frames_supported))
        {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array('message'=>'Time frame ' . $currency_time_frame . ' not supported.')
            ));
        }


        if(isset($laser_instance)) unset($laser_instance);
        $laser_instance = new Laser;
        $laser_data = $laser_instance->get_laser_data($currency_pair,$currency_time_frame);

        if($laser_data)
        {
            api_response(array(
                'code'=> RESPONSE_SUCCESS,
                'data'=> $laser_data
            ));

        } else {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array('message'=>'Unable to retrieve laser indicator data in the database for ' . $currency_pair .' - ' .  $currency_time_frame . '.')
            ));
        }

        break;

}
