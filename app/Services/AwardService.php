<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Award\AwardCategory;
use App\Models\Award\AwardProgression;
use App\Models\Award\AwardReward;
use App\Models\Award\Award;

class AwardService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Award Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of award categories and awards.
    |
    */

    /**********************************************************************************************

        AWARD CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Award\AwardCategory|bool
     */
    public function createAwardCategory($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateCategoryData($data);

            isset($data['character_limit']) && $data['character_limit'] ? $data['character_limit'] : $data['character_limit'] = 0;

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $category = AwardCategory::create($data);

            if ($image) $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param  \App\Models\Award\AwardCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Award\AwardCategory|bool
     */
    public function updateAwardCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(AwardCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateCategoryData($data, $category);

            isset($data['character_limit']) && $data['character_limit'] ? $data['character_limit'] : $data['character_limit'] = 0;

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $category->update($data);

            if ($category) $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handle category data.
     *
     * @param  array                               $data
     * @param  \App\Models\Award\AwardCategory|null  $category
     * @return array
     */
    private function populateCategoryData($data, $category = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);

        if(isset($data['remove_image']))
        {
            if($category && $category->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Delete a category.
     *
     * @param  \App\Models\Award\AwardCategory  $category
     * @return bool
     */
    public function deleteAwardCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if(Award::where('award_category_id', $category->id)->exists()) throw new \Exception("An ".__('awards.award')." with this category exists. Please change its category first.");

            if($category->has_image) $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            $category->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortAwardCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                AwardCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        AWARDS

    **********************************************************************************************/

    /**
     * Creates a new award.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Award\Award
     */
    public function createAward($data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['award_category_id']) && $data['award_category_id'] == 'none') $data['award_category_id'] = null;

            if((isset($data['award_category_id']) && $data['award_category_id']) && !AwardCategory::where('id', $data['award_category_id'])->exists()) throw new \Exception("The selected ".__('awards.award')." category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $award = Award::create($data);

            // Make the image directory if it doesn't exist
            if(!file_exists($award->imagePath))
            {
                // Create the directory.
                if (!mkdir($award->imagePath, 0755, true)) {
                    $this->setError('error', 'Failed to create image directory.');
                    return false;
                }
                chmod($award->imagePath, 0755);
            }

            $award->update([
                'data' => json_encode([
                    'rarity' => isset($data['rarity']) && $data['rarity'] ? $data['rarity'] : null,
                    'release' => isset($data['release']) && $data['release'] ? $data['release'] : null,
                    'prompts' => isset($data['prompts']) && $data['prompts'] ? $data['prompts'] : null,
                    'credits' => isset($data['credits']) && $data['credits'] ? $data['credits'] : null,
                    ]) // rarity, availability info (original source, drop locations), credits
            ]);


            if ($image) {
                $award->extension = $image->getClientOriginalExtension();
                $award->update();
                $this->handleImage($image, $award->imagePath, $award->imageFileName, null);
            }

            return $this->commitReturn($award);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an award.
     *
     * @param  \App\Models\Award\Award  $award
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Award\Award
     */
    public function updateAward($award, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['award_category_id']) && $data['award_category_id'] == 'none') $data['award_category_id'] = null;

            // More specific validation
            if(Award::where('name', $data['name'])->where('id', '!=', $award->id)->exists()) throw new \Exception("The name has already been taken.");
            if((isset($data['award_category_id']) && $data['award_category_id']) && !AwardCategory::where('id', $data['award_category_id'])->exists()) throw new \Exception("The selected ".__('awards.award')." category is invalid.");

            $data = $this->populateData($data, $award);

            $image = null;

            if (isset($data['image']) && $data['image']) {
                if (isset($award->extension) && $award->extension && $award->has_image) {
                    $old = $award->imageFileName;
                } else {
                    $old = null;
                }
                $image = $data['image'];
                unset($data['image']);
            }

            // Make the image directory if it doesn't exist
            if(!file_exists($award->imagePath))
            {
                // Create the directory.
                if (!mkdir($award->imagePath, 0755, true)) {
                    $this->setError('error', 'Failed to create image directory.');
                    return false;
                }
                chmod($award->imagePath, 0755);
            }

            if ($image) {
                $award->extension = $image->getClientOriginalExtension();
                $award->has_image = 1;
                $award->update();
                $this->handleImage($image, $award->imagePath, $award->imageFileName, $old);
                $award->update();
            }

            $award->update($data);

            $award->update([
                'data' => json_encode([
                    'rarity' => isset($data['rarity']) && $data['rarity'] ? $data['rarity'] : null,
                    'release' => isset($data['release']) && $data['release'] ? $data['release'] : null,
                    'prompts' => isset($data['prompts']) && $data['prompts'] ? $data['prompts'] : null,
                    'credits' => isset($data['credits']) && $data['credits'] ? $data['credits'] : null,
                    ]) // rarity, availability info (original source, drop locations)
            ]);

            $this->populateProgression($data, $award);
            $this->populateRewards($data, $award);

            return $this->commitReturn($award);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an award.
     *
     * @param  array                  $data
     * @param  \App\Models\Award\Award  $award
     * @return array
     */
    private function populateData($data, $award = null)
    {

        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        else $data['parsed_description'] = null;

        $data['allow_transfer'] = ((isset($data['allow_transfer']) && $data['allow_transfer']) ? 1 : 0);
        $data['is_released'] = ((isset($data['is_released']) && $data['is_released']) ? 1 : 0);
        $data['is_featured'] = ((isset($data['is_featured']) && $data['is_featured']) ? 1 : 0);
        $data['is_character_owned'] = ((isset($data['is_character_owned']) && $data['is_character_owned']) ? 1 : 0);
        $data['is_user_owned'] = ((isset($data['is_user_owned']) && $data['is_user_owned']) ? 1 : 0);
        $data['allow_reclaim'] = ((isset($data['allow_reclaim']) && $data['allow_reclaim']) ? 1 : 0);

        $data['credits'] = [];
        if(isset($data['credit-name']))
            foreach($data['credit-name'] as $key => $name) {
                $data['credits'][] = [
                    'name'  => $name,
                    'url'   => $data['credit-url'][$key],
                    'id'    => (int)$data['credit-id'][$key],
                    'role'  => $data['credit-role'][$key],
                ];
            }

        unset($data['credit-name']);
        unset($data['credit-url']);
        unset($data['credit-id']);
        unset($data['credit-role']);

        if(isset($data['remove_image']))
        {
            if($award && $award->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $data['extension'] = null;
                $this->deleteImage($award->imagePath, $award->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Populates the progressions of an award.
     */
    private function populateProgression($data, $award)
    {
        // Clear the old shit...
        $award->progressions()->delete();

        if(isset($data['rewardable_type'])) {
            foreach($data['rewardable_type'] as $key => $type)
            {
                AwardProgression::create([
                    'award_id' => $award->id,
                    'type'     => $type,
                    'type_id'       => $data['rewardable_id'][$key],
                    'quantity' => $data['quantity'][$key],
                ]);
            }
        }
    }

    /**
     * Populates the rewards of an award.
     */
    private function populateRewards($data, $award)
    {
        // Clear the old shit...
        $award->rewards()->delete();

        if(isset($data['award_type'])) {
            foreach($data['award_type'] as $key => $type)
            {
                AwardReward::create([
                    'award_id' => $award->id,
                    'type'     => $type,
                    'type_id'       => $data['award_id'][$key],
                    'quantity' => $data['award_quantity'][$key],
                ]);
            }
        }
    }

    /**
     * Deletes an award.
     *
     * @param  \App\Models\Award\Award  $award
     * @return bool
     */
    public function deleteAward($award)
    {
        DB::beginTransaction();

        try {
            // Check first if the award is currently owned or if some other site feature uses it
            if(DB::table('user_awards')->where([['award_id', '=', $award->id], ['count', '>', 0]])->exists()) throw new \Exception("At least one user currently owns this ".__('awards.award').". Please remove the ".__('awards.awards')." before deleting it.");
            if(DB::table('character_awards')->where([['award_id', '=', $award->id], ['count', '>', 0]])->exists()) throw new \Exception("At least one character currently owns this ".__('awards.award').". Please remove the ".__('awards.awards')." before deleting it.");
            if(DB::table('loots')->where('rewardable_type', 'Award')->where('rewardable_id', $award->id)->exists()) throw new \Exception("At least one loot table currently distributes this ".__('awards.award')." as a potential reward. Please remove the ".__('awards.awards')." before deleting it.");
            if(DB::table('prompt_rewards')->where('rewardable_type', 'Award')->where('rewardable_id', $award->id)->exists()) throw new \Exception("At least one prompt currently distributes this ".__('awards.award')." as a reward. Please remove the ".__('awards.awards')." before deleting it.");

            DB::table('awards_log')->where('award_id', $award->id)->delete();
            DB::table('user_awards')->where('award_id', $award->id)->delete();
            DB::table('character_awards')->where('award_id', $award->id)->delete();
            if($award->has_image) $this->deleteImage($award->imagePath, $award->imageFileName);
            $award->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
