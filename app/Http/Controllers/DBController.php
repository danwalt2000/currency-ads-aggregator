<?php
 
namespace App\Http\Controllers;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Psr\Http\Message\RequestInterface;
use Log;
use App\Http\Controllers\GetAdsController;
use App\Http\Controllers\CurrencyController;
use App\Models\Ads;
 
class DBController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */

    public static function getPosts( $get_or_count = "get", $sell_buy = '', $currency = '', $search = '', $offset = 0 ){
        $sort = 'date';
        if(!empty($_GET["sort"]) && str_contains( "date rate popularity", $_GET["sort"]) ){
            $sort = $_GET["sort"];
        } 
        $asc_desc = 'desc';
        if(!empty($_GET["order"]) && str_contains( "asc desc", $_GET["order"]) ){
            $asc_desc = $_GET["order"];
        }
        
        $time_range = 24;
        if(!empty($_GET["date"]) && filter_var($_GET["date"], FILTER_VALIDATE_INT)!== false ){
            $time_range = $_GET["date"];
        }
        $query = '';
        if( !empty($sell_buy) || !empty($currency) ){
            $query = '_' . $currency;
            if($sell_buy != "all"){
                $query = $sell_buy . $query;
            }
        }
        $limit = 20;
        $search_clean = '';
        if( !empty($search) ){
            $search_clean = htmlspecialchars($search);
        }
        $offset = $offset * $limit;

        $cut_by_time = time() - $time_range * 60 * 60;
        return Ads::where("date", ">", $cut_by_time)
                  ->where('type', 'like', "%" . $query . "%")
                  ->where('content', 'like', "%" . $search_clean . "%")
                  ->orderBy($sort, $asc_desc)
                  ->skip($offset)
                  ->take($limit)
                  ->$get_or_count();
    }

    public static function getPhone( $info ){
        $ad = Ads::where('vk_id', $info["postId"])->take(1)->get();
        if( !count($ad) ){
            return;
        }
        $ad = $ad[0]; 

        // ?????????????????? +1 ?? ?????????????? ???????????????? ?????? ???????????????? ????????????
        $phone_or_link = "phone_showed";
        if( $info["phoneOrLink"] == "link" ){
            $phone_or_link = "link_followed";
        }
        $actual_value = $ad->{$phone_or_link};
        if( empty($actual_value) ) $actual_value = 0; 

        // ?????????????????? +1 ?? ???????????????????????? ?????? ???????????? ?????????????????? ???????????? 
        $popularity = $ad->popularity; 
        if( empty($popularity) ) $popularity = 0; 
        $ad->update([
            'popularity'     => $popularity + 1,
            $phone_or_link   => $actual_value + 1
        ]); 

        // ???????? ?????????????????? ??????????????????, ???????????? ???? ??????????????
        $phones = explode(",", $ad->phone);
        return $phones[$info["phoneIndex"]];
    }

    public static function storePosts( $args, $store = ["type" => "create", "compare" => [] ] )
    {
        if( !empty($store["type"]) && $store["type"] == "update" ){
            Ads::where($store["compare"]["key"], '=', $store["compare"]["value"])
                ->update($args);
        } else{
            Ads::create($args);
        }
    }
}