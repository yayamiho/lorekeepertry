<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\CultivationManager;

use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\Currency\Currency;
use App\Models\Cultivation\CultivationArea;
use App\Models\User\UserItem;
use App\Models\User\UserPlot;
use App\Models\User\UserArea;

class CultivationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Cultivation Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing the cultivation areas, going into them and the cultivating process.
    |
    */

    /**
     * Shows the cultivation index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('cultivation.index', [
            'areas' => CultivationArea::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
            'user' => Auth::user()
        ]);
    }

    /**
     * Shows a cultivation area index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getArea($id)
    {
        $user = Auth::user();
        $area = CultivationArea::find($id);
        if(!$user->areas->contains($area)) abort(404);
        if(!isset($area)) abort(404);

        $userArea = UserArea::where('user_id', $user->id)->where('area_id', $id)->first();
        return view('cultivation.area', [
            'userArea' => $userArea,
            'areas' => CultivationArea::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
            'user' => Auth::user()
        ]);
    }

    /**
     * Get modal for a specific plot.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPlotModal($id, $plot_number)
    {
        $user = Auth::user();
        $area = CultivationArea::find($id);
        if(!isset($area)) abort(404);

        $userArea = UserArea::where('area_id', $id)->where('user_id', $user->id)->first();
        if(!isset($userArea)) abort(404);

        $userPlot = UserPlot::where('user_id', Auth::user()->id)->where('plot_number', $plot_number)->where('user_area_id', $userArea->id)->first();
        $tags = ItemTag::where('tag', 'tool')->where('is_active', 1)->pluck('item_id');
        $userTools = array_unique(UserItem::where('user_id', $user->id)->whereIn('item_id', $tags)->where('count', '>', 0)->with('item')->get()->pluck('item.name', 'id')->toArray());
        $seedTags = ItemTag::where('tag', 'seed')->where('is_active', 1)->pluck('item_id');
        $userSeeds = array_unique(UserItem::where('user_id', $user->id)->whereIn('item_id', $seedTags)->where('count', '>', 0)->with('item')->get()->pluck('item.name', 'id')->toArray());

        return view('cultivation._plot_modal', [
            'plotNumber' => $plot_number,
            'userPlot' => $userPlot,
            'userTools' => $userTools,
            'userArea' => $userArea,
            'userSeeds' => $userSeeds
        ]);
    }

    /**
     * Prepares a plot for usage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postPreparePlot($plot_number, Request $request, CultivationManager $service)
    {
        
        if($service->preparePlot($request['tool_id'], $plot_number, $request["area_id"])) {
            flash('Successfully prepared plot.')->success();
        }
        else {
            dd($service->errors()->getMessages());
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }        
        return redirect()->back();
    }
   
    
    /**
     * Places an item into the plot.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postCultivatePlot($plot_number, Request $request, CultivationManager $service)
    {
        
        if($service->cultivatePlot($request['seed_id'], $plot_number, $request["area_id"])) {
            flash('Successfully cultivated plot.')->success();
        }
        else {
            dd($service->errors()->getMessages());
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }        
        return redirect()->back();
    }
}


