<?php

namespace App\Models\Pet;

use App\Models\Model;

class PetVariantDropData extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pet_drop_data_id', 'variant_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pet_variant_drop_data';

    /**
     * Validation rules for pet creation.
     *
     * @var array
     */
    public static $createRules = [
        'variant_id'     => 'required|unique:pet_variant_drop_data',
        'drop_frequency' => 'required',
        'drop_interval'  => 'required',
    ];

    /**
     * Validation rules for pet updating.
     *
     * @var array
     */
    public static $updateRules = [
        'drop_frequency' => 'required',
        'drop_interval'  => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the pet to which the data pertains.
     */
    public function variant() {
        return $this->belongsTo('App\Models\Pet\PetVariant', 'variant_id');
    }

    /**
     * Get any pet drops using this data.
     */
    public function petDrops() {
        return $this->hasMany('App\Models\Pet\PetDrop', 'drop_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the parameter attribute as an associative array.
     *
     * @return array
     */
    public function getUrlAttribute() {
        return url('admin/data/pets/drops/edit/'.$this->pet->id.'/variants/'.$this->variant_id);
    }

    /**
     * Get the parameter attribute as an associative array.
     *
     * @return array
     */
    public function getParametersAttribute() {
        if (isset($this->attributes['parameters'])) {
            return json_decode($this->attributes['parameters'], true);
        } else {
            return null;
        }
    }

    /**
     * Get the parameter attribute as an array with the keys and values the same.
     *
     * @return array
     */
    public function getParameterArrayAttribute() {
        foreach ($this->parameters as $parameter=>$weight) {
            $paramArray[$parameter] = $parameter;
        }

        return $paramArray;
    }

    /**
     * Get the parameter attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() {
        if (isset($this->attributes['data'])) {
            return json_decode($this->attributes['data'], true);
        } else {
            return null;
        }
    }

    /**
     * Check if the drop data is active or not.
     *
     * @return array
     */
    public function getIsActiveAttribute() {
        return $this->attributes['is_active'];
    }

    /**
     * Retrieve the drop data's cap.
     *
     * @return array
     */
    public function getCapAttribute() {
        return $this->data['cap'] ?? null;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Rolls a group for a pet.
     *
     * @return string
     */
    public function rollParameters() {
        $parameters = $this->parameters;
        $totalWeight = 0;
        foreach ($parameters as $parameter=>$weight) {
            $totalWeight += $weight;
        }

        for ($i = 0; $i < 1; $i++) {
            $roll = mt_rand(0, $totalWeight - 1);
            $result = null;
            $prev = null;
            $count = 0;
            foreach ($parameters as $parameter=>$weight) {
                $count += $weight;

                if ($roll < $count) {
                    $result = $parameter;
                    break;
                }
                $prev = $parameter;
            }
            if (!$result) {
                $result = $prev;
            }
        }

        return $result;
    }

    /**
     * Get the rewards for the submission/claim.
     *
     * @param mixed $namespace
     *
     * @return array
     */
    public function Rewards($namespace = false) {
        if ($this->data && isset($this->data['assets'])) {
            $assets = parseDropAssetData($this->data['assets']);
            $rewards = [];
            foreach ($assets as $group => $types) {
                foreach ($types as $type => $a) {
                    $class = getAssetModelString($type, $namespace);
                    foreach ($a as $id => $asset) {
                        $rewards[$group][] = (object) [
                            'rewardable_type' => $class,
                            'rewardable_id'   => $id,
                            'min_quantity'    => $asset['min_quantity'],
                            'max_quantity'    => $asset['max_quantity'],
                        ];
                    }
                }
            }

            return $rewards;
        }

        return null;
    }

    /**
     * gets the rewards as a comma-seperated string.
     */
    public function rewardString() {
        $string = [];
        foreach ($this->rewards(true) as $label => $reward_values) {
            foreach ($reward_values as $reward) {
                $reward_object = $reward->rewardable_type::find($reward->rewardable_id);
                if ($reward->min_quantity == $reward->max_quantity) {
                    $string[$label][] = $reward_object->displayname.' ('.$reward->min_quantity.')';
                } else {
                    $string[$label][] = $reward_object->displayname.' ('.$reward->min_quantity.'-'.$reward->max_quantity.')';
                }
            }
        }

        return $string;
    }
}
