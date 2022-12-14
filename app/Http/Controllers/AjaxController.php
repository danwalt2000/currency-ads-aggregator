<?php
 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Log;
use App\Http\Controllers\DBController;
use App\Http\Controllers\CurrencyController;
 
class AjaxController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */

    public function ajax(Request $request){
        $to_view = (new CurrencyController)->to_view;

        $sellbuy = $request->query('sellbuy');
        if( empty($sellbuy) ) $sellbuy = '';

        $currency = $request->query('currency');
        if( empty($currency) ) $currency = '';
        
        $offset = $request->query('offset');
        if( empty($offset) ) $offset = 0;

        $to_view['ads'] = DBController::getPosts("get", $sellbuy, $currency, '', $offset );
        return view('feed', $to_view);;
    }

    public function ajaxPost(Request $request){
        $input = $request->all();
        $ad = DBController::getPhone($input);
        if( empty($ad->phone) ){
            return $ad;
        }
        return $ad->phone;
    }
}