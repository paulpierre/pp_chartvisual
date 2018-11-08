<?php
global $controllerID,$controllerObject,$controllerFunction,$currency_pairs_supported;

/** ======================================
 *  Currency Strength Indicator controller
 *  ======================================
 *
 *  api.chartvisual.com/csi/$currency_pair/$currency_time_frame
 *
 */




switch($controllerFunction)
{



    default:

        $currency_pair = $controllerFunction;
        $currency_time_frame = $controllerID;
        $data_size = $controllerData;


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


        if(isset($csi_instance)) unset($csi_instance);
        $csi_instance = new CSI;
        $csi_data = $csi_instance->get_csi_data($currency_pair,$currency_time_frame,$data_size);

        if($csi_data)
        {
            api_response(array(
                'code'=> RESPONSE_SUCCESS,
                'data'=> $csi_data
            ));

        } else {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array('message'=>'Unable to retrieve currency strength indicator data in the database for ' . $currency_pair .' - ' .  $currency_time_frame . '.')
            ));
        }

        break;

}
