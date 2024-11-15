<?php

use App\Services\Service;

use DB;
use Config;
use Carbon\Carbon;

use App\Models\Character\Character;
use App\Models\Item\Item;
use App\Models\Shop\Shop;
use App\Models\Shop\ShopStock;
use App\Models\Shop\UserItemDonation;
use App\Models\Item\ItemLog;
use App\Models\Shop\ShopLog;
use App\Models\User\UserItem;

class ShopManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Shop Manager
    |--------------------------------------------------------------------------
    |
    | Handles purchasing of items from shops.
    |
    */

    /**
     * Buys an item from a shop.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return App\Models\Shop\Shop|bool
     */
    public function buyStock($data, $user) {
        DB::beginTransaction();

        try {
            $quantity = ceil($data['quantity']);
            if(!$quantity || $quantity == 0) throw new \Exception("Invalid quantity selected.");

            // Check that the shop exists and is open
            $shop = Shop::where('id', $data['shop_id'])->where('is_active', 1)->first();
            if (!$shop) {
                throw new \Exception('Invalid shop selected.');
            }

            // Check that the stock exists and belongs to the shop
            $shopStock = ShopStock::where('id', $data['stock_id'])->where('shop_id', $data['shop_id'])->with('currency')->first();
            if (!$shopStock) {
                throw new \Exception('Invalid item selected.');
            }

            // Check if the item has a quantity, and if it does, check there is enough stock remaining
            if ($shopStock->is_limited_stock && $shopStock->quantity < $quantity) {
                throw new \Exception('There is insufficient stock to fulfill your request.');
            }

            // Check if the user can only buy a limited number of this item, and if it does, check that the user hasn't hit the limit
            if ($shopStock->purchase_limit && $this->checkPurchaseLimitReached($shopStock, $user)) {
                throw new \Exception('You have already purchased the maximum amount of this item you can buy.');
            }

            if (isset($data['use_coupon'])) {
                // check if the the stock is limited stock
                if ($shopStock->is_limited_stock && !Settings::get('limited_stock_coupon_settings')) {
                    throw new \Exception('Sorry! You can\'t use coupons on limited stock items');
                }

            if ($shopStock->purchase_limit && $quantity > $shopStock->purchase_limit) {
                throw new \Exception('The quantity specified exceeds the amount of this item you can buy.');
            }

            $total_cost = $shopStock->cost * $quantity;

            $character = null;
            if ($data['bank'] == 'character') {
                // Check if the user is using a character to pay
                // - stock must be purchaseable with characters
                // - currency must be character-held
                // - character has enough currency
                if (!$shopStock->use_character_bank || !$shopStock->currency->is_character_owned) {
                    throw new \Exception("You cannot use a character's bank to pay for this item.");
                }
                if (!$data['slug']) {
                    throw new \Exception('Please enter a character code.');
                }
                $character = Character::where('slug', $data['slug'])->first();
                if (!$character) {
                    throw new \Exception('Please enter a valid character code.');
                }
                if ($character->user_id != $user->id) {
                    throw new \Exception('That character does not belong to you.');
                }
                if (!(new CurrencyManager)->debitCurrency($character, null, 'Shop Purchase', 'Purchased '.$shopStock->item->name.' from '.$shop->name, $shopStock->currency, $total_cost)) {
                    throw new \Exception('Not enough currency to make this purchase.');
                }
            } else {
                // If the user is paying by themselves
                // - stock must be purchaseable by users
                // - currency must be user-held
                // - user has enough currency
                if (!$shopStock->use_user_bank || !$shopStock->currency->is_user_owned) {
                    throw new \Exception('You cannot use your user bank to pay for this item.');
                }
                if ($shopStock->cost > 0 && !(new CurrencyManager)->debitCurrency($user, null, 'Shop Purchase', 'Purchased '.$shopStock->item->name.' from '.$shop->name, $shopStock->currency, $total_cost)) {
                    throw new \Exception('Not enough currency to make this purchase.');
                }
            }

            // If the item has a limited quantity, decrease the quantity
            if($shopStock->is_limited_stock)
            {
                $shopStock->quantity -= $quantity;
                $shopStock->save();
            }

            // Add a purchase log
            $shopLog = ShopLog::create([
                'shop_id' => $shop->id,
                'character_id' => $character ? $character->id : null,
                'user_id' => $user->id,
                'currency_id' => $shopStock->currency->id,
                'cost' => $total_cost,
                'item_id' => $shopStock->item_id,
                'quantity' => $quantity
            ]);

            // Give the user the item, noting down 1. whose currency was used (user or character) 2. who purchased it 3. which shop it was purchased from
            if(!(new InventoryManager)->creditItem(null, $user, 'Shop Purchase', [
                'data' => $shopLog->itemData,
                'notes' => 'Purchased ' . format_date($shopLog->created_at)
            ], $shopStock->item, $quantity)) throw new \Exception("Failed to purchase item.");

            return $this->commitReturn($shop);
        } 
    }
    catch(\Exception $e) {
        $this->setError('error', $e->getMessage());
    }

        return $this->rollbackReturn(false);
    }

    /**
     * Checks if the purchase limit for an item from a shop has been reached.
     *
     * @param ShopStock             $shopStock
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function checkPurchaseLimitReached($shopStock, $user) {
        if ($shopStock->purchase_limit > 0) {
            return $this->checkUserPurchases($shopStock, $user) >= $shopStock->purchase_limit;
        }

        return false;
    }

    /**
     * Checks how many times a user has purchased a shop item.
     *
     * @param ShopStock             $shopStock
     * @param \App\Models\User\User $user
     *
     * @return int
     */
    public function checkUserPurchases($shopStock, $user) {
        $date = $shopStock->purchaseLimitDate;
        $shopQuery = ShopLog::where('shop_id', $shopStock->shop_id)->where('cost', $shopStock->cost)->where('item_id', $shopStock->item_id)->where('user_id', $user->id);
        $shopQuery = isset($date) ? $shopQuery->where('created_at', '>=', date('Y-m-d H:i:s', $date)) : $shopQuery;

        return $shopQuery->sum('quantity');
    }

    /**
     * Gets the purchase limit for a user for a shop item.
     */
    public function getStockPurchaseLimit($shopStock, $user) {
        $limit = config('lorekeeper.settings.default_purchase_limit');
        if ($shopStock->purchase_limit > 0) {
            $user_purchase_limit = $shopStock->purchase_limit - $this->checkUserPurchases($shopStock, $user);
            if ($user_purchase_limit < $limit) {
                $limit = $user_purchase_limit;
            }
        }
        if ($shopStock->is_limited_stock) {
            if ($shopStock->quantity < $limit) {
                $limit = $shopStock->quantity;
            }
        }

        return $limit;
    }

    /**
     * Collects an item from the donation shop.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return bool|App\Models\Shop\Shop
     */
    public function collectDonation($data, $user)
    {
        DB::beginTransaction();

        try {
            // Check that the stock exists and belongs to the shop
            $stock = UserItemDonation::where('id', $data['stock_id'])->first();
            if(!$stock) throw new \Exception("Invalid item selected.");

            // Check that the user hasn't collected from the shop too recently
            if($user->donationShopCooldown) throw new \Exception("You've collected an item too recently. Please try again later.");

            // Check if the item has a quantity, and if it does, check there is enough stock remaining
            if($stock->stock == 0) throw new \Exception("This item is out of stock.");

            // Decrease the quantity
            $stock->stock -= 1;
            $stock->save();

            // Give the user the item
            if(!(new InventoryManager)->creditItem(null, $user, 'Collected from Donation Shop', [
                'data' => isset($stock->stack->data['data']) ? $stock->stack->data['data'] : null,
                'notes' => isset($stock->stack->data['notes']) ? $stock->stack->data['notes'] : null,
            ], $stock->item, 1)) throw new \Exception("Failed to collect item.");

            return $this->commitReturn($stock);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Clears out expired donations from the shop, if relevant.
     *
     * @return bool
     */
    public function cleanDonations()
    {
        $count = UserItemDonation::expired()->count();
        if($count) {
            DB::beginTransaction();

            try {
                // Fetch the logs for all expired items.
                // This is necessary because the quantity is needed
                $expiredLogs = ItemLog::where('log_type', 'Donated by User')->where('created_at', '<', Carbon::now()->subMonths(Config::get('lorekeeper.settings.donation_shop.expiry')));

                // Process through expired items and remove the expired quantitie(s)
                foreach(UserItemDonation::expired()->get() as $expired) {
                    $quantityExpired = $expiredLogs->where('stack_id', $expired->stack_id)->sum('quantity');
                    $expired->update(['stock', ($expired->stock -= $quantityExpired > 0 ? $expired->stock -= $quantityExpired : 0)]);
                    unset($quantityExpired);
                }

                return $this->commitReturn(true);
            } catch(\Exception $e) {
                $this->setError('error', $e->getMessage());
            }
            return $this->rollbackReturn(false);
        }
    }
     /*
     * Gets how many of a shop item a user owns.
     */
    public function getUserOwned($stock, $user) {
        switch (strtolower($stock->stock_type)) {
            case 'item':
                return $user->items()->where('item_id', $stock->item_id)->count();
            case 'pet':
                return $user->pets()->where('pet_id', $stock->item_id)->count();
            break;
        }
    }
}
