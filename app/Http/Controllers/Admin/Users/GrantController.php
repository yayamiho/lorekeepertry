<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Auth;
use Settings;
use Config;
use Illuminate\Http\Request;

use App\Models\Character\Character;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterItem;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Pet\Pet;
use App\Models\Submission\Submission;
use App\Models\Trade;
use App\Models\User\User;
use App\Models\Award\Award;
use App\Models\Award\AwardCategories;
use App\Models\Recipe\Recipe;

use App\Models\User\UserItem;
use App\Models\User\UserCurrency;
use App\Models\SitePage;

use App\Services\CurrencyManager;
use App\Services\InventoryManager;
use App\Services\AwardCaseManager;
use App\Services\RecipeService;
use App\Models\Border\Border;
use App\Services\BorderService;

class GrantController extends Controller {
    /**
     * Show the currency grant page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserCurrency() {
        return view('admin.grants.user_currency', [
            'users'          => User::orderBy('id')->pluck('name', 'id'),
            'userCurrencies' => Currency::where('is_user_owned', 1)->orderBy('sort_user', 'DESC')->pluck('name', 'id'),
        ]);
    }

    /**
     * Grants or removes currency from multiple users.
     *
     * @param App\Services\CurrencyManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUserCurrency(Request $request, CurrencyManager $service) {
        $data = $request->only(['names', 'currency_id', 'quantity', 'data']);
        if ($service->grantUserCurrencies($data, Auth::user())) {
            flash('Currency granted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Show the item grant page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItems() {
        return view('admin.grants.items', [
            'users' => User::orderBy('id')->pluck('name', 'id'),
            'items' => Item::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Grants or removes items from multiple users.
     *
     * @param App\Services\InventoryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postItems(Request $request, InventoryManager $service) {
        $data = $request->only(['names', 'item_ids', 'quantities', 'data', 'disallow_transfer', 'notes']);
        if ($service->grantItems($data, Auth::user())) {
            flash('Items granted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Show the award grant page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAwards()
    {
        return view('admin.grants.awards', [
            'userOptions'           => User::orderBy('id')->pluck('name', 'id'),
            'userAwardOptions'      => Award::orderBy('name')->where('is_user_owned',1)->pluck('name', 'id'),
            'characterOptions'      => Character::myo(0)->orderBy('name')->get()->pluck('fullName', 'id'),
            'characterAwardOptions' => Award::orderBy('name')->where('is_character_owned',1)->pluck('name', 'id')
        ]);
    }

    /**
     * Grants or removes awards from multiple users.
     *
     * @param  \Illuminate\Http\Request        $request
     * @param  App\Services\AwardCaseManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAwards(Request $request, AwardCaseManager $service)
    {
        $data = $request->only([
            'names', 'award_ids', 'quantities', 'data', 'disallow_transfer', 'notes',
            'character_names', 'character_award_ids', 'character_quantities',
        ]);
        if($service->grantAwards($data, Auth::user())) {
            flash(ucfirst(__('awards.awards')).' granted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Show the recipe grant page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRecipes()
    {
        return view('admin.grants.recipes', [
            'users' => User::orderBy('id')->pluck('name', 'id'),
            'recipes' => Recipe::orderBy('name')->pluck('name', 'id')
        ]);
    }

     /*
     * Show the pet grant page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPets() 
    {
        return view('admin.grants.pets', [
            'users' => User::orderBy('id')->pluck('name', 'id'),
            'pets'  => Pet::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Grants or removes items from multiple users.
     *
     * @param  \Illuminate\Http\Request        $request
     * @param  App\Services\InventoryManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRecipes(Request $request, RecipeService $service)
    {
        $data = $request->only(['names', 'recipe_ids', 'data']);
        if($service->grantRecipes($data, Auth::user())) {
            flash('Recipes granted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /*
     * Gets all variants of a pet.
     *
     * @param mixed $id
     */
    public function getPetVariants($id) {
        $pet = Pet::find($id);

        return $pet->variants->pluck('variant_name', 'id')->toArray();
    }

    /**
     * Gets all evlutions of a pet.
     *
     * @param mixed $id
     */
    public function getPetEvolutions($id) {
        $pet = Pet::find($id);

        return $pet->evolutions->pluck('evolution_name', 'id')->toArray();
    }

    /**
     * Grants or removes pets from multiple users.
     *
     * @param App\Services\InvenntoryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postPets(Request $request, PetManager $service) {
        $data = $request->only(['names', 'pet_ids', 'quantities', 'data', 'disallow_transfer', 'notes', 'variant', 'evolution']);
        if ($service->grantPets($data, Auth::user())) {
            flash('Pets granted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Show the item search page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItemSearch(Request $request) {
        $item = Item::find($request->only(['item_id']))->first();

        if ($item) {
            // Gather all instances of this item
            $userItems = UserItem::where('item_id', $item->id)->where('count', '>', 0)->get();
            $characterItems = CharacterItem::where('item_id', $item->id)->where('count', '>', 0)->get();

            // Gather the users and characters that own them
            $users = User::whereIn('id', $userItems->pluck('user_id')->toArray())->orderBy('name', 'ASC')->get();
            $characters = Character::whereIn('id', $characterItems->pluck('character_id')->toArray())->orderBy('slug', 'ASC')->get();

            // Gather hold locations
            $designUpdates = CharacterDesignUpdate::whereIn('user_id', $userItems->pluck('user_id')->toArray())->whereNotNull('data')->get();
            $trades = Trade::whereIn('sender_id', $userItems->pluck('user_id')->toArray())->orWhereIn('recipient_id', $userItems->pluck('user_id')->toArray())->get();
            $submissions = Submission::whereIn('user_id', $userItems->pluck('user_id')->toArray())->whereNotNull('data')->get();
        }

        return view('admin.grants.item_search', [
            'item'           => $item ? $item : null,
            'items'          => Item::orderBy('name')->pluck('name', 'id'),
            'userItems'      => $item ? $userItems : null,
            'characterItems' => $item ? $characterItems : null,
            'users'          => $item ? $users : null,
            'characters'     => $item ? $characters : null,
            'designUpdates'  => $item ? $designUpdates : null,
            'trades'         => $item ? $trades : null,
            'submissions'    => $item ? $submissions : null,
        ]);
    }

    /**
     * Show the border grant page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBorders()
    {
        return view('admin.grants.borders', [
            'users' => User::orderBy('id')->pluck('name', 'id'),
            'borders' => Border::base()->where('is_default', 0)->where('admin_only', 0)->orderBy('name')->pluck('name', 'id')
        ]);
    }

    /**
     * Grants or removes items from multiple users.
     *
     * @param  \Illuminate\Http\Request        $request
     * @param  App\Services\InventoryManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postBorders(Request $request, BorderService $service)
    {
        $data = $request->only(['names', 'border_ids', 'data']);
        if($service->grantBorders($data, Auth::user())) {
            flash('Borders granted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

}
